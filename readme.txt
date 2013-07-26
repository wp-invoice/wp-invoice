=== WP-Invoice - Web Invoice and Billing ===
Contributors: jbrw, mattalland, andypotanin
Donate link: http://twincitiestech.com/services/wp-invoice/
Tags: bill, paypal, invoice, pay, online payment, send invoice, bill clients, authorize.net, credit cards, recurring billing, ARB
Requires at least: 2.6
Tested up to: 2.8.0
Stable tag: trunk

WP-Invoice lets you create and send web-invoices and setup recurring billing for your clients.

== Description ==

**[Download the original WordPress Invoicing plugin now!](http://downloads.wordpress.org/plugin/wp-invoice.zip)**

WP-Invoice lets WordPress blog owners send itemized invoices to their clients. Ideal for web developers, SEO consultants, general contractors, or anyone with a WordPress blog and clients to bill. The plugin ties into WP's user management database to keep track of your clients and their information.

Once an invoice is created from the WP admin section, an email with a brief description and a unique link is sent to client. Clients follow the link to your blog's special invoice page, view their invoice, and pay their bill using PayPal. The control panel is very user-friendly and intuitive. 

Credit card payments may be accepted via Authorize.net, MerchantPlus' NaviGate, or PayPal account.  For recurring billing we have integrated Authorize.net's ARB API that will allow you to setup payment schedules along with invoices.


New Features:

* Custom tax label, states input, and PayPal button URL
* Insert an "Invoice Lookup" form using PHP or Wordpress Shortcode anywhere
* Create users directly from WP-Invoice
* Customize billing settings per invoice
* Customize invoice notification email per invoice
* Invoice notification, reminder and receipt templates
* Clients can select their form of payment between PayPal and Credit Card

Features:

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


== Installation ==

1. Upload all the files into your wp-content/plugins directory, be sure to put them into a folder called "wp-invoice"
2. Activate the plugin at the plugin administration page
3. Follow set-up steps on main Web Invoice page
4. To create your first invoice navigate to Web Invoice -> New Invoice, and select the user who will be the recipient.

Please see the [wp-invoice plugin home page](http://twincitiestech.com/services/wp-invoice/) for details. 

== Frequently Asked Questions ==

Please visit the [wp-invoice community page](http://wpinvoice.uservoice.com/) for suggestions and help.

== Screenshots ==

1. Invoice Overview 
1. New Invoice Creation 
1. Client Email Preview
1. Frontend Example


== Change Log ==
**Version 1.95**
* Fixed array error that occurs if a user with an invoice has been deleted.

**Version 1.94**
* Compatibility with WordPress 2.8.0
* Upgraded: jquery.calculation, jquery.field and jquery.form to latest available.  Replaced jquery.delegate to jquery.livequery.


**Version 1.93**

* Fixed jQuery conflict issues by isolating script loading to WP-Invoice Pages
* Added function to hide errors if using PHP4 to avoid html_entity_decode() errors in function.php
