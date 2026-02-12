<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\User;
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
            'organizer_id' => User::factory()->state([
                'role' => UserRole::ORGANIZER->value,
            ]),
            'status' => EventStatus::PUBLISHED->value,
        ];
    }

    public function publishedFuture(): static
    {
        return $this->state(fn () => [
            'status' => EventStatus::PUBLISHED->value,
            'start_time' => now()->addDays(5),
            'end_time' => now()->addDays(5)->addHours(3),
        ]);
    }

    public function draftFuture(): static
    {
        return $this->state(fn () => [
            'status' => EventStatus::DRAFT->value,
            'start_time' => now()->addDays(10),
            'end_time' => now()->addDays(10)->addHours(3),
        ]);
    }

    public function endedPast(): static
    {
        return $this->state(fn () => [
            'status' => EventStatus::ENDED->value,
            'start_time' => now()->subDays(10),
            'end_time' => now()->subDays(10)->addHours(3),
        ]);
    }
}
