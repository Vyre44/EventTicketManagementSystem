/**
 * AJAX Helper - Centralized Fetch Wrapper
 * 
 * Otomatik CSRF token injection (Laravel)
 * JSON request/response handling
 * Error handling ve user notifications
 * Promise-based async/await pattern
 */

/**
 * Fetch wrapper with CSRF + JSON handling
 * @returns {Promise} { success, message, data } or throws error
 */
async function ajaxRequest(url, method = 'POST', data = {}, options = {}) {
    // Fetch options with CSRF token
    const defaultOptions = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        ...options
    };

    /**
     * ADIM 2: Gövde Verisi Ekle (GET'te değil, POST/PUT/PATCH'te var)
     * 
     * GET: URL parametreleriyle veri gönderilir (body yok)
     * POST/PUT/PATCH: JSON body'de veri gönderilir
     * DELETE: Genelde body yok (sadece ID URL'de)
     */
    if (['POST', 'PUT', 'PATCH'].includes(method.toUpperCase())) {
        /**
         * JSON.stringify(): JS object'i JSON string'e çevir
         * Örnek:
         * { name: 'John', email: 'john@example.com' }
         * ->
         * '{"name":"John","email":"john@example.com"}'
         */
        defaultOptions.body = JSON.stringify(data);
    }

    try {
        /**
         * ADIM 3: İstemi Yap
         * 
         * fetch(): Tarayıcının built-in AJAX methodu
         * - Modern, promise-based
         * - XHR'den daha temiz
         */
        const response = await fetch(url, defaultOptions);
        
        /**
         * ADIM 4: Yanıtı Oku (JSON olarak)
         * 
         * response.json(): Response'u JSON'a parse et
         * Otomatik olarak string'den object'e çevir
         */
        const result = await response.json();

        /**
         * ADIM 5: HTTP Durumunu Kontrol Et
         * 
         * response.ok: Status 200-299 ise true
         * !response.ok: Status 400, 401, 403, 404, 500 vb. ise true (hata)
         */
        if (!response.ok) {
            /**
             * HATA YANITI
             * 
             * Sunucudan gelen hata:
             * - 400: Validasyon hatası (form errors)
             * - 401: Unauthorized (giriş yaptı mı?)
             * - 403: Forbidden (yetkisi var mı?)
             * - 404: Not found (kaynak yok mu?)
             * - 422: Unprocessable entity (validasyon)
             * - 500: Server error
             * 
             * Object oluştur ve fırlat (throw)
             */
            throw {
                status: response.status,
                message: result.message || 'Bir hata oluştu.',
                errors: result.errors || {}
            };
        }

        /**
         * ADIM 6: Başarılı Yanıt Döner
         * 
         * result: Sunucudan dönen veri
         * Örnek:
         * {
         *     success: true,
         *     message: "Bilet başarıyla check-in yapıldı",
         *     data: { ticket_id: 123, status: "checked_in" }
         * }
         */
        return result;
    } catch (error) {
        /**
         * ADIM 7: Hata Yönetimi
         * 
         * error.status varsa: Sunucu hatası (throw ettik)
         * error.status yoksa: Network hatası (fetch başarısız)
         */
        if (error.status) {
            // Sunucu hatası (zaten oluşturduk ve fırlatttık)
            throw error;
        }
        
        // Network hatası (internet bağlantısı yok, vb.)
        throw {
            status: 0,
            message: 'Bağlantı hatası. Lütfen tekrar deneyin.',
            errors: {}
        };
    }
}

/**
 * ============================================================
 * KULLANICI BİLDİRİMİ - Alert Göster
 * ============================================================
 * 
 * AÇIKLAMA:
 * Sayfanın tepesinde başarı/hata mesajı gösterir.
 * Belirtilen süre sonra otomatik olarak kaybolur.
 * 
 * PARAMETRELER:
 * @param {string} type - 'success' (yeşil) veya 'error' (kırmızı)
 * @param {string} message - Gösterilecek mesaj
 * @param {number} duration - Milisaniye cinsinden gösterim süresi (default: 5000)
 * 
 * KULLANIM:
 * showAlert('success', 'Bilet başarıyla check-in yapıldı!');
 * showAlert('error', 'Hata oluştu, tekrar deneyin.');
 * showAlert('success', 'İşlem tamamlandı', 3000); // 3 saniye
 * 
 * HTML GEREKSINIM:
 * <div id="ajax-alert-container"></div>
 * (Layout'ta bulunması gerekli)
 */
