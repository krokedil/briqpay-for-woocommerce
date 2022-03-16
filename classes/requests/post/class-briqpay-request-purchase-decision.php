<?php
/**
 * @package Briqpay_For_WooCommerce/Classes/Requests/Post
 */


/**
 * Class Briqpay_Request_Purchase_Decision
 */
class Briqpay_Request_Purchase_Decision extends Briqpay_Request_Post {

	/**
	 * Class constructor.
	 *
	 * @param array $arguments The request arguments.
	 * @param  bool  $generate_token Checks whether generating the token based on an existing session is needed.
	 */
	public function __construct( $arguments, $generate_token ) {
		parent::__construct( $arguments, $generate_token );
		$this->log_title = 'Briqpay purchase decision';
	}

	/**
	 * Returns the arguments request.
	 *
	 * @return array
	 */
	protected function get_body() {
		return array(
			'decision' => $this->arguments['decision'] ? 'allow' : 'reject',
		);
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		return $this->get_api_url_base() . 'v2/session/' . $this->arguments['session_id'] . '/decision/purchase';
	}
}
