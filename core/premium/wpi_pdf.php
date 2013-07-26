<?php
/**
 * Name: PDF
 * Class: wpi_pdf
 * Internal Slug: wpi_pdf
 * Version: 1.4.5
 * Feature ID: 15
 * Minimum Core Version: 3.08.7
 * Description: Allows option to generate PDF for invoices, receipts and quotes.
 */

class wpi_pdf {

  /**
   * Default feature settings
   */
  private static $default_options = array(

    'display_logo' => 'false',
    'logo_path' => '',
    'display_name' => 'true',
    'display_description' => 'true',
    'template' => 'pdf_quote.php',

    'display_terms_n_conditions' => 'false',
    'term_n_conditions' => '',

    'display_notes' => 'false',
    'notes' => ''

  );

  /**
   * Notification template tags that feature supports.
   * @var array
   */
  private static $notification_template_tags = array(
    'pdf' => array(
      'tag'   => 'pdf',
      'label' => 'URL to PDF version of invoice.'
    )
  );

  /**
   * Init feature filters and actions
   * @global object $wpi_settings
   * @global object $wpi_quotes
   */
  function wpi_premium_loaded() {
    global $wpi_settings, $wpi_quotes;

    add_shortcode('invoice_pdf', array(__CLASS__, 'wpi_pdf_link'));

    if(isset($wpi_settings['pdf']['do_not_insert_link_automatically']) && $wpi_settings['pdf']['do_not_insert_link_automatically'] != 'true') {
      add_action('wpi_front_end_right_col_bottom', create_function('', ' wpi_pdf::wpi_pdf_link(array("return" => false)); '));
    }

    add_filter('wpi_settings_tabs', array(__CLASS__, 'wpi_settings_tabs_pdf'));

    if (isset($_REQUEST['format']) && $_REQUEST['format'] == 'pdf') {
      wpi_pdf::wpi_get_pdf($_REQUEST['invoice_id']);
    }

    if (empty($wpi_settings['pdf'])) {
      $wpi_settings['pdf'] = wpi_pdf::$default_options;
    }

    add_action("wpi_publish_options", array(__CLASS__, 'wpi_publish_options'));

    add_filter('post_row_actions', array(__CLASS__, 'wpi_list_table_pdf_link'), 10, 2);

    add_action('postbox_overview', array(__CLASS__, 'wpi_link_paid_invoices'));

    //** Filter that processes new template tag %pdf% into WPI email templates. [korotkov@ud] */
    add_filter('wpi_email_template_vars', array(__CLASS__, 'wpi_add_template_vars'));
    add_filter('wpi_notification_content', array(__CLASS__, 'wpi_process_template_tags'), 10, 2);
  }

  /**
   * Filter that adds available template tags into contextual help of invoice plugin.
   *
   * @param array $current
   * @return array
   * @author korotkov@ud
   */
  function wpi_add_template_vars( $current ) {
    /** If there are no available tags - return what we already have. */
    if ( empty( wpi_pdf::$notification_template_tags ) ) return $current;

    /** Walk through tags and add all into list off tags. */
    foreach ( wpi_pdf::$notification_template_tags as $key => $value ) {
      /** Add tags only if all tag data is presented. */
      if ( !empty( $value ) && !empty( $value['tag'] ) && !empty( $value['label'] ) ) {
        $current[ $value['tag'] ] = __( $value['label'], WPI );
      }
    }

    /** Return filtered array */
    return $current;
  }

  /**
   * Filter for processing PDF link template tag for Invoice Notifications.
   *
   * @param string $current
   * @param array $invoice
   * @return string
   *
   * @author korotkov@ud
   */
  function wpi_process_template_tags( $current, $invoice ) {
    $current = str_replace('%'.wpi_pdf::$notification_template_tags['pdf']['tag'].'%', self::pdf_permalink( $invoice['invoice_id'] ), $current);
    return $current;
  }

