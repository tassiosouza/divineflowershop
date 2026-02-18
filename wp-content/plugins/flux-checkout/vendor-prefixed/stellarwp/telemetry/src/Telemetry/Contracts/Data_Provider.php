<?php
/**
 * An interface that provides the API for all data providers.
 *
 * @since 1.0.0
 *
 * @package Iconic_Flux_NS\StellarWP\Telemetry\Contracts
 *
 * @license GPL-2.0-or-later
 * Modified by iconicwp on 27-January-2026 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Iconic_Flux_NS\StellarWP\Telemetry\Contracts;

/**
 * An interface that provides the API for all data providers.
 *
 * @since 1.0.0
 *
 * @package Iconic_Flux_NS\StellarWP\Telemetry\Contracts
 */
interface Data_Provider {

	/**
	 * Gets the data that should be sent to the telemetry server.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_data(): array;
}
