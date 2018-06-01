<?php

namespace WP_Image_Optimizer\Inc\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       https://www.niroma.net
 * @since      1.0.0
 *
 * @author    Niroma
 */
class Admin {

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
	 * The text domain of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_text_domain    The text domain of this plugin.
	 */
	private $plugin_text_domain;
	
	private $opt_png;
	private $opt_gif;
	private $opt_jpg;
	private $opt_valid_os;
	private $opt_exec_enable;
	private $opt_skip_check;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since       1.0.0
	 * @param       string $plugin_name        The name of this plugin.
	 * @param       string $version            The version of this plugin.
	 * @param       string $plugin_text_domain The text domain of this plugin.
	 */
	public function __construct( $plugin_name, $version, $plugin_text_domain, $opt_png, $opt_gif, $opt_jpg, $opt_valid_os, $opt_exec_enable, $opt_skip_check ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->plugin_text_domain = $plugin_text_domain;
		
		$this->opt_png = $opt_png;
		$this->opt_gif = $opt_gif;
		$this->opt_jpg = $opt_jpg;
		$this->opt_valid_os = $opt_valid_os;
		$this->opt_exec_enable = $opt_exec_enable;
		$this->opt_skip_check = $opt_skip_check;

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
		 * defined in Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-image-optimizer-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		/*
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-image-optimizer-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script('cw-image-optimizer-ajax', 'ajaxurl', admin_url( 'admin-ajax.php' ));

	}
	public function display_plugin_setup_page() {
		include_once( 'views/html-wp-image-optimizer-admin-display.php' );
	}
	
	public function add_plugin_admin_menu() {

    /*
     * Add a settings page for this plugin to the Settings menu.
     *
     * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
     *
     *        Administration Menus: http://codex.wordpress.org/Administration_Menus
     *
     */
  		add_media_page( 'WP Image Optimizer', 'WP Image Optimizer', 'edit_others_posts', $this->plugin_name, array($this, 'display_plugin_setup_page') );
		
	}
	
	/// AJAX && CUSTOM QUERIES FUNCTIONS
	public function image_optimizer_file_optimizer() {
		$attachmentId = $_POST['file'];
		$meta = $this->image_optimizer_resize_from_meta_data( wp_get_attachment_metadata( $attachmentId, true ), $attachmentId );
		wp_update_attachment_metadata( $attachmentId, $meta );	
		$meta['id'] = $attachmentId;
		wp_send_json($meta);
	}
	
	public function get_all_files_list() {
		global $wpdb;
		$attachments = $wpdb->get_results("SELECT ID FROM {$wpdb->posts} WHERE post_mime_type LIKE 'image%' AND post_type = 'attachment';" ); 
		$dataset = array();
		foreach($attachments as $attachment)  $dataset[] = $attachment->ID;
		wp_send_json($dataset);
	}
	
	public function get_nonopti_files_list() {
		global $wpdb;
		$attachments = $wpdb->get_results("SELECT ID FROM {$wpdb->posts} INNER JOIN {$wpdb->postmeta} ON ({$wpdb->posts}.ID = {$wpdb->postmeta}.post_id )  WHERE ({$wpdb->posts}.post_mime_type LIKE 'image%' AND  {$wpdb->posts}.post_type = 'attachment') AND ({$wpdb->postmeta}.meta_key = 'is_optimized' AND CAST({$wpdb->postmeta}.meta_value AS CHAR) NOT IN ('1'));"); 
		$dataset = array();
		foreach($attachments as $attachment)  $dataset[] = $attachment->ID;
		wp_send_json($dataset);
	}
	
	public function get_files_sum() {
		global $wpdb;
		$res = $wpdb->get_var("select COUNT(*) FROM {$wpdb->posts} WHERE post_mime_type LIKE 'image%' AND post_type = 'attachment';");
		return $res;
	}
	
