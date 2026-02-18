<?php
/**
 * Template to display the reservation table.
 *
 * @package Iconic_WDS
 */

?>
<div class="wds-reservation-table-wrapper" data-shipping-method="<?php echo esc_attr( $args['shipping_method'] ); ?>">
</div>
<script type="text/template" id='wds-reservation-table-script'>
	<div class="wds-reservation-table" id="wds-reservation-table" v-if="page_loaded" v-cloak>
		<!-- Address step -->
		<div 
			class="wds-reservation-table__step wds-reservation-table__step--address" 
			:class="{'wds-reservation-table__step--open': step1.form_open}"
			v-if="false === shipping_method_provided_as_shortcode_arg"
			>
			<div class="wds-reservation-table__head">
				<div class="wds-reservation-table__head-title">
					<span class="wds-reservation-table__head-title-heading"><?php esc_html_e( 'Your Address', 'jckwds' ); ?></span>
					<a class="wds-reservation-table__head-title-button" href="#" @click.prevent='open_form(1)'>
						<span>{{btn_text(1)}}</span>
					</a>	
				</div>
				<div class="wds-reservation-table__head-caption" v-show="! step1.form_open">{{decode_entities( step1.caption ? step1.caption : step1.none_caption )}}</div>
			</div>

			<div class="wds-reservation-table__form" v-show="step1.form_open">
				<wds-address @address_changed="handle_address_changed" @button_clicked="fetch_shipping_methods"></wds-address>
			</div>
		</div> 
		<!-- /Address step -->
		<!-- Shipping method step -->
		<div 
			class="wds-reservation-table__step wds-reservation-table__step--shippingmethod" 
			:class="{ 'wds-reservation-table__step--grey': has_grey_bg( 2 ), 'wds-reservation-table__step--open': step2.form_open }"
			v-if="false === shipping_method_provided_as_shortcode_arg"
			>
			<div class="wds-reservation-table__head">
				<div class="wds-reservation-table__head-title">
					<span class="wds-reservation-table__head-title-heading"><?php esc_html_e( 'Shipping Method', 'jckwds' ); ?></span>
					<a class="wds-reservation-table__head-title-button" 
						href="#" 
						@click.prevent='open_form(2)'
						v-if="step1.caption && Object.keys(shipping_methods).length"
						>
						<span>{{btn_text(2)}}</span>
					</a>	
				</div>
				<div class="wds-reservation-table__head-caption" v-show="! step2.form_open">{{ decode_entities( step2.caption ? step2.caption : step2.none_caption ) }}</div>
			</div>
			<div class="wds-reservation-table__form"  v-show="step2.form_open">
				<ul class="wds-reservation-table__shipping-methods">
					<li v-for="(shipping_method, shipping_method_key) in shipping_methods" 
						@click="selected_shipping_method = shipping_method_key"
						class='wds-reservation-table__shipping-methods' 
						:class="{'wds-reservation-table__shipping-method--selected': shipping_method_key === selected_shipping_method }"
						>
						<div class="wds-reservation-table__shipping-methods-row">
							<div class="wds-reservation-table__shipping-methods-row__left">
								<input type="radio" v-model="selected_shipping_method" :value="shipping_method_key" name="wds-reservation-table-shipping-method" class="iconic-wds-radio"> <label>{{decode_entities( shipping_method.label )}}</label>
							</div>
							<div class="wds-reservation-table__shipping-methods-row__right" v-html="shipping_method.cost">
							</div>
						</div>
					</li>
				</ul>

				<button 
					class="wds-reservation-table-button" 
					:disabled="!selected_shipping_method"
					@click="handle_fetch_dates_button_click"><?php esc_html_e( 'Continue', 'jckwds' ); ?></button>
			</div>
		</div>
		<!-- /Shipping method step -->
		<!-- Timeslot step -->
		<div class="wds-reservation-table__step wds-reservation-table__step--datetime" :class="{ 'wds-reservation-table__step--grey': has_grey_bg( 3 ), 'wds-reservation-table__step--open': step3.form_open }">
			<div class="wds-reservation-table__head">
				<div class="wds-reservation-table__head-title">
					<span class="wds-reservation-table__head-title-heading">{{shipping_method_type}} <?php esc_html_e( 'Date & Time', 'jckwds' ); ?></span>
					<a class="wds-reservation-table__head-title-button" 
						href="#" 
						@click.prevent='open_form(3)'
						v-if="( shipping_method_provided_as_shortcode_arg || step2.caption ) && datetime_required_for_selected_method"
						>
						<span>{{btn_text(3)}}</span>
					</a>	
				</div>
				<div class="wds-reservation-table__head-caption" v-show="! step3.form_open">
					{{decode_entities( step3.caption ? step3.caption : step3.none_caption )}}
					<span class="wds-reservation-table__total-fee" v-if="total_fees" v-html="total_fees_formatted"></span>
				</div>
			</div>
			<div class="wds-reservation-table__form" v-show="step3.form_open">
				<div class="wds-reservation-table__select-date">
					<wds-reservation-date-slider 
						:initial_selected_date="selected_date"
						:available_dates="available_dates" 
						@select_date='handle_select_date'
						/>
				</div>

				<!-- only show this section if timeslot_enabled is enabled -->
				<template v-if="timeslot_enabled">
					<div class="wds-reservation-table__select-time">
						<ul class="wds-reservation-table__select-time-ul">
							<li class="wds-reservation-table__select-time-ul-li"
								:class="{ 
									'wds-reservation-table__select-time-ul-li--selected': selected_slot === timeslot.slot_id,
									'wds-reservation-table__select-time-ul-li--disabled': 0 === parseInt( timeslot.slots_available_count )
								}" 
								v-for="(timeslot,timeslot_index) in selected_date_timeslots" 
								@click="select_slot(timeslot)">
								<span class="wds-reservation-table__select-time-ul-li-left" :class="{'wds-reservation-table__select-time-ul-li-left--has-earliest': 0 === timeslot_index }">
									<input type="radio" :value="timeslot.slot_id" v-model="selected_slot" class="iconic-wds-radio"> 
									<label class='wds-reservation-table__select-time-ul-li-label'>{{timeslot.formatted}}</label>
									<span class='wds-reservation-table__select-time-ul-li-earliest-slot' v-if="is_earliest_slot( timeslot_index )"><?php esc_html_e( 'Earliest Available Slot', 'jckwds' ); ?></span>
									<span class='wds-reservation-table__select-time-ul-li-remaining' v-if="remaining_label_threshold >= timeslot.slots_available_count">
										<span v-if="0 < parseInt( timeslot.slots_available_count )">
											{{parseInt( timeslot.slots_available_count )}} <?php esc_html_e( 'remaining', 'jckwds' ); ?>
										</span>
										<span v-else><?php esc_html_e( 'Slot Unavailable', 'jckwds' ); ?></span>
									</span>
								</span>
								<span class="wds-reservation-table__select-time-ul-li-right">
									<span class='wds-reservation-table__select-time-fee' v-if="timeslot.fee.value" v-html="timeslot.fee.formatted"></span>
									<span class='wds-reservation-table__select-time-fee' v-else>{{strings.free}}</span>
								</span>
							</li>
						</ul>

						<div
							v-if="false === available_timeslots[selected_date]" 
							class="wds-reservation-table__select-time-noslots">
							<?php esc_html_e( 'No timeslots available for this date', 'jckwds' ); ?>
						</div>
					</div>
				</template>

				<div class="wds-reservation-table-button_wrap">
					<button class="wds-reservation-table-button"
						@click="reserve_slot"
						:disabled="timeslot_enabled ? !selected_slot : !selected_date"
						>
						{{ decode_entities( step3_button_text ) }}
					</button>
				</div>
			</div>
		</div>
		<!-- /Timeslot step -->
	</div>

</script>
