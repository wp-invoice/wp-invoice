<?php
/**
  Name: Invoice Quotes
  Class: wpi_quotes
  Global Variable: wpi_quotes
  Internal Slug: wpi_quotes
  JS Slug: wpi_quotes
  Version: 2.0.0
  Feature ID: 16
  Minimum Core Version: 4.0.0
  Description: Lets user create Quotes.
 */

class wpi_quotes {

  /**
   * Init feature filters and actions
   */
  static function init() {

    //** Comments */
    add_action('comment_post', array(__CLASS__, 'wpi_save_comment_meta_data'));
    add_action('comment_post_redirect', array(__CLASS__, 'wpi_redirect_to_invoice'));
    add_action('wpi_add_comments_box', array(__CLASS__, 'wpi_add_comments_box'));
    add_filter('wpi_closed_comments', array(__CLASS__, 'wpi_closed_comments_handler'));
    add_filter('comments_array', array(__CLASS__, 'wpi_filter_comments'));
    add_filter('get_comments_number', array(__CLASS__, 'wpi_count_comments'));
    add_filter('comment_form_defaults', array(__CLASS__, 'comment_form_defaults'));
    add_filter('comment_form_logged_in', array(__CLASS__, 'wpi_change_comment_form'));
    add_filter('comment_form_default_fields', array(__CLASS__, 'wpi_change_comment_form_default'));
    add_filter('comment_row_actions', array(__CLASS__, 'wpi_change_comment_actions'), 10, 2);

    //** Editor */
    add_action('wpi_ui_admin_scripts_invoice_editor', array(__CLASS__, 'load_invoice_editor_scripts'));

    //** Publish options */
    add_action("wpi_publish_options", array(__CLASS__, 'wpi_publish_options'));

    //** Setting options */
    add_action('wpi_settings_page_basic_settings',    array(__CLASS__, 'settings_options'));

    //** Filter existing WPI Object types */
    add_filter('wpi_object_types', array(__CLASS__, 'wpi_object_types'));
    add_filter('wpi_invoice_history_allow_types', array(__CLASS__, 'wpi_invoice_history_allow_types'));

    //** Accept button */
    add_action('wpi_front_end_right_col_bottom', array(__CLASS__, 'accept_quote_button'));
    add_action('wpi_unified_template_top_navigation', array(__CLASS__, 'accept_quote_button'));
    add_action('wp_ajax_wpi_accept_quote', array(__CLASS__, 'accept_quote'));
    add_action('wp_ajax_nopriv_wpi_accept_quote', array(__CLASS__, 'accept_quote'));
  }

  /**
   *
   * @param object $wpi_settings
   */
  static function settings_options( $wpi_settings ) {
    ?>
      <tr>
        <th><?php _e('Quotes', ud_get_wp_invoice_quotes()->domain);?></th>
        <td>
          <ul>
            <li>
              <?php
                echo WPI_UI::checkbox(array(
                    'name'  => 'wpi_settings[quotes][make_authorize]',
                    'value' => 'true',
                    'label' => __('Make recipients to be authorized to be able to Accept Quote.', ud_get_wp_invoice_quotes()->domain)
                ), isset($wpi_settings['quotes']['make_authorize'])?$wpi_settings['quotes']['make_authorize']:false );
              ?>
              <div class="description"><?php _e('By default quote recipients do not have to sign in before Accepting the quote you sent to them. Tick this option if you need them to be logged in.', ud_get_wp_invoice_quotes()->domain);?></div>
            </li>
          </ul>
        </td>
      </tr>
      <tr>
        <th></th>
        <td>
          <ul class="wpi_something_advanced_wrapper">
            <li>
              <?php
                echo WPI_UI::checkbox(array(
                    'name'  => 'wpi_settings[quotes][tos_checkbox]',
                    'value' => 'true',
                    'label' => __('Add "Terms &amp; Conditions" checkbox to Quote page.', ud_get_wp_invoice_quotes()->domain),
                    'class' => 'wpi_show_advanced'
                ), isset($wpi_settings['quotes']['tos_checkbox'])?$wpi_settings['quotes']['tos_checkbox']:false );
              ?>
              <div class="description"><?php _e('This option allows you to add "Terms &amp; Conditions" checkbox to every Quote page. Be sure you have specified Terms page ID below.', ud_get_wp_invoice_quotes()->domain);?></div>
            </li>
            <li class="wpi_advanced_option">
              <label for=""><?php _e( '"T&amp;C" Page ID:', ud_get_wp_invoice_quotes()->domain ); ?></label>
              <?php echo WPI_UI::input(array(
                  'type' => 'text',
                  'style' => 'width:50px;',
                  'name' => 'tos_page_id',
                  'group' => 'wpi_settings[quotes]',
                  'value' => !empty($wpi_settings['quotes']['tos_page_id'])?$wpi_settings['quotes']['tos_page_id']:''
              )); ?>
              <div class="description wpi_advanced_option"><?php _e('Numeric value of WordPress page ID that has "Terms &amp; Conditions".', ud_get_wp_invoice_quotes()->domain) ?></div>
            </li>
          </ul>
        </td>
      </tr>
    <?php
  }

