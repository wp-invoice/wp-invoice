<?php
/**
 * Application namespace
 */

namespace UsabilityDynamics\WPIES {

  /**
   * Class Application
   * @package UsabilityDynamics\WPIES
   */
  class Application {

    /**
     * Init things
     */
    public function __construct() {

      /**
       * Determine we are on invoice page
       */
      add_action( 'wpi_template_redirect', array( $this, 'invoice_template_redirect' ));
      add_action( 'wpi_unified_page_scripts', array( $this, 'invoice_template_redirect' ));

      /**
       * Insert signature UI to invoice page
       */
      add_action( 'wpi_after_payment_fields', array( $this, 'signature_ui' ) );

      /**
       * Process signature prior to payment
       */
      add_action( 'wpi_before_process_payment', array( $this, 'handle_signature' ) );

      /**
       * Insert signature result onto receipt page
       */
      add_action( 'wpi_front_end_right_col_bottom', array( $this, 'invoice_receipt_signature' ) );
      add_action( 'wpi_unified_template_before_actions_history', array( $this, 'invoice_receipt_signature' ) );

      /**
       * Add options to edit invoice page
       */
      add_action( 'wpi_payment_options_box', array( $this, 'invoice_options' ) );

      /**
       * Early init things
       */
      add_action( 'init', array( $this, 'init' ), 50 );

    }

    /**
     * Early init (50)
     */
    public function init() {
      global $wpi_settings;
      add_action('wpi_pre_header_' . $wpi_settings['pages']['edit'], array( $this, 'signature_metaboxes' ));
      add_filter('wpi_invoice_pre_save', array( $this, 'invoice_pre_save' ), 10, 2);
    }

    /**
     * Metabox for edit invoice page
     */
    public function signature_metaboxes() {
      global $wpi_settings;
      wp_enqueue_style( 'wpi-signature-pad-css', ud_get_wp_invoice_electronic_signature()->path( 'static/scripts/vendor/signature/jquery.signaturepad.css', 'url' ), array(), ud_get_wp_invoice_electronic_signature()->version );
      wp_enqueue_script( 'wpi-signature-pad-js', ud_get_wp_invoice_electronic_signature()->path( 'static/scripts/vendor/signature/jquery.signaturepad.js', 'url' ), array('jquery'), ud_get_wp_invoice_electronic_signature()->version );
      add_meta_box( 'wpies_signature', __( 'Signature Information', ud_get_wp_invoice_electronic_signature()->domain ), array($this, 'signature_metabox_renderer'), $wpi_settings['pages']['edit'], 'side', 'high' );
    }

    /**
     * Render metabox for edit invoice page
     */
    public function signature_metabox_renderer( $invoice, $metabox ) {

      if ( !empty( $invoice['require_signature'] ) && $invoice['require_signature'] == "on" ) {
        $_signature = $this->get_signature_data( $invoice['ID'] );
        $_signature = apply_filters( 'wpies_signature_metabox_data', $_signature );
        if ( !empty( $_signature ) ) {
          ?>
          <script type="application/javascript">
            var sig = <?php echo $_signature['data']; ?>;
            jQuery(document).ready(function () {
              jQuery('.signedPad').signaturePad({displayOnly:true}).regenerate(sig);
            });
          </script>

          <style>
            #wpies_signature label { font-weight: bold; }
            #wpies_signature p { border-bottom: 1px solid #eeeeee; }
          </style>

          <?php do_action( 'wpies_signature_metabox_data_begin', $_signature ); ?>
          <label><?php _e( 'Signee Name:', ud_get_wp_invoice_electronic_signature()->domain ); ?></label>
          <p><?php echo $_signature['name']; ?></p>

          <label><?php _e( 'Signee IP:', ud_get_wp_invoice_electronic_signature()->domain ); ?></label>
          <p><?php echo $_signature['ip']; ?></p>

          <label><?php _e( 'Date/Time:', ud_get_wp_invoice_electronic_signature()->domain ); ?></label>
          <p><?php echo date( get_option('date_format').' '.get_option('time_format'), $_signature['time']); ?></p>

          <label><?php _e( 'Signature:', ud_get_wp_invoice_electronic_signature()->domain ); ?></label>
          <div class="signedPad signed">
            <div class="sigWrapper" style="height: auto;overflow-x:scroll;">
              <canvas class="pad" width="370" height="130"></canvas>
            </div>
          </div>

          <?php do_action( 'wpies_signature_metabox_data_end', $_signature ); ?>

          <?php
        } else {
          if ( empty($invoice['ID']) || !wpi_is_full_paid_invoice( $invoice['invoice_id'] ) || !($invoice[ 'subtotal' ] - $invoice[ 'total_discount' ]) ) {
            _e('Signature will be displayed here once invoice is paid.');
            return;
          }
        }
      } else {
        return _e( 'Signature is not required for this invoice.', ud_get_wp_invoice_electronic_signature()->domain );
      }
    }

    /**
     * Save custom invoice data
     * @param $ni
     * @param $data
     * @return mixed
     */
    public function invoice_pre_save( $ni, $data ) {
      $ni->set(array(
        'require_signature' => $data['require_signature']
      ));
      return $ni;
    }

