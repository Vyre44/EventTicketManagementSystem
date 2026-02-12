# Etkinlik Biletleme Yönetim Sistemi

Laravel 11 tabanlı modern etkinlik biletleme ve yönetim platformu. Rol bazlı erişim kontrolü (Admin/Organizer/Attendee), gerçek zamanlı check-in sistemi ve güvenli sipariş yönetimi sunar.

## 🚀 Hızlı Başlangıç

### Gereksinimler
- Docker & Docker Compose
- PHP 8.2+
- PostgreSQL
- Composer
- Node.js & NPM

### Kurulum

1. **Servisleri Başlatma**
   ```bash
   docker-compose up -d
   ```

2. **Laravel Kurulumu (İlk Defa)**
   ```bash
   docker-compose exec app composer install
   docker-compose exec app php artisan key:generate
   docker-compose exec app php artisan migrate
   docker-compose exec app php artisan storage:link
   ```

3. **Frontend Asset'leri**
   ```bash
   npm install
   npm run build
   ```

4. **Uygulamaya Erişim**
   - **Ana Sayfa:** http://localhost:8080
   - **Admin Panel:** http://localhost:8080/admin
   - **Organizer Panel:** http://localhost:8080/organizer

### Veritabanı Kontrolü

Migration durumu:
```bash
docker-compose exec app php artisan migrate:status
```

Tüm tabloları görüntüleme:
```bash
docker-compose exec db psql -U laravel -d laravel -c "\dt"
```

---

# 📋 İSTER – DOSYA YOLU EŞLEŞME RAPORU

## 1️⃣ KULLANICI YÖNETİMİ

| # | İster | Dosya Yolu | Kanıt | Açıklama |
|---|-------|-----------|-------|----------|
| 1 | Kullanıcı kayıt işlemi | `app/Http/Controllers/AuthController.php` | L:60-73 `register()` | Yeni kullanıcı oluşturma, varsayılan rol: ATTENDEE |
| 2 | Kullanıcı giriş işlemi | `app/Http/Controllers/AuthController.php` | L:25-48 `login()` | Email/şifre doğrulama, session başlatma |
| 3 | Çıkış işlemi | `app/Http/Controllers/AuthController.php` | L:79-84 `logout()` | Session sonlandırma |
| 4 | Şifre hashleme | `app/Http/Controllers/AuthController.php` | L:70 `Hash::make()` | Güvenli şifre saklama |
| 5 | Rol yapısı | `app/Enums/UserRole.php` | L:13-123 | ADMIN, ORGANIZER, ATTENDEE (PHP 8.1 Enum) |
| 6 | Rol casting | `app/Models/User.php` | L:39 `'role' => UserRole::class` | Otomatik enum dönüşümü |
| 7 | Rol bazlı middleware | `app/Http/Middleware/RoleMiddleware.php` | L:17-226 | Route seviyesinde yetkilendirme |
| 8 | Organizatör sahiplik kontrolü | `app/Http/Middleware/EventOwnerMiddleware.php` | L:12-52 | `organizer_id === auth()->id()` |
| 9 | Route rol gruplama | `routes/web.php` | L:32, 100 | `middleware(['auth','role:admin'])` |
| 10 | Rol bazlı yönlendirme | `routes/web.php` | L:77-93 | Admin/Organizer/Attendee dashboard'a |

---

## 2️⃣ ETKİNLİK YÖNETİMİ

