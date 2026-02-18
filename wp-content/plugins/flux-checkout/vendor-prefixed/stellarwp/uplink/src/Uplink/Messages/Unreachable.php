<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by iconicwp on 27-January-2026 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Iconic_Flux_NS\StellarWP\Uplink\Messages;

class Unreachable extends Message_Abstract {
	/**
	 * @inheritDoc
	 */
	public function get(): string {
		$message = esc_html__( 'Sorry, key validation server is not available.', 'flux-checkout' );

		return $message;
	}
}