  /**
   * View PDF Link
   *
   * @param type $this_invoice
   */
  static public function wpi_publish_options($this_invoice) {

    $pdf_link = wpi_pdf::pdf_permalink($this_invoice['invoice_id']);

    ?>
    <li class="wpi_hide_until_saved"><a target="_blank" url_annex="&format=pdf" class="wpi_new_win wpi_update_with_invoice_url" href="<?php echo $pdf_link; ?>"><?php _e('View PDF', WPI); ?></a></li>
    <?php
  }

  /**
   * echo PDF link on front end
   * @global array $invoice
   */
  function wpi_pdf_link($args = false) {
    global $invoice;

    $args = wp_parse_args($args, array(
      'invoice_id' => $invoice['invoice_id'],
      'label' => __('Download PDF', WPI),
      'target' => '_blank',
      'class' => apply_filters( 'wpi_class:button' , 'btn' ),
      'return' => true
    ));

    //** Convert from random ID if it is set */
    $args['invoice_id'] = wpi_invoice_id_to_post_id($args['invoice_id']);

    $pdf_link = wpi_pdf::pdf_permalink($args['invoice_id']);

    if(empty($pdf_link)) {
      return;
    }

    $html = '<a href="' . $pdf_link . '" target="'. $args['label'] .'"  class="' . $args['class'] . '">'. $args['label'] .'</a>';

    if($args['return']) {
      return $html;
    } else {
      echo $html;
    }
  }

  /**
   * echo PDF link for paid invoices
   * @param array $this_invoice
   */
  function wpi_link_paid_invoices($this_invoice){
    if( $this_invoice['type'] == 'single_payment' && $this_invoice['post_status'] == 'paid' ){
      $pdf_link = '<a class="wpi_new_win" target="_blank" href="' . wpi_pdf::pdf_permalink($this_invoice['hash']) . '" title="' . esc_attr( sprintf( __( 'View PDF', WPI ) ) ) . '" rel="permalink">' . __( 'View PDF', WPI ) . '</a>';
      ?>
      <tr>
        <th>
      <?php echo $pdf_link; ?>
        </th>
        <td></td>
      </tr>
      <?php
    }
  }

  /**
   * PDF link list table
   * @param array $actions
   * @param object $post
   * @return array
   */
  function wpi_list_table_pdf_link($actions = false, $post = false){
    $actions['pdf'] = '<a target="_blank" href="' . wpi_pdf::pdf_permalink($post->hash) . '" title="' . esc_attr( sprintf( __( 'Download PDF', WPI ) ) ) . '" rel="permalink">' . __( 'PDF', WPI ) . '</a>';
    return $actions;
  }

  /**
   * PDF link
   * @param $invoice_id
   * @return PDF link
   */
  function pdf_permalink($invoice_id = false ) {
    if(!$invoice_id) {
      return false;
    }

    $invoice_link = get_invoice_permalink($invoice_id);

    if(!empty($invoice_link)) {
      /** @todo: Are we sure that this is not first $_GET variable? [korotkov@ud] */
      return $invoice_link . '&format=pdf';
    }
    return false;
  }

