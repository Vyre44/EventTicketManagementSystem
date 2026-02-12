<?php

namespace Database\Factories;

use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketTypeFactory extends Factory
{
    protected $model = TicketType::class;

    public function definition(): array
    {
        $totalQuantity = $this->faker->numberBetween(10, 100);
        return [
            'event_id' => \App\Models\Event::factory(),
            'name' => $this->faker->word(),
            'price' => $this->faker->randomFloat(2, 50, 500),
            'total_quantity' => $totalQuantity,
            'remaining_quantity' => $totalQuantity,
        ];
    }
}
