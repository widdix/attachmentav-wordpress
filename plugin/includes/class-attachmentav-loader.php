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
			if ($file['size'] <= 10000000) { // Maximum file size for realtime scanning is 10 MB
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
					$error_messages = implode(',', $response->get_error_messages());
					$file['error'] = "attachmentAV: Failed to scan uploaded file for malware ({$error_messages}).";
				} else {
					if ($response['response']['code'] == 200) {
						$body = json_decode($response['body'], true);
						if ($body['status'] == 'no' && get_option('attachmentav_block_unscannable') == 'true') {
							$file['error'] = "attachmentAV: Could not scan file (e.g., encrypted files). Upload blocked.";
						} else if ($body['status'] == 'infected') {
							$file['error'] = "attachmentAV: Uploaded file is infected ({$body['finding']}). Upload blocked.";
						}
					} else if ($response['response']['code'] == 401) {
						$file['error'] = "attachmentAV: Could not scan uploaded file for malware as license key is missing or invalid.";
					} else if ($response['response']['code'] == 429) {
						$file['error'] = "attachmentAV: You've reached the maximum number of malware scans.";
					} else {
						$file['error'] = "attachmentAV: Failed to scan uploaded file for malware due to unknown error.";
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

		function scan_attachment_block_unscannable($post_id) {
			if (get_option('attachmentav_block_unscannable') == 'true') {
				wp_delete_attachment($post_id, true);
			}
		}
		function scan_attachment_error($post_id, $error_code) {
			add_post_meta($post_id, 'attachmentav_scan_result', "error:" . $error_code);
			scan_attachment_block_unscannable($post_id);
		}
		function scan_attachment($post_id) {
			$file = get_attached_file($post_id);
			if (filesize($file) <= 10000000) { // Maximum file size for realtime scanning is 10 MB
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
					error_log("attachmentAV: Failed to scan uploaded file for malware ({$error_messages}).");
					scan_attachment_error($post_id, 'client');
				} else {
					if ($response['response']['code'] == 200) {
						$body = json_decode($response['body'], true);
						add_post_meta($post_id, 'attachmentav_scan_result', $body['status']);
						if (array_key_exists('finding', $body)) {
							add_post_meta($post_id, 'attachmentav_scan_finding', $body['finding']);
						}
						if ($body['status'] == 'infected') {
							wp_delete_attachment($post_id, true);
						} else if ($body['status'] == 'no') {
							scan_attachment_block_unscannable($post_id);
						}
					} else if ($response['response']['code'] == 401) {
						scan_attachment_error($post_id, 'license_key_missing');
					} else if ($response['response']['code'] == 429) {
						scan_attachment_error($post_id, 'max_scans_reached');
					} else {
						scan_attachment_error($post_id, 'server');
					}
				}
			} else {
				scan_attachment_error($post_id, 'too_big');
			}
		}
		add_action('add_attachment', 'scan_attachment');

		function wpforms_scan_file($file) {
			if (filesize($file) <= 10000000) { // Maximum file size for realtime scanning is 10 MB
				$endpoint = 'https://eu.developer.attachmentav.com/v1/scan/sync/binary';
				$response = wp_remote_post($endpoint, array(
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
					error_log("attachmentAV: Failed to scan uploaded WPForms file for malware ({$error_messages}).");
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
			} else {
				if (get_option('attachmentav_block_unscannable') == 'true') {
					return array('status' => 'error:too_big');
				}
			}
		}
		function wpforms_status2error($file, $filename, $ret) {
			wp_delete_file($file);
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
			$upload_dir = wpforms_upload_dir();
			$upload_path = $upload_dir['path'];
			$form_directory = absint( $form_id ) . '-' . md5( $form_id . $form_data['created'] );
			$upload_path_form = wp_normalize_path( trailingslashit( $upload_path ) . $form_directory );
			foreach ($fields as $i => $field) {
				if ($field['type'] == 'file-upload') {
					$errors = [];
					if(!empty($field['value_raw'])) { // style modern
						foreach($field['value_raw'] as $v) {
							$file = trailingslashit($upload_path_form) . $v['file'];
							$ret = wpforms_scan_file($file);
							if ($ret['status'] == 'no') {
								if (get_option('attachmentav_block_unscannable') == 'true') {
									$errors[] = wpforms_status2error($file, $v['file_user_name'], $ret);
								}
							} else if ($ret['status'] == 'clean') {
								// continue
							} else {
								$errors[] = wpforms_status2error($file, $v['file_user_name'], $ret);
							}
						}
					} else if(!empty($field['value'])) { // style classic
						$file = trailingslashit($upload_path_form) . $field['file'];
						$ret = wpforms_scan_file($file);
						if ($ret['status'] == 'no') {
							if (get_option('attachmentav_block_unscannable') == 'true') {
								$errors[] = wpforms_status2error($file, $field['file_user_name'], $ret);
							}
						} else if ($ret['status'] == 'clean') {
							// continue
						} else {
							$errors[] = wpforms_status2error($file, $field['file_user_name'], $ret);
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

		function wfu_after_file_loaded_error($wp_filesystem, $changable_data, $file, $error_message) {
			$changable_data['error_message'] = $error_message;
			$changable_data['admin_message'] = $error_message;
			$wp_filesystem->delete($file);
			return $changable_data;
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
					$changable_data = wfu_after_file_loaded_error($wp_filesystem, $changable_data, $additional_data['file_path'], "Failed to scan uploaded file for malware ({$error_messages}).");
				} else {
					if ($response['response']['code'] == 200) {
						$body = json_decode($response['body'], true);
						if ($body['status'] == 'no' && get_option('attachmentav_block_unscannable') == 'true') {
							$changable_data = wfu_after_file_loaded_error($wp_filesystem, $changable_data, $additional_data['file_path'], "Could not scan file (e.g., encrypted files). Upload blocked.");
						} else if ($body['status'] == 'infected') {
							$changable_data = wfu_after_file_loaded_error($wp_filesystem, $changable_data, $additional_data['file_path'], "Uploaded file is infected ({$body['finding']}). Upload blocked.");
						}
					} else if ($response['response']['code'] == 401) {
						$changable_data = wfu_after_file_loaded_error($wp_filesystem, $changable_data, $additional_data['file_path'], "Could not scan uploaded file for malware as license key is missing or invalid.");
					} else if ($response['response']['code'] == 429) {
						$changable_data = wfu_after_file_loaded_error($wp_filesystem, $changable_data, $additional_data['file_path'], "You've reached the maximum number of malware scans.");
					} else {
						$changable_data = wfu_after_file_loaded_error($wp_filesystem, $changable_data, $additional_data['file_path'], "Failed to scan uploaded file for malware due to unknown error.");
					}
				}
			} else {
				if (get_option('attachmentav_block_unscannable') == 'true') {
					$changable_data = wfu_after_file_loaded_error($wp_filesystem, $changable_data, $additional_data['file_path'], "Could not scan file as it exceeds the maximum of 10 MB. Upload blocked.");
				}
			}
			return $changable_data;
		}
		if (get_option('attachmentav_scan_wpfileupload') != 'false') {
			add_filter('wfu_after_file_loaded', 'wfu_after_file_loaded', 10, 2);
		}

		function wpcf7_validate_file_error($result, $tag, $file, $error_message) {
			$result->invalidate($tag, $error_message);
			wp_delete_file($file);
			return $result;
		}
		function wpcf7_validate_file($result, $tag, $additional_data) {
			foreach ($additional_data['uploaded_files'] as $file) {
				if (filesize($file) <= 10000000) { // Maximum file size for realtime scanning is 10 MB
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
						$result = wpcf7_validate_file_error($result, $tag, $file, "Failed to scan uploaded file for malware ({$error_messages}).");
					} else {
						if ($response['response']['code'] == 200) {
							$body = json_decode($response['body'], true);
							if ($body['status'] == 'no' && get_option('attachmentav_block_unscannable') == 'true') {
								$result = wpcf7_validate_file_error($result, $tag, $file, "Could not scan file (e.g., encrypted files). Upload blocked.");
							} else if ($body['status'] == 'infected') {
								$result = wpcf7_validate_file_error($result, $tag, $file, "Uploaded file is infected ({$body['finding']}). Upload blocked.");
							}
						} else if ($response['response']['code'] == 401) {
							$result = wpcf7_validate_file_error($result, $tag, $file, "Could not scan uploaded file for malware as license key is missing or invalid.");
						} else if ($response['response']['code'] == 429) {
							$result = wpcf7_validate_file_error($result, $tag, $file, "You've reached the maximum number of malware scans.");
						} else {
							$result = wpcf7_validate_file_error($result, $tag, $file, "Failed to scan uploaded file for malware due to unknown error.");
						}
					}
				} else {
					if (get_option('attachmentav_block_unscannable') == 'true') {
						$result = wpcf7_validate_file_error($result, $tag, $file, "Could not scan file as it exceeds the maximum of 10 MB. Upload blocked.");
					}
				}
			}
			return $result;
		}
		function wpcf7_upload_file_name_custom_error($file, $error_message) {
			wp_delete_file($file);
			wp_send_json_error($error_message); // calls PHP's die to stop execution of request
		}
		function wpcf7_upload_file_name_custom($file, $filename) {
			if (filesize($file) <= 10000000) { // Maximum file size for realtime scanning is 10 MB
				$endpoint = 'https://eu.developer.attachmentav.com/v1/scan/sync/binary';
				$response = wp_remote_post($endpoint, array(
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
					wpcf7_upload_file_name_custom_error($file, "Failed to scan uploaded file for malware ({$error_messages}).");
				} else {
					if ($response['response']['code'] == 200) {
						$body = json_decode($response['body'], true);
						if ($body['status'] == 'no' && get_option('attachmentav_block_unscannable') == 'true') {
							wpcf7_upload_file_name_custom_error($file, "Could not scan file (e.g., encrypted files). Upload blocked.");
						} else if ($body['status'] == 'infected') {
							wpcf7_upload_file_name_custom_error($file, "Uploaded file is infected ({$body['finding']}). Upload blocked.");
						}
					} else if ($response['response']['code'] == 401) {
						wpcf7_upload_file_name_custom_error($file, "Could not scan uploaded file for malware as license key is missing or invalid.");
					} else if ($response['response']['code'] == 429) {
						wpcf7_upload_file_name_custom_error($file, "You've reached the maximum number of malware scans.");
					} else {
						wpcf7_upload_file_name_custom_error($file, "Failed to scan uploaded file for malware due to unknown error.");
					}
				}
			} else {
				if (get_option('attachmentav_block_unscannable') == 'true') {
					wpcf7_upload_file_name_custom_error($file, "Could not scan file as it exceeds the maximum of 10 MB. Upload blocked.");
				}
			}
		}
		if (get_option('attachmentav_scan_wpcf7') != 'false') {
			add_action('wpcf7_upload_file_name_custom', 'wpcf7_upload_file_name_custom', 10, 2 ); // This hook is not provided by CF7 as the name implies, it is provided by "Drag and Drop Multiple File Upload for Contact Form 7"
			add_filter('wpcf7_validate_file*', 'wpcf7_validate_file', 20, 3);
			add_filter('wpcf7_validate_file', 'wpcf7_validate_file', 20, 3);
		}
	}
}
