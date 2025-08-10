<?php
if ( ! defined('ABSPATH') ) { exit; }

// ====== CPT Portada ======
add_action('init', function(){
    register_post_type('sk_home', [
        'label' => 'Portada',
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-format-video',
        'supports' => ['title']
    ]);
});

// ====== Metabox ======
add_action('add_meta_boxes', function(){
    add_meta_box('ecim_home_fields', 'Contenido de Portada', 'ecim_home_fields_cb', 'sk_home', 'normal', 'high');
});

// ====== Encolar media uploader ======
add_action('admin_enqueue_scripts', function($hook){
    global $post;
    if( $hook == 'post.php' || $hook == 'post-new.php' ){
        if( isset($post) && $post->post_type === 'sk_home' ){
            wp_enqueue_media();
        }
    }
});

function ecim_home_fields_cb($post){
    $logo_id   = get_post_meta($post->ID,'ecim_home_logo_id',true);
    $video_id  = get_post_meta($post->ID,'ecim_home_video_id',true);
    $video_url = get_post_meta($post->ID,'ecim_home_video_url',true);

    wp_nonce_field('ecim_home_save','ecim_home_nonce');

    $logo_src  = $logo_id ? wp_get_attachment_image_url($logo_id,'medium') : '';
    $video_src = $video_id ? wp_get_attachment_url($video_id) : $video_url;
    ?>
    <p><strong>Logo</strong></p>
    <div>
        <img id="logo_preview" src="<?php echo esc_url($logo_src); ?>" style="max-height:60px;width:auto;display:block;margin-bottom:10px;">
        <input type="hidden" id="ecim_home_logo_id" name="ecim_home_logo_id" value="<?php echo esc_attr($logo_id); ?>">
        <button type="button" class="button" id="upload_logo_btn">Seleccionar Logo</button>
        <button type="button" class="button" id="remove_logo_btn">Quitar Logo</button>
    </div>
    <hr>
    <p><strong>Video MP4</strong></p>
    <div>
        <span id="video_filename"><?php echo esc_html( $video_id ? basename($video_src) : 'No hay video seleccionado' ); ?></span><br>
        <input type="hidden" id="ecim_home_video_id" name="ecim_home_video_id" value="<?php echo esc_attr($video_id); ?>">
        <button type="button" class="button" id="upload_video_btn">Seleccionar Video</button>
        <button type="button" class="button" id="remove_video_btn">Quitar Video</button>
    </div>
    <p>O URL directa del video:</p>
    <input type="url" name="ecim_home_video_url" value="<?php echo esc_attr($video_url); ?>" style="width:100%;">
    <script>
    jQuery(document).ready(function($){
        var logo_frame, video_frame;
        $('#upload_logo_btn').click(function(e){
            e.preventDefault();
            if(logo_frame){ logo_frame.open(); return; }
            logo_frame = wp.media({ title: 'Seleccionar Logo', button: { text: 'Usar este logo' }, multiple: false, library: { type: 'image' } });
            logo_frame.on('select', function(){
                var att = logo_frame.state().get('selection').first().toJSON();
                $('#ecim_home_logo_id').val(att.id);
                $('#logo_preview').attr('src', att.url);
            });
            logo_frame.open();
        });
        $('#remove_logo_btn').click(function(){
            $('#ecim_home_logo_id').val('');
            $('#logo_preview').attr('src','');
        });

        $('#upload_video_btn').click(function(e){
            e.preventDefault();
            if(video_frame){ video_frame.open(); return; }
            video_frame = wp.media({ title: 'Seleccionar Video', button: { text: 'Usar este video' }, multiple: false, library: { type: 'video' } });
            video_frame.on('select', function(){
                var att = video_frame.state().get('selection').first().toJSON();
                $('#ecim_home_video_id').val(att.id);
                $('#video_filename').text(att.filename);
            });
            video_frame.open();
        });
        $('#remove_video_btn').click(function(){
            $('#ecim_home_video_id').val('');
            $('#video_filename').text('No hay video seleccionado');
        });
    });
    </script>
    <?php
}