  /**
   * Draw accept button
   * @global type $invoice
   */
  static function accept_quote_button() {
    global $invoice, $wpi_settings;

    if ( !is_quote() ) return;

    if ( !empty( $wpi_settings['quotes']['tos_checkbox'] )
         && $wpi_settings['quotes']['tos_checkbox'] == 'true'
         && !empty( $wpi_settings['quotes']['tos_page_id'] ) && is_numeric( $wpi_settings['quotes']['tos_page_id'] ) ) {
      $link = get_page_link($wpi_settings['quotes']['tos_page_id']);
      if ( $link ) {
        ?>
        <div>
          <input type="checkbox" id="quote_tos" />
          <label for="quote_tos"><?php echo sprintf(__( 'I accept <a target="_blank" href="%s">Terms &amp; Conditions</a>', ud_get_wp_invoice_quotes()->domain ), $link); ?></label>
        </div>
        <?php
      }
    }

    ?>
      <form id="wpi_quote_accept" action="" method="">
        <?php wp_nonce_field('wpi_accept_quote', 'my-nonce'); ?>
        <input type="hidden" name="action" value="wpi_accept_quote" />
        <input type="hidden" name="invoice" value="<?php echo $invoice['ID']; ?>" />
        <input type="submit" value="<?php _e( 'Accept Quote', ud_get_wp_invoice_quotes()->domain ); ?>" />
        <img style="display: none;" class="loader-img" src="<?php echo ud_get_wp_invoice_quotes()->path( 'static/styles/images/processing-ajax.gif', 'url' ); ?>" alt="" />
        <div class="wpi_quote_accept_result" style="display: none;"></div>
      </form>
      <script type="text/javascript">
        jQuery(document).ready(function(){
          jQuery('#wpi_quote_accept').on('submit', function(e){
            if ( !jQuery('#quote_tos').length ) {
              jQuery('[type="submit"]', e.target).hide();
              jQuery('.loader-img', e.target).show();
              jQuery.ajax({
                dataType: "json",
                data: jQuery(e.target).serialize(),
                type: "POST",
                url: wpi_ajax.url,
                success: function ( data ) {
                  if ( data.success == false ) {
                    jQuery('.loader-img', e.target).hide();
                    jQuery('.wpi_quote_accept_result', e.target).html(data.message).addClass('wpi_error').show();
                  }
                  if ( data.success == true ) {
                    jQuery('.loader-img', e.target).hide();
                    jQuery('.wpi_quote_accept_result', e.target).html(data.message).addClass('wpi_error').show();
                    window.location.reload();
                  }
                }
              });
              return false;
            } else {
              if ( jQuery('#quote_tos').is(":checked") ) {
                jQuery('[type="submit"]', e.target).hide();
                jQuery('.loader-img', e.target).show();
                jQuery.ajax({
                  dataType: "json",
                  data: jQuery(e.target).serialize(),
                  type: "POST",
                  url: wpi_ajax.url,
                  success: function ( data ) {
                    if ( data.success == false ) {
                      jQuery('.loader-img', e.target).hide();
                      jQuery('.wpi_quote_accept_result', e.target).html(data.message).addClass('wpi_error').show();
                    }
                    if ( data.success == true ) {
                      jQuery('.loader-img', e.target).hide();
                      jQuery('.wpi_quote_accept_result', e.target).html(data.message).addClass('wpi_error').show();
                      window.location.reload();
                    }
                  }
                });
                return false;
              } else {
                jQuery('.wpi_quote_accept_result', e.target).html('<?php _e('You have to accept Terms &amp; Conditions.', ud_get_wp_invoice_quotes()->domain); ?>').addClass('wpi_error').show();
                return false;
              }
            }
          });
        });
      </script>
    <?php

  }

