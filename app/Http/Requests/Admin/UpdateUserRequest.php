<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use App\Enums\UserRole;

/**
 * UpdateUserRequest - Kullanıcı güncelleme validasyonu
 * 
 * Controller: Admin/UserController@update
 * Admin tarafından kullanıcı bilgileri (ad, email, rol) güncellemesi
 */
class UpdateUserRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        $userId = $this->route('user')->id ?? null;
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'role' => ['sometimes', new Enum(UserRole::class)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }
}
