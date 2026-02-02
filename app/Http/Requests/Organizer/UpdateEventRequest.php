<?php

namespace App\Http\Requests\Organizer;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdateEventRequest: Organizer için etkinlik güncelleme isteği validasyonu.
 */
class UpdateEventRequest extends FormRequest
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
                // organizer_id asla request'ten alınmaz
            ];
        }
    }
