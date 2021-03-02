/* global briqpayParams */
jQuery(function ($) {
	var briqpayForWooCommerce = {
		bodyEl: $('body'),
		checkoutFormSelector: 'form.checkout',
		preventPaymentMethodChange: false,
		selectAnotherSelector: '#briqpay-select-other',
		paymentMethodEl: $('input[name="payment_method"]'),

		init: function () {
			if( this.checkIfBriqpaySelected() ) {
				window._briqpay.subscribe("purchasepressed", briqpayForWooCommerce.getBriqpayOrder);
				window._briqpay.subscribe("addressupdate", function (data) {
					briqpayForWooCommerce.updateAddress(data);
				})
				// Update Checkout.
				briqpayForWooCommerce.bodyEl.on('updated_checkout', briqpayForWooCommerce.updateBriqpayOrder);
				briqpayForWooCommerce.bodyEl.on('update_checkout', function () {
					briqpayForWooCommerce.suspend();
				});
				$(document).ready( briqpayForWooCommerce.moveExtraCheckoutFields() );
				briqpayForWooCommerce.bodyEl.on( 'click', briqpayForWooCommerce.selectAnotherSelector, briqpayForWooCommerce.changeFromBriqpay );
			}
			briqpayForWooCommerce.bodyEl.on( 'change', 'input[name="payment_method"]', briqpayForWooCommerce.maybeChangeToBriqpay );

		},
		/**
		 * Moves all non standard fields to the extra checkout fields.
		 */
		moveExtraCheckoutFields: function() {
			// Move order comments.
			$('.woocommerce-additional-fields').appendTo('#briqpay-extra-checkout-fields');

			let form = $('form[name="checkout"] input, form[name="checkout"] select, textarea');
			for ( i = 0; i < form.length; i++ ) {
				let name = form[i].name;
				// Check if field is inside the order review.
				if( $( 'table.woocommerce-checkout-review-order-table' ).find( form[i] ).length ) {
					continue;
				}

				// Check if this is a standard field.
				if ( -1 === $.inArray( name, briqpayParams.standardWooCheckoutFields ) ) {
					// This is not a standard Woo field, move to our div.
					if ( 0 < $( 'p#' + name + '_field' ).length ) {
						$( 'p#' + name + '_field' ).appendTo( '#briqpay-extra-checkout-fields' );
					} else {
						$( 'input[name="' + name + '"]' ).closest( 'p' ).appendTo( '#briqpay-extra-checkout-fields' );
					}
				}
			}
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
			console.log( addressData );

			// Billing fields.
			$('#billing_first_name').val(addressData.billing_address.firstname);
			$('#billing_last_name').val(addressData.billing_address.lastname);
			$('#billing_company').val(addressData.billing_address.companyname);
			$('#billing_address_1').val(addressData.billing_address.streetaddress);
			$('#billing_city').val(addressData.billing_address.city);
			$('#billing_postcode').val(addressData.billing_address.zip);
			$('#billing_phone').val(addressData.billing_address.cellno);
			$('#billing_email').val(addressData.billing_address.email);

			// Shipping fields.
			$('#ship-to-different-address-checkbox').prop( 'checked', true);
			$('#shipping_first_name').val(addressData.shipping_address.firstname);
			$('#shipping_last_name').val(addressData.shipping_address.lastname);
			$('#shipping_company').val(addressData.billing_address.companyname);
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
							briqpayForWooCommerce.logToFile( 'Checkout error | ' + data.messages );
							briqpayForWooCommerce.failOrder( 'submission', data.messages );
						} else {

							// window._briqpay.checkout.purchaseDecision(false);
							briqpayForWooCommerce.logToFile( 'Checkout error | No message' );
							briqpayForWooCommerce.failOrder( 'submission', '<div class="woocommerce-error">' + 'Checkout error' + '</div>' );
						}
					}
				},
				error: function (data) {
					briqpayForWooCommerce.failOrder();
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
					briqpayForWooCommerce.resume();
				}
			});
		},
		failOrder: function( event, error_message ) {
			// Send false and cancel
			window._briqpay.checkout.purchaseDecision(false);

			// Renable the form.
			$( 'body' ).trigger( 'updated_checkout' );
			$( briqpayForWooCommerce.checkoutFormSelector ).removeClass( 'processing' );
			$( briqpayForWooCommerce.checkoutFormSelector ).unblock();
			$( '.woocommerce-checkout-review-order-table' ).unblock();

			// Print error messages, and trigger checkout_error, and scroll to notices.
			$( '.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message' ).remove();
			$( 'form.checkout' ).prepend( '<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' + error_message + '</div>' ); // eslint-disable-line max-len
			$( 'form.checkout' ).removeClass( 'processing' ).unblock();
			$( 'form.checkout' ).find( '.input-text, select, input:checkbox' ).trigger( 'validate' ).blur();
			$( document.body ).trigger( 'checkout_error' , [ error_message ] );
			$( 'html, body' ).animate( {
				scrollTop: ( $( 'form.checkout' ).offset().top - 100 )
			}, 1000 );
		},
		resume: function () {
			window._briqpay.checkout.resume();
		},
		suspend: function () {
			window._briqpay.checkout.suspend();
		},
		/**
		 * When the customer changes from Briqpay to other payment methods.
		 * @param {Event} e 
		 */
		changeFromBriqpay: function( e ) {
			e.preventDefault();

			$( briqpayForWooCommerce.checkoutFormSelector ).block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});

			$.ajax({
				type: 'POST',
				dataType: 'json',
				data: {
					briqpay: false,
					nonce: briqpayParams.change_payment_method_nonce
				},
				url: briqpayParams.change_payment_method_url,
				success: function( data ) {},
				error: function( data ) {},
				complete: function( data ) {
					window.location.href = data.responseJSON.data.redirect;
				}
			});
		},

		/**
		 * When the customer changes to Briqpay from other payment methods.
		 */
		maybeChangeToBriqpay: function() {
			if ( ! briqpayForWooCommerce.preventPaymentMethodChange ) {

			if ( 'briqpay' === $( this ).val() ) {
				$( '.woocommerce-info' ).remove();

				$( briqpayForWooCommerce.checkoutFormSelector ).block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				});

				$.ajax({
					type: 'POST',
					data: {
						briqpay: true,
						nonce: briqpayParams.change_payment_method_nonce
					},
					dataType: 'json',
					url: briqpayParams.change_payment_method_url,
					success: function( data ) {},
					error: function( data ) {},
					complete: function( data ) {
						window.location.href = data.responseJSON.data.redirect;
					}
				});
			}
		}
		},
		/*
		 * Check if Briqpay is the selected gateway.
		 */
		checkIfBriqpaySelected: function() {
			if (briqpayForWooCommerce.paymentMethodEl.length > 0) {
				briqpayForWooCommerce.paymentMethod = briqpayForWooCommerce.paymentMethodEl.filter(':checked').val();
				if( 'briqpay' === briqpayForWooCommerce.paymentMethod ) {
					return true;
				}
			} 
			return false;
		},
	};

	briqpayForWooCommerce.init();
});
