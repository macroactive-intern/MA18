<?php

use App\Models\NutritionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function parseCsv(string $content): array
{
    return array_map(
        'str_getcsv',
        array_filter(explode("\n", str_replace("\r\n", "\n", trim($content))))
    );
}

// --- Authentication tests ---

it('rejects unauthenticated export request', function () {
    $this->getJson('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-01-07')
        ->assertUnauthorized();
});

it('rejects unauthenticated summary request', function () {
    $this->getJson('/api/nutrition-logs/summary?start_date=2026-01-01&end_date=2026-01-07')
        ->assertUnauthorized();
});

// --- Validation tests ---

it('returns 422 when start_date is missing', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/nutrition-logs/export?end_date=2026-01-07')
        ->assertUnprocessable();
});

it('returns 422 when end_date is missing', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/nutrition-logs/export?start_date=2026-01-01')
        ->assertUnprocessable();
});

it('returns 422 when end_date is before start_date', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/nutrition-logs/export?start_date=2026-01-15&end_date=2026-01-01')
        ->assertUnprocessable();
});

it('returns 422 when date range exceeds 90 days', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-04-20')
        ->assertUnprocessable();
});

it('accepts exactly 90 days as a valid range', function () {
    $user = User::factory()->create();

    // 2026-01-01 to 2026-03-31 = 90 days inclusive
    $this->actingAs($user, 'sanctum')
        ->getJson('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-03-31')
        ->assertOk();
});

// --- CSV header tests ---

it('export response has content-type text/csv', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->get('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-01-07');

    expect($response->headers->get('Content-Type'))->toContain('text/csv');
});

it('export response has correct content-disposition header', function () {
    $user = User::factory()->create();

    $disposition = $this->actingAs($user, 'sanctum')
        ->get('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-01-07')
        ->headers->get('Content-Disposition');

    expect($disposition)->toContain('attachment');
    expect($disposition)->toContain('nutrition-log-export.csv');
});

// --- CSV content tests ---

it('export csv contains the header row', function () {
    $user = User::factory()->create();

    $rows = parseCsv(
        $this->actingAs($user, 'sanctum')
            ->get('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-01-07')
            ->streamedContent()
    );

    expect($rows[0])->toBe(['Date', 'Meal', 'Protein (g)', 'Carbs (g)', 'Fat (g)', 'Calories']);
});

it('export rows are ordered by date then meal name', function () {
    $user = User::factory()->create();

    NutritionLog::factory()->create(['user_id' => $user->id, 'logged_at' => '2026-01-02', 'meal_name' => 'Lunch',     'protein_g' => 20, 'carbs_g' => 30, 'fat_g' => 5,  'calories' => 250.0]);
    NutritionLog::factory()->create(['user_id' => $user->id, 'logged_at' => '2026-01-01', 'meal_name' => 'Dinner',    'protein_g' => 25, 'carbs_g' => 35, 'fat_g' => 8,  'calories' => 320.0]);
    NutritionLog::factory()->create(['user_id' => $user->id, 'logged_at' => '2026-01-01', 'meal_name' => 'Breakfast', 'protein_g' => 30, 'carbs_g' => 45, 'fat_g' => 12, 'calories' => null]);

    $rows = parseCsv(
        $this->actingAs($user, 'sanctum')
            ->get('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-01-07')
            ->streamedContent()
    );

    expect($rows[1][0])->toBe('2026-01-01');
    expect($rows[1][1])->toBe('Breakfast');
    expect($rows[2][0])->toBe('2026-01-01');
    expect($rows[2][1])->toBe('Dinner');
    expect($rows[3][0])->toBe('2026-01-02');
    expect($rows[3][1])->toBe('Lunch');
});

it('computes calories when stored value is null', function () {
    $user = User::factory()->create();

    NutritionLog::factory()->create([
        'user_id' => $user->id, 'logged_at' => '2026-01-01', 'meal_name' => 'Breakfast',
        'protein_g' => 10, 'carbs_g' => 20, 'fat_g' => 5, 'calories' => null,
    ]);

    $rows = parseCsv(
        $this->actingAs($user, 'sanctum')
            ->get('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-01-01')
            ->streamedContent()
    );

    // 10*4 + 20*4 + 5*9 = 40 + 80 + 45 = 165.0
    expect($rows[1][5])->toBe('165.0');
});

