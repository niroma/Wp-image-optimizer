<?php

namespace WP_Image_Optimizer\Inc\Core;
use WP_Image_Optimizer as NS;
use WP_Image_Optimizer\Inc\Admin as Admin;
//use WP_Image_Optimizer\Inc\Frontend as Frontend;

/**
 * The core plugin class.
 * Defines internationalization, admin-specific hooks, and public-facing site hooks.
 *
 * @link       https://www.niroma.net
 * @since      1.0.0
 *
 * @author     Niroma
 */
class Init {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_base_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_basename;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The text domain of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $plugin_text_domain;

	/**
	 * Initialize and define the core functionality of the plugin.
	 */
	public function __construct() {

		$this->plugin_name = NS\PLUGIN_NAME;
		$this->version = NS\PLUGIN_VERSION;
		$this->plugin_basename = NS\PLUGIN_BASENAME;
		$this->plugin_text_domain = NS\PLUGIN_TEXT_DOMAIN;
		
		$this->opt_png = NS\OPT_PNG;
		$this->opt_gif = NS\OPT_GIF;
		$this->opt_jpg = NS\OPT_JPG;
		$this->opt_valid_os = NS\OPT_VALID_OS;
		$this->opt_exec_enable = NS\EXEC_ENABLE;
		$this->opt_skip_check = NS\OPT_SKIP_CHECK;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		//$this->define_public_hooks();
	}

	/**
	 * Loads the following required dependencies for this plugin.
	 *
	 * - Loader - Orchestrates the hooks of the plugin.
	 * - Internationalization_I18n - Defines internationalization functionality.
	 * - Admin - Defines all hooks for the admin area.
	 * - Frontend - Defines all hooks for the public side of the site.
	 *
	 * @access    private
	 */
	private function load_dependencies() {
		$this->loader = new Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Internationalization_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @access    private
	 */
	private function set_locale() {

		$plugin_i18n = new Internationalization_I18n( $this->plugin_text_domain );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @access    private
	 */
	private function define_admin_hooks() {

		if('Linux' != PHP_OS && 'Darwin' != PHP_OS) {
			//$this->loader->add_action('admin_notices', $plugin_admin, 'image_optimizer_notice_os');
			$this->opt_valid_os = false;
			$this->opt_png = false;
			$this->opt_gif = false;
			$this->opt_jpg = false;
		} else {
	
			// To skip binary checking, define CW_IMAGE_OPTIMIZER_SKIP_CHECK in your wp-config.php
			if ($this->opt_skip_check || get_option($this->plugin_name.'_skip_check') == TRUE ){
				$this->opt_skip_check = $skip = true;
			} else {
				$this->opt_skip_check = $skip = false;
			}
		 
			//$missing = array();
			if(!$skip && empty( trim(exec('which opt-png')) )){
				$this->opt_png = false;
			} else {
				$this->opt_png = true;
			}
			
			if(!$skip && empty( trim(exec('which opt-jpg')) )){
				$this->opt_jpg = false;
			} else {
				$this->opt_jpg = true;
			}
			
			if(!$skip && empty( trim(exec('which opt-gif')) )){
				$this->opt_gif = false;
			} else {
				$this->opt_gif = true;
			}
			// Check if exec is disabled
			$disabled = array_map('trim', explode(',', ini_get('disable_functions')));
			if(in_array('exec', $disabled)){
				$this->opt_exec_enable = false;
			}
		
		}   
		
		$plugin_admin = new Admin\Admin( $this->get_plugin_name(), $this->get_version(), $this->get_plugin_text_domain(), $this->opt_png, $this->opt_gif, $this->opt_jpg, $this->opt_valid_os, $this->opt_exec_enable, $this->opt_skip_check );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );

		$this->loader->add_filter('wp_generate_attachment_metadata', $plugin_admin, 'image_optimizer_resize_from_meta_data', 10, 2);
		// TEST
		//$this->loader->add_filter('wp_insert_post', $plugin_admin, 'optimize_post_images', 10, 3);

		$this->loader->add_filter('manage_media_columns', $plugin_admin, 'image_optimizer_columns');
		$this->loader->add_filter( 'attachment_fields_to_edit', $plugin_admin, 'add_media_attachment_field_edit', 10, 2);
		
		$this->loader->add_action('manage_media_custom_column', $plugin_admin, 'image_optimizer_custom_column', 10, 2);
		//add_action('admin_init', 'image_optimizer_admin_init');
		$this->loader->add_action('admin_action_image_optimizer_manual', $plugin_admin, 'image_optimizer_manual');
		
		$this->loader->add_action( 'wp_ajax_image_optimizer_optimize_file', $plugin_admin, 'image_optimizer_file_optimizer' );
		$this->loader->add_action( 'wp_ajax_nopriv_image_optimizer_optimize_file', $plugin_admin, 'image_optimizer_file_optimizer' );
		
		$this->loader->add_action( 'wp_ajax_get_all_files_list', $plugin_admin, 'get_all_files_list' );
		$this->loader->add_action( 'wp_ajax_nopriv_get_all_files_list', $plugin_admin, 'get_all_files_list' );
		
		$this->loader->add_action( 'wp_ajax_get_nonopti_files_list', $plugin_admin, 'get_nonopti_files_list' );
		$this->loader->add_action( 'wp_ajax_nopriv_get_nonopti_files_list', $plugin_admin, 'get_nonopti_files_list' );
		
		$this->loader->add_action( 'admin_post_optimizer_form_response', $plugin_admin, 'form_process');
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'print_plugin_admin_notices');
		
		/*
		 * Additional Hooks go here
		 *
		 * e.g.
		 *
		 * //admin menu pages
		 * $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
		 *
		 *  //plugin action links
		 * $this->loader->add_filter( 'plugin_action_links_' . $this->plugin_basename, $plugin_admin, 'add_additional_action_link' );
		 *
		 */
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @access    private
	 */
	/*
	private function define_public_hooks() {

		$plugin_public = new Frontend\Frontend( $this->get_plugin_name(), $this->get_version(), $this->get_plugin_text_domain() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}
	*/
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve the text domain of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The text domain of the plugin.
	 */
	public function get_plugin_text_domain() {
		return $this->plugin_text_domain;
	}
	
	
	

}
