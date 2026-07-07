<?php

namespace SGCart\CrmTickets\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxAttachments = config('crm-tickets.max_attachments_per_comment', 3);

        return [
            'body' => ['required', 'string'],
            'images' => ['nullable', 'array', "max:{$maxAttachments}"],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }
}
