<?php
/**
 * The API implemented by all subscribers.
 *
 * @package Iconic_Flux_NS\StellarWP\Telemetry\Contracts
 *
 * @license GPL-2.0-or-later
 * Modified by iconicwp on 27-January-2026 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Iconic_Flux_NS\StellarWP\Telemetry\Contracts;

/**
 * Interface Subscriber_Interface
 *
 * @package Iconic_Flux_NS\StellarWP\Telemetry\Contracts
 */
interface Subscriber_Interface {

	/**
	 * Register action/filter listeners to hook into WordPress
	 *
	 * @return void
	 */
	public function register();
}
