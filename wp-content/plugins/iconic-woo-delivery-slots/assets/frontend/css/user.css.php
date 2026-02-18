<?php
/**
 * User defined CSS for reservation table.
 *
 * @package Iconic_WDS
 */

?>
<style>
	<?php global $iconic_wds; ?>
	:root {
		--wds-primary: <?php echo esc_html( $iconic_wds->settings['reservations_reservations_highlight_color'] ); ?>;
		/* 20% darker */
		--wds-primary-hover: <?php echo esc_html( Iconic_WDS\Helpers::adjust_color_brightness( $iconic_wds->settings['reservations_reservations_highlight_color'], -0.2 ) ); ?>;
		/* 90% lighter */
		--wds-primary-lighter: <?php echo esc_html( Iconic_WDS\Helpers::adjust_color_brightness( $iconic_wds->settings['reservations_reservations_highlight_color'], 0.9 ) ); ?>;
		--wds-border: #DEDEDE;
		--wds-heading-color: #606060;
		--wds-earliest-slot-color: <?php echo esc_html( $iconic_wds->settings['reservations_reservations_earliest_slot_color'] ); ?>;
		--wds-remaining-label-color: <?php echo esc_html( $iconic_wds->settings['reservations_reservations_slots_remaining_color'] ); ?>;
		--wds-unavailable-slot-color: <?php echo esc_html( $iconic_wds->settings['reservations_reservations_slots_unavailable_color'] ); ?>;
	}
</style>
