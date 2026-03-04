// panel.js - Admin Panel JavaScript Functions

// ==================== GLOBAL VARIABLES ====================
let currentModal = null;

// ==================== DOM READY ====================
document.addEventListener('DOMContentLoaded', function() {
    initializeComponents();
    initializeModals();
    initializeForms();
    initializeDeleteButtons();
    initializeAlerts();
});

// ==================== COMPONENT INITIALIZATION ====================
function initializeComponents() {
    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            fadeOut(alert);
        });
    }, 5000);
}

// ==================== MODAL FUNCTIONS ====================
function initializeModals() {
    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            closeModal();
        }
    });

    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && currentModal) {
            closeModal();
        }
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        currentModal = modal;
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId = null) {
    if (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    } else if (currentModal) {
        currentModal.style.display = 'none';
        currentModal = null;
    }
    document.body.style.overflow = 'auto';
}

// ==================== FORM FUNCTIONS ====================
function initializeForms() {
    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'Bu alan zorunludur');
            isValid = false;
        } else {
            clearFieldError(field);
        }
    });
    
    return isValid;
}

function showFieldError(field, message) {
    clearFieldError(field);
    field.style.borderColor = '#e74c3c';
    
    const error = document.createElement('div');
    error.className = 'field-error';
    error.style.color = '#e74c3c';
    error.style.fontSize = '12px';
    error.style.marginTop = '5px';
    error.textContent = message;
    
    field.parentNode.appendChild(error);
}

function clearFieldError(field) {
    field.style.borderColor = '';
    const error = field.parentNode.querySelector('.field-error');
    if (error) {
        error.remove();
    }
}

// ==================== DELETE CONFIRMATION ====================
function initializeDeleteButtons() {
    const deleteButtons = document.querySelectorAll('[data-delete]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const message = this.getAttribute('data-message') || 'Bu kaydı silmek istediğinizden emin misiniz?';
            if (confirm(message)) {
                window.location.href = this.href;
            }
        });
    });
}

// ==================== ALERT FUNCTIONS ====================
function initializeAlerts() {
    // Close button for alerts
    const closeButtons = document.querySelectorAll('.alert .close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            fadeOut(this.parentElement);
        });
    });
}

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `
        <i class="fas fa-${getAlertIcon(type)}"></i>
        ${message}
    `;
    
    const content = document.querySelector('.content');
    if (content) {
        content.insertBefore(alertDiv, content.firstChild);
        
        setTimeout(() => {
            fadeOut(alertDiv);
        }, 5000);
    }
}

function getAlertIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || icons.info;
}

// ==================== UTILITY FUNCTIONS ====================
function fadeOut(element) {
    element.style.transition = 'opacity 0.5s ease';
    element.style.opacity = '0';
    setTimeout(() => {
        element.remove();
    }, 500);
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showAlert('Panoya kopyalandı!', 'success');
    }).catch(() => {
        showAlert('Kopyalama başarısız!', 'error');
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('tr-TR');
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('tr-TR');
}

// ==================== TABLE FUNCTIONS ====================
function initializeTableSearch(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    
    if (input && table) {
        input.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
}

function sortTable(tableId, columnIndex) {
    const table = document.getElementById(tableId);
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aText = a.cells[columnIndex].textContent.trim();
        const bText = b.cells[columnIndex].textContent.trim();
        return aText.localeCompare(bText);
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

// ==================== ETKINLIK FUNCTIONS ====================
function toggleEtkinlikDurum(etkinlikId, aktif) {
    if (confirm('Etkinlik durumunu değiştirmek istediğinizden emin misiniz?')) {
        fetch('ajax/toggle_etkinlik.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                etkinlik_id: etkinlikId,
                aktif: aktif
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            showAlert('Bir hata oluştu!', 'error');
        });
    }
}

// ==================== EXPORT FUNCTIONS ====================
function exportToExcel(tableId, filename = 'export') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    // Basic CSV export
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = Array.from(cols).map(col => col.textContent.trim());
        csv.push(rowData.join(','));
    });
    
    const csvString = csv.join('\n');
    const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', filename + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// ==================== LOADING FUNCTIONS ====================
function showLoading(message = 'Yükleniyor...') {
    const loading = document.createElement('div');
    loading.id = 'loading-overlay';
    loading.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 99999;
    `;
    loading.innerHTML = `
        <div style="text-align: center; color: white;">
            <div class="spinner"></div>
            <p style="margin-top: 20px;">${message}</p>
        </div>
    `;
    document.body.appendChild(loading);
}

function hideLoading() {
    const loading = document.getElementById('loading-overlay');
    if (loading) {
        loading.remove();
    }
}

// ==================== CONFIRMATION ====================
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// ==================== AJAX HELPERS ====================
async function ajaxPost(url, data) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        return await response.json();
    } catch (error) {
        console.error('AJAX Error:', error);
        return { success: false, message: 'Bağlantı hatası' };
    }
}

async function ajaxGet(url) {
    try {
        const response = await fetch(url);
        return await response.json();
    } catch (error) {
        console.error('AJAX Error:', error);
        return { success: false, message: 'Bağlantı hatası' };
    }
}

// ==================== SIDEBAR TOGGLE (Mobile) ====================
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('active');
}

// ==================== AUTO SAVE ====================
function autoSave(formId, url, interval = 30000) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    setInterval(() => {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        ajaxPost(url, data).then(response => {
            if (response.success) {
                console.log('Auto-saved');
            }
        });
    }, interval);
}

// ==================== CONSOLE INFO ====================
console.log('%c🤖 YZ Kulübü Admin Panel', 'color: #667eea; font-size: 24px; font-weight: bold;');
console.log('%cPanel JS Yüklendi ✓', 'color: #00b894; font-size: 14px;');
