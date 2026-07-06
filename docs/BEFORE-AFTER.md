PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response                                                                                     0.15s  

   FAIL  Tests\Feature\NutritionLogTest
  ⨯ it rejects unauthenticated export request                                                                                         0.10s  
  ⨯ it rejects unauthenticated summary request                                                                                        0.01s  
  ⨯ it returns 422 when start_date is missing                                                                                         0.03s  
  ⨯ it returns 422 when end_date is missing                                                                                           0.01s  
  ⨯ it returns 422 when end_date is before start_date                                                                                 0.01s  
  ⨯ it returns 422 when date range exceeds 90 days                                                                                    0.01s  
  ⨯ it export response has content-type text/csv                                                                                      0.03s  
  ⨯ it export response has correct content-disposition header                                                                         0.01s  
  ⨯ it export csv contains the header row                                                                                             0.01s  
  ⨯ it export rows are ordered by date then meal name                                                                                 0.02s  
  ⨯ it computes calories when stored value is null                                                                                    0.01s  
  ⨯ it uses stored calories when present in export                                                                                    0.01s  
  ⨯ it required 408.0 row is correct                                                                                                  0.01s  
  ⨯ it export contains a final TOTAL row                                                                                              0.01s  
  ⨯ it TOTAL row values are correct                                                                                                   0.01s  
  ✓ it user A cannot export user B logs                                                                                               0.01s  
  ⨯ it user A summary does not include user B logs                                                                                    0.01s  
  ⨯ it summary returns correct date range                                                                                             0.01s  
  ⨯ it summary returns per-day totals                                                                                                 0.01s  
  ⨯ it summary omits days with no logs                                                                                                0.01s  
  ⨯ it summary uses computed calories when stored value is null                                                                       0.01s  
  ⨯ it summary uses stored calories when present                                                                                      0.01s  
  ⨯ it neutralizes meal names starting with formula characters                                                                        0.01s  
  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it rejects unauthenticated export request                                                        
  Expected response status code [401] but received 404.
Failed asserting that 404 is identical to 401.

  at tests\Feature\NutritionLogTest.php:21
     17▕ // --- Authentication tests ---
     18▕ 
     19▕ it('rejects unauthenticated export request', function () {
     20▕     $this->getJson('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-01-07')
  ➜  21▕         ->assertUnauthorized();
     22▕ });
     23▕ 
     24▕ it('rejects unauthenticated summary request', function () {
     25▕     $this->getJson('/api/nutrition-logs/summary?start_date=2026-01-01&end_date=2026-01-07')

  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it rejects unauthenticated summary request                                                       
  Expected response status code [401] but received 404.
Failed asserting that 404 is identical to 401.

  at tests\Feature\NutritionLogTest.php:26
     22▕ });
     23▕ 
     24▕ it('rejects unauthenticated summary request', function () {
     25▕     $this->getJson('/api/nutrition-logs/summary?start_date=2026-01-01&end_date=2026-01-07')
  ➜  26▕         ->assertUnauthorized();
     27▕ });
     28▕ 
     29▕ // --- Validation tests ---
     30▕

  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it returns 422 when start_date is missing                                                        
  Expected response status code [422] but received 404.
