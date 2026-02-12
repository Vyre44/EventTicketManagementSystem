<?php

namespace Database\Seeders;

use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1) Kullanicilar
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $organizers = collect();
        for ($i = 1; $i <= 3; $i++) {
            $organizers->push(User::factory()->organizer()->create([
                'name' => "Organizer {$i}",
                'email' => "organizer{$i}@example.com",
                'password' => Hash::make('password'),
            ]));
        }

        $attendees = collect();
        for ($i = 1; $i <= 15; $i++) {
            $attendees->push(User::factory()->attendee()->create([
                'name' => "Attendee {$i}",
                'email' => "attendee{$i}@example.com",
                'password' => Hash::make('password'),
            ]));
        }

        // 2) Organizer event'leri (15 event)
        $events = collect();
        foreach ($organizers as $index => $organizer) {
            if ($index < 2) {
                $events = $events->merge(Event::factory()->count(4)->publishedFuture()->create([
                    'organizer_id' => $organizer->id,
                ]));
                $events = $events->merge(Event::factory()->count(1)->draftFuture()->create([
                    'organizer_id' => $organizer->id,
                ]));
                continue;
            }

            $events = $events->merge(Event::factory()->count(3)->publishedFuture()->create([
                'organizer_id' => $organizer->id,
            ]));
            $events = $events->merge(Event::factory()->count(1)->draftFuture()->create([
                'organizer_id' => $organizer->id,
            ]));
            $events = $events->merge(Event::factory()->count(1)->endedPast()->create([
                'organizer_id' => $organizer->id,
            ]));
        }

        // 3) Ticket type'lar (her event icin 3 adet)
        $ticketTypes = collect();
        $typeTemplates = [
            ['name' => 'Standart', 'price' => 100.00],
            ['name' => 'VIP', 'price' => 200.00],
            ['name' => 'Ogrenci', 'price' => 60.00],
        ];

        foreach ($events as $event) {
            foreach ($typeTemplates as $idx => $template) {
                $totalQuantity = random_int(50, 200);
                $remainingQuantity = $totalQuantity;
                $saleStart = null;
                $saleEnd = null;

                if ($event->status === EventStatus::PUBLISHED->value) {
                    if ($idx === 0) {
                        $saleStart = now()->subDays(15);
                        $saleEnd = now()->addDays(15);
                        $remainingQuantity = max($totalQuantity - random_int(5, min(30, $totalQuantity)), 0);
                    } elseif ($idx === 1) {
                        $saleStart = now()->subDays(30);
                        $saleEnd = now()->subDays(1);
                    } else {
                        $saleStart = now()->addDays(3);
                        $saleEnd = now()->addDays(20);
                    }
                } elseif ($event->status === EventStatus::ENDED->value) {
                    $saleStart = now()->subDays(30);
                    $saleEnd = now()->subDays(5);
                } else {
                    $saleStart = now()->addDays(7);
                    $saleEnd = now()->addDays(30);
                }

                $ticketTypes->push(TicketType::factory()->create([
                    'event_id' => $event->id,
                    'name' => $template['name'],
                    'price' => $template['price'],
                    'total_quantity' => $totalQuantity,
                    'remaining_quantity' => $remainingQuantity,
                    'sale_start' => $saleStart,
                    'sale_end' => $saleEnd,
                ]));
            }
        }

        // 4) Order + Ticket uretimi
        $publishedEvents = $events->where('status', EventStatus::PUBLISHED->value)->values();
        $ticketTypesByEvent = $ticketTypes->groupBy('event_id');

        $createOrders = function (int $count, string $status) use ($attendees, $publishedEvents, $ticketTypesByEvent) {
            for ($i = 0; $i < $count; $i++) {
                $attendee = $attendees->random();
                $event = $publishedEvents->random();
                $eventTicketTypes = $ticketTypesByEvent->get($event->id, collect());
                if ($eventTicketTypes->isEmpty()) {
                    continue;
                }

                $orderFactory = Order::factory();
                if ($status === OrderStatus::PAID->value) {
                    $orderFactory = $orderFactory->paid();
                } elseif ($status === OrderStatus::PENDING->value) {
                    $orderFactory = $orderFactory->pending();
                } elseif ($status === OrderStatus::CANCELLED->value) {
                    $orderFactory = $orderFactory->cancelled();
                } else {
                    $orderFactory = $orderFactory->refunded();
                }

                $order = $orderFactory->create([
                    'user_id' => $attendee->id,
                    'event_id' => $event->id,
                ]);

                $ticketCount = random_int(1, 5);
                $totalAmount = 0.0;

                for ($t = 0; $t < $ticketCount; $t++) {
                    $ticketType = $eventTicketTypes->random();
                    $totalAmount += (float) $ticketType->price;

                    $ticketFactory = Ticket::factory();
                    if ($status === OrderStatus::PAID->value) {
                        $ticketFactory = random_int(1, 10) <= 2
                            ? $ticketFactory->checkedIn()
                            : $ticketFactory->active();
                    } elseif ($status === OrderStatus::PENDING->value) {
                        $ticketFactory = $ticketFactory->active();
                    } elseif ($status === OrderStatus::CANCELLED->value) {
                        $ticketFactory = $ticketFactory->cancelled();
                    } else {
                        $ticketFactory = $ticketFactory->refunded();
                    }

                    $ticketFactory->create([
                        'order_id' => $order->id,
                        'ticket_type_id' => $ticketType->id,
                    ]);
                }

                $order->update([
                    'total_amount' => $totalAmount,
                ]);
            }
        };

        $createOrders(30, OrderStatus::PAID->value);
        $createOrders(15, OrderStatus::PENDING->value);
        $createOrders(10, OrderStatus::CANCELLED->value);
        $createOrders(10, OrderStatus::REFUNDED->value);

        // 5) remaining_quantity duzelt
        foreach (TicketType::query()->get() as $ticketType) {
            $soldCount = Ticket::query()
                ->where('ticket_type_id', $ticketType->id)
                ->whereIn('status', [
                    TicketStatus::ACTIVE->value,
                    TicketStatus::CHECKED_IN->value,
                ])
                ->count();

            $remaining = $ticketType->total_quantity - $soldCount;
            if ($remaining < 0) {
                $remaining = 0;
            }

            $ticketType->update([
                'remaining_quantity' => $remaining,
            ]);
        }
    }
}
