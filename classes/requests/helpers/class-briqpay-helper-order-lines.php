<?php
/**
 * Helper class to get order lines for the requests.
 *
 * @package Briqpay_For_WooCommerce/Classes/Requests/Helpers
 */

defined( 'ABSPATH' ) || exit;

/**
 * Helper class to get order lines for the requests.
 */
class Briqpay_Helper_Order_Lines {
	/**
	 * The WooCommerce order being processed.
	 *
	 * @var WC_Order
	 */
	public static $order;

	/**
	 * Is refund order or not.
	 *
	 * @var bool
	 */
	public static $refund;

	/**
	 * Get the order total amount.
	 *
	 * @param  WC_Order $order  The WooCommerce order.
	 * @param  bool     $refund Is refund order or not.
	 *
	 * @return int
	 */
	public static function get_order_amount( $order, $refund = false ) {
		self::$refund = $refund;
		$order_amount = $order->get_total();

		return self::format_number( $order_amount );
	}

	/**
	 * Get the order lines for the request.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @param  bool     $refund Is refund order or not.
	 * @return array
	 */
	public static function get_order_lines( $order, $refund = false ) {
		self::$refund = $refund;
		self::$order  = $order;
		$order_lines  = array();

		/**
		 * Process order item products.
		 *
		 * @var WC_Order_Item_Product $order_item WooCommerce order item product.
		 */
		foreach ( $order->get_items() as $order_item ) {
			$order_lines[] = self::process_order_item_product( $order_item, $order );
		}

		/**
		 * Process order item shipping.
		 *
		 * @var WC_Order_Item_Shipping $order_item WooCommerce order item shipping.
		 */
		foreach ( $order->get_items( 'shipping' ) as $order_item ) {
			$order_lines[] = self::process_order_item_shipping( $order_item, $order );
		}

		/**
		 * Process order item fee.
		 *
		 * @var WC_Order_Item_Fee $order_item WooCommerce order item fee.
		 */
		foreach ( $order->get_items( 'fee' ) as $order_item ) {
			$order_lines[] = self::process_order_item_fee( $order_item, $order );
		}

		return $order_lines;
	}

	/**
	 * Process order item product and return it formatted for the request.
	 *
	 * @param WC_Order_Item_Product $order_item WooCommerce order item product.
	 * @param WC_Order|null         $order The WooCommerce order.
	 * @return array
	 */
	public static function process_order_item_product( $order_item, $order = null ) {
		if ( ! empty( $order ) && empty( self::$order ) ) {
			self::$order = $order;
		}

		return array(
			'producttype'  => self::get_product_type( $order_item ),
			'reference'    => self::get_reference( $order_item ),
			'name'         => self::get_name( $order_item ),
			'quantity'     => self::get_quantity( $order_item ),
			'quantityunit' => 'pc',
			'unitprice'    => self::get_unit_price( $order_item ),
			'taxrate'      => self::get_tax_rate( $order_item ),
			'discount'     => 0,
		);
	}

	/**
	 * Process order item shipping and return it formatted for the request.
	 *
	 * @param WC_Order_Item_Shipping $order_item WooCommerce order item shipping.
	 * @param WC_Order|null          $order The WooCommerce order.
	 * @return array
	 */
	public static function process_order_item_shipping( $order_item, $order = null ) {
		if ( ! empty( $order ) && empty( self::$order ) ) {
			self::$order = $order;
		}

		return array(
			'producttype'  => 'shipping_line',
			'reference'    => self::get_reference( $order_item ),
			'name'         => self::get_name( $order_item ),
			'quantity'     => self::get_quantity( $order_item ),
			'quantityunit' => 'pc',
			'unitprice'    => self::get_unit_price( $order_item ),
			'taxrate'      => self::get_tax_rate( $order_item ),
			'discount'     => 0,
		);
	}

	/**
	 * Process order item fee and return it formatted for the request.
	 *
	 * @param WC_Order_Item_Fee $order_item WooCommerce order item fee.
	 * @param WC_Order|null     $order The WooCommerce order.
	 * @return array
	 */
	public static function process_order_item_fee( $order_item, $order = null ) {
		if ( ! empty( $order ) && empty( self::$order ) ) {
			self::$order = $order;
		}

		return array(
			'producttype'  => 'digital',
			'reference'    => self::get_reference( $order_item ),
			'name'         => self::get_name( $order_item ),
			'quantity'     => self::get_quantity( $order_item ),
			'quantityunit' => 'pc',
			'unitprice'    => self::get_unit_price( $order_item ),
			'taxrate'      => self::get_tax_rate( $order_item ),
			'discount'     => 0,
		);
	}

