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