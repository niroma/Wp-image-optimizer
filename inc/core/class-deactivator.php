<?php

namespace WP_Image_Optimizer\Inc\Core;

/**
 * Fired during plugin deactivation
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @link       https://www.niroma.net
 * @since      1.0.0
 *
 * @author     Niroma
 **/
class Deactivator {

	/**
	 * Short Description.
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook("cron_image_optimizer");
	}

}
