/**
 * Organizer Tickets - Event Delegation Pattern
 * 
 * AJAX operations: checkin, checkinUndo, cancel
 * Event delegation: document-level click listener
 * data-* attributes ile ticket ID ve action binding
 * Response handling: showAlert() notifications
 */

/**
 * SAYFA YÜKLENDİĞİNDE: Event Listener'ı Kur
 * 
 * DOMContentLoaded: HTML'in tamamı yüklendi (img/CSS bekleme yok)
 * Bu event'de DOM manipülasyonu güvenlidir
 */
document.addEventListener('DOMContentLoaded', function() {
    /**
     * EVENT DELEGATION: Document seviyesinde click listener
     * 
     * NASIL ÇALIŞIR:
     * 1. Tarayıcı: "Bir şey tıklandı"
     * 2. Event bubble: Click event document'e çıkıyor
     * 3. Listener: "Bu .ticket-action-btn mu?
     * 4. Evet -> handleTicketAction() çalıştır
     * 5. Hayır -> İgnore et
     * 
     * FAYDALAR:
     * - Dinamik olarak eklenen butonlara da çalışır
     * - Her button için ayrı listener lazım değil
     * - Memory efficient
     */
    document.addEventListener('click', function(e) {
        /**
         * e.target.closest('.ticket-action-btn'):
         * 
         * e.target: Tıklanan element
         * .closest(): Ebeveynlerde arama
         * Bulursa: Element node
         * Bulamazsa: null
         * 
         * Örnek:
         * <button class="ticket-action-btn">Check-in</button>
         *    -> Bulur, button döner
         * 
         * <button>
         *    <span>Check-in</span>  <- Tıklanan element
         * </button class="ticket-action-btn">
         *    -> Span'den yukarı gider, button'u bulur
         */
        const actionBtn = e.target.closest('.ticket-action-btn');
        if (!actionBtn) return;  // Eğer button değilse çık

        e.preventDefault();

        /**
         * ADIM 1: Bilet ID'sini Bul
         * 
         * data-ticket-id attribute'ünden aç
         * 
         * Öncelik:
         * 1. Butonun kendisinde var mı?
         * 2. Bir parent row'da var mı?
         * 3. Document'de başka container'lar var mı?
         * 
         * Örnek HTML:
         * <tr data-ticket-id="123">
         *     <td>Bilet Info</td>
         *     <td>
         *         <button class="ticket-action-btn" 
         *                 data-action="checkin">Check-in</button>
         *     </td>
         * </tr>
         */
        let ticketId = actionBtn.dataset.ticketId;
        if (!ticketId) {
            const row = actionBtn.closest('[data-ticket-id]');
            const container = document.querySelector('[data-ticket-id]');
            ticketId = row ? row.dataset.ticketId : container?.dataset.ticketId;
        }

        // Extract action from data-action attribute 
         //* data-action attribute'ünden oku
         //rneğin: 'checkin', 'undo', 'cancel'
        
        const action = actionBtn.dataset.action;

        /**
         * ADIM 3: Validasyon
         * 
         * Eğer ticket ID veya action yoksa:
         * - Console'a error yaz
         * - Fonksiyondan çık
         */
        if (!ticketId || !action) {
            console.error('Ticket ID veya action bulunamadı');
            return;
        }

        /**
         * ADIM 4: İşlemi Yap
         * 
         * handleTicketAction: Ana fonksiyon
         * ticketId ve action'u gönder
         */
        handleTicketAction(action, ticketId);
    });
});

/**
 * ============================================================
 * TİKET İŞLEMİ - ANA FONKSİYON
 * ============================================================
 * 
 * AÇIKLAMA:
 * Bilet işlemini (check-in, undo, cancel) yapma mantığı
 * 
 * PARAMETRELER:
 * @param {string} action - 'checkin', 'undo', 'cancel'
 * @param {number|string} ticketId - Bilet ID
 * 
 * AKIŞ:
 * 1. Confirmation dialog göster
 * 2. Eğer iptal etti, çık
 * 3. URL oluştur
 * 4. AJAX isteği yap
 * 5. Başarı: Mesaj göster + UI güncelle
 * 6. Hata: Hata mesajı göster
 */
