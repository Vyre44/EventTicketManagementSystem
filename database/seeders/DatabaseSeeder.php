<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Admin
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => UserRole::ADMIN->value,
            'password' => bcrypt('admin123'),
        ]);

        // Organizer
        $organizer = User::factory()->create([
            'name' => 'Organizer User',
            'email' => 'organizer@example.com',
            'role' => UserRole::ORGANIZER->value,
            'password' => bcrypt('organizer123'),
        ]);

        // Attendee
        $attendee = User::factory()->create([
            'name' => 'Attendee User',
            'email' => 'attendee@example.com',
            'role' => UserRole::ATTENDEE->value,
            'password' => bcrypt('attendee123'),
        ]);

        // Organizer'a ait bir event ve ilişkili ticketType, order, ticket zincirleme oluştur
        $event = \App\Models\Event::factory()
            ->has(
                \App\Models\TicketType::factory()
                    ->count(2)
                    ->has(
                        \App\Models\Ticket::factory()
                            ->count(5)
                            ->state(function (array $attributes, \App\Models\TicketType $ticketType) use ($attendee) {
                                // Her ticket için attendee'ya ait bir order oluştur
                                $order = \App\Models\Order::factory()->create([
                                    'user_id' => $attendee->id,
                                    'event_id' => $ticketType->event_id,
                                    'status' => \App\Enums\OrderStatus::PAID,
                                ]);
                                return [
                                    'order_id' => $order->id,
                                    'ticket_type_id' => $ticketType->id,
                                ];
                            })
                    , 'tickets')
            , 'ticketTypes')
            ->create([
                'organizer_id' => $organizer->id,
            ]);
    }
}
