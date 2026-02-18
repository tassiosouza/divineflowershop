<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by James Kemp on 07-July-2025 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */ declare( strict_types=1 );

namespace Iconic_WSB_NS\StellarWP\Uplink\Admin;

use Iconic_WSB_NS\StellarWP\Uplink\Config;

class Group {
	public const STELLARWP_UPLINK_GROUP = 'stellarwp_uplink_group';

	/**
	 * @param string $group_modifier
	 *
	 * @return string
	 */
	public function get_name( string $group_modifier = '' ) : string {
		$group_name = sprintf( '%s_%s', self::STELLARWP_UPLINK_GROUP, $group_modifier );

		return apply_filters( 'stellarwp/uplink/' . Config::get_hook_prefix() . '/license_field_group_name', $group_name, self::STELLARWP_UPLINK_GROUP, $group_modifier );
	}
}
