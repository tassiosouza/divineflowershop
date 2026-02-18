<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by James Kemp on 07-July-2025 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */ declare( strict_types=1 );

namespace Iconic_WSB_NS\StellarWP\Uplink\Storage;

use Iconic_WSB_NS\StellarWP\Uplink\Config;
use Iconic_WSB_NS\StellarWP\Uplink\Contracts\Abstract_Provider;
use Iconic_WSB_NS\StellarWP\Uplink\Storage\Contracts\Storage;
use Iconic_WSB_NS\StellarWP\Uplink\Storage\Drivers\Option_Storage;

final class Provider extends Abstract_Provider {

	/**
	 * @inheritDoc
	 */
	public function register() {
		$this->container->singleton( Option_Storage::class, function () {
			$option_name = Config::get_hook_prefix() . '_storage';

			return new Option_Storage( $option_name );
		} );

		$this->container->singleton( Storage::class, static function( $c ): Storage {
			return $c->get( Config::get_storage_driver() );
		} );
	}

}
