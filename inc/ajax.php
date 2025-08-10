<?php
if ( ! defined('ABSPATH') ) { exit; }

// Función de prueba AJAX
add_action('wp_ajax_nopriv_ecimsk_test','ecimsk_test');
add_action('wp_ajax_ecimsk_test','ecimsk_test');
function ecimsk_test(){
  wp_send_json_success(['message'=>'AJAX funciona correctamente!','timestamp'=>current_time('mysql')]);
}

// Registro
add_action('wp_ajax_nopriv_ecimsk_register','ecimsk_register');
function ecimsk_register(){
  // Debug: Log de datos recibidos
  error_log('ECIM DEBUG - Registro recibido: ' . print_r($_POST, true));
  
  // Verificación de nonce más flexible
  if(!wp_verify_nonce($_POST['nonce'] ?? '', 'ecimsk_nonce')){
    error_log('ECIM DEBUG - Nonce falló. Nonce recibido: ' . ($_POST['nonce'] ?? 'VACIO'));
    wp_send_json_error(['message'=>'Error de seguridad. Recarga la página e intenta de nuevo.']);
  }
  $email = sanitize_email($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  $fn = sanitize_text_field($_POST['first_name'] ?? '');
  $ln = sanitize_text_field($_POST['last_name'] ?? '');
  if(!$email || !$pass){ wp_send_json_error(['message'=>'Completa correo y contraseña.']); }
  if(email_exists($email)){ wp_send_json_error(['message'=>'Este correo ya está registrado.']); }
  $uid = wp_create_user($email,$pass,$email);
  if(is_wp_error($uid)){ wp_send_json_error(['message'=>$uid->get_error_message()]); }
  wp_update_user(['ID'=>$uid,'display_name'=>trim($fn.' '.$ln) ?: $email,'first_name'=>$fn,'last_name'=>$ln]);
  wp_set_current_user($uid); wp_set_auth_cookie($uid);
  wp_send_json_success(['redirect'=>home_url('/pago')]);
}

// Login
add_action('wp_ajax_nopriv_ecimsk_login','ecimsk_login');
function ecimsk_login(){
  // Debug: Log de datos recibidos
  error_log('ECIM DEBUG - Login recibido: ' . print_r($_POST, true));
  
  // Verificación de nonce más flexible
  if(!wp_verify_nonce($_POST['nonce'] ?? '', 'ecimsk_nonce')){
    error_log('ECIM DEBUG - Nonce login falló. Nonce recibido: ' . ($_POST['nonce'] ?? 'VACIO'));
    wp_send_json_error(['message'=>'Error de seguridad. Recarga la página e intenta de nuevo.']);
  }
  $email = sanitize_email($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  $user = wp_signon(['user_login'=>$email,'user_password'=>$pass,'remember'=>true], false);
  if(is_wp_error($user)){ wp_send_json_error(['message'=>'Credenciales inválidas.']); }
  $uid = $user->ID;
  $paid = get_user_meta($uid,'skwp_paid',true) === 'yes';
  $mods = (array)get_user_meta($uid,'skwp_enrolled_modules',true);
  update_user_meta($uid,'ecimsk_after_login', (!$paid && empty($mods)) ? 'pago' : '');
  $dest = (!$paid && empty($mods)) ? home_url('/pago') : home_url('/plataforma');
  wp_send_json_success(['redirect'=>$dest]);
}

// Completar lección
add_action('wp_ajax_ecimsk_toggle_complete','ecimsk_toggle_complete');
function ecimsk_toggle_complete(){
  check_ajax_referer('ecimsk_nonce','nonce');
  if(!is_user_logged_in()){ wp_send_json_error(['message'=>'Inicia sesión.']); }
  $lid = intval($_POST['lesson_id'] ?? 0); if(!$lid){ wp_send_json_error(['message'=>'Lección inválida']); }
  $uid = get_current_user_id();
  $arr = array_map('intval',(array)get_user_meta($uid,'skwp_completed_lessons',true));
  if(in_array($lid,$arr,true)){ $arr = array_values(array_diff($arr,[$lid])); $done=false; }
  else{ $arr[]=$lid; $arr=array_values(array_unique($arr)); $done=true; }
  update_user_meta($uid,'skwp_completed_lessons',$arr);
  wp_send_json_success(['done'=>$done]);
}

// Subir comprobante de pago
add_action('wp_ajax_ecimsk_upload_voucher','ecimsk_upload_voucher');
function ecimsk_upload_voucher(){
  // Debug: Log de datos recibidos
  error_log('ECIM DEBUG - Comprobante recibido: ' . print_r($_POST, true));
  error_log('ECIM DEBUG - Archivos recibidos: ' . print_r($_FILES, true));
  
  // Verificación de nonce
  if(!wp_verify_nonce($_POST['nonce'] ?? '', 'ecimsk_nonce')){
    error_log('ECIM DEBUG - Nonce comprobante falló. Nonce recibido: ' . ($_POST['nonce'] ?? 'VACIO'));
    wp_send_json_error(['message'=>'Error de seguridad. Recarga la página e intenta de nuevo.']);
  }
  
  // Verificar que el usuario esté logueado
  if(!is_user_logged_in()){ 
    wp_send_json_error(['message'=>'Debes iniciar sesión para subir comprobantes.']); 
  }
  
  // Verificar que se haya enviado un archivo
  if(empty($_FILES['voucher']) || $_FILES['voucher']['error'] !== UPLOAD_ERR_OK){
    wp_send_json_error(['message'=>'Debes seleccionar un archivo válido.']);
  }
  
  $user_id = get_current_user_id();
  $user = get_userdata($user_id);
  $payment_method = sanitize_text_field($_POST['payment_method'] ?? 'yape');
  $note = sanitize_textarea_field($_POST['note'] ?? '');
  
  // Configurar el directorio de subida
  $upload_dir = wp_upload_dir();
  $voucher_dir = $upload_dir['basedir'] . '/vouchers';
  
  // Crear directorio si no existe
  if(!file_exists($voucher_dir)){
    wp_mkdir_p($voucher_dir);
  }
  
  // Validar tipo de archivo
  $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
  $file_type = $_FILES['voucher']['type'];
  
  if(!in_array($file_type, $allowed_types)){
    wp_send_json_error(['message'=>'Tipo de archivo no permitido. Solo se permiten imágenes (JPG, PNG, GIF) y PDF.']);
  }
  
  // Generar nombre único para el archivo
  $file_extension = pathinfo($_FILES['voucher']['name'], PATHINFO_EXTENSION);
  $filename = 'voucher_' . $user_id . '_' . time() . '.' . $file_extension;
  $file_path = $voucher_dir . '/' . $filename;
  
  // Mover el archivo subido
  if(!move_uploaded_file($_FILES['voucher']['tmp_name'], $file_path)){
    wp_send_json_error(['message'=>'Error al subir el archivo. Inténtalo de nuevo.']);
  }
  
  // Guardar información del comprobante en la base de datos
  $voucher_data = [
    'user_id' => $user_id,
    'user_email' => $user->user_email,
    'user_name' => $user->display_name,
    'payment_method' => $payment_method,
    'filename' => $filename,
    'file_path' => $file_path,
    'file_url' => $upload_dir['baseurl'] . '/vouchers/' . $filename,
    'note' => $note,
    'status' => 'pending',
    'uploaded_at' => current_time('mysql'),
  ];
  
  // Guardar en meta del usuario
  $user_vouchers = get_user_meta($user_id, 'ecimsk_vouchers', true) ?: [];
  $user_vouchers[] = $voucher_data;
  update_user_meta($user_id, 'ecimsk_vouchers', $user_vouchers);
  
  // Guardar en opción global para el panel de administración
  $all_vouchers = get_option('ecimsk_pending_vouchers', []);
  $voucher_data['id'] = uniqid('voucher_');
  $all_vouchers[$voucher_data['id']] = $voucher_data;
  update_option('ecimsk_pending_vouchers', $all_vouchers);
  
  // Enviar notificación por email al administrador (opcional)
  $admin_email = get_option('admin_email');
  $subject = 'Nuevo comprobante de pago - ECIM Academy';
  $message = "Se ha recibido un nuevo comprobante de pago:\n\n";
  $message .= "Usuario: {$user->display_name} ({$user->user_email})\n";
  $message .= "Método de pago: " . ucfirst($payment_method) . "\n";
  $message .= "Nota: {$note}\n";
  $message .= "Archivo: {$voucher_data['file_url']}\n";
  $message .= "Fecha: " . date('d/m/Y H:i:s') . "\n\n";
  $message .= "Revisa el panel de administración para aprobar el pago.";
  
  wp_mail($admin_email, $subject, $message);
  
  error_log('ECIM DEBUG - Comprobante guardado exitosamente: ' . $filename);
  wp_send_json_success([
    'message' => 'Comprobante enviado correctamente. Te contactaremos pronto para confirmar tu pago.',
    'filename' => $filename
  ]);
}
