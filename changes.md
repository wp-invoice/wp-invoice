#### 4.2.2 ( August 7, 2018 )
* Fixed warnings related to PHP 7.*
* Added Support tab
* Updated FR localization

#### 4.2.1 ( August 7, 2018 )
* PHP 7.2 Compatibility fixes.

#### 4.2.0 ( June 4, 2018 )
* Improved Spanish and added French localization
* Fixed PHP 7.2 Warnings
* Fixed Stripe Gateway - recurring invoice processing issue related to API Upgrade

#### 4.1.10 ( February 9, 2018 )
* Fixed calculation of invoice total paid amount.
* Fixed unnecessary SQL query on every page.
* Added Feedback form to plugin settings.

#### 4.1.9 ( November 27, 2017 )
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

#### 4.1.8 ( September 5, 2017 )
* Fixed PayPal payment experience.
* Added reCaptcha support with WP-CRM integration.
* Added new filters and actions (for developers).
* Fixed tax field on edit invoice page.

#### 4.1.7 ( April 5, 2017 )
* Fixed edit profile page.

#### 4.1.6 ( April 4, 2017 )
* Client Dashboard enhancements.
* PayPal IPN handler fixes.
* Added ability to create invoice from WP-CRM user profile.
* 2Checkout Gateway fixes.
* Custom invoice fields fixes.
* Code cleanup.
* Compatibility fixes.

#### 4.1.5 ( January 6, 2017 )
* Fixed deprecated code.
* Enhanced logo management process.
* Fixed contextual help.
* Fixed Authorize.net ARB handling.

#### 4.1.4 ( December 8, 2016 )
* Fixed WordPress 4.7 compatibility issue.

#### 4.1.3 ( August 30, 2016 )
* Improved Stripe error notifications.
* Fixed PayPal IPN issue.
* Disabled the ability to select empty decimal separator.
* Code cleanup.

#### 4.1.2 ( March 29, 2016 )
* Fixed ability to customize client dashboard.
* Added new option for decimal separator symbol.
* Fixed invoice search by custom IDs.
* Added ability to require terms acceptance on regular invoices.
* Localisation fixes.
* Usability fixes.

#### 4.1.1 ( January 25, 2016 )
* Fixed several possible security issues.
* Fixed minor issue in Stripe Gateway.
* Fixed Reports Page issue.

#### 4.1.0 ( January 11, 2016 )
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

#### 4.0.2 ( September 1, 2015 )
* Added more actions and filters.
* Fixed warnings and notices.
* Fixed typo.
* Fixed RU localization.

#### 4.0.1 ( August 21, 2015 )
* Fixed loading of localisation files. The bug persists in 4.0.0 version.
* Fixed incorrect behaviour on custom 'Install Plugins' page after depended plugins ( Add-ons ) activation.
* Fixed the way of widgets initialization. Compatibility with WordPress 4.3 and higher.
* Fixed Warnings which were breaking ajax request on pagination and filtering items on All Properties page for PHP 5.6.

#### 4.0.0 ( August 3, 2015 )
* Changed plugin initialization functionality.
* Added Composer ( dependency manager ) modules and moved some functionality to composer modules ( vendors ).
* Added doing WP-Invoice Settings backup on upgrade to new version. Get information about backup: get_option('wpi_options_backup');
* Moved premium features to separate plugins.
* Cleaned up functionality of plugin.
* Refactored file structure of plugin.
* Refactored 'View All' page.
* Design fixes.
* Fixed conflict with WP-Property plugin.
* Fixed Fatal Error when accessing private Invoices.
* Fixed Warnings and Notices.