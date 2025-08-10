<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Panel de Gestión de Cursos */
add_action('admin_menu', function(){
  add_submenu_page('ecimsk_root','Cursos','Cursos','manage_options','ecimsk_courses','ecimsk_courses_panel');
});

function ecimsk_courses_panel(){
  if ( ! current_user_can('manage_options') ) return;

  // Estadísticas
  $courses = get_posts(['post_type'=>'sk_course','numberposts'=>-1,'post_status'=>'any']);
  $published_courses = array_filter($courses, function($c) { return $c->post_status === 'publish'; });
  $draft_courses = array_filter($courses, function($c) { return $c->post_status === 'draft'; });
  
  $total_courses = count($courses);
  $published_count = count($published_courses);
  $draft_count = count($draft_courses);
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
  .ecim-stat-card.published { border-left-color: #10b981; }
  .ecim-stat-card.draft { border-left-color: #f59e0b; }
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
  .ecim-course-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid;
  }
  .ecim-course-card.published { border-left-color: #10b981; }
  .ecim-course-card.draft { border-left-color: #f59e0b; }
  .ecim-course-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
  }
  .ecim-course-info h3 {
    margin: 0 0 0.25rem 0;
    font-size: 1.3rem;
    color: #1f2937;
  }
  .ecim-course-meta {
    color: #6b7280;
    font-size: 0.9rem;
    margin: 0.25rem 0;
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
    margin-right: 0.5rem;
  }
  .ecim-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    color: white;
  }
  .ecim-btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  }
  .ecim-btn-success:hover {
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
  }
  .ecim-courses-grid {
    display: grid;
    gap: 1rem;
  }
  .ecim-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
  }
  @media (max-width: 768px) {
    .ecim-stats-grid {
      grid-template-columns: 1fr;
    }
    .ecim-course-header {
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
      <h1>📚 Gestión de Cursos</h1>
      <p>Administra todos los cursos de tu plataforma educativa</p>
    </div>

    <!-- Estadísticas -->
    <div class="ecim-stats-grid">
      <div class="ecim-stat-card total">
        <div class="ecim-stat-number" style="color: #3b82f6;"><?php echo $total_courses; ?></div>
        <div class="ecim-stat-label">Total Cursos</div>
      </div>
      <div class="ecim-stat-card published">
        <div class="ecim-stat-number" style="color: #10b981;"><?php echo $published_count; ?></div>
        <div class="ecim-stat-label">Publicados</div>
      </div>
      <div class="ecim-stat-card draft">
        <div class="ecim-stat-number" style="color: #f59e0b;"><?php echo $draft_count; ?></div>
        <div class="ecim-stat-label">Borradores</div>
      </div>
    </div>

    <!-- Acciones rápidas -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
      <h3 style="margin: 0 0 1rem 0;">⚡ Acciones Rápidas</h3>
      <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
        <a href="<?php echo admin_url('post-new.php?post_type=sk_course'); ?>" class="ecim-btn ecim-btn-success">
          ➕ Crear Nuevo Curso
        </a>
        <a href="<?php echo admin_url('edit.php?post_type=sk_course'); ?>" class="ecim-btn">
          📝 Ver Todos los Cursos
        </a>
        <a href="<?php echo admin_url('admin.php?page=ecimsk_modules'); ?>" class="ecim-btn">
          📖 Gestionar Módulos
        </a>
      </div>
    </div>

    <h2 style="margin: 2rem 0 1rem 0;">📋 Cursos Recientes</h2>
    
    <?php if ($courses): ?>
      <div class="ecim-courses-grid">
        <?php 
        $recent_courses = array_slice($courses, 0, 10); // Mostrar solo los 10 más recientes
        foreach ($recent_courses as $course):
          $modules = get_posts(['post_type'=>'sk_module','meta_query'=>[['key'=>'course_id','value'=>$course->ID]],'numberposts'=>-1]);
          $lessons_count = 0;
          foreach($modules as $module) {
            $lessons = get_posts(['post_type'=>'sk_lesson','meta_query'=>[['key'=>'module_id','value'=>$module->ID]],'numberposts'=>-1]);
            $lessons_count += count($lessons);
          }
          ?>
          <div class="ecim-course-card <?php echo $course->post_status; ?>">
            <div class="ecim-course-header">
              <div class="ecim-course-info">
                <h3><?php echo esc_html($course->post_title ?: 'Sin título'); ?></h3>
                <div class="ecim-course-meta">
                  📅 Creado: <?php echo date('d/m/Y', strtotime($course->post_date)); ?>
                </div>
                <div class="ecim-course-meta">
                  📖 <?php echo count($modules); ?> módulos • 📝 <?php echo $lessons_count; ?> lecciones
                </div>
                <?php if ($course->post_excerpt): ?>
                  <div class="ecim-course-meta" style="margin-top: 0.5rem; color: #374151;">
                    <?php echo esc_html(wp_trim_words($course->post_excerpt, 15)); ?>
                  </div>
                <?php endif; ?>
              </div>
              <div style="text-align: right;">
                <span class="ecim-status-badge <?php echo $course->post_status; ?>">
                  <?php echo $course->post_status === 'publish' ? '✅ Publicado' : '📝 Borrador'; ?>
                </span>
              </div>
            </div>
            
            <div class="ecim-actions">
              <a href="<?php echo admin_url('post.php?post='.$course->ID.'&action=edit'); ?>" class="ecim-btn">
                ✏️ Editar
              </a>
              <a href="<?php echo get_permalink($course->ID); ?>" class="ecim-btn" target="_blank">
                👁️ Ver
              </a>
              <?php if (count($modules) === 0): ?>
                <a href="<?php echo admin_url('post-new.php?post_type=sk_module&course_id='.$course->ID); ?>" class="ecim-btn ecim-btn-success">
                  ➕ Agregar Módulo
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      
      <?php if (count($courses) > 10): ?>
        <div style="text-align: center; margin-top: 2rem;">
          <a href="<?php echo admin_url('edit.php?post_type=sk_course'); ?>" class="ecim-btn">
            Ver todos los <?php echo count($courses); ?> cursos →
          </a>
        </div>
      <?php endif; ?>
      
    <?php else: ?>
      <div style="text-align: center; padding: 3rem; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="font-size: 3rem; margin-bottom: 1rem;">📚</div>
        <h3 style="color: #6b7280; margin: 0 0 1rem 0;">No hay cursos creados</h3>
        <p style="color: #9ca3af; margin-bottom: 2rem;">Comienza creando tu primer curso</p>
        <a href="<?php echo admin_url('post-new.php?post_type=sk_course'); ?>" class="ecim-btn ecim-btn-success">
          ➕ Crear Primer Curso
        </a>
      </div>
    <?php endif; ?>
  </div>
  <?php
}
