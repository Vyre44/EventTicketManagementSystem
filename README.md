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
