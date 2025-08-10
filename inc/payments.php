<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Menú admin */
add_action('admin_menu', function(){
  add_menu_page('Skool Theme','Skool Theme','manage_options','ecimsk_root','ecimsk_root','dashicons-welcome-learn-more',30);
  add_submenu_page('ecimsk_root','Ajustes','Ajustes','manage_options','ecimsk_settings','ecimsk_settings');
  add_submenu_page('ecimsk_root','Alumnos','Alumnos','manage_options','ecimsk_students','ecimsk_students');
  add_submenu_page('ecimsk_root','Portada','Portada','manage_options','ecimsk_home','ecimsk_home');
});

function ecimsk_root(){ echo '<div class="wrap"><h1>Skool WP Theme</h1><p>Administra pagos, alumnos y portada.</p></div>'; }

/* Ajustes (Stripe + Portada + Yape) */
function ecimsk_settings(){
  if ( ! current_user_can('manage_options') ) return;

  if ( isset($_POST['ecimsk_save']) ) {
    check_admin_referer('ecimsk_settings');
    update_option('skwp_stripe_secret', sanitize_text_field($_POST['skwp_stripe_secret'] ?? ''));
    update_option('skwp_stripe_price_id', sanitize_text_field($_POST['skwp_stripe_price_id'] ?? ''));
    update_option('skwp_success_url', esc_url_raw($_POST['skwp_success_url'] ?? home_url('/plataforma')));
    update_option('skwp_cancel_url', esc_url_raw($_POST['skwp_cancel_url'] ?? home_url('/plataforma')));
    update_option('skwp_webhook_secret', sanitize_text_field($_POST['skwp_webhook_secret'] ?? ''));

    update_option('skwp_home_privacy', sanitize_text_field($_POST['skwp_home_privacy'] ?? 'Privado'));
    update_option('skwp_home_members', intval($_POST['skwp_home_members'] ?? 0));
    update_option('skwp_home_price', sanitize_text_field($_POST['skwp_home_price'] ?? '$700 /año'));
    update_option('skwp_home_cover', esc_url_raw($_POST['skwp_home_cover'] ?? ''));

    update_option('skwp_yape_qr_url', esc_url_raw($_POST['skwp_yape_qr_url'] ?? ''));
    update_option('skwp_yape_number', sanitize_text_field($_POST['skwp_yape_number'] ?? ''));
    echo '<div class="updated"><p>Guardado.</p></div>';
  }

  wp_enqueue_media();

  $stripe_secret = get_option('skwp_stripe_secret','');
  $price_id      = get_option('skwp_stripe_price_id','');
  $success_url   = get_option('skwp_success_url',home_url('/plataforma'));
  $cancel_url    = get_option('skwp_cancel_url',home_url('/plataforma'));
  $webhook       = get_option('skwp_webhook_secret','');

  $privacy   = get_option('skwp_home_privacy','Privado');
  $members   = (int)get_option('skwp_home_members',0);
  $home_price= get_option('skwp_home_price','$700 /año');
  $home_cover= get_option('skwp_home_cover','');

  $yape_qr = get_option('skwp_yape_qr_url','');
  $yape_no = get_option('skwp_yape_number','');
  ?>
  <div class="wrap">
    <h1>Ajustes</h1>
    <form method="post">
      <?php wp_nonce_field('ecimsk_settings'); ?>

      <h2>Pagos (Stripe)</h2>
      <table class="form-table">
        <tr><th>Stripe Secret Key</th><td><input class="regular-text" type="text" name="skwp_stripe_secret" value="<?php echo esc_attr($stripe_secret); ?>"></td></tr>
        <tr><th>Stripe Price ID</th><td><input class="regular-text" type="text" name="skwp_stripe_price_id" value="<?php echo esc_attr($price_id); ?>"></td></tr>
        <tr><th>Success URL</th><td><input class="regular-text" type="url" name="skwp_success_url" value="<?php echo esc_attr($success_url); ?>"></td></tr>
        <tr><th>Cancel URL</th><td><input class="regular-text" type="url" name="skwp_cancel_url" value="<?php echo esc_attr($cancel_url); ?>"></td></tr>
        <tr><th>Stripe Webhook Secret</th><td><input class="regular-text" type="text" name="skwp_webhook_secret" value="<?php echo esc_attr($webhook); ?>"></td></tr>
      </table>

      <h2>Portada</h2>
      <table class="form-table">
        <tr><th>Privacidad</th><td><input class="regular-text" type="text" name="skwp_home_privacy" value="<?php echo esc_attr($privacy); ?>"></td></tr>
        <tr><th>Miembros</th><td><input class="small-text" type="number" name="skwp_home_members" value="<?php echo esc_attr($members); ?>"></td></tr>
        <tr><th>Precio</th><td><input class="regular-text" type="text" name="skwp_home_price" value="<?php echo esc_attr($home_price); ?>"></td></tr>
        <tr><th>Cover (URL)</th><td><input class="regular-text" type="url" name="skwp_home_cover" value="<?php echo esc_attr($home_cover); ?>"></td></tr>
      </table>

      <h2>Yape</h2>
      <table class="form-table">
        <tr>
          <th>QR (URL)</th>
          <td>
            <input class="regular-text" type="url" name="skwp_yape_qr_url" value="<?php echo esc_attr($yape_qr); ?>">
            <button type="button" class="button btn-pick-qr">Seleccionar desde medios</button>
          </td>
        </tr>
        <tr><th>Número / Alias</th><td><input class="regular-text" type="text" name="skwp_yape_number" value="<?php echo esc_attr($yape_no); ?>"></td></tr>
      </table>

      <p><button class="button button-primary" name="ecimsk_save" value="1">Guardar</button></p>
      <p>Webhook Stripe: <code><?php echo esc_html( home_url('/wp-json/skool/v1/stripe') ); ?></code></p>
    </form>
  </div>
  <?php
}

