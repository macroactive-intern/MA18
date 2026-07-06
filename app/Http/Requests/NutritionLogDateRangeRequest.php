<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class NutritionLogDateRangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date'   => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }

    protected function passedValidation(): void
    {
        $start = Carbon::parse($this->input('start_date'));
        $end   = Carbon::parse($this->input('end_date'));

        if ($start->diffInDays($end) + 1 > 90) {
            throw ValidationException::withMessages([
                'end_date' => ['The date range may not be greater than 90 days.'],
            ]);
        }
    }
}
