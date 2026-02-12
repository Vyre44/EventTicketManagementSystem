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
        return [
            'order_id' => \App\Models\Order::factory(),        // order üret
            'ticket_type_id' => \App\Models\TicketType::factory(), 
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
}
