<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://attachmentav.com
 * @since             1.0.0
 * @package           Attachmentav
 *
 * @wordpress-plugin
 * Plugin Name:       attachmentAV
 * Plugin URI:        https://attachmentav.com/solution/malware-protection-for-wordpress/
 * Description:       Protect your blog from malware. Scan attachments for viruses, worms, and trojans by sending them to the attachmentAV API powered by Sophos. To get started, please go to your <a href="/wp-admin/admin.php?page=attachmentav">attachmentAV Settings page</a> to set up your API key.
 * Version:           1.0.0
 * Author:            widdix GmbH
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       attachmentav
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ATTACHMENTAV_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-attachmentav-activator.php
 */
function attachmentav_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-attachmentav-activator.php';
	Attachmentav_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-attachmentav-deactivator.php
 */
function attachmentav_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-attachmentav-deactivator.php';
	Attachmentav_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'attachmentav_activate' );
register_deactivation_hook( __FILE__, 'attachmentav_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-attachmentav.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function attachmentav_run() {

	$plugin = new Attachmentav();
	$plugin->run();

}
attachmentav_run();