| # | İster | Dosya Yolu | Kanıt | Açıklama |
|---|-------|-----------|-------|----------|
| 1 | Admin etkinlik CRUD | `app/Http/Controllers/Admin/EventController.php` | L:17-112 | Tüm etkinlikleri yönetme yetkisi |
| 2 | Organizer etkinlik CRUD | `app/Http/Controllers/Organizer/EventController.php` | L:21-121 | Sadece kendi etkinliklerini düzenleme |
| 3 | Attendee etkinlik listesi | `app/Http/Controllers/Attendee/EventController.php` | L:26-45 | Sadece PUBLISHED etkinlikleri görme |
| 4 | Event modeli | `app/Models/Event.php` | L:25-292 | `belongsTo(User)`, `hasMany(TicketType)` |
| 5 | Event migration | `database/migrations/2026_01_28_132907_create_events_table.php` | L:14-26 | title, description, start_time, organizer_id |
| 6 | Event status enum | `app/Enums/EventStatus.php` | L:49-54 | PUBLISHED, DRAFT, ENDED |
| 7 | Kapak görseli yükleme | `app/Http/Controllers/Admin/EventController.php` | L:56-63 | `Storage::store('events', 'public')` |
| 8 | Görsel silme | `app/Http/Controllers/Organizer/EventController.php` | L:89-95 | Güncelleme sırasında eski görseli temizleme |
| 9 | Etkinlik arama | `app/Http/Controllers/Organizer/EventController.php` | L:33-42 | Title ve location'a göre LIKE sorgusu |
| 10 | Konum bilgisi | `database/migrations/2026_02_06_120002_add_location_to_events_table.php` | ✅ | location alanı |
| 11 | Etkinlik filtreleme | `app/Http/Controllers/Organizer/EventController.php` | L:27-29 | Status'a göre filtreleme |

---

## 3️⃣ BİLET TİPİ YÖNETİMİ

| # | İster | Dosya Yolu | Kanıt | Açıklama |
|---|-------|-----------|-------|----------|
| 1 | TicketType modeli | `app/Models/TicketType.php` | ✅ | `belongsTo(Event)`, fiyat ve stok yönetimi |
| 2 | TicketType migration | `database/migrations/2026_01_29_131410_create_ticket_types_table.php` | ✅ | name, price, total_quantity, remaining_quantity |
| 3 | Admin TicketType CRUD | `app/Http/Controllers/Admin/TicketTypeController.php` | ✅ | Tüm bilet tiplerini yönetme |
| 4 | Organizer TicketType CRUD | `app/Http/Controllers/Organizer/TicketTypeController.php` | ✅ | Sadece kendi etkinliklerinin bilet tipleri |
| 5 | Satış penceresi | `app/Models/TicketType.php` | ✅ | sale_start, sale_end alanları |
| 6 | Satış zamanı kontrolü | `app/Http/Controllers/Attendee/OrderController.php` | L:85-92 | Satış penceresinde mi? |
| 7 | Stok kontrolü | `app/Http/Controllers/Attendee/OrderController.php` | L:93-96 | `remaining_quantity >= quantity` |
| 8 | Stok düşürme | `app/Http/Controllers/Attendee/OrderController.php` | L:100-103 | `decrement('remaining_quantity', $quantity)` |

---

## 4️⃣ SİPARİŞ (ORDER) YÖNETİMİ

| # | İster | Dosya Yolu | Kanıt | Açıklama |
|---|-------|-----------|-------|----------|
| 1 | Order modeli | `app/Models/Order.php` | L:17-175 | `belongsTo(User)`, `hasMany(Ticket)` |
| 2 | Order migration | `database/migrations/2026_01_29_131420_create_orders_table.php` | L:15-40 | user_id, event_id, total_amount, status |
| 3 | Order status enum | `app/Enums/OrderStatus.php` | L:53-73 | PENDING, PAID, CANCELLED, REFUNDED |
| 4 | Satın alma işlemi | `app/Http/Controllers/Attendee/OrderController.php` | L:35-130 | `buy()` - sipariş oluşturma |
| 5 | Transaction koruması | `app/Http/Controllers/Attendee/OrderController.php` | L:63-130 | `DB::transaction()` atomik işlem |
| 6 | Stok kilitleme | `app/Http/Controllers/Attendee/OrderController.php` | L:68-69 | `lockForUpdate()` race condition önleme |
| 7 | Etkinlik tarihi kontrolü | `app/Http/Controllers/Attendee/OrderController.php` | L:48-56 | Geçmiş etkinliğe satış engeli |
| 8 | Event status kontrolü | `app/Http/Controllers/Attendee/OrderController.php` | L:37-45 | Sadece PUBLISHED etkinlikler |
| 9 | İptal/İade timestamp'leri | `database/migrations/2026_02_06_120001_add_refund_timestamps_to_orders_table.php` | ✅ | cancelled_at, refunded_at |
| 10 | Sipariş listeleme (Admin) | `app/Http/Controllers/Admin/OrderController.php` | L:13-43 | Tüm siparişler, filtreleme |
| 11 | Sipariş listeleme (Attendee) | `app/Http/Controllers/Attendee/OrderController.php` | L:150-180 | Kullanıcının kendi siparişleri |

