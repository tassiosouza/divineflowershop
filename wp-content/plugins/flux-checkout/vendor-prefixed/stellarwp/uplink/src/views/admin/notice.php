<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by iconicwp on 27-January-2026 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */ declare( strict_types=1 );
/**
 * Render a WordPress dashboard notice.
 *
 * @see \Iconic_Flux_NS\StellarWP\Uplink\Notice\Notice_Controller
 *
 * @var string $message The message to display.
 * @var string $classes The CSS classes for the notice.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="<?php echo esc_attr( $classes ) ?>">
	<p><?php echo esc_html( $message ) ?></p>
</div>
