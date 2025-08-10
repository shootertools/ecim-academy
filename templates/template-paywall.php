<?php
/*
Template Name: Paywall (Skool)
*/
get_header(); ?>
<main class="container" style="padding:2rem 0;">
  <?php if(!is_user_logged_in()): ?>
    <div class="sk-panel"><p>Inicia sesión para continuar.</p></div>
  <?php else: ?>
    <?php echo do_shortcode('[sk_paywall_dark]'); ?>
  <?php endif; ?>
</main>
<?php get_footer(); ?>