> 📌 **Not:** Gerçek ödeme gateway entegrasyonu yoktur. Sipariş durumu sistem içinde manuel güncellenmektedir.

---

## 5️⃣ BİLET (TICKET) YÖNETİMİ

| # | İster | Dosya Yolu | Kanıt | Açıklama |
|---|-------|-----------|-------|----------|
| 1 | Ticket modeli | `app/Models/Ticket.php` | L:16-241 | `belongsTo(Order)`, `belongsTo(TicketType)` |
| 2 | Ticket migration | `database/migrations/2026_01_29_131435_create_tickets_table.php` | ✅ | order_id, ticket_type_id, code, status |
| 3 | Ticket status enum | `app/Enums/TicketStatus.php` | L:12-20 | ACTIVE, CHECKED_IN, CANCELLED, REFUNDED |
| 4 | Otomatik bilet oluşturma | `app/Http/Controllers/Attendee/OrderController.php` | L:110-118 | Sipariş sonrası ticket generate |
| 5 | Benzersiz bilet kodu | `app/Http/Controllers/Attendee/OrderController.php` | L:112 | `Str::upper(Str::random(8))` |
| 6 | Admin ticket listeleme | `app/Http/Controllers/Admin/TicketController.php` | L:30-62 | Tüm biletler, çoklu filtre |
| 7 | Organizer ticket listeleme | `app/Http/Controllers/Organizer/TicketController.php` | L:29-62 | Sadece kendi etkinliklerinin biletleri |
| 8 | Bilet kodu araması | `app/Http/Controllers/Admin/TicketController.php` | L:35-38 | `where('code', 'like', "%$q%")` |
| 9 | Etkinlik adına göre arama | `app/Http/Controllers/Admin/TicketController.php` | L:46-53 | `whereHas('ticketType.event')` |
| 10 | Email araması | `app/Http/Controllers/Admin/TicketController.php` | L:55-61 | `whereHas('order.user')` |
| 11 | Order PAID kontrolü | `app/Http/Controllers/Admin/TicketController.php` | L:455-472 | Check-in öncesi ödeme doğrulama |
| 12 | Bilet iptal | `app/Http/Controllers/Admin/TicketController.php` | L:494-592 | `cancelTicket()` metodu |

---

## 6️⃣ CHECK-IN SİSTEMİ

| # | İster | Dosya Yolu | Kanıt | Açıklama |
|---|-------|-----------|-------|----------|
| 1 | Organizer check-in | `app/Http/Controllers/Organizer/CheckInController.php` | L:13-59 | QR/Barcode ile giriş kontrolü |
| 2 | Admin check-in | `app/Http/Controllers/Admin/CheckInController.php` | L:10-101 | Tüm etkinlikler için yetkili |
| 3 | Check-in form | `resources/views/checkin/form.blade.php` | ✅ | Bilet kodu girişi |
| 4 | Check-in request validation | `app/Http/Requests/Organizer/CheckInRequest.php` | ✅ | Kod formatı doğrulama |
| 5 | Double check-in önleme | `app/Http/Controllers/Organizer/CheckInController.php` | L:49-54 | Status = CHECKED_IN kontrolü |
| 6 | Race condition koruması | `app/Http/Controllers/Organizer/CheckInController.php` | L:35-37 | `DB::transaction()` + `lockForUpdate()` |
| 7 | Event ownership kontrolü | `app/Http/Controllers/Organizer/CheckInController.php` | L:40-49 | Etkinlik sahipliği doğrulama |
| 8 | Check-in zamanı | `app/Models/Ticket.php` | ✅ | `checked_in_at` timestamp |
| 9 | AJAX check-in (Admin) | `resources/js/admin-tickets.js` | L:1-679 | POST request ile check-in |
| 10 | AJAX check-in (Organizer) | `resources/js/organizer-tickets.js` | L:1-494 | POST request ile check-in |
| 11 | Check-in undo | `app/Http/Controllers/Admin/TicketController.php` | L:473-493 | CHECKED_IN → ACTIVE |
| 12 | Order PAID guard | `app/Http/Controllers/Organizer/TicketController.php` | L:238-252 | PENDING ödeme check-in engeli |

