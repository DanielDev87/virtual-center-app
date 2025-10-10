/**
 * Virtual Center - Main JavaScript Library
 * Provides common functionality for the Virtual Center application
 */

class VirtualCenter {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeComponents();
    }

    setupEventListeners() {
        // Theme toggle
        $(document).on('click', '[data-theme-toggle]', this.toggleTheme.bind(this));
        
        // Form validation
        $(document).on('submit', 'form', this.handleFormSubmit.bind(this));
        
        // Auto-hide alerts
        $(document).on('DOMNodeInserted', '.alert', this.autoHideAlerts.bind(this));
    }

    initializeComponents() {
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        // Initialize popovers
        $('[data-bs-toggle="popover"]').popover();
        
        // Initialize modals
        $('.modal').modal();
        
        // Initialize dropdowns
        $('.dropdown-toggle').dropdown();
    }

    // Theme Management
    toggleTheme() {
        const currentTheme = localStorage.getItem('theme') || 'light';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        localStorage.setItem('theme', newTheme);
        document.documentElement.setAttribute('data-theme', newTheme);
        
        this.showAlert(`Tema cambiado a ${newTheme === 'light' ? 'claro' : 'oscuro'}`, 'info', 2000);
    }

    // Alert System
    showAlert(message, type = 'info', duration = 5000) {
        const alertId = 'alert-' + Date.now();
        const alertHtml = `
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-${this.getAlertIcon(type)} me-2"></i>
                    <div class="flex-grow-1">${message}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        $('body').append(alertHtml);
        
        if (duration > 0) {
            setTimeout(() => {
                $(`#${alertId}`).alert('close');
            }, duration);
        }
    }

    getAlertIcon(type) {
        const icons = {
            'success': 'check-circle',
            'danger': 'exclamation-triangle',
            'warning': 'exclamation-circle',
            'info': 'info-circle',
            'primary': 'info-circle',
            'secondary': 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    // Loading Management
    showLoading(element, text = 'Cargando...') {
        const $element = $(element);
        const originalText = $element.html();
        
        $element.data('original-text', originalText);
        $element.html(`
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            ${text}
        `);
        $element.prop('disabled', true);
    }

    hideLoading(element) {
        const $element = $(element);
        const originalText = $element.data('original-text');
        
        if (originalText) {
            $element.html(originalText);
            $element.prop('disabled', false);
        }
    }

    // Form Handling
    handleFormSubmit(event) {
        const $form = $(event.target);
        const $submitBtn = $form.find('button[type="submit"]');
        
        if ($submitBtn.length && !$form.data('no-loading')) {
            this.showLoading($submitBtn);
        }
    }

    // Auto-hide alerts
    autoHideAlerts(event) {
        const $alert = $(event.target);
        if ($alert.hasClass('alert') && !$alert.hasClass('alert-permanent')) {
            setTimeout(() => {
                $alert.alert('close');
            }, 5000);
        }
    }

    // Utility Methods
    formatDate(date, format = 'DD/MM/YYYY') {
        if (!date) return '';
        
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        
        return format.replace('DD', day).replace('MM', month).replace('YYYY', year);
    }

    formatCurrency(amount, currency = 'USD') {
        return new Intl.NumberFormat('es-ES', {
            style: 'currency',
            currency: currency
        }).format(amount);
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    // API Helper
    async apiRequest(url, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        };

        const finalOptions = { ...defaultOptions, ...options };

        try {
            const response = await fetch(url, finalOptions);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Error en la solicitud');
            }

            return data;
        } catch (error) {
            this.showAlert(error.message, 'danger');
            throw error;
        }
    }

    // File Upload Helper
    uploadFile(file, url, progressCallback = null) {
        return new Promise((resolve, reject) => {
            const formData = new FormData();
            formData.append('file', file);

            const xhr = new XMLHttpRequest();

            if (progressCallback) {
                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        progressCallback(percentComplete);
                    }
                });
            }

            xhr.addEventListener('load', () => {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        resolve(response);
                    } catch (e) {
                        reject(new Error('Error al procesar la respuesta'));
                    }
                } else {
                    reject(new Error('Error en la carga del archivo'));
                }
            });

            xhr.addEventListener('error', () => {
                reject(new Error('Error de red'));
            });

            xhr.open('POST', url);
            xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            xhr.send(formData);
        });
    }

    // Validation Helpers
    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    validatePhone(phone) {
        const re = /^[\+]?[1-9][\d]{0,15}$/;
        return re.test(phone.replace(/\s/g, ''));
    }

    validatePassword(password) {
        return {
            length: password.length >= 8,
            hasLower: /[a-z]/.test(password),
            hasUpper: /[A-Z]/.test(password),
            hasNumber: /\d/.test(password),
            hasSpecial: /[^a-zA-Z0-9]/.test(password)
        };
    }

    // Local Storage Helpers
    setStorage(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (e) {
            console.warn('No se pudo guardar en localStorage:', e);
        }
    }

    getStorage(key, defaultValue = null) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : defaultValue;
        } catch (e) {
            console.warn('No se pudo leer de localStorage:', e);
            return defaultValue;
        }
    }

    removeStorage(key) {
        try {
            localStorage.removeItem(key);
        } catch (e) {
            console.warn('No se pudo eliminar de localStorage:', e);
        }
    }

    // Print Helper
    printElement(element) {
        const printWindow = window.open('', '_blank');
        const elementHtml = $(element).html();
        
        printWindow.document.write(`
            <html>
                <head>
                    <title>Imprimir</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        @media print {
                            .no-print { display: none !important; }
                        }
                    </style>
                </head>
                <body>
                    ${elementHtml}
                </body>
            </html>
        `);
        
        printWindow.document.close();
        printWindow.print();
    }
}

// Initialize Virtual Center when DOM is ready
$(document).ready(function() {
    window.VirtualCenter = new VirtualCenter();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = VirtualCenter;
}
