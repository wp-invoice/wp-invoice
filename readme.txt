=== WP-Invoice - Web Invoice and Billing ===
Contributors: usability_dynamics, Anton Korotkoff, andypotanin, jbrw1984, maxim.peshkov, ideric, MariaKravchenko, smoot328
Donate link: https://www.usabilitydynamics.com/product/wp-invoice
Tags: wp-invoice, web invoice, bill, paypal, invoice, pay, online payment, send invoice, bill clients, authorize.net, credit cards, recurring billing, ARB, stripe, paypal, interkassa, 2checkout, merchantplus, mijireh checkout
Requires at least: 4.0
Tested up to: 4.9.4
Stable tag: 4.1.10
License: GPLv2 or later
WP-Invoice lets you create and send web-invoices and setup recurring billing for your clients.

== Description ==

WP-Invoice 4.0 is the most popular and flexible plugin for WordPress that allows you to make your site accept payments. Complete e-commerce solutions out of the box. Flexible and extendable.

http://vimeo.com/27887971

WP-Invoice lets WordPress blog owners send itemized invoices to their clients. Ideal for web developers, SEO consultants, general contractors, or anyone with a WordPress blog and clients to bill. The plugin ties into WP's user management database to keep track of your clients and their information.

Once an invoice is created from the WP admin section, an email with a brief description and a unique link is sent to client. Clients follow the link to your blog's special invoice page, view their invoice, and pay their bill using one of the available payment system. The control panel is very user-friendly and intuitive.

Credit card payments may be accepted via Authorize.net, PayPal, Interkassa (Eastern Europe), Stripe and 2Checkout. Subscriptions (Recurring Billing) implemented using Authorize.net ARB, PayPal Subscriptions, Stripe Subscriptions and 2Checkout Recurring Billing. More gateways available as Add-ons.

