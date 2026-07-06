Overview

This feature will add a nutrition log export system to a Laravel API.

Authenticated clients will be able to:

Download their nutrition logs as a CSV file.
View per-day nutrition totals as JSON.

The main implementation detail is that the CSV export must be memory-safe. I will not use ->get() for the export because that loads all matching records into memory. Instead, I will use a streaming response with cursor() so rows are processed one at a time.

The export will also include a final TOTAL row. Since the CSV is streamed, I will keep running totals while writing each row, then write the final TOTAL row after the cursor finishes.

Data model

I will create a nutrition_logs table.

nutrition_logs
Column	Type	Notes
id	bigIncrements	Primary key
user_id	foreignId	References users.id
logged_at	date	Calendar date of the meal/log
meal_name	string(100)	Name of the meal
protein_g	decimal(6,1)	Protein in grams
carbs_g	decimal(6,1)	Carbs in grams
fat_g	decimal(6,1)	Fat in grams
calories	decimal(6,1)->nullable()	Stored calories, optional
created_at	timestamp	Laravel timestamp
updated_at	timestamp	Laravel timestamp
Constraints
user_id will have a foreign key constraint to the users table.
I will use cascadeOnDelete() so if a user is deleted, their nutrition logs are also removed.
meal_name will be limited to 100 characters as required by the brief.
Macro and calorie fields will use decimal(6,1) as required by the brief.
Model

I will create a NutritionLog model.

The model will include:

protected $fillable = [
    'user_id',
    'logged_at',
    'meal_name',
    'protein_g',
    'carbs_g',
    'fat_g',
    'calories',
];

The model will cast:

protected $casts = [
    'logged_at' => 'date',
    'protein_g' => 'decimal:1',
    'carbs_g' => 'decimal:1',
    'fat_g' => 'decimal:1',
    'calories' => 'decimal:1',
];

The model will have a user() relationship:

public function user()
{
    return $this->belongsTo(User::class);
}
Endpoints and routes

Both endpoints will be protected by Sanctum authentication.

Routes will be added in routes/api.php.

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/nutrition-logs/export', [NutritionLogController::class, 'export']);
    Route::get('/nutrition-logs/summary', [NutritionLogController::class, 'summary']);
});
Endpoint: GET /api/nutrition-logs/export
Purpose

Downloads the authenticated client’s nutrition logs as a CSV file.

Query parameters
Parameter	Required	Format	Notes
start_date	Yes	YYYY-MM-DD	Start of export range
end_date	Yes	YYYY-MM-DD	Must be on or after start_date
Response headers

The response must include:

Content-Type: text/csv
Content-Disposition: attachment; filename="nutrition-log-export.csv"

The Content-Disposition: attachment header tells the browser to download the response as a file instead of displaying it in the browser tab.

CSV columns

The CSV header row will be:

Date,Meal,Protein (g),Carbs (g),Fat (g),Calories

Each log row will output:

logged_at,meal_name,protein_g,carbs_g,fat_g,calories

The final row will be a summary row:

TOTAL,,total_protein,total_carbs,total_fat,total_calories
CSV streaming strategy

I will use response()->streamDownload() for the CSV export.

Inside the streamed callback:

Open php://output.
Write the CSV header row with fputcsv().
Query the authenticated user’s logs for the requested date range.
Order rows by logged_at ascending, then meal_name ascending.
Iterate using cursor().
For each row:
Calculate calories if calories is null.
Write the row with fputcsv().
Add protein, carbs, fat, and calories to running totals.
After all rows have been streamed, write the final TOTAL row.
Close the output handle.

Example structure:

