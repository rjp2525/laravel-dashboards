<?php

namespace Reno\Dashboard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Reno\Dashboard\Enums\Period;

class WidgetDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'period' => ['nullable', 'string', Rule::in(array_column(Period::cases(), 'value'))],
            'filters' => ['nullable', 'array'],
            'timezone' => ['nullable', 'string', 'timezone'],
            'start_date' => ['nullable', 'date', 'required_with:end_date'],
            'end_date' => ['nullable', 'date', 'required_with:start_date', 'after_or_equal:start_date'],
        ];
    }
}
