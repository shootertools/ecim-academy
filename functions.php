<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

define('ECIM_SK_VERSION','2.3.0');
define('ECIM_SK_PATH', get_template_directory().'/' );
define('ECIM_SK_URI',  get_template_directory_uri().'/' );

require_once ECIM_SK_PATH.'inc/enqueue.php';
require_once ECIM_SK_PATH.'inc/cpt.php';
require_once ECIM_SK_PATH.'inc/ajax.php';
require_once ECIM_SK_PATH.'inc/shortcodes.php';
require_once ECIM_SK_PATH.'inc/shortcodes-dark.php';
require_once ECIM_SK_PATH.'inc/payments.php';
require_once ECIM_SK_PATH.'inc/admin.php';
require_once ECIM_SK_PATH.'inc/admin-courses.php';
require_once ECIM_SK_PATH.'inc/admin-modules.php';
require_once ECIM_SK_PATH.'inc/admin-lessons.php';
require_once ECIM_SK_PATH.'inc/admin-educators.php';

add_action('after_setup_theme', function(){
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('custom-logo', ['height'=>80,'width'=>240,'flex-height'=>true,'flex-width'=>true]);
  register_nav_menus(['primary'=>'Primary Menu']);
});

// Crear páginas y menú
function ecim_sk_create_page($title,$slug,$tpl=''){
  $p = get_page_by_path($slug);
  if(!$p){ $id = wp_insert_post(['post_title'=>$title,'post_name'=>$slug,'post_status'=>'publish','post_type'=>'page']); }
  else { $id = $p->ID; }
  if($tpl){ update_post_meta($id,'_wp_page_template',$tpl); }
  return (int)$id;
}

add_action('after_switch_theme', function(){
  $home = ecim_sk_create_page('Inicio','inicio','templates/template-home-skool.php');
  $plat = ecim_sk_create_page('Plataforma','plataforma','templates/template-plataforma.php');
  $pago = ecim_sk_create_page('Pago','pago','templates/template-paywall.php');
  update_option('show_on_front','page');
  update_option('page_on_front',$home);

  $menu = wp_get_nav_menu_object('Principal');
  $menu_id = $menu ? $menu->term_id : wp_create_nav_menu('Principal');
  if(!wp_get_nav_menu_items($menu_id)){
    wp_update_nav_menu_item($menu_id,0,['menu-item-title'=>'Inicio','menu-item-url'=>home_url('/'),'menu-item-status'=>'publish']);
    wp_update_nav_menu_item($menu_id,0,['menu-item-title'=>'Plataforma','menu-item-object'=>'page','menu-item-object-id'=>$plat,'menu-item-type'=>'post_type','menu-item-status'=>'publish']);
  }
  $loc = (array)get_theme_mod('nav_menu_locations'); $loc['primary'] = $menu_id; set_theme_mod('nav_menu_locations',$loc);
  flush_rewrite_rules();
});

// Redirecciones de acceso
add_action('template_redirect', function(){
  if(is_page('plataforma') && is_user_logged_in()){
    $uid = get_current_user_id();
    $paid = get_user_meta($uid,'skwp_paid',true)==='yes';
    $mods = (array)get_user_meta($uid,'skwp_enrolled_modules',true);
    if(!$paid && empty($mods)){ wp_safe_redirect( home_url('/pago') ); exit; }
  }
  // Post-login redirect safeguard
  if(is_user_logged_in() && !is_admin() && !wp_doing_ajax()){
    $uid = get_current_user_id(); $flag = get_user_meta($uid,'ecimsk_after_login',true);
    if($flag==='pago'){ delete_user_meta($uid,'ecimsk_after_login'); wp_safe_redirect( home_url('/pago') ); exit; }
  }
});

// Ocultar "Plataforma" del menú si no hay acceso
add_filter('wp_nav_menu_objects', function($items,$args){
  if(!isset($args->theme_location) || $args->theme_location!=='primary'){ return $items; }
  $has = false;
  if(is_user_logged_in()){
    $uid = get_current_user_id();
    $paid = get_user_meta($uid,'skwp_paid',true)==='yes';
    $mods = (array)get_user_meta($uid,'skwp_enrolled_modules',true);
    $has = $paid || !empty($mods);
  }
  if(!$has){
    foreach($items as $k=>$it){
      $title = is_object($it) ? strtolower(trim($it->title)) : '';
      if($title==='plataforma'){ unset($items[$k]); }
    }
  }
  return $items;
},10,2);

// Gating secuencial: exige completar la lección previa
add_action('template_redirect', function(){
  if(is_singular('sk_lesson') && is_user_logged_in()){
    $lid = get_queried_object_id();
    $mid = (int)get_post_meta($lid,'_sk_module_id',true);
    $uid = get_current_user_id();
    if(!ecim_sk_user_can_access_module($uid,$mid)){
      wp_safe_redirect( home_url('/pago') ); exit;
    }
    $less = ecim_sk_lessons_by_module($mid);
    $ids = wp_list_pluck($less,'ID');
    $idx = array_search($lid,$ids,true);
    $completed = array_map('intval',(array)get_user_meta($uid,'skwp_completed_lessons',true));
    if($idx!==false && $idx>0){
      $prev = $ids[$idx-1];
      if(!in_array($prev,$completed,true)){
        wp_safe_redirect( get_permalink($prev) ); exit;
      }
    }
  }
});

// Helpers
function ecim_sk_user_can_access_module($uid,$module_id){
  if(!$uid) return false;
  if(get_user_meta($uid,'skwp_paid',true)==='yes') return true;
  $mods = array_map('intval',(array)get_user_meta($uid,'skwp_enrolled_modules',true));
  return in_array((int)$module_id,$mods,true);
}
function ecim_sk_completed_lessons($uid){
  return array_map('intval',(array)get_user_meta($uid,'skwp_completed_lessons',true));
}