/* Inserta el picker de medios (sin cerrar PHP en medio del callback) */
add_action('admin_print_footer_scripts', function(){
  if ( ! isset($_GET['page']) || $_GET['page'] !== 'ecimsk_settings' ) return;
  echo '<script>
    jQuery(function($){
      var frame;
      $(".btn-pick-qr").on("click", function(e){
        e.preventDefault();
        if(frame){ frame.open(); return; }
        frame = wp.media({ title: "Selecciona tu QR de Yape", button: { text: "Usar este QR" }, multiple: false });
        frame.on("select", function(){
          var att = frame.state().get("selection").first().toJSON();
          $("input[name=\\"skwp_yape_qr_url\\"]").val(att.url);
        });
        frame.open();
      });
    });
  </script>';
});

/* Portada (editar textos básicos) */
function ecimsk_home(){
  if ( ! current_user_can('manage_options') ) return;
  if ( isset($_POST['ecimsk_home_save']) ) {
    check_admin_referer('ecimsk_home');
    update_option('ecim_home_title', wp_kses_post($_POST['ecim_home_title'] ?? ''));
    update_option('ecim_home_highlight', sanitize_text_field($_POST['ecim_home_highlight'] ?? ''));
    update_option('ecim_home_subtitle', wp_kses_post($_POST['ecim_home_subtitle'] ?? ''));
    update_option('ecim_home_btn1_text', sanitize_text_field($_POST['ecim_home_btn1_text'] ?? ''));
    update_option('ecim_home_btn1_url', esc_url_raw($_POST['ecim_home_btn1_url'] ?? ''));
    update_option('ecim_home_btn2_text', sanitize_text_field($_POST['ecim_home_btn2_text'] ?? ''));
    update_option('ecim_home_btn2_url', esc_url_raw($_POST['ecim_home_btn2_url'] ?? ''));
    echo '<div class="updated"><p>Portada guardada.</p></div>';
  }
  $t  = get_option('ecim_home_title','Aprende en comunidad');
  $hl = get_option('ecim_home_highlight','tipo SKOOL');
  $st = get_option('ecim_home_subtitle','Escuela de Ecommerce, Importaciones y Marketing. Acceso a cursos, módulos y lecciones; gestión de alumnos y pagos.');
  $b1t= get_option('ecim_home_btn1_text','Empieza gratis');
  $b1u= get_option('ecim_home_btn1_url',home_url('/'));
  $b2t= get_option('ecim_home_btn2_text','Entrar a la plataforma');
  $b2u= get_option('ecim_home_btn2_url',home_url('/plataforma')); ?>
  <div class="wrap">
    <h1>Portada</h1>
    <form method="post">
      <?php wp_nonce_field('ecimsk_home'); ?>
      <table class="form-table">
        <tr><th>Título</th><td><input class="large-text" type="text" name="ecim_home_title" value="<?php echo esc_attr($t); ?>"></td></tr>
        <tr><th>Palabra destacada</th><td><input class="regular-text" type="text" name="ecim_home_highlight" value="<?php echo esc_attr($hl); ?>"></td></tr>
        <tr><th>Subtítulo</th><td><textarea class="large-text" rows="3" name="ecim_home_subtitle"><?php echo esc_textarea($st); ?></textarea></td></tr>
        <tr><th>Botón 1</th><td><input class="regular-text" type="text" name="ecim_home_btn1_text" value="<?php echo esc_attr($b1t); ?>"> <input class="regular-text" type="url" name="ecim_home_btn1_url" value="<?php echo esc_attr($b1u); ?>"></td></tr>
        <tr><th>Botón 2</th><td><input class="regular-text" type="text" name="ecim_home_btn2_text" value="<?php echo esc_attr($b2t); ?>"> <input class="regular-text" type="url" name="ecim_home_btn2_url" value="<?php echo esc_attr($b2u); ?>"></td></tr>
      </table>
      <p><button class="button button-primary" name="ecimsk_home_save" value="1">Guardar</button></p>
    </form>
  </div>
<?php }

