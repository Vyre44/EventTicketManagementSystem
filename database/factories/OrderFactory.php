<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
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
            'status' => OrderStatus::PENDING->value,
            'paid_at' => null,
            'cancelled_at' => null,
            'refunded_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => OrderStatus::PAID->value,
            'paid_at' => now()->subDays(2),
            'cancelled_at' => null,
            'refunded_at' => null,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => OrderStatus::PENDING->value,
            'paid_at' => null,
            'cancelled_at' => null,
            'refunded_at' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => OrderStatus::CANCELLED->value,
            'paid_at' => null,
            'cancelled_at' => now()->subDays(3),
            'refunded_at' => null,
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn () => [
            'status' => OrderStatus::REFUNDED->value,
            'paid_at' => now()->subDays(5),
            'cancelled_at' => null,
            'refunded_at' => now()->subDays(2),
        ]);
    }
}