	/**
	 * Gets the reference for the order line.
	 *
	 * @param WC_Order_Item_Product|WC_Order_Item_Shipping|WC_Order_Item_Fee $order_item The WooCommerce order item.
	 * @return string
	 */
	public static function get_reference( $order_item ) {
		if ( 'line_item' === $order_item->get_type() ) {
			$product = $order_item['variation_id'] ? wc_get_product( $order_item['variation_id'] ) : wc_get_product( $order_item['product_id'] );
			if ( $product->get_sku() ) {
				$reference = $product->get_sku();
			} else {
				$reference = $product->get_id();
			}
		} elseif ( 'shipping' === $order_item->get_type() ) {
			$reference = $order_item->get_method_id() . ':' . $order_item->get_instance_id();
		} else {
			$reference = $order_item->get_id();
		}

		return $reference;
	}

	/**
	 * Get the name of the order item.
	 *
	 * @param WC_Order_Item $order_item The WooCommerce order item.
	 * @return string
	 */
	public static function get_name( $order_item ) {
		return substr( $order_item->get_name(), 0, 255 );
	}

	/**
	 * Get order item quantity.
	 *
	 * @param WC_Order_Item $order_item The WooCommerce order item.
	 * @return int
	 */
	public static function get_quantity( $order_item ) {
		if ( 'line_item' === $order_item->get_type() ) {
			$quantity = $order_item->get_quantity();
		} else {
			$quantity = 1;
		}

		return abs( $quantity );
	}

	/**
	 * Get the unit price.
	 *
	 * @param WC_Order_Item_Product|WC_Order_Item_Shipping|WC_Order_Item_Fee $order_item The WooCommerce order item.
	 * @return int
	 */
	public static function get_unit_price( $order_item ) {
		$unit_price = ( $order_item->get_total() ) / $order_item->get_quantity();
		return self::format_number( $unit_price );
	}

	/**
	 * Get the tax rate.
	 *
	 * @param WC_Order_Item_Product|WC_Order_Item_Shipping|WC_Order_Item_Fee $order_item The WooCommerce order item.
	 * @return int
	 */
	public static function get_tax_rate( $order_item ) {
		// If we don't have any tax, return 0.
		if ( '0' === $order_item->get_total_tax() ) {
			return 0;
		}

		$tax_items = self::$order->get_items( 'tax' );
		/**
		 * Process the tax items.
		 *
		 * @var WC_Order_Item_Tax $tax_item The WooCommerce order tax item.
		 */
		foreach ( $tax_items as $tax_item ) {
			$rate_id = $tax_item->get_rate_id();
			if ( key( $order_item->get_taxes()['total'] ) === $rate_id ) {
				return round( WC_Tax::_get_tax_rate( $rate_id )['tax_rate'] * 100 );
			}
		}
		return 0;
	}

	/**
	 * Get the total tax amount.
	 *
	 * @param WC_Order_Item_Product|WC_Order_Item_Shipping|WC_Order_Item_Fee $order_item The WooCommerce order item.
	 * @return int
	 */
	public static function get_total_tax_amount( $order_item ) {
		$total_tax_amount = $order_item->get_total_tax();

		return self::format_number( $total_tax_amount );
	}

	/**
	 * Formats a number to the expected format for Briqpay.
	 *
	 * @param int|float $number The number to be formatted.
	 * @return int
	 */
	public static function format_number( $number ) {
		if ( self::$refund ) {
			return abs( intval( round( round( $number, wc_get_price_decimals() ) * 100 ) ) );
		}
		return intval( round( round( $number, wc_get_price_decimals() ) * 100 ) );

	}


	/**
	 * Returns a product type.
	 *
	 * @param WC_Order_Item $order_item WC order item.
	 *
	 * @return string
	 */
	public static function get_product_type( $order_item ) {
		$product_type = $order_item->get_product()->is_virtual() ? 'digital' : 'physical';
		return $product_type;
	}
}