return response()->streamDownload(function () use ($request, $startDate, $endDate) {
    $handle = fopen('php://output', 'w');

    fputcsv($handle, [
        'Date',
        'Meal',
        'Protein (g)',
        'Carbs (g)',
        'Fat (g)',
        'Calories',
    ]);

    $totalProtein = 0;
    $totalCarbs = 0;
    $totalFat = 0;
    $totalCalories = 0;

    NutritionLog::query()
        ->where('user_id', $request->user()->id)
        ->whereBetween('logged_at', [$startDate, $endDate])
        ->orderBy('logged_at')
        ->orderBy('meal_name')
        ->cursor()
        ->each(function (NutritionLog $log) use ($handle, &$totalProtein, &$totalCarbs, &$totalFat, &$totalCalories) {
            $calories = $this->caloriesFor($log);

            fputcsv($handle, [
                $log->logged_at->toDateString(),
                $this->escapeCsvInjection($log->meal_name),
                $this->formatNumber($log->protein_g),
                $this->formatNumber($log->carbs_g),
                $this->formatNumber($log->fat_g),
                number_format($calories, 1, '.', ''),
            ]);

            $totalProtein += (float) $log->protein_g;
            $totalCarbs += (float) $log->carbs_g;
            $totalFat += (float) $log->fat_g;
            $totalCalories += $calories;
        });

    fputcsv($handle, [
        'TOTAL',
        '',
        $this->formatNumber($totalProtein),
        $this->formatNumber($totalCarbs),
        $this->formatNumber($totalFat),
        number_format($totalCalories, 1, '.', ''),
    ]);

    fclose($handle);
}, 'nutrition-log-export.csv', [
    'Content-Type' => 'text/csv',
]);

The exact implementation may differ slightly, but the important decision is that the export will stream and use cursor() instead of get().

Memory approach

The export must not load all matching records into memory at once.

I will not do this:

$logs = NutritionLog::where(...)->get();

That would collect every matching row into memory.

Instead, I will use:

NutritionLog::where(...)->cursor()

This returns a lazy iterable result, allowing the application to process rows one at a time.

This matters because the previous developer only tested 500 rows, but the brief says some clients now have 5,000+ log entries. The task also explicitly requires cursor() or chunked processing.

I am choosing cursor() because it fits naturally with writing a CSV row-by-row.

Calorie calculation

The calories column uses the stored database value if it exists.

If calories is null, I will calculate it from macros.

Formula:

Calories = protein_g * 4 + carbs_g * 4 + fat_g * 9

Constants:

Macro	Calories per gram
Protein	4
Carbs	4
Fat	9

Required example:

30g protein = 30 * 4 = 120
45g carbs = 45 * 4 = 180
12g fat = 12 * 9 = 108

120 + 180 + 108 = 408.0

So a row with:

protein_g = 30
carbs_g = 45
fat_g = 12
calories = null

must output:

408.0

I will create a shared helper method so both the CSV export and JSON summary use the same calorie logic.

Example:

private function caloriesFor(NutritionLog $log): float
{
    if ($log->calories !== null) {
        return (float) $log->calories;
    }

    return ((float) $log->protein_g * 4)
        + ((float) $log->carbs_g * 4)
        + ((float) $log->fat_g * 9);
}
Endpoint: GET /api/nutrition-logs/summary
Purpose

Returns per-day aggregate nutrition totals as JSON.

Query parameters

Same as the CSV export:

Parameter	Required	Format
start_date	Yes	YYYY-MM-DD
end_date	Yes	YYYY-MM-DD
Response shape
{
  "start_date": "2026-01-01",
  "end_date": "2026-01-07",
  "days": [
    {
      "date": "2026-01-01",
      "total_protein_g": 160,
      "total_carbs_g": 200,
      "total_fat_g": 65,
      "total_calories": 2025.0
    }
  ]
}
Summary strategy

The summary endpoint does not need to stream a file because it returns at most 90 grouped daily rows due to the maximum date range rule.

I will query the authenticated user’s logs within the date range and group totals by logged_at.

There are two possible approaches:

Use SQL aggregation.
Use a cursor and accumulate by date in PHP.

I will use SQL aggregation because the summary response is grouped and small. The important part is that calories use the same stored-or-computed logic.

The calorie SQL expression should be:

COALESCE(calories, protein_g * 4 + carbs_g * 4 + fat_g * 9)

The summary query will sum:

SUM(protein_g)
SUM(carbs_g)
SUM(fat_g)
SUM(COALESCE(calories, protein_g * 4 + carbs_g * 4 + fat_g * 9))

The result will be grouped by logged_at and ordered by logged_at.

Days with no logs will not be manually inserted into the response, so they will be omitted as required.

Validation approach

Both endpoints use the same validation rules.

