<?php
if ( ! defined('ABSPATH') ) { exit; }

add_action('init', function(){
  register_post_type('sk_course',[
    'label'=>'Cursos','public'=>true,'menu_icon'=>'dashicons-welcome-learn-more',
    'supports'=>['title','editor','thumbnail','excerpt','page-attributes'],'show_in_rest'=>true
  ]);
  register_post_type('sk_module',[
    'label'=>'Módulos','public'=>true,'menu_icon'=>'dashicons-index-card',
    'supports'=>['title','editor','thumbnail','excerpt','page-attributes'],'show_in_rest'=>true
  ]);
  register_post_type('sk_lesson',[
    'label'=>'Lecciones','public'=>true,'menu_icon'=>'dashicons-welcome-write-blog',
    'supports'=>['title','editor','thumbnail','excerpt','page-attributes'],'show_in_rest'=>true
  ]);
  register_post_type('sk_educator',[
    'label'=>'Educadores','public'=>true,'menu_icon'=>'dashicons-groups',
    'supports'=>['title','editor','thumbnail','excerpt','page-attributes'],'show_in_rest'=>true
  ]);
});

add_action('add_meta_boxes', function(){
  add_meta_box('sk_module_course','Curso del módulo','ecim_sk_mb_module_course','sk_module','side');
  add_meta_box('sk_lesson_module','Módulo de la lección','ecim_sk_mb_lesson_module','sk_lesson','side');
  add_meta_box('sk_lesson_video','Video (URL o embed)','ecim_sk_mb_lesson_video','sk_lesson','normal');
});
function ecim_sk_mb_module_course($post){
  $sel = get_post_meta($post->ID,'_sk_course_id',true);
  $courses = get_posts(['post_type'=>'sk_course','numberposts'=>-1]);
  echo '<select name="sk_course_id" style="width:100%"><option value="">— Selecciona curso —</option>';
  foreach($courses as $c){ printf('<option value="%d" %s>%s</option>',$c->ID, selected($sel,$c->ID,false), esc_html($c->post_title)); }
  echo '</select>';
}
function ecim_sk_mb_lesson_module($post){
  $sel = get_post_meta($post->ID,'_sk_module_id',true);
  $mods = get_posts(['post_type'=>'sk_module','numberposts'=>-1]);
  echo '<select name="sk_module_id" style="width:100%"><option value="">— Selecciona módulo —</option>';
  foreach($mods as $m){ printf('<option value="%d" %s>%s</option>',$m->ID, selected($sel,$m->ID,false), esc_html($m->post_title)); }
  echo '</select>';
}
function ecim_sk_mb_lesson_video($post){
  $url = get_post_meta($post->ID,'_sk_video',true);
  echo '<input type="text" name="sk_video" value="'.esc_attr($url).'" style="width:100%" placeholder="https://... (Vimeo/YouTube/Loom/CF Stream)" />';
}
add_action('save_post_sk_module', function($id){ if(isset($_POST['sk_course_id'])) update_post_meta($id,'_sk_course_id',(int)$_POST['sk_course_id']); });
add_action('save_post_sk_lesson', function($id){
  if(isset($_POST['sk_module_id'])) update_post_meta($id,'_sk_module_id',(int)$_POST['sk_module_id']);
  if(isset($_POST['sk_video'])) update_post_meta($id,'_sk_video',esc_url_raw($_POST['sk_video']));
});

function ecim_sk_modules_by_course($course_id){
  return get_posts(['post_type'=>'sk_module','numberposts'=>-1,'meta_key'=>'_sk_course_id','meta_value'=>$course_id,'orderby'=>'menu_order','order'=>'ASC']);
}
function ecim_sk_lessons_by_module($module_id){
  return get_posts(['post_type'=>'sk_lesson','numberposts'=>-1,'meta_key'=>'_sk_module_id','meta_value'=>$module_id,'orderby'=>'menu_order','order'=>'ASC']);
}

// ACF para Educadores (si ACF está activo)
add_action('init', function(){
  if(function_exists('acf_add_local_field_group')){
    acf_add_local_field_group([
      'key'=>'group_ecim_educadores',
      'title'=>'Datos del Educador',
      'fields'=>[
        ['key'=>'field_ecim_role','label'=>'Rol','name'=>'ecim_role','type'=>'text'],
        ['key'=>'field_ecim_avatar','label'=>'Foto (1:1)','name'=>'ecim_avatar','type'=>'image','return_format'=>'array','preview_size'=>'medium','library'=>'all'],
        ['key'=>'field_ecim_shortbio','label'=>'Bio corta','name'=>'ecim_shortbio','type'=>'textarea','rows'=>3,'new_lines'=>'br'],
        ['key'=>'field_ecim_links','label'=>'Redes','name'=>'ecim_links','type'=>'repeater','layout'=>'table','button_label'=>'+ Red','sub_fields'=>[
          ['key'=>'field_ecim_link_label','label'=>'Nombre','name'=>'label','type'=>'text'],
          ['key'=>'field_ecim_link_url','label'=>'URL','name'=>'url','type'=>'url'],
        ]],
        ['key'=>'field_ecim_courses','label'=>'Cursos','name'=>'ecim_courses','type'=>'relationship','post_type'=>['sk_course'],'return_format'=>'id'],
      ],
      'location'=>[[['param'=>'post_type','operator'=>'==','value'=>'sk_educator']]],
      'position'=>'acf_after_title','style'=>'seamless'
    ]);
  }
});
