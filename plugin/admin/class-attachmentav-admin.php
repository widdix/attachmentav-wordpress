<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://attachmentav.com
 * @since      1.0.0
 *
 * @package    Attachmentav
 * @subpackage Attachmentav/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Attachmentav
 * @subpackage Attachmentav/admin
 * @author     widdix GmbH <hello@attachmentav.com>
 */
class Attachmentav_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		
		add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
		

	}

	/**
     * Add options page
     */
    public function add_plugin_page() {
        add_options_page(
            'attachmentAV Settings', 
            'attachmentAV', 
            'manage_options', 
            'attachmentav', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page() {
        ?>
        <div class="wrap">
            <h1>attachmentAV Settings</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'attachmentav' );
                do_settings_sections( 'attachmentav' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init() {        
        register_setting(
            'attachmentav', // Option group
            'attachmentav_api_key', // Option name
            array( $this, 'sanitize_text' ) // Sanitize
        );

		register_setting(
            'attachmentav', // Option group
            'attachmentav_block_unscannable', // Option name
            array( $this, 'sanitize_text' ) // Sanitize
        );

        add_settings_section(
            'attachmentav_subscription', // ID
            'Subscription', // Title
            array( $this, 'print_subscription_section' ), // Callback
            'attachmentav' // Page
        );  

        add_settings_field(
            'attachmentav_api_key', // ID
            'API Key', // Title 
            array( $this, 'print_api_key' ), // Callback
            'attachmentav', // Page
            'attachmentav_subscription' // Section           
        );

        add_settings_section(
            'attachmentav_general', // ID
            'General', // Title
            array( $this, 'print_general_section' ), // Callback
            'attachmentav' // Page
        ); 

		add_settings_field(
            'attachmentav_block_unscannable', // ID
            'Block unscannable files?', // Title 
            array( $this, 'print_block_unscannable' ), // Callback
            'attachmentav', // Page
            'attachmentav_general' // Section           
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize_text( $input ) {
		return sanitize_text_field($input);
    }

    /** 
     * Print the Section text
     */
    public function print_subscription_section()
    {
        print '<a target="_blank" href="https://attachmentav.com/subscribe/wordpress/">Subscribe to attachmentAV</a> and enter the API key below.';
    }

    /** 
     * Print the Section text
     */
    public function print_general_section()
    {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function print_api_key() {
			printf(
				'<input type="text" name="attachmentav_api_key" value="%s" />', esc_attr(get_option('attachmentav_api_key'))
			);
		
    }

	public function print_block_unscannable() {
		if (get_option('attachmentav_block_unscannable') == 'true') {
			print('<select name="attachmentav_block_unscannable"><option value="true" selected>Yes</option><option value="false">No</option></select>');
		} else {
			print('<select name="attachmentav_block_unscannable"><option value="true">Yes</option><option value="false" selected>No</option></select>');
		}	
	}

    /** 
     * Get the settings option array and print one of its values
     */
    public function title_callback()
    {
        printf(
            '<input type="text" id="title" name="my_option_name[title]" value="%s" />',
            isset( $this->options['title'] ) ? esc_attr( $this->options['title']) : ''
        );
    }
	
	

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Attachmentav_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Attachmentav_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/attachmentav-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Attachmentav_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Attachmentav_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/attachmentav-admin.js', array( 'jquery' ), $this->version, false );

	}

}
