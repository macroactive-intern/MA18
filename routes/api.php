<?php

use App\Http\Controllers\NutritionLogController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/nutrition-logs/export',  [NutritionLogController::class, 'export']);
    Route::get('/nutrition-logs/summary', [NutritionLogController::class, 'summary']);
});
