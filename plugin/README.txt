=== attachmentAV ===
Contributors: andreaswittig
Tags: malware,virus,upload,attachment,antivirus
Requires at least: 6.0
Tested up to: 6.5.2
Stable tag: 1.0.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Protect your blog from malware. Scan attachments for viruses, worms, and trojans by sending them to the attachmentAV API powered by Sophos.

== Description ==

The plugin protects your blog from malware like viruses, worms, and tronjans.

1. The user uploads an attachment.
2. The plugin sends the uploaded file to the attachmentAV API.
3. The attachmentAV API scans the file for malware by using the Sophos engine.
4. In case of an infected file, the plugin blocks the upload.

An API key and subscription for the 3rd party service [attachmentAV](https://attachmentav.com/solution/malware-protection-for-wordpress/) is required. In order to scan user uploads for malware the plugin sends the files to the API endpoint `https://eu.developer.attachmentav.com/v1/scan/sync/binary`.

== Installation ==

* Install attachmentAV either via the WordPress.org plugin repository or by uploading the files to your server. (See instructions on [how to install a WordPress plugin](https://www.wpbeginner.com/beginners-guide/step-by-step-guide-to-install-a-wordpress-plugin-for-beginners/))
* Activate attachmentAV.
* Open the attachmentAV plugin settings tab.
* Create a subscription for the attachmentAV API and enter the API key.

== Frequently Asked Questions ==

= Which file types are supported? =

attachmentAV scans all file types. The maximum file size is 10 MB.

== Screenshots ==

1. attachmentAV blocks upload of infected files.

== Changelog ==

= 1.0 =
* Initial Release