  /**
   * Settings tab for PDF
   * @global object $wpi_settings
   */
  function wpi_pdf_settings() {
    global $wpi_settings;
    $templates = array();
    $dirfiles = scandir(WPI_Path . '/core/template');
    foreach ($dirfiles as $dirfile) {
      if (strstr($dirfile, 'pdf_quote')) {
        $file_data = get_file_data(WPI_Path . '/core/template/' . $dirfile, array('name' => 'Template Name'));
        $templates[$dirfile] = $file_data['name'];
      }
    }
    ?>
    <table class="form-table">
      <tr class="wpi_something_advanced_wrapper">
        <th><?php _e("Settings", WPI); ?></th>
        <td>
          <ul>
            <li><?php echo WPI_UI::checkbox('name=wpi_settings[pdf][do_not_insert_link_automatically]&value=true&label=' . __('Do not automatically insert PDF link on bottom of invoices.', WPI), WPI_Functions::is_true($wpi_settings['pdf']['do_not_insert_link_automatically'])) ?></li>
          </ul>
        </td>
      </tr>

      <tr class="wpi_something_advanced_wrapper">
      <th><?php _e("PDF Logo", WPI); ?></th>
        <td>
          <ul>
            <li><?php echo WPI_UI::checkbox('class=pdf_display_logo wpi_show_advanced&name=wpi_settings[pdf][display_logo]&value=true&label=' . __('Display logo in the PDF.', WPI), WPI_Functions::is_true($wpi_settings['pdf']['display_logo'])) ?></li>
            <li class="wpi_advanced_option">
              <?php echo WPI_UI::input("id=pdf_logo_path&name=wpi_settings[pdf][logo_path]&value={$wpi_settings['pdf']['logo_path']}") ?>
              <div class="description"><?php _e('Enter a valid URL of an image you would like to use in the PDF as a logo.', WPI); ?></div>
            </li>
          </ul>
        </td>
      </tr>
      <tr>
        <th><?php _e("Header Settings", WPI); ?></th>
        <td>
          <ul>
            <li><?php echo WPI_UI::checkbox("class=pdf_display_name&name=wpi_settings[pdf][display_name]&value=true&label=" . __('Display recipient name and address.', WPI), WPI_Functions::is_true($wpi_settings['pdf']['display_name'])) ?></li>
            <li><?php echo WPI_UI::checkbox("class=pdf_display_desctiption&name=wpi_settings[pdf][display_description]&value=true&label=" . __('Display invoice description.', WPI), WPI_Functions::is_true($wpi_settings['pdf']['display_description'])) ?></li>
          </ul>
        </td>
      </tr>
      <tr>
        <th><?php _e("Select template", WPI); ?></th>
        <td><?php echo WPI_UI::select("name=wpi_settings[pdf][template]&values=" . serialize($templates) . "&current_value={$wpi_settings['pdf']['template']}"); ?></td>
      </tr>
      <tr valign="top" class="wpi_something_advanced_wrapper">
        <th scope="row"><?php _e('Default Font','wpi'); ?></th>
        <td>
          <?php
          /**
           * @author odokienko@UD
           */

          require_once(WPI_Path.'/third-party/tcpdf/wpi_tcpdf.php');
          $pdf = new WPI_TCPDF();
          $pdf->getHTMLFontList("name=wpi_settings[pdf][setfont]&selected={$wpi_settings['pdf']['setfont']}");
          ?><br>
          <span class="description"><?php _e('The default font is Helvetica (core). If you will have any problems with current font try choosing another one.','wpi'); ?></span>
        </td>
      </tr>
      <tr class="wpi_something_advanced_wrapper">
        <th><?php _e("Terms &amp; Conditions:", WPI) ?></th>
        <td>
          <ul>
            <li><?php echo WPI_UI::checkbox("class=pdf_display_terms_n_conditions wpi_show_advanced&name=wpi_settings[pdf][display_terms_n_conditions]&value=true&label=".__('Display Terms and Conditions', WPI), WPI_Functions::is_true($wpi_settings['pdf']['display_terms_n_conditions'])); ?><br /></li>
            <li class="wpi_advanced_option">
              <?php echo WPI_UI::textarea("name=wpi_settings[pdf][terms_n_conditions]&value=" . (!empty($wpi_settings['pdf']['terms_n_conditions']) ? strip_tags( $wpi_settings['pdf']['terms_n_conditions'] ) : '')); ?><br />
              <div class="description"><?php _e( 'Text of Term and Conditions for the Invoice/Quote', WPI ); ?></div>
            </li>
          </ul>
        </td>
      </tr>
      <tr class="wpi_something_advanced_wrapper">
        <th><?php _e("Notes", WPI) ?></th>
        <td>
          <ul>
            <li><?php echo WPI_UI::checkbox("class=pdf_display_notes wpi_show_advanced&name=wpi_settings[pdf][display_notes]&value=true&label=".__('Display Notes', WPI), WPI_Functions::is_true($wpi_settings['pdf']['display_notes'])); ?><br /></li>
            <li class="wpi_advanced_option">
              <?php echo WPI_UI::textarea("name=wpi_settings[pdf][notes]&value=" . (!empty($wpi_settings['pdf']['notes']) ? strip_tags( $wpi_settings['pdf']['notes'] ) : '')) ?><br />
              <div class="description"><?php _e('Notes will be displayed towards the bottom of the Invoice/Quote, depending on the template.', WPI); ?></div>
            </li>
          </ul>
        </td>
      </tr>
    </table>
    <?php
  }

