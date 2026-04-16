=== attachmentAV - Virus Scan & Malware Protection for form plugins like Contact Form 7, WPForms, Gravity Forms ===
Contributors: andreaswittig,michaelwittig
Tags: virus scan, malware protection, wpforms, contact form 7, gravity forms
Requires at least: 6.0
Tested up to: 6.9.1
Stable tag: 1.8.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Virus scan and malware protection for Contact Form 7, WPForms, Gravity Forms, Formidable Forms, Iptanus File Upload, and more. Powered by Sophos.

== Description ==

Protect your WordPress site from malware by scanning all file uploads for viruses, worms, and trojans. Powered by the Sophos engine, attachmentAV scans every file before it reaches your server — blocking infected uploads in real time.

= Virus Scan & Malware Protection for Form Plugins =

attachmentAV integrates with the most popular WordPress form and file upload plugins:

* [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) — virus scan and malware protection for Contact Form 7 file uploads
* [WPForms](https://wordpress.org/plugins/wpforms-lite/) — virus scan and malware protection for WPForms file upload fields
* [Gravity Forms](https://gravity.com) — virus scan and malware protection for Gravity Forms file upload fields
* [Formidable Forms](https://wordpress.org/plugins/formidable/) — virus scan and malware protection for Formidable Forms file uploads
* [Drag and Drop Multiple File Upload for Contact Form 7](https://wordpress.org/plugins/drag-and-drop-multiple-file-upload-contact-form-7/) — virus scan and malware protection for drag-and-drop file uploads
* [Iptanus File Upload](https://wordpress.org/plugins/wp-file-upload/) — virus scan and malware protection for Iptanus File Upload
* [Media Library](https://wordpress.com/support/media/) — virus scan and malware protection for core WordPress media uploads

= How It Works =

1. A user uploads a file through a form (see supported plugins).
2. The attachmentAV plugin sends the file to the attachmentAV API.
3. The attachmentAV API scans the file for malware using the Sophos engine.
4. Infected files are blocked and an error is shown to the user.

An API key and subscription for the 3rd party service [attachmentAV](https://attachmentav.com/solution/malware-protection-for-wordpress/) are required. To scan user uploads for malware, the plugin sends the files to the API endpoint `https://eu.developer.attachmentav.com/v1/scan/sync/binary`.

Would you like to see attachmentAV in action? Check out the [demo video](https://youtu.be/gK3Py4tiuHQ).

== Installation ==

* Install attachmentAV either via the WordPress.org plugin repository or by uploading the files to your server. (See instructions on [how to install a WordPress plugin](https://www.wpbeginner.com/beginners-guide/step-by-step-guide-to-install-a-wordpress-plugin-for-beginners/))
* Activate attachmentAV.
* Open the attachmentAV plugin settings tab.
* Create a subscription for the attachmentAV API and enter the API key.

Go to [attachmentAV for WordPress Setup Guide](https://attachmentav.com/help/setup-guide/wordpress.html) for more detailed setup instructions.

== Frequently Asked Questions ==

= Which file types are supported? =

attachmentAV scans all file types.

= What's the maximum supported file size? =

The maximum file size is 10 MB.

= Which upload methods are covered? =

attachmentAV scans all files uploaded via:

* The plugin [Contact Form 7](https://wordpress.org/plugins/contact-form-7/)
* The plugin [WPForms](https://wordpress.org/plugins/wpforms-lite/)
* The plugin [Gravity Forms](https://gravity.com)
* The plugin [Formidable Forms](https://wordpress.org/plugins/formidable/)
* The plugin [Drag and Drop Multiple File Upload for Contact Form 7](https://wordpress.org/plugins/drag-and-drop-multiple-file-upload-contact-form-7/)
* The plugin [Iptanus File Upload](https://wordpress.org/plugins/wp-file-upload/)
* The core [Media Library](https://wordpress.com/support/media/)

== Screenshots ==

1. Block uploads of infected files via form plugins.
2. Configure the attachmentAV plugin to your needs.

== Changelog ==

= 1.8.0 =
* Adding support for Gravity Forms.

= 1.7.1 =
* Bug fixes

= 1.7.0 =
* Show API key usage information
* Support Wordpress 6.9

= 1.6.0 =
* Minor improvements

= 1.5.2 =
* Bug fixes

= 1.5.1 =
* Bug fixes

= 1.5.0 =
* Support for plugin Drag and Drop Multiple File Upload for Contact Form 7 added

= 1.4.0 =
* Support for plugin Contact Form 7 added

= 1.3.0 =
* Support Wordpress 6.8

= 1.2.1 =
* Bug fixes

= 1.2.0 =
* Support for plugin Formidable Forms added

= 1.1.2 =
* Bug fixes

= 1.1.1 =
* Bug fixes

= 1.1.0 =
* Support for plugin WPForms added
* Support for plugin Iptanus File Upload added

= 1.0.5 =
* Adding scan results to metadata

= 1.0.4 =
* Bug fixes

= 1.0.3 =
* Bug fixes

= 1.0.2 =
* Bug fixes

= 1.0.1 =
* Bug fixes

= 1.0 =
* Initial Release
