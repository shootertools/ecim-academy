<?php
/* DISEÑO OSCURO POR SECCIONES - ECIM ACADEMY */
add_shortcode('sk_paywall_dark', function(){
  $qr   = trim( get_option('skwp_yape_qr_url','') );
  $yape = esc_html( get_option('skwp_yape_number','') );
  ob_start(); ?>
  
  <style>
    /* Estilos para el diseño oscuro de la página de pago */
    body.page-template-template-paywall {
        background: #1a1a1a !important;
        color: #ffffff !important;
    }
    
    body.page-template-template-paywall .site-header,
    body.page-template-template-paywall header,
    body.page-template-template-paywall #masthead {
        background: #1a1a1a !important;
        color: #ffffff !important;
    }
    
    body.page-template-template-paywall .site-header a,
    body.page-template-template-paywall header a,
    body.page-template-template-paywall #masthead a {
        color: #ffffff !important;
    }
    
    /* Ocultar barra de WordPress y opciones de registro si está logueado */
    body.logged-in.page-template-template-paywall #wpadminbar {
        display: none !important;
    }
    
    body.logged-in.page-template-template-paywall {
        margin-top: 0 !important;
    }
    
    body.page-template-template-paywall .site-footer,
    body.page-template-template-paywall footer {
        background: #1a1a1a !important;
        color: #ffffff !important;
    }
    
    body.page-template-template-paywall .site-footer a,
    body.page-template-template-paywall footer a {
        color: #ffffff !important;
    }
    
    /* Ocultar opciones de registro/login cuando el usuario está logueado */
    body.logged-in .sk-register-link,
    body.logged-in .sk-login-link,
    body.logged-in .register-link,
    body.logged-in .login-link,
    body.logged-in #sk-register-form,
    body.logged-in #sk-login-form,
    body.logged-in .auth-forms,
    body.logged-in .login-register-section {
        display: none !important;
    }
    
    .sk-paywall-dark {
        background: #1a1a1a;
        color: #ffffff;
        min-height: 100vh;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    .dark-paywall {
      background: #1a1a1a;
      min-height: 100vh;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      color: #ffffff;
    }
    .paywall-layout {
      display: flex;
      min-height: 100vh;
    }
    .payment-sidebar {
      width: 280px;
      background: #2d2d2d;
      padding: 2rem 0;
      position: fixed;
      height: 100vh;
      overflow-y: auto;
    }
    .sidebar-title {
      text-align: center;
      color: #ffffff;
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 2rem;
      padding: 0 1rem;
    }
    .payment-method {
      display: flex;
      align-items: center;
      padding: 1rem 1.5rem;
      margin: 0.5rem 1rem;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
      border: 2px solid transparent;
    }
    .payment-method:hover {
      background: rgba(255, 255, 255, 0.1);
    }
    .payment-method.active {
      background: #f39c12;
      border-color: #f39c12;
    }
    .method-icon {
      width: 40px;
      height: 40px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 1rem;
      font-size: 1.2rem;
      background: rgba(255, 255, 255, 0.1);
    }
    .method-text {
      font-weight: 600;
      font-size: 1rem;
    }
    .payment-content {
      margin-left: 280px;
      padding: 2rem;
      flex: 1;
    }
    .payment-section {
      display: none;
      background: #f5f5f5;
      border-radius: 20px;
      padding: 3rem;
      color: #333;
      max-width: 800px;
      margin: 0 auto;
    }
    .payment-section.active {
      display: block;
    }
    .section-title {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 1rem;
      color: #333;
    }
    .section-subtitle {
      font-size: 1.1rem;
      color: #666;
      margin-bottom: 2rem;
      line-height: 1.5;
    }
    .bank-list {
      display: grid;
      gap: 1rem;
      margin-bottom: 2rem;
    }
    .bank-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: white;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    .bank-logo {
      width: 60px;
      height: 40px;
      background: #007bff;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: bold;
      font-size: 0.9rem;
    }
    .bank-info {
      flex: 1;
      margin-left: 1rem;
    }
    .bank-name {
      font-weight: 600;
      color: #333;
      margin-bottom: 0.25rem;
    }
    .bank-account {
      font-family: 'Courier New', monospace;
      font-size: 1.1rem;
      font-weight: 600;
      color: #007bff;
    }
    .copy-btn {
      background: #28a745;
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    .copy-btn:hover {
      background: #218838;
      transform: translateY(-1px);
    }
    .yape-section {
      max-width: 800px;
      margin: 0 auto;
    }
    .yape-qr {
      background: white;
      border-radius: 12px;
      padding: 2rem;
      text-align: center;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      margin-bottom: 1.5rem;
    }
    .qr-frame {
      display: inline-block;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      padding: 1rem;
      margin: 0 auto 1.5rem;
    }
    .qr-code {
      width: 180px;
      height: 180px;
      display: block;
      margin: 0 auto;
    }
    .qr-code img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
    .yape-info {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      margin: 1.5rem auto;
      max-width: 500px;
    }
    .contact-btn {
      background: #25d366;
      color: white;
      border: none;
      padding: 1rem 2rem;
      border-radius: 12px;
      font-size: 1.1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 1rem;
    }
    .contact-btn:hover {
      background: #128c7e;
      transform: translateY(-2px);
    }
    .upload-section {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      margin-top: 2rem;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    .upload-title {
      font-size: 1.3rem;
      font-weight: 600;
      color: #333;
      margin-bottom: 1rem;
    }
    .form-row {
      display: flex;
      gap: 1rem;
      margin-bottom: 1rem;
    }
    .form-input {
      flex: 1;
      padding: 0.75rem;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 1rem;
    }
    .upload-btn {
      background: #f39c12;
      color: white;
      border: none;
      padding: 0.75rem 2rem;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 100%;
      margin-top: 1rem;
    }
    .upload-btn:hover {
      background: #e67e22;
    }
    .security-notice {
      background: #f8f9fa;
      padding: 1rem;
      border-radius: 8px;
      margin-top: 2rem;
      text-align: center;
      color: #666;
      font-size: 0.9rem;
    }
    @media (max-width: 768px) {
      .paywall-layout {
        flex-direction: column;
      }
      .payment-sidebar {
        position: relative;
        width: 100%;
        height: auto;
      }
      .payment-content {
        margin-left: 0;
        padding: 1rem;
      }
      .yape-section {
        flex-direction: column;
        align-items: center;
        gap: 1.5rem;
      }
      .yape-qr {
        min-width: auto;
        width: 100%;
        max-width: 280px;
      }
      .qr-frame {
        padding: 1rem;
        border-radius: 16px;
      }
      .qr-code {
        width: 180px;
        height: 180px;
      }
      .yape-info {
        min-width: auto;
        width: 100%;
        padding: 1.5rem;
        margin-top: 0;
      }
      .yape-info::before {
        top: -8px;
        right: 15px;
        width: 40px;
        height: 40px;
        font-size: 1.5rem;
      }
    }
    @media (max-width: 480px) {
      .qr-code {
        width: 160px;
        height: 160px;
      }
      .qr-frame {
        padding: 0.8rem;
      }
      .yape-info {
        padding: 1.2rem;
      }
    }
  </style>

  <div class="dark-paywall">
    <div class="paywall-layout">
      <!-- Sidebar -->
      <div class="payment-sidebar">
        <h2 class="sidebar-title">MÉTODOS DE PAGO</h2>
        
        <div class="payment-method active" data-method="yape">
          <div class="method-icon">📱</div>
          <span>Yape/Plin</span>
        </div>
        
        <div class="payment-method" data-method="transfer">
          <div class="method-icon">💰</div>
          <span>Transferencia o depósito</span>
        </div>
        
        <div class="payment-method" data-method="card">
          <div class="method-icon">💳</div>
          <span>Pago con tarjeta</span>
        </div>
        
        <div class="payment-method" data-method="gift">
          <div class="method-icon">🎁</div>
          <span>Gift Card</span>
        </div>
      </div>

      <!-- Content -->
      <div class="payment-content">
        <!-- Yape Section -->
        <div class="payment-section active" id="yape-section">
          <h1 class="section-title">Pago por YAPE / PLIN</h1>
          <p class="section-subtitle">Realiza tu pago por YAPE o realiza un PLIN al YAPE. Para ello escanea el QR mostrado o ingresa el número 937167682.</p>
          
          <div class="yape-section">
            <div class="yape-qr">
              <h3 style="margin: 0 0 1.5rem 0; color: #333; font-size: 1.5rem;">Escanea el código QR</h3>
              <div class="qr-frame">
                <?php if($qr): ?>
                  <img src="<?php echo esc_url($qr); ?>" alt="Código QR de Yape" class="qr-code" style="width: 180px; height: 180px;" />
                <?php else: ?>
                  <div style="width: 180px; height: 180px; display: flex; align-items: center; justify-content: center; color: #666;">
                    <div style="text-align: center;">
                      <div style="font-size: 2rem; margin-bottom: 0.5rem;">📱</div>
                      <div>QR no configurado</div>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
              <p style="margin: 1rem 0 0; color: #666;">o usa el número de teléfono:</p>
            </div>

            <div class="yape-info">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                  <div style="color: #666; margin-bottom: 0.25rem; font-size: 0.95rem;">Número de Yape:</div>
                  <div style="font-size: 1.25rem; font-weight: 600; color: #333; font-family: 'Courier New', monospace;">937 167 682</div>
                </div>
                <button class="copy-btn btn-copy" data-copy="937167682" style="background: #007bff;">Copiar</button>
              </div>
            </div>

            <p style="color: #666; margin: 1rem 0;">
              Luego de realizar el pago, sube tu comprobante para verificación:
            </p>

            <?php if(is_user_logged_in()): ?>
            <div class="upload-section">
              <h3 class="upload-title">📤 Subir comprobante de pago</h3>
              <form id="yape-proof-form" enctype="multipart/form-data">
                <div class="form-row">
                  <input type="file" name="voucher" accept="image/*,application/pdf" required class="form-input">
                  <input type="text" name="note" placeholder="Nota u observación (opcional)" class="form-input">
                </div>
                <button type="submit" class="upload-btn">📤 Enviar Comprobante</button>
                <div id="yape-proof-msg" style="margin-top: 0.5rem; color: #666;"></div>
              </form>
            </div>
            <?php endif; ?>
          </div>

          <div class="security-notice">
            🔒 Privacidad Absoluta<br>
            Certificado SSL para asegurar la transmisión de datos
          </div>
        </div>

        <!-- Transferencia Section -->
        <div class="payment-section" id="transfer-section">
          <h1 class="section-title">Transferencia o depósito</h1>
          <p class="section-subtitle">Puedes realizar el pago desde Banca por Internet, Banca Móvil o acercándote a cualquiera de los siguientes establecimientos:</p>
          
          <div class="bank-list">
            <div class="bank-item">
              <div class="bank-logo" style="background: #e30613; display: flex; align-items: center; justify-content: center; border-radius: 4px; overflow: hidden;">
                <svg width="40" height="24" viewBox="0 0 100 60" fill="white">
                  <rect width="100%" height="100%" fill="#e30613" />
                  <text x="50" y="38" text-anchor="middle" font-family="Arial, sans-serif" font-size="24" font-weight="bold" fill="white">BCP</text>
                </svg>
              </div>
              <div class="bank-info">
                <div class="bank-name">ECIM ACADEMY - Cuenta Soles</div>
                <div class="bank-account">3057205769051</div>
              </div>
              <button class="copy-btn btn-copy" data-copy="3057205769051">Copiar</button>
            </div>
            
            <div class="bank-item">
              <div class="bank-logo" style="background: #e30613; display: flex; align-items: center; justify-content: center; border-radius: 4px; overflow: hidden;">
                <svg width="40" height="24" viewBox="0 0 100 60" fill="white">
                  <rect width="100%" height="100%" fill="#e30613" />
                  <text x="50" y="38" text-anchor="middle" font-family="Arial, sans-serif" font-size="24" font-weight="bold" fill="white">BCP</text>
                </svg>
              </div>
              <div class="bank-info">
                <div class="bank-name">ECIM ACADEMY - CCI Soles</div>
                <div class="bank-account">00230500720576905112</div>
              </div>
              <button class="copy-btn btn-copy" data-copy="00230500720576905112">Copiar</button>
            </div>
          </div>

          <p style="text-align: center; margin: 2rem 0; color: #666;">
            Al depositar o hacer una transferencia a cualquiera de nuestras cuentas, sube tu comprobante para verificación:
          </p>

          <?php if(is_user_logged_in()): ?>
          <div class="upload-section">
            <h3 class="upload-title">📤 Subir comprobante de transferencia</h3>
            <form id="transfer-proof-form" enctype="multipart/form-data">
              <div class="form-row">
                <input type="file" name="voucher" accept="image/*,application/pdf" required class="form-input">
                <input type="text" name="note" placeholder="Nota u observación (opcional)" class="form-input">
              </div>
              <button type="submit" class="upload-btn">📤 Enviar Comprobante</button>
              <div id="transfer-proof-msg" style="margin-top: 0.5rem; color: #666;"></div>
            </form>
          </div>
          <?php else: ?>
            <p style="text-align: center; color: #666;">Inicia sesión para subir tu comprobante.</p>
          <?php endif; ?>

          <div class="security-notice">
            🔒 Privacidad Absoluta<br>
            Certificado SSL para asegurar la transmisión de datos
          </div>
        </div>

        <!-- Tarjeta Section -->
        <div class="payment-section" id="card-section">
          <h1 class="section-title">Pago con tarjeta de crédito o débito</h1>
          <p class="section-subtitle">Pago seguro con Stripe. Acceso inmediato una vez confirmado el pago.</p>
          
          <div style="text-align: center; padding: 3rem;">
            <div style="font-size: 4rem; margin-bottom: 2rem;">💳</div>
            <button id="sk-pay-card" class="contact-btn" style="background: #007bff;">
              💳 Pagar con Tarjeta
            </button>
            <div class="security-notice">
              🔒 Privacidad Absoluta<br>
              Certificado SSL para asegurar la transmisión de datos
            </div>
          </div>
        </div>

        <!-- Gift Card Section -->
        <div class="payment-section" id="gift-section">
          <h1 class="section-title">Gift Card</h1>
          <p class="section-subtitle">¿Tienes una tarjeta de regalo? Úsala aquí para acceder al curso.</p>
          
          <div style="text-align: center; padding: 3rem;">
            <div style="font-size: 4rem; margin-bottom: 2rem;">🎁</div>
            <p style="color: #666; margin-bottom: 2rem;">Próximamente disponible</p>
            <div class="security-notice">
              🔒 Privacidad Absoluta<br>
              Certificado SSL para asegurar la transmisión de datos
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para QR -->
    <div id="qr-modal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); display: none; align-items: center; justify-content: center; z-index: 9999;">
      <div style="position: relative;">
        <button class="qr-close" style="position: absolute; top: -40px; right: 0; background: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer;">&times;</button>
        <img id="qr-modal-img" src="" alt="QR" style="max-width: 90vw; max-height: 90vh; border-radius: 12px;" />
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Cambio de secciones
      const methods = document.querySelectorAll('.payment-method');
      const sections = document.querySelectorAll('.payment-section');

      methods.forEach(method => {
        method.addEventListener('click', function() {
          const targetMethod = this.getAttribute('data-method');
          
          // Remover active de todos los métodos
          methods.forEach(m => m.classList.remove('active'));
          // Agregar active al método clickeado
          this.classList.add('active');
          
          // Ocultar todas las secciones
          sections.forEach(s => s.classList.remove('active'));
          // Mostrar la sección correspondiente
          const targetSection = document.getElementById(targetMethod + '-section');
          if (targetSection) {
            targetSection.classList.add('active');
          }
        });
      });

      // Funcionalidad para botones copiar
      document.querySelectorAll('.btn-copy').forEach(button => {
        button.addEventListener('click', function() {
          const textToCopy = this.getAttribute('data-copy');
          
          // Usar la API moderna del portapapeles
          if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(textToCopy).then(() => {
              // Cambiar texto del botón temporalmente
              const originalText = this.textContent;
              this.textContent = '✓ Copiado';
              this.style.background = '#28a745';
              
              setTimeout(() => {
                this.textContent = originalText;
                this.style.background = '';
              }, 2000);
            }).catch(err => {
              console.error('Error al copiar: ', err);
              fallbackCopyTextToClipboard(textToCopy, this);
            });
          } else {
            // Fallback para navegadores más antiguos
            fallbackCopyTextToClipboard(textToCopy, this);
          }
        });
      });

      // Función fallback para copiar
      function fallbackCopyTextToClipboard(text, button) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        textArea.style.top = "-999999px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
          const successful = document.execCommand('copy');
          if (successful) {
            const originalText = button.textContent;
            button.textContent = '✓ Copiado';
            button.style.background = '#28a745';
            
            setTimeout(() => {
              button.textContent = originalText;
              button.style.background = '';
            }, 2000);
          }
        } catch (err) {
          console.error('Fallback: Error al copiar', err);
        }
        
        document.body.removeChild(textArea);
      }

      // Modal para QR
      document.querySelectorAll('.qr-zoom').forEach(qr => {
        qr.addEventListener('click', function() {
          const src = this.getAttribute('data-src') || this.src;
          if (src) {
            const modal = document.createElement('div');
            modal.style.cssText = `
              position: fixed;
              border-radius: 10px;
            `;
            
            modal.appendChild(img);
            document.body.appendChild(modal);
            
            modal.addEventListener('click', () => {
              document.body.removeChild(modal);
            });
          }
        });
      });

      // Formulario de comprobante de Yape
      const yapeForm = document.getElementById('yape-proof-form');
      if (yapeForm) {
        yapeForm.addEventListener('submit', function(e) {
          e.preventDefault();
          
          const formData = new FormData(this);
          formData.append('action', 'ecimsk_upload_voucher');
          formData.append('payment_method', 'yape');
          formData.append('nonce', ECIMSK.nonce);
          
          const submitBtn = this.querySelector('button[type="submit"]');
          const msgDiv = document.getElementById('yape-proof-msg');
          
          submitBtn.textContent = 'Enviando...';
          submitBtn.disabled = true;
          
          fetch(ECIMSK.ajaxurl, {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              msgDiv.textContent = '✓ Comprobante enviado correctamente. Te contactaremos pronto.';
              msgDiv.style.color = '#28a745';
              yapeForm.reset();
            } else {
              msgDiv.textContent = '✗ Error: ' + (data.data || 'No se pudo enviar el comprobante');
              msgDiv.style.color = '#dc3545';
            }
          })
          .catch(error => {
            msgDiv.textContent = '✗ Error de conexión. Inténtalo de nuevo.';
            msgDiv.style.color = '#dc3545';
          })
          .finally(() => {
            submitBtn.textContent = '📤 Enviar Comprobante';
            submitBtn.disabled = false;
          });
        });
      }

      // Formulario de comprobante de transferencia
      const transferForm = document.getElementById('transfer-proof-form');
      if (transferForm) {
        transferForm.addEventListener('submit', function(e) {
          e.preventDefault();
          
          const formData = new FormData(this);
          formData.append('action', 'ecimsk_upload_voucher');
          formData.append('payment_method', 'transfer');
          formData.append('nonce', ECIMSK.nonce);
          
          const submitBtn = this.querySelector('button[type="submit"]');
          const msgDiv = document.getElementById('transfer-proof-msg');
          
          submitBtn.textContent = 'Enviando...';
          submitBtn.disabled = true;
          
          fetch(ECIMSK.ajaxurl, {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              msgDiv.textContent = '✓ Comprobante enviado correctamente. Te contactaremos pronto.';
              msgDiv.style.color = '#28a745';
              transferForm.reset();
            } else {
              msgDiv.textContent = '✗ Error: ' + (data.data || 'No se pudo enviar el comprobante');
              msgDiv.style.color = '#dc3545';
            }
          })
          .catch(error => {
            msgDiv.textContent = '✗ Error de conexión. Inténtalo de nuevo.';
            msgDiv.style.color = '#dc3545';
          })
          .finally(() => {
            submitBtn.textContent = '📤 Enviar Comprobante';
            submitBtn.disabled = false;
          });
        });
      }
    });
  </script>
  
  <?php return ob_get_clean();
});
?>