async function handleTicketAction(action, ticketId) {
    /**
     * ADIM 1: Onay Dialog'u
     * 
     * Kullanıcı: "Emin misin?"
     * Yes -> İşleme devam et
     * No -> Fonksiyondan çık
     * 
     * confirmMessages: Her action için farklı mesaj
     */
    const confirmMessages = {
        'undo': 'Bu bilet\'in check-in\'ini geri almak istediğinizden emin misiniz?',
        'cancel': 'Bu bileti iptal etmek istediğinizden emin misiniz?',
        'checkin': 'Bu bilete check-in yapmak istediğinizden emin misiniz?'
    };

    /**
     * Confirmation mesajı varsa sor
     * confirm(): Browser'ın built-in dialog
     * True dönerse devam et
     * False dönerse çık
     */
    if (confirmMessages[action]) {
        if (!confirm(confirmMessages[action])) {
            return;  // Kullanıcı iptal etti
        }
    }

    // Route'u oluştur (buildTicketUrl fonksiyonu)
    const url = buildTicketUrl(action, ticketId);

    try {
        /**
         * ADIM 2: AJAX İsteği Yap
         * 
         * ajaxRequest: ajax-helper.js'teki merkezi fonksiyon
         * 
         * POST /organizer/tickets/{id}/checkin
         * Body: {} (Boş, sadece parametreler)
         * 
         * Başarı: { success: true, message: "...", data: {} }
         * Hata: throw { status, message, errors }
         */
        const result = await ajaxRequest(url, 'POST', {});

        /**
         * ADIM 3: Başarılı Yanıt
         * 
         * result.success: true ise işlem başarılı
         * result.message: Gösterilecek mesaj
         * updateTicketUI(): UI'ı güncelle
         */
        if (result.success) {
            showAlert('success', result.message);
            updateTicketUI(ticketId, action);
        } else {
            showAlert('error', result.message || 'Bilinmeyen bir hata oluştu.');
        }
    } catch (error) {
        /**
         * ADIM 4: Hata Yönetimi
         * 
         * Sunucu hatası, network hatası, vb.
         * error.message: Hata mesajı
         * showAlert: Kırmızı uyarı göster
         */
        showAlert('error', error.message || 'Bir hata oluştu. Lütfen tekrar deneyin.');
    }
}

/**
 * ============================================================
 * URL OLUŞTUR
 * ============================================================
 * 
 * AÇIKLAMA:
 * İşleme göre doğru endpoint URL'sini oluştur
 * 
 * PARAMETRELER:
 * @param {string} action - 'checkin', 'undo', 'cancel'
 * @param {number|string} ticketId
 * @returns {string} - Tam URL (ör: https://example.com/organizer/tickets/123/checkin)
 * 
 * ROUT MAPPING:
 * checkin -> /organizer/tickets/{id}/checkin
 * undo    -> /organizer/tickets/{id}/checkin-undo
 * cancel  -> /organizer/tickets/{id}/cancel
 */
function buildTicketUrl(action, ticketId) {
    const baseUrl = window.location.origin;  // https://example.com
    const prefix = '/organizer/tickets';  // /organizer/tickets

    const routes = {
        'checkin': `${prefix}/${ticketId}/checkin`,
        'undo': `${prefix}/${ticketId}/checkin-undo`,
        'cancel': `${prefix}/${ticketId}/cancel`
    };

    return baseUrl + (routes[action] || '');
}

/**
 * ============================================================
 * UI GÜNCELLE - SUNUCU YANITTI SONRASI
 * ============================================================
 * 
 * AÇIKLAMA:
 * Sayfa yenilenmeden, bilet satırının UI'ını güncelle
 * Status badge ve butonlar yeni duruma göre değişir
 * 
 * PARAMETRELER:
 * @param {number|string} ticketId - Hangi bilet güncellenecek
 * @param {string} action - Yapılan işlem ('checkin', 'undo', 'cancel')
 * 
 * ADIMLAR:
 * 1. Bilet satırını bul
 * 2. Yeni status'u belirle
 * 3. Status badge'i güncelle
 * 4. Action butonlarını güncelle
 */
