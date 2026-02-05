<script>
    /**
     * ATTENDEE AJAX HANDLER
     * Buy, Cancel, Pay, Refund işlemleri için AJAX
     * Progressive enhancement: form normal submit ile de çalışır
     */

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.querySelector('input[name="_token"]')?.value
            || document.getElementById('csrf-token')?.value
            || '';
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // =====================================================
        // 1. QUANTITY SELECTOR (Event Show)
        // =====================================================
        const qtyPlusButtons = document.querySelectorAll('.qty-plus');
        const qtyMinusButtons = document.querySelectorAll('.qty-minus');
        const qtyInputs = document.querySelectorAll('.qty-input');
        
        qtyPlusButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const fieldName = this.dataset.field;
                const input = document.querySelector(`input[name="${fieldName}"]`);
                const maxStock = parseInt(input.dataset.max);
                const currentVal = parseInt(input.value) || 0;
                
                if (currentVal < Math.min(maxStock, 10)) {
                    input.value = currentVal + 1;
                }
            });
        });
        
        qtyMinusButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const fieldName = this.dataset.field;
                const input = document.querySelector(`input[name="${fieldName}"]`);
                const currentVal = parseInt(input.value) || 0;
                
                if (currentVal > 0) {
                    input.value = currentVal - 1;
                }
            });
        });
        
        qtyInputs.forEach(input => {
            input.addEventListener('change', function() {
                let val = parseInt(this.value) || 0;
                const maxStock = parseInt(this.dataset.max);
                
                if (val < 0) val = 0;
                if (val > Math.min(maxStock, 10)) val = Math.min(maxStock, 10);
                
                this.value = val;
            });
        });
        
        // =====================================================
        // 2. BUY FORM SUBMISSION (AJAX)
        // =====================================================
        const buyForm = document.getElementById('buy-form');
        if (buyForm) {
            buyForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate: en az 1 bilet seçilmiş mi?
                const totalQty = Array.from(qtyInputs).reduce((sum, input) => {
                    return sum + (parseInt(input.value) || 0);
                }, 0);
                
                if (totalQty === 0) {
                    showAlert('Lütfen en az 1 bilet seçiniz.', 'error');
                    return;
                }
                
                // AJAX submit
                const button = buyForm.querySelector('button[type="submit"]');
                const originalText = button.textContent;
                button.disabled = true;
                button.textContent = '🔄 Sipariş oluşturuluyor...';
                
                const formData = new FormData(buyForm);

                fetch(buyForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok && response.status !== 422) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Success: redirect to order
                        showAlert('✓ Siparişiniz oluşturuldu! Ödeme sayfasına yönlendiriliyorsunuz...', 'success');
                        setTimeout(() => {
                            window.location.href = data.data.redirect_url;
                        }, 1500);
                    } else {
                        // Error
                        showAlert(data.message || 'İşlem başarısız oldu.', 'error');
                        button.disabled = false;
                        button.textContent = originalText;
                    }
                })
                .catch(error => {
                    showAlert('Bir hata oluştu: ' + error.message, 'error');
                    button.disabled = false;
                    button.textContent = originalText;
                });
            });
        }
        
        // =====================================================
        // 3. CANCEL BUTTON (AJAX)
        // =====================================================
        const cancelBtn = document.getElementById('order-cancel-btn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (!confirm('Siparişi iptal etmek istediğine emin misin?')) {
                    return;
                }
                
                const orderId = this.dataset.orderId;
                const originalText = this.textContent;
                this.disabled = true;
                this.textContent = '🔄 İptal ediliyor...';
                
                fetch(`/orders/${orderId}/cancel`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update status badge
                        const badge = document.getElementById('order-status-badge');
                        if (badge) {
                            badge.innerHTML = '<span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">❌ İptal Edildi</span>';
                        }
                        
                        // Update ticket badges
                        document.querySelectorAll('.ticket-status-badge').forEach(el => {
                            el.innerHTML = '<span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">❌ İptal Edildi</span>';
                        });
                        
                        // Hide action buttons
                        document.getElementById('order-actions').innerHTML = '<p class="text-gray-600">Sipariş iptal edilmiştir.</p>';
                        
                        showAlert('✓ Siparişiniz başarıyla iptal edildi.', 'success');
                    } else {
                        showAlert(data.message || 'İptal işlemi başarısız oldu.', 'error');
                        this.disabled = false;
                        this.textContent = originalText;
                    }
                })
                .catch(error => {
                    showAlert('Bir hata oluştu: ' + error.message, 'error');
                    this.disabled = false;
                    this.textContent = originalText;
                });
            });
        }
        
        // =====================================================
        // 4. PAY BUTTON (AJAX)
        // =====================================================
        const payBtn = document.getElementById('order-pay-btn');
        if (payBtn) {
            payBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                const orderId = this.dataset.orderId;
                const originalText = this.textContent;
                this.disabled = true;
                this.textContent = '🔄 Ödeme işleniyor...';
                
                fetch(`/orders/${orderId}/pay`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update status badge
                        const badge = document.getElementById('order-status-badge');
                        if (badge) {
                            badge.innerHTML = '<span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">✅ Ödendi</span>';
                        }
                        
                        // Replace action buttons
                        const actionsDiv = document.getElementById('order-actions');
                        actionsDiv.innerHTML = `
                            <button id="order-refund-btn" data-order-id="${orderId}" class="w-full md:w-auto bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-orange-700 transition">
                                ↩️ İade Talep Et
                            </button>
                        `;
                        
                        // Attach refund listener
                        document.getElementById('order-refund-btn').addEventListener('click', refundHandler);
                        
                        showAlert('✓ Ödeme başarılı! Biletleriniz hazır.', 'success');
                    } else {
                        showAlert(data.message || 'Ödeme işlemi başarısız oldu.', 'error');
                        this.disabled = false;
                        this.textContent = originalText;
                    }
                })
                .catch(error => {
                    showAlert('Bir hata oluştu: ' + error.message, 'error');
                    this.disabled = false;
                    this.textContent = originalText;
                });
            });
        }
        
        // =====================================================
        // 5. REFUND BUTTON (AJAX)
        // =====================================================
        function refundHandler(e) {
            e.preventDefault();
            
            if (!confirm('Ödemenizi geri almak istediğine emin misin? (3-5 gün sürebilir)')) {
                return;
            }
            
            const orderId = this.dataset.orderId;
            const originalText = this.textContent;
            this.disabled = true;
            this.textContent = '🔄 İade işleniyor...';
            
            fetch(`/orders/${orderId}/refund`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update status badge
                    const badge = document.getElementById('order-status-badge');
                    if (badge) {
                        badge.innerHTML = '<span class="inline-block bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-semibold">🔄 İade Edildi</span>';
                    }
                    
                    // Update ticket badges
                    document.querySelectorAll('.ticket-status-badge').forEach(el => {
                        el.innerHTML = '<span class="inline-block bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-semibold">🔄 İade Edildi</span>';
                    });
                    
                    // Hide action buttons
                    document.getElementById('order-actions').innerHTML = '<p class="text-gray-600">İade işlemi tamamlanmıştır. Ödemeniz 3-5 gün içinde hesabınıza yatırılacaktır.</p>';
                    
                    showAlert('✓ İade işlemi başarılı. Ödemeniz 3-5 gün içinde hesabınıza yatırılacaktır.', 'success');
                } else {
                    showAlert(data.message || 'İade işlemi başarısız oldu.', 'error');
                    this.disabled = false;
                    this.textContent = originalText;
                }
            })
            .catch(error => {
                showAlert('Bir hata oluştu: ' + error.message, 'error');
                this.disabled = false;
                this.textContent = originalText;
            });
        }
        
        const refundBtn = document.getElementById('order-refund-btn');
        if (refundBtn) {
            refundBtn.addEventListener('click', refundHandler);
        }
    });
    
    // =====================================================
    // HELPER FUNCTION: Show Alert
    // =====================================================
    function showAlert(message, type = 'info') {
        const container = document.querySelector('.container');
        const alertDiv = document.createElement('div');
        
        const classes = {
            'success': 'bg-green-100 border-green-400 text-green-700',
            'error': 'bg-red-100 border-red-400 text-red-700',
            'warning': 'bg-yellow-100 border-yellow-400 text-yellow-700',
            'info': 'bg-blue-100 border-blue-400 text-blue-700',
        }[type] || 'bg-blue-100 border-blue-400 text-blue-700';
        
        const icon = {
            'success': '✓',
            'error': '✗',
            'warning': '⚠',
            'info': 'ℹ',
        }[type] || 'ℹ';
        
        alertDiv.className = `border rounded-lg px-4 py-3 mb-4 ${classes} alert-${type}`;
        alertDiv.innerHTML = `<span class="text-lg mr-2">${icon}</span> ${message}`;
        
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
</script>
