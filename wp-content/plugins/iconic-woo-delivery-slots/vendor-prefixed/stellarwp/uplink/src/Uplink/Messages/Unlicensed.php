<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by James Kemp on 14-January-2026 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Iconic_WDS_NS\StellarWP\Uplink\Messages;

class Unlicensed extends Message_Abstract {
	/**
	 * @inheritDoc
	 */
	public function get(): string {
        $message  = '<div class="notice notice-warning"><p>';
        $message .= esc_html__( 'No license entered.', 'jckwds' );
        $message .= '</p></div>';

		return $message;
	}
}
