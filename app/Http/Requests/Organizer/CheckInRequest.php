<?php

namespace App\Http\Requests\Organizer;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CheckInRequest: Organizer için check-in kodu validasyonu.
 */
class CheckInRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => ['required', 'string'],
        ];
    }
}