  /**
   * settings tab fo PDF
   * @param array $wpi_settings_tabs
   * @return array
   */
  function wpi_settings_tabs_pdf($wpi_settings_tabs) {
    $wpi_settings_tabs['pdf'] = array(
      'label' => __('PDF Invoice', WPI),
      'position' => 80,
      'callback' => array('wpi_pdf', 'wpi_pdf_settings')
    );

    return $wpi_settings_tabs;
  }

  /**
   * Returns proper BILL TO.
   * If user data from invoice has Company Name - use it.
   * In case if it is empty - use Display Name from userdata of invoice.
   * In case if it is empty too - we need to load it.
   *
   * @param array $invoice
   * @return string
   * @author korotkov@ud
  **/
  function wpi_get_bill_to($invoice) {
    /** If company name exists, return it */
    if ( !empty( $invoice->data['user_data']['company_name'] ) ) return $invoice->data['user_data']['company_name'];
    return wpi_get_user_display_name( $invoice->data );
  }

  /**
   * Returns formatted address based on user's data
   *
   * @param array $invoice
   * @return string|bool
   * @author korotkov@ud
   */
  function wpi_get_address($invoice) {
    $street  = !empty( $invoice->data['user_data']['streetaddress'] )?( $invoice->data['user_data']['streetaddress']."<br />"):'';
    $city    = !empty( $invoice->data['user_data']['city'] )?( $invoice->data['user_data']['city']."<br />" ):'';
    $state   = !empty( $invoice->data['user_data']['state'] )?( $invoice->data['user_data']['state'].' ' ):'';
    $zip     = !empty( $invoice->data['user_data']['zip'] )?( $invoice->data['user_data']['zip'] ):'';

    $address = trim( $street.$city.$state.$zip );
    return !empty( $address ) ? $address : false;
  }

  /**
   * Returns user's phone number
   *
   * @param array $invoice
   * @return string|bool
   * @author korotkov@ud
   */
  function wpi_get_telephone($invoice) {
    return !empty( $invoice->data['user_data']['phonenumber'] )?trim($invoice->data['user_data']['phonenumber']):false;
  }

  /**
   * generate PDF
   * @global object $wpi_settings
   * @global object $wpdb
   * @param array $invoice
   */
  function wpi_get_pdf($invoice) {
    global $wpi_settings, $wpdb;

    ob_start();

    $invoice_id = $wpdb->get_var("SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key='invoice_id' AND MD5(meta_value) = '" . mysql_escape_string($invoice) . "'");

    $invoice = new WPI_Invoice();
    $invoice->load_invoice("id=" . $invoice_id);

    require_once(WPI_Path . '/third-party/tcpdf/wpi_tcpdf.php');
    $pdf = new WPI_TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    /** set default font subsetting mode */
    $pdf->setFontSubsetting(true);
    
    /** set document information */
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle($invoice->data['post_title']);

    /** set margins */
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    /** set auto page breaks */
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    /** set image scale factor */
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);






    /** Add a page */
    $pdf->AddPage();

    //** Use proper template file @author korotkov@ud*/
    if ( isset( $wpi_settings['pdf']['template'] ) ) {

      //** If child theme has template file */
      if ( file_exists( get_stylesheet_directory() . '/wpi/' . $wpi_settings['pdf']['template'] ) ) {
        require_once( get_stylesheet_directory() . '/wpi/' . $wpi_settings['pdf']['template'] );
      }
      //** If parent theme has template file */
      elseif( file_exists( get_template_directory() . '/wpi/' . $wpi_settings['pdf']['template'] ) ) {
        require_once( get_template_directory() . '/wpi/' . $wpi_settings['pdf']['template'] );
      }
      //** If all other cases */
      else {
        require_once(WPI_Path . '/core/template/'.$wpi_settings['pdf']['template']);
      }

    } else {
      require_once(WPI_Path . '/core/template/pdf_quote.php');
    }

