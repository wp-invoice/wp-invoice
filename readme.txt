=== WP-Invoice - Web Invoice and Billing ===
Contributors: usability_dynamics, Anton Korotkoff, andypotanin, jbrw
Donate link: https://usabilitydynamics.com/products/wp-invoice/
Tags: web invoice, bill, paypal, invoice, pay, online payment, send invoice, bill clients, authorize.net, credit cards, recurring billing, ARB
Requires at least: 3.1
Tested up to: 3.5
Stable tag: 3.08.1
WP-Invoice lets you create and send web-invoices and setup recurring billing for your clients.

== Description == 

This is the original WordPress invoicing and billing plugin - completely rewritten and re-released.

http://vimeo.com/27887971

**[Download the original WordPress Invoicing plugin now!](http://downloads.wordpress.org/plugin/wp-invoice.zip)**

WP-Invoice lets WordPress blog owners send itemized invoices to their clients. Ideal for web developers, SEO consultants, general contractors, or anyone with a WordPress blog and clients to bill. The plugin ties into WP's user management database to keep track of your clients and their information.

Once an invoice is created from the WP admin section, an email with a brief description and a unique link is sent to client. Clients follow the link to your blog's special invoice page, view their invoice, and pay their bill using PayPal. The control panel is very user-friendly and intuitive.

Credit card payments may be accepted via Authorize.net, MerchantPlus' NaviGate, PayPal or Google Checkout account. For recurring billing we have integrated Authorize.net's ARB API that will allow you to setup payment schedules along with invoices. Subscriptions implemented using PayPal Subscriptions and Google Checkout Subscriptions.

= New Features =

* Automatically import invoices from Web Invoice plugin.
* WP-CRM Integration
* Brand new user interface for much improved invoicing filtering and searching
* Minimum and Split / Partial Payments
* Invoices can be reassigned to a different recipient
* Receipt Page with Invoice Log
* Per-defined Line Items
* Discount Line Items
* Custom Payment Entry, and administrative adjustments
* Customizable Invoice and Receipt Templates

= More Features =

* Create invoices from the WordPress control panel
* Prefill customer information using the WordPress user list
* Send invoice notifications to customers with a secured link back to the web-invoice
* Accept credit card payment via Authorize.net or MerchantPlus NaviGate
* PayPal available if you don't have a credit card processing account
* Setup recurring billing using Authorize.net's ARB (Automatic Recurring Billing) feature
* Force web-invoice pages to be viewed in SSL mode
* Archive old invoices
* Easily use old invoices as templates for new ones
* Dynamic and intuitive user interface
* Custom tax label, states input, and PayPal button URL
* Insert an "Invoice Lookup" form using PHP or WordPress Shortcode anywhere
* Create users directly from WP-Invoice
* Customize billing settings per invoice
* Customize invoice notification email per invoice
* Invoice notification, reminder and receipt templates

== Installation ==

1. Upload all the files into your wp-content/plugins directory, be sure to put them into a folder called "wp-invoice"
2. Activate the plugin at the plugin administration page
3. Follow set-up steps on main Invoice page
4. To create your first invoice navigate to Invoice -> New Invoice, and select the user who will be the recipient.

Please see the [wp-invoice plugin home page](https://usabilitydynamics.com/products/wp-invoice/) for details.

== Frequently Asked Questions ==

Please visit the [wp-invoice community page](https://usabilitydynamics.com/products/wp-invoice/forums/) for suggestions and help.

== Screenshots ==

1. Add can add custom payments and charges
2. Insert pre-defined line items and discounts
3. Quickly view invoice status and progression
4. Easily filter and find invoices
5. Show invoice receipts and invoice history
6. Create notification e-mail templates
7. Create pre-defined line items
8. View 3 standard reports - collected vs uncollected invoices, 10 most valuable clients and top grossing line items

== Upgrade Notice ==

= Version 3.06.1 =
* Critical bug fixes.

= Version 3.06.0 =
* New features.

= Version 3.04.3 =
* Total improvements.

= Version 3.04.1 =
* Critical bugs fixed.

= Version 3.04.0 =
* Core plugin filename changed, plugin may require manual re-activation after upgrade.

= Version 3.01 =
* Major fixes.

= Version 3.00 =
* Complete rewrite. Old invoice data is not deleted, please notify us if you have any upgrading issues.

== Change Log ==

= Version 3.08.1 =
* Added WordPress 3.5 compatibility.
* TCPDF bug fixed for PDF Feature.
* Fixed conflict with file names which have 'cookie' substring.
* Localization files updated.
* Visual/cosmetic UI fixes.

= Version 3.08.0 =
* Added Export/Import Invoices.
* Added Internal refunds.
* Added WP-Property plugin's FEPS integration.
* Added new Google Checkout payment method.
* Added the ability to force manual payment by unchecking all methods.
* Fixed 'execution time limit' issue in high-load systems.
* User search UI improved.
* Contextual Help updates.
* Localization files updated.
* Visual/cosmetic UI fixes.

= Version 3.07.0 =
* Added Wordpress 3.4-RC1 compatibility.
* Added (modified) shortcodes [wp-invoice-history] and [wp-invoice-lookup].
* Added ability to chose thousands separator symbol.
* Added ability to change "From" field in WP-Invoice e-mails.
* Added JS validation on WP-Invoice Settings page.
* Added protection of user invoices from changing emails.
* Added prevention of wpi_hourly_event and wpi_update from being sheduled twice.
* Added a 0 (zero) value to Visualize sales if there were no sales during specific period.
* Added Merchant's Information and invoice items information to Google Analytics Tracking function.
* PDF Feature: Added PNG to JPEG conversion for WPI PDF because TCPDF fails when logo image is a transparent PNG.
* PDF Feature: Fixed issues with PDF Output.
* PDF Feature: Fixed URL displaying if WordPress is set up not in server root.
* Fixed jQuery UI scripts and styles adding.
* Fixed Invoice Lookup widget: Now it available only for Logged In users. For non-admin users are allows to lookup only their own invoices.
* Fixed New invoice Email autocomplete function.
* Fixed problems on plugin's activation if plugin's dir-name is different from wp-invoice e.g wp-invoice-new.
* Fixed Invoice partial payments in case when amount is very high.
* WP-CRM integration: Changed notification slug to wpi_notification and added notification label "WP-Invoice Notification" to slug wpi_notification.
* Contextual Help updates.
* Visual/cosmetic UI fixes.
* Other improvements and fixes.

= Version 3.06.1 =
* Fixed blank installation currency issue.
* Fixed array_key_exists issue.
* Currency settings moved from "toggle advanced payment options" on "editing invoice" page.
* Other simple fixes.

= Version 3.06.0 =
* New Contextual Help. It is now fully compatible with WordPress 3.3+. Each information block has it's own tab.
* Notifications with WP-CRM. Now you have the ability to manage WP-Invoice notification templates with WP-CRM.
* Added the ability to show the Due Date on the invoice page.
* Added the ability to manage currencies. You can add any currency you want to use, or delete unwanted currencies.
* Single Page Checkout improvements.
* Added the ability to Visualize Sales. New sales graph according to the filter displayed on the invoice list page.
* New feature for tracking Google Analytics events added.
* Other small improvements in functionality and UI.
* Updated ability to backup and restore your WP-Invoice configuration.

= Version 3.05.0 =
* PayPal Subscriptions integrations added.
* Fixed slash issue on premium features page.
* Simple fixes to Authorize.net recurring billing.
* Fix to Single Page Checkout SSL option.
* Fixed PHP Warning on settings page.
* Fixes to PDF logo function.
* Fix to ampersand in line item description.

= Version 3.04.7.2 =
* Fix to encoding of unsupported characters for non-UTF databases.

= Version 3.04.7.1 =
* Urgent fix to settings system.
* Fix to currency symbols with unsupported encoding.

= Version 3.04.7 =
* Ability to use PDF link tag in notification templates if 'PDF Invoices and Receipts' Premium Feature installed.
* Major fix for PDF library. Fixed Fatal Error if WP-Invoice and WP-Property are installed on the same site.
* Major fix for updating settings functions.
* Fix for currency signs.
* Other simple internal improvements.

= Version 3.04.6 =
* Fixes for 'PDF Invoices and Receipts' Premium Feature.
* Typo 'County' fixed.
* Fixed currency symbol on Edit Invoice page.

= Version 3.04.5 =
* Option 'Automatically increment the invoice's custom ID by one.' fixed.
* 'First Time Setup' page fixed.
* South African Rand currency added.
* Fixed error when username is empty on invoice page.
* Added proper signs for every currency.
* Fixes for 'PDF Invoices and Receipts' Premium Feature.
* Fixes for 'Single Page Checkout' Premium Feature.
* Other simple improvements and UI fixes.

= Version 3.04.4 =
* WordPress 3.3 compatibility.
* Reported bugs fixed.

= Version 3.04.3 =
* Fixed Reports calculation process with discount.
* UI fixes.
* Installing from scratch issues fixed.
* Default settings data improved.
* Other improvements

= Version 3.04.2 =
* Fixed Reports calculation process.
* Fixed critical bug with float value of paid amount.
* Fixed bug from 3.04.0 with updating table structure.
* Fixed bug when partial payment is allowed and it's amount is less than balance.
* Settings UI improvements.

= Version 3.04.1 =
* Critical bug with unknown column 'blog_id' fixed.
* Bug with disappeared recepient name fixed.

= Version 3.04.0 =
* WordPress Multi Site (MS) compatibility.
* WP-CRM integration improvements.
* Fix to allow IPN URL to be changed.
* General improvements to Settings Page and Invoice Editor UI.

= Version 3.03.0 =
* Automatic import of Web Invoice plugin invoices.
* Invoice logs display the user that created the invoice.
* Dynamic column toggling improvements for the overview page.
* Improvements to invoice editing page for better UI.
* Fix to incorrect invoice total calculation when using a combination of discounts and taxes.
* Added option to send invoice payment notification to invoice creator, as well as site admin.
* Improved time formatting.
* Added option to set global tax.
* Fix to negative balance if price quantity is negative.
* Fix to tax amount not being displayed in line items on overview page.
* Fix to discount field not working if discount name is blank.
* Fix to discount field only allowing integers.
* EOL fixes that were causing parse errors on some hosts.
* WP-CRM integration.

= Version 3.02 =
* Minor fixes.

= Version 3.01 =
* Invoice currency settings fixed.
* Bug with loading of custom templates fixed.
* Added an ability to use manual payment method if there is no any payment venue accepted.
* Fixed bug with PayPal IPN which logged wrong amount.
* Fixed bug with email notifications.
* Default template layout and design improvements.
* Other simple fixes and improvements.

= Version 3.00 =
* Complete rewrite and re-launch.
* Over 130 tasks completed.

= Version 2.039 =
* Fixed bug custom PayPal button graphic not being saved
* Fixed bug with payment selection dropdown appearing when not supposed to
* Chnged update_option to add_option for invoice templates on install
* Chnged Tabs to use jQuery UI Tabs
* Improved UI on invoice page
* Added feature to exclude certain IP addresses from invoice logs
* Added shortcode [wp-invoice-list] to display logged in user's due and paid invoices
* Added function check for premium features
* Added message that displays when JavaScript is broken
* Added code to prevent JavaScript conflict caused by the Hover plugin

= Version 2.038 =
* Added option to add custom zip code label
* Added button to delete itemized lines

= Version 2.037 =
* Fixed a bug that prevented the new user form not displaying properly

= Version 2.02 =
* Fixed error with creating a template based invoice and new user at the same time
* Added checkbox to send new user emails when creating a new user from an invoice
* Added new invoice class

= Version 2.00 =
* Updated UI on overview page and on invoice management page.
* Added a "date sent" column to overview page
* Added two widgets for viewing invoice history and invoice lookup
* Added feature to disable recurring billing

= Version 1.95 =
* Fixed array error that occurs if a user with an invoice has been deleted.

= Version 1.94 =
* Compatibility with WordPress 2.8.0
* Upgraded: jquery.calculation, jquery.field and jquery.form to latest available.  Replaced jquery.delegate to jquery.livequery.

= Version 1.93 =
* Fixed jQuery conflict issues by isolating script loading to WP-Invoice Pages
* Added function to hide errors if using PHP4 to avoid html_entity_decode() errors in function.php
