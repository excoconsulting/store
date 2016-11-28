=== WooCommerce Distance Rate Shipping ===
Contributors: kloon
Tags: woocommerce, shipping, distance
Requires at least: 3.8
Tested up to: 3.9.1
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily calculate shipping for your WooCommerce store based on the distance and travel time to customer, items in cart and cart total

== Description ==

The WooCommerce Distance Rates Shipping extension enabled you to charge based on the distance between your store and the customer location.

You also have the option to setup rules to charge based on the total time it would take to travel from your store to the customer location, 
as well as based on the weight of the items in the cart or the number of items in the cart.

Rules can be combine to offer shipping that matches different conditions or only one condition at a time.

== Installation ==

This extension require WooCommerce to be installed and activated

To install the extension

1. Upload `woocommerce-distance-rate-shipping` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to `WooCommerce -> Settings -> Shipping -> Distance Rate` to configure

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

= 1.0.3 =
* Fix - Avoid calculating the rate if only the country is selected.

= 1.0.2 =
* Fix - Convert metric to imperial as Google only returns metric values.

= 1.0.1 =
* Fix - Distance unit only worked in km, now support mi

= 1.0.0 =
* First release