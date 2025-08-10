<?php
/*
Template Name: Home (Estilo Skool 2025 Mejorado)
*/
if ( ! defined('ABSPATH') ) { exit; }

function ecim_get_home_settings(){
  $args = ['post_type'=>'sk_home','post_status'=>'publish','numberposts'=>1,'orderby'=>'date','order'=>'DESC'];
  $p = get_posts($args);
  $logo_id  = $p ? (int) get_post_meta($p[0]->ID,'ecim_home_logo_id',true) : 0;
  $video_id = $p ? (int) get_post_meta($p[0]->ID,'ecim_home_video_id',true) : 0;
  $video_url = $p ? trim((string) get_post_meta($p[0]->ID,'ecim_home_video_url',true)) : '';
  if($video_id){ $video_url = wp_get_attachment_url($video_id) ?: $video_url; }
  return [
    'logo_url'  => $logo_id ? wp_get_attachment_image_url($logo_id,'full') : '',
    'video_url' => $video_url,
  ];
}
$home = ecim_get_home_settings();
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php wp_head(); ?>
<style>
  :root {
    --radius: 18px;
    --yellow: #ffd65a;
    --bg-glass: rgba(15, 15, 15, 0.6);
  }
  html,body { height:100%; }
  body {
    margin:0;
    font-family: Manrope, system-ui, sans-serif;
    background:#000;
    color:#fff;
  }
  /* Fondo video y overlay */
  .home-wrap { position:relative; min-height:100vh; display:flex; align-items:center; justify-content:center; overflow:hidden; }
  .bg-video { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; z-index:0; }
  .bg-overlay { position:absolute; inset:0; background: radial-gradient(80% 60% at 70% 50%, rgba(0,0,0,.1), rgba(0,0,0,.75) 60%), linear-gradient(180deg, rgba(0,0,0,.3), rgba(0,0,0,.75)); z-index:1; }
  /* Contenido principal */
  .home-content { position:relative; z-index:2; width:100%; max-width:1200px; padding:clamp(16px,4vw,32px); }
  .home-grid { display:grid; grid-template-columns:1fr; gap:32px; align-items:center; }
  @media(min-width:980px){ .home-grid { grid-template-columns:1fr minmax(340px,410px); } }
  .headline h1 { font-size:clamp(32px,4vw,56px); line-height:1.05; margin:0 0 8px; }
  .headline p { font-size:clamp(16px,1.3vw,18px); opacity:.9; margin:0; }
  /* Bloque lateral */
  .hero-slot {
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:14px;
    background:var(--bg-glass);
    border-radius:var(--radius);
    padding:24px;
    backdrop-filter: blur(12px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
    animation: fadeInUp 0.6s ease-out;
  }
  .hero-slot img { max-width:180px; height:auto; transition: transform 0.3s ease; }
  .hero-slot img:hover { transform: scale(1.05); }
  .btn-acceder, .btn-ghost {
    width:100%;
    padding:14px;
    font-weight:700;
    font-size:1.1rem;
    cursor:pointer;
    border-radius:8px;
    transition: all 0.25s ease;
  }
  .btn-acceder { background:var(--yellow); color:#1a1a1a; border:none; }
  .btn-acceder:hover { background:#ffca2c; }
  .btn-ghost { background:transparent; color:#fff; border:1px solid rgba(255,255,255,.35); }
  .btn-ghost:hover { background:rgba(255,255,255,.08); }
  /* Popup */
  .popup-overlay {
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.75);
    z-index:9998;
    align-items:center;
    justify-content:center;
    padding:20px;
    animation: fadeIn 0.3s ease;
  }
  .popup {
    background:var(--bg-glass);
    border-radius:var(--radius);
    padding:24px;
    max-width:400px;
    width:100%;
    position:relative;
    backdrop-filter: blur(16px);
    box-shadow: 0 8px 28px rgba(0,0,0,0.5);
    animation: scaleIn 0.4s ease;
  }
  .popup .close-btn {
    position:absolute; top:10px; right:14px;
    background:none; border:none; color:#fff;
    font-size:1.5rem; cursor:pointer;
  }
  .brand { display:flex; flex-direction:column; align-items:center; margin-bottom:14px; }
  .brand img { max-width:140px; height:auto; }
  .frm label { display:block; font-size:.95rem; margin:8px 0 4px; opacity:.9; }
  .frm input {
    width:100%; padding:12px 14px;
    border-radius:12px;
    border:1px solid rgba(255,255,255,.2);
    background:#0c0c0c;
    color:#fff;
  }
  .btn { display:inline-block; width:100%; margin-top:12px; padding:12px 16px; font-weight:900; border-radius:12px; border:0; cursor:pointer; }
  .btn-primary { background:var(--yellow); color:#1a1a1a; }
  @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
  @keyframes fadeInUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
  @keyframes scaleIn { from { transform:scale(0.95); opacity:0; } to { transform:scale(1); opacity:1; } }
</style>
</head>
<body <?php body_class('home-video'); ?>>
  <main class="home-wrap">
    <?php if(!empty($home['video_url'])): ?>
      <video class="bg-video" src="<?php echo esc_url($home['video_url']); ?>" autoplay muted loop playsinline preload="auto"></video>
    <?php endif; ?>
    <div class="bg-overlay" aria-hidden="true"></div>

    <div class="home-content">
      <div class="home-grid">
        <!-- Texto -->
        <div class="headline">
          <h1><?php echo esc_html( get_option('ecim_home_title','ECIM – Ecommerce, Importaciones y Marketing') ); ?></h1>
          <p><?php echo esc_html( get_option('ecim_home_subtitle','Aprende en comunidad, con módulos claros y soporte humano.')); ?></p>
        </div>
        <!-- Bloque vertical -->
        <div class="hero-slot">
          <?php if(!empty($home['logo_url'])): ?>
            <img src="<?php echo esc_url($home['logo_url']); ?>" alt="Logo">
          <?php endif; ?>
          <button class="btn-acceder" id="open-register">Registrarse</button>
          <button class="btn-ghost" id="open-login">Iniciar sesión</button>
        </div>
      </div>
    </div>
  </main>

  <!-- Popup -->
  <div class="popup-overlay" id="popup-overlay">
    <div class="popup">
      <button class="close-btn" id="close-popup">&times;</button>
      <div class="brand">
        <?php if(!empty($home['logo_url'])): ?>
          <img src="<?php echo esc_url($home['logo_url']); ?>" alt="Logo">
        <?php endif; ?>
      </div>
      <!-- Registro -->
      <div id="panel-register" class="tabpanel">
        <form id="sk-register-form" class="frm" autocomplete="on">
          <label>Nombre</label>
          <input type="text" name="first_name" placeholder="Tu nombre" required>
          <label>Apellido</label>
          <input type="text" name="last_name" placeholder="Tu apellido" required>
          <label>Correo electrónico</label>
          <input type="email" name="email" placeholder="tucorreo@dominio.com" required>
          <label>Contraseña</label>
          <input type="password" name="password" placeholder="Crea una contraseña" required>
          <button type="submit" class="btn btn-primary">Crear cuenta</button>
        </form>
      </div>
      <!-- Login -->
      <div id="panel-login" class="tabpanel">
        <form id="sk-login-form" class="frm" autocomplete="on">
          <label>Correo electrónico</label>
          <input type="email" name="email" placeholder="tucorreo@dominio.com" required>
          <label>Contraseña</label>
          <input type="password" name="password" placeholder="Tu contraseña" required>
          <button type="submit" class="btn btn-primary">Entrar</button>
        </form>
      </div>
    </div>
  </div>

<script>
(function(){
  const overlay=document.getElementById('popup-overlay');
  const closeBtn=document.getElementById('close-popup');
  const openRegister=document.getElementById('open-register');
  const openLogin=document.getElementById('open-login');
  const panelRegister=document.getElementById('panel-register');
  const panelLogin=document.getElementById('panel-login');

  function showPanel(panel){
    panelRegister.classList.remove('active');
    panelLogin.classList.remove('active');
    setTimeout(()=>{
      panel.style.display='block';
      requestAnimationFrame(()=> panel.classList.add('active'));
    },50);
    panelRegister.style.display = panel === panelRegister ? 'block' : 'none';
    panelLogin.style.display = panel === panelLogin ? 'block' : 'none';
  }

  openRegister.addEventListener('click',()=>{ overlay.style.display='flex'; showPanel(panelRegister); });
  openLogin.addEventListener('click',()=>{ overlay.style.display='flex'; showPanel(panelLogin); });
  closeBtn.addEventListener('click',()=> overlay.style.display='none');
  overlay.addEventListener('click',e=>{ if(e.target===overlay) overlay.style.display='none'; });
})();
</script>

<style>
.tabpanel {
  opacity:0;
  transform: translateY(20px);
  transition: all 0.3s ease;
}
.tabpanel.active {
  opacity:1;
  transform: translateY(0);
}
</style>


<?php wp_footer(); ?>
</body>
</html>
