<?php

namespace App\Http\Controllers;

use App\Http\Requests\NutritionLogDateRangeRequest;
use App\Models\NutritionLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NutritionLogController extends Controller
{
    public function export(NutritionLogDateRangeRequest $request): StreamedResponse
    {
        $userId    = $request->user()->id;
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        return response()->streamDownload(function () use ($userId, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Date', 'Meal', 'Protein (g)', 'Carbs (g)', 'Fat (g)', 'Calories']);

            $totalProtein  = 0.0;
            $totalCarbs    = 0.0;
            $totalFat      = 0.0;
            $totalCalories = 0.0;

            NutritionLog::where('user_id', $userId)
                ->whereDate('logged_at', '>=', $startDate)
                ->whereDate('logged_at', '<=', $endDate)
                ->orderBy('logged_at')
                ->orderBy('meal_name')
                ->cursor()
                ->each(function (NutritionLog $log) use ($handle, &$totalProtein, &$totalCarbs, &$totalFat, &$totalCalories) {
                    $calories = $this->caloriesFor($log);

                    fputcsv($handle, [
                        $log->logged_at->toDateString(),
                        $this->escapeCsvInjection($log->meal_name),
                        $this->formatMacro($log->protein_g),
                        $this->formatMacro($log->carbs_g),
                        $this->formatMacro($log->fat_g),
                        number_format($calories, 1, '.', ''),
                    ]);

                    $totalProtein  += (float) $log->protein_g;
                    $totalCarbs    += (float) $log->carbs_g;
                    $totalFat      += (float) $log->fat_g;
                    $totalCalories += $calories;
                });

            fputcsv($handle, [
                'TOTAL',
                '',
                $this->formatMacro($totalProtein),
                $this->formatMacro($totalCarbs),
                $this->formatMacro($totalFat),
                number_format($totalCalories, 1, '.', ''),
            ]);

            fclose($handle);
        }, 'nutrition-log-export.csv', ['Content-Type' => 'text/csv']);
    }

    public function summary(NutritionLogDateRangeRequest $request): JsonResponse
    {
        $userId    = $request->user()->id;
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        $days = NutritionLog::where('user_id', $userId)
            ->whereDate('logged_at', '>=', $startDate)
            ->whereDate('logged_at', '<=', $endDate)
            ->select(
                DB::raw('DATE(logged_at) as date'),
                DB::raw('SUM(protein_g) as total_protein_g'),
                DB::raw('SUM(carbs_g) as total_carbs_g'),
                DB::raw('SUM(fat_g) as total_fat_g'),
                DB::raw('SUM(COALESCE(calories, protein_g * 4 + carbs_g * 4 + fat_g * 9)) as total_calories')
            )
            ->groupBy(DB::raw('DATE(logged_at)'))
            ->orderBy(DB::raw('DATE(logged_at)'))
            ->get()
            ->map(fn ($row) => [
                'date'           => $row->date,
                'total_protein_g' => (float) $row->total_protein_g,
                'total_carbs_g'  => (float) $row->total_carbs_g,
                'total_fat_g'    => (float) $row->total_fat_g,
                'total_calories' => (float) $row->total_calories,
            ])
            ->values()
            ->all();

        return response()->json([
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'days'       => $days,
        ]);
    }

    private function caloriesFor(NutritionLog $log): float
    {
        if ($log->calories !== null) {
            return (float) $log->calories;
        }

        return ((float) $log->protein_g * 4)
            + ((float) $log->carbs_g * 4)
            + ((float) $log->fat_g * 9);
    }

    private function escapeCsvInjection(string $value): string
    {
        if (preg_match('/^[=+\-@]/', $value)) {
            return "\t" . $value;
        }

        return $value;
    }

    private function formatMacro(float $value): string
    {
        return number_format($value, 1, '.', '');
    }
}
