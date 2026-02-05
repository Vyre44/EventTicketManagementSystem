<?php

namespace App\Http\Requests\Organizer;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'total_quantity' => ['required', 'integer', 'min:1'],
            'sale_start' => ['nullable', 'date'],
            'sale_end' => ['nullable', 'date', 'after_or_equal:sale_start'],
        ];
    }
}
