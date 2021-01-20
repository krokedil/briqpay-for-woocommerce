<?php //phpcs:ignore
/**
 * Functions file for the plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gets a Briqpay order. Either creates or updates existing order
 *
 * @param  int $order_id  The WooCommerce order id.
 *
 * @return array|mixed|void
 */
function briqpay_create_or_update_order( int $order_id = 0 ) {
	$cart    = WC()->cart;
	$session = WC()->session;
	$api     = BRIQPAY()->api;
	$cart->calculate_fees();
	$cart->calculate_shipping();
	$cart->calculate_totals();
	$md5         = md5( wp_json_encode( $session->get( 'cart' ) ) );
	$md5_session = $session->get( 'briqpay_md5_hash' );
	// $briqpay_order = $api->create_briqpay_order();
	$a = 10;
	if ( $md5_session === $md5 ) {
		// $briqpay_order = $api->update_briqpay_order( $session->get( 'briqpay_session_id' ) );
		// TODO get existing session from briqpay api.
		$args          = array(
			'session_id' => $session->get( 'briqpay_session_id' ),
		);
		$briqpay_order = $api->get_briqpay_order( $args );
		error_log( 'read' );
		error_log( var_export( $briqpay_order, true ) );
		// if ( ! $briqpay_order ) {
		// If update order failed try to create new order.
		// $briqpay_order = $api->create_briqpay_order();
		// if ( ! $briqpay_order ) {
		// If failed then bail.
		// return;
		// }
		// $session->set( 'briqpay_session_id', $briqpay_order['sessionid'] );
		// $session->set( 'briqpay_md5_hash', $md5 );
		// return $briqpay_order;
		// }
		return $briqpay_order;
	} else {
		$briqpay_order = $api->create_briqpay_order();
		error_log( 'create' );
		 error_log( var_export( $briqpay_order, true ) );

		if ( ! $briqpay_order ) {
			return;
		}
		$session->set( 'briqpay_session_id', $briqpay_order['sessionid'] );
		$session->set( 'briqpay_md5_hash', $md5 );

		return $briqpay_order;
	}
}

/**
 * Echoes Briqpay Checkout iframe snippet.
 */
function briqpay_wc_show_snippet() {
	$briqpay_order = briqpay_create_or_update_order();
	do_action( 'briqpay_wc_show_snippet', $briqpay_order );
	echo $briqpay_order['snippet'];//phpcs:ignore
}
function is_briqpay_confirmation() {
	return isset( $_GET['confirm'], $_GET['briqpay_wc_order_id'] ) && 'yes' === $_GET['confirm'];//phpcs:ignore
}

function briqpay_extract_error_message( $wp_error ) {
	wc_print_notice( $wp_error->get_error_message(), 'error' );
}
