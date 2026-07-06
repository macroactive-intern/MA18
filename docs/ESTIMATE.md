Step 1

    Project set up
                1. Start new Laravel project
                2. connect to Github repo
                                                                                                    10 mins

----------------------------------------------------------------------------------------------------------------

Step 2

    Documentation
                1. Write out the Understand.md
                2. Write out the Time Estimate.md
                3. Add the Ai Time estimate to the Estimate.md
                4. Write out the Aproach.md
                                                                                                        120 mins

----------------------------------------------------------------------------------------------------------------

Step 3

    Finish Project set up
                1. Install dependencies
                2. Install Sanctum
                3. Install Pest
                4. Confirm API/auth setup
                                                                                                    20 mins

----------------------------------------------------------------------------------------------------------------

Step 4

    Tests

                1. Authentication tests
                                        Unauthenticated export request fails.
                                        Unauthenticated summary request fails.
                
                2. Validation tests
                                    Missing start_date returns 422.
                                    Missing end_date returns 422.
                                    end_date before start_date returns 422.
                                    Range over 90 days returns 422.
                
                3. CSV header tests
                                    Response has Content-Type: text/csv.
                                    Response has Content-Disposition: attachment; filename="nutrition-log-export.csv".
                
                4. CSV content tests
                                    Header row exists.
                                    Rows are ordered by date then meal name.
                                    Null calories are computed.
                                    Stored calories are used.
                                    Required 408.0 row is correct.
                                    Final TOTAL row exists.
                                    TOTAL row values are correct
                
                5. Cross-user isolation tests
                                    User A cannot export User B’s logs.
                                    User A summary does not include User B’s logs. 
                
                6. JSON summary tests
                                    Returns correct date range.
                                    Returns per-day totals.
                                    Omits days with no logs.
                                    Uses computed calories when calories is null.
                                    Uses stored calories when present.

                7. CSV injection test
                                    Meal names starting with formula characters are neutralized.
                                                                                                    120 mins

----------------------------------------------------------------------------------------------------------------

Step 5

    Build Data Layer

                1. Create migration
                                    Create nutrition_logs table.
                                    Add:
                                        id
                                        user_id
                                        logged_at
                                        meal_name
                                        protein_g
                                        carbs_g
                                        fat_g
                                        calories
                                        timestamps
                
                2. Add constraints
                                    Foreign key to users table.
                                    Cascade delete if appropriate.
                                    Decimal precision according to brief.
                                    meal_name max length 100.
                
                3. Create model
                                    NutritionLog
                                    Add fillable fields.
                                    Add casts for dates/decimals.
                                    Add user() relationship
                
                4. Run migration

                                                                                                    40 mins

----------------------------------------------------------------------------------------------------------------

Step 6

    Build Validation

                1. Create request validation
                                    Either use a Form Request or controller validation.
                
                2. Validate required dates
                                    start_date required.
                                    end_date required.
                                    Both must use YYYY-MM-DD.

                3. Validate date ordering
                                    end_date must be after or equal to start_date.
                
                4. Validate max range
                                    Reject ranges over 90 days with 422.
                
                5. Reuse validation
                                    Use the same validation logic for both export and summary endpoints.

                                                                                                    45 mins

----------------------------------------------------------------------------------------------------------------

Step 7

    Build CSV Export

                1. Create controller
                                    Example: NutritionLogExportController.
                
                2. Add export method
                                    Read validated start_date and end_date.
                                    Query authenticated user’s logs only.
                                    Filter by date range.
                                    Order by logged_at, then meal_name.
                
                3. Use streaming response
                                    Set Content-Type: text/csv.
                                    Set Content-Disposition: attachment; filename="nutrition-log-export.csv".
                
                4. Write CSV header
                                    Date,Meal,Protein (g),Carbs (g),Fat (g),Calories
                
                5. Stream rows with cursor()
                                    Do not use get().
                                    Write each row using fputcsv().
                
                6. Calculate calories per row
                                    Use stored calories when not null.
                                    Otherwise compute from macros.
                
                7. Accumulate totals
                                    Total protein.
                                    Total carbs.
                                    Total fat.
                                    Total calories.
                
                8. Write final TOTAL row
                                    Date column: TOTAL
                                    Meal column: empty
                                    Macro/calorie columns: accumulated totals.
                
                9. Apply CSV injection protection
                                    Sanitize risky meal_name values before export.
                                                                                                    35 mins

