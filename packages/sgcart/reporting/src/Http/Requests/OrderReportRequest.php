<?php

namespace SGCart\Reporting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderReportRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
