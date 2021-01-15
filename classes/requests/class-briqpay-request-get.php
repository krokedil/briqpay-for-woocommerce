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
}
