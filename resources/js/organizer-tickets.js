/**
 * Organizer Tickets AJAX Handler
 * 
 * Event delegation pattern - tüm ticket işlemlerini handle eder
 * - Check-in, Undo Check-in, Cancel
 * - Status badge güncellemesi
 * - Button state management
 */

document.addEventListener('DOMContentLoaded', function() {
    // Event delegation for ticket action buttons
    document.addEventListener('click', function(e) {
        const actionBtn = e.target.closest('.ticket-action-btn');
        if (!actionBtn) return;

        e.preventDefault();

        // Ticket ID'yi al - row'dan veya inline container'dan
        let ticketId = actionBtn.dataset.ticketId;
        if (!ticketId) {
            const row = actionBtn.closest('[data-ticket-id]');
            const container = document.querySelector('[data-ticket-id]');
            ticketId = row ? row.dataset.ticketId : container?.dataset.ticketId;
        }

        const action = actionBtn.dataset.action;

        if (!ticketId || !action) {
            console.error('Ticket ID veya action bulunamadı');
            return;
        }

        handleTicketAction(action, ticketId);
    });
});

/**
 * Ticket action'ını handle et
 * @param {string} action - 'checkin', 'undo', 'cancel'
 * @param {number|string} ticketId
 */
async function handleTicketAction(action, ticketId) {
    // Confirmation dialogs
    const confirmMessages = {
        'undo': 'Bu bilet\'in check-in\'ini geri almak istediğinizden emin misiniz?',
        'cancel': 'Bu bileti iptal etmek istediğinizden emin misiniz?',
        'checkin': 'Bu bilete check-in yapmak istediğinizden emin misiniz?'
    };

    if (confirmMessages[action]) {
        if (!confirm(confirmMessages[action])) {
            return;
        }
    }

    // Route'u oluştur
    const url = buildTicketUrl(action, ticketId);

    try {
        // AJAX isteğini yap
        const result = await ajaxRequest(url, 'POST', {});

        // Başarılı yanıt
        if (result.success) {
            showAlert('success', result.message);
            updateTicketUI(ticketId, action);
        } else {
            showAlert('error', result.message || 'Bilinmeyen bir hata oluştu.');
        }
    } catch (error) {
        showAlert('error', error.message || 'Bir hata oluştu. Lütfen tekrar deneyin.');
    }
}

/**
 * Route URL'sini oluştur
 * @param {string} action - 'checkin', 'undo', 'cancel'
 * @param {number|string} ticketId
 * @returns {string} - API endpoint URL
 */
function buildTicketUrl(action, ticketId) {
    const baseUrl = window.location.origin;
    const prefix = '/organizer/tickets';

    const routes = {
        'checkin': `${prefix}/${ticketId}/checkin`,
        'undo': `${prefix}/${ticketId}/checkin-undo`,
        'cancel': `${prefix}/${ticketId}/cancel`
    };

    return baseUrl + (routes[action] || '');
}

/**
 * Ticket UI'ını güncelle
 * @param {number|string} ticketId
 * @param {string} action - yapılan işlem
 */
function updateTicketUI(ticketId, action) {
    // Row veya container'ı bul
    const row = document.querySelector(`[data-ticket-id="${ticketId}"]`);
    if (!row) return;

    // Yeni status'u belirle
    const statusMap = {
        'checkin': 'checked_in',
        'undo': 'active',
        'cancel': 'cancelled'
    };

    const newStatus = statusMap[action];
    if (!newStatus) return;

    // Status attribute'unu güncelle
    row.dataset.ticketStatus = newStatus;

    // Status badge'ini güncelle
    updateStatusBadge(row, newStatus);

    // Action butonlarını güncelle
    updateActionButtons(row, newStatus);
}

/**
 * Status badge'ini güncelle
 * @param {HTMLElement} container
 * @param {string} status
 */
function updateStatusBadge(container, status) {
    const badgeContainer = container.querySelector('.ticket-status-badge');
    if (!badgeContainer) return;

    const badges = {
        'active': '<span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">Aktif</span>',
        'checked_in': '<span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">✅ Kullanıldı</span>',
        'cancelled': '<span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">❌ İptal</span>',
        'refunded': '<span class="inline-block bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold">🔄 İade</span>'
    };

    badgeContainer.innerHTML = badges[status] || badges['active'];
}

/**
 * Action butonlarını güncelle
 * @param {HTMLElement} container
 * @param {string} status
 */
function updateActionButtons(container, status) {
    const actionsContainer = container.querySelector('.ticket-actions');
    if (!actionsContainer) return;

    // Existing buttons'ı kaldır (Detay linki hariç)
    const buttons = actionsContainer.querySelectorAll('.ticket-action-btn');
    buttons.forEach(btn => btn.remove());

    // Yeni button'ları ekle
    const buttonHTML = getButtonsForStatus(status);
    
    if (buttonHTML) {
        // Detay linki bulunuyorsa ondan önce ekle, yoksa başına ekle
        const detayLink = actionsContainer.querySelector('a[href*="organizer.tickets.show"]');
        if (detayLink) {
            detayLink.insertAdjacentHTML('beforebegin', buttonHTML);
        } else {
            actionsContainer.insertAdjacentHTML('afterbegin', buttonHTML);
        }
    } else {
        // Detay linki yoksa mesaj göster
        if (!detayLink) {
            actionsContainer.innerHTML = '<span class="text-gray-400 text-sm">-</span>';
        }
    }
}

/**
 * Status'a göre button HTML'ini döndür
 * @param {string} status
 * @returns {string} - HTML string
 */
function getButtonsForStatus(status) {
    const buttons = {
        'active': `
            <button class="ticket-action-btn text-green-600 hover:text-green-800 text-sm font-medium" data-action="checkin" title="Check-in">
                ✅ Check-in
            </button>
            <button class="ticket-action-btn text-red-600 hover:text-red-800 text-sm font-medium" data-action="cancel" title="İptal Et">
                ❌ İptal
            </button>
        `,
        'checked_in': `
            <button class="ticket-action-btn text-orange-600 hover:text-orange-800 text-sm font-medium" data-action="undo" title="Check-in'i Geri Al">
                ↩️ Geri Al
            </button>
        `,
        'cancelled': '',
        'refunded': ''
    };

    return buttons[status] || '';
}