    $currency_symbol = $invoice->data['default_currency_code'] ? $invoice->data['default_currency_code'] : "$";

    list( , , $type) = getimagesize($wpi_settings['pdf']['logo_path']);
    if ($type==IMAGETYPE_PNG) {
      $img = imagecreatefrompng($wpi_settings['pdf']['logo_path']);
      imagealphablending($img, false);
      imagesavealpha($img,true);
      /* gives the index of current transparent color or -1 */
      $transparent_index = ImageColorTransparent($img);
      if($transparent_index!=(-1)) {
        $trsp = ImageColorsForIndex($img,$transparent_index);
        // get transparent color set
        $delete = imagecolorallocate($img, $trsp['red'], $trsp['green'], $trsp['blue']);
        // replace
        imagecolorset($img, $delete, 255, 255, 255);
      }

      $upload_dir = wp_upload_dir();
      $files_dir = $upload_dir['basedir'];
      $files_url = $upload_dir['baseurl'];
      /** Create DIR if it doesn't exist */
      if(!is_dir($files_dir)) {
        mkdir($files_dir, 0755);
        fopen($files_dir . '/index.php', "w");
      }
      $file_name = wp_unique_filename($files_dir, basename($wpi_settings['pdf']['logo_path']).'.jpg');
      $path = $files_dir . '/'. $file_name;
      $url = $files_url . '/'. $file_name;

      /** Try to put file */

      imagejpeg($img, $path, 100);
      $wpi_settings['pdf']['logo_path'] = $url;
      $wpi_core = WPI_Core::getInstance();
      $wpi_core->Settings->SaveSettings( $wpi_settings );
    }