I will either create a shared private validation method in the controller or a dedicated Form Request.

Because both endpoints share the same query parameters, a Form Request would be clean, but a private controller method is also acceptable for this small task.

Validation rules:

$request->validate([
    'start_date' => ['required', 'date_format:Y-m-d'],
    'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
]);

Then I will manually check the max range:

$start = Carbon::parse($validated['start_date']);
$end = Carbon::parse($validated['end_date']);

if ($start->diffInDays($end) + 1 > 90) {
    throw ValidationException::withMessages([
        'end_date' => ['The date range may not be greater than 90 days.'],
    ]);
}

I will treat the 90-day limit as inclusive. That means both the start and end date count as part of the range.

Auth and ownership approach

Both routes will use:

auth:sanctum

The API must never allow a user to pass another user’s ID.

I will always scope queries using the authenticated user:

where('user_id', $request->user()->id)

This prevents User A from exporting or summarizing User B’s logs.

The brief does not define a separate client role. My assumption is that any authenticated user is the client for their own logs.

Libraries and packages
Laravel

Used as the main framework.

Laravel Sanctum

Used for API authentication because the brief requires auth:sanctum.

Pest or PHPUnit

The project may use Laravel’s default testing setup. If the workflow expects Pest, I will install Pest and write feature tests with Pest.

Carbon

Used for date parsing and calculating the inclusive date range length.

Native PHP CSV functions

I will use:

fputcsv()

This is better than manually joining strings because it handles commas, quotes, and line breaks correctly.

Seeder approach

I will create seed data that proves both paths work.

The seeder will create at least two users:

User A
User B

User A will have logs inside the test date range.

User B will have logs in the same date range to verify cross-user isolation.

Seeder records will include:

A null-calorie row with the required math example:
protein_g = 30
carbs_g = 45
fat_g = 12
calories = null
Expected calories: 408.0
A row with stored calories:
This proves stored calories are used instead of recalculating.
Multiple logs on the same day:
This tests daily aggregation.
Logs on different dates:
This tests ordering and grouping.
A log outside the requested date range:
This proves date filtering works.
A meal name that looks like a spreadsheet formula:
Example: =SUM(A1:A100)
This tests CSV injection handling.
CSV injection handling

CSV injection can happen when user-controlled text is exported to a CSV and opened in a spreadsheet program.

If a cell starts with characters such as:

=
+
-
@

spreadsheet software may treat it as a formula.

This applies to this feature because meal_name is user-controlled.

Example risky meal name:

=SUM(A1:A100)

I will neutralize risky meal names before writing them to the CSV.

A simple helper method:

private function escapeCsvInjection(string $value): string
{
    if (preg_match('/^[=+\-@]/', $value)) {
        return "\t".$value;
    }

    return $value;
}

This keeps the visible text but prevents spreadsheet software from treating it as a formula.

Only text fields need this. Numeric fields are already numeric and should not be treated as user-entered formulas.

Number formatting

The brief examples show macro values sometimes without .0, but the database stores one decimal place.

I will make sure calories are formatted with one decimal place because the acceptance criteria specifically expects:

408.0

For macros, I will avoid unnecessary formatting issues by consistently outputting numeric values.

Acceptable examples:

30
30.0

The key requirement is that totals and calorie math are correct.

If tests expect exact CSV strings, I will standardize formatting to:

Macros: remove trailing .0 where possible.
Calories: always one decimal place.
Timezone decision

The logged_at column is a date, not a datetime.

I will treat it as a calendar date and will not apply timezone conversion.

The date range will be inclusive:

whereBetween('logged_at', [$startDate, $endDate])

Reason:

There is no time component to shift.
The user is exporting logs by calendar date.
Applying timezone conversion to a date-only field could create incorrect off-by-one behaviour.
Edge cases
Missing start_date

Return 422.

Missing end_date

Return 422.

Invalid date format

Return 422.

Example invalid input:

start_date=01-01-2026

The expected format is:

YYYY-MM-DD
end_date before start_date

Return 422.

Example:

start_date=2026-06-15
end_date=2026-06-01
Date range greater than 90 days

Return 422.

Empty CSV export

If the user has no logs in the date range, I will still return a valid CSV.

