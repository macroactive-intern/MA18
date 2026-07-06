<?php

namespace Database\Seeders;

use App\Models\NutritionLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class NutritionLogSeeder extends Seeder
{
    public function run(): void
    {
        $userA = User::factory()->create([
            'name'  => 'User A',
            'email' => 'usera@example.com',
        ]);

        $userB = User::factory()->create([
            'name'  => 'User B',
            'email' => 'userb@example.com',
        ]);

        // --- User A ---

        // Required null-calorie example: 30g protein + 45g carbs + 12g fat → 408.0
        NutritionLog::create([
            'user_id'   => $userA->id,
            'logged_at' => '2026-01-01',
            'meal_name' => 'Breakfast',
            'protein_g' => 30,
            'carbs_g'   => 45,
            'fat_g'     => 12,
            'calories'  => null,
        ]);

        // Same day second meal — tests multi-meal grouping and ordering
        NutritionLog::create([
            'user_id'   => $userA->id,
            'logged_at' => '2026-01-01',
            'meal_name' => 'Lunch',
            'protein_g' => 40,
            'carbs_g'   => 60,
            'fat_g'     => 15,
            'calories'  => null,
        ]);

        // Stored calorie row — 550.0 must be used, not the macro computation
        NutritionLog::create([
            'user_id'   => $userA->id,
            'logged_at' => '2026-01-02',
            'meal_name' => 'Dinner',
            'protein_g' => 35,
            'carbs_g'   => 50,
            'fat_g'     => 18,
            'calories'  => 550.0,
        ]);

        // Different date, null calories
        NutritionLog::create([
            'user_id'   => $userA->id,
            'logged_at' => '2026-01-03',
            'meal_name' => 'Breakfast',
            'protein_g' => 25,
            'carbs_g'   => 40,
            'fat_g'     => 10,
            'calories'  => null,
        ]);

        // CSV injection — meal name starts with '='
        NutritionLog::create([
            'user_id'   => $userA->id,
            'logged_at' => '2026-01-04',
            'meal_name' => '=SUM(A1:A100)',
            'protein_g' => 20,
            'carbs_g'   => 30,
            'fat_g'     => 8,
            'calories'  => null,
        ]);

        // Outside range — must be excluded when querying 2026-01-01 to 2026-01-07
        NutritionLog::create([
            'user_id'   => $userA->id,
            'logged_at' => '2025-12-31',
            'meal_name' => 'New Year Dinner',
            'protein_g' => 50,
            'carbs_g'   => 70,
            'fat_g'     => 25,
            'calories'  => 800.0,
        ]);

        // --- User B (same date range — proves cross-user isolation) ---

        NutritionLog::create([
            'user_id'   => $userB->id,
            'logged_at' => '2026-01-01',
            'meal_name' => 'User B Breakfast',
            'protein_g' => 60,
            'carbs_g'   => 80,
            'fat_g'     => 20,
            'calories'  => null,
        ]);

        NutritionLog::create([
            'user_id'   => $userB->id,
            'logged_at' => '2026-01-02',
            'meal_name' => 'User B Lunch',
            'protein_g' => 45,
            'carbs_g'   => 55,
            'fat_g'     => 12,
            'calories'  => 480.0,
        ]);
    }
}
