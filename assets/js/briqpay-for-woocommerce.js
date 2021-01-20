/* global briqpayParams */
jQuery(function ($) {
    var briqpayForWooCommerce = {
        init: function () {
            window._briqpay.subscribe("purchasepressed", function (data) {
                briqpayForWooCommerce.getBriqpayOrder();

            });
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
                    console.log("complete", data);
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
                        console.log('success data', data);
                        console.log(data.result);
                        if ('success' === data.result) {
                            console.log('if part');
                            // logToFile( 'Successfully placed order. Sending "paymentInitiationVerified" to Payson' );
                            // paymentInitiationVerified();
                            window._briqpay.checkout.purchaseDecision(true);
                        } else {
                            throw 'Result failed';
                        }
                    } catch (err) {
                        if (data.messages) {
                            // logToFile( 'Checkout error | ' + data.messages );
                            // failOrder( 'submission', data.messages );
                            console.log(data.messages);
                            console.log('error');
                            window._briqpay.checkout.purchaseDecision(false);
                        } else {
                            // logToFile('Checkout error | No message');
                            // failOrder('submission', '<div class="woocommerce-error">' + 'Checkout error' + '</div>');
                            console.log('catch else');
                            window._briqpay.checkout.purchaseDecision(false);
                        }
                    }
                },
                error: function (data) {
                    // logToFile('AJAX error | ' + data);
                    // failOrder('ajax-error', data);
                    console.log('error function');
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
    };

    briqpayForWooCommerce.init();
});
