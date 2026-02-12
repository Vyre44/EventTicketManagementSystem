<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        $event = \App\Models\Event::factory();
        return [
            'order_id' => \App\Models\Order::factory()->for($event),        // order üret
            'ticket_type_id' => \App\Models\TicketType::factory()->for($event), 
            'code' => strtoupper(Str::random(8)),
            'status' => TicketStatus::ACTIVE->value,
            'checked_in_at' => null,
        ];
    }

    public function forSameEvent(): static
    {
        return $this->afterMaking(function (Ticket $ticket) {
        })->afterCreating(function (Ticket $ticket) {
            // order'ın event'ine göre ticket type üret
            $order = $ticket->order()->first();
            $tt = \App\Models\TicketType::factory()->create(['event_id' => $order->event_id]);

            $ticket->ticket_type_id = $tt->id;
            $ticket->save();
        });
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => TicketStatus::ACTIVE->value,
            'checked_in_at' => null,
        ]);
    }

    public function checkedIn(): static
    {
        return $this->state(fn () => [
            'status' => TicketStatus::CHECKED_IN->value,
            'checked_in_at' => now()->subHours(2),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => TicketStatus::CANCELLED->value,
            'checked_in_at' => null,
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn () => [
            'status' => TicketStatus::REFUNDED->value,
            'checked_in_at' => null,
        ]);
    }
}
