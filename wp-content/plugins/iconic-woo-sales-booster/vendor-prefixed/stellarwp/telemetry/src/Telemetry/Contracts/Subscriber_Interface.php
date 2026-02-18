<?php
/**
 * The API implemented by all subscribers.
 *
 * @package Iconic_WSB_NS\StellarWP\Telemetry\Contracts
 *
 * @license GPL-2.0-or-later
 * Modified by James Kemp on 17-November-2025 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Iconic_WSB_NS\StellarWP\Telemetry\Contracts;

/**
 * Interface Subscriber_Interface
 *
 * @package Iconic_WSB_NS\StellarWP\Telemetry\Contracts
 */
interface Subscriber_Interface {

	/**
	 * Register action/filter listeners to hook into WordPress
	 *
	 * @return void
	 */
	public function register();
}
