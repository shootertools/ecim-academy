<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Panel de Gestión de Lecciones */
add_action('admin_menu', function(){
  add_submenu_page('ecimsk_root','Lecciones','Lecciones','manage_options','ecimsk_lessons','ecimsk_lessons_panel');
});

function ecimsk_lessons_panel(){
  if ( ! current_user_can('manage_options') ) return;

  // Estadísticas
  $lessons = get_posts(['post_type'=>'sk_lesson','numberposts'=>-1,'post_status'=>'any']);
  $published_lessons = array_filter($lessons, function($l) { return $l->post_status === 'publish'; });
  $draft_lessons = array_filter($lessons, function($l) { return $l->post_status === 'draft'; });
  
  $total_lessons = count($lessons);
  $published_count = count($published_lessons);
  $draft_count = count($draft_lessons);
  
  // Contar progreso de estudiantes
  $completed_lessons = 0;
  $users = get_users(['fields' => ['ID']]);
  foreach($users as $user) {
    $completed = get_user_meta($user->ID, 'skwp_completed_lessons', true);
    if (is_array($completed)) {
      $completed_lessons += count($completed);
    }
  }
  ?>
  
  <style>
  .ecim-admin-header {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
  .ecim-stat-card.total { border-left-color: #06b6d4; }
  .ecim-stat-card.published { border-left-color: #10b981; }
  .ecim-stat-card.draft { border-left-color: #f59e0b; }
  .ecim-stat-card.completed { border-left-color: #8b5cf6; }
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
  .ecim-lesson-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid;
  }
  .ecim-lesson-card.published { border-left-color: #10b981; }
  .ecim-lesson-card.draft { border-left-color: #f59e0b; }
  .ecim-lesson-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
  }
  .ecim-lesson-info h3 {
    margin: 0 0 0.25rem 0;
    font-size: 1.3rem;
    color: #1f2937;
  }
  .ecim-lesson-meta {
    color: #6b7280;
    font-size: 0.9rem;
    margin: 0.25rem 0;
  }
  .ecim-module-tag {
    display: inline-block;
    background: #ddd6fe;
    color: #5b21b6;
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
    background: #fef3c7;
    color: #92400e;
  }
  .ecim-btn {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
    box-shadow: 0 4px 12px rgba(79, 172, 254, 0.4);
    color: white;
  }
  .ecim-btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  }
  .ecim-btn-success:hover {
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
  }
  .ecim-lessons-grid {
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
    .ecim-lesson-header {
      flex-direction: column;
      gap: 1rem;
    }
    .ecim-actions {
      flex-direction: column;
    }
  }
  </style>

  <div class="wrap">
    <div class="ecim-admin-header">
      <h1>📝 Gestión de Lecciones</h1>
      <p>Administra el contenido educativo de cada lección</p>
    </div>

    <!-- Estadísticas -->
    <div class="ecim-stats-grid">
      <div class="ecim-stat-card total">
        <div class="ecim-stat-number" style="color: #06b6d4;"><?php echo $total_lessons; ?></div>
        <div class="ecim-stat-label">Total Lecciones</div>
      </div>
      <div class="ecim-stat-card published">
        <div class="ecim-stat-number" style="color: #10b981;"><?php echo $published_count; ?></div>
        <div class="ecim-stat-label">Publicadas</div>
      </div>
      <div class="ecim-stat-card draft">
        <div class="ecim-stat-number" style="color: #f59e0b;"><?php echo $draft_count; ?></div>
        <div class="ecim-stat-label">Borradores</div>
      </div>
      <div class="ecim-stat-card completed">
        <div class="ecim-stat-number" style="color: #8b5cf6;"><?php echo $completed_lessons; ?></div>
        <div class="ecim-stat-label">Completadas</div>
      </div>
    </div>

    <!-- Acciones rápidas -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
      <h3 style="margin: 0 0 1rem 0;">⚡ Acciones Rápidas</h3>
      <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
        <a href="<?php echo admin_url('post-new.php?post_type=sk_lesson'); ?>" class="ecim-btn ecim-btn-success">
          ➕ Crear Nueva Lección
        </a>
        <a href="<?php echo admin_url('edit.php?post_type=sk_lesson'); ?>" class="ecim-btn">
          📝 Ver Todas las Lecciones
        </a>
        <a href="<?php echo admin_url('admin.php?page=ecimsk_modules'); ?>" class="ecim-btn">
          📖 Gestionar Módulos
        </a>
      </div>
    </div>

    <h2 style="margin: 2rem 0 1rem 0;">📋 Lecciones Recientes</h2>
    
    <?php if ($lessons): ?>
      <div class="ecim-lessons-grid">
        <?php 
        $recent_lessons = array_slice($lessons, 0, 10); // Mostrar solo las 10 más recientes
        foreach ($recent_lessons as $lesson):
          $module_id = get_post_meta($lesson->ID, 'module_id', true);
          $module = $module_id ? get_post($module_id) : null;
          $video_url = get_post_meta($lesson->ID, 'video_url', true);
          ?>
          <div class="ecim-lesson-card <?php echo $lesson->post_status; ?>">
            <div class="ecim-lesson-header">
              <div class="ecim-lesson-info">
                <h3><?php echo esc_html($lesson->post_title ?: 'Sin título'); ?></h3>
                <div class="ecim-lesson-meta">
                  📅 Creado: <?php echo date('d/m/Y', strtotime($lesson->post_date)); ?>
                </div>
                <div class="ecim-lesson-meta">
                  <?php if ($video_url): ?>
                    🎥 Con video
                  <?php else: ?>
                    📄 Solo texto
                  <?php endif; ?>
                </div>
                <?php if ($module): ?>
                  <div class="ecim-module-tag">
                    📖 <?php echo esc_html($module->post_title); ?>
                  </div>
                <?php endif; ?>
                <?php if ($lesson->post_excerpt): ?>
                  <div class="ecim-lesson-meta" style="margin-top: 0.5rem; color: #374151;">
                    <?php echo esc_html(wp_trim_words($lesson->post_excerpt, 15)); ?>
                  </div>
                <?php endif; ?>
              </div>
              <div style="text-align: right;">
                <span class="ecim-status-badge <?php echo $lesson->post_status; ?>">
                  <?php echo $lesson->post_status === 'publish' ? '✅ Publicada' : '📝 Borrador'; ?>
                </span>
              </div>
            </div>
            
            <div class="ecim-actions">
              <a href="<?php echo admin_url('post.php?post='.$lesson->ID.'&action=edit'); ?>" class="ecim-btn">
                ✏️ Editar
              </a>
              <a href="<?php echo get_permalink($lesson->ID); ?>" class="ecim-btn" target="_blank">
                👁️ Ver
              </a>
              <?php if (!$video_url): ?>
                <a href="<?php echo admin_url('post.php?post='.$lesson->ID.'&action=edit#video_url'); ?>" class="ecim-btn ecim-btn-success">
                  🎥 Agregar Video
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      
      <?php if (count($lessons) > 10): ?>
        <div style="text-align: center; margin-top: 2rem;">
          <a href="<?php echo admin_url('edit.php?post_type=sk_lesson'); ?>" class="ecim-btn">
            Ver todas las <?php echo count($lessons); ?> lecciones →
          </a>
        </div>
      <?php endif; ?>
      
    <?php else: ?>
      <div style="text-align: center; padding: 3rem; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="font-size: 3rem; margin-bottom: 1rem;">📝</div>
        <h3 style="color: #6b7280; margin: 0 0 1rem 0;">No hay lecciones creadas</h3>
        <p style="color: #9ca3af; margin-bottom: 2rem;">Las lecciones contienen el contenido educativo principal</p>
        <a href="<?php echo admin_url('post-new.php?post_type=sk_lesson'); ?>" class="ecim-btn ecim-btn-success">
          ➕ Crear Primera Lección
        </a>
      </div>
    <?php endif; ?>
  </div>
  <?php
}
