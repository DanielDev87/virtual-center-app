// Virtual Center - Custom JavaScript

$(document).ready(function () {
    // Theme Toggle Functionality
    initializeThemeToggle();

    // Initialize tooltips and popovers
    initializeBootstrapComponents();

    // Initialize AJAX setup
    initializeAjax();

    // Initialize animations
    initializeAnimations();

    // Initialize search functionality
    initializeSearch();
});

/**
 * Initialize theme toggle functionality
 */
function initializeThemeToggle() {
    const themeToggle = $('#themeToggle');
    const themeIcon = $('#themeIcon');
    const html = $('html');

    // Sync theme between session (via attribute) and localStorage
    let currentTheme = localStorage.getItem('theme');
    const sessionTheme = $('html').attr('data-bs-theme');

    // If we have a theme in localStorage but session is default/different, sync them
    if (currentTheme && currentTheme !== sessionTheme) {
        $('html').attr('data-bs-theme', currentTheme);
    } else if (!currentTheme && sessionTheme) {
        currentTheme = sessionTheme;
        localStorage.setItem('theme', currentTheme);
    } else if (!currentTheme && !sessionTheme) {
        currentTheme = 'light';
        localStorage.setItem('theme', currentTheme);
    }

    // Update icon based on final theme
    updateThemeIcon(currentTheme);

    // Theme toggle click handler
    themeToggle.on('click', function () {
        currentTheme = currentTheme === 'light' ? 'dark' : 'light';

        // Update HTML attribute
        html.attr('data-bs-theme', currentTheme);

        // Update icon
        updateThemeIcon(currentTheme);

        // Save theme preference
        saveThemePreference(currentTheme);

        // Add animation
        $(this).addClass('animate__animated animate__pulse');
        setTimeout(() => {
            $(this).removeClass('animate__animated animate__pulse');
        }, 600);
    });

    function updateThemeIcon(theme) {
        if (theme === 'dark') {
            themeIcon.removeClass('fa-moon').addClass('fa-sun');
        } else {
            themeIcon.removeClass('fa-sun').addClass('fa-moon');
        }
    }

    function saveThemePreference(theme) {
        // Save to localStorage
        localStorage.setItem('theme', theme);

        // Send to server to save in session
        $.ajax({
            url: '/ajax/theme',
            method: 'POST',
            data: {
                theme: theme,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                console.log('Theme saved to session successfully');
            },
            error: function (xhr, status, error) {
                console.error('Error saving theme to session:', error);
            }
        });
    }
}

/**
 * Initialize Bootstrap components
 */
function initializeBootstrapComponents() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Initialize modals
    $('.modal').on('show.bs.modal', function () {
        $(this).find('.modal-content').addClass('fade-in');
    });
}

/**
 * Initialize AJAX setup
 */
function initializeAjax() {
    // Set up CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Global AJAX error handler
    $(document).ajaxError(function (event, xhr, settings, thrownError) {
        console.error('AJAX Error:', thrownError);

        if (xhr.status === 419) {
            // CSRF token mismatch
            showAlert('Tu sesión ha expirado. Por favor, recarga la página.', 'warning');
        } else if (xhr.status === 500) {
            // Server error
            showAlert('Ha ocurrido un error en el servidor. Por favor, intenta nuevamente.', 'danger');
        }
    });
}

/**
 * Initialize animations
 */
function initializeAnimations() {
    // Add fade-in animation to cards on scroll
    $(window).on('scroll', function () {
        $('.card').each(function () {
            const elementTop = $(this).offset().top;
            const elementBottom = elementTop + $(this).outerHeight();
            const viewportTop = $(window).scrollTop();
            const viewportBottom = viewportTop + $(window).height();

            if (elementBottom > viewportTop && elementTop < viewportBottom) {
                $(this).addClass('fade-in');
            }
        });
    });

    // Add hover effects to buttons
    $('.btn').on('mouseenter', function () {
        $(this).addClass('animate__animated animate__pulse');
    }).on('mouseleave', function () {
        $(this).removeClass('animate__animated animate__pulse');
    });
}

/**
 * Initialize search functionality
 */
function initializeSearch() {
    const searchInput = $('#searchInput');
    const searchResults = $('#searchResults');

    if (searchInput.length && searchResults.length) {
        let searchTimeout;

        searchInput.on('input', function () {
            const query = $(this).val().trim();

            clearTimeout(searchTimeout);

            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    performSearch(query);
                }, 300);
            } else {
                searchResults.hide();
            }
        });
    }
}

/**
 * Perform search
 */
function performSearch(query) {
    $.ajax({
        url: '/ajax/search',
        method: 'GET',
        data: { q: query },
        success: function (response) {
            displaySearchResults(response);
        },
        error: function (xhr, status, error) {
            console.error('Search error:', error);
        }
    });
}

/**
 * Display search results
 */
function displaySearchResults(results) {
    const searchResults = $('#searchResults');

    if (results.length === 0) {
        searchResults.html('<div class="p-3 text-muted">No se encontraron resultados</div>').show();
        return;
    }

    let html = '<div class="list-group">';
    results.forEach(function (item) {
        html += `
            <a href="/projects/${item.tracking_id}" class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${item.project_name}</h6>
                    <small class="text-muted">${item.project_status}</small>
                </div>
                <p class="mb-1">${item.project_description}</p>
                <small class="text-muted">${item.institution.institution_name}</small>
            </a>
        `;
    });
    html += '</div>';

    searchResults.html(html).show();
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info', duration = 5000) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    $('body').append(alertHtml);

    // Auto dismiss after duration
    setTimeout(function () {
        $('.alert').alert('close');
    }, duration);
}

/**
 * Show loading spinner
 */
function showLoading(element) {
    const spinner = '<div class="spinner-border spinner-border-sm me-2" role="status"></div>';
    $(element).prepend(spinner).prop('disabled', true);
}

/**
 * Hide loading spinner
 */
function hideLoading(element) {
    $(element).find('.spinner-border').remove();
    $(element).prop('disabled', false);
}

/**
 * Format date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

/**
 * Format datetime
 */
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Confirm action
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function () {
        showAlert('Copiado al portapapeles', 'success', 2000);
    }).catch(function (err) {
        console.error('Error copying to clipboard:', err);
        showAlert('Error al copiar al portapapeles', 'danger');
    });
}

// Export functions for global use
window.VirtualCenter = {
    showAlert,
    showLoading,
    hideLoading,
    formatDate,
    formatDateTime,
    confirmAction,
    copyToClipboard
};

// Initialize DataTable for tables with data-table class
$(document).ready(function () {
    $('.data-table').each(function () {
        new DataTable(this);
    });
});

