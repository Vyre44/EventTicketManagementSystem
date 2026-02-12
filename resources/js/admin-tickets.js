/**
 * ============================================================
 * ADMIN TİKETLER - AJAX HANDLER
 * ============================================================
 * 
 * AMAÇ:
 * Admin'in TÜM etkinliklerin biletlerini merkezi olarak yönetmesi
 * Organizatörlerin biletlerine de müdahale edebilme
 * 
 * OPERASYONLAR:
 * - Check-in: Bilet durumunu ACTIVE -> CHECKED_IN yap
 * - Undo Check-in: CHECKED_IN -> ACTIVE geri al
 * - Cancel: Bileti iptal et (ACTIVE -> CANCELLED)
 * 
 * ROUTE FARKI (Organizer'dan):
 * Admin routes:
 * - /admin/tickets/{id}/checkin       (POST)
 * - /admin/tickets/{id}/checkin-undo  (POST)
 * - /admin/tickets/{id}/cancel-ticket (POST)  <- Organizer'da /cancel
 * 
 * PATERN: Event Delegation
 * - Single listener'ı tüm button işlemleri handle eder
 * - Dinamik olarak eklenen butonları da destekler
 * - Memory efficient: 1 listener > 50 button
 * 
 * ============================================================
 */

/**
 * SAYFA YÜKLENDİĞİNDE: Event Listener'ı Kur
 * 
 * DOMContentLoaded'de listener eklemesi önemli:
 * - HTML render completed
 * - DOM tamamen hazır
 * - Safe to query/modify elements
 * 
 * ÖNEMLİ: Sadece /admin/tickets sayfasında çalış
 * Organizer sayfalarında organizer-tickets.js yeterli
 */
document.addEventListener('DOMContentLoaded', function() {
    // Guard: Sadece admin tickets sayfalarında çalış
    if (!window.location.pathname.includes('/admin/tickets')) {
        return;
    }

    /**
     * EVENT DELEGATION PATTERN
     * 
     * Avantajları:
     * 1. Performance: 50 button → 1 listener (vs 50 listeners)
     * 2. Dinamik: Sonradan eklenen butonlara da çalışır
     * 3. Memory: Listener count az
     * 4. Maintainability: Tek yerden control
     * 
     * NASIL:
     * - Document seviyesinde listener ekle
     * - Tıklama event'i bubble'lanıyor
     * - Event'i intercept et ve target kontrol et
     * - Eğer istediğimiz button ise action çalıştır
     */
    document.addEventListener('click', function(e) {
        /**
         * e.target.closest('.ticket-action-btn'):
         * 
         * SEÇME SIRASI:
         * 1. e.target: Doğrudan tıklanan element
         *    Eğer <button> elementi tıklandıysa -> Button
         *    Eğer <icon> elementi tıklandıysa -> Icon
         * 
         * 2. .closest(): Ebeveyn ağacında ara
         *    Icon tıklandıysa -> Parent button'u bul
         *    Button tıklandıysa -> Hemen kendisini bulur
         * 
         * ÖRNEK:
         * <button class="ticket-action-btn">
         *     <span>✅</span>  <- Tıklanan element
         *     Check-in
         * </button>
         * 
         * closest('button') -> Button elementini bulur
         * closest('.ticket-action-btn') -> Button'u bulur
         */
        const actionBtn = e.target.closest('.ticket-action-btn');
        if (!actionBtn) return;  // Button değilse çık

        e.preventDefault();  // Default behavior'ı iptal et
        e.stopImmediatePropagation();  // Diğer event listener'ları engelle (organizer-tickets.js çalışmayacak)

        /**
         * ADIM 1: Bilet ID'sini Çıkart
         * 
         * Öncelik sırası:
         * 1. Butonun kendisinde var mı? (dataset.ticketId)
         * 2. Row/container'da var mı?
         * 3. Document'te başka nerede var?
         * 
         * NEDEN FLEXIBIL:
         * İhtiyaca göre farklı HTML yapıları desteklemek için
         */
        let ticketId = actionBtn.dataset.ticketId;
        if (!ticketId) {
            const row = actionBtn.closest('[data-ticket-id]');
            const container = document.querySelector('[data-ticket-id]');
            ticketId = row ? row.dataset.ticketId : container?.dataset.ticketId;
        }

        /**
         * ADIM 2: İşlemi (Action) Al
         * 
         * data-action attribute'ünden oku
         * Değerler: 'checkin', 'undo', 'cancel'
         * 
         * Her action'a özel route'a gider
         */
        const action = actionBtn.dataset.action;

        /**
         * ADIM 3: Validasyon
         * 
         * Minimum gerekli veriler var mı?
         * - ticketId: Hangi bilet?
         * - action: Ne yapacak?
         * 
         * Yoksa abort et (console.error)
         */
        if (!ticketId || !action) {
            console.error('Ticket ID veya action bulunamadı');
            return;
        }

        /**
         * ADIM 4: Handler Çalıştır
         * 
         * Admin-specific handler
         * Organizatör'den farkı: /admin/tickets yolu
         */
        handleAdminTicketAction(action, ticketId);
    });
});

