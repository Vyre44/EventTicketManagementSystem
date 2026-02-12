<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreTicketTypeRequest - Bilet tipi oluşturma validasyonu
 * 
 * Controller: Admin/TicketTypeController@store, Organizer/TicketTypeController@store
 * Event'e bağlı bilet tipi tanımlama (VIP, Standard, ekonomik vb.)
 * Stok yönetimi: total_quantity ve remaining_quantity
 */
class StoreTicketTypeRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'event_id' => ['required', 'exists:events,id'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'total_quantity' => ['required', 'integer', 'min:0'],
        ];
    }
}
