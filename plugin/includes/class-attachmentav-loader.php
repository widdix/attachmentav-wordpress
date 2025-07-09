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
					} else if ($response['response']['code'] == 401) {
						$file['error'] = "attachmentAV: Could not scan uploaded file for malware as license key is missing or invalid. Go to settings and add a valid license key.";
					} else if ($response['response']['code'] == 429) {
						$file['error'] = "attachmentAV: You've reached the maximum number of malware scans. Please try again later or contact hello@attachmentav.com for help.";
					} else {
						$file['error'] = "attachmentAV: Failed to scan uploaded file for malware due to unknown error. Please try again later or contact hello@attachmentav.com for help.";
					}
				}
			} else {
				if (get_option('attachmentav_block_unscannable') == 'true') {
					$file['error'] = "attachmentAV: Could not scan file as it exceeds the maximum of 10 MB. Upload blocked. Go to settings and allow uploading unscannable files.";
				}
			}
			return $file;
		}
		add_filter( 'wp_handle_upload_prefilter', 'attachmentav_upload_prefilter');

		function attachmentav_add_action_links( $links ) {
			$url = get_admin_url() . "options-general.php?page=attachmentav";
			$settings_link = '<a href="' . $url . '">' . __('Settings', 'attachmentav') . '</a>';
			array_unshift($links, $settings_link);
			return $links;
		}
		add_filter( 'plugin_action_links_attachmentav/attachmentav.php', 'attachmentav_add_action_links' );

		function modify_media_meta($media_dims, $post) {
			$scanResult = get_post_meta( $post->ID, 'attachmentav_scan_result', true );
			$media_dims .= "<div><strong>attachmentAV Scan Result:</strong> $scanResult</div>";
			return $media_dims;
		}
		add_filter('media_meta', "modify_media_meta", null, 2);

		function scan_attachment($post_id) {
			$attachment_url = wp_get_attachment_url( $post_id );
			$endpoint = 'https://eu.developer.attachmentav.com/v1/scan/sync/download';
			$args = array(
				'timeout' => 30,
				'body'    => '{"download_url":"' . $attachment_url . '"}',
				'headers' => array(
					'x-api-key' => get_option('attachmentav_api_key'),
					'x-wordpress-site-url' => get_site_url(),
					'Content-Type' => 'application/json',
				),
			);
			$response = wp_remote_post( $endpoint, $args);
			if (is_wp_error($response)) {
				add_post_meta($post_id, 'attachmentav_scan_result', 'error:client');
			} else {
				if ($response['response']['code'] == 200) {
					$body = json_decode($response['body'], true);
					add_post_meta($post_id, 'attachmentav_scan_result', $body['status']);
					if (array_key_exists('finding', $body)) {
						add_post_meta($post_id, 'attachmentav_scan_finding', $body['finding']);
					}
				} else if ($response['response']['code'] == 401) {
					add_post_meta($post_id, 'attachmentav_scan_result', 'error:license_key_missing');
				} else if ($response['response']['code'] == 429) {
					add_post_meta($post_id, 'attachmentav_scan_result', 'error:max_scans_reached');
				} else {
					add_post_meta($post_id, 'attachmentav_scan_result', 'error:server');
				}
			}
			add_post_meta($post_id, 'attachmentav_scan_result', 'CLEAN');
		}
		add_action('add_attachment', 'scan_attachment');

		function wpforms_scan_file($file) {
			$endpoint = 'https://eu.developer.attachmentav.com/v1/scan/sync/download';
			$args = array(
				'timeout' => 30,
				'body'    => '{"download_url":"' . $file['value'] . '"}',
				'headers' => array(
					'x-api-key' => get_option('attachmentav_api_key'),
					'x-wordpress-site-url' => get_site_url(),
					'Content-Type' => 'application/json',
				),
			);
			$response = wp_remote_post( $endpoint, $args);
			if (is_wp_error($response)) {
				return array('status' => 'error:client');
			} else {
				if ($response['response']['code'] == 200) {
					return json_decode($response['body'], true);
				} else if ($response['response']['code'] == 401) {
					return array('status' => 'error:license_key_missing');
				} else if ($response['response']['code'] == 429) {
					return array('status' => 'error:max_scans_reached');
				} else {
					return array('status' => 'error:server');
				}
			}
		}
		function wpforms_status2error($filename, $ret) {
			if ($ret['status'] == 'infected') {
				if (!empty($ret['finding'])) {
					return 'The file ' . $filename . ' is infected and therefore blocked (' . $ret['finding'] . ')';
				} else {
					return 'The file ' . $filename . ' is infected and therefore blocked';
				}
			} else if ($ret['status'] == 'no') {
				if (!empty($ret['finding'])) {
					return 'The file ' . $filename . ' is not scannable and therefore blocked (' . $ret['finding'] . ')';
				} else {
					return 'The file ' . $filename . ' is not scannable and therefore blocked';
				}
			} else {
				return $ret['status'];
			}
		}
		function wpforms_process_entry_save($fields, $entry, $form_id, $form_data) {
			foreach ($fields as $i => $field) {
				if ($field['type'] == 'file-upload') {
					$errors = [];
					if(!empty($field['value_raw'])) { // style modern
						foreach($field['value_raw'] as $file) {
							$ret = wpforms_scan_file($file);
							if ($ret['status'] == 'no') {
								if (get_option('attachmentav_block_unscannable') == 'true') {
									$errors[] = wpforms_status2error($file['file_user_name'], $ret);
								}
							} else if ($ret['status'] == 'clean') {
								// continue
							} else {
								$errors[] = wpforms_status2error($file['file_user_name'], $ret);
							}
						}
					} else if(!empty($field['value'])) { // style classic
						$ret = wpforms_scan_file($field);
						if ($ret['status'] == 'no') {
							if (get_option('attachmentav_block_unscannable') == 'true') {
								$errors[] = wpforms_status2error($field['file_user_name'], $ret);
							}
						} else if ($ret['status'] == 'clean') {
							// continue
						} else {
							$errors[] = wpforms_status2error($field['file_user_name'], $ret);
						}
					}
					if (count($errors) > 0) {
						wpforms()->process->errors[$form_data['id']][$i] = implode('; ', $errors);
					}
				}
			}
			return $fields;
		}
		if (get_option('attachmentav_scan_wpforms') != 'false') {
			add_action('wpforms_process_entry_save', 'wpforms_process_entry_save', 10, 4);
		}

		function wfu_after_file_loaded($changable_data, $additional_data) {
			WP_Filesystem();
			global $wp_filesystem;
			if ($wp_filesystem->size($additional_data['file_path']) <= 10000000) { // Maximum file size for realtime scanning is 10 MB
				$endpoint = 'https://eu.developer.attachmentav.com/v1/scan/sync/binary';
				$response = wp_remote_post( $endpoint, array(
					'timeout' => 30,
					'body'    => $wp_filesystem->get_contents($additional_data['file_path']),
					'headers' => array(
						'x-api-key' => get_option('attachmentav_api_key'),
						'x-wordpress-site-url' => get_site_url(),
						'Content-Type' => 'application/octet-stream',
					),
				));
				if (is_wp_error($response)) {
					$error_messages = implode(',', $response->get_error_messages());
					$changable_data['error_message'] = "Failed to scan uploaded file for malware ({$error_messages}).";
					$changable_data['admin_message'] = "Failed to scan uploaded file for malware ({$errmsg}). Please try again later or contact hello@attachmentav.com for help.";
				} else {
					if ($response['response']['code'] == 200) {
						$body = json_decode($response['body'], true);
						if ($body['status'] == 'no' && get_option('attachmentav_block_unscannable') == 'true') {
							$changable_data['error_message'] = "Could not scan file (e.g., encrypted files). Upload blocked.";
							$changable_data['admin_message'] = "Could not scan file (e.g., encrypted files). Upload blocked. Go to settings and allow uploading unscannable files.";
						} else if ($body['status'] == 'infected') {
							$changable_data['error_message'] = "Uploaded file is infected ({$body['finding']}). Upload blocked.";
							$changable_data['admin_message'] = "Uploaded file is infected ({$body['finding']}). Upload blocked.";
						}
					} else if ($response['response']['code'] == 401) {
						$changable_data['error_message'] = "Could not scan uploaded file for malware as license key is missing or invalid.";
						$changable_data['admin_message'] = "Could not scan uploaded file for malware as license key is missing or invalid. Go to settings and add a valid license key.";
					} else if ($response['response']['code'] == 429) {
						$changable_data['error_message'] = "You've reached the maximum number of malware scans.";
						$changable_data['admin_message'] = "You've reached the maximum number of malware scans. Please try again later or contact hello@attachmentav.com for help.";
					} else {
						$changable_data['error_message'] = "Failed to scan uploaded file for malware due to unknown error.";
						$changable_data['admin_message'] = "Failed to scan uploaded file for malware due to unknown error. Please try again later or contact hello@attachmentav.com for help.";
					}
				}
			} else {
				if (get_option('attachmentav_block_unscannable') == 'true') {
					$changable_data['error_message']= "Could not scan file as it exceeds the maximum of 10 MB. Upload blocked.";
					$changable_data['admin_message'] = "Could not scan file as it exceeds the maximum of 10 MB. Upload blocked. Go to settings and allow uploading unscannable files.";
				}
			}
			return $changable_data;
		}
		if (get_option('attachmentav_scan_wpfileupload') != 'false') {
			add_filter('wfu_after_file_loaded', 'wfu_after_file_loaded', 10, 2);
		}

		function wpcf7_validate_file($result, $tag, $additional_data) {
			foreach ($additional_data['uploaded_files'] as $file) {
				$endpoint = 'https://eu.developer.attachmentav.com/v1/scan/sync/binary';
				$response = wp_remote_post( $endpoint, array(
					'timeout' => 30,
					'body'    => file_get_contents($file),
					'headers' => array(
						'x-api-key' => get_option('attachmentav_api_key'),
						'x-wordpress-site-url' => get_site_url(),
						'Content-Type' => 'application/octet-stream',
					),
				));
				if (is_wp_error($response)) {
					$error_messages = implode(',', $response->get_error_messages());
					$result->invalidate($tag, "Failed to scan uploaded file for malware ({$error_messages}).");
				} else {
					if ($response['response']['code'] == 200) {
						$body = json_decode($response['body'], true);
						if ($body['status'] == 'no' && get_option('attachmentav_block_unscannable') == 'true') {
							$result->invalidate($tag, "Could not scan file (e.g., encrypted files). Upload blocked.");
						} else if ($body['status'] == 'infected') {
							$result->invalidate($tag, "Uploaded file is infected ({$body['finding']}). Upload blocked.");
						}
					} else if ($response['response']['code'] == 401) {
						$result->invalidate($tag, "Could not scan uploaded file for malware as license key is missing or invalid.");
					} else if ($response['response']['code'] == 429) {
						$result->invalidate($tag, "You've reached the maximum number of malware scans.");
					} else {
						$result->invalidate($tag, "Failed to scan uploaded file for malware due to unknown error.");
					}
				}
			}
			return $result;
		}
		if (get_option('attachmentav_scan_wpcf7') != 'false') {
			add_filter('wpcf7_validate_file*', 'wpcf7_validate_file', 20, 3);
			add_filter('wpcf7_validate_file', 'wpcf7_validate_file', 20, 3);
		}
	}

}