it('uses stored calories when present in export', function () {
    $user = User::factory()->create();

    NutritionLog::factory()->create([
        'user_id' => $user->id, 'logged_at' => '2026-01-01', 'meal_name' => 'Lunch',
        'protein_g' => 30, 'carbs_g' => 45, 'fat_g' => 12, 'calories' => 500.0,
    ]);

    $rows = parseCsv(
        $this->actingAs($user, 'sanctum')
            ->get('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-01-01')
            ->streamedContent()
    );

    expect($rows[1][5])->toBe('500.0');
});

it('required 408.0 row is correct', function () {
    $user = User::factory()->create();

    NutritionLog::factory()->create([
        'user_id' => $user->id, 'logged_at' => '2026-01-01', 'meal_name' => 'Test',
        'protein_g' => 30, 'carbs_g' => 45, 'fat_g' => 12, 'calories' => null,
    ]);

    $rows = parseCsv(
        $this->actingAs($user, 'sanctum')
            ->get('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-01-01')
            ->streamedContent()
    );

    expect($rows[1][5])->toBe('408.0');
});

it('export contains a final TOTAL row', function () {
    $user = User::factory()->create();

    NutritionLog::factory()->create([
        'user_id' => $user->id, 'logged_at' => '2026-01-01', 'meal_name' => 'Breakfast',
        'protein_g' => 30, 'carbs_g' => 45, 'fat_g' => 12, 'calories' => null,
    ]);

    $rows = parseCsv(
        $this->actingAs($user, 'sanctum')
            ->get('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-01-01')
            ->streamedContent()
    );

    expect(end($rows)[0])->toBe('TOTAL');
});

it('TOTAL row values are correct', function () {
    $user = User::factory()->create();

    NutritionLog::factory()->create([
        'user_id' => $user->id, 'logged_at' => '2026-01-01', 'meal_name' => 'Breakfast',
        'protein_g' => 30, 'carbs_g' => 45, 'fat_g' => 12, 'calories' => null,   // 408.0
    ]);
    NutritionLog::factory()->create([
        'user_id' => $user->id, 'logged_at' => '2026-01-02', 'meal_name' => 'Lunch',
        'protein_g' => 20, 'carbs_g' => 30, 'fat_g' => 10, 'calories' => 300.0,
    ]);

    $rows = parseCsv(
        $this->actingAs($user, 'sanctum')
            ->get('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-01-07')
            ->streamedContent()
    );

    $total = end($rows);
    expect($total[0])->toBe('TOTAL');
    expect((float) $total[2])->toBe(50.0);   // protein: 30+20
    expect((float) $total[3])->toBe(75.0);   // carbs: 45+30
    expect((float) $total[4])->toBe(22.0);   // fat: 12+10
    expect($total[5])->toBe('708.0');         // calories: 408.0+300.0
});

// --- Cross-user isolation tests ---

it('user A cannot export user B logs', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    NutritionLog::factory()->create([
        'user_id' => $userB->id, 'logged_at' => '2026-01-01', 'meal_name' => 'User B Secret Meal',
        'protein_g' => 50, 'carbs_g' => 60, 'fat_g' => 20, 'calories' => null,
    ]);

    $content = $this->actingAs($userA, 'sanctum')
        ->get('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-01-07')
        ->streamedContent();

    expect($content)->not->toContain('User B Secret Meal');
});

it('user A summary does not include user B logs', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    NutritionLog::factory()->create([
        'user_id' => $userB->id, 'logged_at' => '2026-01-01', 'meal_name' => 'Lunch',
        'protein_g' => 50, 'carbs_g' => 60, 'fat_g' => 20, 'calories' => 600.0,
    ]);

    $this->actingAs($userA, 'sanctum')
        ->getJson('/api/nutrition-logs/summary?start_date=2026-01-01&end_date=2026-01-07')
        ->assertJsonPath('days', []);
});

