<?php
/**
 * Fired during plugin activation
 *
 * @link       https://powerfulwp.com
 * @since      1.0.0
 *
 * @package    GMFW
 * @subpackage GMFW/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    GMFW
 * @subpackage GMFW/includes
 * @author     powerfulwp <support@powerfulwp.com>
 */
class GMFW_Activator {

	/**
	 * This is the plugin activate function.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		add_option( 'gmfw_maximum_length', '160' );
		add_option( 'gmfw_activation_date', date_i18n( 'Y-m-d H:i:s' ) );
	}

}

