=== WP-Invoice - Web Invoice and Billing ===
Contributors: usability_dynamics, Anton Korotkoff, andypotanin, jbrw1984, maxim.peshkov, ideric, MariaKravchenko, smoot328
Donate link: https://www.usabilitydynamics.com/product/wp-invoice
Tags: wp-invoice, web invoice, bill, paypal, invoice, pay, online payment, send invoice, bill clients, authorize.net, credit cards, recurring billing, ARB, stripe, paypal, interkassa, 2checkout, merchantplus, mijireh checkout
Requires at least: 4.0
Tested up to: 4.9.8
Stable tag: 4.2.2
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
= 4.2.2 =
* Fixed warnings related to PHP 7.*
* Added Support tab
* Updated FR localization

= 4.2.1 =
* PHP 7.2 Compatibility fixes.

= 4.2.0 =
* Improved Spanish and added French localization
* Fixed PHP 7.2 Warnings
* Fixed Stripe Gateway - recurring invoice processing issue related to API Upgrade

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

= Earlier versions =
Please refer to the separate changelog.txt file.