=== Up2pay e-Transactions WooCommerce Payment Gateway ===
Contributors: Up2pay e-Transactions
Donate link: none
Tags: Payment Gateway, Orders, woocommerce, e-commerce, payment, E-Transactions
Requires at least: 5.0.0
Tested up to: 6.6
Stable tag: 3.0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
WC requires at least: 4.0
WC tested up to: 9.1.2

This plugin is a Up2pay e-Transactions payment gateway for WooCommerce 4.x

== Description ==

This module adds a Up2pay e-Transactions Payment Gateway to your Installation of WooCommerce.

Up2pay e-Transactions is a Payment Services Provider in Europe, part of the Crédit Agricole Bank.

plugin actions in wordpress:

this plugin offers an admin panel from the order section to the settings of Woocommerce.
it adds payment information to the orders details and changes the status of orders (upon reception of an IPN, see below.) and adds payment means on the checkout page.

This plugin takes information from the order and creates a form containing the details of the payment to be made, including parameters configured in the admin panel of the module that identify the mechant.

The plugin checks for availability of the Up2pay e-Transactions platform, through a call to our servers.
It then submits with javascript the form to the first available server.

the customer is then presented with a payment page, hosted on the Up2pay e-Transactions Platform (urls above).

The Up2pay e-Transactions Platform sends an Instant Payment Notification (IPN) to the server when the customer actually made the payment, indicating to the merchant the status of the payment.

the plugin generates a url that can catch the IPN call from Up2pay e-Transactions's server, filtering incoming calls to the Up2pay e-Transactions IP address.

if payment is successfull, then the plugin validates the order though woocommerce.

== Installation ==

1. Upload the entire folder `e-transactions-wc` to the `/wp-content/plugins/` directory
or through WordPress's plugin upload/install mecanism.

2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= What is the HMAC key in the admin panel for ? =

The HMAC key is generated on Up2pay e-Transactions server through your Up2pay e-Transactions back office. it is used to authenticate your calls to Up2pay e-Transactions Server. it is generated on the platform you choose: Production (live) or Pre-Production (test)

= My orders are not validated, even though the payment went through ? =

The oder paid with Up2pay e-Transactions is only validated upon rectpion of a positive Instant Payment Notification (IPN) this IPN is authenticated with the filter on the IP address, if the IP is somewhow changed, the plugin will give a 500 HTTP error.
Avoid Maintenance mode, or allow Up2pay e-Transactions IP to go through (194.2.122.190, 195.25.67.22). If the WordPress Installation is in maintenance mode, the Up2pay e-Transactions server will not be able to contact it.

= Something is not working for me, how can i get help ? =

Contact [Up2pay e-Transactions WordPress Support](mailto:support@e-transactions.fr "WordPress support at support@e-transactions.fr"), we will be glad to help you out !

== Screenshots ==

1. The administration panel: payment configuration
2. The administration panel: Up2pay e-Transactions Account parameters
3. The Checkout page: Payment method choice (1/ 3 times)
4. The Payment Means choice (hosted at Up2pay e-Transactions)
5. The Payment page
6. Once successfully processed, the Payment transaction details appear in the order details

== Changelog ==
= 3.0.5 =
- Better gestion of update process
- Fixed : Timeout on migration of old orders to "Processing" status (from "Capture" status)

= 3.0.4 =
- Fixed : Prevent multiples captures tentatives on differed payments
- Removed : "Capture" status, and migration of old orders to "Processing" status

= 3.0.3 =
- Fixed : V2 to V3 data migration, problem with the HMAC key
- Fixed : Restored the original HMAC key from V2 if the new one is empty or invalid

= 3.0.2 =
- Fixed : Better regex on PBX_BILLING
- Fixed : V2 to V3 data migration

= 3.0.1 =
- Fixed : Bloc Gutenberg for payment page (Shortcode)

= 3.0.0 =
- Added : New UX design for merchant
- Added : New features : demo mode - test mode - live mode - new contact process - userguide - check configuration - instalment integrated - dedicated page for up2pay transactions
- Added : Bloc Gutenberg for payment page
- Added : New payment mean Pluxee

