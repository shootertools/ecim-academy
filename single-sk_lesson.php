<?php
get_header();
$lid = get_the_ID();
$mid = (int)get_post_meta($lid,'_sk_module_id',true);
$uid = get_current_user_id();
$can = ecim_sk_user_can_access_module($uid,$mid);
$video = get_post_meta($lid,'_sk_video',true);
$lessons = ecim_sk_lessons_by_module($mid);
$ids = wp_list_pluck($lessons,'ID');
$idx = array_search($lid,$ids,true);
$prev = ($idx!==false && $idx>0) ? $ids[$idx-1] : 0;
$next = ($idx!==false && $idx < count($ids)-1) ? $ids[$idx+1] : 0;
$completed = $uid ? ecim_sk_completed_lessons($uid) : [];
$is_done = in_array($lid,$completed,true);
$progress = 0; if(count($lessons)>0){ $done=count(array_intersect($completed,$ids)); $progress = round(($done/count($lessons))*100); }
?>
<main class="container" style="padding:1rem 0;">
  <?php if(!$can): ?><div class="sk-locked"><p>Contenido bloqueado. <a class="sk-link" href="<?php echo esc_url( home_url('/pago') ); ?>">Ir a pagar</a>.</p></div>
  <?php else: ?>
  <div class="lesson-layout">
    <section>
      <div class="lesson-player" oncontextmenu="return false;">
        <div class="player-embed"><div class="wm" id="ecim-wm"></div>
          <?php if($video): ?>
            <div class="player-embed-inner"><?php echo wp_oembed_get($video) ?: wp_kses_post($video); ?></div>
          <?php else: ?>
            <div class="player-embed-inner" style="display:flex;align-items:center;justify-content:center;color:#fff;">Sin video — agrega la URL.</div>
          <?php endif; ?>
        </div>
      </div>
      <article class="sk-card" style="margin-top:.6rem">
        <h1 style="margin:0 0 .5rem"><?php the_title(); ?></h1>
        <div class="content"><?php the_content(); ?></div>
        <div class="lesson-nav">
          <div><?php if($prev): ?><a class="sk-btn" href="<?php echo esc_url( get_permalink($prev) ); ?>">&larr; Lección anterior</a><?php endif; ?></div>
          <div>
            <?php if($next): ?>
              <?php if($is_done): ?><a class="sk-btn sk-btn--primary" href="<?php echo esc_url( get_permalink($next) ); ?>">Siguiente lección &rarr;</a>
              <?php else: ?><button class="sk-btn" disabled title="Marca esta lección como completada para continuar">Siguiente lección &rarr;</button><?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </article>
    </section>
    <aside class="lesson-sidebar">
      <div class="sk-card"><strong>Progreso</strong><div class="progress" style="margin:.5rem 0"><span style="width:<?php echo esc_attr($progress); ?>%"></span></div>
        <button id="sk-toggle-complete" class="sk-btn <?php echo $is_done?'is-done':''; ?>" data-lesson="<?php echo esc_attr($lid); ?>"><?php echo $is_done?'Marcar como no completada':'Marcar como completada'; ?></button>
      </div>
      <div class="sk-card" style="margin-top:.6rem"><strong>Lecciones del módulo</strong><ul class="lesson-list" style="margin-top:.6rem">
        <?php foreach($lessons as $l): $done=in_array($l->ID,$completed,true); ?>
          <li><div class="lesson-left"><span class="dot <?php echo $done?'done':''; ?>"></span><a href="<?php echo esc_url( get_permalink($l->ID) ); ?>"><?php echo esc_html($l->post_title); ?></a></div><?php if($l->ID===$lid): ?><span class="chip">Actual</span><?php endif; ?></li>
        <?php endforeach; ?>
      </ul></div>
    </aside>
  </div>
  <?php endif; ?>
</main>
<?php get_footer(); ?>