    if ($wpi_settings['pdf']['display_logo'] == 'true' && !empty($wpi_settings['pdf']['logo_path']) && filter_var($wpi_settings['pdf']['logo_path'], FILTER_VALIDATE_URL)) {
      $headers = @get_headers($wpi_settings['pdf']['logo_path'], 1);
    }
    /** Fix content type */
    $content_type = !empty($headers['Content-Type'])?$headers['Content-Type']:'';
    if ( !empty($headers['Content-Type']) && is_array($headers['Content-Type']) ) {
      foreach ($headers['Content-Type'] as $key => $value) {
        if ( strstr($value, 'image') ) {
          $content_type = 'image';
        }
      }
    }
    $content_type = !is_array($content_type) ? $content_type : '';
    if ($wpi_settings['pdf']['display_logo'] == 'true' && $headers && strstr($content_type, 'image') && filter_var($wpi_settings['pdf']['logo_path'], FILTER_VALIDATE_URL)) {
      $logo = str_replace('%logo%', $wpi_settings['pdf']['logo_path'], $logo);
      $html = str_replace('%header_width%', $header_width, $html);
    } else {
      $logo = '';
      $html = str_replace('%header_width%', '', $html);
    }
    if ($wpi_settings['pdf']['display_name'] == 'true') {
      /** If phone exists */
      if ( $temp_telephone = self::wpi_get_telephone($invoice) ) {
        $telephone        = str_replace('%telephone%', $temp_telephone, $telephone);
        $name_and_address = str_replace('%telephone%', $telephone, $name_and_address);
      } else {
        $name_and_address = str_replace('%telephone%', '', $name_and_address);
      }
      /** If address is not empty */
      if ( $temp_address = self::wpi_get_address($invoice) ) {
        $address          = str_replace('%address%', $temp_address, $address);
        $name_and_address = str_replace('%address%', $address, $name_and_address);
      } else {
        $name_and_address = str_replace('%address%', '', $name_and_address);
      }

      $bill_to          = str_replace('%bill_to%', self::wpi_get_bill_to($invoice), $bill_to);
      $name_and_address = str_replace('%bill_to%', $bill_to, $name_and_address);

    } else {
      $name_and_address = '';
    }
    $due_date = ($invoice->data['due_date_month'] && $invoice->data['due_date_day'] && $invoice->data['due_date_year']) ? str_replace('%due_date%', $invoice->data['due_date_month'] . ' ' . $invoice->data['due_date_day'] . ', ' . $invoice->data['due_date_year'], $due_date) : '';
    $html = str_replace('%due_date%', $due_date, $html);
    $amount_due = ($invoice->data['post_status'] == 'paid') ? '' : str_replace('%amount_due%',  wp_invoice_currency_format($invoice->data['net']).' '.$currency_symbol, $amount_due);
    $html = str_replace('%amount_due%', $amount_due, $html);
    $attn = str_replace('%attn%', wpi_get_user_display_name($invoice->data), $attn);
    $html = str_replace('%attn%', $attn, $html);
    $html = str_replace('%custom_field%', $custom_field, $html);
    if (isset($invoice->data['itemized_list']) && is_array($invoice->data['itemized_list'])) {
      $description_rows = '';
      $bg_i = 1;
      $description_cols = 3;
      $desc_width = 400;
      foreach ($invoice->data['itemized_list'] as $row) {
        $description = $description_row;
        $description = str_replace('%bgcolor%', $description_row_bgcolor[$bg_i], $description);
        $description = str_replace('%name%', stripslashes($row['name']), $description);
        $description = str_replace('%quantity%', $row['quantity'], $description);
        $description = str_replace('%price%', wp_invoice_currency_format($row['line_total_before_tax']).' '.$currency_symbol, $description);
        if ($row['description']) {
          $description = str_replace('%description%', nl2br($row['description']).'<br />', $description);
        } else {
          $description = str_replace('%description%', '', $description);
        }
        if ($invoice->data['total_tax']) {
          if ($row['line_total_tax']) {
            $line_total_tax = str_replace('%line_total_tax%', wp_invoice_currency_format($row['line_total_tax']).' '.$currency_symbol, $tax_td);
          } else {
            $line_total_tax = str_replace('%line_total_tax%', '', $tax_td);
          }
          $line_total_tax = str_replace('%bgcolor%', $description_row_bgcolor[$bg_i], $line_total_tax);
          $description = str_replace('%tax_td%', $line_total_tax, $description);
          $description_cols = 4;
        } else {
          $description = str_replace('%tax_td%', '', $description);
        }
        $description = str_replace('%description_cols%', $description_cols, $description);
        $description_rows .= $description;
        $bg_i = ($bg_i == 1) ? 2 : 1;
      }
      $description_table = str_replace('%description_row%', $description_rows, $description_table);
      $description_cols  = 3;

      $grand_total = str_replace('%grand_total%', wp_invoice_currency_format($invoice->data['net']).' '.$currency_symbol, $grand_total);
      if ($invoice->data['post_status'] == 'paid') {
          $grand_total = '';
      }
      $description_table = str_replace('%grand_total%', $grand_total, $description_table);

      /** If Total Tax is greater then 0 */
      if ( !empty( $invoice->data['total_tax'] ) && $invoice->data['total_tax'] > 0 ) {
        $total_tax = str_replace('%total_tax%', wp_invoice_currency_format($invoice->data['total_tax']).' '.$currency_symbol, $total_tax);
        $description_cols = 4;
        $total_tax = str_replace('%description_cols%', $description_cols, $total_tax);
        $desc_width = 300;
      } else {
        $total_tax = '';
        $tax_th = '';
      }

      /** If Discount is greater then 0 */
      if ( !empty( $invoice->data['total_discount'] ) && $invoice->data['total_discount'] > 0 ) {
        $total_discount = str_replace('%total_discount%', wp_invoice_currency_format($invoice->data['total_discount']).' '.$currency_symbol, $total_discount);
        $description_cols = 4;
        $total_discount = str_replace('%description_cols%', $description_cols, $total_discount);
      } else {
        $total_discount = '';
      }

      $description_table = str_replace('%description_cols%', $description_cols, $description_table);

      /** Place Subtotal */
      $subtotal          = str_replace('%subtotal%', wp_invoice_currency_format($invoice->data['subtotal']).' '.$currency_symbol, $subtotal);
      $description_table = str_replace('%subtotal%', $subtotal, $description_table);

      /** Place Tax value */
      $description_table = str_replace('%total_tax%', $total_tax, $description_table);

      /** Place Discount */
      $description_table = str_replace('%total_discount%', $total_discount, $description_table);

      $description_table = str_replace('%tax_th%', $tax_th, $description_table);
      $description_table = str_replace('%desc_width%', $desc_width, $description_table);
      $html = str_replace('%description%', $description_table, $html);
    } else {
      $html = str_replace('%description%', '', $html);
    }
    if ($wpi_settings['pdf']['display_description'] == 'true') {
      $content = str_replace('%content_text%', $invoice->data['post_content'], $content);
      $html = str_replace('%content%', $content, $html);
    } else {
      $html = str_replace('%content%', '', $html);
    }

