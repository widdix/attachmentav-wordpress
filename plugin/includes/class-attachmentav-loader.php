<?php

/**
 * Register all actions and filters for the plugin
 *
 * @link       https://attachmentav.com
 * @since      1.0.0
 *
 * @package    Attachmentav
 * @subpackage Attachmentav/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Attachmentav
 * @subpackage Attachmentav/includes
 * @author     widdix GmbH <hello@attachmentav.com>
 */
class Attachmentav_Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->actions = array();
		$this->filters = array();

	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress action that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the action is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         The priority at which the function should be fired.
	 * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
	 * @return   array                                  The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {

		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);

		return $hooks;

	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		
		function attachmentav_upload_prefilter( $file ) {
			// Maximum file size for realtime scanning is 10 MB
			if ($file['size'] <= 10000000) {
				WP_Filesystem();
				global $wp_filesystem;
				$endpoint = 'https://eu.developer.attachmentav.com/v1/scan/sync/binary';
				$response = wp_remote_post( $endpoint, array(
					'timeout' => 30,
					'body'    => $wp_filesystem->get_contents($file['tmp_name']),
					'headers' => array(
						'x-api-key' => get_option('attachmentav_api_key'),
						'x-wordpress-site-url' => get_site_url(),
						'Content-Type' => 'application/octet-stream',
					),
				));
				if (is_wp_error($response)) {
					$errmsg = implode(',', $response->get_error_messages());
					$file['error'] = "attachmentAV: Failed to scan uploaded file for malware ({$errmsg}). Please try again later or contact hello@attachmentav.com for help.";
				} else {
					if ($response['response']['code'] == 200) {
						$body = json_decode($response['body'], true);
						if ($body['status'] == 'no' && get_option('attachmentav_block_unscannable') == 'true') {
							$file['error'] = "attachmentAV: Could not scan file (e.g., encrypted files). Upload blocked. Go to settings and allow uploading unscannable files.";
						} else if ($body['status'] == 'infected') {
							$file['error'] = "attachmentAV: Uploaded file is infected ({$body['finding']}). Upload blocked.";
						}
					} else {
						if ($response['response']['code'] == 401) {
							$file['error'] = "attachmentAV: Could not scan uploaded file for malware as license key is missing or invalid. Go to settings and add a valid license key.";
						} else if ($response['response']['code'] == 429) {
							$body = json_decode($response['body'], true);
							$file['error'] = "attachmentAV: You've reached the maximum number of malware scans. Please try again later or contact hello@attachmentav.com for help.";
						} else {
							$file['error'] = "attachmentAV: Failed to scan uploaded file for malware due to unknown error. Please try again later or contact hello@attachmentav.com for help.";
						}
					}
				}
			} else {
				if (get_option('attachmentav_block_unscannable') == 'true') {
					$file['error'] = "attachmentAV: Could not scan file as it exceeds the maximum of 10 MB. Upload blocked. Go to settings and allow uploading unscannable files.";
				}
			}
			return $file;
		}
		add_filter( 'wp_handle_upload_prefilter', 'attachmentav_upload_prefilter' );

		function attachmentav_add_action_links( $links ) {
			$url = get_admin_url() . "options-general.php?page=attachmentav";
			$settings_link = '<a href="' . $url . '">' . __('Settings', 'attachmentav') . '</a>';
			array_unshift($links, $settings_link);
			return $links;
		}
		add_filter( 'plugin_action_links_attachmentav/attachmentav.php', 'attachmentav_add_action_links' );
	}

}
