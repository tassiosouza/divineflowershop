<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by iconicwp on 27-January-2026 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */ declare( strict_types=1 );

namespace Iconic_Flux_NS\StellarWP\Uplink\View;

use Iconic_Flux_NS\StellarWP\Uplink\Contracts\Abstract_Provider;
use Iconic_Flux_NS\StellarWP\Uplink\View\Contracts\View;

final class Provider extends Abstract_Provider {

	/**
	 * Configure the View Renderer.
	 */
	public function register() {
		$this->container->singleton(
			WordPress_View::class,
			new WordPress_View( __DIR__ . '/../../views' )
		);

		$this->container->bind( View::class, $this->container->get( WordPress_View::class ) );
	}
}
