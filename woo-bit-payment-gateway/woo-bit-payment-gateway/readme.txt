=== Bit Payment Gateway for WooCommerce  ===
Contributors: zorem
Donate link:https://www.zorem.com 
Tags: WooCommerce
Requires at least: 4.0
Requires PHP: 7.2
Tested up to: 6.0.1
Stable tag: 2.0
License: GPLv2 
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WooCommerce Payment Gateway Plugin for Bit payment method, an Israeli money transfer service/app. 

== Description ==

Bit Payment method, is a gateway that require no payment be made online, orders using Bit Payment are set to Bit Pending Payment status until payment clears outside of WooCommerce.
You, as the store owner, should confirm that payments have cleared with Bit before processing orders in WooCommerce. It’s important to verify that you are paid before shipping an order and marking it as Processing or Complete.

== Features ==

* Receive offline Payments using Bit Payment method
* Orders paid using Bit Payment will get order status of “Pending Bit” 
* Bit Payment instructions will display on “order received” page and in order email to customer.
* Bit Payment message will display in Admin new order email
* Set Custom email subject and Custom email heading for Bit payment emails

== Installation ==

1. Upload the folder 'woocommerce-bit-payment-gateway` to the `/wp-content/plugins/` folder
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Enable Bit Payment Gateway from WooCommerce payment settings

== Setup and Configuration ==

* Go to: WooCommerce > Settings > Payments.
* Use the toggle under Enable to select Bit Payment.
* Select Set Up. You are taken to the Bit Payment settings.
* Configure your settings:
Enable/Disable – Enable to use. Disable to turn off.
Title – This controls the title for the payment method the customer sees during checkout.
Description – Payment method description that the customer will see on your checkout.
Receiver Phone Number – Payments are sent to this number.
Instructions – Explain how to make payment using Bit Payment.
Custom email subject for bit payment orders.
Custom email heading  for bit payment orders.


== Changelog ==

= 2.0 =
* WP tested upto 6.0.1
* WC Compatibility added 6.7.0

= 1.9.6 =
* WP tested upto 5.7

= 1.9.4 =
* Updated text domain.

= 1.9.3 =
* WC Compatibility added 4.1
* Updated bit icon image on checkout page
* added bit icon on Recived Email/order page
* Change text option label in setting 

= 1.9.2 =
* WC Compatibility added 4.0
* WP tested upto 5.4

= 1.9.1 =
* WC Compatibility added.

= 1.9 =
* Fixed issue of Conflict with WC reports.

= 1.8 =
* Fixed warning in Bit Payment email
* Fixed issue with email template so user can overwrite email template in theme

= 1.7 =
* PHP warning log fixed.

= 1.6 =
* Bugs Fixed.

= 1.5 =
* fixed error log
* payment_method was called incorrectly. Order properties should not be accessed directly.

= 1.4 =
* WC Compatibility added
* WordPress Compatibility added upto 5.2

= 1.3 =
* WC Compatibility added
* WordPress Compatibility added upto 5.1.1
* Update plugin name

= 1.2 =
* Display bit Payments in reports and add option for disable it in payment setting page.

= 1.2 =
* Display bit Payments in reports and add option for disable it in payment setting page.

= 1.1 =
* Fix Bugs.

= 1.0 =
* Initial version.