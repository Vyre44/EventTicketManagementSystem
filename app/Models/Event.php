<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }
    protected $fillable = [
    'title',
    'description',
    'start_time',
    'end_time',
    'organizer_id',
];
}
