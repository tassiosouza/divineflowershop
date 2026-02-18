<?php
/**
 * Positions field for checkout elements.
 *
 * @package Flux_Checkout
 */

$positions = Iconic_Flux_Checkout_Elements::get_positions();
?>
<div class="flux-ce-position">
	<?php
	foreach ( $positions as $group_name => $positions ) {
		?>
		<div class="flux-ce-position__group">
			<h3 class="flux-ce-position__group-title"><?php echo esc_html( $group_name ); ?></h3>
			<div class="flux-ce-position__group-row flux-ce-position__group-row--<?php echo esc_attr( sanitize_title( $group_name ) ); ?>">
				<?php
				foreach ( $positions as $position_value => $position ) {
					?>
					<label class="flux-ce-position__option">
						<div class="flux-ce-position__option-img">
							<img src="<?php echo esc_url( ICONIC_FLUX_URL . 'images/elements/icons/' . $position['icon'] ); ?>" alt="<?php echo esc_attr( $position['text'] ); ?>">
						</div>
						<div class="flux-ce-position__option-footer">
							<input type="radio" name="fce_position" value="<?php echo esc_attr( $position_value ); ?>"  <?php checked( $position_value, $selected_position, true ); ?>>
							<span class="flux-ce-position__option-text"><?php echo esc_html( $position['text'] ); ?></span>
						</div>
					</label>
					<?php
				}
				?>
			</div> <!-- flux-ce-position__group-row -->
		</div> <!-- flux-ce-position__group -->
		<?php
	}
	?>
</div> <!-- flux-ce-position -->
