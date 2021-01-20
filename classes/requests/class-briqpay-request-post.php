<?php
/**
 * Krokedil Briqpay Request base class
 *
 * @package Briqpay_For_WooCommerce/Classes/Request
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Common ancestor of all POST requests against the Briqpay API.
 *
 * @class    Briqpay_Request_Post
 * @version  0.1
 * @package  Briqpay_For_WooCommerce/Classes/Request
 * @category Class
 * @author   Krokedil
 */
abstract class Briqpay_Request_Post extends Briqpay_Request {
	// 556805-0271

	/**
	 * Briqpay_Request_Post constructor.
	 *
	 * @param  array $arguments  The request arguments.
	 * @param  bool  $generate_token Checks whether generating the token based on an existing session is needed.
	 */
	public function __construct( $arguments = array(), $generate_token = false ) {
		parent::__construct( $arguments );
		$this->method         = 'POST';
		$this->generate_token = $generate_token;
	}

	/**
	 * Build and return proper request arguments for this request type.
	 *
	 * @return array Request arguments
	 */
	protected function get_request_args() {
		return array(
			'headers'    => $this->get_request_headers(),
			'user-agent' => $this->get_user_agent(),
			'method'     => $this->method,
			'timeout'    => apply_filters( 'briqpay_request_timeout', 10 ),
			'body'       => wp_json_encode( $this->get_body() ),
		);
	}

	/**
	 * Builds the request args for a POST request.
	 *
	 * @return array
	 */
	abstract protected function get_body();
}