> WP-Invoice on [GitHub](https://github.com/wp-invoice/wp-invoice)

= Features =

* Free [Add-ons](https://www.usabilitydynamics.com/products#category-wp-invoice) available.
* WP-CRM Integration.
* Brand new user interface for much improved invoicing filtering and searching.
* Partial Payments with minimum amount.
* Receipt Page with Invoice Log.
* Per-defined Line Items.
* Discount Line Items.
* Custom Payment Entry, and administrative adjustments.
* Customizable Invoice and Receipt Templates.
* Create invoices from the WordPress control panel.
* Pre-fill customer information using the WordPress user list.
* Send invoice notifications to customers with a secured link back to the web-invoice.
* Setup recurring billing using Authorize.net's ARB (Automatic Recurring Billing) feature.
* Force web-invoice pages to be viewed in SSL mode.
* Archive old invoices.
* Easily use old invoices as templates for new ones.
* Dynamic and intuitive user interface.
* Insert an "Invoice Lookup" form using PHP or WordPress shortcode anywhere.
* Create users directly from WP-Invoice.
* Customize billing settings per invoice.
* Customize invoice notification email per invoice.
* Invoice notification, reminder and receipt templates.
* Export/Import Invoices (Power Tools Add-on).
* Sales Visualization (Power Tools Add-on).

= Payment Options =

* Authorize.net
* PayPal
* Interkassa (Eastern Europe)
* Stripe
* 2Checkout
* PayPal Payments Pro (Add-on)
* USAePay (Add-on)
* Mijireh Checkout (Add-on)

= Widgets =

* **Invoice Lookup** widget allows you to add a simple invoice search form to a front-end. It will be accessible by your clients. Form accepts Invoice ID. Invoice page appears once correct Invoice ID submitted.
* **Invoice History** widget allows your clients to see a list of their invoices on a front-end.

= Shortcodes =

Shortcodes are replicating widgets doing the same.

* [wp-invoice-lookup]
* [wp-invoice-history]

= Available Add-ons list =
If you found the default functionality of WP-Invoice is not enough for your needs please take a look at the list of available [Add ons](https://www.usabilitydynamics.com/products#category-wp-invoice)

= Single Page Checkout =
The Single Page Checkout (SPC) Add-on for WP-Invoice makes it easy to create one-page-checkout forms that can accept a variety of different payment gateways, such as Authorize.net and PayPal.
[More about Add-on](https://www.usabilitydynamics.com/product/wp-invoice-single-page-checkout)

= PDF Invoices and Receipts =
PDF feature allows you to easily generate PDF versions of your invoices, receipts and quotes. A variety of settings makes it easy to configure PDF appearance.
[More about Add-on](https://www.usabilitydynamics.com/product/wp-invoice-pdf)

= Quotes =
The Quotes feature let's you automate your workflow by creating quotes and letting your clients ask questions regarding quotes directly on your website. Once a quote is approved, it is converted to an invoice with a single click.
[More about Add-on](https://www.usabilitydynamics.com/product/wp-invoice-quotes)

= Power Tools =
This Add-on allows you to export your invoices in the XML and JSON formats and import data from other WP-Invoice installations. Furthermore, it provides a graphic visualization of your sales, filtered by day, week or month.
[More about Add-on](https://www.usabilitydynamics.com/product/wp-invoice-power-tools)

== Installation ==

1. Upload all the files into your wp-content/plugins directory, be sure to put them into a folder called "wp-invoice"
2. Activate the plugin at the plugin administration page
3. Follow set-up steps on main Invoice page
4. To create your first invoice navigate to Invoice -> New Invoice, and select the user who will be the recipient.

Please see the [wp-invoice plugin home page](https://www.usabilitydynamics.com/product/wp-invoice) for details.

== Frequently Asked Questions ==

[WP-Invoice FAQ](https://www.usabilitydynamics.com/product/wp-invoice/docs/home)

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

= Version 4.1.1 =
* Important Security Update.

= Version 4.0.0 =
* Refactoring

= Version 3.09.5 =
* WordPress 4.0 compatibility.

= Version 3.09.4 =
* New 2Checkout Gateway has been added.

= Version 3.08.9 =
* New STRIPE Gateway has been added.

= Version 3.08.7 =
* Fixed critical issue with the way invoices appear on the invoice page.

= Version 3.08.6 =
* Fixed conflicts with Simple Facebook Connect and SEO by Yoast plugins.

= Version 3.08.4 =
* Strongly recommended upgrade.
* Fixes blank settings page.

= Version 3.08.3 =
* Strongly recommended upgrade.

= Version 3.06.1 =
* Critical bug fixes.

= Version 3.06.0 =
* New features.

== Change Log ==

= 4.1.10 =
* Fixed calculation of invoice total paid amount.
* Fixed unnecessary SQL query on every page.
* Added Feedback form to plugin settings.

= 4.1.9 =
* Improved JavaScript library enqueues to follow common conventions.
* Updated jquery.maskedinput.js to newer version to fix a JavaScript bug.
* Moved vendor JavaScript files into scripts/src/vendor.
* Bundled Angular and jQuery libraries that were loading remotely into scripts/src/vendor.
* Added object caching to common invoice lookups to reduce number of MySQL queries.
* Removing extra and write-heavy methods.
* Added sorting option for line items.
* Added new feature of Future Publishing.
* Added actions and filters for developers.
* Added/Fixed compatibility with other plugins and add-ons.

= 4.1.8 =
* Fixed PayPal payment experience.
* Added reCaptcha support with WP-CRM integration.
* Added new filters and actions (for developers).
* Fixed tax field on edit invoice page.

= 4.1.7 =
* Fixed edit profile page.

= 4.1.6 =
* Client Dashboard enhancements.
* PayPal IPN handler fixes.
* Added ability to create invoice from WP-CRM user profile.
* 2Checkout Gateway fixes.
* Custom invoice fields fixes.
* Code cleanup.
* Compatibility fixes.

= 4.1.5 =
* Fixed deprecated code.
* Enhanced logo management process.
* Fixed contextual help.
* Fixed Authorize.net ARB handling.

= 4.1.4 =
* Fixed WordPress 4.7 compatibility issue.

= 4.1.3 =
* Improved Stripe error notifications.
* Fixed PayPal IPN issue.
* Disabled the ability to select empty decimal separator.
* Code cleanup.

= 4.1.2 =
* Fixed ability to customize client dashboard.
* Added new option for decimal separator symbol.
* Fixed invoice search by custom IDs.
* Added ability to require terms acceptance on regular invoices.
* Localisation fixes.
* Usability fixes.

= 4.1.1 =
* Fixed several possible security issues.
* Fixed minor issue in Stripe Gateway.
* Fixed Reports Page issue.

= 4.1.0 =
* Fixed XMLRPC method for creating new invoice.
* Added italian localization.
* Fixed data passed to Authorize.net during payment.
* Fixed Country field inconsistency.
* Fixed Stripe to consider new API version.
* Changed way of setting business logo to use Media Library.
* Added new way of displaying an invoice - Unified Invoice Page.
* Added new feature of Client Dashboard.
* Updated libraries.
* Updated localization.

= 4.0.2 =
* Added more actions and filters.
* Fixed warnings and notices.
* Fixed typo.
* Fixed RU localization.

= 4.0.1 =
* Fixed loading of localisation files. The bug persists in 4.0.0 version.
* Fixed incorrect behaviour on custom 'Install Plugins' page after depended plugins ( Add-ons ) activation.
* Fixed the way of widgets initialization. Compatibility with WordPress 4.3 and higher.
* Fixed Warnings which were breaking ajax request on pagination and filtering items on All Properties page for PHP 5.6.

= 4.0.0 =
* Changed plugin initialization functionality.
* Added Composer ( dependency manager ) modules and moved some functionality to composer modules ( vendors ).
* Added doing WP-Invoice Settings backup on upgrade to new version. Get information about backup: get_option('wpi_options_backup');
* Moved premium features to separate plugins.
* Cleaned up functionality of plugin.
* Refactored file structure of plugin.
* Refactored 'View All' page.
* Fixed Warnings and Notices.

= Version 3.09.5 =
* WordPress 4.0 compatible.
* General code improvements.
* Fixed a lot of warnings and notices.
* Fixed Stripe error handler.
* Fixes to History and Lookup widgets.

= Version 3.09.4 =
* Updated libraries.
* Google Wallet deprecated and removed.
* Fixed issue with Stripe keys having spaces around.
* Fixed a lot of Warnings/Strict Standards.
* Added option to allow partial payments by default.
* Fixes to Invoice History shortcode.
* Fixed JavaScript jQuery deprecated code (live to on).
* Added 2Checkout payment gateway.
* Fixed WP-CRM integration.
* Added fix that should prevent mod_security issue from appearing.

= Version 3.09.3.1 =
* Updated libraries.

= Version 3.09.3 =
* Fixed critical issues.
* Fixed Stripe gateway's conflict.
* Fixed fatal error on sending notifications.
* Updated German (DE) localization.
* Updated Russian (RU) localization.

= Version 3.09.2 =
* Fixed simple style issues.
* Fixed JavaScript issues.
* Added new option which allows to set whether or not to send passwords to new users created by the plugin.
* Added new option which controls compatibility mode state. May help if you have problems with invoice appearence.
* Fixes to Premium Features connector.
* Added ability to re-order Line Items.
* Updated InterKassa Gateway to the new protocol.
* Updated localization files.

= Version 3.09.1 =
* Added API for managing Dashboard Widgets.
* Added new option which allows to set whether guests can see invoice details.
* Updated localization files.
* Fixes to display invoice page.
* Fixes to CRM connection (Notifications).
* Fixed invoice paid time information.
* Added new parameter 'allow_types' to the [wp-invoice-history] shortcode (value should the CSV of types to show).
* Added the ability to send PayPal IPN URL with payment request.
* Updated PDF Invoice Premium Feature.
* Fixed simple style issues.

= Version 3.09.0 =
* Fixed multisite compatibility issue.
* Fixed Stripe Subscriptions issue.
* Added manual payment option for specific invoices.

= Version 3.08.9 =
* Added STRIPE Gateway for regular and recurring invoices.
* Added InterKassa Gateway for regular invoicing.
* Fixed User Lookup autocomplete inputs.
* Fixed jQuery UI issues.
* Removed unwanted Delegate JavaScript library that caused issues with autocomplete.
* Improved Gateways API.

= Version 3.08.8 =
* Improved Single Page Checkout feature.
* Fixed font issue in PDF feature.
* Added WP-Invoice XML-RPC API.

= Version 3.08.7 =
* Fixed critical issue with the way invoices appear on the invoice page. 'How to Insert Invoice' option works correctly now.
* Fixes for template functions.
* Updated PDF Premium Feature. Fonts added.

= Version 3.08.6 =
* Fixed issue with Invoice History widget.
* Fixed specific invoice page for some cases when it doesn't appear.
* Updated Single Page Checkout feature.
* Updated PDF Premium Feature.
* Fixed paid amount for invoices list.
* Fixed invoice time in order to GMT offset.
* Improved Premium Features Updater.
* Removed PayPal button URL option. Was not used.
* Fixed conflicts with Simple Facebook Connect and SEO by Yoast plugins.
* Fixed issues with MS instalations.
* Localization Updates.
* UI improvements.

= Version 3.08.5 =
* Fixed Premium Feature update issue.
* Fixed ampersand issue which caused trimming input data.
* Fixed Invoice Notification template. Replace %recurring% tag with %type% in notification templates.
* Fixed displaying of Discount description tag.

= Version 3.08.4 =
* Fixed critical bug with undefined function.
* Fixed Custom Invoice ID management.

= Version 3.08.3 =
* Fixed critical bugs with checkboxes on settings page.
* Fixed the ability to disable Premium Features.

= Version 3.08.2 =
* Wordpress 3.5 compatibility fixes.
* PHP 5.4 issues fixed.
* Fixes for PDF Feature.
* Global Tax can be non integer now.
* History Widget fixes. It is available to check invoice types to show.
* Fixes for Quotes Feature.
* Added ability to set full discount to make Balance to be 0.
* Localization files updated.
* Visual/cosmetic UI fixes.

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
