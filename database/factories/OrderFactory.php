<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),   // otomatik user üretir
            'event_id' => \App\Models\Event::factory(), // otomatik event üretir
            'total_amount' => $this->faker->randomFloat(2, 100, 1000),
            'status' => \App\Enums\OrderStatus::PAID,
        ];
    }
}
