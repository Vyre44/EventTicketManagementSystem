<?php

namespace App\Http\Requests\Admin;

use App\Enums\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_type_id' => ['sometimes', 'exists:ticket_types,id'],
            'status' => ['sometimes', Rule::in(array_column(TicketStatus::cases(), 'value'))],
            'checked_in_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
        ];
    }
}
