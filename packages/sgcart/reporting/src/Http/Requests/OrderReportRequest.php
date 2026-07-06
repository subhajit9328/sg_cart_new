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
            'search'    => ['nullable', 'string'],
            'sort_by'   => ['nullable', 'string', 'in:created_at,total,discount,tax,shipping_charge,net_amount,total_spent,total_orders,aov,last_order_date,units_sold,cancelled_units,total_revenue,reviews_count,total_sales,items_count'],
            'sort_dir'  => ['nullable', 'string', 'in:asc,desc'],
            'sort_order' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
