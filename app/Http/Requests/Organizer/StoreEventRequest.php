<?php

namespace App\Http\Requests\Organizer;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreEventRequest: Organizer için etkinlik oluşturma isteği validasyonu.
 */
class StoreEventRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date'],
            'end_time' => ['nullable', 'date', 'after_or_equal:start_time'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            // organizer_id asla request'ten alınmaz
        ];
    }

    public function messages()
    {
        return [
            'cover_image.max' => 'Kapak görseli en fazla 2MB olabilir.',
            'cover_image.image' => 'Kapak görseli geçerli bir resim dosyası olmalıdır.',
            'cover_image.mimes' => 'Kapak görseli JPG, JPEG veya PNG formatında olmalıdır.',
        ];
    }
}
