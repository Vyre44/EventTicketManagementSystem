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
        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'role' => UserRole::ADMIN->value,
                'password' => bcrypt('admin123'),
            ]
        );

        // Organizer
        $organizer = User::firstOrCreate(
            ['email' => 'organizer@example.com'],
            [
                'name' => 'Organizer User',
                'role' => UserRole::ORGANIZER->value,
                'password' => bcrypt('organizer123'),
            ]
        );

        // Attendee
        $attendee = User::firstOrCreate(
            ['email' => 'attendee@example.com'],
            [
                'name' => 'Attendee User',
                'role' => UserRole::ATTENDEE->value,
                'password' => bcrypt('attendee123'),
            ]
        );

        // Basit event oluştur
        $event = \App\Models\Event::firstOrCreate(
            ['title' => 'Test Etkinlik'],
            [
                'organizer_id' => $organizer->id,
                'description' => 'Bu bir test etkinliğidir',
                'start_time' => now()->addDays(7),
                'end_time' => now()->addDays(7)->addHours(4),
                'status' => \App\Enums\EventStatus::PUBLISHED->value,
            ]
        );

        // Ticket type oluştur
        $ticketType = \App\Models\TicketType::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Standart Bilet'
            ],
            [
                'price' => 100.00,
                'total_quantity' => 50,
                'remaining_quantity' => 50,
            ]
        );

        // Order ve ticket oluştur
        $order = \App\Models\Order::firstOrCreate(
            [
                'user_id' => $attendee->id,
                'event_id' => $event->id,
            ],
            [
                'total_price' => 100.00,
                'status' => \App\Enums\OrderStatus::PAID->value,
            ]
        );

        \App\Models\Ticket::firstOrCreate(
            [
                'order_id' => $order->id,
                'ticket_type_id' => $ticketType->id,
            ],
            [
                'code' => 'TICKET-' . strtoupper(\Illuminate\Support\Str::random(8)),
                'status' => \App\Enums\TicketStatus::ACTIVE->value,
            ]
        );
    }
}