function updateTicketUI(ticketId, action) {
    /**
     * ADIM 1: Satırı Bul
     * 
     * [data-ticket-id="123"]: data attribute'üne göre element seç
     * 
     * Örnek:
     * <tr data-ticket-id="123">
     *     <td>Ticket #123</td>
     *     ...
     * </tr>
     */
    const row = document.querySelector(`[data-ticket-id="${ticketId}"]`);
    if (!row) return;

    /**
     * ADIM 2: Yeni Status'u Belirle
     * 
     * Action -> Yeni Status Mapping:
     * 'checkin' -> 'checked_in' (ACTIVE -> CHECKED_IN)
     * 'undo'    -> 'active' (CHECKED_IN -> ACTIVE)
     * 'cancel'  -> 'cancelled' (ACTIVE -> CANCELLED)
     */
    const statusMap = {
        'checkin': 'checked_in',
        'undo': 'active',
        'cancel': 'cancelled'
    };

    const newStatus = statusMap[action];
    if (!newStatus) return;

    /**
     * ADIM 3: Status Attribute'ünü Güncelle
     * 
     * row.dataset.ticketStatus = newStatus
     * HTML'de: data-ticket-status="checked_in"
     * 
     * Diğer JavaScript'ler bu attribute'ü okuyabilir
     */
    row.dataset.ticketStatus = newStatus;

    /**
     * ADIM 4: Status Badge'i Güncelle (Renk değişir)
     */
    updateStatusBadge(row, newStatus);

    /**
     * ADIM 5: Action Butonlarını Güncelle
     */
    updateActionButtons(row, newStatus);
}

/**
 * ============================================================
 * STATUS BADGE GÜNCELLE
 * ============================================================
 * 
 * AÇIKLAMA:
 * Bilet durumunu gösteren renk badge'ini güncelle
 * 
 * PARAMETRELER:
 * @param {HTMLElement} container - Bilet satırı
 * @param {string} status - Yeni status
 * 
 * RENKLER:
 * - ACTIVE: Mavi (bg-blue-100)
 * - CHECKED_IN: Yeşil (bg-green-100)
 * - CANCELLED: Kırmızı (bg-red-100)
 * - REFUNDED: Gri (bg-gray-100)
 */
function updateStatusBadge(container, status) {
    /**
     * ADIM 1: Badge Container'ını Bul
     * 
     * .ticket-status-badge sınıfındaki element
     * Örnek:
     * <span class="ticket-status-badge">Aktif</span>
     */
    const badgeContainer = container.querySelector('.ticket-status-badge');
    if (!badgeContainer) return;

    /**
     * ADIM 2: Status -> HTML Badge Mapping
     * 
     * Her status için Tailwind CSS styled HTML
     * - px-3: Horizontal padding
     * - py-1: Vertical padding
     * - rounded-full: Yuvarlak köşeler
     * - text-xs: Küçük font
     * - font-semibold: Kalın yazı
     */
    const badges = {
        'active': '<span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">Aktif</span>',
        'checked_in': '<span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">✅ Kullanıldı</span>',
        'cancelled': '<span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">❌ İptal</span>',
        'refunded': '<span class="inline-block bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold">🔄 İade</span>'
    };

    /**
     * ADIM 3: Badge HTML'ini Değiştir
     * 
     * badgeContainer.innerHTML = badges[status]
     * Eski HTML silinir, yenisi yazılır
     */
    badgeContainer.innerHTML = badges[status] || badges['active'];
}

/**
 * ============================================================
 * ACTION BUTTONLARINI GÜNCELLE
 * ============================================================
 * 
 * AÇIKLAMA:
 * Status'a göre hangi butonlar gösterilecek belirle
 * 
 * PARAMETRELER:
 * @param {HTMLElement} container - Bilet satırı
 * @param {string} status - Bilet status'u
 * 
 * BUTTON MAPPING:
 * ACTIVE:     [Check-in button]  [Cancel button]
 * CHECKED_IN: [Undo button]
 * CANCELLED:  (Hiç button)
 * REFUNDED:   (Hiç button)
 */
