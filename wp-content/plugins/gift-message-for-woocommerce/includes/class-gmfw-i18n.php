<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://powerfulwp.com
 * @since      1.0.0
 *
 * @package    GMFW
 * @subpackage GMFW/includes
 */
if ( ! class_exists( 'GMFW_I18n' ) ) {
	/**
	 * Define the internationalization functionality.
	 *
	 * Loads and defines the internationalization files for this plugin
	 * so that it is ready for translation.
	 *
	 * @since      1.0.0
	 * @package    GMFW
	 * @subpackage GMFW/includes
	 * @author     powerfulwp <support@powerfulwp.com>
	 */
	class GMFW_I18n {


		/**
		 * Load the plugin text domain for translation.
		 *
		 * @since    1.0.0
		 */
		public function load_plugin_textdomain() {

			load_plugin_textdomain(
				'gmfw',
				false,
				dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
			);

		}



	}
}