/**
 * ============================================================
 * TİKET İŞLEMİ - ANA FONKSİYON
 * ============================================================
 * 
 * AÇIKLAMA:
 * Admin'in biletler üzerinde yaptığı işlemleri koordine eder
 * 
 * PARAMETRELER:
 * @param {string} action - 'checkin', 'undo', 'cancel'
 * @param {number|string} ticketId - Hangi bilet
 * 
 * İŞLEM AKIŞI:
 * 1. Confirmation: Admin onay versin mi?
 * 2. API Call: AJAX isteği admin route'una gönder
 * 3. Success: Başarılı ise mesaj + UI güncelle
 * 4. Error: Başarısız ise hata mesajı göster
 * 
 * ROUTE FARCKI:
 * Organizer: /organizer/tickets/{id}/cancel
 * Admin:     /admin/tickets/{id}/cancel-ticket
 * (URL'de "cancel" vs "cancel-ticket" farkı dikkat!)
 */
async function handleAdminTicketAction(action, ticketId) {
    /**
     * ADIM 1: Onay Dialogs
     * 
     * Kullanıcı: "Emin misin?"
     * - Yes: İşleme devam
     * - No: Fonksiyondan çık
     * 
     * Admin için tüm action'lar onay gerektirir
     * (Yanlışlıkla iptal etmeyi önlemek)
     */
    const confirmMessages = {
        'undo': 'Bu bilet\'in check-in\'ini geri almak istediğinizden emin misiniz?',
        'cancel': 'Bu bileti iptal etmek istediğinizden emin misiniz? (Admin)',
        'checkin': 'Bu bilete check-in yapmak istediğinizden emin misiniz? (Admin)'
    };

    if (confirmMessages[action]) {
        if (!confirm(confirmMessages[action])) {
            return;  // Kullanıcı "No" tıkladı
        }
    }

    /**
     * ADIM 2: Admin Route URL'sini Oluştur
     * 
     * buildAdminTicketUrl: Admin-specific URL generator
     * Organizer'dan farkı: /admin/tickets path'ı
     */
    const url = buildAdminTicketUrl(action, ticketId);

    try {
        /**
         * ADIM 3: AJAX İsteği Yap
         * 
         * ajaxRequest: ajax-helper.js'teki merkezi fonksiyon
         * - CSRF token'ı otomatik ekler
         * - JSON parse eder
         * - HTTP status kontrol eder
         * 
         * POST /admin/tickets/{id}/checkin
         * Body: {} (Boş, parametreler URL'de)
         */
        const result = await ajaxRequest(url, 'POST', {});

        /**
         * ADIM 4: Başarılı Yanıt
         * 
         * Server: { success: true, message: "...", data: {...} }
         * 
         * showAlert: Yeşil başarı uyarısı göster
         * updateAdminTicketUI: Admin panelindeki satırı güncelle
         */
        if (result.success) {
            showAlert('success', result.message);
            updateAdminTicketUI(ticketId, action);
        } else {
            showAlert('error', result.message || 'Bilinmeyen bir hata oluştu.');
        }
    } catch (error) {
        /**
         * ADIM 5: Hata Yönetimi
         * 
         * Network error, server error, validation error vb.
         * error.message: Hata açıklaması
         * showAlert: Kırmızı hata uyarısı
         */
        showAlert('error', error.message || 'Bir hata oluştu. Lütfen tekrar deneyin.');
        
        /**
         * BONUS: Console'a log
         * 
         * Geliştiricilerin troubleshooting yapması için
         * Kullanıcıya gösterilmez
         */
        console.error('[Admin Tickets] Error:', error);
    }
}