Failed asserting that 404 is identical to 422.

  at tests\Feature\NutritionLogTest.php:36
     32▕     $user = User::factory()->create();
     33▕ 
     34▕     $this->actingAs($user, 'sanctum')
     35▕         ->getJson('/api/nutrition-logs/export?end_date=2026-01-07')
  ➜  36▕         ->assertUnprocessable();
     37▕ });
     38▕ 
     39▕ it('returns 422 when end_date is missing', function () {
     40▕     $user = User::factory()->create();

  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it returns 422 when end_date is missing                                                          
  Expected response status code [422] but received 404.
Failed asserting that 404 is identical to 422.

  at tests\Feature\NutritionLogTest.php:44
     40▕     $user = User::factory()->create();
     41▕ 
     42▕     $this->actingAs($user, 'sanctum')
     43▕         ->getJson('/api/nutrition-logs/export?start_date=2026-01-01')
  ➜  44▕         ->assertUnprocessable();
     45▕ });
     46▕ 
     47▕ it('returns 422 when end_date is before start_date', function () {
     48▕     $user = User::factory()->create();

  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it returns 422 when end_date is before start_date                                                
  Expected response status code [422] but received 404.
Failed asserting that 404 is identical to 422.

  at tests\Feature\NutritionLogTest.php:52
     48▕     $user = User::factory()->create();
     49▕ 
     50▕     $this->actingAs($user, 'sanctum')
     51▕         ->getJson('/api/nutrition-logs/export?start_date=2026-01-15&end_date=2026-01-01')
  ➜  52▕         ->assertUnprocessable();
     53▕ });
     54▕ 
     55▕ it('returns 422 when date range exceeds 90 days', function () {
     56▕     $user = User::factory()->create();

  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it returns 422 when date range exceeds 90 days                                                   
  Expected response status code [422] but received 404.
Failed asserting that 404 is identical to 422.

  at tests\Feature\NutritionLogTest.php:60
     56▕     $user = User::factory()->create();
     57▕ 
     58▕     $this->actingAs($user, 'sanctum')
     59▕         ->getJson('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-04-20')
  ➜  60▕         ->assertUnprocessable();
     61▕ });
     62▕ 
     63▕ // --- CSV header tests ---
     64▕

  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it export response has content-type text/csv                                                     
  Expected: text/html; charset=utf-8

  To contain: text/csv

  at tests\Feature\NutritionLogTest.php:71
     67▕ 
     68▕     $response = $this->actingAs($user, 'sanctum')
     69▕         ->get('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-01-07');
     70▕ 
  ➜  71▕     expect($response->headers->get('Content-Type'))->toContain('text/csv');
     72▕ });
     73▕ 
     74▕ it('export response has correct content-disposition header', function () {
     75▕     $user = User::factory()->create();

  1   tests\Feature\NutritionLogTest.php:71

  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it export response has correct content-disposition header              InvalidExpectationValue   
  Invalid expectation value type. Expected [iterable].

  at tests\Feature\NutritionLogTest.php:81
     77▕     $disposition = $this->actingAs($user, 'sanctum')
     78▕         ->get('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-01-07')
     79▕         ->headers->get('Content-Disposition');
     80▕ 
  ➜  81▕     expect($disposition)->toContain('attachment');
     82▕     expect($disposition)->toContain('nutrition-log-export.csv');
     83▕ });
     84▕ 
     85▕ // --- CSV content tests ---

  1   tests\Feature\NutritionLogTest.php:81

  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it export csv contains the header row                                                            
  Failed asserting that two arrays are identical.
