=== Briqpay For WooCommerce ===
Contributors: krokedil
Tags: woocommerce, briqpay, ecommerce, e-commerce, checkout
Donate link: https://krokedil.com
Requires at least: 4.0
Tested up to: 5.7.0
Requires PHP: 5.6
WC requires at least: 3.4.0
WC tested up to: 5.1.0
Stable tag: trunk
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
= 2021.04.13    - version 1.0.3 =
* Fix           - Fix the live API endpoint to the correct URL.

= 2021.03.02    - version 1.0.2 =
* Enhancement   - Added seperate filters for create, update and refund requests to Billmate.
* Enhancement   - We now save the PSP name from Billmate to the order in the meta field _briqpay_psp_name

= 2021.03.02    - version 1.0.1 =
* Enhancement   - Add change payment method button.
* Enhancement   - We now save and display the org nr in the order.
* Fix           - We now save the company name to the order.

= 2021.03.02    - version 1.0.0 =
* Initial release.