> 📌 **Kritik:** PENDING veya CANCELLED siparişlerdeki biletler check-in edilemez.

---

## 7️⃣ YÖNETİCİ PANELİ

| # | İster | Dosya Yolu | Kanıt | Açıklama |
|---|-------|-----------|-------|----------|
| 1 | Admin Dashboard | `app/Http/Controllers/Admin/DashboardController.php` | L:21-215 | İstatistik aggregation |
| 2 | Toplam etkinlik sayısı | `app/Http/Controllers/Admin/DashboardController.php` | ✅ | `Event::count()` |
| 3 | Toplam sipariş sayısı | `app/Http/Controllers/Admin/DashboardController.php` | ✅ | `Order::count()` |
| 4 | Toplam bilet sayısı | `app/Http/Controllers/Admin/DashboardController.php` | ✅ | `Ticket::count()` |
| 5 | Check-in edilen biletler | `app/Http/Controllers/Admin/DashboardController.php` | ✅ | `where('status', TicketStatus::CHECKED_IN)` |
| 6 | Ödenen siparişler | `app/Http/Controllers/Admin/DashboardController.php` | ✅ | `where('status', OrderStatus::PAID)` |
| 7 | Satış raporu | `app/Http/Controllers/Admin/ReportController.php` | L:41-685 | Etkinlik bazlı satış verileri |
| 8 | CSV export | `app/Http/Controllers/Admin/ReportController.php` | L:140-200 | Bilet listesi indirme |
| 9 | Bilet raporu | `app/Http/Controllers/Admin/ReportController.php` | L:100-140 | Filtreleme ve pagination |
| 10 | Admin route prefix | `routes/web.php` | L:32-65 | `/admin/*` route grubu |
| 11 | Organizer route prefix | `routes/web.php` | L:100-145 | `/organizer/*` route grubu |
| 12 | Dashboard view | `resources/views/admin/dashboard.blade.php` | ✅ | İstatistik kartları |

---

## 8️⃣ FRONTEND (BLADE VIEWS)

| # | İster | Dosya Yolu | Açıklama |
|---|-------|-----------|----------|
| 1 | Admin layout | `resources/views/layouts/app.blade.php` | Ana şablon (navbar, footer) |
| 2 | Admin etkinlik listesi | `resources/views/admin/events/index.blade.php` | Bootstrap 5 tablo |
| 3 | Admin bilet listesi | `resources/views/admin/tickets/index.blade.php` | Filtreleme, check-in butonları |
| 4 | Admin sipariş listesi | `resources/views/admin/orders/index.blade.php` | Status badge gösterimi |
| 5 | Organizer etkinlik listesi | `resources/views/organizer/events/index.blade.php` | Sadece kendi etkinlikleri |
| 6 | Organizer bilet listesi | `resources/views/organizer/tickets/index.blade.php` | Check-in interface |
| 7 | Attendee etkinlik listesi | `resources/views/attendee/events/index.blade.php` | PUBLISHED etkinlikler |
| 8 | Attendee sipariş görünümü | `resources/views/attendee/orders/show.blade.php` | Bilet detayları |
| 9 | Login formu | `resources/views/auth/login.blade.php` | Email/şifre girişi |
| 10 | Register formu | `resources/views/auth/register.blade.php` | Yeni kullanıcı kaydı |
| 11 | Bootstrap 5 import | `resources/css/app.css` | L:2-5 Bootstrap CSS + Icons |