/**
 * ============================================================
 * ADMIN ROUTE URL OLUŞTUR
 * ============================================================
 * 
 * AÇIKLAMA:
 * Admin-specific URL'leri oluştur
 * 
 * PARAMETRELER:
 * @param {string} action - 'checkin', 'undo', 'cancel'
 * @param {number|string} ticketId - Bilet ID
 * @returns {string} - Tam URL (ör: https://example.com/admin/tickets/123/checkin)
 * 
 * ROUTE MAPPING:
 * checkin -> /admin/tickets/{id}/checkin
 * undo    -> /admin/tickets/{id}/checkin-undo
 * cancel  -> /admin/tickets/{id}/cancel-ticket  <- Dikkat: cancel-ticket!
 * 
 * NEDEN cancel-ticket?
 * Organizatörde sadece kendi etkinliklerinin biletleri silinebilir
 * Admin'de herhangi bir bilet silinebilir, daha resmi bir operasyon
 * Route adı fark yaratır: /admin/tickets/{id}/cancel-ticket
 */
function buildAdminTicketUrl(action, ticketId) {
    const baseUrl = window.location.origin;  // https://example.com
    const prefix = '/admin/tickets';  // /admin/tickets

    const routes = {
        'checkin': `${prefix}/${ticketId}/checkin`,
        'undo': `${prefix}/${ticketId}/checkin-undo`,
        'cancel': `${prefix}/${ticketId}/cancel-ticket`  // <-- Admin'de cancel-ticket
    };

    return baseUrl + (routes[action] || '');
}

/**
 * ============================================================
 * ADMIN PANELINDE UI GÜNCELLE
 * ============================================================
 * 
 * AÇIKLAMA:
 * Admin panelindeki bilet tablosunun ilgili satırını güncelle
 * Status badge ve action butonları yeni duruma göre değişir
 * 
 * PARAMETRELER:
 * @param {number|string} ticketId - Hangi satır güncellenecek
 * @param {string} action - Yapılan işlem ('checkin', 'undo', 'cancel')
 * 
 * ADIMLAR:
 * 1. Tablo satırını bul (data-ticket-id attribute'üne göre)
 * 2. Yeni status'u belirle (action -> status mapping)
 * 3. Status attribute'unu güncelle
 * 4. Badge HTML'ini yenile
 * 5. Button'ları yenile
 */