// --- JSON summary tests ---

it('summary returns correct date range', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/nutrition-logs/summary?start_date=2026-01-01&end_date=2026-01-07')
        ->assertJsonFragment(['start_date' => '2026-01-01', 'end_date' => '2026-01-07']);
});

it('summary returns per-day totals', function () {
    $user = User::factory()->create();

    NutritionLog::factory()->create([
        'user_id' => $user->id, 'logged_at' => '2026-01-01', 'meal_name' => 'Breakfast',
        'protein_g' => 30, 'carbs_g' => 45, 'fat_g' => 12, 'calories' => null,
    ]);
    NutritionLog::factory()->create([
        'user_id' => $user->id, 'logged_at' => '2026-01-01', 'meal_name' => 'Lunch',
        'protein_g' => 20, 'carbs_g' => 30, 'fat_g' => 8, 'calories' => null,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/nutrition-logs/summary?start_date=2026-01-01&end_date=2026-01-07');

    // protein: 50, carbs: 75, fat: 20
    // calories: (30*4+45*4+12*9) + (20*4+30*4+8*9) = 408 + 272 = 680
    $day = $response->json('days.0');
    expect($day['date'])->toBe('2026-01-01');
    expect((float) $day['total_protein_g'])->toBe(50.0);
    expect((float) $day['total_carbs_g'])->toBe(75.0);
    expect((float) $day['total_fat_g'])->toBe(20.0);
    expect((float) $day['total_calories'])->toBe(680.0);
});

it('summary omits days with no logs', function () {
    $user = User::factory()->create();

    NutritionLog::factory()->create([
        'user_id' => $user->id, 'logged_at' => '2026-01-03', 'meal_name' => 'Lunch',
        'protein_g' => 20, 'carbs_g' => 30, 'fat_g' => 8, 'calories' => 300.0,
    ]);

    $data = $this->actingAs($user, 'sanctum')
        ->getJson('/api/nutrition-logs/summary?start_date=2026-01-01&end_date=2026-01-07')
        ->json();

    expect($data['days'])->toHaveCount(1);
    expect($data['days'][0]['date'])->toBe('2026-01-03');
});

it('summary uses computed calories when stored value is null', function () {
    $user = User::factory()->create();

    NutritionLog::factory()->create([
        'user_id' => $user->id, 'logged_at' => '2026-01-01', 'meal_name' => 'Meal',
        'protein_g' => 30, 'carbs_g' => 45, 'fat_g' => 12, 'calories' => null,
    ]);

    $calories = $this->actingAs($user, 'sanctum')
        ->getJson('/api/nutrition-logs/summary?start_date=2026-01-01&end_date=2026-01-01')
        ->json('days.0.total_calories');

    expect((float) $calories)->toBe(408.0);
});

it('summary uses stored calories when present', function () {
    $user = User::factory()->create();

    NutritionLog::factory()->create([
        'user_id' => $user->id, 'logged_at' => '2026-01-01', 'meal_name' => 'Meal',
        'protein_g' => 30, 'carbs_g' => 45, 'fat_g' => 12, 'calories' => 500.0,
    ]);

    $calories = $this->actingAs($user, 'sanctum')
        ->getJson('/api/nutrition-logs/summary?start_date=2026-01-01&end_date=2026-01-01')
        ->json('days.0.total_calories');

    expect((float) $calories)->toBe(500.0);
});

// --- CSV injection test ---

it('neutralizes meal names starting with formula characters', function () {
    $user = User::factory()->create();

    NutritionLog::factory()->create([
        'user_id' => $user->id, 'logged_at' => '2026-01-01', 'meal_name' => '=SUM(A1:A100)',
        'protein_g' => 10, 'carbs_g' => 10, 'fat_g' => 5, 'calories' => null,
    ]);

    $rows = parseCsv(
        $this->actingAs($user, 'sanctum')
            ->get('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-01-01')
            ->streamedContent()
    );

    expect($rows[1][1])->not->toStartWith('=');
});
