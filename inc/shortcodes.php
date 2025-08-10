<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ========= BOTÓN REGISTRO ========= */
add_shortcode('sk_register_button', function($atts){
  // No mostrar el botón si el usuario está logueado
  if (is_user_logged_in()) {
    return '';
  }
  
  $a = shortcode_atts(['text'=>'Registrarse','variant'=>'ghost'],$atts);
  $class = ($a['variant']==='primary') ? 'sk-btn sk-btn--primary sk-open-register' : 'sk-btn sk-btn--ghost sk-open-register';
  return '<a href="#" class="'.$class.'">'.esc_html($a['text']).'</a>';
});

/* ========= MODAL LOGIN/REGISTRO ========= */
function ecim_sk_modal(){
  if(is_admin()) return;
  $html = <<<'HTML'
<div id="sk-modal" class="sk-modal" aria-hidden="true">
  <div class="sk-modal__content">
    <button class="sk-modal__close" id="sk-close-modal" aria-label="Cerrar">&times;</button>
    <div class="sk-tabs">
      <button class="sk-tab sk-tab--active" data-tab="register">Crear cuenta</button>
      <button class="sk-tab" data-tab="login">Iniciar sesión</button>
    </div>
    <div class="sk-tabpanel sk-tabpanel--active" id="sk-tab-register">
      <form id="sk-register-form-modal">
        <label>Nombres</label><input type="text" name="first_name" placeholder="Tu nombre" required />
        <label>Apellidos</label><input type="text" name="last_name" placeholder="Tus apellidos" />
        <label>Correo electrónico</label><input type="email" name="email" placeholder="tucorreo@dominio.com" required />
        <label>Contraseña</label><input type="password" name="password" placeholder="Mín. 8 caracteres" required />
        <button type="submit" class="sk-btn sk-btn--primary">Crear tu cuenta</button>
        <p class="sk-legal">Al registrarte aceptas nuestros <a href="#">términos</a> y <a href="#">política de privacidad</a>.</p>
      </form>
      <p class="sk-switch">¿Ya tienes cuenta? <a href="#" class="sk-switch-login">Inicia sesión</a></p>
    </div>
    <div class="sk-tabpanel" id="sk-tab-login">
      <form id="sk-login-form-modal">
        <label>Correo electrónico</label><input type="email" name="email" placeholder="tucorreo@dominio.com" required />
        <label>Contraseña</label><input type="password" name="password" placeholder="Tu contraseña" required />
        <button type="submit" class="sk-btn sk-btn--primary">Entrar</button>
      </form>
      <p class="sk-switch">¿Aún no tienes cuenta? <a href="#" class="sk-switch-register">Crear cuenta</a></p>
    </div>
  </div>
</div>
HTML;
  echo $html;
}
add_action('wp_footer','ecim_sk_modal',20);

/* ========= DASHBOARD (GRID DE MÓDULOS) ========= */
add_shortcode('sk_dashboard', function(){
  if(!is_user_logged_in()) return '<p>Debes iniciar sesión.</p>';
  $uid = get_current_user_id();
  $paid = get_user_meta($uid,'skwp_paid',true)==='yes';
  $enr  = array_map('intval',(array)get_user_meta($uid,'skwp_enrolled_modules',true));

  $modules = get_posts(['post_type'=>'sk_module','numberposts'=>-1,'orderby'=>'menu_order','order'=>'ASC']);
  if(!$modules){ return '<p>Aún no hay módulos.</p>'; }

  ob_start(); ?>
  <div class="container"><h2 class="h2">Tus módulos</h2>
    <div class="mod-grid">
      <?php foreach($modules as $m):
        $can  = $paid || in_array($m->ID, $enr, true);
        $less = ecim_sk_lessons_by_module($m->ID);
        $ids  = wp_list_pluck($less,'ID');
        $completed = array_map('intval',(array)get_user_meta($uid,'skwp_completed_lessons',true));
        $done = count(array_intersect($completed,$ids));
        $total = max(1,count($ids));
        $progress = round(($done/$total)*100);
        $thumb = get_the_post_thumbnail_url($m->ID,'large');
      ?>
      <article class="mod-card">
        <a class="mod-thumb <?php echo $can?'':'is-locked'; ?>" href="<?php echo esc_url( $can ? get_permalink($m->ID) : home_url('/pago') ); ?>">
          <?php if($thumb): ?>
            <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($m->post_title); ?>" loading="lazy" decoding="async" />
          <?php else: ?>
            <div class="thumb-fallback"><?php echo esc_html(get_bloginfo('name')); ?></div>
          <?php endif; ?>
          <?php if(!$can): ?><div class="mod-lock">🔒 Curso privado</div><?php endif; ?>
        </a>
        <div class="mod-body">
          <h3><?php echo esc_html($m->post_title); ?></h3>
          <div class="progress"><span style="width:<?php echo esc_attr($progress); ?>%"></span></div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
  <?php return ob_get_clean();
});

