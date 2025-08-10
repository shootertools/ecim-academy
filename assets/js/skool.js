/* Skool WP Theme – JS global */
(function($){

  /* ===== Utilidades seguras ===== */
  const ECIM = window.ECIMSK || {};
  const AJAXURL = ECIM.ajaxurl || '';   // si no se localizó, se queda vacío (evita errores)
  const NONCE   = ECIM.nonce   || '';

  /* ===== Función de depuración ===== */
  function debugAjaxConfig(){
    console.log('=== DEBUG ECIM SKOOL AJAX ===');
    console.log('window.ECIMSK:', window.ECIMSK);
    console.log('AJAXURL:', AJAXURL);
    console.log('NONCE:', NONCE);
    console.log('jQuery version:', $.fn.jquery);
    console.log('============================');
  }
  
  // Ejecutar debug al cargar la página
  $(document).ready(function(){
    debugAjaxConfig();
    
    // Botón de prueba removido - debugging completado
  });
  
  // Función de prueba AJAX
  $(document).on('click','#test-ajax-btn',function(){
    console.log('=== PRUEBA AJAX ===');
    $.ajax({
      url: AJAXURL,
      type: 'POST',
      data: {
        action: 'ecimsk_test',
        nonce: NONCE
      },
      dataType: 'json',
      timeout: 5000
    }).done(function(res){
      console.log('✅ PRUEBA EXITOSA:', res);
      alert('✅ AJAX funciona! Mensaje: ' + (res.data ? res.data.message : 'Sin mensaje'));
    }).fail(function(xhr, status, error){
      console.error('❌ PRUEBA FALLÓ:', status, error, xhr.responseText);
      alert('❌ AJAX falló: ' + status + ' - ' + error);
    });
  });

  /* ===== Modal Registro/Login ===== */
  function openModal(){ $('#sk-modal').addClass('is-open').attr('aria-hidden','false'); }
  function closeModal(){ $('#sk-modal').removeClass('is-open').attr('aria-hidden','true'); }
  $(document).on('click','.sk-open-register',function(e){ e.preventDefault(); openModal(); });
  $(document).on('click','#sk-close-modal',function(){ closeModal(); });
  $(document).on('click','#sk-modal',function(e){ if(e.target===this) closeModal(); });

  // Tabs
  $(document).on('click','.sk-tab',function(){
    const tab = $(this).data('tab');
    $('.sk-tab').removeClass('sk-tab--active'); $(this).addClass('sk-tab--active');
    $('.sk-tabpanel').removeClass('sk-tabpanel--active');
    $('#sk-tab-'+tab).addClass('sk-tabpanel--active');
  });
  $(document).on('click','.sk-switch-login',function(e){ e.preventDefault(); $('.sk-tab[data-tab="login"]').trigger('click'); });
  $(document).on('click','.sk-switch-register',function(e){ e.preventDefault(); $('.sk-tab[data-tab="register"]').trigger('click'); });

  // Envío AJAX para registro con mejor manejo de errores
  $(document).on('submit','#sk-register-form, #sk-register-form-modal',function(e){
    e.preventDefault();
    
    // Validación de variables AJAX
    if(!AJAXURL || !NONCE){ 
      alert('Error de configuración: Variables AJAX no disponibles. Contacta al administrador.'); 
      return; 
    }
    
    // Recopilar datos del formulario manualmente
    const formData = {
      action: 'ecimsk_register',
      nonce: NONCE,
      first_name: $(this).find('input[name="first_name"]').val() || '',
      last_name: $(this).find('input[name="last_name"]').val() || '',
      email: $(this).find('input[name="email"]').val() || '',
      password: $(this).find('input[name="password"]').val() || ''
    };
    
    console.log('Datos a enviar:', formData);
    
    $.ajax({
      url: AJAXURL,
      type: 'POST',
      data: formData,
      dataType: 'json',
      timeout: 10000
    }).done(function(res){
      if(res && res.success){ 
        if(res.data && res.data.redirect){
          window.location.href = res.data.redirect;
        } else {
          location.reload(); 
        }
      } else { 
        const errorMsg = (res && res.data && res.data.message) || 'No pudimos completar el registro.';
        alert(errorMsg); 
      }
    }).fail(function(xhr, status, error){
      console.error('Error AJAX:', status, error, xhr.responseText);
      if(status === 'timeout'){
        alert('Error: La conexión tardó demasiado. Intenta de nuevo.');
      } else {
        alert('Error de conexión. Verifica tu internet y vuelve a intentar.');
      }
    });
  });
  $(document).on('submit','#sk-login-form, #sk-login-form-modal',function(e){
    e.preventDefault();
    
    // Validación de variables AJAX
    if(!AJAXURL || !NONCE){ 
      alert('Error de configuración: Variables AJAX no disponibles. Contacta al administrador.'); 
      return; 
    }
    
    // Recopilar datos del formulario manualmente
    const formData = {
      action: 'ecimsk_login',
      nonce: NONCE,
      email: $(this).find('input[name="email"]').val() || '',
      password: $(this).find('input[name="password"]').val() || ''
    };
    
    console.log('Datos de login a enviar:', formData);
    
    $.ajax({
      url: AJAXURL,
      type: 'POST',
      data: formData,
      dataType: 'json',
      timeout: 10000
    }).done(function(res){
      if(res && res.success){ 
        if(res.data && res.data.redirect){
          window.location.href = res.data.redirect;
        } else {
          location.reload(); 
        }
      } else { 
        const errorMsg = (res && res.data && res.data.message) || 'No pudimos iniciar sesión.';
        alert(errorMsg); 
      }
    }).fail(function(xhr, status, error){
      console.error('Error AJAX:', status, error, xhr.responseText);
      if(status === 'timeout'){
        alert('Error: La conexión tardó demasiado. Intenta de nuevo.');
      } else {
        alert('Error de conexión. Verifica tu internet y vuelve a intentar.');
      }
    });
  });

  /* ===== Paywall 2025: copiar y modal QR ===== */
  $(document).on('click','.btn-copy',function(){
    const v=$(this).data('copy')||''; if(!v) return;
    navigator.clipboard.writeText(String(v)).then(()=>{ $(this).text('Copiado'); setTimeout(()=>$(this).text('Copiar'),1600); });
  });
  $(document).on('click','.qr-zoom',function(){
    const src=$(this).data('src'); if(!src) return;
    $('#qr-modal-img').attr('src',src); $('#qr-modal').attr('aria-hidden','false').addClass('is-open');
  });
  $(document).on('click','.qr-close,#qr-modal',function(e){
    if(e.target!==this && !$(e.target).hasClass('qr-close')) return;
    $('#qr-modal').attr('aria-hidden','true').removeClass('is-open'); $('#qr-modal-img').attr('src','');
  });

  /* ===== Subida de comprobante Yape (AJAX) ===== */
  $(document).on('submit','#yape-proof-form',function(e){
    e.preventDefault();
    const $msg = $('#yape-proof-msg');
    if(!ECIMSK.ajaxurl){ $msg.css('color','#b91c1c').text('AJAX no disponible.'); return; }
    $msg.text('Subiendo...');
    const fd = new FormData(this);
    fd.append('action', 'ecimsk_upload_voucher');
    fd.append('payment_method', 'yape');
    fd.append('nonce', ECIMSK.nonce); // Usando la variable global correcta
    $.ajax({ url: ECIMSK.ajaxurl, method:'POST', data:fd, processData:false, contentType:false })
     .done(function(res){
        if(res && res.success){
          $msg.css('color','green').text('¡Comprobante enviado! Te avisaremos cuando se apruebe.');
          $('#yape-proof-form')[0].reset();
        }else{
          const m=(res && res.data && res.data.message) ? res.data.message : 'No pudimos subir el archivo.';
          $msg.css('color','#b91c1c').text(m);
        }
     })
     .fail(function(){ $msg.css('color','#b91c1c').text('Error de red.'); });
  });

})(jQuery);