	public function get_optimized_files_sum() {
		global $wpdb;
		$res = $wpdb->get_var("select COUNT(*) FROM {$wpdb->posts} INNER JOIN {$wpdb->postmeta} ON ({$wpdb->posts}.ID = {$wpdb->postmeta}.post_id )  WHERE ({$wpdb->posts}.post_mime_type LIKE 'image%' AND  {$wpdb->posts}.post_type = 'attachment') AND ({$wpdb->postmeta}.meta_key = 'is_optimized' AND CAST({$wpdb->postmeta}.meta_value AS CHAR) IN ('1'));");
		return $res;
	}
	
	/// OPTIMIZE FUNCTIONS
	
	/**
	 * Manually process an image from the Media Library
	 */
	public function image_optimizer_manual() {
		if ( FALSE === current_user_can('upload_files') ) {
			wp_die(__('You don\'t have permission to work with uploaded files.', $this->plugin_text_domain));
		}
	
		if ( FALSE === isset($_GET['attachment_ID'])) {
			wp_die(__('No attachment ID was provided.', $this->plugin_text_domain));
		}
	
		$attachment_ID = intval($_GET['attachment_ID']);
	
		$original_meta = wp_get_attachment_metadata( $attachment_ID );
	
		$new_meta = $this->image_optimizer_resize_from_meta_data( $original_meta, $attachment_ID );
		wp_update_attachment_metadata( $attachment_ID, $new_meta );
	
		$sendback = wp_get_referer();
		$sendback = preg_replace('|[^a-z0-9-~+_.?#=&;,/:]|i', '', $sendback);
		wp_redirect($sendback);
		exit(0);
	}
	/**
	 * Process an image.
	 *
	 * Returns an array of the $file $results.
	 *
	 * @param   string $file            Full absolute path to the image file
	 * @returns array
	 */
	public function image_optimizer($file) {
		// don't run on localhost, IPv4 and IPv6 checks
		// if( in_array($_SERVER['SERVER_ADDR'], array('127.0.0.1', '::1')) )
		//	return array($file, __('Not processed (local file)', $this->plugin_text_domain));
	
		// canonicalize path - disabled 2011-02-1 troubleshooting 'Could not find...' errors.
		// From the PHP docs: "The running script must have executable permissions on 
		// all directories in the hierarchy, otherwise realpath() will return FALSE."
		// $file_path = realpath($file);
		
		$debug = '';
		
		$file_path = $file;
	
		// check that the file exists
		if ( FALSE === file_exists($file_path) || FALSE === is_file($file_path) ) {
			$msg = sprintf(__("Could not find <span class='code'>%s</span>", $this->plugin_text_domain), $file_path);
			return array($file, $msg);
		}
	
		// check that the file is writable
		if ( FALSE === is_writable($file_path) ) {
			$msg = sprintf(__("<span class='code'>%s</span> is not writable", $this->plugin_text_domain), $file_path);
			return array($file, $msg);
		}
	
		// check that the file is within the WP_CONTENT_DIR
		$upload_dir = wp_upload_dir();
		$wp_upload_dir = $upload_dir['basedir'];
		$wp_upload_url = $upload_dir['baseurl'];
		if ( 0 !== stripos(realpath($file_path), realpath($wp_upload_dir)) ) {
			$msg = sprintf(__("<span class='code'>%s</span> must be within the content directory (<span class='code'>%s</span>)", $this->plugin_text_domain), htmlentities($file_path), $wp_upload_dir);
			return array($file, $msg);
		}
		
		if(function_exists('getimagesize')){
			$type = getimagesize($file_path);
			if(false !== $type){
				$type = $type['mime'];
			}
		}elseif(function_exists('mime_content_type')){
			$type = mime_content_type($file_path);
		}else{
			$type = 'Missing getimagesize() and mime_content_type() PHP functions';
		}
	
		switch($type){
			case 'image/jpeg':
				$command = 'opt-jpg';
				break;
			case 'image/png':
				$command = 'opt-png';
				break;
			case 'image/gif':
				$command = 'opt-gif';
				break;
			default:
				return array($file, __('Unknown type: ' . $type, $this->plugin_text_domain));
		}
	
		if(get_option($this->plugin_name .'_preserve_exif_datas' == TRUE && $command == 'opt-jpg')) $command .= ' -m all';
		stream_set_blocking(STDIN, false);
		$result = exec($command . ' ' . escapeshellarg($file));

		$result = str_replace($file . ': ', '', $result);
	
		if ($result == 'unchanged') {
			return array($file, __('No savings', $this->plugin_text_domain));
		}
	
		if(strpos($result, ' vs. ') !== false) {
			$s = explode(' vs. ', $result);
			
			$savings = intval($s[0]) - intval($s[1]);
			$savings_str = $this->image_optimizer_format_bytes($savings, 1);
			$savings_str = str_replace(' ', '&nbsp;', $savings_str);
	
			$percent = 100 - (100 * ($s[1] / $s[0]));
	
			$results_msg = sprintf(__("Reduced by %01.1f%% (%s)", $this->plugin_text_domain), $percent, $savings_str);
			
	
			return array($file, $results_msg);
		}
	
		return array($file, __('Bad response from optimizer '. $debug, $this->plugin_text_domain));
	}	
	/**
	 * Read the image paths from an attachment's meta data and process each image
	 * with image_optimizer().
	 *
	 * This method also adds a `image_optimizer` meta key for use in the media library.
	 *
	 * Called after `wp_generate_attachment_metadata` is completed.
	 */
	public function image_optimizer_resize_from_meta_data($meta, $ID = NULL) {
		if ( !empty($ID) && empty($meta) || empty($meta['file']) || empty($meta['sizes']) ) {
			$meta = $this->cw_fix_meta($meta, $ID);
		}
		$image_optimizer_meta = !empty($meta['image_optimizer']) ? $meta['image_optimizer'] : '';
		$file_path = $meta['file'];
		$store_absolute_path = true;
		$upload_dir = wp_upload_dir();
		$upload_path = trailingslashit( $upload_dir['basedir'] );
	
		// WordPress >= 2.6.2: determine the absolute $file_path (http://core.trac.wordpress.org/changeset/8796)
		if ( FALSE === strpos($file_path, WP_CONTENT_DIR) ) {
			$store_absolute_path = false;
			$file_path =  $upload_path . $file_path;
		}
	
		list($file, $msg) = $this->image_optimizer($file_path);
	
		$meta['file'] = $file;
		$meta['image_optimizer'] = $msg;
	
		// strip absolute path for Wordpress >= 2.6.2
		if ( FALSE === $store_absolute_path ) {
			$meta['file'] = str_replace($upload_path, '', $meta['file']);
		}
	
		// no resized versions, so we can exit
		if ( !isset($meta['sizes']) )
			return $meta;
	
		// meta sizes don't contain a path, so we calculate one
		$base_dir = dirname($file_path) . '/';
	
		foreach($meta['sizes'] as $size => $data) {
			list($optimized_file, $results) = $this->image_optimizer($base_dir . $data['file']);
	
			$meta['sizes'][$size]['file'] = str_replace($base_dir, '', $optimized_file);
			$meta['sizes'][$size]['image_optimizer'] = $results;
		}
		
		if (!empty($ID)) $this->set_attachment_attributes( $ID, $meta );
		if ($image_optimizer_meta != $meta['image_optimizer']) update_post_meta( $ID, 'image_optimizer', $meta['image_optimizer'] );
		
		return $meta;
	}