----------------------------------------------------------------------------------------------------------------

Step 8

    Build JSON Summary

                1. Add summary method
                                    Use same date validation.
                                    Query authenticated user only.
                                    Filter by date range.
                
                2. Group by day
                                    Group by logged_at.

                3. Calculate totals
                                    Sum protein.
                                    Sum carbs.
                                    Sum fat.
                                    Sum calories using stored-or-computed logic.
                
                4. Format response
                                    Include start_date.
                                    Include end_date.
                                    Include days.
                
                5. Omit empty days
                                    Do not generate rows for dates with no logs.
                                                                                                    30 mins

----------------------------------------------------------------------------------------------------------------

Step 8

    Routes and Auth

                1. Add API routes
                                    In routes/api.php:

                                    Route::middleware('auth:sanctum')->group(function () {
                                        Route::get('/nutrition-logs/export', ...);
                                        Route::get('/nutrition-logs/summary', ...);
                                    });
                
                2. Confirm Sanctum auth works
                                    Use authenticated test users.
                                    Ensure unauthenticated requests fail.
                
                3. Confirm ownership scope
                                    Never accept a user_id query parameter.
                                    Always use $request->user()->id.
                                                                                                    45 mins

----------------------------------------------------------------------------------------------------------------

Step 9

    Seeder

                1. Create nutrition log seeder
                                    Create at least two users.
                                    Create logs for User A.
                                    Create logs for User B.
                
                2. Add null-calorie rows
                                    Include the required example:
                                                                protein 30
                                                                carbs 45
                                                                fat 12
                                                                calories null
                
                3. Add stored-calorie rows
                                    Include rows where calories has a DB value.
                                    Verify stored value is used instead of computed value.
                
                4. Add varied dates
                                    Some inside the test range.
                                    Some outside the test range.
                                    Some same-day multiple meals.
                
                5. Add CSV injection test data
                                                                                                    30 mins

----------------------------------------------------------------------------------------------------------------

Step 10

    Run Tests
                                                                                                    20 mins

----------------------------------------------------------------------------------------------------------------

Step 11

    Fix any failing tests
                                                                                                    40 mins

----------------------------------------------------------------------------------------------------------------

Step 12

    Manual test
                                                                                                    45 mins

----------------------------------------------------------------------------------------------------------------

Step 13

    BEFORE-AFTER.md
                                                                                                    30 mins
----------------------------------------------------------------------------------------------------------------

                                                                                                    10.5 hrs

---------------------------------------------------------------------------------------------------------------- 

AI estimate
Area	Estimated Time
Setup and Sanctum configuration	30 mins
Documentation	90–120 mins
Migration/model/seeder	60–75 mins
Validation and routes	45–60 mins
CSV streaming export	60–90 mins
JSON summary endpoint	30–45 mins
Feature tests	120–150 mins
Debugging and manual verification	60–90 mins
BEFORE-AFTER.md	30 mins
AI total
9.5–11 hours
Reconciliation

My manual estimate comes to about 10.5 hours, which fits inside the AI estimate range of 9.5–11 hours.

The biggest risk areas are:

Getting streamed CSV tests working cleanly.
Matching the exact Content-Disposition header expected by the tests.
Making sure calories use stored values when present and computed values when null.
Ensuring the TOTAL row is correct while using cursor().
Handling CSV injection safely without breaking normal meal names.
Avoiding cross-user data leaks.

Final estimate:

10.5 hours

Safe quote range:

9.5–11 hours