function updateAdminTicketUI(ticketId, action) {
    /**
     * ADIM 1: Bilet Satırını Bul
     * 
     * CSS Selector: [data-ticket-id="123"]
     * 
     * HTML Örneği:
     * <tr data-ticket-id="123" data-ticket-status="active">
     *     <td>Ticket #123</td>
     *     <td>
     *         <span class="ticket-status-badge">...</span>
     *     </td>
     *     <td>
     *         <div class="ticket-actions">...</div>
     *     </td>
     * </tr>
     * 
     * querySelector: İlk eşleşeni döner
     * Tabloda her ticket unique ID'si vardır
     */
    const row = document.querySelector(`[data-ticket-id="${ticketId}"]`);
    if (!row) {
        /**
         * Row bulunamadıysa:
         * - Sayfa reload'lanmış olabilir
         * - Dynamic table update kullanılmıyor
         * - Element silinmiş olabilir
         * 
         * Hiç hata fırlatmıyoruz, sadece çıkıyoruz
         * JavaScript'te DOM element bulunamadığında graceful degrade
         */
        console.warn(`Row for ticket ${ticketId} not found`);
        return;
    }

    /**
     * ADIM 2: Action'a Göre Yeni Status'u Belirle
     * 
     * Ticket Workflow:
     * ACTIVE → [checkin] → CHECKED_IN
     * CHECKED_IN → [undo] → ACTIVE
     * ACTIVE → [cancel] → CANCELLED
     * 
     * statusMap: Action -> Status mapping
     */
    const statusMap = {
        'checkin': 'checked_in',  // ACTIVE -> CHECKED_IN
        'undo': 'active',          // CHECKED_IN -> ACTIVE
        'cancel': 'cancelled'      // ACTIVE -> CANCELLED
    };

    const newStatus = statusMap[action];
    if (!newStatus) {
        console.error(`Unknown action: ${action}`);
        return;
    }

    /**
     * ADIM 3: Data Attribute'unu Güncelle
     * 
     * row.dataset.ticketStatus = 'checked_in'
     * HTML'de: data-ticket-status="checked_in"
     * 
     * Diğer JavaScript'ler bu attribute'ü okuyabilir:
     * - CSS selectors: [data-ticket-status="checked_in"]
     * - JavaScript: row.dataset.ticketStatus
     */
    row.dataset.ticketStatus = newStatus;

    /**
     * ADIM 4: Status Badge'ini Güncelle
     * 
     * Renkli badge'i yeni status'a uygun şekilde değiştir
     * Blue (Aktif) -> Green (Kullanıldı) vb.
     */
    updateStatusBadge(row, newStatus);

    /**
     * ADIM 5: Check-in Zamanını Güncelle
     * 
     * Check-in yapıldıysa: Şu anki zamanı göster
     * Undo yapıldıysa: "-" göster
     */
    updateCheckinTime(row, action);

    /**
     * ADIM 6: Action Button'larını Güncelle
     * 
     * Status'a göre hangi button'lar görüntülenecek?
     * ACTIVE: Check-in ve Cancel button'lar
     * CHECKED_IN: Sadece Undo button'u
     * CANCELLED: Hiç button (sadece detay linki)
     */
    updateActionButtons(row, newStatus);
}

/**
 * ============================================================
 * STATUS BADGE RENK GÜNCELLE
 * ============================================================
 * 
 * AÇIKLAMA:
 * Bilet durumunu gösteren renkli badge'i güncelle
 * Kullanıcı durumu anlamak için visual cue
 * 
 * PARAMETRELER:
 * @param {HTMLElement} container - Bilet satırı
 * @param {string} status - Yeni status
 * 
 * BOOTSTRAP 5 BADGE RENKLER:
 * - ACTIVE: Primary (bg-primary) - Mavi
 * - CHECKED_IN: Success (bg-success) - Yeşil
 * - CANCELLED: Danger (bg-danger) - Kırmızı
 * - REFUNDED: Secondary (bg-secondary) - Gri
 * 
 * BOOTSTRAP CLASS AÇIKLAMASI:
 * - badge: Bootstrap badge stilini uygula (pills biçimi)
 * - bg-primary/success/danger/secondary: Arka plan rengi
 * - Icon'lar: ✅ (Kullanıldı), ❌ (İptal), 🔄 (İade)
 */
