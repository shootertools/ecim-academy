<?php
get_header();
$module_id = get_the_ID();
$uid = get_current_user_id();
$can = ecim_sk_user_can_access_module($uid,$module_id);
$lessons = ecim_sk_lessons_by_module($module_id);
$completed = $uid ? ecim_sk_completed_lessons($uid) : [];
?>
<main class="container" style="padding:1.5rem 0;">
  <div class="module-head"><h1 style="margin:0"><?php the_title(); ?></h1><?php if(!$can): ?><a class="sk-btn sk-btn--primary" href="<?php echo esc_url( home_url('/pago') ); ?>">Pagar para desbloquear</a><?php endif; ?></div>
  <div class="sk-panel">
    <div class="content" style="margin-bottom:1rem"><?php the_content(); ?></div>
    <?php if($lessons): ?><ul class="lesson-list"><?php
      $ids = wp_list_pluck($lessons,'ID');
      foreach($lessons as $i=>$l):
        $prev_ok = ($i===0) ? true : in_array($ids[$i-1], $completed, true);
        $allow = $can && $prev_ok;
        $done = in_array($l->ID,$completed,true);
        $url = $allow ? get_permalink($l->ID) : home_url('/pago');
    ?>
      <li>
        <div class="lesson-left"><span class="dot <?php echo $done?'done':''; ?>"></span><strong><?php echo esc_html($l->post_title); ?></strong></div>
        <div class="lesson-actions">
          <?php if($allow): ?><a class="sk-btn" href="<?php echo esc_url($url); ?>">Ver lección</a>
          <?php else: ?><span class="lock">Bloqueado</span><?php endif; ?>
        </div>
      </li>
    <?php endforeach; ?></ul><?php else: ?><p>Aún no hay lecciones en este módulo.</p><?php endif; ?>
  </div>
</main>
<?php get_footer(); ?>