  /**
   * Accept quote
   */
  static function accept_quote() {
    global $wpi_settings;

    if ( !is_user_logged_in() && !empty( $wpi_settings['quotes']['make_authorize'] ) && $wpi_settings['quotes']['make_authorize'] == "true" )
      die(json_encode(array('success'=>false, 'message'=>__('Authorization required.', ud_get_wp_invoice_quotes()->domain))));

    if ( wp_verify_nonce($_POST['my-nonce'], 'wpi_accept_quote') ) {
      global $current_user;

      $invoice_object = new WPI_Invoice();
      $invoice_object->load_invoice(array('id'=>$_POST['invoice']));

      if ( $current_user->data->user_email != $invoice_object->data['user_email'] && ( !empty( $wpi_settings['quotes']['make_authorize'] ) && $wpi_settings['quotes']['make_authorize'] == "true" ) ) {
        die( json_encode(array('success'=>false, 'message'=>__('You are not allowed to make that action. You are not a recipient of this quote. Plese login under proper account.', ud_get_wp_invoice_quotes()->domain))) );
      }

      $invoice_object->set(array(
          'is_quote'=>false,
          'status'=>null,
          'type'=>'invoice'
      ));
      $invoice_object->save_invoice();

      die(json_encode(array('success'=>true, 'message'=>__('Quote has been accepted and converted to invoice. Refreshing...', ud_get_wp_invoice_quotes()->domain))));
    }
    die(json_encode(array('success'=>false, 'message'=>__('Error! Unknown request.', ud_get_wp_invoice_quotes()->domain))));
  }

  /**
   * Add allowed invoice type to history widget.
   *
   * @param array $current
   * @return string
   */
  static function wpi_invoice_history_allow_types( $current = array() ) {
    $current[] = 'quote';
    return $current;
  }

  /**
   * Change actions buttons for comments
   * @global object $wpi_settings
   * @param array $actions
   * @param object $comments
   * @return array
   */
  static function wpi_change_comment_actions($actions, $comment){
      global $wpi_settings;
      if(isset($_REQUEST['invoice_id']) || (isset($_REQUEST['page']) && $_REQUEST['page']=='wpi_page_manage_invoice')){
        unset($actions);
        $actions['reply'] = '<a class="vim-r" href="#" title="'. __('Reply to this comment', ud_get_wp_invoice_quotes()->domain) .'" onclick="commentReply.open( \''. $comment->comment_ID .'\',\''. $wpi_settings['web_invoice_page'] .'\' );return false;">'. __('Reply', ud_get_wp_invoice_quotes()->domain) .'</a>';
        $actions['delete'] = "<a href='javascript:wpiDeleteComment(" . $comment->comment_ID . ", \"" . wp_create_nonce( "delete-comment_$comment->comment_ID" ) . "\")' class='delete:the-comment-list'>" . __('Delete', ud_get_wp_invoice_quotes()->domain) . '</a>';
      }
      return $actions;
  }

  /**
   * Modify default comment args for WPI Objects
   *
   * $args:
   * - fields - author,email,url
   * - comment_field
   * - must_log_in
   * - logged_in_as
   * - comment_notes_before
   * - comment_notes_after
   * - id_form
   * - id_submit
   * - title_reply
   * - title_reply_to
   * - cancel_reply_link
   * - label_submit
   *
   * @author potanin@UD
   * @since 3.0.3
   */
  static function comment_form_defaults($args) {
    global $invoice;

    if(empty($invoice['is_quote']) || !$invoice['is_quote']) {
      return $args;
    }

    $user = $invoice['user_data'];

    $args['fields']['author'] = '<input type="hidden" name="author" value="' . recipients_name(array('return'=>true)) . '" />';
    $args['fields']['email'] = '<input type="hidden" name="email" value="' . $user['user_email'] . '" />';
    $args['fields']['url'] = '';

    $args['logged_in_as'] = '';

    $args['comment_field'] = '<p class="comment-form-comment"><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></p>';
    $args['comment_field'] .= '<input name="invoice_id" type="hidden" value="' . $invoice['hash'] . '">';

    if(is_user_logged_in()) {
      $args['title_reply'] = __('Response:', ud_get_wp_invoice_quotes()->domain);
      $args['label_submit'] = __('Send Response', ud_get_wp_invoice_quotes()->domain);
    } else {
      $args['title_reply'] = __('Have a question?', ud_get_wp_invoice_quotes()->domain);
      $args['label_submit'] = __('Send Question', ud_get_wp_invoice_quotes()->domain);
    }

    $args['comment_notes_before'] = '';
    $args['comment_notes_after'] = '';

    return $args;
  }

