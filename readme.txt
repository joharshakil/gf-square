=== Gravity Forms Square ===
Contributors: Wpexperts.io
Tags: gravity forms,gravity form,gravity,forms,form

Requires at least: 4.7
Tested up to: 5.4
Stable tag: 2.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

== Description ==

Gravity Form Square plugin is a WordPress plugin that allows users to pay from their gravity form using Square payment gateway. 
This will help you to  add Square payment option to your form created through Gravity Form plugin. So users will be able to pay  via Credit Card number 
and this payment will process through your Square account.

== Installation ==

= Minimum Requirements =

* PHP version 5.5 or greater (PHP 5.6 or greater is recommended)
* Requires Gravity Forms Plugin.
* MySQL version 5.4 or greater (MySQL 5.6 or greater is recommended)
* Some payment gateways require fsockopen support (for IPN access)
* WordPress 4.4+

== Changelog ==

= 1.0 =

* Intial Release.

= 1.0.2 =

Added - Send Form info ( Selected form fields ) option to the square order note.

= 1.0.3 =

Fixed - Error Headers already sent by on plugin file.

= 1.0.4 =

Added - Added compatibility with multi steps form.

= 1.0.5 =

Added - Added compatibility payment form with gf validation.
Added - addon's support.

= 1.0.7 =

Added - Added compatibility payment form with gf multi step forms.

= 1.0.8 =

Fixed - Multiple forms conflict on one page.

= 1.1 =

Update - Update freemius SDK.
Update - hook for freemius.
Update - Jquery form trigger event.

= 1.2 = 

Update - fix compatability with recurring add-on.

= 1.3 = 

Added - Support for shipping field.

= 1.4 = 

Added - Code compatability with addon's support.

= 1.5 = 

Updated Freemius SDK.

= 1.6 = 

Added - OAuth Button Added

= 1.6.1 = 

Updated Freemius SDK Version 2.3.0

= 1.6.2 = 

Fixed Square renew auth error.


= 1.6.3 =

* Added - Sandbox integration with Square v2 api.
* Added - SCA Integration.
* Fixed - OAuth refresh token fixed
* Fixed - Credit Card Fields Fixed
* OAuth - Unsupported type error before connect button fixed

= 1.6.4 =

* Fixed - Order total amount field fixed
* Fixed - Email $ issue fixed

= 1.6.5 =

* Added - Added payment details to Email Notifications
* Added - Added ability to add payment details only in specific emails by adding '{square_payment_details}' string in email
* Fixed - After OAuth Payment details Expire, it was showing fatal error in square settings page
* Updated Freemius SDK Version 2.3.1

= 1.7 =

* Fixed - oauth auto refresh token issue.
* Added - Email notice when oauth expired,renewed or failed.
* Added - Event Email notice check option.

= 1.7.1 =

* Fixed - notification method error.

= 1.7.3 =

* Added - Added payment details functionality in Resend Email Notification
* Fixed - Refresh Token Condition
* Fixed - Payment Amount must be integer on form submit.
* Fixed - Simple and recurring form not working when used on same page, and recurring form loading second.

= 2.0 =

* Added - Card on file feature
* Added - Recurring Payment
* Added - Log in option on top of form if user is not logged in
* Added - Test SCA on Sandbox
* Fixed - MultiForm payment support
* IMPROVEMENT - Frontend and Backend UI

= 2.1 =

* Fixed - Make compitable with old version 
* Fixed - Old Recurring payments will work
* IMPROVEMENT - Old recurring field will automatically replaced with new recurring field in form
* Added - Add notice when activating/updating to v2.1 "New version of GF Square (Premium) have both simple and recurring payment processing functionality. You can keep the other GF Square Recurring (Premium) (Premium) plugin deactivated."

= 2.2 =

* IMPROVEMENT - Code improvement
* IMPROVEMENT - Square SDK Updated to latest version

= 2.3 =

* IMPROVEMENT - Showing square related errors near square card field.

= 2.4 =

* ADDED - Each payment will create a new Order in square and linked with transaction.
* ADDED - Simple/Recurring Form will work together in same form.
* ADDED - Make translation compatible - tested with LOCO TRANSLATE
* ADDED - Option to enable/disable delete card on file.
* ADDED - Confirmation popup before deleting card.
* ADDED - Option to enable/disabled order creation in square. *Note: If "create order in square" option is enable, then you need to re-auth the app with square. 

= 2.5 =

* ADDED - Enable google pay E-wallet payment method.
* ADDED - Option to enable disable from form settings.
* FIXED - Card fields on mobile devices.

= 2.6 =
ADDED - Square Gift Card.
ADDED - Apple pay.
FIXED - Bug fixing.
ADDED - Support with Gravity coupon field.
ADDED - Dismiss notice saved on database.

= 2.6.5 =
ADDED - Support Fixes.
ADDED - Refund Payment For simple & recurring payments.
ADDED - Delay capture payment. Which you can handle from entries, whether to complete or cancel the payment.
ADDED - Affiliation support enabled
Fix - Tested With Latest Gravity Form 2.5

= 2.6.8 =
UPDATE - CreateCard API
UPDATE - Delete card Api
UPDATE - Location Endpoint
FIXED - Js File error (adding a safe script filter “gform_noconflict_scripts“)
FIXED - FormAlreadyBuiltError issue
FIXED - Fixed some jQuery bugs
