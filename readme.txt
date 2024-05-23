=== Briqpay For WooCommerce ===
Contributors: krokedil
Tags: woocommerce, briqpay, ecommerce, e-commerce, checkout
Donate link: https://krokedil.com
Requires at least: 5.0
Tested up to: 6.5.3
Requires PHP: 7.0
WC requires at least: 4.0.0
WC tested up to: 8.8.3
Stable tag: 1.6.6
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

== What is Briqpay for WooCommerce? ==
Briqpay for WooCommerce is an embedded checkout for B2B sales where you as a merchant can offer the payment method you prefer, locally or globally, including your own invoice.

== Why choose Briqpay? ==
You have identified that you have different customers who prefer different payment methods. You may want to include your own billing to a greater extent to provide a higher level of service or reduce high transaction fees.
Briqpay also has credit information services in the platform so that you can control payment methods on creditworthiness, amounts, product, etc., which makes it possible to offer new customers to buy against an invoice or other payment method.
With Briqpay, you are always in control of risk exposure.

== Installation ==
1. Upload plugin folder to to the "/wp-content/plugins/" directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Go WooCommerce Settings –> Payment Gateways and configure your Briqpay settings.
4. Read more about the configuration process in the [plugin documentation](https://docs.krokedil.com/category/396-get-started).

== Changelog ==
= 2023.10.31     - version 1.6.6 =
* Tweak          - Improved handling of checkout update events.
* Tweak          - Better handling if session ID is missing between update calls.

= 2023.06.15     - version 1.6.5 =
* Enhancement    - Added separate filter for predefined order creation arguments to Briqpay. You can now use the filter 'briqpay_predefined_order_create_args' instead of 'briqpay_create_args' to specifically target hosted payment page orders.
* Fix            - Changed how we handle errors to prevent error notices.

= 2022.12.14     - version 1.6.4 =
* Fix            - Fixed compatability with PHP 8.x and higher.

= 2022.11.15     - version 1.6.3 =
* Fix            - Log the session id for each request.
* Fix            - Fix errors and warring when credentials are incorrect or empty
* Feature        - Add order id to the filter (briqpay_capture_args)

= 2022.09.07    - version 1.6.2 =
* Tweak         - Sync order to Briqpay button now displayed for all order statuses except Completed.

= 2022.07.13    - version 1.6.1 =
* Tweak         - Adds support for updating customer address in Sync order feature.
* Tweak         - Only display sync order button if Woo order status is On hold or Processing.
* Fix           - Display error message next to "Sync order" button if ajax request result in error.
* Fix           - Don't try to set session_id in Woo session if create_briqpay_order request results in a WP_Error.

= 2022.07.11    - version 1.6.0 =
* Feature       - Add support for edit order from WooCommerce to Briqpay before order is captured. This is done via a "Sync order to Briqpay" button in the Briqpay order metabox. Only available for payment methods flagged as updateOrderSupported.

= 2022.04.13    - version 1.5.0 =
* Feature       - Adds support for hosted payment pages through email or SMS.

= 2022.03.17    - version 1.4.0 =
* Feature       - Add support for Briqpay API v2.0.

= 2022.01.18    - version 1.3.1 =
* Tweak         - Removed "Briqpay" from the payment method title for created orders.

= 2022.01.13    - version 1.3.0 =
* Feature       - The 15 last failed API requests to Bripay will now be shown on the system status page.
* Feature       - The order page meta box for Briqpay can now be modified with the filter 'briqpay_meta_box_keys'
* Enhancement   - Add a sidebar to the settings page with documentation and support links.
* Fix           - Fixed divide by zero error when calculating shipping taxes.
* Fix           - Fixed an issue with already processed orders being set to processing again after the callback from Briqpay is triggered.

= 2021.07.23    - version 1.2.2 =
* Fix           - Fixes potential Shipping company name bug (if separate billing and shipping adress is entered by customer in checkout).

= 2021.07.12    - version 1.2.1 =
* Tweak         - Improved logging.

= 2021.06.04    - version 1.2.0 =
* Feature       - Enable handling of autocaptured orders from Briqpay. If the order has been autocaptured by Briqpay already, we will not attempt to capture it when the order is set to completed in WooCommerce.

= 2021.04.14    - version 1.1.2 =
* Enhancement   - We now save the second address lines for both shipping and billing addresses.
* Enhancement   - We also save the shipping phone and email to the WooCommerce order and display them on the admin page for the order.
* Enhancement   - Added a metabox which show the payment method name, the PSP name of the payment method used, and any failed credit rules that the customer experienced for the order.

= 2021.04.14    - version 1.1.1 =
* Fix           - Improved CSS to display checkout correctly in mobile view.
* Fix           - Avoid fatal error in checkout when calculate_auth fails.

= 2021.04.14    - version 1.1.0 =
* Feature       - Added settings for creditscoring and maxamount.
* Fix           - Delete bearer token (stored as WP transient) on saved plugin settings.
* Fix           - Coding standards fix.

= 2021.04.13    - version 1.0.3 =
* Fix           - Fix the live API endpoint to the correct URL.

= 2021.03.02    - version 1.0.2 =
* Enhancement   - Added seperate filters for create, update and refund requests to Briqpay.
* Enhancement   - We now save the PSP name from Briqpay to the order in the meta field _briqpay_psp_name

= 2021.03.02    - version 1.0.1 =
* Enhancement   - Add change payment method button.
* Enhancement   - We now save and display the org nr in the order.
* Fix           - We now save the company name to the order.

= 2021.03.02    - version 1.0.0 =
* Initial release.