/* ========= PAYWALL SIMPLE (1 columna + comprobante) ========= */

/* BACKUP DEL DISEÑO ORIGINAL - NO BORRAR */
add_shortcode('sk_paywall_simple_original', function(){
  $qr   = trim( get_option('skwp_yape_qr_url','') );
  $yape = esc_html( get_option('skwp_yape_number','') );
  ob_start(); ?>
  <section class="pay-2025">
    <div class="pay-head">
      <h2 class="h2">Tu acceso está pendiente</h2>
      <p>Cuando confirmemos tu pago te daremos acceso a los módulos correspondientes.</p>
    </div>
    <div class="pay-grid onecol">
      <article class="glass yape-card">
        <header><div class="pill pill-yape"></div><h3>Yape (Perú)</h3></header>
        <div class="yape-qr">
          <?php if($qr): ?>
            <button class="qr-zoom" data-src="<?php echo esc_url($qr); ?>" aria-label="Ampliar QR">Ampliar</button>
            <img src="<?php echo esc_url($qr); ?>" alt="QR de Yape" loading="lazy" decoding="async" />
          <?php else: ?>
            <div class="qr-placeholder">QR no configurado<br><small>Ve a Skool Theme → Ajustes → Yape</small></div>
          <?php endif; ?>
        </div>
        <div class="yape-meta" style="margin-top:.75rem">
          <div class="meta-line">
            <span class="label">Alias / Número</span>
            <span class="value"><?php echo $yape ? esc_html($yape) : '—'; ?></span>
            <?php if($yape): ?><button class="sk-btn btn-copy" data-copy="<?php echo esc_attr($yape); ?>">Copiar</button><?php endif; ?>
          </div>
          <small class="muted">Adjunta tu comprobante aquí. Te habilitaremos manualmente.</small>
        </div>
        <?php if(is_user_logged_in()): ?>
        <form id="yape-proof-form" enctype="multipart/form-data" style="margin-top:.75rem">
          <div class="fileline" style="display:flex;gap:.5rem;flex-wrap:wrap">
            <input type="file" name="voucher" accept="image/*,application/pdf" required style="flex:1;min-width:240px">
            <input type="text" name="note" placeholder="Nota u observación (opcional)" style="flex:1;min-width:240px">
          </div>
          <button type="submit" class="sk-btn sk-btn--primary" style="margin-top:.6rem">Enviar comprobante</button>
          <small class="muted" id="yape-proof-msg" style="display:block;margin-top:.35rem"></small>
        </form>
        <?php else: ?>
          <p class="muted">Inicia sesión para adjuntar tu comprobante.</p>
        <?php endif; ?>
      </article>

      <article class="glass">
        <header><div class="pill pill-dark"></div><h3>Tarjeta</h3></header>
        <p>Pago seguro con Stripe. Acceso completo cuando el administrador confirme.</p>
        <button id="sk-pay-card" class="sk-btn sk-btn--primary">Pagar con tarjeta</button>
        <small class="muted">Configura tu <strong>PRICE_ID</strong> en Skool Theme → Ajustes.</small>
      </article>
    </div>

    <div class="qr-modal" id="qr-modal" aria-hidden="true">
      <button class="qr-close" aria-label="Cerrar">&times;</button>
      <img id="qr-modal-img" src="" alt="QR" />
    </div>
  </section>
  <?php return ob_get_clean();
});