--- Expected
+++ Actual
@@ @@
 Array &0 [
-    0 => 'Date',
-    1 => 'Meal',
-    2 => 'Protein (g)',
-    3 => 'Carbs (g)',
-    4 => 'Fat (g)',
-    5 => 'Calories',
+    0 => '<!DOCTYPE html>',
 ]

  at tests\Feature\NutritionLogTest.php:96
     92▕             ->get('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-01-07')
     93▕             ->getContent()
     94▕     );
     95▕ 
  ➜  96▕     expect($rows[0])->toBe(['Date', 'Meal', 'Protein (g)', 'Carbs (g)', 'Fat (g)', 'Calories']);
     97▕ });
     98▕ 
     99▕ it('export rows are ordered by date then meal name', function () {
    100▕     $user = User::factory()->create();

  1   tests\Feature\NutritionLogTest.php:96

  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it export rows are ordered by date then meal name                                                
  Failed asserting that two strings are identical.
--- Expected
+++ Actual
@@ @@
-'2026-01-01'
+'<html lang="en">'

  at tests\Feature\NutritionLogTest.php:112
    108▕             ->get('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-01-07')
    109▕             ->getContent()
    110▕     );
    111▕ 
  ➜ 112▕     expect($rows[1][0])->toBe('2026-01-01');
    113▕     expect($rows[1][1])->toBe('Breakfast');
    114▕     expect($rows[2][0])->toBe('2026-01-01');
    115▕     expect($rows[2][1])->toBe('Dinner');
    116▕     expect($rows[3][0])->toBe('2026-01-02');

  1   tests\Feature\NutritionLogTest.php:112

  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it computes calories when stored value is null                                  ErrorException   
  Undefined array key 5

  at tests\Feature\NutritionLogTest.php:135
    131▕             ->getContent()
    132▕     );
    133▕ 
    134▕     // 10*4 + 20*4 + 5*9 = 40 + 80 + 45 = 165.0
  ➜ 135▕     expect($rows[1][5])->toBe('165.0');
    136▕ });
    137▕ 
    138▕ it('uses stored calories when present in export', function () {
    139▕     $user = User::factory()->create();

  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it uses stored calories when present in export                                  ErrorException   
  Undefined array key 5

  at tests\Feature\NutritionLogTest.php:152
    148▕             ->get('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-01-01')
    149▕             ->getContent()
    150▕     );
    151▕ 
  ➜ 152▕     expect($rows[1][5])->toBe('500.0');
    153▕ });
    154▕ 
    155▕ it('required 408.0 row is correct', function () {
    156▕     $user = User::factory()->create();

  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it required 408.0 row is correct                                                ErrorException   
  Undefined array key 5

  at tests\Feature\NutritionLogTest.php:169
    165▕             ->get('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-01-01')
    166▕             ->getContent()
    167▕     );
    168▕ 
  ➜ 169▕     expect($rows[1][5])->toBe('408.0');
    170▕ });
    171▕ 
    172▕ it('export contains a final TOTAL row', function () {
    173▕     $user = User::factory()->create();

  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it export contains a final TOTAL row                                                             
  Failed asserting that two strings are identical.
--- Expected
+++ Actual
@@ @@
-'TOTAL'
+'</html>'

  at tests\Feature\NutritionLogTest.php:186
    182▕             ->get('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-01-01')
    183▕             ->getContent()
    184▕     );
    185▕ 
  ➜ 186▕     expect(end($rows)[0])->toBe('TOTAL');
    187▕ });
    188▕ 
    189▕ it('TOTAL row values are correct', function () {
    190▕     $user = User::factory()->create();

  1   tests\Feature\NutritionLogTest.php:186

  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it TOTAL row values are correct                                                                  
  Failed asserting that two strings are identical.
--- Expected
+++ Actual
@@ @@
-'TOTAL'
+'</html>'

  at tests\Feature\NutritionLogTest.php:208
    204▕             ->getContent()
    205▕     );
    206▕ 
    207▕     $total = end($rows);
  ➜ 208▕     expect($total[0])->toBe('TOTAL');
    209▕     expect((float) $total[2])->toBe(50.0);   // protein: 30+20
    210▕     expect((float) $total[3])->toBe(75.0);   // carbs: 45+30
    211▕     expect((float) $total[4])->toBe(22.0);   // fat: 12+10
    212▕     expect($total[5])->toBe('708.0');         // calories: 408.0+300.0

  1   tests\Feature\NutritionLogTest.php:208

  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it user A summary does not include user B logs                                                   
  Failed asserting that null is identical to Array &0 [].

  at tests\Feature\NutritionLogTest.php:244
    240▕     ]);
    241▕ 
    242▕     $this->actingAs($userA, 'sanctum')
    243▕         ->getJson('/api/nutrition-logs/summary?start_date=2026-01-01&end_date=2026-01-07')
  ➜ 244▕         ->assertJsonPath('days', []);
    245▕ });
    246▕ 
    247▕ // --- JSON summary tests ---
    248▕

  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it summary returns correct date range                                                            
  Unable to find JSON fragment: 

[{"end_date":"2026-01-07"}]

within