function updateStatusBadge(container, status) {
    /**
     * ADIM 1: Badge Container'ını Bul
     * 
     * .ticket-status-badge sınıflı span
     * 
     * HTML:
     * <span class="ticket-status-badge">Aktif</span>
     * 
     * querySelector: Container içinde arama
     * (document.querySelector değil, container.querySelector)
     */
    const badgeContainer = container.querySelector('.ticket-status-badge');
    if (!badgeContainer) {
        console.warn('Badge container not found');
        return;
    }

    /**
     * ADIM 2: Status -> HTML Badge Mapping
     * 
     * Her status için tamamen yeni HTML oluştur
     * (Eski HTML silinecek, buna yenisi yazılacak)
     * 
     * Bootstrap 5 badge class'ları kullanıyoruz:
     * - badge: Bootstrap badge stilini uygula
     * - bg-primary, bg-success, bg-danger, bg-secondary: Renkler
     */
    const badges = {
        /**
         * ACTIVE: Aktif bilet
         * - Renk: Mavi (primary)
         * - Anlam: Bilet henüz kullanılmadı
         */
        'active': `<span class="badge bg-primary">Aktif</span>`,
        
        /**
         * CHECKED_IN: Etkinliğe girmiş (kullanılmış)
         * - Renk: Yeşil (success)
         * - Icon: ✅ Check mark
         * - Anlam: Bilet etkinliğe girmek için kullanıldı
         */
        'checked_in': `<span class="badge bg-success">✅ Kullanıldı</span>`,
        
        /**
         * CANCELLED: İptal edilmiş
         * - Renk: Kırmızı (danger)
         * - Icon: ❌ X mark
         * - Anlam: Bilet artık geçersiz
         */
        'cancelled': `<span class="badge bg-danger">❌ İptal</span>`,
        
        /**
         * REFUNDED: Para iade edilmiş
         * - Renk: Gri (secondary)
         * - Icon: 🔄 Refresh/cycle icon
         * - Anlam: Bilet iade edilmiş (para geri verildi)
         */
        'refunded': `<span class="badge bg-secondary">🔄 İade</span>`
    };

    /**
     * ADIM 3: Badge HTML'ini Değiştir
     * 
     * badgeContainer.innerHTML = newHTML
     * 
     * Eski HTML: <span>Aktif</span>
     * Yeni HTML: <span>✅ Kullanıldı</span>
     * 
     * Eğer status'u tanımadıysak, default olarak 'active' göster
     */
    badgeContainer.innerHTML = badges[status] || badges['active'];
}

/**
 * ============================================================
 * CHECK-IN ZAMANINI GÜNCELLE
 * ============================================================
 * 
 * AÇIKLAMA:
 * Check-in sütunundaki zamanı güncelle
 * 
 * PARAMETRELER:
 * @param {HTMLElement} container - Bilet satırı (tr)
 * @param {string} action - Yapılan işlem ('checkin', 'undo')
 */