Expected output:

Date,Meal,Protein (g),Carbs (g),Fat (g),Calories
TOTAL,,0,0,0,0.0
Empty JSON summary

If there are no logs in the range, return:

{
  "start_date": "2026-01-01",
  "end_date": "2026-01-07",
  "days": []
}
Stored calories are present

Use the stored value.

Stored calories are null

Compute calories from macros.

Multiple logs on the same day

Summary endpoint should combine them into one daily total.

Logs outside the date range

Exclude them.

Logs from another user

Exclude them.

Meal names with commas

Use fputcsv() so commas are quoted correctly.

Example:

Breakfast, eggs and toast
Meal names with formula-like text

Neutralize CSV injection.

Example:

=SUM(A1:A100)

Should be exported safely so Excel does not treat it as a formula.

Large export result

Use cursor() and stream the response so memory usage stays low.

Tests I will write
Auth tests
Unauthenticated users cannot access CSV export.
Unauthenticated users cannot access JSON summary.
Validation tests
Missing start_date returns 422.
Missing end_date returns 422.
Invalid date format returns 422.
end_date before start_date returns 422.
Date range over 90 days returns 422.
CSV export tests
Response has Content-Type: text/csv.
Response has Content-Disposition: attachment; filename="nutrition-log-export.csv".
CSV contains the header row.
Rows are ordered by logged_at ascending, then meal_name.
Null calories are computed correctly.
The required 30g/45g/12g row outputs 408.0.
Stored calories are used when present.
CSV contains the final TOTAL row.
TOTAL row values are correct.
User A does not see User B’s logs.
CSV injection meal names are neutralized.
JSON summary tests
Response includes start_date.
Response includes end_date.
Response includes days.
Per-day totals are correct.
Days with no logs are omitted.
Calories use stored value when present.
Calories are computed when stored value is null.
User A does not see User B’s logs.
Files I expect to create or modify
database/migrations/xxxx_xx_xx_xxxxxx_create_nutrition_logs_table.php
app/Models/NutritionLog.php
app/Http/Controllers/NutritionLogController.php
routes/api.php
database/seeders/NutritionLogSeeder.php
database/seeders/DatabaseSeeder.php
tests/Feature/NutritionLogExportTest.php
UNDERSTANDING.md
ESTIMATE.md
APPROACH.md
BEFORE-AFTER.md

If Pest is installed, the test file may use Pest syntax. If the project remains on PHPUnit, I will use Laravel feature test classes.

Decisions made from ambiguous parts of the brief
Client role

The brief says clients can export their own logs, but does not define a role system.

Decision: Any authenticated user is treated as the client for their own logs.

Empty export

The brief does not say whether an empty export should be an error.

Decision: Empty exports should return a valid CSV with a header and zero-value TOTAL row.

Empty summary

Decision: Empty summaries should return an empty days array.

90-day limit

The brief does not clarify inclusive vs exclusive range counting.

Decision: The date range is inclusive. start_date and end_date both count toward the 90-day limit.

Timezone

The brief does not define timezone handling.

Decision: No timezone conversion will be applied because logged_at is a date-only field.

CSV injection

The brief asks me to explain CSV injection but does not explicitly list it as an acceptance criterion.

Decision: I will still protect the meal_name field because it is user-controlled and can be opened in spreadsheet software.

Summary implementation

The brief requires streaming for CSV but not for JSON summary.

Decision: The CSV endpoint will stream using cursor(). The JSON summary can use SQL aggregation because it returns grouped daily totals for a maximum 90-day range.

Final implementation plan
Create the nutrition_logs migration.
Create the NutritionLog model.
Add the model relationship to User if needed.
Add shared date-range validation.
Add a calorie helper method.
Add a CSV injection protection helper method.
Build the CSV export endpoint with streamDownload() and cursor().
Accumulate running totals while streaming.
Write the TOTAL row after all rows have streamed.
Build the JSON summary endpoint using grouped totals.
Add protected API routes with auth:sanctum.
Add seeder data for null calories, stored calories, cross-user isolation, and CSV injection.
Write feature tests.
Run tests.
Fix issues.
Run formatter.
Paste terminal output into BEFORE-AFTER.md.