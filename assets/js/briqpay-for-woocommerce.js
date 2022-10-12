/* global briqpayParams */
jQuery(function ($) {
	if (window.briqpayForWooCommerce === undefined || window.briqpayForWooCommerce === null) {
		return false;
	}
	var briqpayForWooCommerce = {
		bodyEl: $('body'),
		checkoutFormSelector: 'form.checkout',
		preventPaymentMethodChange: false,
		selectAnotherSelector: '#briqpay-select-other',
		paymentMethodEl: $('input[name="payment_method"]'),

		init: function () {
			if( this.checkIfBriqpaySelected() ) {
				window._briqpay.subscribe("purchasepressed", function() { briqpayForWooCommerce.getBriqpayOrder(1) });
				window._briqpay.subscribe("before-purchase", function() { briqpayForWooCommerce.getBriqpayOrder(2) });
				window._briqpay.subscribe("paymentProcessCancelled", function() { briqpayForWooCommerce.unlockCheckout(); })
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
		getBriqpayOrder: function (version) {
			briqpayForWooCommerce.logToFile( 'Received purchasepressed callback from Briqpay' );
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
					briqpayForWooCommerce.setAddressData(data.responseJSON.data, version);
				}
			});
		},
		/*
		 * Sets the WooCommerce form field data.
		 */
		setAddressData: function (addressData, version) {
			if (0 < $('form.checkout #terms').length) {
				$('form.checkout #terms').prop('checked', true);
			}
			console.log( addressData );

			// Billing fields.
			$('#billing_first_name').val(addressData.billing_address.firstname);
			$('#billing_last_name').val(addressData.billing_address.lastname);
			$('#billing_company').val(addressData.billing_address.companyname);
			$('#billing_address_1').val(addressData.billing_address.streetaddress);
			$('#billing_address_2').val(addressData.billing_address.streetaddress2);
			$('#billing_city').val(addressData.billing_address.city);
			$('#billing_postcode').val(addressData.billing_address.zip);
			$('#billing_phone').val(addressData.billing_address.cellno);
			$('#billing_email').val(addressData.billing_address.email);

			// Shipping fields.
			$('#ship-to-different-address-checkbox').prop( 'checked', true);
			$('#shipping_first_name').val(addressData.shipping_address.firstname);
			$('#shipping_last_name').val(addressData.shipping_address.lastname);
			$('#shipping_company').val(addressData.shipping_address.companyname);
			$('#shipping_address_1').val(addressData.shipping_address.streetaddress);
			$('#shipping_address_2').val(addressData.shipping_address.streetaddress2);
			$('#shipping_city').val(addressData.shipping_address.city);
			$('#shipping_postcode').val(addressData.shipping_address.zip);

			// Only set country fields if we have data in them.
			if (addressData.countryCode) {
				$('#billing_country').val(addressData.billing_address.countryCode);
				$('#shipping_country').val(addressData.shipping_address.countryCode);
			}

			briqpayForWooCommerce.submitOrder(version);

		},
		/**
		 * Submit the order using the WooCommerce AJAX function.
		 */
		submitOrder: function (version) {
			$('.woocommerce-checkout-review-order-table').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			if(version === 2) {
				// Append version number to the form.
				$(briqpayForWooCommerce.checkoutFormSelector).append("<input type='hidden' value='2' id='briqpay_checkout_version' name='briqpay_checkout_version'>");
			}

			$.ajax({
				type: 'POST',
				url: briqpayParams.submitOrder,
				data: $('form.checkout').serialize(),
				dataType: 'json',
				success: function (data) {
					try {
						if ('success' === data.result) {
							console.log('success', data);
							briqpayForWooCommerce.logToFile( 'Successfully placed order. Sending purchaseDecision true to Briqpay' );
							briqpayForWooCommerce.handlePurchaseResult(version, true)
						} else {
							console.log('not success', data);
							throw 'Result failed';
						}
					} catch (err) {
						if (data.messages) {
							console.log('catch if', data);
							briqpayForWooCommerce.logToFile( 'Checkout error | ' + data.messages );
							briqpayForWooCommerce.failOrder( 'submission', data.messages, version );
						} else {
							console.log('catch else', err);
							briqpayForWooCommerce.handlePurchaseResult(version, false)
							briqpayForWooCommerce.logToFile( 'Checkout error | No message' );
							briqpayForWooCommerce.failOrder( 'submission', '<div class="woocommerce-error">' + 'Checkout error' + '</div>', version );
						}
					}
				},
				error: function (data) {
					console.log('error', data);
					briqpayForWooCommerce.failOrder(null, null, version);
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

		failOrder: function( event, error_message, version ) {
			// Send false and cancel
			briqpayForWooCommerce.handlePurchaseResult(version, false)

			// Renable the form.
			briqpayForWooCommerce.unlockCheckout();

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
		
		handlePurchaseResult(version, success) {
			if(version === 1) {
				window._briqpay.checkout.purchaseDecision(success);
			} else {
				window._briqpay.checkout.resumeDecision();
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

		/**
		 * Unlock the WooCommerce checkout for the customer.
		 */
		unlockCheckout: function() {
			$( 'body' ).trigger( 'updated_checkout' );
			$( briqpayForWooCommerce.checkoutFormSelector ).removeClass( 'processing' );
			$( briqpayForWooCommerce.checkoutFormSelector ).unblock();
			$( '.woocommerce-checkout-review-order-table' ).unblock();
		},
	};

	briqpayForWooCommerce.init();
});
