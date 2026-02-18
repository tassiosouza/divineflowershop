<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by James Kemp on 17-November-2025 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Iconic_WSB_NS\StellarWP\Uplink\Messages;

class Expired_Key extends Message_Abstract {
	/**
	 * @inheritDoc
	 */
	public function get(): string {
        $message  = '<div class="notice notice-warning"><p>';
        $message  .= __( 'Your license is expired', 'iconic-wsb' );
		$message .= '<a href="https://evnt.is/195y" target="_blank" class="button button-primary">' .
			__( 'Renew Your License Now', 'iconic-wsb' ) .
			'<span class="screen-reader-text">' .
			__( ' (opens in a new window)', 'iconic-wsb' ) .
			'</span></a>';
        $message .= '</p>    </div>';

		return $message;
	}
}