/* Alumnos */
function ecimsk_students(){
  if ( ! current_user_can('manage_options') ) return;

  if ( isset($_POST['ecimsk_do']) ) {
    check_admin_referer('ecimsk_students');
    $uid = intval($_POST['user_id'] ?? 0);
    if ( $_POST['ecimsk_do'] === 'approve_paid' ) {
      update_user_meta($uid,'skwp_paid','yes');
      echo '<div class="updated"><p>Alumno marcado como pagado (acceso completo).</p></div>';
    } elseif ( $_POST['ecimsk_do'] === 'remove_paid' ) {
      delete_user_meta($uid,'skwp_paid');
      echo '<div class="updated"><p>Pago retirado.</p></div>';
    } elseif ( $_POST['ecimsk_do'] === 'save_modules' ) {
      $mods = array_map('intval', $_POST['modules'] ?? []);
      update_user_meta($uid,'skwp_enrolled_modules',$mods);
      echo '<div class="updated"><p>Módulos actualizados.</p></div>';
    }
  }

  // Estadísticas
  $all_users = get_users(['fields' => ['ID']]);
  $paid_users = array_filter($all_users, function($u) {
    return get_user_meta($u->ID, 'skwp_paid', true) === 'yes';
  });
  $total_students = count($all_users);
  $paid_count = count($paid_users);
  $unpaid_count = $total_students - $paid_count;

  $query_email = sanitize_email($_GET['email'] ?? '');
  $user = $query_email ? get_user_by('email',$query_email) : null;
  $modules = get_posts(['post_type'=>'sk_module','numberposts'=>-1,'orderby'=>'title','order'=>'ASC']); 
  ?>
  
  <style>
  .ecim-admin-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin: 0 0 2rem 0;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  }
  .ecim-admin-header h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
    font-weight: 700;
  }
  .ecim-admin-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 1.1rem;
  }
  .ecim-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
  }
  .ecim-stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid;
  }
  .ecim-stat-card.total { border-left-color: #3b82f6; }
  .ecim-stat-card.paid { border-left-color: #10b981; }
  .ecim-stat-card.unpaid { border-left-color: #f59e0b; }
  .ecim-stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    margin: 0;
    line-height: 1;
  }
  .ecim-stat-label {
    color: #6b7280;
    font-size: 0.9rem;
    margin: 0.5rem 0 0 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .ecim-search-box {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  }
  .ecim-student-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid;
  }
  .ecim-student-card.paid { border-left-color: #10b981; }
  .ecim-student-card.unpaid { border-left-color: #f59e0b; }
  .ecim-student-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 1rem;
  }
  .ecim-student-info h3 {
    margin: 0 0 0.25rem 0;
    font-size: 1.2rem;
    color: #1f2937;
  }
  .ecim-student-email {
    color: #6b7280;
    font-size: 0.9rem;
  }
  .ecim-status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .ecim-status-badge.paid {
    background: #d1fae5;
    color: #065f46;
  }
  .ecim-status-badge.unpaid {
    background: #fef3c7;
    color: #92400e;
  }
  .ecim-modules-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 0.75rem;
    margin: 1rem 0;
  }
  .ecim-module-checkbox {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    background: #f9fafb;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
  }
  .ecim-module-checkbox input {
    margin-right: 0.5rem;
  }
  .ecim-students-grid {
    display: grid;
    gap: 1rem;
  }
  .ecim-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s;
  }
  .ecim-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    color: white;
  }
  .ecim-btn-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  }
  .ecim-btn-danger:hover {
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
  }
  .ecim-pagination {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    margin-top: 2rem;
    justify-content: center;
  }
  @media (max-width: 768px) {
    .ecim-stats-grid {
      grid-template-columns: 1fr;
    }
    .ecim-student-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 0.5rem;
    }
    .ecim-modules-grid {
      grid-template-columns: 1fr;
    }
  }
  </style>

  <div class="wrap">
    <div class="ecim-admin-header">
      <h1>👥 Gestión de Estudiantes</h1>
      <p>Administra el acceso y permisos de tus estudiantes</p>
    </div>

    <!-- Estadísticas -->
    <div class="ecim-stats-grid">
      <div class="ecim-stat-card total">
        <div class="ecim-stat-number" style="color: #3b82f6;"><?php echo $total_students; ?></div>
        <div class="ecim-stat-label">Total Estudiantes</div>
      </div>
      <div class="ecim-stat-card paid">
        <div class="ecim-stat-number" style="color: #10b981;"><?php echo $paid_count; ?></div>
        <div class="ecim-stat-label">Con Acceso Pagado</div>
      </div>
      <div class="ecim-stat-card unpaid">
        <div class="ecim-stat-number" style="color: #f59e0b;"><?php echo $unpaid_count; ?></div>
        <div class="ecim-stat-label">Sin Pago</div>
      </div>
    </div>

    <!-- Búsqueda -->
    <div class="ecim-search-box">
      <h3 style="margin: 0 0 1rem 0;">🔍 Buscar Estudiante</h3>
      <form method="get" style="display: flex; gap: 1rem; align-items: center;">
        <input type="hidden" name="page" value="ecimsk_students">
        <input class="regular-text" type="email" name="email" placeholder="Buscar por email..." value="<?php echo esc_attr($query_email); ?>" style="flex: 1; padding: 0.75rem; border-radius: 6px;">
        <button class="ecim-btn">Buscar</button>
        <?php if ($query_email): ?>
          <a href="<?php echo admin_url('admin.php?page=ecimsk_students'); ?>" class="ecim-btn" style="background: #6b7280;">Limpiar</a>
        <?php endif; ?>
      </form>
    </div>

    <?php if ($user): 
      $paid = get_user_meta($user->ID,'skwp_paid',true)==='yes';
      $enr  = (array)get_user_meta($user->ID,'skwp_enrolled_modules',true); ?>
      
      <div class="ecim-student-card <?php echo $paid ? 'paid' : 'unpaid'; ?>">
        <div class="ecim-student-header">
          <div class="ecim-student-info">
            <h3><?php echo esc_html($user->display_name ?: $user->user_email); ?></h3>
            <div class="ecim-student-email"><?php echo esc_html($user->user_email); ?></div>
          </div>
          <span class="ecim-status-badge <?php echo $paid ? 'paid' : 'unpaid'; ?>">
            <?php echo $paid ? '✓ Pagado' : '⚠ Sin Pago'; ?>
          </span>
        </div>

        <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
          <form method="post">
            <?php wp_nonce_field('ecimsk_students'); ?>
            <input type="hidden" name="user_id" value="<?php echo esc_attr($user->ID); ?>">
            <?php if(!$paid): ?>
              <button class="ecim-btn" name="ecimsk_do" value="approve_paid">✓ Marcar como Pagado</button>
            <?php else: ?>
              <button class="ecim-btn ecim-btn-danger" name="ecimsk_do" value="remove_paid">✗ Quitar Pago</button>
            <?php endif; ?>
          </form>
        </div>

        <form method="post">
          <?php wp_nonce_field('ecimsk_students'); ?>
          <input type="hidden" name="user_id" value="<?php echo esc_attr($user->ID); ?>">
          <h4 style="margin: 0 0 1rem 0;">📚 Acceso por Módulos (<?php echo count($enr); ?> seleccionados)</h4>
          <div class="ecim-modules-grid">
            <?php foreach($modules as $m): ?>
              <label class="ecim-module-checkbox">
                <input type="checkbox" name="modules[]" value="<?php echo esc_attr($m->ID); ?>" <?php checked(in_array($m->ID,$enr)); ?>>
                <?php echo esc_html($m->post_title); ?>
              </label>
            <?php endforeach; ?>
          </div>
          <div style="margin-top: 1rem;">
            <button class="ecim-btn" name="ecimsk_do" value="save_modules">💾 Guardar Módulos</button>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <h2 style="margin: 2rem 0 1rem 0;">📋 Todos los Estudiantes</h2>
    <?php
      $per_page = 20;
      $paged = max(1, intval($_GET['paged'] ?? 1));
      $offset = ($paged-1) * $per_page;
      $users = get_users(['number'=>$per_page,'offset'=>$offset,'orderby'=>'registered','order'=>'DESC','fields'=>['ID','user_email','display_name']]);
      $total_users_data = function_exists('wp_count_users') ? wp_count_users() : ['total_users' => 0];
      $total_users = isset($total_users_data['total_users']) ? (int)$total_users_data['total_users'] : 0;
      $pages = max(1, ceil($total_users / $per_page));
    ?>
    
    <?php if ($users): ?>
      <div class="ecim-students-grid">
        <?php foreach ($users as $u):
          $paid_u = get_user_meta($u->ID,'skwp_paid',true)==='yes';
          $mods_u = (array)get_user_meta($u->ID,'skwp_enrolled_modules',true); ?>
          <div class="ecim-student-card <?php echo $paid_u ? 'paid' : 'unpaid'; ?>">
            <div class="ecim-student-header">
              <div class="ecim-student-info">
                <h3><?php echo esc_html($u->display_name ?: '—'); ?></h3>
                <div class="ecim-student-email"><?php echo esc_html($u->user_email); ?></div>
                <div style="margin-top: 0.5rem; color: #6b7280; font-size: 0.9rem;">
                  📚 <?php echo count($mods_u); ?> módulos asignados
                </div>
              </div>
              <div style="text-align: right;">
                <span class="ecim-status-badge <?php echo $paid_u ? 'paid' : 'unpaid'; ?>">
                  <?php echo $paid_u ? '✓ Pagado' : '⚠ Sin Pago'; ?>
                </span>
                <div style="margin-top: 0.5rem;">
                  <a class="ecim-btn" href="<?php echo esc_url( admin_url('admin.php?page=ecimsk_students&email='.urlencode($u->user_email)) ); ?>">Gestionar</a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="ecim-pagination">
        <?php if ($paged > 1): ?>
          <a class="ecim-btn" href="<?php echo esc_url( admin_url('admin.php?page=ecimsk_students&paged='.($paged-1)) ); ?>">← Anterior</a>
        <?php endif; ?>
        <span style="padding: 0.75rem 1rem; background: #f3f4f6; border-radius: 6px; font-weight: 600;">
          Página <?php echo $paged; ?> de <?php echo $pages; ?>
        </span>
        <?php if ($paged < $pages): ?>
          <a class="ecim-btn" href="<?php echo esc_url( admin_url('admin.php?page=ecimsk_students&paged='.($paged+1)) ); ?>">Siguiente →</a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div style="text-align: center; padding: 3rem; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="font-size: 3rem; margin-bottom: 1rem;">👥</div>
        <h3 style="color: #6b7280; margin: 0;">No hay estudiantes registrados</h3>
      </div>
    <?php endif; ?>
  </div>
  <?php
}