	public function cw_fix_meta($meta, $id) {
		$file = get_attached_file($id);
		$newmeta = wp_generate_attachment_metadata( $id, $file );
		wp_update_attachment_metadata( $id, $newmeta );
		$meta = wp_get_attachment_metadata($id);
	
		return $meta;
	}	
	/**
	 * Add fields to attachment
	*/
	public function add_media_attachment_field_edit( $form_fields, $post ){
		
		$is_optimized = get_post_meta( $post->ID, 'is_optimized', true );
		$wp_optimize_stats = maybe_unserialize(get_post_meta( $post->ID, 'wp_optimize_stats', true ));
		$wp_optimize_stats_html = '';
		if ( !empty($wp_optimize_stats) && $wp_optimize_stats['sizes'] ) {
			$wp_optimize_stats_html .= '<ul>';
			foreach($wp_optimize_stats['sizes'] as $size => $data) {
				$wp_optimize_stats_html .= '<li>';
					$wp_optimize_stats_html .=  $this->handleFilename(  $this->handleFilename( $wp_optimize_stats['sizes'][$size]['file'], '.'), '-', false);
					$wp_optimize_stats_html .= ' - ';
					$wp_optimize_stats_html .= $wp_optimize_stats['sizes'][$size]['image_optimizer'];
				$wp_optimize_stats_html .= '</li>';
			}
			$wp_optimize_stats_html .= '</ul>';
		}
			
		$form_fields['is_optimized'] = array(
			'label' => __( 'WP Optimized' ),
			'input' => 'html',
			'html' => $is_optimized ? 'YES :)' : 'NO :('
		); 
		$form_fields['wp_optimize_stats'] = array(
			'label' => __( 'WP Optimized Stats' ),
			'input' => 'html',
			'html' => $wp_optimize_stats_html
		); 
		return $form_fields;
	}	
	
