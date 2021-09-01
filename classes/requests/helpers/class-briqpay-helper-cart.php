<?php
/**
 * Cart helper class file.
 *
 * @package Briqpay_For_WooCommerce/Classes/Requests/Helpers
 */

/**
 * Class Briqpay_Helper_Cart
 */
class Briqpay_Helper_Cart {


	/**
	 * Briqpay_Helper_Cart constructor.
	 */
	private function __construct() {}

	/**
	 * Gets formatted cart items.
	 *
	 * @param object $cart The WooCommerce cart object.
	 * @return array Formatted cart items.
	 */
	public static function get_cart_items( $cart = null ) {
		$formatted_cart_items = array();

		if ( null === $cart ) {
			$cart = WC()->cart->get_cart();
		}

		// Get cart items.
		foreach ( $cart as $cart_item ) {
			$formatted_cart_items[] = self::get_cart_item( $cart_item );
		}

		// Get cart fees.
		$cart_fees = WC()->cart->get_fees();
		foreach ( $cart_fees as $fee ) {
			$formatted_cart_items[] = self::get_fee( $fee );
		}

		// Get cart shipping.
		if ( WC()->cart->needs_shipping() ) {
			$shipping = self::get_shipping();
			if ( null !== $shipping ) {
				$formatted_cart_items[] = $shipping;
			}
		}

		return $formatted_cart_items;
	}

	/**
	 * Gets formated cart item.
	 *
	 * @param object $cart_item WooCommerce cart item object.
	 * @return array Formated cart item.
	 */
	public static function get_cart_item( $cart_item ) {
		if ( $cart_item['variation_id'] ) {
			$product = wc_get_product( $cart_item['variation_id'] );
		} else {
			$product = wc_get_product( $cart_item['product_id'] );
		}
		return array(
			'producttype'  => self::get_product_type( $product ),
			'reference'    => self::get_product_sku( $product ), // String.
			'name'         => self::get_product_name( $cart_item ), // String.
			'quantity'     => $cart_item['quantity'], // Float.
			'quantityunit' => 'pc',
			'unitprice'    => self::get_product_unit_price( $cart_item ), // Float.
			'taxrate'      => self::get_product_tax_rate( $cart_item ), // Float.
			'discount'     => 0,
		);

	}

	/**
	 * Gets the product name.
	 *
	 * @param object $cart_item The cart item.
	 * @return string
	 */
	public static function get_product_name( $cart_item ) {
		$cart_item_data = $cart_item['data'];
		$cart_item_name = $cart_item_data->get_name();
		$item_name      = apply_filters( 'briqpay_cart_item_name', $cart_item_name, $cart_item );
		return strip_tags( $item_name );//phpcs:ignore
	}

	/**
	 * Gets the products unit price.
	 *
	 * @param object $cart_item The cart item.
	 * @return float
	 */
	public static function get_product_unit_price( $cart_item ) {
		$item_subtotal = ( $cart_item['line_total'] ) / $cart_item['quantity'];
		return intval( round( $item_subtotal, 2 ) * 100 );
	}

	/**
	 * Gets the tax rate for the product.
	 *
	 * @param object $cart_item The cart item.
	 * @return float
	 */
	public static function get_product_tax_rate( $cart_item ) {
		if ( 0 === intval( $cart_item['line_total'] ) ) {
			return 0;
		}
		return intval( round( $cart_item['line_tax'] / $cart_item['line_total'], 2 ) * 10000 );
	}

	/**
	 * Undocumented static function
	 *
	 * @param object $product The WooCommerce Product.
	 * @return string
	 */
	public static function get_product_sku( $product ) {
		if ( $product->get_sku() ) {
			$item_reference = $product->get_sku();
		} else {
			$item_reference = $product->get_id();
		}

		return $item_reference;
	}

	/**
	 * Formats the fee.
	 *
	 * @param object $fee A WooCommerce Fee.
	 * @return array
	 */
	public static function get_fee( $fee ) {
		return array(
			'producttype'  => 'digital',
			'name'         => $fee->name,
			'unitprice'    => intval( round( $fee->amount, 2 ) * 100 ),
			'quantityunit' => 'pc',
			'quantity'     => 1,
			'taxrate'      => intval( ( $fee->tax / $fee->amount ) * 10000 ),
			'reference'    => 'fee|' . $fee->id,
			'discount'     => 0,
		);
	}

	/**
	 * Formats the shipping.
	 *
	 * @return array
	 */
	public static function get_shipping() {
		$packages        = WC()->shipping()->get_packages();
		$chosen_methods  = WC()->session->get( 'chosen_shipping_methods' );
		$chosen_shipping = $chosen_methods[0];
		foreach ( $packages as $i => $package ) {
			foreach ( $package['rates'] as $method ) {
				if ( $chosen_shipping === $method->id ) {
					if ( $method->cost > 0 ) {

						$taxrate = 0;
						if ( intval( WC()->cart->shipping_total ) > 0 ) {
							$taxrate = intval( ( WC()->cart->shipping_tax_total / WC()->cart->shipping_total ) * 10000 );
						}

						return array(
							'producttype'  => 'shipping_fee',
							'name'         => $method->label, // String.
							'unitprice'    => intval( round( WC()->cart->shipping_total * 100 ) ),
							'quantityunit' => 'pc',
							'quantity'     => 1,
							'taxrate'      => $taxrate,
							'reference'    => 'shipping|' . $method->id, // String.
							'discount'     => 0,
						);
					}

					return array(
						'producttype'  => 'shipping_fee',
						'name'         => $method->label, // String.
						'unitprice'    => 0,
						'quantityunit' => 'pc',
						'quantity'     => 1,
						'taxrate'      => 0,
						'reference'    => 'shipping|' . $method->id,
						'discount'     => 0,
					);
				}
			}
		}
	}

	/**
	 * Returns a product type.
	 *
	 * @param WC_Product $product WC product.
	 *
	 * @return string
	 */
	public static function get_product_type( $product ) {
		return $product->is_virtual() ? 'digital' : 'physical';
	}
}
