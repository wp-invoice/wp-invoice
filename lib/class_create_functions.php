<?php


/**
 * Replacement for create_function calls so we can use
 * this plugin with PHP 7.2+
 *
 * @author lipemat
 * @since  12/9/2017
 *
 */
class WPI_Create_Functions {

	/**
	 * stores text to be outputted in JS console
	 *
	 * @static
	 * @var string
	 */
	public static $echo_text;

	/**
	 * Gateway type
	 *
	 * @static
	 * @var string
	 */
	public static $type;

	public static $brackets;


	public static function json_encode( &$item, $key ) {
		if( is_string( $item ) ){
			$item = mb_encode_numericentity( $item, array( 0x80, 0xffff, 0, 0xffff ), 'UTF-8' );
		}
	}


	public static function cleanup_extra_whitespace( $matches ) {
		return preg_replace( '~>[\s]+<((?:t[rdh]|li|\/tr|/table|/ul ))~ims', '><$1', $matches[ 0 ] );
	}


	public static function replace_data( &$val ) {
		$val = self::$brackets[ 'left' ] . $val . self::$brackets[ 'right' ];
	}


	public static function add_log_page() {
		add_menu_page( __( 'Log', ud_get_wp_invoice()->domain ), __( 'Log', ud_get_wp_invoice()->domain ), current_user_can( 'manage_options' ), 'ud_log', array(
			'UD_API',
			'show_log_page',
		) );
	}


	public static function parse_str( &$value, $key ) {
		$value = str_replace( md5( '%2B' ), '+', $value );
	}


	public static function order_sort( $a, $b ) {
		if( $a[ 'order' ] == $b[ 'order' ] ){
			return 0;
		}

		return ( $a[ 'order' ] < $b[ 'order' ] ) ? - 1 : 1;
	}


	public static function position_sort( $a, $b ) {
		return $a[ 'position' ] - $b[ 'position' ];
	}


	public static function gateways( $gateways ) {
		$gateways[] = self::$type;

		return $gateways;
	}


	public static function footer_log() {
		?>
        <script type="text/javascript">
			if( typeof console == "object" ){
				console.log( "<?php echo esc_html( self::$echo_text ); ?>" );
			}
        </script>
		<?php
	}


	public static function register_widgets() {
		//** Load invoice lookup widget */
		register_widget( 'InvoiceLookupWidget' );
		//** load user's invoice history widget */
		register_widget( 'InvoiceHistoryWidget' );
	}


	public static function ajax_get_user_date() {
		die( WPI_Ajax::get_user_date( $_REQUEST[ 'user_email' ] ) );
	}

}