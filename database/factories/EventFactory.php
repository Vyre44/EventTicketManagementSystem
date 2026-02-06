<?php

namespace Database\Factories;

use App\Models\Event;
use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'start_time' => $this->faker->dateTimeBetween('+1 days', '+2 days'),
            'end_time' => $this->faker->dateTimeBetween('+2 days', '+3 days'),
            // Seeder'da organizer_id elle atanacak, burada factory zincirleme için null bırakıyoruz
            'organizer_id' => null,
            'status' => EventStatus::PUBLISHED->value,
        ];
    }
}
