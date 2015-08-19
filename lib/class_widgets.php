<?php

/**
 * Lookup Widget class
 */
class InvoiceLookupWidget extends WP_Widget {

  /**
   * Construct
   */
  function __construct() {
    parent::__construct( false, $name = 'Invoice Lookup' );
  }

  /**
   * Draw widget.
   *
   * @see WP_Widget::widget
   *
   * @param type $args
   * @param type $instance
   */
  function widget( $args, $instance ) {
    extract( $args );

    $title = apply_filters( 'widget_title', $instance[ 'title' ] );
    $message = $instance[ 'message' ];
    $button_text = $instance[ 'button_text' ];
    echo $before_widget;

    if ( $title )
      echo $before_title . $title . $after_title;

    wp_invoice_lookup( array(
      'message' => $message,
      'button' => $button_text,
      'return' => false
    ) );
    echo $after_widget;
  }

  /**
   * Widget settings form.
   *
   * @see WP_Widget::form
   *
   * @param type $instance
   */
  function form( $instance ) {
    $title = esc_attr( isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : __('Search Invoices', ud_get_wp_invoice()->domain) );
    $message = esc_attr( isset( $instance[ 'message' ] ) ? $instance[ 'message' ] : '' );
    $button_text = esc_attr( isset( $instance[ 'button_text' ] ) ? $instance[ 'button_text' ] : __('Find', ud_get_wp_invoice()->domain) );
    ?>
    <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', ud_get_wp_invoice()->domain ); ?>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>"/></label></p>
    <p><label for="<?php echo $this->get_field_id( 'message' ); ?>"><?php _e( 'Message:', ud_get_wp_invoice()->domain ); ?>
        <textarea class="widefat" id="<?php echo $this->get_field_id( 'message' ); ?>" name="<?php echo $this->get_field_name( 'message' ); ?>" type="text"><?php echo $message; ?></textarea></label></p>
    <p><label for="<?php echo $this->get_field_id( 'button_text' ); ?>"><?php _e( 'Button Text:', ud_get_wp_invoice()->domain ); ?>
        <input class="widefat" id="<?php echo $this->get_field_id( 'button_text' ); ?>" name="<?php echo $this->get_field_name( 'button_text' ); ?>" type="text" value="<?php echo $button_text; ?>"/></label></p>
  <?php
  }

}

/**
 * Invoice History widget
 */
class InvoiceHistoryWidget extends WP_Widget {

  /**
   * Construct
   */
  function __construct() {
    $widget_ops = array( 'classname' => 'widget_invoice_history', 'description' => __( "User's Paid and Pending Invoices", ud_get_wp_invoice()->domain ) );
    parent::__construct( 'invoice_history', __( 'Invoice History', ud_get_wp_invoice()->domain ), $widget_ops );
  }

  /**
   * Draw widget.
   *
   * @see WP_Widget::widget
   * @global type $current_user
   *
   * @param type $args
   * @param type $instance
   *
   * @return type
   */
  function widget( $args, $instance ) {
    
    extract( $args );
    
    global $current_user, $wpi_settings;

    if ( !$current_user->ID ) {
      return;
    }

    $title = apply_filters( 'widget_title', !empty($instance[ 'title' ])?$instance[ 'title' ]:'' );
    
    $allow_types = !empty( $instance[ 'allow_types' ] ) ? $instance[ 'allow_types' ] : array( 'invoice', 'recurring' );
    $allow_statuses = !empty( $instance[ 'allow_statuses' ] ) ? $instance[ 'allow_statuses' ] : array( 'active', 'paid' );
    
    if ( !is_array($allow_types) ) {
      $allow_types = explode(',', $allow_types);
    }
    
    if ( !is_array($allow_statuses) ) {
      $allow_statuses = explode(',', $allow_statuses);
    }
    
    echo $before_widget;
    
    if ( $title ) {
      echo $before_title . $title . $after_title;
    }
    
  ?>
    <div class="wpi_widget_invoice_history">
      <?php
      
      foreach( $allow_types as $_type ) {

        $invoice_array = WPI_Functions::get_user_invoices( array(
          'user_email' => $current_user->user_email,
          'status' => $allow_statuses,
          'type' => $_type
        ));

        $invoices_found = false;

        if ( !empty( $invoice_array ) && is_array( $invoice_array ) ) {
          $invoices_found = true;
          ?>
          <b class="wpi_sidebar_title"><?php echo $wpi_settings['types'][$_type]['label']; ?></b>
          <ul class="wpi_invoice_history_list wpi_active_invoices">
            <?php
            foreach ( $invoice_array as $invoice ) {
              ?>
              <li class="<?php echo $_type; ?> <?php echo $invoice->data['post_status'] ?>">
                <a href="<?php echo get_invoice_permalink( $invoice->data[ 'invoice_id' ] ); ?>"><?php echo $invoice->data[ 'post_title' ]; ?></a> (<?php echo $invoice->data['post_status'] ?>)
              </li>
            <?php
            }
            ?>
          </ul>
        <?php
        }
      }
      ?>
    </div>
    <?php echo $after_widget; ?>
  <?php
  }

  /**
   * Widget settings form
   *
   * @see WP_Widget::form
   *
   * @param type $instance
   */
  function form( $instance ) {
    global $wpi_settings;
    
    $title = !empty( $instance[ 'title' ] ) ? esc_attr( $instance[ 'title' ] ) : '';
    $types = !empty( $instance[ 'allow_types' ] ) ? $instance[ 'allow_types' ] : array( 'invoice', 'recurring' );
    $statuses = !empty( $instance[ 'allow_statuses' ] ) ? $instance[ 'allow_statuses' ] : array( 'active', 'paid' );
    $allow_types = apply_filters( 'wpi_invoice_history_allow_types', array( 'invoice', 'recurring' ) );
    $allow_statuses = $wpi_settings['statuses'];

    ?>
    <p>
      <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', ud_get_wp_invoice()->domain ); ?>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>"/>
      </label>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id( 'allow_types' ); ?>"><?php _e( 'Types to display:', ud_get_wp_invoice()->domain ); ?></label>
      <ul>
        <?php foreach ( $allow_types as $allow_type ): ?>
          <li><input <?php checked( true, in_array( $allow_type, $types ) ); ?> type="checkbox" name="<?php echo $this->get_field_name( 'allow_types' ); ?>[]" value="<?php echo $allow_type; ?>"/> <?php echo ucfirst( $allow_type ); ?></li>
        <?php endforeach; ?>
      </ul>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id( 'allow_statuses' ); ?>"><?php _e( 'Statuses to display:', ud_get_wp_invoice()->domain ); ?></label>
      <ul>
        <?php foreach ( $allow_statuses as $allow_status ): ?>
          <li><input <?php checked( true, in_array( $allow_status, $statuses ) ); ?> type="checkbox" name="<?php echo $this->get_field_name( 'allow_statuses' ); ?>[]" value="<?php echo $allow_status; ?>"/> <?php echo ucfirst( $allow_status ); ?></li>
        <?php endforeach; ?>
      </ul>
    </p>
  <?php
  }

}
