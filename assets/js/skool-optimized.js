/* ECIM Skool - JavaScript Optimizado */
(function($) {
  'use strict';
  
  // Cache de elementos DOM para mejor rendimiento
  const DOM_CACHE = {};
  
  function getElement(selector) {
    if (!DOM_CACHE[selector]) {
      DOM_CACHE[selector] = $(selector);
    }
    return DOM_CACHE[selector];
  }
  
  // Debounce para optimizar eventos
  function debounce(func, wait) {
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
  
  // Lazy loading para imágenes
  function initLazyLoading() {
    if ('IntersectionObserver' in window) {
      const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src;
            img.classList.remove('lazy-load');
            img.classList.add('loaded');
            observer.unobserve(img);
          }
        });
      });
      
      document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
      });
    }
  }
  
  // Preload crítico de recursos
  function preloadCriticalResources() {
    const criticalResources = [
      ECIMSK.ajaxurl,
      // Agregar más recursos críticos aquí
    ];
    
    criticalResources.forEach(url => {
      if (url) {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.as = 'fetch';
        link.href = url;
        document.head.appendChild(link);
      }
    });
  }
  
  // Optimización de AJAX con cache
  const ajaxCache = new Map();
  
  function optimizedAjax(options) {
    const cacheKey = JSON.stringify(options.data);
    
    if (options.cache && ajaxCache.has(cacheKey)) {
      return Promise.resolve(ajaxCache.get(cacheKey));
    }
    
    return $.ajax(options).then(response => {
      if (options.cache) {
        ajaxCache.set(cacheKey, response);
      }
      return response;
    });
  }
  
  // Modal optimizado
  function openModal() {
    const modal = getElement('#sk-modal');
    modal.addClass('is-open').attr('aria-hidden', 'false');
    document.body.style.overflow = 'hidden'; // Prevenir scroll
  }
  
  function closeModal() {
    const modal = getElement('#sk-modal');
    modal.removeClass('is-open').attr('aria-hidden', 'true');
    document.body.style.overflow = ''; // Restaurar scroll
  }
  
  // Eventos optimizados con delegación
  $(document).ready(function() {
    // Inicializar optimizaciones
    initLazyLoading();
    preloadCriticalResources();
    
    // Modal events (delegación)
    $(document).on('click', '.sk-open-register', function(e) {
      e.preventDefault();
      openModal();
    });
    
    $(document).on('click', '#sk-close-modal', closeModal);
    
    $(document).on('click', '#sk-modal', function(e) {
      if (e.target === this) closeModal();
    });
    
    // Tabs optimizados
    $(document).on('click', '.sk-tab', function() {
      const $this = $(this);
      const tab = $this.data('tab');
      
      // Usar requestAnimationFrame para mejor rendimiento
      requestAnimationFrame(() => {
        getElement('.sk-tab').removeClass('sk-tab--active');
        getElement('.sk-tabpanel').removeClass('sk-tabpanel--active');
        
        $this.addClass('sk-tab--active');
        getElement(`#sk-tab-${tab}`).addClass('sk-tabpanel--active');
      });
    });
    
    // Botón copiar optimizado
    $(document).on('click', '.copy-btn', function() {
      const $btn = $(this);
      const value = $btn.data('copy') || '';
      
      if (!value) return;
      
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(String(value)).then(() => {
          $btn.text('✓ Copiado');
          setTimeout(() => $btn.text('Copiar'), 1600);
        }).catch(() => {
          // Fallback para navegadores antiguos
          fallbackCopyText(value, $btn);
        });
      } else {
        fallbackCopyText(value, $btn);
      }
    });
    
    function fallbackCopyText(text, $btn) {
      const textArea = document.createElement('textarea');
      textArea.value = text;
      textArea.style.position = 'fixed';
      textArea.style.opacity = '0';
      document.body.appendChild(textArea);
      textArea.select();
      
      try {
        document.execCommand('copy');
        $btn.text('✓ Copiado');
        setTimeout(() => $btn.text('Copiar'), 1600);
      } catch (err) {
        console.warn('Fallback copy failed:', err);
      }
      
      document.body.removeChild(textArea);
    }
    
    // QR Modal optimizado
    $(document).on('click', '.qr-zoom', function() {
      const src = $(this).data('src');
      if (!src) return;
      
      const modal = getElement('#qr-modal');
      const img = getElement('#qr-modal-img');
      
      img.attr('src', src);
      modal.attr('aria-hidden', 'false').addClass('is-open');
      document.body.style.overflow = 'hidden';
    });
    
    $(document).on('click', '.qr-close, #qr-modal', function(e) {
      if (e.target !== this && !$(e.target).hasClass('qr-close')) return;
      
      const modal = getElement('#qr-modal');
      modal.attr('aria-hidden', 'true').removeClass('is-open');
      getElement('#qr-modal-img').attr('src', '');
      document.body.style.overflow = '';
    });
    
    // Formularios AJAX optimizados
    $(document).on('submit', '#yape-proof-form', function(e) {
      e.preventDefault();
      
      const $form = $(this);
      const $msg = getElement('#yape-proof-msg');
      
      if (!ECIMSK.ajaxurl) {
        $msg.css('color', '#b91c1c').text('AJAX no disponible.');
        return;
      }
      
      $msg.text('Subiendo...');
      
      const formData = new FormData(this);
      formData.append('action', 'ecimsk_upload_voucher');
      formData.append('payment_method', 'yape');
      formData.append('nonce', ECIMSK.nonce);
      
      // Usar fetch optimizado
      fetch(ECIMSK.ajaxurl, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          $msg.css('color', 'green').text('¡Comprobante enviado! Te avisaremos cuando se apruebe.');
          $form[0].reset();
        } else {
          const message = (data.data && data.data.message) || 'No pudimos subir el archivo.';
          $msg.css('color', '#b91c1c').text(message);
        }
      })
      .catch(error => {
        console.error('Error AJAX:', error);
        $msg.css('color', '#b91c1c').text('Error de red. Intenta de nuevo.');
      });
    });
    
    // Registro optimizado
    $(document).on('submit', '#sk-register-form, #sk-register-form-modal', function(e) {
      e.preventDefault();
      
      const $form = $(this);
      
      if (!ECIMSK.ajaxurl || !ECIMSK.nonce) {
        alert('Error de configuración: Variables AJAX no disponibles.');
        return;
      }
      
      const formData = {
        action: 'ecimsk_register',
        nonce: ECIMSK.nonce,
        first_name: $form.find('input[name="first_name"]').val() || '',
        last_name: $form.find('input[name="last_name"]').val() || '',
        email: $form.find('input[name="email"]').val() || '',
        password: $form.find('input[name="password"]').val() || ''
      };
      
      optimizedAjax({
        url: ECIMSK.ajaxurl,
        type: 'POST',
        data: formData,
        dataType: 'json',
        timeout: 10000
      }).then(res => {
        if (res && res.success) {
          if (res.data && res.data.redirect) {
            window.location.href = res.data.redirect;
          } else {
            location.reload();
          }
        } else {
          const errorMsg = (res && res.data && res.data.message) || 'No pudimos completar el registro.';
          alert(errorMsg);
        }
      }).catch((xhr, status, error) => {
        console.error('Error AJAX:', status, error);
        const message = status === 'timeout' 
          ? 'La conexión tardó demasiado. Intenta de nuevo.'
          : 'Error de conexión. Verifica tu internet y vuelve a intentar.';
        alert(message);
      });
    });
    
    // Login optimizado
    $(document).on('submit', '#sk-login-form, #sk-login-form-modal', function(e) {
      e.preventDefault();
      
      const $form = $(this);
      
      if (!ECIMSK.ajaxurl || !ECIMSK.nonce) {
        alert('Error de configuración: Variables AJAX no disponibles.');
        return;
      }
      
      const formData = {
        action: 'ecimsk_login',
        nonce: ECIMSK.nonce,
        email: $form.find('input[name="email"]').val() || '',
        password: $form.find('input[name="password"]').val() || ''
      };
      
      optimizedAjax({
        url: ECIMSK.ajaxurl,
        type: 'POST',
        data: formData,
        dataType: 'json',
        timeout: 10000
      }).then(res => {
        if (res && res.success) {
          if (res.data && res.data.redirect) {
            window.location.href = res.data.redirect;
          } else {
            location.reload();
          }
        } else {
          const errorMsg = (res && res.data && res.data.message) || 'No pudimos iniciar sesión.';
          alert(errorMsg);
        }
      }).catch((xhr, status, error) => {
        console.error('Error AJAX:', status, error);
        const message = status === 'timeout'
          ? 'La conexión tardó demasiado. Intenta de nuevo.'
          : 'Error de conexión. Verifica tu internet y vuelve a intentar.';
        alert(message);
      });
    });
    
    // Ripple effect optimizado con requestAnimationFrame
    $(document).on('click', '.sk-btn', function(e) {
      const $btn = $(this);
      const offset = $btn.offset() || { left: 0, top: 0 };
      const x = e.pageX - offset.left;
      const y = e.pageY - offset.top;
      
      const $ripple = $('<span class="ripple"></span>').css({
        left: x,
        top: y
      });
      
      $btn.append($ripple);
      
      // Usar requestAnimationFrame para mejor rendimiento
      requestAnimationFrame(() => {
        setTimeout(() => $ripple.remove(), 650);
      });
    });
    
    // Contador de progreso optimizado
    $('.mod-card').each(function() {
      const $card = $(this);
      const $bar = $card.find('.progress > span');
      
      if (!$bar.length) return;
      
      const match = ($bar.attr('style') || '').match(/width:\s*([\d.]+)%/i);
      if (!match) return;
      
      const target = Math.round(parseFloat(match[1]));
      const $val = $('<div class="progress-val">0%</div>');
      $card.find('.mod-body').append($val);
      
      // Usar requestAnimationFrame para animación suave
      let current = 0;
      const increment = target / 60; // 60 frames para 1 segundo
      
      function animate() {
        current += increment;
        if (current >= target) {
          current = target;
          $val.text(`${target}%`);
          return;
        }
        
        $val.text(`${Math.round(current)}%`);
        requestAnimationFrame(animate);
      }
      
      // Iniciar animación cuando el elemento sea visible
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            animate();
            observer.unobserve(entry.target);
          }
        });
      });
      
      observer.observe($card[0]);
    });
  });
  
  // Optimización para reducir repaints
  $(window).on('scroll', debounce(function() {
    // Lógica de scroll optimizada aquí si es necesaria
  }, 16)); // ~60fps
  
  // Optimización para resize
  $(window).on('resize', debounce(function() {
    // Limpiar cache de elementos DOM en resize
    Object.keys(DOM_CACHE).forEach(key => delete DOM_CACHE[key]);
  }, 250));
  
})(jQuery);

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
  // Optimizaciones adicionales aquí
  
  // Preload de fuentes críticas
  const fontPreload = document.createElement('link');
  fontPreload.rel = 'preload';
  fontPreload.as = 'font';
  fontPreload.type = 'font/woff2';
  fontPreload.href = 'https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap';
  fontPreload.crossOrigin = 'anonymous';
  document.head.appendChild(fontPreload);
});