= 2.0.4 =
- Phone field mandatory

= 2.0.3 =
- Fix broken links for picture

= 2.0.2 =
- Fixed:  missing directory

= 2.0.1 =
- Fixed: Sodexo name rebranding to Pluxee
- Fixed : format field PBX_CMD

= 2.0.0 =
- Deleted: IP declaration
- Deleted: Paylib payment mean withdrawn
- Fixed: Logo & rebranding
- Fixed: Code refactoring (class & request modernization, minification)
- Fixed: Cancel or back button on payment iframe
- Fixed: Appetiz name rebranding to Bimply
- Added: Refund
- Added: New logs

= 1.0.9 =
- Update IP list
- Fix IPN error in some specific cases

= 1.0.8 =
- Fix CountryCode value if empty
- Fix IPN for 3x CB

= 1.0.7 =
- Improve 3DSv2 XML
- Update compatibility
- Fix typo into FR translations

= 1.0.6 =
- Add 3DS exemptions

= 1.0.5 =
- Remove Paylib
- Improve debug & IPN HTTP status code

= 1.0.3 =
-updated compatibility levels declaration
-HMAC integration for API calls
-3DSv2 fields adjustments
-CountryCode fix (3 positions)
-no more IP modification possible
-filter on configuration parameters  (to avoid input error)
-fix cart persistance on some woocommerce versions

= 1.0.2 =
-updated compatibility levels declaration
-fixed compatibility with wordpress 5.8.1 (thx glouton)
-fixed test mode switching

= 1.0.1 =
-Up2pay branding & Adding many features to the plugin:
-iFrame integration
-payment mean choice on the woocommerce checkout
-one-click payments
-debit upon change of status (when shipping for instance)
-settings is now fully sappable between production and testing:
one can have 2 very different settings for both modes and review settings,
without changing the actual working mode for the website.

= 0.9.9.9.3 =
Force 3DSv2 for all cards

= 0.9.9.9.2 =
Add 3DSv2 support

= 0.9.8.9 =
various fixes and alignments

= 0.9.8.9 =
shortening long api names for plugin

= 0.9.8.8 =
fixing Woocommerce active detection mechanism

= 0.9.8.7 =
fixing mcrypt deprecation when goin with php > 7.1.x + translations and adding icon for payment means

= 0.9.8.6 =
Correcting transaction/call mixup and translation of minimal amount label.

= 0.9.8.5 =
adding HTTP/2 compatibility.

= 0.9.8.4 =
Correction for PHP7.2 mcrypt removal: openssl used if no mcrypt present.

= 0.9.8.3 =
Correction for potential HTTP 500 error: thx @.

= 0.9.8.2 =
Correction for network urls, order properties calling.

= 0.9.8.1 =
Correction of url called, to work for mobile.

== Changelog ==
= 0.9.8 =
Correction of minor bugs.

= 0.9.7.1 =
Correction of multisite wordpress bug.

= 0.9.7 =
Correction of a potential fatal error on error logging thx @vasyltech!.

Urls construct
= 0.9.6.9 =
Compatibility for folder-based wordpress mono-site.
Urls construct

= 0.9.6.8 =
Added compatibility for folder-based wordpress multi-site.
Removed IPN IP checking

= 0.9.6.7 =
Changed:
only rely on the $_SERVER data to check for the IP address:
this solves the non reception of the IPN  (error 500)

= 0.9.6.6 =
Second release:
Fixed:
-Missing table now created ok.
-"Syntax error: Unexpected token < " message when checking out,
-Use of deprecated functions to get pages url: now we use endpoints.

Added	:
-Informations about the payment on the order detail page, now actually displayed.
-3D Secure status properly rendered
-card numbers appear in the detail
-three time payment IPN properly stored


= 0.9.6.5 =
First stable release



== Upgrade Notice ==

= 1.0 =
This is the first major Release.