function updateCheckinTime(container, action) {
    // Check-in zamanı hücresini bul
    // Tablo yapısı: <tr><td>ID</td><td>Kod</td><td>Durum</td><td>Tip</td><td>Etkinlik</td><td>Kullanıcı</td><td>Check-in</td><td>İşlem</td></tr>
    // Check-in 7. sütun (index 6)
    const cells = container.querySelectorAll('td');
    const checkinCell = cells[6]; // 7. hücre (0-indexed)
    
    if (!checkinCell) {
        console.warn('Check-in time cell not found');
        return;
    }
    
    if (action === 'checkin') {
        // Check-in yapıldı - şu anki zamanı göster
        const now = new Date();
        const formatted = now.toLocaleDateString('tr-TR', { 
            day: '2-digit', 
            month: '2-digit', 
            year: 'numeric' 
        }) + ' ' + now.toLocaleTimeString('tr-TR', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        checkinCell.textContent = formatted;
    } else if (action === 'undo') {
        // Undo yapıldı - boş göster
        checkinCell.innerHTML = '<span class="text-muted">-</span>';
    }
}

/**
 * ============================================================
 * ACTION BUTTONLARINI GÜNCELLE
 * ============================================================
 * 
 * AÇIKLAMA:
 * Bilet status'una göre hangi işlem butonları gösterilecek
 * 
 * PARAMETRELER:
 * @param {HTMLElement} container - Bilet satırı
 * @param {string} status - Bilet status'u
 * 
 * BUTTON MAPPING:
 * 
 * ACTIVE status → 2 button:
 * - "✅ Check-in" (Green) - Etkinliğe giriş işaretle
 * - "❌ İptal" (Red) - Bileti iptal et
 * 
 * CHECKED_IN status → 1 button:
 * - "↩️ Geri Al" (Orange) - Check-in'i geri al (CHECKED_IN -> ACTIVE)
 * 
 * CANCELLED/REFUNDED status → No buttons:
 * - Artık işlem yapılamaz
 * - Sadece detay linki varsa görünür
 */
function updateActionButtons(container, status) {
    /**
     * ADIM 1: Actions Container'ını Bul
     * 
     * .ticket-actions sınıflı div
     * 
     * HTML:
     * <div class="ticket-actions">
     *     <button data-action="checkin">Check-in</button>
     *     <button data-action="cancel">İptal</button>
     *     <a href="...">Detay</a>
     * </div>
     */
    const actionsContainer = container.querySelector('.ticket-actions');
    if (!actionsContainer) {
        console.warn('Actions container not found');
        return;
    }

    /**
     * ADIM 2: Eski Button'ları Sil
     * 
     * querySelectorAll: Tüm eşleşeni seçer
     * forEach: Her element için loop
     * remove(): Element'i DOM'dan sil
     * 
     * Neden? Yeni button'lar ekleyeceğiz
     * 
     * Detay linki silinmez (<a> elementi değil):
     * .ticket-action-btn sadece <button>'ları seçer
     */
    const buttons = actionsContainer.querySelectorAll('.ticket-action-btn');
    buttons.forEach(btn => btn.remove());

    /**
     * ADIM 3: Status'a Göre Yeni Button'ları Ekle
     * 
     * getAdminButtonsForStatus: Status -> HTML button mapping
     * 
     * Eğer status CANCELLED ise:
     * buttonHTML = '' (boş string)
     * 
     * Eğer status ACTIVE ise:
     * buttonHTML = '<button>...</button><button>...</button>'
     */
    /**
     * ADIM 3: Yeni Button'ları Oluştur
     * 
     * Detay linkini koru, sadece action button'ları değiştir
     */
    const detayLink = actionsContainer.querySelector('a[href*="admin.tickets.show"]');
    const buttonHTML = getAdminButtonsForStatus(status);
    
    if (buttonHTML) {
        // Button'ları detay linkinden önce ekle
        if (detayLink) {
            detayLink.insertAdjacentHTML('beforebegin', buttonHTML);
        } else {
            actionsContainer.insertAdjacentHTML('afterbegin', buttonHTML);
        }
    }
}

/**
 * ============================================================
 * STATUS'A GÖRE BUTTON LISTESI
 * ============================================================
 * 
 * AÇIKLAMA:
 * Bilet status'una göre gösterilecek butonların HTML'sini döndür
 * 
 * PARAMETRELER:
 * @param {string} status - Bilet status'u
 * @returns {string} - HTML string (button'lar veya boş)
 * 
 * AÇIKLAMA:
 * 
 * ACTIVE:
 * - Check-in button: Bilete check-in yap (ACTIVE -> CHECKED_IN)
 * - Cancel button: Bileti iptal et (ACTIVE -> CANCELLED)
 * 
 * CHECKED_IN:
 * - Undo button: Check-in'i geri al (CHECKED_IN -> ACTIVE)
 * 
 * CANCELLED / REFUNDED:
 * - (No buttons)
 * 
 * EVENT LISTENER:
 * .ticket-action-btn sınıfı event listener tarafından dinlenir
 * onClick: handleAdminTicketAction() çalışır
 */
function getAdminButtonsForStatus(status) {
    const buttons = {
        /**
         * ACTIVE: İki button seçeneği
         * Bootstrap 5 button class'ları kullanıyoruz
         * Not: Button'lar arasında boşluk bırak (d-inline-flex gap-2 için)
         */
        'active': '<button class="ticket-action-btn btn btn-outline-success btn-sm" data-action="checkin" title="Giriş Kontrolü">✅ Giriş Onayla</button> ' +
                  '<button class="ticket-action-btn btn btn-outline-danger btn-sm" data-action="cancel" title="İptal">❌ İptal</button> ',
        
        /**
         * CHECKED_IN: Sadece Undo button'u
         */
        'checked_in': '<button class="ticket-action-btn btn btn-outline-warning btn-sm" data-action="undo" title="Geri Al">↩️ Geri Al</button> ',
        
        /**
         * CANCELLED: Hiç button
         */
        'cancelled': '',
        
        /**
         * REFUNDED: Hiç button
         */
        'refunded': ''
    };

    return buttons[status] || '';
}
