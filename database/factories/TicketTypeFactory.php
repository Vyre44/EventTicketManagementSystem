<?php

namespace Database\Factories;

use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketTypeFactory extends Factory
{
    protected $model = TicketType::class;

    public function definition(): array
    {
        return [
            'event_id' => \App\Models\Event::factory(),
            'name' => $this->faker->word(),
            'price' => $this->faker->randomFloat(2, 50, 500),
            'total_quantity' => $this->faker->numberBetween(10, 100),
            'remaining_quantity' => $this->faker->numberBetween(0, 100),
        ];
    }
}