	public function handleFilename($filename, $delimiter, $remove = true) {
		$filebroken = explode( $delimiter, $filename);
		$extension = array_pop($filebroken);
		if ($remove) return implode($delimiter, $filebroken);
		else return $extension;
	}	

	/**
	 * Set Optimized status
	*/	
	public function set_attachment_attributes( $attachment_id, $metas ) {
		$status = 1; //Files are supposed optimized
		$valid = array('No savings', 'Reduced by');
		foreach($metas['sizes'] as $size => $data) {
			$results = $metas['sizes'][$size]['image_optimizer'];
			if (($found = $this->findStringFromArray($valid, $results, $pos)) !== FALSE) {
				//echo "'$found' was found in '$results' at string position $pos. Image has been optimized if possible";
			} else $status = 0;
		}
		update_post_meta( $attachment_id, 'is_optimized', $status );
		update_post_meta( $attachment_id, 'wp_optimize_stats', maybe_serialize($metas) );
	}
	
	
	public function findStringFromArray($phrases, $string, &$position) {
		// Reverse sort phrases according to length.
		// This ensures that 'taxi' isn't found when 'taxi cab' exists in the string.
		usort($phrases, create_function('$a,$b',
										'$diff=strlen($b)-strlen($a);
										 return $diff<0?-1:($diff>0?1:0);'));

		// Pad-out the string and convert it to lower-case
		$string = ' '.strtolower($string).' ';

		// Find the phrase
		foreach ($phrases as $key => $value) {
			if (($position = strpos($string, ' '.strtolower($value).' ')) !== FALSE) {
				return $phrases[$key];
			}
		}

		// Not found
		return FALSE;
	}	
	/**
	 * Print column header for optimizer results in the media library using
	 * the `manage_media_columns` hook.
	 */
	public function image_optimizer_columns($defaults) {
		$defaults['cw-image-optimizer'] = 'Image Optimizer';
		return $defaults;
	}
	
	/**
	 * Return the filesize in a humanly readable format.
	 * Taken from http://www.php.net/manual/en/function.filesize.php#91477
	 */
	public function image_optimizer_format_bytes($bytes, $precision = 2) {
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= pow(1024, $pow);
		return round($bytes, $precision) . ' ' . $units[$pow];
	}
	/**
	 * Print column data for optimizer results in the media library using
	 * the `manage_media_custom_column` hook.
	 */
	public function image_optimizer_custom_column($column_name, $id) {
		if( $column_name == 'cw-image-optimizer' ) {
			$data = wp_get_attachment_metadata($id);

			if(!isset($data['file'])){
				$msg = 'Metadata is missing file path.';
				print __('Unsupported file type', $this->plugin_text_domain) . $msg;
				return;
			}
	
			$file_path = $data['file'];
			$upload_dir = wp_upload_dir();
			$upload_path = trailingslashit( $upload_dir['basedir'] );

			// WordPress >= 2.6.2: determine the absolute $file_path (http://core.trac.wordpress.org/changeset/8796)
			if ( FALSE === strpos($file_path, WP_CONTENT_DIR) ) {
				$file_path =  $upload_path . $file_path;
			}
	
			$msg = '';
	
			if (function_exists('getimagesize')) {
				$type = getimagesize($file_path);
				if(false !== $type){
					$type = $type['mime'];
				}
			} elseif(function_exists('mime_content_type')){
				$type = mime_content_type($file_path);
			} else{
				$type = false;
				$msg = 'getimagesize() and mime_content_type() PHP functions are missing';
			}
	
			$valid = true;
			switch($type){
				case 'image/jpeg':
					if($this->opt_jpg == false) {
						$valid = false;
						$msg = '<br>' . __('<em>opt-jpg</em> is missing');
					}
					break; 
				case 'image/png':
					if($this->opt_png == false) {
						$valid = false;
						$msg = '<br>' . __('<em>opt-png</em> is missing');
					}
					break;
				case 'image/gif':
					if($this->opt_gif == false) {
						$valid = false;
						$msg = '<br>' . __('<em>opt-gif</em> is missing');
					}
					break;
				default:
					$valid = false;
			}
	
			if($valid == false) {
				print __('Unsupported file type', $this->plugin_text_domain) . $msg;
				return;
			}
	
			if ( isset($data['image_optimizer']) && !empty($data['image_optimizer']) ) {
				print $data['image_optimizer'];
				printf("<br><a href=\"admin.php?action=image_optimizer_manual&amp;attachment_ID=%d\">%s</a>",
						 $id,
						 __('Re-optimize', $this->plugin_text_domain));
			} else {
				print __('Not processed', $this->plugin_text_domain);
				printf("<br><a href=\"admin.php?action=image_optimizer_manual&amp;attachment_ID=%d\">%s</a>",
						 $id,
						 __('Optimize now!', $this->plugin_text_domain));
			}
		}
	}
	/**** SETTINGS FORM ***/
	
	public function form_process(){
			if ( isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], $this->plugin_name.'submit-form') ){
				$admin_notice = '';
				$messageLog = '';
				$skip_check = $_POST[$this->plugin_name.'_skip_check'];
				$preserve_exif =  $_POST[$this->plugin_name.'_preserve_exif_datas']; 
				
				if (empty($admin_notice)) {
					if ( get_option( $this->plugin_name.'_skip_check' ) !== false ) {
						update_option( $this->plugin_name.'_skip_check', $skip_check );
					} else {
						add_option( $this->plugin_name.'_skip_check', $skip_check);
					}
					
					if ( get_option( $this->plugin_name.'_preserve_exif_datas' ) !== false ) {
						update_option( $this->plugin_name.'_preserve_exif_datas', $preserve_exif );
					} else {
						add_option( $this->plugin_name.'_preserve_exif_datas', $preserve_exif);
					}
					
					$admin_notice = "success";
					$messageLog .= 'Settings saved';
				}
				
				$this->custom_redirect( $admin_notice, $messageLog);
				die();
			}  else {
				wp_die( __( 'Invalid nonce specified', $this->plugin_name ), __( 'Error', $this->plugin_name ), array(
						'response' 	=> 403,
						'back_link' => 'upload.php?page=' . $this->plugin_name,
				) );
			}
	}
	
	public function custom_redirect( $admin_notice, $response ) {
		wp_redirect( esc_url_raw( add_query_arg( array(
									$this->plugin_name .'_admin_add_notice' => $admin_notice,
									$this->plugin_name .'_response' => $response,
									),
							admin_url('upload.php?page='. $this->plugin_name ) 
					) ) );

	}

	public function print_plugin_admin_notices() {              
		  if ( isset( $_REQUEST[$this->plugin_name .'_admin_add_notice'] ) ) {
			if( $_REQUEST[$this->plugin_name .'_admin_add_notice'] === "success") {
				$html =	'<div class="notice notice-success is-dismissible"> 
							<p><strong>' . htmlspecialchars( print_r( $_REQUEST[$this->plugin_name .'_response'], true) ) . '</strong></p></div>';
				echo $html;
			}
			if( $_REQUEST[$this->plugin_name .'_admin_add_notice'] === "error") {
				$html =	'<div class="notice notice-error is-dismissible"> 
							<p><strong>' . htmlspecialchars( print_r( $_REQUEST[$this->plugin_name .'_response'], true) ) . '</strong></p></div>';
				echo $html;
			}
		  } else {
			  return;
		  }

	}
	
}
