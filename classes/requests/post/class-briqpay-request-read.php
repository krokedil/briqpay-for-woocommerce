<?php
/**
 * Briqpay_Request_Read class file.
 *
 * @package Briqpay_For_WooCommerce/Classes/Requests/Post
 */

/**
 * Class Briqpay_Request_Read
 */
class Briqpay_Request_Read extends Briqpay_Request_Post {
	/**
	 * Returns the arguments request.
	 *
	 * @return array
	 */
	protected function get_body() {
		return array(
			'sessionid' => $this->arguments['session_id'],
		);
	}

	/**
	 * Returns the token for Bearer auth.
	 *
	 * @return string
	 */
	protected function calculate_auth() {
		$token = get_transient( 'briqpay_bearer_token_read_session' );
		if ( ! empty( $token ) ) {
			return 'Bearer ' . $token;
		}
	}

	/**
	 * Returns the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		return $this->get_api_url_base() . 'checkout/v1/readsession';
	}
}

