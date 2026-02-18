<?php
/**
 * Meta box for Checkout elements.
 *
 * @package Flux_Checkout
 */
?>

<div class="flux-ce" data-settings="<?php echo esc_attr( wp_json_encode( $settings ) ); ?>" data-categories="<?php echo esc_attr( wp_json_encode( $product_categories ) ); ?>">
	<div class="flux-ce-row flux-ce-row--position">
		<div class="flux-ce-row__label">
			<label for="fce_position">
				<strong><?php esc_html_e( 'Position', 'flux-checkout' ); ?></strong>
			</label>
			<p class='flux-cse-desc'><?php esc_html_e( 'Specify the position on the checkout page where this element will be placed.', 'flux-checkout' ); ?></p>
		</div>
		<div class="flux-ce-row__content">
			<?php include ICONIC_FLUX_PATH . 'templates/admin/checkout-elements/position-field.php'; ?>
		</div>
	</div>

	<div class="flux-ce-row">
		<div class="flux-ce-row__label">
			<label for="fce_enable_conditions">
				<strong><?php esc_html_e( 'Enable Conditional Display', 'flux-checkout' ); ?></strong>
			</label>
			<p class='flux-cse-desc'><?php echo wp_kses( __( 'Conditionally show/hide this element. Element always shows if left unchecked.', 'flux-checkout' ), Iconic_Flux_Helpers::get_kses_allowed_tags() ); ?></p>
		</div>
		<div class="flux-ce-row__content">
			<label class="flux-ce-checkbox">
				<input type="checkbox" name='fce_enable_conditions' v-model="enable_rules">
				<span class="flux-ce-checkbox__slider"></span>
			</label>
			<span class='flux-ce-checkbox__slider-text' v-if="enable_rules"><?php esc_html_e( 'Enabled', 'flux-checkout' ); ?></span>
			<span class='flux-ce-checkbox__slider-text' v-else><?php esc_html_e( 'Disabled', 'flux-checkout' ); ?></span>
		</div>
	</div>

	<div class="flux-ce-row" v-show="enable_rules">
		<div class="flux-ce-row__label">
			<label for="fce_enable_conditions">
				<strong><?php esc_html_e( 'Conditional Display Rules', 'flux-checkout' ); ?></strong>
			</label>
			<p class='flux-cse-desc'><?php esc_html_e( 'Add a set of rules to determine when this Element should show.', 'flux-checkout' ); ?></p>
		</div>
		<div class="flux-ce-row__content flux-ce-row__content--rules">

			<div class="flux-ce-section">
				<select name="flux-ce-rule-condition" id="flux-ce-rule-condition" v-model="rule_condition">
					<option value="show"><?php esc_html_e( 'Show', 'flux-checkout' ); ?></option>
					<option value="hide"><?php esc_html_e( 'Hide', 'flux-checkout' ); ?></option>
				</select> <span class='flux-cs-show-hide-text'><?php esc_html_e( 'this Checkout Element when any of the following rules match:', 'flux-checkout' ); ?></span>
			</div>
			<div class="flux-ce-section">
				<div class="flux-ce-rules">
					<?php require ICONIC_FLUX_PATH . 'templates/admin/checkout-elements/rule.php'; ?>
				</div> <!-- flux-ce-rules -->
			</div> <!-- flux-ce-section -->

			<section :class="{'flux-ce-add-btn__wrap': true, 'flux-ce-add-btn__wrap--empty': 0 === rules.length }" >
				<template v-if="!rules.length">
					<p class='flux-ce-add-btn__wrap_p1'><?php esc_html_e( 'No rules applied.', 'flux-checkout' ); ?></p>
					<p class='flux-ce-add-btn__wrap_p2' v-if="'show' === rule_condition"><?php esc_html_e( 'This Element will appear unconditionally without rules.', 'flux-checkout' ); ?></p>
					<p class='flux-ce-add-btn__wrap_p2' v-else><?php esc_html_e( 'This Element will hide unconditionally without rules.', 'flux-checkout' ); ?></p>
				</template>

				<button :class="{'button': true, 'button-primary': !rules.length}" @click.prevent="addOrRule">
					<template v-if="rules.length">
						<?php esc_html_e( 'Add New Rule', 'flux-checkout' ); ?>
					</template>
					<template v-else>
						<?php esc_html_e( 'Add Your First Rule', 'flux-checkout' ); ?>
					</template>
				</button>
			</section>
		</div> <!-- flux-ce-row__content -->
	</div>

	<?php
	wp_nonce_field( 'fce_metabox_nonce', 'fce_metabox_nonce' );
	?>

	<input type="hidden" name='fce_settings' :value="settings">
</div>
