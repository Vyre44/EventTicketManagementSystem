# Docker ile Laravel Kurulumu ve Migration Tablo Kontrolü

## Servisleri Başlatma

1. Terminalde proje dizinine gelin.
2. Servisleri başlatmak için:
   ```
   docker-compose up -d
   ```

## Laravel Kurulumu (İlk Defa)

App konteynerinde aşağıdaki komutları çalıştırın:

```
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
```

## Migration Tablolarını Görüntüleme

Mevcut migration ile oluşturulan tabloları görmek için:

```
docker-compose exec app php artisan migrate:status
```

Veritabanındaki tüm tabloları görmek için (psql ile):

```
docker-compose exec db psql -U laravel -d laravel -c "\dt"
```

## Uygulamaya Erişim

Tarayıcıdan: http://localhost:8080

---
Tüm adımlar ve komutlar bu dosyada güncel tutulmaktadır.


## Notlar
- .env dosyası PostgreSQL için ayarlanmıştır.
- Geliştirme için dosya değişiklikleri otomatik olarak konteynıra yansır.
- Sorun yaşarsanız `docker compose logs` ile hata detaylarını görebilirsiniz.

# Event Ticket Management System - Detailed Report

## 1️⃣ Kullanıcı Yönetimi
- Auth işlemleri → app/Http/Controllers/AuthController.php
- Rol ENUM → app/Enums/UserRole.php
- Role Middleware → app/Http/Middleware/RoleMiddleware.php
- Event Owner Kontrolü → app/Http/Middleware/EventOwnerMiddleware.php
- Route Gruplama → routes/web.php

## 2️⃣ Etkinlik Yönetimi
- Admin CRUD → app/Http/Controllers/Admin/EventController.php
- Organizer CRUD → app/Http/Controllers/Organizer/EventController.php
- Migration → database/migrations/2026_01_28_132907_create_events_table.php
- Enum Durum → app/Enums/EventStatus.php
- View Dosyaları → resources/views/admin/events/*

## 3️⃣ Bilet Yönetimi
- Ticket Model → app/Models/Ticket.php
- TicketType Model → app/Models/TicketType.php
- Admin TicketController → app/Http/Controllers/Admin/TicketController.php
- Organizer TicketController → app/Http/Controllers/Organizer/TicketController.php
- Ticket Migration → database/migrations/2026_01_29_131435_create_tickets_table.php

## 4️⃣ Sipariş ve Ödeme Akışı
- Order Model → app/Models/Order.php
- OrderStatus Enum → app/Enums/OrderStatus.php
- Attendee OrderController → app/Http/Controllers/Attendee/OrderController.php
- Order Migration → database/migrations/2026_01_29_131420_create_orders_table.php
- Refund Timestamp Migration → database/migrations/2026_02_06_120001_add_refund_timestamps_to_orders_table.php

## 5️⃣ Check-in Sistemi
- Admin CheckIn → app/Http/Controllers/Admin/CheckInController.php
- Organizer CheckIn → app/Http/Controllers/Organizer/CheckInController.php
- Race Condition Koruması → lockForUpdate() kullanımı
- AJAX İşlemleri → resources/js/admin-tickets.js

## 6️⃣ Raporlama ve Dashboard
- Admin Dashboard → app/Http/Controllers/Admin/DashboardController.php
- Raporlama → app/Http/Controllers/Admin/ReportController.php
- CSV Export → ReportController içerisinde
- Dashboard View → resources/views/admin/dashboard.blade.php

