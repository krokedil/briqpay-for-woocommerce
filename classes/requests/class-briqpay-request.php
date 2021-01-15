<?php
/**
 * Krokedil Briqpay Request base class
 *
 * @package Briqpay_For_WooCommerce/Classes/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Briqpay_Request
 */
abstract class Briqpay_Request {

	/**
	 * The request method.
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * The request title.
	 *
	 * @var string
	 */
	protected $log_title;

	/**
	 * The Briqpay session id.
	 *
	 * @var string
	 */
	protected $briqpay_session_id;

	/**
	 * The request arguments.
	 *
	 * @var array
	 */
	protected $arguments;

	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Class constructor.
	 *
	 * @param array $arguments The request args.
	 */
	public function __construct( $arguments = array() ) {
		$this->arguments = $arguments;
		// Loads the Briqpay settings and sets them to be used here.
		$this->settings = get_option( 'woocommerce_briqpay_settings' );
	}

	/**
	 * Obtain the global base URL for Briqpay API requests based on settings option.
	 *
	 * @return string API base URL
	 */
	public function get_api_url_base() {
		return $this->is_test_mode() ? $this->test_api_url_base() : $this->api_url_base();
	}

	/**
	 * Is testmode enabled.
	 *
	 * @return bool
	 */
	protected function is_test_mode() {
		return 'yes' === $this->settings['testmode'];
	}

	/**
	 * Returns base api url.
	 *
	 * @return string API base URL
	 */
	private function api_url_base() {
		return 'https://playground-api.briqpay.com/';
	}
	/**
	 * Returns test base api url.
	 *
	 * @return string API base URL
	 */
	private function test_api_url_base() {
		return 'https://playground-api.briqpay.com/';
	}

	/**
	 * Returns a client id
	 *
	 * @return string
	 */
	protected function get_merchant_id() {
		return $this->is_test_mode() ? $this->settings['test_merchant_id'] : $this->settings['merchant_id'];
	}

	/**
	 * Returns a client secret
	 *
	 * @return string
	 */
	protected function get_secret() {
		return $this->is_test_mode() ? $this->settings['test_shared_secret'] : $this->settings['shared_secret'];
	}


	/**
	 * Get the request headers.
	 *
	 * @return array
	 */
	protected function get_request_headers() {
		return array(
			'Content-type'  => 'application/json',
			'Authorization' => $this->calculate_auth(),
		);
	}

	/**
	 * Calculates the basic auth.
	 *
	 * @return string
	 */
	protected function calculate_auth() {
		$token = get_transient( 'briqpay_bearer_token' );

		if ( empty( $token ) ) {
			// todo create auth request class like we have in Briqpay.
			$auth_request = new Briqpay_Request_Auth(
				array(
					'headers' => array(
						'Authorization' => 'Basic ' . base64_encode( $this->get_merchant_id() . ':' . $this->get_secret() ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- Base64 used to calculate auth header.
					),
				)
			);
			$response     = $auth_request->obtain_token();

			if ( isset( $response['token'] ) && ! is_wp_error( $response ) ) {
				$token = $response['token'];
				// NOTE: The Bearer Token is good for 24 hours (86400 seconds).
				set_transient( 'briqpay_bearer_token', $token, 86200 );
			}
		}

		// FIXME:  If obtaining a token failed, and is_wp_error($token), then that should clearly be handled somehow.

		if ( ! empty( $token ) ) {
			return 'Bearer ' . $token;
		}
		// Falls through to return null if no token could be obtained.
	}

	/**
	 * Get the user agent.
	 *
	 * @return string
	 */
	protected function get_user_agent() {
		return apply_filters(
			'http_headers_useragent',
			'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' )
		) . ' - WooCommerce: ' . WC()->version . ' - KUS: ' . BRIQPAY_WC_PLUGIN_VERSION . ' - PHP Version: ' . PHP_VERSION . ' - Krokedil';
	}

	/**
	 * Get the request args.
	 *
	 * @return array
	 */
	abstract protected function get_request_args();

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	abstract protected function get_request_url();

	/**
	 * Make the request.
	 *
	 * @return object|WP_Error
	 */
	public function request() {
		$url      = $this->get_request_url();
		$args     = $this->get_request_args();
		$response = wp_remote_request( $url, $args );
		return $this->process_response( $response, $args, $url );
	}

	/**
	 * Processes the response checking for errors.
	 *
	 * @param object|WP_Error $response The response from the request.
	 * @param array           $request_args The request args.
	 * @param string          $request_url The request url.
	 * @return array|WP_Error
	 */
	protected function process_response( $response, $request_args, $request_url ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code < 200 || $response_code > 299 ) {
			$data          = 'URL: ' . $request_url . ' - ' . wp_json_encode( $request_args );
			$error_message = '';
			// TODO fix.

			// error_log(var_export( $response['body'], true));
			// error_log('ovo je body');
			// Get the error messages.
			// if ( null !== json_decode( $response['body'], true ) ) {
			// $errors = json_decode( $response['body'], true );
			// error_log('ovo je error');
			// error_log(var_export($error, true));
			// error_log('ovo je error');
			// foreach ( $errors as $error ) {
			// $error_message .= ' ' . $error;
			// }
			// }
			$return = new WP_Error( wp_remote_retrieve_response_code( $response ), $response['body'] . 'something', $data );
		} else {
			$return = json_decode( wp_remote_retrieve_body( $response ), true );
		}

		$this->log_response( json_decode( wp_remote_retrieve_body( $response ), true ), $request_args, $request_url );

		return $return;
	}

	/**
	 * Logs the response from the request.
	 *
	 * @param object|WP_Error $response The response from the request.
	 * @param array           $request_args The request args.
	 * @param string          $request_url The request URL.
	 * @return void
	 */
	protected function log_response( $response, $request_args, $request_url ) {
		$method = $this->method;
		$title  = "{$this->log_title} - URL: {$request_url}";
		$code   = wp_remote_retrieve_response_code( $response );
		Briqpay_Logger::format_log( 1, $method, $title, $request_args, $response, $code );
	}
}
