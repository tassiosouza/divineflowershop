<?php
/**
 * WDS Settings.
 *
 * @package Iconic_WDS
 */

use Iconic_WDS\Settings;

add_filter( 'wpsf_register_settings_jckwds', 'jckwds_settings' );

/**
 * Delivery Slots Settings
 *
 * @param array $wpsf_settings Settings.
 *
 * @return array
 */
function jckwds_settings( $wpsf_settings ) {
	global $jckwds;

	if ( ! $jckwds || ! function_exists( 'WC' ) ) {
		return $wpsf_settings;
	}

	$date_format = Iconic_WDS_Core_Settings::get_setting_from_db( 'datesettings_datesettings', 'dateformat' );
	$date_format = $date_format ? $date_format : 'dd/mm/yy';

	// Tabs.

	$wpsf_settings['tabs'][] = array(
		'id'    => 'general',
		'title' => __( 'General Settings', 'jckwds' ),
	);

	$wpsf_settings['tabs'][] = array(
		'id'    => 'datesettings',
		'title' => __( 'Date Settings', 'jckwds' ),
	);

	$wpsf_settings['tabs'][] = array(
		'id'    => 'timesettings',
		'title' => __( 'Time Settings', 'jckwds' ),
	);

	$wpsf_settings['tabs'][] = array(
		'id'    => 'holidays',
		'title' => __( 'Holidays', 'jckwds' ),
	);

	$wpsf_settings['tabs'][] = array(
		'id'    => 'reservations',
		'title' => __( 'Reservation Table', 'jckwds' ),
	);

	$wpsf_settings['tabs'][] = array(
		'id'    => 'advance',
		'title' => __( 'Advanced', 'jckwds' ),
	);

	// Sections.

	$wpsf_settings['sections'][] = array(
		'tab_id'              => 'general',
		'section_id'          => 'setup',
		'section_title'       => __( 'General Setup', 'jckwds' ),
		'section_description' => '',
		'section_order'       => 0,
		'fields'              => array(
			array(
				'id'       => 'position',
				'title'    => __( 'Checkout Fields Position', 'jckwds' ),
				'subtitle' => __( 'Where should the date and time fields show on the checkout page?', 'jckwds' ),
				'type'     => 'select',
				'default'  => 'woocommerce_checkout_order_review',
				'choices'  => Settings::get_field_position_choices(),
			),
			array(
				'id'          => 'position_priority',
				'title'       => __( 'Checkout Fields Position Priority', 'jckwds' ),
				'subtitle'    => __( 'Enter a number of priority, e.g. 10 is early/before, 50 is late/after', 'jckwds' ),
				'type'        => 'number',
				'default'     => '10',
				'placeholder' => '',
			),
			array(
				'id'          => 'display_for_virtual',
				'title'       => __( 'Display Fields Even When Shipping Is Not Required?', 'jckwds' ),
				'subtitle'    => __( 'Should we display the date and time fields even if shipping is not required at checkout?', 'jckwds' ),
				'type'        => 'checkbox',
				'default'     => 0,
				'placeholder' => '',
			),
			array(
				'id'       => 'max_order_calculation_method',
				'title'    => __( 'Max Orders Calculation Method', 'jckwds' ),
				'subtitle' => __( 'Determine maximum orders limit by the number of orders placed or number of products purchased.', 'jckwds' ),
				'type'     => 'select',
				'default'  => 'orders',
				'choices'  => Settings::get_max_order_calculation_methods(),
			),
			array(
				'id'       => 'max_orders_exclude_products',
				'title'    => __( 'Exclude Products from Max Order Limit', 'jckwds' ),
				'subtitle' => __( 'Add product(s) the Max Orders Calculation will not be applied to.', 'jckwds' ),
				'type'     => 'custom',
				'default'  => array(),
				'output'   => Settings::get_products_search_field( 'general_setup', 'max_orders_exclude_products' ),
			),	
			array(
				'id'       => 'labels',
				'title'    => __( 'Default Labels', 'jckwds' ),
				'subtitle' => __( 'Select which labels to use for the delivery slots checkout fields. You can override these based on the select shipping method below.', 'jckwds' ),
				'type'     => 'select',
				'default'  => 'delivery',
				'choices'  => array(
					'delivery'   => __( 'Delivery', 'jckwds' ),
					'collection' => __( 'Collection', 'jckwds' ),
				),
			),
			array(
				'id'       => 'shipping_methods',
				'title'    => __( 'Shipping Methods', 'jckwds' ),
				'subtitle' => __( 'Enable delivery slots for the following shipping methods.', 'jckwds' ),
				'type'     => 'custom',
				'default'  => array( 'any' ),
				'output'   => Settings::get_field_labels_by_shipping_method(),
			),
			array(
				'id'       => 'exclude_products',
				'title'    => __( 'Exclude Products', 'jckwds' ),
				'subtitle' => __( 'If these products are in the cart, delivery date selection will be disabled.', 'jckwds' ),
				'type'     => 'custom',
				'default'  => array(),
				'output'   => Settings::get_products_search_field( 'general_setup', 'exclude_products' ),
			),
			array(
				'id'       => 'exclude_products_condition',
				'title'    => __( 'Exclude Product Condition', 'jckwds' ),
				'subtitle' => __( 'Disable delivery date selection when any/all of the excluded products are in the cart.', 'jckwds' ),
				'type'     => 'select',
				'choices'  => array(
					'any' => 'Any',
					'all' => 'All',
				),
				'default'  => 'any',
			),
			array(
				'id'          => 'exclude_categories',
				'title'       => __( 'Exclude Categories', 'jckwds' ),
				'subtitle'    => __( 'If a product from these categories is in the cart, delivery date selection will be disabled.', 'jckwds' ),
				'type'        => 'checkboxes',
				'default'     => '',
				'placeholder' => '',
				'choices'     => Settings::get_category_options(),
			),
			array(
				'id'       => 'exclude_categories_condition',
				'title'    => __( 'Exclude Category Condition', 'jckwds' ),
				'subtitle' => __( 'Disable delivery date selection when any/all products in the cart have at least 1 excluded category.', 'jckwds' ),
				'type'     => 'select',
				'choices'  => array(
					'any' => 'Any',
					'all' => 'All',
				),
				'default'  => 'any',
			),
		),
	);

	$wpsf_settings['sections'][] = array(
		'tab_id'              => 'general',
		'section_id'          => 'customer',
		'section_title'       => __( 'Customer Time Slot Management', 'jckwds' ),
		'section_description' => '',
		'section_order'       => 0,
		'fields'              => array(
			array(
				'id'          => 'display_on_thankyou_page',
				'title'       => __( 'Allow customers to update time slots from the Order Received page', 'jckwds' ),
				'subtitle'    => __( 'Adds updatable time slot fields to the Order Received/Thank You page after customers have placed an order.<br><br>Or only adds updatable time slot fields if the customer receives a reminder email. See Reminder Email settings below.', 'jckwds' ),
				'type'        => 'select',
				'choices'     => array(
					'always'         => esc_html__( 'Allow always', 'jckwds' ),
					'reminders_only' => esc_html__( 'Allow for reminder emails only', 'jckwds' ),
				),
				'default'     => 0,
				'placeholder' => '',
			),
			array(
				'id'          => 'display_on_myaccount_order_page',
				'title'       => __( 'Allow customers to update time slots from the My Account Order page.', 'jckwds' ),
				'subtitle'    => __( 'Adds updatable time slot fields to the customer’s \'My Account > Orders > View Order\' page.', 'jckwds' ),
				'type'        => 'select',
				'choices'     => array(
					'allow' => esc_html__( 'Allow', 'jckwds' ),
					'dont'  => esc_html__( "Don't Allow", 'jckwds' ),
				),
				'default'     => 'dont',
				'placeholder' => '',
			),
			array(
				'id'       => 'editing_window',
				'title'    => __( 'Time Slot Editing Window', 'jckwds' ),
				'subtitle' => __( 'Let customers edit their time slot selection up until X minutes/hours/days before their currently selected timeslot.<br><br>Leave the field empty to allow customers to update their timeslots anytime until the delivery time.', 'jckwds' ),
				'type'     => 'custom',
				'default'  => array(
					'number' => '1',
					'unit'   => 'days',
				),
				'output'   => Settings::get_field_timeslot_editing_window(),
			),
			array(
				'id'       => 'charge_fee_difference',
				'title'    => __( 'Charge Fee Difference', 'jckwds' ),
				'subtitle' => __( 'Require customers to pay a fee for the difference when the new timeslot\'s fee is more than the previously selected timeslot.', 'jckwds' ),
				'type'     => 'checkbox',
				'default'  => '1',
			),
			array(
				'id'       => 'threshold',
				'title'    => __( 'Fee Difference Threshold', 'jckwds' ),
				// Translators: The currency.
				'subtitle' => sprintf( __( 'Only prompt the customer to pay for the fee difference if the fee difference exceeds this amount (%s).', 'jckwds' ), get_woocommerce_currency() ),
				'type'     => 'number',
				'default'  => '1',
				'show_if'  => array(
					array(
						'field' => 'general_customer_charge_fee_difference',
						'value' => array( '1' ),
					),
				),
			),
		),
	);

	$wpsf_settings['sections'][] = array(
		'tab_id'              => 'general',
		'section_id'          => 'email_reminders',
		'section_title'       => __( 'Reminder Email', 'jckwds' ),
		'section_description' => '',
		'section_order'       => 0,
		'fields'              => array(
			array(
				'id'       => 'enable_reminders',
				'title'    => __( 'Enable Reminders', 'jckwds' ),
				'subtitle' => __( 'Turn on email reminders for customers who have not selected a delivery date/time during checkout.<br><br>Especially helpful for orders placed with express checkout payment methods like Google Pay, Apple Pay, and PayPal Express checkout.', 'jckwds' ),
				'type'     => 'checkbox',
			),
			array(
				'id'       => 'duration',
				'title'    => __( 'Reminder Frequency Duration', 'jckwds' ),
				'subtitle' => __( 'Set the frequency for sending automated email reminders to customers.', 'jckwds' ),
				'type'     => 'custom',
				'output'   => Settings::get_reminder_duration_fields(),
				'default'  => 12,
			),
			array(
				'id'       => 'max_emails',
				'title'    => __( 'Maximum Number of Reminders', 'jckwds' ),
				'subtitle' => __( 'Set the maximum number of email reminders sent to each customer.', 'jckwds' ),
				'type'     => 'number',
				'default'  => 3,
			),
			array(
				'id'       => 'email_text',
				'title'    => __( 'Email Text', 'jckwds' ),
				'subtitle' => __( 'Customize the content of the email reminder.<br><br>Available placeholders: {SITE_NAME}, {ORDER_ID}, {ORDER_NUMBER}, {ORDER_DATE_TIME}, {CUSTOMER_NAME}, {CUSTOMER_EMAIL}, {CUSTOMER_ADDRESS}, {CUSTOMER_PHONE}, {NOTE}, {CART_ITEMS}, {THANKYOU_URL} ', 'jckwds' ),
				'type'     => 'textarea',
				'default'  => "Hello {CUSTOMER_NAME}, thank you for your order.\n\nPlease select the delivery timeslot for your order #{ORDER_ID} by clicking on the URL below.\n\n{THANKYOU_URL}\n\nThanks,\n\n{SITE_NAME}",
			),
		),
	);

	$wpsf_settings['sections'][] = array(
		'tab_id'              => 'datesettings',
		'section_id'          => 'datesettings_setup',
		'section_title'       => __( 'Date Setup', 'jckwds' ),
		'section_description' => '',
		'section_order'       => 0,
		'fields'              => array(
			array(
				'id'          => 'mandatory',
				'title'       => __( 'Required', 'jckwds' ),
				'subtitle'    => __( 'Is the delivery date a required field at checkout?', 'jckwds' ),
				'type'        => 'checkbox',
				'default'     => 1,
				'placeholder' => '',
			),
			array(
				'id'          => 'show_description',
				'title'       => __( 'Show Description?', 'jckwds' ),
				'type'        => 'checkbox',
				'default'     => 0,
				'placeholder' => '',
			),
			array(
				'id'          => 'auto_select_first',
				'title'       => __( 'Auto-select first available date?', 'jckwds' ),
				'subtitle'    => __( 'The first available date will be automatically selected.', 'jckwds' ),
				'type'        => 'checkbox',
				'default'     => 0,
				'placeholder' => '',
			),
			array(
				'id'          => 'uitheme',
				'title'       => __( 'Theme', 'jckwds' ),
				'subtitle'    => __( 'Select a theme for the front-end calendar at checkout.', 'jckwds' ),
				'type'        => 'select',
				'default'     => 'dark',
				'placeholder' => '',
				'choices'     => array(
					'dark'  => __( 'Dark', 'jckwds' ),
					'light' => __( 'Light', 'jckwds' ),
					'none'  => __( 'None', 'jckwds' ),
				),
			),
		),
	);

	$wpsf_settings['sections'][] = array(
		'tab_id'              => 'datesettings',
		'section_id'          => 'datesettings',
		'section_title'       => __( 'Date Settings', 'jckwds' ),
		'section_description' => '',
		'section_order'       => 10,
		'fields'              => array(
			array(
				'id'       => 'days',
				'title'    => __( 'Delivery Days', 'jckwds' ),
				'subtitle' => __( 'Which days do you deliver, and how many orders can you accept on any given day? Leave maximum orders blank for "unlimited".', 'jckwds' ),
				'type'     => 'custom',
				'default'  => array( 0, 1, 2, 3, 4, 5, 6 ),
				'output'   => Settings::get_delivery_days_fields(),
			),
			array(
				'id'        => 'specific_days',
				'title'     => __( 'Specific Delivery Days', 'jckwds' ),
				'subtitle'  => __( 'Enter any specific days you would like to enable for delivery or collection.', 'jckwds' ),
				'type'      => 'group',
				'default'   => array(
					array(
						'row_id' => uniqid(),
					),
				),
				'subfields' => array(
					array(
						'id'         => 'date',
						'title'      => __( 'Date', 'jckwds' ),
						'type'       => 'date',
						'datepicker' => array(
							'dateFormat' => $date_format,
							'altFormat'  => 'yy-mm-dd',
							'altField'   => '#datesettings_datesettings_specific_days_0_alt_date',
						),
					),
					array(
						'id'    => 'alt_date',
						'title' => __( 'From', 'jckwds' ),
						'type'  => 'hidden',
					),
					array(
						'id'          => 'fee',
						// Translators: currency symbol.
						'title'       => sprintf( __( 'Fee (%s)', 'jckwds' ), get_woocommerce_currency_symbol() ),
						'subtitle'    => '',
						'type'        => 'number',
						'placeholder' => 'E.g. 3.00',
					),
					array(
						'id'       => 'lockout',
						'title'    => __( 'Maximum Orders', 'jckwds' ),
						'subtitle' => __( 'Enter the maximum number of orders allowed for this date.', 'jckwds' ),
						'type'     => 'number',
						'default'  => '',
					),
					array(
						'id'      => 'repeat_yearly',
						'title'   => '',
						'desc'    => __( 'Repeat every year?', 'jckwds' ),
						'type'    => 'checkbox',
						'default' => '',
					),
					array(
						'id'      => 'bypass_max',
						'title'   => '',
						'desc'    => __( 'Bypass max selectable date setting?', 'jckwds' ),
						'type'    => 'checkbox',
						'default' => '',
					),
				),
			),
			array(
				'id'          => 'minmaxmethod',
				'title'       => __( 'Delivery Days Calculation Method', 'jckwds' ),
				'subtitle'    => __( 'Calculate minimum, maximum, same day, and next day delivery dates based on all days of the week, selected days only, or weekdays only.', 'jckwds' ),
				'type'        => 'select',
				'default'     => 'allowed',
				'placeholder' => '',
				'choices'     => array(
					'allowed'  => 'Selected Days Only',
					'all'      => 'All Days',
					'weekdays' => 'Weekdays Only',
				),
			),
			array(
				'id'          => 'skip_current',
				'title'       => __( 'Skip Current Day if Not a Selected Delivery Day?', 'jckwds' ),
				'subtitle'    => __( 'When checked, same day delivery will be classed as the next available delivery day.', 'jckwds' ),
				'type'        => 'checkbox',
				'default'     => 0,
				'placeholder' => '',
			),
			array(
				'id'          => 'minimum',
				'title'       => __( 'Lead Time', 'jckwds' ),
				'subtitle'    => __( 'Days from now. Enter "0" for same day.', 'jckwds' ),
				'type'        => 'text',
				'default'     => '2',
				'placeholder' => '',
			),
			array(
				'id'          => 'maximum',
				'title'       => __( 'Maximum Selectable Date', 'jckwds' ),
				'subtitle'    => __( 'Days from now.', 'jckwds' ),
				'type'        => 'text',
				'default'     => '14',
				'placeholder' => '',
			),
			array(
				'id'         => 'sameday_cutoff',
				'title'      => __( 'Disable Same Day Delivery if Current Time is After (x)', 'jckwds' ),
				'type'       => 'time',
				'timepicker' => array(
					'amPmText' => array(
						__( 'AM', 'jckwds' ),
						__( 'PM', 'jckwds' ),
					),
				),
			),
			array(
				'id'         => 'nextday_cutoff',
				'title'      => __( 'Disable Next Day Delivery if Current Time is After (x)', 'jckwds' ),
				'type'       => 'time',
				'timepicker' => array(
					'amPmText' => array(
						__( 'AM', 'jckwds' ),
						__( 'PM', 'jckwds' ),
					),
				),
			),
			array(
				'id'          => 'week_limit',
				'title'       => __( 'Only Allow Deliveries Within the Current Week?', 'jckwds' ),
				'type'        => 'checkbox',
				'default'     => 0,
				'placeholder' => '',
			),
			array(
				'id'          => 'last_day_of_week',
				'title'       => __( 'Last Day of the Week', 'jckwds' ),
				'subtitle'    => '',
				'type'        => 'select',
				'placeholder' => '',
				'choices'     => array(
					'sunday'    => __( 'Sunday', 'jckwds' ),
					'monday'    => __( 'Monday', 'jckwds' ),
					'tuesday'   => __( 'Tuesday', 'jckwds' ),
					'wednesday' => __( 'Wednesday', 'jckwds' ),
					'thursday'  => __( 'Thursday', 'jckwds' ),
					'friday'    => __( 'Friday', 'jckwds' ),
					'saturday'  => __( 'Saturday', 'jckwds' ),
				),
				'default'     => 'sunday',
			),
			array(
				'id'          => 'dateformat',
				'title'       => __( 'Date Format', 'jckwds' ),
				'subtitle'    => __( 'Available formats can be found <a href="http://api.jqueryui.com/datepicker/#utility-formatDate" target="_blank">here</a>.', 'jckwds' ),
				'type'        => 'text',
				'default'     => 'dd/mm/yy',
				'placeholder' => '',
			),
		),
	);

	$wpsf_settings['sections'][] = array(
		'tab_id'              => 'datesettings',
		'section_id'          => 'fees',
		'section_title'       => __( 'Fees', 'jckwds' ),
		'section_description' => '',
		'section_order'       => 20,
		'fields'              => array(
			array(
				'id'       => 'days',
				'title'    => sprintf( '%s (%s)', __( 'Days', 'jckwds' ), get_woocommerce_currency_symbol() ),
				'subtitle' => __( 'Fees applied to specific days of the week.', 'jckwds' ),
				'type'     => 'custom',
				'default'  => array(),
				'output'   => Settings::get_day_fees_fields(),
			),
			array(
				'id'          => 'same_day',
				'title'       => sprintf( '%s (%s)', __( 'Same Day', 'jckwds' ), get_woocommerce_currency_symbol() ),
				'subtitle'    => __( 'Fee applied when a same day delivery is selected.', 'jckwds' ),
				'type'        => 'number',
				'default'     => '',
				'placeholder' => __( 'E.g. 3.00', 'jckwds' ),
			),
			array(
				'id'          => 'next_day',
				'title'       => sprintf( '%s (%s)', __( 'Next Day', 'jckwds' ), get_woocommerce_currency_symbol() ),
				'subtitle'    => __( 'Fee applied when a next day delivery is selected.', 'jckwds' ),
				'type'        => 'number',
				'default'     => '',
				'placeholder' => __( 'E.g. 3.00', 'jckwds' ),
			),
		),
	);

	$wpsf_settings['sections'][] = array(
		'tab_id'              => 'timesettings',
		'section_id'          => 'timesettings_setup',
		'section_title'       => __( 'Time Setup', 'jckwds' ),
		'section_description' => '',
		'section_order'       => 0,
		'fields'              => array(
			array(
				'id'          => 'enable',
				'title'       => __( 'Enable Time Slots', 'jckwds' ),
				'subtitle'    => __( 'Check this box to enable time slots at checkout.', 'jckwds' ),
				'type'        => 'checkbox',
				'default'     => 1,
				'placeholder' => '',
			),
			array(
				'id'          => 'mandatory',
				'title'       => __( 'Required', 'jckwds' ),
				'subtitle'    => __( 'Is the time slot a required field at checkout?', 'jckwds' ),
				'type'        => 'checkbox',
				'default'     => 1,
				'placeholder' => '',
			),
			array(
				'id'          => 'show_description',
				'title'       => __( 'Show Description?', 'jckwds' ),
				'type'        => 'checkbox',
				'default'     => 0,
				'placeholder' => '',
			),
			array(
				'id'          => 'auto_select_first',
				'title'       => __( 'Auto-select first available time slot?', 'jckwds' ),
				'subtitle'    => __( 'The first available time slot will be automatically selected.', 'jckwds' ),
				'type'        => 'checkbox',
				'default'     => 0,
				'placeholder' => '',
			),
			array(
				'id'          => 'timeformat',
				'title'       => __( 'Time Format', 'jckwds' ),
				'subtitle'    => __( 'Select a time format for the frontend.', 'jckwds' ),
				'type'        => 'select',
				'default'     => 'H:i A',
				'placeholder' => '',
				'choices'     => array(
					'H:i A' => '13:30 PM',
					'H:i'   => '13:30',
					'h:i A' => '01:30 PM',
				),
			),
		),
	);

	$wpsf_settings['sections'][] = array(
		'tab_id'              => 'timesettings',
		'section_id'          => 'timesettings_asap',
		'section_title'       => __( 'ASAP Delivery', 'jckwds' ),
		'section_description' => '',
		'section_order'       => 0,
		'fields'              => array(
			array(
				'id'          => 'enable',
				'title'       => __( 'Enable ASAP Delivery', 'jckwds' ),
				'subtitle'    => __( 'Allow your customers to choose ASAP for their selected delivery date, or a time slot.', 'jckwds' ),
				'type'        => 'checkbox',
				'default'     => 0,
				'placeholder' => '',
			),
			array(
				'id'       => 'lockout',
				'title'    => __( 'Maximum Orders', 'jckwds' ),
				'subtitle' => __( 'Enter the maximum number of orders allowed for the ASAP time slot.', 'jckwds' ),
				'type'     => 'number',
				'default'  => '',
			),
			array(
				'id'          => 'fee',
				'title'       => __( 'ASAP Fee', 'jckwds' ),
				'subtitle'    => __( 'Fee applied when an ASAP delivery time is selected.', 'jckwds' ),
				'type'        => 'number',
				'default'     => '',
				'placeholder' => __( 'E.g. 3.00', 'jckwds' ),
			),
			array(
				'id'         => 'cutoff',
				'title'      => __( 'Same Day Cut Off', 'jckwds' ),
				'subtitle'   => __( 'Disable same day ASAP delivery slot if current time is after (x).', 'jckwds' ),
				'type'       => 'time',
				'timepicker' => array(
					'amPmText' => array(
						__( 'AM', 'jckwds' ),
						__( 'PM', 'jckwds' ),
					),
				),
			),
		),
	);

	$wpsf_settings['sections']['timesettings'] = array(
		'tab_id'              => 'timesettings',
		'section_id'          => 'timesettings',
		'section_title'       => __( 'Time Slot Configuration', 'jckwds' ),
		'section_description' => '',
		'section_order'       => 10,
		'fields'              => array(
			array(
				'id'          => 'calculate_tax',
				'title'       => __( 'Calculate Tax?', 'jckwds' ),
				'subtitle'    => __( 'Check this box to calculate tax on time slot fees. If enabled, fees should be entered exclusive of tax.', 'jckwds' ),
				'type'        => 'checkbox',
				'default'     => 0,
				'placeholder' => '',
			),
			array(
				'id'          => 'cutoff',
				'title'       => __( 'Allow Bookings Up To (x) Minutes Before Slot', 'jckwds' ),
				'subtitle'    => __( 'This option will prevent bookings being made too close to the delivery time. Can be overridden on an individual time slot basis. (Check your timezone in WordPress Settings).', 'jckwds' ),
				'type'        => 'text',
				'default'     => '30',
				'placeholder' => '',
			),
			'timeslots' => array(
				'id'        => 'timeslots',
				'title'     => __( 'Time Slots', 'jckwds' ),
				'subtitle'  => __( 'Fill the Slot Duration and Slot Frequency fields to dynamically generate slots. Leave them empty to create a single time slot.<br><br>Use "24:00" to represent the end of the day.', 'jckwds' ),
				'type'      => 'group',
				'row_title' => __( 'Time Slot', 'jckwds' ),
				'format'    => 'table',
				'default'   => array(
					array(
						'row_id'           => uniqid(),
						'duration'         => 30,
						'frequency'        => 30,
						'timefrom'         => '07:00',
						'timeto'           => '10:00',
						'cutoff'           => '',
						'lockout'          => 4,
						'shipping_methods' => array( 'any' ),
						'fee'              => '',
						'days'             => array( 0, 1, 2, 3, 4, 5, 6 ),
					),
					array(
						'row_id'           => uniqid(),
						'duration'         => '',
						'frequency'        => '',
						'timefrom'         => '12:00',
						'timeto'           => '14:00',
						'cutoff'           => '',
						'lockout'          => 2,
						'shipping_methods' => array( 'any' ),
						'fee'              => 5,
						'days'             => array( 1, 5 ),
					),
				),
				'subfields' => array(
					array(
						'id'          => 'duration',
						'title'       => __( 'Slot Duration - (x) Minutes per Slot', 'jckwds' ),
						'subtitle'    => '',
						'type'        => 'number',
						'placeholder' => '',
					),
					array(
						'id'          => 'frequency',
						'title'       => __( 'Slot Frequency - Every (x) Minutes', 'jckwds' ),
						'subtitle'    => '',
						'type'        => 'number',
						'placeholder' => '',
					),
					array(
						'id'         => 'timefrom',
						'title'      => __( 'From', 'jckwds' ),
						'type'       => 'time',
						'timepicker' => array(
							'amPmText' => array(
								__( 'AM', 'jckwds' ),
								__( 'PM', 'jckwds' ),
							),
						),
					),
					array(
						'id'           => 'timeto',
						'title'        => __( 'To', 'jckwds' ),
						'subtitle'  => '24:00 can be used to represent end of the day.',
						'type'         => 'time',
						'timepicker'   => array(
							'amPmText' => array(
								__( 'AM', 'jckwds' ),
								__( 'PM', 'jckwds' ),
							),
						),
					),
					array(
						'id'          => 'cutoff',
						'title'       => __( 'Allow Bookings Up To (x) Minutes Before Slot', 'jckwds' ),
						'subtitle'    => '',
						'type'        => 'number',
						'placeholder' => '',
					),
					array(
						'id'       => 'cutoff_based_on',
						'title'    => __( 'Cutoff time based on', 'jckwds' ),
						'subtitle' => '',
						'type'     => 'select',
						'choices'  => array(
							'from' => __( 'From time', 'jckwds' ),
							'to'   => __( 'To time', 'jckwds' ),
						),
						'default'  => 'from',
					),
					array(
						'id'          => 'lockout',
						'title'       => __( 'Maximum Orders per Time Slot', 'jckwds' ),
						'subtitle'    => '',
						'type'        => 'number',
						'placeholder' => '',
					),
					'postcodes' => array(
						'id'          => 'postcodes',
						'title'       => __( 'Postcodes', 'jckwds' ),
						'type'        => 'text',
						'placeholder' => '',
					),
					array(
						'id'          => 'fee',
						// Translators: currency symbol.
						'title'       => sprintf( __( 'Fee (%s)', 'jckwds' ), get_woocommerce_currency_symbol() ),
						'subtitle'    => '',
						'type'        => 'number',
						'placeholder' => 'E.g. 3.00',
					),
					array(
						'id'          => 'days',
						'title'       => __( 'Days', 'jckwds' ),
						'subtitle'    => '',
						'type'        => 'checkboxes',
						'placeholder' => '',
						'default'     => array( 0, 1, 2, 3, 4, 5, 6 ),
						'choices'     => Settings::get_time_slot_day_choices(),
					),
				),
			),
		),
	);

	$wpsf_settings['sections'][] = array(
		'tab_id'              => 'holidays',
		'section_id'          => 'holidays',
		'section_title'       => __( 'Holidays', 'jckwds' ),
		'section_description' => __( 'Please add any holidays where deliveries should not be made.', 'jckwds' ),
		'section_order'       => 0,
		'fields'              => array(
			array(
				'id'        => 'holidays',
				'title'     => __( 'Holidays', 'jckwds' ),
				'subtitle'  => __( 'For single days, just enter a date in the "From" field. For ranges, enter a "From" and "To" date. Ranges are up to and including the dates you enter.', 'jckwds' ),
				'type'      => 'group',
				'row_title' => __( 'Holiday', 'jckwds' ),
				'format'    => 'table',
				'subfields' => array(
					array(
						'id'         => 'date',
						'title'      => __( 'From', 'jckwds' ),
						'type'       => 'date',
						'datepicker' => array(
							'dateFormat' => $date_format,
							'altFormat'  => 'dd/mm/yy',
							'altField'   => '#holidays_holidays_holidays_0_alt_date',
						),
					),
					array(
						'id'    => 'alt_date',
						'title' => __( 'From', 'jckwds' ),
						'type'  => 'hidden',
					),
					array(
						'id'         => 'date_to',
						'title'      => __( 'To', 'jckwds' ),
						'type'       => 'date',
						'datepicker' => array(
							'dateFormat' => $date_format,
							'altFormat'  => 'dd/mm/yy',
							'altField'   => '#holidays_holidays_holidays_0_alt_date_to',
						),
					),
					array(
						'id'    => 'alt_date_to',
						'title' => __( 'to', 'jckwds' ),
						'type'  => 'hidden',
					),
					array(
						'id'          => 'shipping_methods',
						'title'       => __( 'Shipping Methods', 'jckwds' ),
						'subtitle'    => '',
						'type'        => 'checkboxes',
						'placeholder' => '',
						'choices'     => $jckwds->get_shipping_method_options(),
					),
					array(
						'id'          => 'name',
						'title'       => __( 'Name', 'jckwds' ),
						'subtitle'    => '',
						'type'        => 'text',
						'default'     => '',
						'placeholder' => __( 'e.g. Christmas', 'jckwds' ),
					),
					array(
						'id'      => 'repeat_yearly',
						'title'   => '',
						'desc'    => __( 'Repeat every year?', 'jckwds' ),
						'type'    => 'checkbox',
						'default' => '',
					),
				),
			),
		),
	);

	$wpsf_settings['sections'][] = array(
		'tab_id'              => 'reservations',
		'section_id'          => 'reservations',
		'section_title'       => __( 'Reservations', 'jckwds' ),
		'section_description' => __( 'You can insert a reservation table using the shortcode <strong>[jckwds]</strong>. This allows your customers to reserve a delivery time and date while they shop. <strong>Note:</strong> Time Slots should be enabled if you want to use the reservation table.', 'jckwds' ),
		'section_order'       => 0,
		'fields'              => array(
			array(
				'id'          => 'expires',
				'title'       => __( 'Expiration', 'jckwds' ),
				'subtitle'    => __( 'Reservations expire after (x) Minutes.', 'jckwds' ),
				'type'        => 'text',
				'default'     => '30',
				'placeholder' => '30',
			),
			array(
				'id'          => 'remaining_label_threshold',
				'title'       => __( 'Remaining Label Threshold', 'jckwds' ),
				'subtitle'    => __( 'Show "x Remaining" label when available timeslots become less than this threshold. Set -1 to disable.', 'jckwds' ),
				'type'        => 'text',
				'default'     => '2',
				'placeholder' => '2',
			),
			array(
				'id'          => 'hide_unavailable_dates',
				'title'       => __( 'Hide Unavailable Dates?', 'jckwds' ),
				'subtitle'    => __( 'Check this box to hide any dates if there are no available time slots.', 'jckwds' ),
				'type'        => 'checkbox',
				'default'     => 0,
				'placeholder' => '',
			),
			array(
				'id'          => 'redirect_url',
				'title'       => __( 'Redirect URL', 'jckwds' ),
				'subtitle'    => __( 'Redirect to this URL after booking a slot.', 'jckwds' ),
				'type'        => 'text',
				'default'     => '',
				'placeholder' => 'https://',
			),
			array(
				'id'       => 'highlight_color',
				'title'    => __( 'Highlight Color', 'jckwds' ),
				'subtitle' => '',
				'type'     => 'color',
				'default'  => '#96588A',
			),
			array(
				'id'       => 'earliest_slot_color',
				'title'    => __( 'Color for "Earliest Available Slot" label', 'jckwds' ),
				'subtitle' => '',
				'type'     => 'color',
				'default'  => '#34A855',
			),
			array(
				'id'       => 'slots_remaining_color',
				'title'    => __( 'Color for "X Slots remaining" label', 'jckwds' ),
				'subtitle' => '',
				'type'     => 'color',
				'default'  => '#a9b2ab',
			),
			array(
				'id'       => 'slots_unavailable_color',
				'title'    => __( 'Color for "Slot Unavailable" label', 'jckwds' ),
				'subtitle' => '',
				'type'     => 'color',
				'default'  => '#EF5350',
			),
		),
	);

	$wpsf_settings['sections'][] = array(
		'tab_id'              => 'advance',
		'section_id'          => 'importexport',
		'section_title'       => __( 'Import/Export', 'jckwds' ),
		'section_description' => '',
		'section_order'       => 0,
		'fields'              => array(
			array(
				'id'       => 'import',
				'title'    => __( 'Import', 'jckwds' ),
				'subtitle' => __( 'Import settings', 'jckwds' ),
				'type'     => 'import',
			),
			array(
				'id'       => 'export',
				'title'    => __( 'Export', 'jckwds' ),
				'subtitle' => __( 'Export settings', 'jckwds' ),
				'type'     => 'export',
			),
		),
	);

	if ( class_exists( 'WC_Shipping_Zones' ) ) {
		$wpsf_settings['sections']['timesettings']['fields']['timeslots']['subfields']['postcodes'] = array(
			'id'          => 'shipping_methods',
			'title'       => __( 'Shipping Methods', 'jckwds' ),
			'subtitle'    => '',
			'type'        => 'checkboxes',
			'placeholder' => '',
			'choices'     => $jckwds->get_shipping_method_options(),
		);
	}

	return $wpsf_settings;
}
