/**
 * AJAX Helper - Tek fetch wrapper tüm AJAX istekleri için
 * 
 * Usage:
 * ajaxRequest('/orders/123/cancel', 'POST', {})
 *   .then(data => showAlert('success', data.message))
 *   .catch(error => showAlert('error', error.message));
 */

async function ajaxRequest(url, method = 'POST', data = {}, options = {}) {
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

    // POST/PUT/PATCH için body ekle
    if (['POST', 'PUT', 'PATCH'].includes(method.toUpperCase())) {
        defaultOptions.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(url, defaultOptions);
        const result = await response.json();

        if (!response.ok) {
            // 422, 403, 404 vb.
            throw {
                status: response.status,
                message: result.message || 'Bir hata oluştu.',
                errors: result.errors || {}
            };
        }

        return result;
    } catch (error) {
        if (error.status) {
            // Sunucu hatası
            throw error;
        }
        // Network hatası
        throw {
            status: 0,
            message: 'Bağlantı hatası. Lütfen tekrar deneyin.',
            errors: {}
        };
    }
}

/**
 * Alert göster
 */
function showAlert(type, message, duration = 5000) {
    const alertContainer = document.getElementById('ajax-alert-container');
    if (!alertContainer) {
        console.warn('Alert container bulunamadı!');
        return;
    }

    const alertClass = type === 'success' 
        ? 'bg-green-50 border-green-200 text-green-800' 
        : 'bg-red-50 border-red-200 text-red-800';
    
    const icon = type === 'success' ? '✅' : '❌';

    const alertHTML = `
        <div class="ajax-alert ${alertClass} border rounded-lg p-4 mb-4 flex justify-between items-center">
            <span>${icon} ${message}</span>
            <button onclick="this.parentElement.remove()" class="ml-4 text-gray-600 hover:text-gray-800">×</button>
        </div>
    `;

    alertContainer.insertAdjacentHTML('beforeend', alertHTML);

    // Auto-remove after duration
    if (duration > 0) {
        setTimeout(() => {
            const alerts = alertContainer.querySelectorAll('.ajax-alert');
            if (alerts.length > 0) {
                alerts[0].remove();
            }
        }, duration);
    }
}

/**
 * Alert container'ı temizle
 */
function clearAlerts() {
    const alertContainer = document.getElementById('ajax-alert-container');
    if (alertContainer) {
        alertContainer.innerHTML = '';
    }
}
