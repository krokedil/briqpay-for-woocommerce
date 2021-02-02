<?php
/**
 * Request class for making auth request
 *
 * @package Briqpay_For_WooCommerce/Classes/Requests/Get
 */

/**
 * Class Briqpay_Request_Auth
 */
class Briqpay_Request_Auth extends Briqpay_Request_Get {


	/**
	 * Briqpay_Request_Auth constructor.
	 *
	 * @param  array $arguments  The request arguments.
	 * @param  bool  $generate_token Checks whether generating the token based on an existing session is needed.
	 */
	public function __construct( $arguments = array(), $generate_token = false ) {
		parent::__construct( $arguments );
		$this->generate_token = $generate_token;

	}

	/**
	 * Get the request headers.
	 *
	 * @return array
	 */
	protected function get_request_headers() {
		return array(
			'Authorization' => 'Basic ' . base64_encode( $this->get_merchant_id() . ':' . $this->get_secret() ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- Base64 used to calculate auth header.
		);
	}

	/**
	 * Empty authorization header for authentication POSTs
	 * Returns nothing so there is no Authorization header. The actual authentication is done in the request body.
	 *
	 * @return void
	 */
	public function calculate_auth() { }

	/**
	 * Obtain an bearer token for Briqpay based on the configured credentials
	 *
	 * @return string|object Either a fresh Briqpay token, or a null value if the request failed.
	 */
	public function obtain_token() {
		$response = $this->request();
		if ( is_wp_error( $response ) ) {
			// This block is essentially useless, but I'm leaving it in in case the error needs to be modified later.
			return $response; // For now, just pass the WP error up the chain.
		}
		return $response;
	}

	/**
	 *  Get the request url.
	 *
	 * @return string|void
	 */
	protected function get_request_url() {
		if ( true === $this->generate_token ) {
			return $this->get_api_url_base() . 'auth/' . $this->arguments['session_id'];
		}
		return $this->get_api_url_base() . 'auth';
	}

}
