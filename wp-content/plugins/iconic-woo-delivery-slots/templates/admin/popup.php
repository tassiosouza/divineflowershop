<?php
/**
 * Template to display the delivery slots popup on the order page in the admin.
 *
 * @package Iconic_WDS
 */

use Iconic_WDS\Order;

?>
<div class='wds-admin-popup-wrap' id="wds-admin-popup-wrap" v-cloak v-show="showPopup" @click.self="popup(false)" data-init="<?php echo esc_attr( wp_json_encode( Order::get_order_date_time( $order ) ) ); ?>">
	<div class="wds-admin-popup">
		<div class="wds-admin-popup__header">
			<div class="wds-admin-popup__header-left">
				<h3><?php echo esc_html_x( 'Add delivery slot', 'admin', 'jckwds' ); ?></h3>
			</div>
			<div class="wds-admin-popup__header-right">
				<div class="wds-admin-popup__header-right-inner">
					<span class="wds-admin-popup__header-right-label" @click='overrideRules = ! overrideRules'><?php echo esc_html_x( 'Override Rules', 'admin', 'jckwds' ); ?></span>	
					<label class="wds-admin-popup-switch">
						<input type="checkbox" v-model='overrideRules' value="yes" @change="handleOverrideChange">
						<span class="wds-admin-popup-switch__slider"></span>
					</label>
				</div>
			</div>
		</div>

		<div class="wds-admin-popup-body">

			<!-- if address is empty -->
			<div class="wds-admin-popup-no-shipping" v-if="!hasAddress && ! overrideRules">
				<div class="wds-admin-popup-no-shipping__img">
					<img src="<?php echo esc_attr( ICONIC_WDS_URL ); ?>/assets/img/admin-popup-noshipping.svg" />
				</div>
				<div class="wds-admin-popup-no-shipping__text">
					<p><?php echo esc_html_x( 'Please add a shipping address to the order, or toggle “override rules” to continue.', 'admin', 'jckwds' ); ?></p>
				</div>
				<div class="wds-admin-popup-no-shipping__btn">
					<button @click="popup(false)" class="wds-admin-popup-btn--large button button-primary"><?php echo esc_html_x( 'Close', 'admin', 'jckwds' ); ?></button>
				</div>
			</div>

			<!-- Address not empty -->
			<div class="wds-admin-popup-has-shipping" v-if=" hasAddress || overrideRules ">
				<div class="wds-admin-popup-sm" v-if="!overrideRules ">
					<div class="wds-admin-popup-sm__address">
						<div class="wds-admin-popup-title">
							<div class="wds-admin-popup-title__icon">
								<img src="<?php echo esc_attr( ICONIC_WDS_URL ); ?>/assets/img/admin-popup-delivery.svg" alt="">
							</div>
							<div class="wds-admin-popup-title__inner">
								<h3 class="wds-admin-popup-title__inner-h">Shipping / Delivery</h3>
								<div class="wds-admin-popup-title__inner-desc">
									<p>{{formattedDestination}}</p>
								</div>
							</div>
						</div>

						<div class="wds-admin-popup-sm__select wds-admin-popup-input wds-admin-popup-input--has-arrow wds-admin-popup-input--select" v-if="shippingMethods">
							<label>
								<?php echo esc_html_x( 'Shipping Method', 'admin', 'jckwds' ); ?>
								<select v-model="selectedShippingMethod" name='wds-admin-popup-sm' @change="handleShippingMethodChange">
									<option value=""><?php echo esc_html_x( 'Select a shipping method', 'admin', 'jckwds' ); ?></option>
									<option 
										v-for="(shippingMethod, key) in shippingMethods"
										:key="key"
										:value="key">
										{{shippingMethod.label}}
									</option>
								</select>
							</label>
						</div>
					</div>
				</div>

				<div class="wds-admin-popup-timeslot" v-if="( selectedShippingMethod && dateTimeEnabledForSM(selectedShippingMethod) ) || overrideRules">
					<div class="wds-admin-popup-title">
						<div class="wds-admin-popup-title__icon"><img src="<?php echo esc_attr( ICONIC_WDS_URL ); ?>/assets/img/admin-popup-clock.svg" alt=""></div>
						<div class="wds-admin-popup-title__inner">
							<h3 class="wds-admin-popup-title__inner-h"><?php echo esc_html_x( 'Delivery Slots', 'admin', 'jckwds' ); ?></h3>
							<div class="wds-admin-popup-title__inner-desc"></div>
						</div>
					</div>

					<!-- Date and time picker -->
					<div class="wds-admin-popup-timeslot-picker" v-click-outside="dateTimeBlur">
						<div class="wds-admin-popup-timeslot-picker__date">
							<div class="wds-admin-popup-input wds-admin-popup-input--has-arrow wds-admin-popup-input--date" >
								<label :data-date-label="decodeHtml(selectedDateLabel)" class="wds-admin-popup-masked-input">
									<?php echo esc_html_x( 'Delivery Date', 'admin', 'jckwds' ); ?>

									<input 
										type="text"
										@focus="timeslotFocus=true"
										@click="timeslotFocus=true"
										:value="formattedDate"
										/>

									<div class="wds-admin-popup-input-datepicker" v-show="timeslotFocus">
										<v-date-picker
											v-model="selectedDate" 
											@input="handleSelectDate"
											:available-dates="enabledDatesForDatepicker"
											:disabled-dates="disabledDates"
											:attributes="datepickerAttributes"
											ref="datepicker">
											<template #day-popover="{ day, dayTitle }">
												<div class="">
												{{ datePopover( day ) }} 
												</div>
											</template>
										</v-date-picker>
									</div>
								</label>
							</div>
						</div>
						<div class="wds-admin-popup-timeslot-picker__time" v-if="timeslotSetupEnable">
							<div class="wds-admin-popup-input wds-admin-popup-input--has-arrow wds-admin-popup-input--select">
								<label>
									<?php echo esc_html_x( 'Time Slot', 'admin', 'jckwds' ); ?>
									<select 
										v-model="selectedSlot"
										:disabled="! selectedDate"

									>
										<option value="" disabled v-if="! selectedDate"><?php echo esc_html_x( 'Please select a date first...', 'admin', 'jckwd' ); ?></option>
										<option value="" v-if="!overrideRules && ! selectedDateTimeslots && selectedDate && selectedDate.isValid()"><?php echo esc_html_x( 'No timeslots available for this date..', 'admin', 'jckwd' ); ?></option>
										<option 
											v-for="timeslot in selectedDateTimeslots"
											:value="timeslot.value"
											:key="timeslot.value"
											>
											{{decodeHtml( timeslot.formatted_with_fee )}}
										</option>
									</select>
								</label>
							</div>
						</div>
					</div>
				</div>
				<div class="wds-admin-popup-timeslot__not_required" v-if="selectedShippingMethod && !overrideRules && !dateTimeEnabledForSM(selectedShippingMethod)">
					<?php echo esc_html_x( 'This shipping method does not require a date/time to be selected.', 'admin', 'jckwds' ); ?>
				</div>

			</div>
			<button 
				type="button" 
				v-if="hasAddress || overrideRules"
				:disabled="timeslotSetupEnable ? (!selectedDate || !selectedSlot) : !selectedDate" 
				class="wds-admin-popup-btn--large wds-admin-popup-btn--footer button button-primary"
				@click="saveSlot"
				>
				<?php echo esc_html_x( 'Save Delivery Slot', 'admin', 'jckwds' ); ?>
			</button>
		</div>

	</div>
</div>