/* ==== VIDA 100% – JS (tilt, reveal, ripple, contador) ==== */
jQuery(function($){

  // Marcamos los grids como "reveal-ready" para que el CSS oculte sólo si el JS corre
  $('.mod-grid').addClass('reveal-ready');

  // Reveal on scroll con fallback
  const cards = $('.mod-card').toArray();
  if ('IntersectionObserver' in window){
    const io = new IntersectionObserver((entries)=>{
      entries.forEach(e=>{
        if(e.isIntersecting){
          e.target.classList.add('is-in');
          io.unobserve(e.target);
        }
      });
    }, {threshold:.12});
    cards.forEach(el=>io.observe(el));
  } else {
    $('.mod-card').addClass('is-in');
  }

  // Tilt/parallax suave (desktop)
  const isTouch = 'ontouchstart' in window || navigator.maxTouchPoints>0;
  if(!isTouch){
    $(document).on('mousemove','.mod-card',function(e){
      const $el = $(this);
      $el.attr('data-tilt','1');
      const rect = this.getBoundingClientRect();
      const cx = rect.left + rect.width/2;
      const cy = rect.top + rect.height/2;
      const dx = (e.clientX - cx)/rect.width;
      const dy = (e.clientY - cy)/rect.height;
      const rx = (dy * -6).toFixed(2);
      const ry = (dx *  6).toFixed(2);
      $el.css('transform',`translateY(-4px) rotateX(${rx}deg) rotateY(${ry}deg)`);
    }).on('mouseleave','.mod-card',function(){
      $(this).css('transform','').removeAttr('data-tilt');
    });
  }

  // Ripple en botones
  $(document).on('click','.sk-btn',function(e){
    const $btn = $(this);
    const off  = $btn.offset() || {left:0,top:0};
    const x    = e.pageX - off.left, y = e.pageY - off.top;
    const $r   = $('<span class="ripple"></span>').css({left:x, top:y});
    $btn.append($r);
    setTimeout(()=> $r.remove(), 650);
  });

  // Contador de progreso (0 -> %)
  $('.mod-card').each(function(){
    const $bar = $(this).find('.progress > span');
    if(!$bar.length) return;
    const m = ($bar.attr('style')||'').match(/width:\s*([\d.]+)%/i);
    if(!m) return;
    const target = Math.round(parseFloat(m[1]));
    const $val = $('<div class="progress-val">0%</div>');
    $(this).find('.mod-body').append($val);
    let now = 0;
    const step = () => {
      now += Math.max(1, Math.ceil(target/24));
      if(now >= target){ now = target; }
      $val.text(now + '%');
      if(now < target) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  });

});
