<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_WSB_Blocks.
 *
 * Register blocks.
 *
 * @class    Iconic_WSB_Blocks
 * @version  1.0.0
 */
class Iconic_WSB_Blocks {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'register_blocks' ), 20 );
	}

	/**
	 * Register Blocks.
	 *
	 * @return void
	 */
	public static function register_blocks() {
		register_block_type(
			'iconic-wsb/fbt',
			array(
				'render_callback' => array( __CLASS__, 'iconic_wsb_fbt_callback' ),
				'attributes'      => array(
					'productId' => array(
						'type' => 'string',
					),
				),
			)
		);
	}

	/**
	 * Render FBT Block.
	 *
	 * @param array $attributes Attributes.
	 * @return string
	 */
	public static function iconic_wsb_fbt_callback( $attributes ) {
		$attributes['product_id'] = $attributes['productId'];
		return Iconic_WSB_Shortcodes::iconic_wsb_fbt_shortcode( $attributes );
	}
}
