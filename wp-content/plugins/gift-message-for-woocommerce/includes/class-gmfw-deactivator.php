<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://powerfulwp.com
 * @since      1.0.0
 *
 * @package    GMFW
 * @subpackage GMFW/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    GMFW
 * @subpackage GMFW/includes
 * @author     powerfulwp <support@powerfulwp.com>
 */
class GMFW_Deactivator {

	/**
	 * This is the plugin deactivate function.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		delete_option( 'gmfw_activation_date' );
	}

}
