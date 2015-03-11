=== NDMS ePay Plus ===
Contributors: ndmscorp
Donate link: http://www.ndmscorp.com/
Tags: woocommerce, ecommerce, payment gateway
Requires at least: 3.5
Tested up to: 4.1.1
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Accept Credit Cards. Simply.™ with NDMS ePay Plus for WooCommerce

== Description ==

NDMS ePay Plus is a complete payment processing solution for merchants using WooCommerce to power their eCommerce Store. ePay Plus includes everything you need to accept credit and debit cards at checkout.

The customer enters their credit card details (Card Holder Name, Credit Card Number, Expiration Date, CVV) and NDMS ePay Plus handles authorization and settlement. The customer stays on your site for a seamless checkout experience.

== Installation ==

 * Unzip the files and upload the folder into your plugins folder (wp-content/plugins/) overwriting old versions if they exist
 * Activate the plugin in your WordPress admin area.
 * Open the settings page for WooCommerce and click the "Payment Gateways" tab
 * Click on the sub tab for "ePay+"
 * Configure your ePay+ express settings.  See below how to.


 Configuring ePay+ settings in the WooCommerce admin area

 * Enable/Disable - Enable or disable this gateway from being used on the site.
 * Title - This is the title that appears on the checkout page for this payment gateway.
 * Description - This setting controls the message that appears under the payment fields on the checkout page. Here you can list the types of cards you accept.
 * Transaction Description -
 * ePay+ Secure ID - ePay+ Secure ID is generated in the Merchant Console.  See below.
 * Payment Type - Which payment command to run: Sale authorizes and charges the card. Authorize Only verifies funds only.
 * Test Mode - If checked the transaction will be simulated by ePay+, but not actually processed.
 * Sandbox - If checked the sandbox server will be used.  This overrides the gateway URL parameter below.
 * Gateway URL Override - Override for URL of ePay+
 * CVV - If checked the CVV field is displayed to the customer and required.
 * Debugging - Receive emails containing the data sent to and from ePay+. Does not include credit card number.
 * Debugging Email - Email of recipient of debug emails.

 Press "Save changes" to apply your changes.


 NDMS ePay+ Secure ID

 * To use your ePay+ payment gateway for live transactions you will need your unique ePay+ Secure Id. 
 * The secure ID can be found in the NDMS Welcome email along with Login instructions for the Merchant Console.
 * If you don't have an NDMS ePay+ Account please visit our website http://epayplus.ndmscorp.com 
 * Or Call 424.247.5511 to speak to one of our team members direct.


== Frequently Asked Questions ==


== Screenshots ==


== Changelog ==

= 1.0.3 =
* Fixed images link

= 1.0.2 =
* Adding compatibility with WooCommerce Subscriptions

= 1.0.1 =
* Adjusting the PIN parameter

= 1.0.0 =
* First Version

== Upgrade Notice ==


