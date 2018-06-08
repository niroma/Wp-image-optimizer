<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.niroma.net
 * @since             1.0.1
 * @package           WP_Image_Optimizer
 *
 * @wordpress-plugin
 * Plugin Name:       WP Image Optimizer
 * Plugin URI:        https://wordpress.org/plugins/wp-image-optimizer/
 * Description:       Reduce image file sizes and improve website performance using Linux littleutils image optimizers within WordPress.
 * Version:           1.0.0
 * Author:            Niroma
 * Author URI:        https://www.niroma.net/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-image-optimizer
 * Domain Path:       /languages
 */

namespace WP_Image_Optimizer;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Constants
 */

define( __NAMESPACE__ . '\NS', __NAMESPACE__ . '\\' );

define( NS . 'PLUGIN_NAME', 'wp-image-optimizer' );

define( NS . 'PLUGIN_VERSION', '1.1.0' );

define( NS . 'PLUGIN_NAME_DIR', plugin_dir_path( __FILE__ ) );

define( NS . 'PLUGIN_NAME_URL', plugin_dir_url( __FILE__ ) );

define( NS . 'PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

define( NS . 'PLUGIN_TEXT_DOMAIN', 'wp-image-optimizer' );

define( NS . 'OPT_PNG', true);

define( NS . 'OPT_GIF', true);

define( NS . 'OPT_JPG', true);

define( NS . 'OPT_JPEG_RECOMPRESS', true);

define( NS . 'OPT_VALID_OS', true);

define( NS . 'EXEC_ENABLE', true);

define( NS . 'OPT_SKIP_CHECK', false);
/**
 * Autoload Classes
 */

require_once( PLUGIN_NAME_DIR . 'inc/libraries/autoloader.php' );

/**
 * Register Activation and Deactivation Hooks
 * This action is documented in inc/core/class-activator.php
 */

register_activation_hook( __FILE__, array( NS . 'Inc\Core\Activator', 'activate' ) );

/**
 * The code that runs during plugin deactivation.
 * This action is documented inc/core/class-deactivator.php
 */

register_deactivation_hook( __FILE__, array( NS . 'Inc\Core\Deactivator', 'deactivate' ) );


/**
 * Plugin Singleton Container
 *
 * Maintains a single copy of the plugin app object
 *
 * @since    1.0.0
 */
class WP_Image_Optimizer {

	/**
	 * The instance of the plugin.
	 *
	 * @since    1.0.0
	 * @var      Init $init Instance of the plugin.
	 */
	private static $init;
	/**
	 * Loads the plugin
	 *
	 * @access    public
	 */
	public static function init() {

		if ( null === self::$init ) {
			self::$init = new Inc\Core\Init();
			self::$init->run();
		}

		return self::$init;
	}

}

/**
 * Begins execution of the plugin
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * Also returns copy of the app object so 3rd party developers
 * can interact with the plugin's hooks contained within.
 **/
function wp_plugin_name_init() {
		return WP_Image_Optimizer::init();
}

$min_php = '5.6.0';

// Check the minimum required PHP version and run the plugin.
if ( version_compare( PHP_VERSION, $min_php, '>=' ) ) {
		wp_plugin_name_init();
}
