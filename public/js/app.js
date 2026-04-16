/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/css/app.css":
/*!*******************************!*\
  !*** ./resources/css/app.css ***!
  \*******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./resources/js/app.js":
/*!*****************************!*\
  !*** ./resources/js/app.js ***!
  \*****************************/
/***/ (() => {

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
  var themeToggle = $('#themeToggle');
  var themeIcon = $('#themeIcon');
  var html = $('html');

  // Sync theme between session (via attribute) and localStorage
  var currentTheme = localStorage.getItem('theme');
  var sessionTheme = $('html').attr('data-bs-theme');

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
    var _this = this;
    currentTheme = currentTheme === 'light' ? 'dark' : 'light';

    // Update HTML attribute
    html.attr('data-bs-theme', currentTheme);

    // Update icon
    updateThemeIcon(currentTheme);

    // Save theme preference
    saveThemePreference(currentTheme);

    // Add animation
    $(this).addClass('animate__animated animate__pulse');
    setTimeout(function () {
      $(_this).removeClass('animate__animated animate__pulse');
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
      success: function success(response) {
        console.log('Theme saved to session successfully');
      },
      error: function error(xhr, status, _error) {
        console.error('Error saving theme to session:', _error);
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
      var elementTop = $(this).offset().top;
      var elementBottom = elementTop + $(this).outerHeight();
      var viewportTop = $(window).scrollTop();
      var viewportBottom = viewportTop + $(window).height();
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
  var searchInput = $('#searchInput');
  var searchResults = $('#searchResults');
  if (searchInput.length && searchResults.length) {
    var searchTimeout;
    searchInput.on('input', function () {
      var query = $(this).val().trim();
      clearTimeout(searchTimeout);
      if (query.length >= 2) {
        searchTimeout = setTimeout(function () {
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
    data: {
      q: query
    },
    success: function success(response) {
      displaySearchResults(response);
    },
    error: function error(xhr, status, _error2) {
      console.error('Search error:', _error2);
    }
  });
}

/**
 * Display search results
 */
function displaySearchResults(results) {
  var searchResults = $('#searchResults');
  if (results.length === 0) {
    searchResults.html('<div class="p-3 text-muted">No se encontraron resultados</div>').show();
    return;
  }
  var html = '<div class="list-group">';
  results.forEach(function (item) {
    html += "\n            <a href=\"/projects/".concat(item.tracking_id, "\" class=\"list-group-item list-group-item-action\">\n                <div class=\"d-flex w-100 justify-content-between\">\n                    <h6 class=\"mb-1\">").concat(item.project_name, "</h6>\n                    <small class=\"text-muted\">").concat(item.project_status, "</small>\n                </div>\n                <p class=\"mb-1\">").concat(item.project_description, "</p>\n                <small class=\"text-muted\">").concat(item.institution.institution_name, "</small>\n            </a>\n        ");
  });
  html += '</div>';
  searchResults.html(html).show();
}

/**
 * Show alert message
 */
function showAlert(message) {
  var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'info';
  var duration = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 5000;
  var alertHtml = "\n        <div class=\"alert alert-".concat(type, " alert-dismissible fade show position-fixed\" \n             style=\"top: 20px; right: 20px; z-index: 9999; min-width: 300px;\" role=\"alert\">\n            ").concat(message, "\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>\n        </div>\n    ");
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
  var spinner = '<div class="spinner-border spinner-border-sm me-2" role="status"></div>';
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
  var date = new Date(dateString);
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
  var date = new Date(dateString);
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
  })["catch"](function (err) {
    console.error('Error copying to clipboard:', err);
    showAlert('Error al copiar al portapapeles', 'danger');
  });
}

// Export functions for global use
window.VirtualCenter = {
  showAlert: showAlert,
  showLoading: showLoading,
  hideLoading: hideLoading,
  formatDate: formatDate,
  formatDateTime: formatDateTime,
  confirmAction: confirmAction,
  copyToClipboard: copyToClipboard
};

// Initialize DataTable for tables with data-table class
$(document).ready(function () {
  $('.data-table').each(function () {
    new DataTable(this);
  });
});

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"/js/app": 0,
/******/ 			"css/app": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkvirtual_center_app"] = self["webpackChunkvirtual_center_app"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	__webpack_require__.O(undefined, ["css/app"], () => (__webpack_require__("./resources/js/app.js")))
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["css/app"], () => (__webpack_require__("./resources/css/app.css")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;