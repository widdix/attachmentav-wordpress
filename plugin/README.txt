=== attachmentAV - Antivirus for WordPress ===
Contributors: andreaswittig,michaelwittig
Tags: malware,virus,antivirus,malware scanner,security
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 1.7
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Protect your blog from malware. Scan attachments for viruses, worms, and trojans by sending them to the attachmentAV API powered by Sophos.

== Description ==

The plugin protects your blog from malware like viruses, worms, and trojans.

1. The user uploads an attachment.
2. The plugin sends the uploaded file to the attachmentAV API.
3. The attachmentAV API scans the file for malware by using the Sophos engine.
4. In the case of an infected file, the plugin blocks the upload.

Also works with the popular file upload plugins [WPForms](https://wordpress.org/plugins/wpforms-lite/), [Formidable Forms](https://wordpress.org/plugins/formidable/), [WordPress File Upload](https://wordpress.org/plugins/wp-file-upload/), [Contact Form 7](https://wordpress.org/plugins/contact-form-7/), and [Drag and Drop Multiple File Upload for Contact Form 7](https://wordpress.org/plugins/drag-and-drop-multiple-file-upload-contact-form-7/).

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

* The core [Media Library](https://wordpress.com/support/media/)
* The plugin [WPForms](https://wordpress.org/plugins/wpforms-lite/)
* The plugin [Formidable Forms](https://wordpress.org/plugins/formidable/)
* The plugin [WordPress File Upload](https://wordpress.org/plugins/wp-file-upload/)
* The plugin [Contact Form 7](https://wordpress.org/plugins/contact-form-7/)
* The plugin [Drag and Drop Multiple File Upload for Contact Form 7](https://wordpress.org/plugins/drag-and-drop-multiple-file-upload-contact-form-7/)

== Screenshots ==

1. attachmentAV blocks uploads of infected files.
2. attachmentAV scan status shown for each media file.

== Changelog ==

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
* Support for plugin WordPress File Upload added

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
