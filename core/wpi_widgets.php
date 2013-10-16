<?php

/**
 * Lookup Widget class
 */
class InvoiceLookupWidget extends WP_Widget {

  /**
   * Construct
   */
  function InvoiceLookupWidget() {
    parent::WP_Widget( false, $name = 'Invoice Lookup' );
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
   * Update widget.
   *
   * @see WP_Widget::update
   *
   * @param type $new_instance
   * @param type $old_instance
   *
   * @return type
   */
  function update( $new_instance, $old_instance ) {
    return $new_instance;
  }

  /**
   * Widget settings form.
   *
   * @see WP_Widget::form
   *
   * @param type $instance
   */
  function form( $instance ) {
    $title = esc_attr( $instance[ 'title' ] );
    $message = esc_attr( $instance[ 'message' ] );
    $button_text = esc_attr( $instance[ 'button_text' ] );
    ?>
    <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>"/></label></p>
    <p><label for="<?php echo $this->get_field_id( 'message' ); ?>"><?php _e( 'Message:' ); ?>
        <textarea class="widefat" id="<?php echo $this->get_field_id( 'message' ); ?>" name="<?php echo $this->get_field_name( 'message' ); ?>" type="text"><?php echo $message; ?></textarea></label></p>
    <p><label for="<?php echo $this->get_field_id( 'button_text' ); ?>"><?php _e( 'Button Text:' ); ?>
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
  function InvoiceHistoryWidget() {
    $widget_ops = array( 'classname' => 'widget_invoice_history', 'description' => __( 'User&#8217;s Paid and Pending Invoices' ) );
    parent::WP_Widget( 'invoice_history', __( 'Invoice History' ), $widget_ops );
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
    global $current_user;

    if ( !$current_user->ID )
      return;

    $title = apply_filters( 'widget_title', $instance[ 'title' ] );
    $allow_types = !empty( $instance[ 'allow_types' ] ) ? $instance[ 'allow_types' ] : array( 'invoice', 'recurring' );
    if ( !is_array($allow_types) ) $allow_types = explode(',', $allow_types);
    ?>
    <?php echo $before_widget; ?>
    <?php if ( $title )
      echo $before_title . $title . $after_title; ?>
    <div class="wpi_widget_invoice_history">
      <?php
      $invoice_array = WPI_Functions::get_user_invoices( "user_email={$current_user->user_email}&status=active" );

      foreach ( $invoice_array as $key => $wpi_object ) {
        if ( !in_array( $wpi_object->data[ 'type' ], $allow_types ) ) {
          unset( $invoice_array[ $key ] );
        }
      }

      $invoices_found = false;

      if ( !empty( $invoice_array ) && is_array( $invoice_array ) ) {
        $invoices_found = true;
        ?>
        <b class="wpi_sidebar_title"><?php _e( "Active Invoice(s)" ); ?></b>
        <ul class="wpi_invoice_history_list wpi_active_invoices">
          <?php
          foreach ( $invoice_array as $invoice ) {
            ?>
            <li><a href="<?php echo get_invoice_permalink( $invoice->data[ 'invoice_id' ] ); ?>"><?php echo $invoice->data[ 'post_title' ]; ?></a></li>
          <?php
          }
          ?>
        </ul>
      <?php
      }
      ?>
      <?php
      $invoice_array = WPI_Functions::get_user_invoices( "user_email={$current_user->user_email}&status=paid" );

      foreach ( $invoice_array as $key => $wpi_object ) {
        if ( !in_array( $wpi_object->data[ 'type' ], $allow_types ) ) {
          unset( $invoice_array[ $key ] );
        }
      }

      if ( !empty( $invoice_array ) && is_array( $invoice_array ) ) {
        $invoices_found = true;
        ?>
        <b class="wpi_sidebar_title"><?php _e( "Paid Invoice(s)" ); ?></b>
        <ul class="wpi_invoice_history_list wpi_active_invoices">
          <?php
          foreach ( $invoice_array as $invoice ) {
            ?>
            <li><a href="<?php echo get_invoice_permalink( $invoice->data[ 'invoice_id' ] ); ?>"><?php echo $invoice->data[ 'post_title' ]; ?></a></li>
          <?php
          }
          ?>
        </ul>
      <?php
      }

      if ( !$invoices_found ) {
        ?>
        <p><?php _e( 'You currently do not have any invoices', WPI ); ?></p>
      <?php
      }
      ?>
    </div>
    <?php echo $after_widget; ?>
  <?php
  }

  /**
   * Update widget
   *
   * @see WP_Widget::update
   *
   * @param type $new_instance
   * @param type $old_instance
   *
   * @return type
   */
  function update( $new_instance, $old_instance ) {
    return $new_instance;
  }

  /**
   * Widget settings form
   *
   * @see WP_Widget::form
   *
   * @param type $instance
   */
  function form( $instance ) {
    $title = !empty( $instance[ 'title' ] ) ? esc_attr( $instance[ 'title' ] ) : '';
    $types = !empty( $instance[ 'allow_types' ] ) ? $instance[ 'allow_types' ] : array( 'invoice', 'recurring' );

    $allow_types = apply_filters( 'wpi_invoice_history_allow_types', array( 'invoice', 'recurring' ) );

    ?>
    <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>"/></label></p>
    <p>
      <label for="<?php echo $this->get_field_id( 'allow_types' ); ?>"><?php _e( 'Types to display:' ); ?></label>
    <ul>
        <?php foreach ( $allow_types as $allow_type ): ?>
          <li><input <?php checked( true, in_array( $allow_type, $types ) ); ?> type="checkbox" name="<?php echo $this->get_field_name( 'allow_types' ); ?>[]" value="<?php echo $allow_type; ?>"/> <?php echo ucfirst( $allow_type ); ?></li>
        <?php endforeach; ?>
        </ul>
    </p>
  <?php
  }

}