[{"exception":"Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\AbstractRouteCollection.php","line":44,"message":"The route api\/nutrition-logs\/summary could not be found.","trace":[{"class":"P\\Tests\\Feature\\NutritionLogTest","function":"Pest\\Factories\\{closure}","type":"->"},{"file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\pestphp\\pest\\bin\\pest","function":"{closure}","line":192},{"file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\pestphp\\pest\\src\\Concerns\\Testable.php","function":"call_user_func_array","line":419},{"class":"Illuminate\\Foundation\\Http\\Kernel","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Kernel.php","function":"sendRequestThroughRouter","line":144,"type":"->"},{"class":"Illuminate\\Foundation\\Http\\Kernel","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Testing\\Concerns\\MakesHttpRequests.php","function":"handle","line":607,"type":"->"},{"class":"Illuminate\\Foundation\\Http\\Kernel","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php","function":"Illuminate\\Foundation\\Http\\{closure}","line":180,"type":"->"},{"class":"Illuminate\\Foundation\\Http\\Middleware\\ConvertEmptyStringsToNull","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php","function":"handle","line":219,"type":"->"},{"class":"Illuminate\\Foundation\\Http\\Middleware\\InvokeDeferredCallbacks","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php","function":"handle","line":219,"type":"->"},{"class":"Illuminate\\Foundation\\Http\\Middleware\\PreventRequestsDuringMaintenance","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php","function":"handle","line":219,"type":"->"},{"class":"Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\ConvertEmptyStringsToNull.php","function":"handle","line":31,"type":"->"},{"class":"Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\TrimStrings.php","function":"handle","line":51,"type":"->"},{"class":"Illuminate\\Foundation\\Http\\Middleware\\TrimStrings","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php","function":"handle","line":219,"type":"->"},{"class":"Illuminate\\Foundation\\Testing\\TestCase","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\tests\\Feature\\NutritionLogTest.php","function":"getJson","line":253,"type":"->"},{"class":"Illuminate\\Foundation\\Testing\\TestCase","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Testing\\Concerns\\MakesHttpRequests.php","function":"call","line":573,"type":"->"},{"class":"Illuminate\\Foundation\\Testing\\TestCase","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Testing\\Concerns\\MakesHttpRequests.php","function":"json","line":381,"type":"->"},{"class":"Illuminate\\Http\\Middleware\\HandleCors","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php","function":"handle","line":219,"type":"->"},{"class":"Illuminate\\Http\\Middleware\\TrustProxies","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php","function":"handle","line":219,"type":"->"},{"class":"Illuminate\\Http\\Middleware\\ValidatePathEncoding","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php","function":"handle","line":219,"type":"->"},{"class":"Illuminate\\Http\\Middleware\\ValidatePostSize","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php","function":"handle","line":219,"type":"->"},{"class":"Illuminate\\Pipeline\\Pipeline","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Kernel.php","function":"then","line":175,"type":"->"},{"class":"Illuminate\\Pipeline\\Pipeline","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\InvokeDeferredCallbacks.php","function":"Illuminate\\Pipeline\\{closure}","line":22,"type":"->"},{"class":"Illuminate\\Pipeline\\Pipeline","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\PreventRequestsDuringMaintenance.php","function":"Illuminate\\Pipeline\\{closure}","line":109,"type":"->"},{"class":"Illuminate\\Pipeline\\Pipeline","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest.php","function":"Illuminate\\Pipeline\\{closure}","line":21,"type":"->"},{"class":"Illuminate\\Pipeline\\Pipeline","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest.php","function":"Illuminate\\Pipeline\\{closure}","line":21,"type":"->"},{"class":"Illuminate\\Pipeline\\Pipeline","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Http\\Middleware\\HandleCors.php","function":"Illuminate\\Pipeline\\{closure}","line":74,"type":"->"},{"class":"Illuminate\\Pipeline\\Pipeline","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Http\\Middleware\\TrustProxies.php","function":"Illuminate\\Pipeline\\{closure}","line":58,"type":"->"},{"class":"Illuminate\\Pipeline\\Pipeline","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Http\\Middleware\\ValidatePathEncoding.php","function":"Illuminate\\Pipeline\\{closure}","line":26,"type":"->"},{"class":"Illuminate\\Pipeline\\Pipeline","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Http\\Middleware\\ValidatePostSize.php","function":"Illuminate\\Pipeline\\{closure}","line":27,"type":"->"},{"class":"Illuminate\\Pipeline\\Pipeline","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php","function":"Illuminate\\Pipeline\\{closure}","line":137,"type":"->"},{"class":"Illuminate\\Routing\\AbstractRouteCollection","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\RouteCollection.php","function":"handleMatchedRoute","line":184,"type":"->"},{"class":"Illuminate\\Routing\\RouteCollection","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\Router.php","function":"match","line":777,"type":"->"},{"class":"Illuminate\\Routing\\Router","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Http\\Kernel.php","function":"dispatch","line":200,"type":"->"},{"class":"Illuminate\\Routing\\Router","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\Router.php","function":"dispatchToRoute","line":753,"type":"->"},{"class":"Illuminate\\Routing\\Router","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\laravel\\framework\\src\\Illuminate\\Routing\\Router.php","function":"findRoute","line":764,"type":"->"},{"class":"PHPUnit\\Framework\\TestCase","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\phpunit\\phpunit\\src\\Framework\\TestCase.php","function":"runTest","line":519,"type":"->"},{"class":"PHPUnit\\Framework\\TestCase","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\phpunit\\phpunit\\src\\Framework\\TestRunner\\TestRunner.php","function":"runBare","line":87,"type":"->"},{"class":"PHPUnit\\Framework\\TestCase","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\phpunit\\phpunit\\src\\Framework\\TestSuite.php","function":"run","line":369,"type":"->"},{"class":"PHPUnit\\Framework\\TestRunner","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\phpunit\\phpunit\\src\\Framework\\TestCase.php","function":"run","line":365,"type":"->"},{"class":"PHPUnit\\Framework\\TestSuite","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\phpunit\\phpunit\\src\\Framework\\TestSuite.php","function":"run","line":369,"type":"->"},{"class":"PHPUnit\\Framework\\TestSuite","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\phpunit\\phpunit\\src\\Framework\\TestSuite.php","function":"run","line":369,"type":"->"},{"class":"PHPUnit\\Framework\\TestSuite","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\phpunit\\phpunit\\src\\TextUI\\TestRunner.php","function":"run","line":64,"type":"->"},{"class":"PHPUnit\\TextUI\\Application","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\pestphp\\pest\\src\\Kernel.php","function":"run","line":103,"type":"->"},{"class":"PHPUnit\\TextUI\\TestRunner","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\phpunit\\phpunit\\src\\TextUI\\Application.php","function":"run","line":211,"type":"->"},{"class":"P\\Tests\\Feature\\NutritionLogTest","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\pestphp\\pest\\src\\Concerns\\Testable.php","function":"__callClosure","line":321,"type":"->"},{"class":"P\\Tests\\Feature\\NutritionLogTest","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\pestphp\\pest\\src\\Factories\\TestCaseFactory.php(173) : eval()'d code","function":"__runTest","line":170,"type":"->"},{"class":"P\\Tests\\Feature\\NutritionLogTest","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\pestphp\\pest\\src\\Factories\\TestCaseMethodFactory.php","function":"{closure}","line":171,"type":"->"},{"class":"P\\Tests\\Feature\\NutritionLogTest","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\pestphp\\pest\\src\\Support\\ExceptionTrace.php","function":"Pest\\Concerns\\{closure}","line":26,"type":"->"},{"class":"P\\Tests\\Feature\\NutritionLogTest","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\phpunit\\phpunit\\src\\Framework\\TestCase.php","function":"__pest_evaluable_it_summary_returns_correct_date_range","line":1667,"type":"->"},{"class":"Pest\\Kernel","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\pestphp\\pest\\bin\\pest","function":"handle","line":184,"type":"->"},{"class":"Pest\\Support\\ExceptionTrace","file":"C:\\Users\\mccor\\Desktop\\Projects\\MacroActive\\MA18\\nutritionLog\\vendor\\pestphp\\pest\\src\\Concerns\\Testable.php","function":"ensure","line":419,"type":"::"}]}].
Failed asserting that false is true.

  at tests\Feature\NutritionLogTest.php:254
    250▕     $user = User::factory()->create();
    251▕ 
    252▕     $this->actingAs($user, 'sanctum')
    253▕         ->getJson('/api/nutrition-logs/summary?start_date=2026-01-01&end_date=2026-01-07')
  ➜ 254▕         ->assertJsonFragment(['start_date' => '2026-01-01', 'end_date' => '2026-01-07']);
    255▕ });
    256▕ 
    257▕ it('summary returns per-day totals', function () {
    258▕     $user = User::factory()->create();

  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it summary returns per-day totals                                                                
  Failed asserting that null is identical to '2026-01-01'.

  at tests\Feature\NutritionLogTest.php:274
    270▕         ->getJson('/api/nutrition-logs/summary?start_date=2026-01-01&end_date=2026-01-07');
    271▕ 
    272▕     // protein: 50, carbs: 75, fat: 20
    273▕     // calories: (30*4+45*4+12*9) + (20*4+30*4+8*9) = 408 + 272 = 680
  ➜ 274▕     $response->assertJsonPath('days.0.date', '2026-01-01');
    275▕     $response->assertJsonPath('days.0.total_protein_g', 50.0);
    276▕     $response->assertJsonPath('days.0.total_carbs_g', 75.0);
    277▕     $response->assertJsonPath('days.0.total_fat_g', 20.0);
    278▕     $response->assertJsonPath('days.0.total_calories', 680.0);

  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it summary omits days with no logs                                              ErrorException   
  Undefined array key "days"

  at tests\Feature\NutritionLogTest.php:293
    289▕     $data = $this->actingAs($user, 'sanctum')
    290▕         ->getJson('/api/nutrition-logs/summary?start_date=2026-01-01&end_date=2026-01-07')
    291▕         ->json();
    292▕ 
  ➜ 293▕     expect($data['days'])->toHaveCount(1);
    294▕     expect($data['days'][0]['date'])->toBe('2026-01-03');
    295▕ });
    296▕ 
    297▕ it('summary uses computed calories when stored value is null', function () {

  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it summary uses computed calories when stored value is null                                      
  Failed asserting that null is identical to 408.0.

  at tests\Feature\NutritionLogTest.php:307
    303▕     ]);
    304▕ 
    305▕     $this->actingAs($user, 'sanctum')
    306▕         ->getJson('/api/nutrition-logs/summary?start_date=2026-01-01&end_date=2026-01-01')
  ➜ 307▕         ->assertJsonPath('days.0.total_calories', 408.0);
    308▕ });
    309▕ 
    310▕ it('summary uses stored calories when present', function () {
    311▕     $user = User::factory()->create();

  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it summary uses stored calories when present                                                     
  Failed asserting that null is identical to 500.0.

  at tests\Feature\NutritionLogTest.php:320
    316▕     ]);
    317▕ 
    318▕     $this->actingAs($user, 'sanctum')
    319▕         ->getJson('/api/nutrition-logs/summary?start_date=2026-01-01&end_date=2026-01-01')
  ➜ 320▕         ->assertJsonPath('days.0.total_calories', 500.0);
    321▕ });
    322▕ 
    323▕ // --- CSV injection test ---
    324▕

  ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\NutritionLogTest > it neutralizes meal names starting with formula characters                      ErrorException   
  Undefined array key 1

  at tests\Feature\NutritionLogTest.php:339
    335▕             ->get('/api/nutrition-logs/export?start_date=2026-01-01&end_date=2026-01-01')
    336▕             ->getContent()
    337▕     );
    338▕ 
  ➜ 339▕     expect($rows[1][1])->not->toStartWith('=');
    340▕ });
    341▕


  Tests:    22 failed, 3 passed (19 assertions)
  Duration: 0.74s