function showAlert(type, message, duration = 5000) {
    /**
     * ADIM 1: Alert Container'ını Bul
     * 
     * HTML'de önceden tanımlanmış olmalı:
     * <div id="ajax-alert-container"></div>
     * 
     * Eğer yoksa uyarı ver ve çık
     */
    const alertContainer = document.getElementById('ajax-alert-container');
    if (!alertContainer) {
        console.warn('Alert container bulunamadı!');
        return;
    }

    /**
     * ADIM 2: CSS Sınıfını Seç
     * 
     * type === 'success': Yeşil arka plan
     * type === 'error': Kırmızı arka plan
     */
    const alertClass = type === 'success' 
        ? 'bg-green-50 border-green-200 text-green-800' 
        : 'bg-red-50 border-red-200 text-red-800';
    
    /**
     * ADIM 3: İkon Seç
     * 
     * success: ✅
     * error: ❌
     */
    const icon = type === 'success' ? '✅' : '❌';

    /**
     * ADIM 4: Alert HTML'i Oluştur
     * 
     * Tailwind CSS ile stillendirilmiş:
     * - bg-*-50: Açık arka plan
     * - border: Sınır
     * - rounded-lg: Köşeleri yuvarlat
     * - p-4: İçeri boşluk
     * - flex: Yan yana yerleştirme
     * - justify-between: Uçlara dağıt (mesaj solda, X sağda)
     */
    const alertHTML = `
        <div class="ajax-alert ${alertClass} border rounded-lg p-4 mb-4 flex justify-between items-center">
            <span>${icon} ${message}</span>
            <button onclick="this.parentElement.remove()" class="ml-4 text-gray-600 hover:text-gray-800">×</button>
        </div>
    `;

    /**
     * ADIM 5: HTML'i Sayfaya Ekle
     * 
     * insertAdjacentHTML('beforeend', ...): Sonu'na ekle
     * - beforebegin: Dış elementin başından önce
     * - afterbegin: İçine, başa
     * - beforeend: İçine, sona (burada kullanıyoruz)
     * - afterend: Dış elementin sonundan sonra
     */
    alertContainer.insertAdjacentHTML('beforeend', alertHTML);

    /**
     * ADIM 6: Auto-remove (İlk alert'i belirtilen süre sonra kaldır)
     * 
     * duration > 0 ise: Otomatik kaldırma etkin
     * duration = 0 ise: Manuel kapatma (X butonunla)
     */
    if (duration > 0) {
        /**
         * setTimeout(): Belirtilen süre sonra kod çalıştır
         * 
         * duration (ms) * 1000 = saniye
         * Örnek: 5000 = 5 saniye sonra
         */
        setTimeout(() => {
            const alerts = alertContainer.querySelectorAll('.ajax-alert');
            if (alerts.length > 0) {
                /**
                 * İlk alert'i kaldır (FIFO - First In First Out)
                 * En eski mesaj önce kaybolur
                 */
                alerts[0].remove();
            }
        }, duration);
    }
}

/**
 * ============================================================
 * ALERT CONTAINER'INI TEMIZLE
 * ============================================================
 * 
 * AÇIKLAMA:
 * Sayfadaki tüm alert mesajlarını sil
 * 
 * KULLANIM:
 * clearAlerts(); // Tüm mesajları sil
 * 
 * NEREDEN ÇAĞRILIR:
 * - Sayfa değiştiğinde
 * - Modal kapatıldığında
 * - Yeni işlem başladığında
 */
function clearAlerts() {
    const alertContainer = document.getElementById('ajax-alert-container');
    if (alertContainer) {
        /**
         * innerHTML = '': Tüm içeriği sil
         * Tüm child element'ler kaldırılır
         */
        alertContainer.innerHTML = '';
    }
}

/**
 * Export functions to global scope (window)
 * 
 * ES6 module'ler varsayılan olarak local scope'ta çalışır.
 * Diğer dosyalardan (admin-tickets.js, organizer-tickets.js) 
 * bu fonksiyonları kullanabilmek için window nesnesine ekliyoruz.
 */
window.ajaxRequest = ajaxRequest;
window.showAlert = showAlert;
window.clearAlerts = clearAlerts;