  /**
   * PHP function to echoing a message to JS console
   *
   * @author potanin@UD
   * @since 3.0.3
   */
  static function load_invoice_editor_scripts() {
    wp_enqueue_script('admin-comments');
  }

  /**
   * Filter invoice comments
   * @global object $wpi_settings
   * @param type $comments
   * @return array|false
   */
  static function wpi_filter_comments($comments) {
    global $wpi_settings;
    if (isset($_REQUEST['invoice_id']) && !empty($_REQUEST['invoice_id'])) {
      $invoice_id = $_REQUEST['invoice_id'];
    } else {
      return $comments;
    }

    add_filter('comments_clauses', array(__CLASS__, 'wpi_comments_clauses'));
    $comments = get_comments('post_id=' . (!empty($wpi_settings['web_invoce_page'])?$wpi_settings['web_invoce_page']:'') );
    $invoice_comments = array();
    foreach ($comments as $key => $comment) {
      if ($invoice_id == get_comment_meta($comment->comment_ID, 'invoice_id', true)) {
        $invoice_comments[] = $comment;
      }
    }
    return $invoice_comments;
  }

  /**
   * Count filtered invoice comments
   * @global object $wpi_settings
   * @param type $comments
   * @return int|false
   */
  static function wpi_count_comments($comments) {
    global $wpi_settings;
    if (isset($_REQUEST['invoice_id']) && !empty($_REQUEST['invoice_id'])) {
      $invoice_id = $_REQUEST['invoice_id'];
    } else {
      return $comments;
    }
    
    add_filter('comments_clauses', array(__CLASS__, 'wpi_comments_clauses'));
    $comments = get_comments('post_id=' . $wpi_settings['web_invoice_page']);
    $invoice_comments = array();
    foreach ($comments as $key => $comment) {
      if ($invoice_id == get_comment_meta($comment->comment_ID, 'invoice_id', true)) {
        $invoice_comments[] = $comment;
      }
    }

    return count($invoice_comments);
  }

  /**
   * approved comment
   * @param array $arr
   * @return string
   */
  static function wpi_comments_clauses($arr) {
    $arr['where'] = 'comment_approved=2';
    return $arr;
  }

  /**
   * Redirect to invoice after commenting
   * @param string $location
   * @return string
   */
  static function wpi_redirect_to_invoice($location) {
    if (isset($_REQUEST['invoice_id']) && !empty($_REQUEST['invoice_id'])) {
      $location = str_replace('?', '?invoice_id=' . $_REQUEST['invoice_id'] . '&', $location);
      if(!strstr($location, 'invoice_id=')) $location = str_replace('#', '?invoice_id=' . $_REQUEST['invoice_id'] . '#', $location);
    }
    return $location;
  }

  /**
   * Adding invoice id to comment form
   * @global type $invoice
   * @param type $comment
   * @return string
   */
  static function wpi_change_comment_form($comment) {
    global $invoice;
    $comment = '<input name="invoice_id" type="hidden" value="' . $invoice['hash'] . '">';
    return $comment;
  }

  /**
   * Adding invoice id to comment form
   * @global type $invoice
   * @param type $comment
   * @return string
   */
  static function wpi_change_comment_form_default($comment) {
    global $invoice;
    $comment['url'] .= '<input name="invoice_id" type="hidden" value="' . $invoice['hash'] . '">';
    return $comment;
  }

  /**
   * Save comment meta data
   * @param int $comment_id
   */
  static function wpi_save_comment_meta_data($comment_id) {
    global $wpdb;
    if (isset($_REQUEST['invoice_id']) && !empty($_REQUEST['invoice_id'])) {
      add_comment_meta($comment_id, 'invoice_id', $_REQUEST['invoice_id']);
      $wpdb->update($wpdb->comments, array('comment_approved' => 2), array('comment_ID' => $comment_id));
    }
  }

  /**
   * Add hadler to remove comments
   */
  static function wpi_closed_comments_handler() {
    add_filter('comments_open', create_function("", " return false; "));
  }

  /**
   * Add new wpi_object type
   * @param array $types
   * @return array
   */
  static function wpi_object_types($types) {
    $types['quote'] = array('label' => 'Quote');
    return $types;
  }