/* NUEVO DISEÑO MODERNO */
add_shortcode('sk_paywall_simple', function(){
  $qr   = trim( get_option('skwp_yape_qr_url','') );
  $yape = esc_html( get_option('skwp_yape_number','') );
  ob_start(); ?>
  
  <style>
    .modern-paywall {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 2rem 0;
      position: relative;
      overflow: hidden;
    }
    .modern-paywall::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
      pointer-events: none;
    }
    .paywall-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 1rem;
      position: relative;
      z-index: 1;
    }
    .paywall-hero {
      text-align: center;
      color: white;
      margin-bottom: 3rem;
      animation: fadeInUp 0.8s ease-out;
    }
    .paywall-hero h1 {
      font-size: 3rem;
      font-weight: 800;
      margin-bottom: 1rem;
      background: linear-gradient(45deg, #fff, #e0e7ff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .paywall-hero p {
      font-size: 1.2rem;
      opacity: 0.9;
      max-width: 600px;
      margin: 0 auto;
    }
    .payment-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
      gap: 2rem;
      margin-bottom: 2rem;
    }
    .payment-card {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 20px;
      padding: 2rem;
      transition: all 0.3s ease;
      animation: fadeInUp 0.8s ease-out;
      position: relative;
      overflow: hidden;
    }
    .payment-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    }
    .payment-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1);
    }
    .card-header {
      display: flex;
      align-items: center;
      margin-bottom: 1.5rem;
    }
    .card-icon {
      width: 50px;
      height: 50px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 1rem;
      font-size: 1.5rem;
    }
    .yape-icon {
      background: linear-gradient(135deg, #722ed1, #9254de);
    }
    .card-icon {
      background: linear-gradient(135deg, #1890ff, #096dd9);
    }
    .card-title {
      color: white;
      font-size: 1.5rem;
      font-weight: 700;
      margin: 0;
    }
    .qr-container {
      text-align: center;
      margin: 1.5rem 0;
      position: relative;
    }
    .qr-image {
      max-width: 200px;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      transition: transform 0.3s ease;
      cursor: pointer;
    }
    .qr-image:hover {
      transform: scale(1.05);
    }
    .qr-placeholder {
      background: rgba(255, 255, 255, 0.1);
      border: 2px dashed rgba(255, 255, 255, 0.3);
      border-radius: 12px;
      padding: 2rem;
      color: white;
      text-align: center;
    }
    .payment-info {
      background: rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      padding: 1rem;
      margin: 1rem 0;
    }
    .info-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.5rem;
    }
    .info-label {
      color: rgba(255, 255, 255, 0.8);
      font-weight: 500;
    }
    .info-value {
      color: white;
      font-weight: 600;
    }
    .modern-btn {
      background: linear-gradient(135deg, #667eea, #764ba2);
      border: none;
      border-radius: 12px;
      color: white;
      padding: 12px 24px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
      margin: 0.25rem;
    }
    .modern-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }
    .modern-btn.primary {
      background: linear-gradient(135deg, #ff6b6b, #ee5a24);
    }
    .modern-btn.copy {
      background: linear-gradient(135deg, #4ecdc4, #44a08d);
      padding: 8px 16px;
      font-size: 0.9rem;
    }
    .upload-form {
      margin-top: 1.5rem;
    }
    .form-group {
      margin-bottom: 1rem;
    }
    .modern-input {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.1);
      color: white;
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    .modern-input::placeholder {
      color: rgba(255, 255, 255, 0.6);
    }
    .modern-input:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
    }
    .upload-message {
      margin-top: 0.5rem;
      padding: 0.5rem;
      border-radius: 6px;
      font-size: 0.9rem;
    }
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.8);
      backdrop-filter: blur(10px);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
    }
    .modal-overlay.is-open {
      opacity: 1;
      visibility: visible;
    }
    .modal-content {
      max-width: 90vw;
      max-height: 90vh;
      position: relative;
    }
    .modal-close {
      position: absolute;
      top: -40px;
      right: 0;
      background: white;
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      font-size: 1.5rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  </style>

  <section class="modern-paywall">
    <div class="paywall-container">
      <div class="paywall-hero">
        <h1>🚀 ¡Casi estás dentro!</h1>
        <p>Tu acceso está siendo procesado. Completa tu pago y te daremos acceso inmediato a todo el contenido premium.</p>
      </div>

      <div class="payment-grid">
        <!-- Yape Card -->
        <div class="payment-card">
          <div class="card-header">
            <div class="card-icon yape-icon">💜</div>
            <h3 class="card-title">Yape (Perú)</h3>
          </div>
          
          <div class="qr-container">
            <?php if($qr): ?>
              <img src="<?php echo esc_url($qr); ?>" alt="QR de Yape" class="qr-image qr-zoom" data-src="<?php echo esc_url($qr); ?>" loading="lazy" decoding="async" />
            <?php else: ?>
              <div class="qr-placeholder">
                <div style="font-size: 2rem; margin-bottom: 1rem;">📱</div>
                <div>QR no configurado</div>
                <small>Ve a Skool Theme → Ajustes → Yape</small>
              </div>
            <?php endif; ?>
          </div>

          <div class="payment-info">
            <div class="info-row">
              <span class="info-label">Número/Alias:</span>
              <span class="info-value"><?php echo $yape ? esc_html($yape) : '—'; ?></span>
              <?php if($yape): ?>
                <button class="modern-btn copy btn-copy" data-copy="<?php echo esc_attr($yape); ?>">📋 Copiar</button>
              <?php endif; ?>
            </div>
          </div>

          <?php if(is_user_logged_in()): ?>
            <form id="yape-proof-form" enctype="multipart/form-data" class="upload-form">
              <div class="form-group">
                <input type="file" name="voucher" accept="image/*,application/pdf" required class="modern-input" style="padding: 8px;">
              </div>
              <div class="form-group">
                <input type="text" name="note" placeholder="Nota u observación (opcional)" class="modern-input">
              </div>
              <button type="submit" class="modern-btn primary" style="width: 100%;">📤 Enviar Comprobante</button>
              <div class="upload-message" id="yape-proof-msg"></div>
            </form>
          <?php else: ?>
            <p style="color: rgba(255,255,255,0.8); text-align: center; margin-top: 1rem;">
              🔐 Inicia sesión para adjuntar tu comprobante
            </p>
          <?php endif; ?>
        </div>

        <!-- Card Payment -->
        <div class="payment-card">
          <div class="card-header">
            <div class="card-icon">💳</div>
            <h3 class="card-title">Tarjeta de Crédito</h3>
          </div>
          
          <div style="text-align: center; margin: 2rem 0;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🔒</div>
            <p style="color: rgba(255,255,255,0.9); margin-bottom: 1.5rem;">
              Pago seguro con Stripe. Acceso inmediato una vez confirmado el pago.
            </p>
            <button id="sk-pay-card" class="modern-btn primary" style="font-size: 1.1rem; padding: 16px 32px;">
              💳 Pagar con Tarjeta
            </button>
            <small style="display: block; margin-top: 1rem; color: rgba(255,255,255,0.6);">
              Configura tu <strong>PRICE_ID</strong> en Skool Theme → Ajustes
            </small>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para QR -->
    <div class="modal-overlay" id="qr-modal" aria-hidden="true">
      <div class="modal-content">
        <button class="modal-close qr-close" aria-label="Cerrar">&times;</button>
        <img id="qr-modal-img" src="" alt="QR" style="max-width: 100%; border-radius: 12px;" />
      </div>
    </div>
  </section>
  
  <?php return ob_get_clean();
});