    /**
     * Edit invoice options UI
     * @param $this_invoice
     */
    public function invoice_options( $this_invoice ) {
      include ud_get_wp_invoice_electronic_signature()->path( 'static/views/signature-invoice-options.php', 'dir' );
    }

    /**
     * Get signature data
     * @param $invoice_id
     * @return array
     */
    private function get_signature_data( $invoice_id ) {
      return array_filter( array(
        'name' => get_post_meta( $invoice_id, 'signature_name', 1 ),
        'data' => get_post_meta( $invoice_id, 'signature_data', 1 ) ? json_encode(get_post_meta( $invoice_id, 'signature_data', 1 )) : false,
        'ip' => get_post_meta( $invoice_id, 'signature_ip', 1 ),
        'time' => get_post_meta( $invoice_id, 'signature_time', 1 )
      ) );
    }

    /**
     * Show signature on receipt page
     */
    public function invoice_receipt_signature() {
      global $invoice;

      if ( $invoice['post_status'] != 'paid' || ( empty($invoice['require_signature']) || $invoice['require_signature'] == "off" ) ) return;

      $signature = $this->get_signature_data( $invoice['ID'] );

      include ud_get_wp_invoice_electronic_signature()->path( 'static/views/signature-output-markup.php', 'dir' );
    }

    /**
     * Show signature input on invoice page
     */
    public function signature_ui() {
      global $invoice;

      if ( empty( $invoice['require_signature'] ) || $invoice['require_signature'] == 'off' ) return;

      include ud_get_wp_invoice_electronic_signature()->path( 'static/views/signature-input-markup.php', 'dir' );
    }

    /**
     * Place where we enqueue assets for front-end
     * @param $data
     */
    public function invoice_template_redirect( $data ) {

      /**
       * Do this only if signature is required for current invoice
       */
      if ( empty( $data->data['require_signature'] ) || $data->data['require_signature'] == 'off' ) return;

      wp_enqueue_style( 'wpi-signature-pad-css', ud_get_wp_invoice_electronic_signature()->path( 'static/scripts/vendor/signature/jquery.signaturepad.css', 'url' ), array(), ud_get_wp_invoice_electronic_signature()->version );
      wp_enqueue_style( 'wpi-signature-pad-custom-css', ud_get_wp_invoice_electronic_signature()->path( 'static/styles/application.css', 'url' ), array(), ud_get_wp_invoice_electronic_signature()->version );
      wp_enqueue_script( 'wpi-signature-pad-js', ud_get_wp_invoice_electronic_signature()->path( 'static/scripts/vendor/signature/jquery.signaturepad.js', 'url' ), array('jquery'), ud_get_wp_invoice_electronic_signature()->version );
      wp_enqueue_script( 'wpi-flashcanvas-js', ud_get_wp_invoice_electronic_signature()->path( 'static/scripts/vendor/signature/flashcanvas.js', 'url' ), array('wpi-signature-pad-js'), ud_get_wp_invoice_electronic_signature()->version );
      wp_enqueue_script( 'wpi-json2-js', ud_get_wp_invoice_electronic_signature()->path( 'static/scripts/vendor/signature/json2.min.js', 'url' ), array('wpi-signature-pad-js'), ud_get_wp_invoice_electronic_signature()->version);
      wp_enqueue_script( 'wpi-signature-app', ud_get_wp_invoice_electronic_signature()->path( 'static/scripts/application.js', 'url' ), array('wpi-signature-pad-js'), ud_get_wp_invoice_electronic_signature()->version );
    }

    /**
     * Process signature prior payment
     * @param $invoice
     */
    public function handle_signature( $invoice ) {

      /**
       * Process only if required
       */
      if ( empty( $invoice['require_signature'] ) || $invoice['require_signature'] == "off" ) return;

      $name   = $_POST['signature']['name'];
      $output = $_POST['signature']['output'];

      /**
       * If some of required data is empty - show error
       */
      if ( empty( $name ) || empty( $output ) ) wpi_send_json_error( array('messages' => array(__('Signature data incomplete. Aborting payment.', ud_get_wp_invoice_electronic_signature()->domain))) );

      /**
       * If signature is invalid
       */
      if ( !$signature_data = json_decode( stripslashes( $output ) ) ) wpi_send_json_error( array('messages' => array(__('Signature drawing is invalid. Aborting payment.', ud_get_wp_invoice_electronic_signature()->domain))) );

      /**
       * Put information into invoice object
       */
      if (
        update_post_meta( $invoice['ID'], 'signature_name', $name ) &&
        update_post_meta( $invoice['ID'], 'signature_data', $signature_data ) &&
        update_post_meta( $invoice['ID'], 'signature_ip', $_SERVER['REMOTE_ADDR'] ) &&
        update_post_meta( $invoice['ID'], 'signature_time', current_time( 'timestamp' ) ) ) {

        do_action( 'wpies_signature_updated', $_POST['signature'], $invoice );
      }
    }

  }
}