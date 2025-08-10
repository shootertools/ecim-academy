<?php
if ( ! defined('ABSPATH') ) { exit; }
add_action('wp_enqueue_scripts', function(){
  wp_enqueue_style('ecim-font','https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap',[],null);
  wp_enqueue_style('ecim-skool', ECIM_SK_URI.'assets/css/skool.css',['ecim-font'],ECIM_SK_VERSION);
  wp_enqueue_script('ecim-skool', ECIM_SK_URI.'assets/js/skool.js',['jquery'],ECIM_SK_VERSION,true);
  wp_localize_script('ecim-skool','ECIMSK',[
    'ajaxurl'=>admin_url('admin-ajax.php'),
    'nonce'=>wp_create_nonce('ecimsk_nonce'),
    'loginRedirect'=>home_url('/plataforma'),
    'payRedirect'=>home_url('/pago'),
  ]);
});
