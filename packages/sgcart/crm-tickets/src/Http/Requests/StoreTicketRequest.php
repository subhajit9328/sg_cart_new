<?php

namespace SGCart\CrmTickets\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use SGCart\CrmTickets\Enums\TicketPriority;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxAttachments = config('crm-tickets.max_attachments_per_comment', 3);

        return [
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['sometimes', Rule::enum(TicketPriority::class)],
            'ticketable_type' => ['required', 'string'],
            'ticketable_id' => ['required', 'integer'],
            'images' => ['nullable', 'array', "max:{$maxAttachments}"],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }
}
