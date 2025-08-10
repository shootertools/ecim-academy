<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Panel de Gestión de Educadores */
add_action('admin_menu', function(){
  add_submenu_page('ecimsk_root','Educadores','Educadores','manage_options','ecimsk_educators','ecimsk_educators_panel');
});

function ecimsk_educators_panel(){
  if ( ! current_user_can('manage_options') ) return;

  // Estadísticas
  $educators = get_posts(['post_type'=>'sk_educator','numberposts'=>-1,'post_status'=>'any']);
  $published_educators = array_filter($educators, function($e) { return $e->post_status === 'publish'; });
  $draft_educators = array_filter($educators, function($e) { return $e->post_status === 'draft'; });
  
  $total_educators = count($educators);
  $published_count = count($published_educators);
  $draft_count = count($draft_educators);
  
  // Contar cursos asignados
  $total_courses_assigned = 0;
  foreach($educators as $educator) {
    $courses = get_posts(['post_type'=>'sk_course','meta_query'=>[['key'=>'educator_id','value'=>$educator->ID]],'numberposts'=>-1]);
    $total_courses_assigned += count($courses);
  }
  ?>
  
  <style>
  .ecim-admin-header {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
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
  .ecim-stat-card.total { border-left-color: #f59e0b; }
  .ecim-stat-card.published { border-left-color: #10b981; }
  .ecim-stat-card.draft { border-left-color: #6b7280; }
  .ecim-stat-card.courses { border-left-color: #3b82f6; }
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
  .ecim-educator-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid;
  }
  .ecim-educator-card.published { border-left-color: #10b981; }
  .ecim-educator-card.draft { border-left-color: #6b7280; }
  .ecim-educator-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
  }
  .ecim-educator-info {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
  }
  .ecim-educator-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    font-weight: bold;
    flex-shrink: 0;
  }
  .ecim-educator-details h3 {
    margin: 0 0 0.25rem 0;
    font-size: 1.3rem;
    color: #1f2937;
  }
  .ecim-educator-meta {
    color: #6b7280;
    font-size: 0.9rem;
    margin: 0.25rem 0;
  }
  .ecim-specialty-tag {
    display: inline-block;
    background: #fef3c7;
    color: #92400e;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-top: 0.5rem;
  }
  .ecim-status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .ecim-status-badge.published {
    background: #d1fae5;
    color: #065f46;
  }
  .ecim-status-badge.draft {
    background: #f3f4f6;
    color: #374151;
  }
  .ecim-btn {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s;
    margin-right: 0.5rem;
  }
  .ecim-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(250, 112, 154, 0.4);
    color: white;
  }
  .ecim-btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  }
  .ecim-btn-success:hover {
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
  }
  .ecim-educators-grid {
    display: grid;
    gap: 1rem;
  }
  .ecim-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    flex-wrap: wrap;
  }
  @media (max-width: 768px) {
    .ecim-stats-grid {
      grid-template-columns: 1fr;
    }
    .ecim-educator-header {
      flex-direction: column;
      gap: 1rem;
    }
    .ecim-educator-info {
      flex-direction: column;
      text-align: center;
    }
    .ecim-actions {
      flex-direction: column;
    }
  }
  </style>

  <div class="wrap">
    <div class="ecim-admin-header">
      <h1>👨‍🏫 Gestión de Educadores</h1>
      <p>Administra el equipo de profesores y especialistas</p>
    </div>

    <!-- Estadísticas -->
    <div class="ecim-stats-grid">
      <div class="ecim-stat-card total">
        <div class="ecim-stat-number" style="color: #f59e0b;"><?php echo $total_educators; ?></div>
        <div class="ecim-stat-label">Total Educadores</div>
      </div>
      <div class="ecim-stat-card published">
        <div class="ecim-stat-number" style="color: #10b981;"><?php echo $published_count; ?></div>
        <div class="ecim-stat-label">Activos</div>
      </div>
      <div class="ecim-stat-card draft">
        <div class="ecim-stat-number" style="color: #6b7280;"><?php echo $draft_count; ?></div>
        <div class="ecim-stat-label">Inactivos</div>
      </div>
      <div class="ecim-stat-card courses">
        <div class="ecim-stat-number" style="color: #3b82f6;"><?php echo $total_courses_assigned; ?></div>
        <div class="ecim-stat-label">Cursos Asignados</div>
      </div>
    </div>

    <!-- Acciones rápidas -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
      <h3 style="margin: 0 0 1rem 0;">⚡ Acciones Rápidas</h3>
      <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
        <a href="<?php echo admin_url('post-new.php?post_type=sk_educator'); ?>" class="ecim-btn ecim-btn-success">
          ➕ Agregar Nuevo Educador
        </a>
        <a href="<?php echo admin_url('edit.php?post_type=sk_educator'); ?>" class="ecim-btn">
          📝 Ver Todos los Educadores
        </a>
        <a href="<?php echo admin_url('admin.php?page=ecimsk_courses'); ?>" class="ecim-btn">
          📚 Asignar Cursos
        </a>
      </div>
    </div>

    <h2 style="margin: 2rem 0 1rem 0;">📋 Educadores del Equipo</h2>
    
    <?php if ($educators): ?>
      <div class="ecim-educators-grid">
        <?php foreach ($educators as $educator):
          $specialty = get_post_meta($educator->ID, 'specialty', true);
          $email = get_post_meta($educator->ID, 'email', true);
          $courses = get_posts(['post_type'=>'sk_course','meta_query'=>[['key'=>'educator_id','value'=>$educator->ID]],'numberposts'=>-1]);
          $initials = '';
          $name_parts = explode(' ', $educator->post_title);
          foreach($name_parts as $part) {
            if (!empty($part)) {
              $initials .= strtoupper(substr($part, 0, 1));
            }
          }
          $initials = substr($initials, 0, 2);
          ?>
          <div class="ecim-educator-card <?php echo $educator->post_status; ?>">
            <div class="ecim-educator-header">
              <div class="ecim-educator-info">
                <div class="ecim-educator-avatar">
                  <?php echo $initials ?: '👨‍🏫'; ?>
                </div>
                <div class="ecim-educator-details">
                  <h3><?php echo esc_html($educator->post_title ?: 'Sin nombre'); ?></h3>
                  <?php if ($email): ?>
                    <div class="ecim-educator-meta">
                      📧 <?php echo esc_html($email); ?>
                    </div>
                  <?php endif; ?>
                  <div class="ecim-educator-meta">
                    📅 Agregado: <?php echo date('d/m/Y', strtotime($educator->post_date)); ?>
                  </div>
                  <div class="ecim-educator-meta">
                    📚 <?php echo count($courses); ?> cursos asignados
                  </div>
                  <?php if ($specialty): ?>
                    <div class="ecim-specialty-tag">
                      🎯 <?php echo esc_html($specialty); ?>
                    </div>
                  <?php endif; ?>
                  <?php if ($educator->post_excerpt): ?>
                    <div class="ecim-educator-meta" style="margin-top: 0.5rem; color: #374151;">
                      <?php echo esc_html(wp_trim_words($educator->post_excerpt, 15)); ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
              <div style="text-align: right;">
                <span class="ecim-status-badge <?php echo $educator->post_status; ?>">
                  <?php echo $educator->post_status === 'publish' ? '✅ Activo' : '⚪ Inactivo'; ?>
                </span>
              </div>
            </div>
            
            <div class="ecim-actions">
              <a href="<?php echo admin_url('post.php?post='.$educator->ID.'&action=edit'); ?>" class="ecim-btn">
                ✏️ Editar Perfil
              </a>
              <a href="<?php echo get_permalink($educator->ID); ?>" class="ecim-btn" target="_blank">
                👁️ Ver Perfil
              </a>
              <?php if (count($courses) === 0): ?>
                <a href="<?php echo admin_url('edit.php?post_type=sk_course'); ?>" class="ecim-btn ecim-btn-success">
                  📚 Asignar Cursos
                </a>
              <?php else: ?>
                <a href="<?php echo admin_url('edit.php?post_type=sk_course&educator_id='.$educator->ID); ?>" class="ecim-btn">
                  📚 Ver Cursos (<?php echo count($courses); ?>)
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      
    <?php else: ?>
      <div style="text-align: center; padding: 3rem; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="font-size: 3rem; margin-bottom: 1rem;">👨‍🏫</div>
        <h3 style="color: #6b7280; margin: 0 0 1rem 0;">No hay educadores registrados</h3>
        <p style="color: #9ca3af; margin-bottom: 2rem;">Agrega perfiles de los profesores y especialistas</p>
        <a href="<?php echo admin_url('post-new.php?post_type=sk_educator'); ?>" class="ecim-btn ecim-btn-success">
          ➕ Agregar Primer Educador
        </a>
      </div>
    <?php endif; ?>
  </div>
  <?php
}
