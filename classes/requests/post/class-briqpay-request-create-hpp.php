<?php
/**
 * @package Briqpay_For_WooCommerce/Classes/Requests/Post
 */


/**
 * Class Briqpay_Request_Create_HPP
 */
class Briqpay_Request_Create_HPP extends Briqpay_Request_Post {

	/**
	 * Class constructor.
	 */
	public function __construct($args,$new_token) {
		parent::__construct($args,$new_token);
		$this->log_title = 'Create a HPP session';
	}
	/**
	 * Returns the arguments request.
	 *
	 * @return array
	 */
	protected function get_body() {
	
		return apply_filters(
			'briqpay_create_hpp_args',
			array(
				'checkoutsessionid'       => $this->arguments["session_id"],
				'deliverymethod'         => array( "type" => $this->arguments["destination_type"], "destination" => $this->arguments["destination"]),
				'config' => array("showcart" => true, "logoUrl" =>"https://test.com")
			)
		);
	}
	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		return $this->get_api_url_base() . 'hostedpage/v1/checkout';
	}
}