---

## 9️⃣ TEKNİK ALTYAPI

| # | Özellik | Dosya/Konum | Açıklama |
|---|---------|-------------|----------|
| 1 | Database Factories | `database/factories/` | Test verileri üretimi |
| 2 | Route Model Binding | `routes/web.php` | `{event}`, `{order}`, `{ticket}` otomatik yükleme |
| 3 | Eager Loading | Controller'lar | `with(['relation'])` N+1 önleme |
| 4 | Transaction Management | OrderController, TicketController | `DB::transaction()` |
| 5 | Pessimistic Locking | OrderController | `lockForUpdate()` |
| 6 | File Upload | EventController | `Storage::store()` |
| 7 | AJAX Operations | `resources/js/*.js` | Fetch API kullanımı |
| 8 | CSRF Protection | Laravel default | `@csrf` token |
| 9 | Query Scoping | Model'lar | `whereHas()` nested queries |
| 10 | Pagination | Controller'lar | `paginate(20)->withQueryString()` |

---

# 🏗️ TEKNİK MİMARİ

## MVC Yapısı

- **Models** → `app/Models/` (User, Event, Order, Ticket, TicketType)
- **Controllers** → `app/Http/Controllers/` (Admin, Organizer, Attendee)
- **Views** → `resources/views/` (admin, organizer, attendee, auth, layouts)

## Middleware

- **Rol Kontrolü** → `app/Http/Middleware/RoleMiddleware.php`
- **Etkinlik Sahipliği** → `app/Http/Middleware/EventOwnerMiddleware.php`

## Enum Kullanımı (PHP 8.1+)

- **Kullanıcı Rolleri** → `app/Enums/UserRole.php` (ADMIN, ORGANIZER, ATTENDEE)
- **Etkinlik Durumu** → `app/Enums/EventStatus.php` (PUBLISHED, DRAFT, ENDED)
- **Sipariş Durumu** → `app/Enums/OrderStatus.php` (PENDING, PAID, CANCELLED, REFUNDED)
- **Bilet Durumu** → `app/Enums/TicketStatus.php` (ACTIVE, CHECKED_IN, CANCELLED, REFUNDED)

## Request Validation (Form Requests)

- **Admin** → `app/Http/Requests/Admin/` (StoreEventRequest, UpdateEventRequest)
- **Organizer** → `app/Http/Requests/Organizer/` (StoreEventRequest, CheckInRequest)
- **Attendee** → `app/Http/Requests/Attendee/` (BuyTicketRequest)

## Route Model Binding

Otomatik model yükleme → `routes/web.php`
- `{event}` → Event modeli
- `{order}` → Order modeli
- `{ticket}` → Ticket modeli
- `{user}` → User modeli

## Dosya Yükleme (Storage)

- **Kapak Görseli** → `app/Http/Controllers/Admin/EventController.php`
- **Disk** → `public` (storage/app/public/events/)
- **Yöntem** → `Storage::disk('public')->store()`

## Bootstrap 5 Framework

- **Tüm Views** → `resources/views/**/*.blade.php`
- **CSS Import** → `resources/css/app.css`
- **Grid System, Cards, Forms, Tables, Buttons, Badges**

---

## 📝 Notlar

- `.env` dosyası PostgreSQL için yapılandırılmıştır
- Geliştirme için dosya değişiklikleri otomatik olarak konteynıra yansır
- Hata logları için: `docker-compose logs -f`
- Gerçek ödeme entegrasyonu yoktur (demo amaçlı)

## 📄 Lisans

Bu proje eğitim amaçlı geliştirilmiştir.

