<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ProfileUpdateRequest - Kullanıcı profili güncelleme validasyonu
 * 
 * Controller: ProfileController@update
 * Kullanıcı kendi profil bilgileri (ad, email) güncelleyebilir
 */
class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ];
    }
}