/* ========= BLOQUEO DE LECCIÓN (visual) ========= */
add_filter('the_content', function($content){
  if(is_singular('sk_lesson')){
    $mid = (int)get_post_meta(get_the_ID(),'_sk_module_id',true);
    $uid = get_current_user_id();
    $can = function_exists('ecim_sk_user_can_access_module') ? ecim_sk_user_can_access_module($uid,$mid) : false;
    if(!$can){
      return '<div class="sk-locked"><p>Contenido bloqueado. <a class="sk-link" href="'.esc_url(home_url('/pago')).'">Ir a pagar</a>.</p></div>';
    }
  }
  return $content;
});

/* ========= EDUCADORES ========= */
add_shortcode('sk_educadores', function(){
  $eds = get_posts(['post_type'=>'sk_educator','numberposts'=>-1,'orderby'=>'menu_order','order'=>'ASC']);
  if(!$eds) return '<p class="ecim-muted">Aún no hay educadores.</p>';
  ob_start(); ?>
  <div class="container" style="padding:1rem 0">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem">
      <?php foreach($eds as $e): ?>
        <article class="sk-card">
          <?php if(function_exists('get_field') && ($img=get_field('ecim_avatar',$e->ID))): ?>
            <img src="<?php echo esc_url($img['sizes']['medium']); ?>" alt="<?php echo esc_attr($e->post_title); ?>" style="width:100%;border-radius:12px;aspect-ratio:1/1;object-fit:cover" />
          <?php elseif(has_post_thumbnail($e->ID)): ?>
            <?php echo get_the_post_thumbnail($e->ID,'medium',['style'=>'width:100%;border-radius:12px;aspect-ratio:1/1;object-fit:cover']); ?>
          <?php endif; ?>
          <h3 style="margin:.6rem 0"><?php echo esc_html($e->post_title); ?></h3>
          <?php if(function_exists('get_field') && ($role=get_field('ecim_role',$e->ID))): ?><div class="chip"><?php echo esc_html($role); ?></div><?php endif; ?>
          <div class="ecim-muted" style="margin:.4rem 0"><?php echo function_exists('get_field') && get_field('ecim_shortbio',$e->ID) ? esc_html(get_field('ecim_shortbio',$e->ID)) : ''; ?></div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
  <?php return ob_get_clean();
});
