<?php
/**
 * Base class for request class.
 *
 * @package Briqpay_For_WooCommerce/classes/requests/
 */

/**
 * Class Briqpay_Request_Get
 */
abstract class Briqpay_Request_Get extends Briqpay_Request {


	/**
	 * Briqpay_Request_Get constructor.
	 *
	 * @param  array $arguments The request arguments.
	 */
	public function __construct( $arguments = array() ) {
		parent::__construct( $arguments );
		$this->method = 'GET';
	}

	/**
	 *  Get the request args.
	 *
	 * @return array
	 */
	protected function get_request_args() {
		return array(
			'headers'    => $this->get_request_headers(),
			'user-agent' => $this->get_user_agent(),
			'method'     => $this->method,
			'timeout'    => apply_filters( 'briqpay_request_timeout', 10 ),
		);
	}
}