  /**
   * Quote checkbox
   *
   * @param type $this_invoice
   */
  static public function wpi_publish_options($this_invoice) {
    ?>
    <li class="wpi_quote_option wpi_not_for_recurring wpi_not_for_deposit"><?php echo WPI_UI::checkbox("name=wpi_invoice[quote]&value=true&label=Quote", ((!empty($this_invoice['status']) && $this_invoice['status'] == 'quote') ? true : false)); ?></li>
    <?php
  }

  /**
   * Draw wp_editor if it exists
   *
   * @return null
   * @author korotkov@ud
   */
  static function load_custom_wp_editor() {
    if (function_exists('wp_editor')) {
      $quicktags_settings = array( 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,spell,close' );
      wp_editor( '', 'replycontent', array( 'media_buttons' => false, 'tinymce' => false, 'quicktags' => $quicktags_settings, 'tabindex' => 104 ) );
      return;
    }
    ?>
    <textarea style="width:100%;" id="replycontent" name="replycontent"></textarea>
    <?php
  }

  /**
   * Adding comments to WP-admin
   * @global type $this_invoice
   * @global object $wpi_settings
   */
  static function wpi_add_comments_box() {
    global $this_invoice, $wpi_settings;

    add_filter('comments_clauses', array(__CLASS__, 'wpi_comments_clauses'));
    $comments = get_comments('post_id=' . $wpi_settings['web_invoice_page']);
    $invoice_comments = array();
    foreach ($comments as $key => $comment) {
      if (!empty($this_invoice->data['hash']) && $this_invoice->data['hash'] == get_comment_meta($comment->comment_ID, 'invoice_id', true)) {
        $invoice_comments[] = $comment;
      }
    }
    ?>
    <div id="wpi_comments_box" class="postbox hidden">
      <div class="handlediv" title="Click to toggle"><br/></div>
      <h3 class="hndle"><span><?php _e('Comments', ud_get_wp_invoice_quotes()->domain); ?></span></h3>
      <div class="inside">
        <table class="widefat fixed comments" cellspacing="0">
          <thead>
            <tr>
              <th scope="col" id="author" class="manage-column column-author" style=""><?php _e('Author', ud_get_wp_invoice_quotes()->domain); ?></th>
              <th scope="col" id="comment" class="manage-column column-comment" style=""><?php _e('Comment', ud_get_wp_invoice_quotes()->domain); ?></th>
            </tr>
          </thead>
          <tbody id="the-comment-list" class="list:comment">
            <?php
            if(!empty($invoice_comments)) {
              foreach ($invoice_comments as $comment) {
                $in_reply = false;
                if ($comment->comment_parent != 0) {
                  $in_reply = get_comment($comment->comment_parent);
                }
                $wp_list_table = _get_list_table('WP_Post_Comments_List_Table');
                $wp_list_table->single_row( $comment );
              }
            } else {
              ?>
              <tr>
                <td colspan="2"><?php _e('There are no comments for this quote.', ud_get_wp_invoice_quotes()->domain); ?></td>
              </tr>
              <?php
            }
            ?>
          </tbody>
          <tbody id="the-extra-comment-list" class="list:comment" style="display: none;">
          </tbody>
        </table>
        <script type='text/javascript'>
          /* <![CDATA[ */
          var commonL10n = {
            warnDelete: "You are about to permanently delete the selected items.\n  \'Cancel\' to stop, \'OK\' to delete."
          };

          try{convertEntities(commonL10n);}catch(e){};

          var wpAjax = {
            noPerm: "You do not have permission to do that.",
            broken: "An unidentified error has occurred."
          };

          try{convertEntities(wpAjax);}catch(e){};

          var quicktagsL10n = {
            quickLinks: "(Quick Links)",
            wordLookup: "Enter a word to look up:",
            dictionaryLookup: "Dictionary lookup",
            lookup: "lookup",
            closeAllOpenTags: "Close all open tags",
            closeTags: "close tags",
            enterURL: "Enter the URL",
            enterImageURL: "Enter the URL of the image",
            enterImageDescription: "Enter a description of the image",
            fullscreen: "fullscreen",
            toggleFullscreen: "Toggle fullscreen mode"
          };

          try{convertEntities(quicktagsL10n);}catch(e){};

          var adminCommentsL10n = {
            hotkeys_highlight_first: "",
            hotkeys_highlight_last: "",
            replyApprove: "Approve and Reply",
            reply: "Reply"
          };

          function displayBox(obj) {
            var el = document.getElementById(obj);
            if ( el.style.display != 'none' ) {
              el.style.display = 'none';
            } else {
              el.style.display = 'block';
            }
          }

          jQuery.noConflict();
          jQuery(document).ready(function() {
            if(jQuery("#wpi_wpi_invoice_quote_").is(':checked')) {
              jQuery("#postbox_payment_methods").hide();
              jQuery("#wpi_comments_box").show();
              jQuery('#wpi_invoice_type_quote').val('quote');
            } else {
              jQuery("#postbox_payment_methods").show();
              jQuery("#wpi_comments_box").hide();
              jQuery('#wpi_invoice_type_quote').val('');
            }
          });

          jQuery("#wpi_wpi_invoice_quote_").live('click', function() {
            if(jQuery("#wpi_wpi_invoice_quote_").is(':checked')) {
              jQuery("#postbox_payment_methods").hide();
              jQuery("#wpi_comments_box").show();
              jQuery('#wpi_invoice_type_quote').val('quote');
            } else {
              jQuery("#postbox_payment_methods").show();
              jQuery("#wpi_comments_box").hide();
              jQuery('#wpi_invoice_type_quote').val('');
            }
          });

          function wpiDeleteComment(comment, nonce){
            jQuery.post('<?php echo get_site_url(); ?>/wp-admin/admin-ajax.php', {
              id: comment,
              action: 'delete-comment',
              trash: 1,
              _wpnonce: nonce
            });
            jQuery("#comment-"+comment).hide();
          }
          /* ]]> */
        </script>
        <div id="ajax-response"></div>
        <table style="display:none;">
          <tbody id="com-reply">
            <tr id="replyrow" style="display:none;">
              <td colspan="2" class="colspanchange">
                <div id="replyhead" style="display:none;">
                  <h5><?php _e('Reply to Comment', ud_get_wp_invoice_quotes()->domain); ?></h5>
                </div>
                <div id="edithead" style="display:none;">
                  <div class="inside">
                    <label for="author"><?php _e('Name', ud_get_wp_invoice_quotes()->domain); ?></label>
                    <input type="text" name="newcomment_author" size="50" value="" tabindex="101" id="author" />
                  </div>
                  <div class="inside">
                    <label for="author-email"><?php _e('E-mail', ud_get_wp_invoice_quotes()->domain); ?></label>
                    <input type="text" name="newcomment_author_email" size="50" value="" tabindex="102" id="author-email" />
                  </div>
                  <div class="inside">
                    <label for="author-url"><?php _e('URL', ud_get_wp_invoice_quotes()->domain) ?></label>
                    <input type="text" id="author-url" name="newcomment_author_url" size="103" value="" tabindex="103" />
                  </div>
                  <div style="clear:both;"></div>
                </div>
                <div id="replycontainer">
                <?php
                  self::load_custom_wp_editor();
                ?>
                </div>
                <p id="replysubmit" class="submit">
                  <a href="#comments-form" class="cancel button-secondary alignleft btn" tabindex="106"><?php _e('Cancel', ud_get_wp_invoice_quotes()->domain); ?></a>
                  <a href="#comments-form" class="save button-primary alignright btn" tabindex="104">
                    <span id="savebtn" class="btn" style="display:none;"><?php _e('Update Comment', ud_get_wp_invoice_quotes()->domain); ?></span>
                    <span id="replybtn" class="btn" style="display:none;"><?php _e('Submit Reply', ud_get_wp_invoice_quotes()->domain); ?></span>
                  </a>
                  <img class="waiting" style="display:none;" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
                  <span class="error" style="display:none;"></span>
                  <br class="clear" />
                </p>
                <input type="hidden" name="user_ID" id="user_ID" value="<?php echo get_current_user_id(); ?>" />
                <input type="hidden" name="action" id="action" value="" />
                <input type="hidden" name="comment_ID" id="comment_ID" value="" />
                <input type="hidden" name="comment_post_ID" id="comment_post_ID" value="" />
                <input type="hidden" name="status" id="status" value="" />
                <input type="hidden" name="invoice_id" id="invoice_id" value="<?php echo !empty($this_invoice->data['hash'])?$this_invoice->data['hash']:''; ?>" />
                <input type="hidden" name="position" id="position" value="-1" />
                <input type="hidden" name="checkbox" id="checkbox" value="1" />
                <input type="hidden" name="mode" id="mode" value="single" />
                <?php
                  wp_nonce_field( 'replyto-comment', '_ajax_nonce-replyto-comment', false );
                  if ( current_user_can( 'unfiltered_html' ) )
                    wp_nonce_field( 'unfiltered-html-comment', '_wp_unfiltered_html_comment', false );
                ?>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <?php
  }

}