// ====== Guardar ======
add_action('save_post_sk_home', function($post_id){
    if(!isset($_POST['ecim_home_nonce']) || !wp_verify_nonce($_POST['ecim_home_nonce'], 'ecim_home_save')) return;
    update_post_meta($post_id,'ecim_home_logo_id', sanitize_text_field($_POST['ecim_home_logo_id']));
    update_post_meta($post_id,'ecim_home_video_url', sanitize_text_field($_POST['ecim_home_video_url']));
});

// ====== Panel de Comprobantes de Pago ======
add_action('admin_menu', function(){
    add_menu_page(
        'Comprobantes de Pago',
        'Comprobantes',
        'manage_options',
        'ecim-vouchers',
        'ecim_vouchers_page',
        'dashicons-money-alt',
        30
    );
});

function ecim_vouchers_page(){
    // Procesar acciones
    if(isset($_POST['action']) && isset($_POST['voucher_id'])){
        $voucher_id = sanitize_text_field($_POST['voucher_id']);
        $action = sanitize_text_field($_POST['action']);
        
        if(wp_verify_nonce($_POST['_wpnonce'], 'ecim_voucher_action')){
            $all_vouchers = get_option('ecimsk_pending_vouchers', []);
            
            if(isset($all_vouchers[$voucher_id])){
                $voucher = $all_vouchers[$voucher_id];
                
                if($action === 'approve'){
                    // Aprobar pago - marcar usuario como pagado
                    update_user_meta($voucher['user_id'], 'skwp_paid', 'yes');
                    
                    // Marcar comprobante como aprobado
                    $voucher['status'] = 'approved';
                    $voucher['approved_at'] = current_time('mysql');
                    $all_vouchers[$voucher_id] = $voucher;
                    
                    // Mover a comprobantes aprobados
                    $approved_vouchers = get_option('ecimsk_approved_vouchers', []);
                    $approved_vouchers[$voucher_id] = $voucher;
                    update_option('ecimsk_approved_vouchers', $approved_vouchers);
                    
                    // Remover de pendientes
                    unset($all_vouchers[$voucher_id]);
                    update_option('ecimsk_pending_vouchers', $all_vouchers);
                    
                    echo '<div class="notice notice-success"><p>Pago aprobado correctamente.</p></div>';
                    
                } elseif($action === 'reject'){
                    // Rechazar pago
                    $voucher['status'] = 'rejected';
                    $voucher['rejected_at'] = current_time('mysql');
                    
                    // Mover a comprobantes rechazados
                    $rejected_vouchers = get_option('ecimsk_rejected_vouchers', []);
                    $rejected_vouchers[$voucher_id] = $voucher;
                    update_option('ecimsk_rejected_vouchers', $rejected_vouchers);
                    
                    // Remover de pendientes
                    unset($all_vouchers[$voucher_id]);
                    update_option('ecimsk_pending_vouchers', $all_vouchers);
                    
                    echo '<div class="notice notice-error"><p>Pago rechazado.</p></div>';
                }
            }
        }
    }
    
    $pending_vouchers = get_option('ecimsk_pending_vouchers', []);
    $approved_vouchers = get_option('ecimsk_approved_vouchers', []);
    $rejected_vouchers = get_option('ecimsk_rejected_vouchers', []);
    ?>
    <div class="wrap ecim-admin-wrap">
        <style>
        .ecim-admin-wrap {
            background: #f8fafc;
            margin: 20px 0 0 -20px;
            padding: 20px;
            min-height: calc(100vh - 32px);
        }
        .ecim-admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .ecim-admin-title {
            font-size: 2rem;
            margin: 0 0 0.5rem 0;
            font-weight: 700;
        }
        .ecim-admin-subtitle {
            opacity: 0.9;
            font-size: 1.1rem;
            margin: 0;
        }
        .ecim-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .ecim-stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid;
            transition: transform 0.2s ease;
        }
        .ecim-stat-card:hover {
            transform: translateY(-2px);
        }
        .ecim-stat-card.pending {
            border-left-color: #f59e0b;
        }
        .ecim-stat-card.approved {
            border-left-color: #10b981;
        }
        .ecim-stat-card.rejected {
            border-left-color: #ef4444;
        }
        .ecim-stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            line-height: 1;
        }
        .ecim-stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            margin: 0.5rem 0 0 0;
        }
        .ecim-tabs {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .ecim-tab-nav {
            display: flex;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }
        .ecim-tab-btn {
            flex: 1;
            padding: 1rem 1.5rem;
            border: none;
            background: transparent;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
            position: relative;
        }
        .ecim-tab-btn.active {
            background: white;
            color: #3b82f6;
        }
        .ecim-tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: #3b82f6;
        }
        .ecim-tab-content {
            padding: 2rem;
        }
        .ecim-voucher-grid {
            display: grid;
            gap: 1rem;
        }
        .ecim-voucher-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            transition: all 0.2s ease;
        }
        .ecim-voucher-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-color: #3b82f6;
        }
        .ecim-voucher-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        .ecim-voucher-user {
            font-weight: 600;
            font-size: 1.1rem;
        }
        .ecim-voucher-email {
            color: #6b7280;
            font-size: 0.9rem;
        }
        .ecim-voucher-method {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .ecim-voucher-method.yape {
            background: #fef3c7;
            color: #92400e;
        }
        .ecim-voucher-method.transfer {
            background: #dbeafe;
            color: #1e40af;
        }
        .ecim-voucher-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .ecim-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .ecim-btn-approve {
            background: #10b981;
            color: white;
        }
        .ecim-btn-approve:hover {
            background: #059669;
        }
        .ecim-btn-reject {
            background: #ef4444;
            color: white;
        }
        .ecim-btn-reject:hover {
            background: #dc2626;
        }
        .ecim-btn-view {
            background: #3b82f6;
            color: white;
        }
        .ecim-btn-view:hover {
            background: #2563eb;
        }
        .ecim-empty-state {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }
        .ecim-empty-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        @media (max-width: 768px) {
            .ecim-admin-wrap {
                margin: 10px 0 0 -10px;
                padding: 10px;
            }
            .ecim-admin-header {
                padding: 1.5rem;
            }
            .ecim-admin-title {
                font-size: 1.5rem;
            }
            .ecim-stats-grid {
                grid-template-columns: 1fr;
            }
            .ecim-tab-nav {
                flex-direction: column;
            }
            .ecim-voucher-header {
                flex-direction: column;
                gap: 0.5rem;
            }
            .ecim-voucher-actions {
                flex-direction: column;
            }
        }
        </style>
        
        <div class="ecim-admin-header">
            <h1 class="ecim-admin-title">💳 Gestión de Comprobantes</h1>
            <p class="ecim-admin-subtitle">Administra y aprueba los comprobantes de pago de tus estudiantes</p>
        </div>
        
        <div class="ecim-stats-grid">
            <div class="ecim-stat-card pending">
                <h2 class="ecim-stat-number" style="color: #f59e0b;"><?php echo count($pending_vouchers); ?></h2>
                <p class="ecim-stat-label">Pendientes</p>
            </div>
            <div class="ecim-stat-card approved">
                <h2 class="ecim-stat-number" style="color: #10b981;"><?php echo count($approved_vouchers); ?></h2>
                <p class="ecim-stat-label">Aprobados</p>
            </div>
            <div class="ecim-stat-card rejected">
                <h2 class="ecim-stat-number" style="color: #ef4444;"><?php echo count($rejected_vouchers); ?></h2>
                <p class="ecim-stat-label">Rechazados</p>
            </div>
        </div>
        
        <div class="ecim-tabs">
            <div class="ecim-tab-nav">
                <button class="ecim-tab-btn active" onclick="showTab('pending')" data-tab="pending">
                    🕐 Pendientes (<?php echo count($pending_vouchers); ?>)
                </button>
                <button class="ecim-tab-btn" onclick="showTab('approved')" data-tab="approved">
                    ✅ Aprobados (<?php echo count($approved_vouchers); ?>)
                </button>
                <button class="ecim-tab-btn" onclick="showTab('rejected')" data-tab="rejected">
                    ❌ Rechazados (<?php echo count($rejected_vouchers); ?>)
                </button>
            </div>
        
            <!-- Comprobantes Pendientes -->
            <div id="pending-tab" class="ecim-tab-content">
                <?php if(empty($pending_vouchers)): ?>
                    <div class="ecim-empty-state">
                        <div class="ecim-empty-icon">🕐</div>
                        <h3>No hay comprobantes pendientes</h3>
                        <p>Los nuevos comprobantes aparecerán aquí cuando los estudiantes los envíen.</p>
                    </div>
                <?php else: ?>
                    <div class="ecim-voucher-grid">
                        <?php foreach($pending_vouchers as $id => $voucher): ?>
                        <div class="ecim-voucher-card">
                            <div class="ecim-voucher-header">
                                <div>
                                    <div class="ecim-voucher-user"><?php echo esc_html($voucher['user_name']); ?></div>
                                    <div class="ecim-voucher-email"><?php echo esc_html($voucher['user_email']); ?></div>
                                </div>
                                <span class="ecim-voucher-method <?php echo esc_attr($voucher['payment_method']); ?>">
                                    <?php echo $voucher['payment_method'] === 'yape' ? '📱 YAPE' : '🏦 Transferencia'; ?>
                                </span>
                            </div>
                            
                            <div style="margin: 1rem 0;">
                                <strong>📄 Archivo:</strong> 
                                <a href="<?php echo esc_url($voucher['file_url']); ?>" target="_blank" style="color: #3b82f6;">
                                    <?php echo esc_html($voucher['filename']); ?>
                                </a>
                            </div>
                            
                            <?php if(!empty($voucher['note'])): ?>
                            <div style="margin: 1rem 0; padding: 0.75rem; background: #f8fafc; border-radius: 6px;">
                                <strong>💬 Nota:</strong> <?php echo esc_html($voucher['note']); ?>
                            </div>
                            <?php endif; ?>
                            
                            <div style="margin: 1rem 0; color: #6b7280; font-size: 0.9rem;">
                                <strong>📅 Enviado:</strong> <?php echo esc_html(date('d/m/Y H:i', strtotime($voucher['uploaded_at']))); ?>
                            </div>
                            
                            <div class="ecim-voucher-actions">
                                <a href="<?php echo esc_url($voucher['file_url']); ?>" target="_blank" class="ecim-btn ecim-btn-view">
                                    👁️ Ver Comprobante
                                </a>
                                <form method="post" style="display: inline;">
                                    <?php wp_nonce_field('ecim_voucher_action'); ?>
                                    <input type="hidden" name="voucher_id" value="<?php echo esc_attr($id); ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="ecim-btn ecim-btn-approve" onclick="return confirm('¿Aprobar este pago y dar acceso al estudiante?')">
                                        ✅ Aprobar
                                    </button>
                                </form>
                                <form method="post" style="display: inline;">
                                    <?php wp_nonce_field('ecim_voucher_action'); ?>
                                    <input type="hidden" name="voucher_id" value="<?php echo esc_attr($id); ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="ecim-btn ecim-btn-reject" onclick="return confirm('¿Rechazar este pago?')">
                                        ❌ Rechazar
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        
            <!-- Comprobantes Aprobados -->
            <div id="approved-tab" class="ecim-tab-content" style="display: none;">
                <?php if(empty($approved_vouchers)): ?>
                    <div class="ecim-empty-state">
                        <div class="ecim-empty-icon">✅</div>
                        <h3>No hay comprobantes aprobados</h3>
                        <p>Los comprobantes aprobados aparecerán aquí.</p>
                    </div>
                <?php else: ?>
                    <div class="ecim-voucher-grid">
                        <?php foreach($approved_vouchers as $voucher): ?>
                        <div class="ecim-voucher-card" style="border-left: 4px solid #10b981;">
                            <div class="ecim-voucher-header">
                                <div>
                                    <div class="ecim-voucher-user"><?php echo esc_html($voucher['user_name']); ?></div>
                                    <div class="ecim-voucher-email"><?php echo esc_html($voucher['user_email']); ?></div>
                                </div>
                                <span class="ecim-voucher-method <?php echo esc_attr($voucher['payment_method']); ?>">
                                    <?php echo $voucher['payment_method'] === 'yape' ? '📱 YAPE' : '🏦 Transferencia'; ?>
                                </span>
                            </div>
                            
                            <div style="margin: 1rem 0;">
                                <strong>📄 Archivo:</strong> 
                                <a href="<?php echo esc_url($voucher['file_url']); ?>" target="_blank" style="color: #3b82f6;">
                                    <?php echo esc_html($voucher['filename']); ?>
                                </a>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin: 1rem 0; color: #6b7280; font-size: 0.9rem;">
                                <div>
                                    <strong>📅 Enviado:</strong><br>
                                    <?php echo esc_html(date('d/m/Y H:i', strtotime($voucher['uploaded_at']))); ?>
                                </div>
                                <div>
                                    <strong>✅ Aprobado:</strong><br>
                                    <?php echo esc_html(date('d/m/Y H:i', strtotime($voucher['approved_at']))); ?>
                                </div>
                            </div>
                            
                            <div class="ecim-voucher-actions">
                                <a href="<?php echo esc_url($voucher['file_url']); ?>" target="_blank" class="ecim-btn ecim-btn-view">
                                    👁️ Ver Comprobante
                                </a>
                                <span style="background: #dcfce7; color: #166534; padding: 0.5rem 1rem; border-radius: 6px; font-weight: 600; font-size: 0.8rem;">
                                    ✅ APROBADO
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        
            <!-- Comprobantes Rechazados -->
            <div id="rejected-tab" class="ecim-tab-content" style="display: none;">
                <?php if(empty($rejected_vouchers)): ?>
                    <div class="ecim-empty-state">
                        <div class="ecim-empty-icon">❌</div>
                        <h3>No hay comprobantes rechazados</h3>
                        <p>Los comprobantes rechazados aparecerán aquí.</p>
                    </div>
                <?php else: ?>
                    <div class="ecim-voucher-grid">
                        <?php foreach($rejected_vouchers as $voucher): ?>
                        <div class="ecim-voucher-card" style="border-left: 4px solid #ef4444;">
                            <div class="ecim-voucher-header">
                                <div>
                                    <div class="ecim-voucher-user"><?php echo esc_html($voucher['user_name']); ?></div>
                                    <div class="ecim-voucher-email"><?php echo esc_html($voucher['user_email']); ?></div>
                                </div>
                                <span class="ecim-voucher-method <?php echo esc_attr($voucher['payment_method']); ?>">
                                    <?php echo $voucher['payment_method'] === 'yape' ? '📱 YAPE' : '🏦 Transferencia'; ?>
                                </span>
                            </div>
                            
                            <div style="margin: 1rem 0;">
                                <strong>📄 Archivo:</strong> 
                                <a href="<?php echo esc_url($voucher['file_url']); ?>" target="_blank" style="color: #3b82f6;">
                                    <?php echo esc_html($voucher['filename']); ?>
                                </a>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin: 1rem 0; color: #6b7280; font-size: 0.9rem;">
                                <div>
                                    <strong>📅 Enviado:</strong><br>
                                    <?php echo esc_html(date('d/m/Y H:i', strtotime($voucher['uploaded_at']))); ?>
                                </div>
                                <div>
                                    <strong>❌ Rechazado:</strong><br>
                                    <?php echo esc_html(date('d/m/Y H:i', strtotime($voucher['rejected_at']))); ?>
                                </div>
                            </div>
                            
                            <div class="ecim-voucher-actions">
                                <a href="<?php echo esc_url($voucher['file_url']); ?>" target="_blank" class="ecim-btn ecim-btn-view">
                                    👁️ Ver Comprobante
                                </a>
                                <span style="background: #fee2e2; color: #991b1b; padding: 0.5rem 1rem; border-radius: 6px; font-weight: 600; font-size: 0.8rem;">
                                    ❌ RECHAZADO
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <script>
        function showTab(tabName) {
            // Ocultar todas las pestañas
            document.querySelectorAll('.ecim-tab-content').forEach(tab => {
                tab.style.display = 'none';
            });
            
            // Remover clase activa de todos los botones
            document.querySelectorAll('.ecim-tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Mostrar pestaña seleccionada
            document.getElementById(tabName + '-tab').style.display = 'block';
            
            // Agregar clase activa al botón clickeado
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
        }
        </script>
    <?php
}
?>
