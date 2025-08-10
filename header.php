<?php if ( ! defined('ABSPATH') ) { exit; } ?>
<!doctype html><html <?php language_attributes(); ?>><head>
<meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?>
</head><body <?php body_class(); ?> data-user-email="<?php echo is_user_logged_in()? esc_attr(wp_get_current_user()->user_email) : ''; ?>">
<header class="site-header"><div class="container">
  <div class="logo"><?php if(has_custom_logo()){ the_custom_logo(); } else { echo '<strong>'.esc_html(get_bloginfo('name')).'</strong>'; } ?></div>
  <nav><?php wp_nav_menu(['theme_location'=>'primary','container'=>false]); ?></nav>
  <div><?php echo do_shortcode('[sk_register_button text="Registrarse" variant="ghost"]'); ?></div>
</div></header>
<main class="site-content">
