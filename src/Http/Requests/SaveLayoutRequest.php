<?php

namespace Reno\Dashboard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveLayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'layout' => ['required', 'array'],
            'layout.*.key' => ['required', 'string'],
            'layout.*.position' => ['required', 'array'],
            'layout.*.position.x' => ['required', 'integer', 'min:0'],
            'layout.*.position.y' => ['required', 'integer', 'min:0'],
            'layout.*.position.w' => ['required', 'integer', 'min:1'],
            'layout.*.position.h' => ['required', 'integer', 'min:1'],
            'filters' => ['nullable', 'array'],
        ];
    }
}
