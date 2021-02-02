/* global briqpayParams */
jQuery(function ($) {
	var briqpayForWooCommerce = {
		bodyEl: $('body'),
		init: function () {
			window._briqpay.subscribe("purchasepressed", function (data) {
				briqpayForWooCommerce.getBriqpayOrder();

			});
			window._briqpay.subscribe("addressupdate", function (data) {
				briqpayForWooCommerce.updateAddress(data);
			})
			// Update Checkout.
			briqpayForWooCommerce.bodyEl.on('updated_checkout', briqpayForWooCommerce.updateBriqpayOrder);

		},
		updateAddress: function (data) {
			let billingCountry = (('country' in data.billingaddress) ? data.billingaddress.country : null);
			let billingZip = (('zip' in data.billingaddress) ? data.billingaddress.zip : null);
			let shippingCountry = (('country' in data.shippingaddress) ? data.shippingaddress.country : null);
			let shippingZip = (('zip' in data.shippingaddress) ? data.shippingaddress.zip : null);

			(billingCountry !== null && billingCountry !== undefined) ? $('#billing_country').val(data.billingaddress.country) : null;
			(billingZip !== null && billingZip !== undefined) ? $('#billing_postcode').val(data.billingaddress.zip) : null;
			(shippingCountry !== null && shippingCountry !== undefined) ? $('#shipping_country').val(data.shippingaddress.country) : null;
			(shippingZip !== null && shippingZip !== undefined) ? $('#shipping_postcode').val(data.shippingaddress.zip) : null;

			$("form.checkout").trigger('update_checkout');
		},
		getBriqpayOrder: function () {
			$.ajax({
				type: 'POST',
				data: {
					briqpay: true,
					nonce: briqpayParams.get_order_nonce,
				},
				dataType: 'json',
				url: briqpayParams.get_order_url,
				success: function (data) {
				},
				error: function (data) {
				},
				complete: function (data) {
					briqpayForWooCommerce.setAddressData(data.responseJSON.data);
				}
			});
		},

		/*
		 * Sets the WooCommerce form field data.
		 */
		setAddressData: function (addressData) {
			if (0 < $('form.checkout #terms').length) {
				$('form.checkout #terms').prop('checked', true);
			}
			// Billing fields.
			$('#billing_first_name').val(addressData.billing_address.firstname);
			$('#billing_last_name').val(addressData.billing_address.lastname);
			$('#billing_address_1').val(addressData.billing_address.streetaddress);
			$('#billing_city').val(addressData.billing_address.city);
			$('#billing_postcode').val(addressData.billing_address.zip);
			$('#billing_phone').val(addressData.billing_address.cellno)
			$('#billing_email').val(addressData.billing_address.email);

			// Shipping fields.
			$('#shipping_first_name').val(addressData.shipping_address.firstname);
			$('#shipping_last_name').val(addressData.shipping_address.lastname);
			$('#shipping_address_1').val(addressData.shipping_address.streetaddress);
			$('#shipping_city').val(addressData.shipping_address.city);
			$('#shipping_postcode').val(addressData.shipping_address.zip);

			// Only set country fields if we have data in them.
			if (addressData.countryCode) {
				$('#billing_country').val(addressData.billing_address.countryCode);
				$('#shipping_country').val(addressData.shipping_address.countryCode);
			}

			briqpayForWooCommerce.submitOrder();

		},
		/**
		 * Submit the order using the WooCommerce AJAX function.
		 */
		submitOrder: function () {
			$('.woocommerce-checkout-review-order-table').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			$.ajax({
				type: 'POST',
				url: briqpayParams.submitOrder,
				data: $('form.checkout').serialize(),
				dataType: 'json',
				success: function (data) {
					try {
						if ('success' === data.result) {
							window._briqpay.checkout.purchaseDecision(true);
						} else {
							throw 'Result failed';
						}
					} catch (err) {
						if (data.messages) {
							window._briqpay.checkout.purchaseDecision(false);
						} else {
							// logToFile('Checkout error | No message');
							// failOrder('submission', '<div class="woocommerce-error">' + 'Checkout error' + '</div>');
							window._briqpay.checkout.purchaseDecision(false);
						}
					}
				},
				error: function (data) {
					// logToFile('AJAX error | ' + data);
					// failOrder('ajax-error', data);
					window._briqpay.checkout.purchaseDecision(false);
				}
			});
		},
		/**
		 * Logs the message to the Briqpay log in WooCommerce.
		 * @param {string} message
		 */
		logToFile: function (message) {
			$.ajax(
				{
					url: briqpayParams.log_to_file_url,
					type: 'POST',
					dataType: 'json',
					data: {
						message: message,
						nonce: briqpayParams.get_log_nonce
					}
				}
			);
		},
		updateBriqpayOrder: function () {
			$.ajax({
				type: 'POST',
				url: briqpayParams.update_order_url,
				data: {
					nonce: briqpayParams.update_order_nonce
				},
				dataType: 'json',
				success: function (data) {
				},
				error: function (data) {
				},
				complete: function (data) {
					let result = data.responseJSON;
					// TODO Update iframe.
					// TODO error handling.
				}
			});
		},
	};

	briqpayForWooCommerce.init();
});