function updateActionButtons(container, status) {
    /**
     * ADIM 1: Actions Container'ını Bul
     * 
     * .ticket-actions: Butonları barındıran div
     * Örnek:
     * <div class="ticket-actions">
     *     <button>Check-in</button>
     *     <a href="...">Detay</a>
     * </div>
     */
    const actionsContainer = container.querySelector('.ticket-actions');
    if (!actionsContainer) return;

    /**
     * ADIM 2: Eski Button'ları Kaldır
     * 
     * .ticket-action-btn sınıflı tüm butonlar silinir
     * (Detay linki silinmez)
     * 
     * Neden? Güncellenmiş button'lar ekleyeceğiz
     */
    const buttons = actionsContainer.querySelectorAll('.ticket-action-btn');
    buttons.forEach(btn => btn.remove());

    /**
     * ADIM 3: Yeni Button HTML'ini Al
     * 
     * getButtonsForStatus: Status'a göre button HTML'ini döner
     */
    const buttonHTML = getButtonsForStatus(status);
    
    if (buttonHTML) {
        /**
         * ADIM 4: Button'ları Ekle
         * 
         * Detay linki varsa: Ondan önce ekle
         * Detay linki yoksa: Container'ın başına ekle
         * 
         * insertAdjacentHTML('beforebegin', ...):
         * - Seçilen element'in BEFORE'ine ekle
         * - Nested element değil, kardeş element
         */
        const detayLink = actionsContainer.querySelector('a[href*="organizer.tickets.show"]');
        if (detayLink) {
            detayLink.insertAdjacentHTML('beforebegin', buttonHTML);
        } else {
            actionsContainer.insertAdjacentHTML('afterbegin', buttonHTML);
        }
    } else {
        /**
         * Hiç button gösterilecek yoksa (CANCELLED, REFUNDED)
         * Ancak detay linki de yoksa "-" yazı göster
         */
        if (!detayLink) {
            actionsContainer.innerHTML = '<span class="text-gray-400 text-sm">-</span>';
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
 * @returns {string} - Button HTML (veya boş string)
 * 
 * BUTTON MAPPING:
 * 
 * ACTIVE:
 * - ✅ Check-in (green) - Kullan button'u
 * - ❌ İptal (red) - Cancel button'u
 * 
 * CHECKED_IN:
 * - ↩️ Geri Al (orange) - Undo button'u
 * 
 * CANCELLED / REFUNDED:
 * - (Hiç button, sadece detay linki varsa görüntülenebilir)
 */
function getButtonsForStatus(status) {
    const buttons = {
        /**
         * ACTIVE: İki button seçeneği sunula
         * 
         * Check-in Button:
         * - Data: data-action="checkin"
         * - Color: Green (text-green-600, hover:text-green-800)
         * - Handler: ticket-action-btn sınıfı dinleniyor
         * 
         * Cancel Button:
         * - Data: data-action="cancel"
         * - Color: Red
         * - Handler: ticket-action-btn sınıfı dinleniyor
         */
        'active': `
            <button class="ticket-action-btn text-green-600 hover:text-green-800 text-sm font-medium" data-action="checkin" title="Check-in">
                ✅ Check-in
            </button>
            <button class="ticket-action-btn text-red-600 hover:text-red-800 text-sm font-medium" data-action="cancel" title="İptal Et">
                ❌ İptal
            </button>
        `,
        
        /**
         * CHECKED_IN: Sadece Undo button'u
         * 
         * Undo Button:
         * - Data: data-action="undo"
         * - Color: Orange (warning color)
         * - Amacı: Hatalı check-in'i geri alma
         */
        'checked_in': `
            <button class="ticket-action-btn text-orange-600 hover:text-orange-800 text-sm font-medium" data-action="undo" title="Check-in'i Geri Al">
                ↩️ Geri Al
            </button>
        `,
        
        /**
         * CANCELLED / REFUNDED: Hiç button (boş string)
         * 
         * İptal veya iade edilmiş biletlere
         * Artık işlem yapılamaz
         * Sadece detay bilgisi gösterilebilir
         */
        'cancelled': '',
        'refunded': ''
    };

    return buttons[status] || '';
}
