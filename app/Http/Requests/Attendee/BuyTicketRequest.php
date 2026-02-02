<?php

namespace App\Http\Requests\Attendee;

use Illuminate\Foundation\Http\FormRequest;

class BuyTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ticket_types' => ['required', 'array', 'min:1'],
            'ticket_types.*' => ['required', 'integer', 'min:1', 'max:10'], // Her bilet tipi için max 10 adet
        ];
    }

    /**
     * Özel hata mesajları
     */
    public function messages(): array
    {
        return [
            'ticket_types.required' => 'En az bir bilet tipi seçmelisiniz.',
            'ticket_types.min' => 'En az bir bilet tipi seçmelisiniz.',
            'ticket_types.*.required' => 'Bilet adedi belirtilmelidir.',
            'ticket_types.*.integer' => 'Bilet adedi sayı olmalıdır.',
            'ticket_types.*.min' => 'Bilet adedi en az 1 olmalıdır.',
            'ticket_types.*.max' => 'Bir bilet tipinden en fazla 10 adet alabilirsiniz.',
        ];
    }
}