    if ($invoice->data['post_status'] == 'paid') {
      $html = str_replace('%is_quote%', __('RECEIPT', WPI), $html);

    } elseif ($invoice->data['is_quote'] == 'true') {
      $html = str_replace('%is_quote%', __('QUOTE', WPI), $html);

    } else {
      $html = str_replace('%is_quote%', __('INVOICE', WPI), $html);

    }

    /** Terms & Conditions */
    if ( !empty( $wpi_settings['pdf']['display_terms_n_conditions'] ) && $wpi_settings['pdf']['display_terms_n_conditions'] == 'true' ) {
      $terms_text = trim( strip_tags( $wpi_settings['pdf']['terms_n_conditions'] ) );
      if ( !empty( $terms_text ) ) {
        $terms_text = str_replace('%terms_n_conditions_text%', $terms_text, $terms_n_conditions);
        $html = str_replace('%terms_n_conditions%', $terms_text, $html);
      } else {
        $html = str_replace('%terms_n_conditions%', '', $html);
      }
    } else {
      $html = str_replace('%terms_n_conditions%', '', $html);
    }

    /** Notes */
    if ( !empty( $wpi_settings['pdf']['display_notes'] ) && $wpi_settings['pdf']['display_notes'] == 'true' ) {
      $notes_text = trim( strip_tags( $wpi_settings['pdf']['notes'] ) );
      if ( !empty( $notes_text ) ) {
        $notes_text = str_replace('%notes_text%', $notes_text, $notes);
        $html = str_replace('%notes%', $notes_text, $html);
      } else {
        $html = str_replace('%notes%', '', $html);
      }
    } else {
      $html = str_replace('%notes%', '', $html);
    }

    $html = str_replace('%id%', !empty($invoice->data['custom_id'])?$invoice->data['custom_id']:$invoice->data['invoice_id'], $html);
    $html = str_replace('%post_date%', date(get_option('date_format'), strtotime($invoice->data['post_date'])), $html);
    $html = str_replace('%logo%', $logo, $html);
    $html = str_replace('%name_and_address%', $name_and_address, $html);
    $html = str_replace('%business_address%', $wpi_settings['business_address'], $html);
    $html = str_replace('%business_phone%', $wpi_settings['business_phone'], $html);
    $html = str_replace('%email_address%', $wpi_settings['email_address'], $html);
    $html = str_replace('%business_name%', $wpi_settings['business_name'], $html);
    $html = str_replace('%url%', get_bloginfo('home'), $html);

    /** Print text using writeHTMLCell() */
    if (isset($font)){ 
      //$pdf->SetFont($font, 'bi', 12, '', 'false');
      $pdf->SetFont($font);
    }elseif ($wpi_settings['pdf']['setfont']){
      $pdf->SetFont($wpi_settings['pdf']['setfont']);
    }


    $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);

    /* Remove any output to prevent TCPDF failure*/
    ob_end_clean();
    error_reporting(0);
    /** Close and output PDF document */
    $pdf->Output($invoice->data['post_title'] . '.pdf', 'I');
    exit;
  }

}

/** Init PDF premium feature */
add_action('wpi_premium_loaded', array('wpi_pdf', 'wpi_premium_loaded'));