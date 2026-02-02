<?php

namespace App\Http\Requests\Admin;

use App\Enums\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_type_id' => ['required', 'exists:ticket_types,id'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'code' => ['required', 'string', 'max:255', 'unique:tickets,code'],
            'status' => ['required', Rule::in(array_column(TicketStatus::cases(), 'value'))],
        ];
    }
}
