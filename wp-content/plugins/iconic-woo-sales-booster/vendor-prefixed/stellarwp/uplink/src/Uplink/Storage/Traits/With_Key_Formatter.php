<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by James Kemp on 07-July-2025 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */ declare( strict_types=1 );

namespace Iconic_WSB_NS\StellarWP\Uplink\Storage\Traits;

use Iconic_WSB_NS\StellarWP\Uplink\Storage\Contracts\Storage;
use Iconic_WSB_NS\StellarWP\Uplink\Storage\Exceptions\Invalid_Key_Exception;

/**
 * @mixin Storage
 */
trait With_Key_Formatter {

	/**
	 * Converts a storage key into a string.
	 *
	 * @param string|int|float|mixed[]|object $key The cache key. Accepts any variable that can be json encoded.
	 *
	 * @throws Invalid_Key_Exception
	 */
	private function key( $key ): string {
		if ( ! $key ) {
			throw new Invalid_Key_Exception( 'The cache key cannot be empty' );
		}

		return is_scalar( $key ) ? (string) $key : md5( (string) wp_json_encode( $key ) );
	}

}
