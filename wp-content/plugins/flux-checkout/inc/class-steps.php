<?php
/**
 * Iconic_Flux_Steps.
 *
 * Handle the steps.
 *
 * @package Iconic_Flux
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Iconic_Flux_Steps.
 *
 * Register/enqueue frontend and backend scripts.
 *
 * @class    Iconic_Flux_Steps
 * @version  1.0.0
 */
class Iconic_Flux_Steps {

	/**
	 * Render header.
	 *
	 * @since 2.0.0
	 */
	public static function render_header( $show_breadcrumps = true ) {
		/**
		 * Before Header.
		 *
		 * @since 2.0.0
		 */
		do_action( 'flux_before_header' );
		?>
		<header class="flux-checkout__header header">
			<div class="header__inner">
				<?php
				/**
				 * Before header link.
				 *
				 * @since 2.21.0
				 */
				do_action( 'flux_before_header_link' );

				/**
				 * Allows you to override the hyperlink on the header logo.
				 *
				 * @since 2.0.2
				 */
				$back_url = apply_filters( 'flux_checkout_logo_href', esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ) );
				?>
				<a class="header__link" href="<?php echo esc_url( $back_url ); ?>">
					<?php if ( Iconic_Flux_Helpers::get_header_text() ) { ?>
						<h1 class="header__title entry-title"><?php echo esc_html( Iconic_Flux_Helpers::get_header_text() ); ?></h1>
					<?php } else { ?>
						<?php $width = Iconic_Flux_Helpers::get_logo_width(); ?>
						<img
							class="header__image"
							src="<?php echo esc_url( Iconic_Flux_Helpers::get_logo_image() ); ?>"
							<?php echo ! empty( $width ) ? 'style="width:' . esc_attr( $width ) . 'px"' : ''; ?>
						/>
					<?php } ?>
				</a>
				<?php
				/**
				 * After header link.
				 *
				 * @since 2.21.0
				 */
				do_action( 'flux_after_header_link' );
				?>
			</div>
			<?php
			if ( $show_breadcrumps ) {
				self::render_stepper();
			}
			?>
		</header>

		<?php
		/**
		 * After header.
		 *
		 * @since 2.0.0
		 */
		do_action( 'flux_after_header' );
	}

	/**
	 * Render Stepper.
	 */
	public static function render_stepper() {
		if ( Iconic_Flux_Core::is_thankyou_page() ) {
			return;
		}

		$steps = self::get_steps();

		if ( empty( $steps ) ) {
			return;
		}

		?>
		<nav class="flux-stepper">
			<ul>
				<?php
				foreach ( $steps as $key => $step ) {
					$enabled = 0 !== $key ? ' disabled' : ' selected';
					?>
					<li data-stepper-li="<?php echo esc_attr( $key + 1 ); ?>" class="flux-stepper__step flux-stepper__step--<?php echo esc_attr( $key + 1 ); ?> stepper__step--<?php echo esc_attr( $step['slug'] ); ?> <?php echo esc_attr( $enabled ); ?>">
						<button type="button" class="flux-stepper__button" data-stepper="<?php echo esc_attr( $key + 1 ); ?>" data-step-show="<?php echo esc_attr( $key + 2 ); ?>"<?php echo esc_attr( $enabled ); ?> data-hash="<?php echo esc_attr( $step['slug'] ); ?>">
							<span class="flux-stepper__indicator">
								<?php echo esc_html( $key + 1 ); ?>
							</span>
							<span class="flux-stepper__title">
									<?php echo esc_html( $step['title'] ); ?>
							</span>
						</button>
					</li>
					<?php
				}
				?>
			</ul>
		</nav>
		<?php
	}

	/**
	 * Render Steps.
	 */
	public static function render_steps() {
		$checkout          = WC()->checkout;
		$settings          = Iconic_Flux_Core_Settings::$settings;
		$theme             = Iconic_Flux_Core::get_theme();
		$show_back_to_shop = '0' !== $settings['general_general_show_back_to_shop_btn'];

		$steps = self::get_steps();

		if ( empty( $steps ) ) {
			return;
		}

		foreach ( $steps as $key => $step ) {
			?>
			<section data-step="<?php echo esc_attr( $key + 1 ); ?>" class="flux-step flux-step--<?php echo esc_attr( $key + 1 ); ?> flux-step--<?php echo esc_attr( $step['slug'] ); ?>" <?php echo 0 !== $key ? 'style="display:none;" aria-hidden="true"' : ''; ?>>
				<?php
				// @todo: should these be blocks, so they can be placed by the customer?
				if ( 0 === $key ) {
					/**
					 * Before customer details.
					 *
					 * @since 2.0.0
					 */
					do_action( 'woocommerce_checkout_before_customer_details' );

					/**
					 * Before checkout billing form.
					 *
					 * @param WC_Checkout $checkout Checkout object.
					 *
					 * @since 2.0.0
					 */
					do_action( 'woocommerce_before_checkout_billing_form', $checkout );
				}
				?>
				<div class="flux-step__content">
					<?php
						/**
						 * Before step content.
						 *
						 * @since 2.3.0
						 */
						do_action( 'flux_before_step_content', $step );
						do_action( 'flux_before_step_content_' . $step['slug'], $step );

						call_user_func( $step['callback'] );

						/**
						 * After step content.
						 *
						 * @since 2.3.0
						 */
						do_action( 'flux_after_step_content', $step );
						do_action( 'flux_after_step_content_' . $step['slug'], $step );
					?>
				</div>
				<?php
				// Render before last step.
				if ( count( $steps ) - 2 === $key ) {
					/**
					 * After customer details.
					 *
					 * @since 2.0.0
					 */
					do_action( 'woocommerce_checkout_after_customer_details' );
				}
				if ( count( $steps ) - 1 !== $key ) {
					if ( 'classic' === $theme ) {
						?>
						<button class="flux-button--step flux-button" data-step-next data-step-show="<?php echo esc_attr( $key + 2 ); ?>">
							<?php esc_html_e( 'Continue', 'flux-checkout' ); ?>
						</button>
						<?php
					} else {
						?>
						<footer class="flux-footer <?php echo ( ! $show_back_to_shop && 'details' === $step['slug'] ) ? 'flux-footer--no-back-shop' : ''; ?>">
							<?php self::back_button( $step['slug'] ); ?>
							<button class="flux-button" data-step-next data-step-show="<?php echo esc_attr( $key + 2 ); ?>">
								<?php esc_html_e( 'Continue to', 'flux-checkout' ); ?>&nbsp;<?php echo esc_html( $steps[ $key + 1 ]['title'] ); ?>
							</button>
						</footer>
						<?php
					}
				}
				/**
				 * After step.
				 *
				 * @since 2.21.0
				 */
				do_action( 'flux_after_step', $step );

				/**
				 * After step.
				 *
				 * @since 2.21.0
				 */
				do_action( 'flux_after_step_' . $step['slug'], $step );
				?>
			</section>
			<?php
		}
	}

	/**
	 * Get Steps.
	 *
	 * Currently returns steps from an array. In the future this will support
	 * steps being defined via sub-pages of the checkout page.
	 *
	 * @return array
	 */
	public static function get_steps() {
		// @todo: in Gutenberg `title` (which is the step title) will come from the page title.
		// @todo: the inline page title will come from the title block.
		$steps = array(
			array(
				'callback' => array( __CLASS__, 'render_default_customer_details' ),
				'slug'     => 'details',
				'title'    => esc_html__( 'Details', 'flux-checkout' ),
				'post_id'  => 0,
			),
			array(
				'callback' => array( __CLASS__, 'render_default_billing_address' ),
				'slug'     => 'address',
				'title'    => esc_html__( 'Address', 'flux-checkout' ),
				'post_id'  => 0,
			),
			array(
				'callback' => array( __CLASS__, 'render_payment_details' ),
				'slug'     => 'payment',
				'title'    => esc_html__( 'Payment', 'flux-checkout' ),
				'post_id'  => 0,
			),
		);

		/**
		 * Filters the Custom Steps.
		 *
		 * @param array $steps Steps.
		 *
		 * @return array
		 *
		 * @since 2.0.0
		 */
		return apply_filters( 'flux_custom_steps', $steps );
	}

	/**
	 * Get Default Billing Address.
	 *
	 * Get the billing address when page has not been defined.
	 */
	public static function render_default_customer_details() {
		$checkout = WC()->checkout;
		$settings = Iconic_Flux_Core_Settings::$settings;
		$theme    = Iconic_Flux_Core::get_theme();

		if ( 'classic' === $theme ) {
			// @todo this needs turning into a dynamic block when Gutenberg integration is built.
			self::render_login_button();
		}

		// @todo: this will be a title block.

		$has_login_btn_class = ( ! is_user_logged_in() && 'no' !== get_option( 'woocommerce_enable_checkout_login_reminder' ) ) ? 'flux-heading--has-login-btn' : '';
		?>
		<h2 class="flux-heading flux-heading--customer-details  <?php echo esc_attr( $has_login_btn_class ); ?>"><?php echo esc_html__( 'Customer Details', 'flux-checkout' ); ?></h2>
		<?php

		if ( 'classic' !== $theme ) {
			// @todo this needs turning into a dynamic block when Gutenberg integration is built.
			self::render_modern_login_button();
		}

		// @todo dynamic block needed for each type of Woo field, plus additional custom fields.
		foreach ( Iconic_Flux_Helpers::get_details_fields( $checkout ) as $key => $field ) {
			woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
		};

		// @todo this needs turning into a dynamic block when Gutenberg integration is built.
		self::render_account_form( $checkout );
	}

	/**
	 * Get Billing Address.
	 *
	 * Get the billing address when page has not been defined.
	 */
	public static function render_default_billing_address() {
		$checkout = WC()->checkout;

		$billing_title   = esc_html__( 'Billing Address', 'flux-checkout' );
		$is_modern_theme = Iconic_Flux_Helpers::is_modern_theme();
		$destination     = get_option( 'woocommerce_ship_to_destination' );

		if ( wc_ship_to_billing_address_only() && WC()->cart->needs_shipping() ) {
			$billing_title = esc_html__( 'Billing &amp; Shipping Address', 'flux-checkout' );
		} elseif ( 'shipping' === $destination ) {
			$billing_title = esc_html__( 'Shipping Address', 'flux-checkout' );
		}

		if ( $is_modern_theme ) {
			self::render_customer_review();
		}

		?>
		<h2 class="flux-heading flux-heading--billing"><?php echo esc_html( $billing_title ); ?></h2>
		<?php

		switch ( $destination ) {
			case 'shipping':
				self::render_shipping_fields();
				self::render_billing_fields();
				break;

			case 'billing':
				self::render_billing_fields();
				self::render_shipping_fields();
				break;

			case 'billing_only':
				self::render_billing_fields();
				break;
		}
		?>

		<table class="flux-checkout__shipping-table">
			<tbody></tbody>
		</table>

		<?php
		// @todo: this will be a wrapper block that will contain fields (additional fields form wrapper block,
		// with option for show hide (default text is order notes) search. Fields can only be inserted into
		// wrapper, so show hide works correctly).

		/**
		 * Enable order notes field.
		 *
		 * @since 2.0.0
		 */
		if ( apply_filters( 'woocommerce_enable_order_notes_field', true ) ) {
			?>
			<div class="woocommerce-additional-fields__wrapper">
				<?php
					/**
					 * Before order notes.
					 *
					 * @since 2.3.3
					 */
					do_action( 'woocommerce_before_order_notes', $checkout );
				?>
				<h3 id="show-additional-fields">
					<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
						<input id="show-additional-fields-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" type="checkbox" name="show_additional_fields" value="1" />
						<span class="toggle__ie11"></span>
						<span><?php esc_html_e( 'Order notes?', 'flux-checkout' ); ?></span>
					</label>
				</h3>
				<div class="woocommerce-additional-fields" style="display:none;" aria-hidden="true">
					<div class="woocommerce-additional-fields__field-wrapper">
						<?php
						// @todo dynamic block needed for each type of Woo field, plus additional custom fields.
						foreach ( $checkout->checkout_fields['order'] as $key => $field ) {
							woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
						};
						?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * After order notes.
		 *
		 * @since 1.4.0
		 */
		do_action( 'woocommerce_after_order_notes', $checkout );
	}

	/**
	 * Render shipping fields.
	 */
	/**
	 * Render shipping fields.
	 */
	public static function render_shipping_fields() {
		$checkout = WC()->checkout;

		$destination = get_option( 'woocommerce_ship_to_destination' );

		?>
		<div class="woocommerce-shipping-fields__wrapper">
			<?php if ( true === WC()->cart->needs_shipping_address() ) : ?>
				<?php if ( 'shipping' !== $destination ) { ?>
					<h3 id="ship-to-different-address">
						<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
							<?php
							/**
							 * Ship to a different address checked by default.
							 *
							 * @since 2.4.0
							 */
							$checked = checked( apply_filters( 'woocommerce_ship_to_different_address_checked', 'shipping' === get_option( 'woocommerce_ship_to_destination' ) ? 1 : 0 ), 1, false );
							?>
							<input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" type="checkbox" name="ship_to_different_address" value="1" <?php echo esc_attr( $checked ); ?>/>
							<span class="toggle__ie11"></span>
							<span><?php esc_html_e( 'Ship to a different address?', 'flux-checkout' ); ?></span>
						</label>
					</h3>

				<?php } ?>
				<div class="shipping_address">
					<?php self::render_shipping_address_search(); ?>
					<div class="woocommerce-shipping-fields">
						<div class="woocommerce-shipping-fields__fields-wrapper">
							<p class="flux-address-button-wrapper flux-address-button-wrapper--shipping-lookup">
								<button class="flux-address-button flux-address-button--lookup flux-address-button--shipping-lookup">
									<?php esc_attr_e( 'Look up address', 'flux-checkout' ); ?>
								</button>
							</p>
							<?php

							// @todo dynamic block needed for each type of Woo field, plus additional custom fields.
							foreach ( Iconic_Flux_Helpers::get_shipping_fields( $checkout ) as $key => $field ) {
								woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
							};

							?>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render billing fields.
	 *
	 * @return void
	 */
	public static function render_billing_fields() {
		/**
		 * After Billing address heading.
		 *
		 * @since 2.4.0
		 */
		do_action( 'flux_before_billing_address_heading' );

		$checkout                 = WC()->checkout;
		$destination              = get_option( 'woocommerce_ship_to_destination' );
		$use_same_billing_address = Iconic_Flux_Core::use_same_billing_address();
		// @todo: this will be a wrapper block that will contain fields (billing form wrapper block,
		// with option for address search. Fields can only be inserted into wrapper, so show hide works correctly).
		?>
		<div class="woocommerce-billing-fields__wrapper">
			<?php if ( 'shipping' === $destination ) { ?>
				<h3 id="same-billing-address">
					<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
						<?php
						/**
						 * Ship to a different address checked by default.
						 *
						 * @since 2.4.0
						 */
						$checked = checked( $use_same_billing_address, false, false );
						?>
						<input id="same-billing-address-input" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" type="checkbox" name="billing_same_billing_address" value="1" <?php echo esc_attr( $checked ); ?>/>
						<span class="toggle__ie11"></span>
						<span><?php esc_html_e( 'Use same address for billing?', 'flux-checkout' ); ?></span>
					</label>
				</h3>
				<div class="flux-hidden" id="ship-to-different-address">
					<!-- 
					When ship-to-different-address>input is checked, then WooCommerce (see `update_checkout_action` function in woocommerce/assets/js/frontend/checkout.js)
					will use the shipping address to determine the shipping package (shipping methods).

					And because we have implemented `same-billing-address-input` which doesn't exist in Woo by default
					We need to always keep this checked when the `woocommerce_ship_to_destination` is set to `shipping`
					-->
					<input 
						type="checkbox"
						value="1"
						checked="checked"
					>
				</div>

			<?php } ?>

			<div class="billing_address">
				<?php if ( 'shipping' === $destination ) { ?>
				<h2 class="flux-heading flux-heading--billing"><?php esc_html_e( 'Billing address', 'flux-checkout' ); ?></h2>
				<?php } ?>
				<?php self::render_address_search(); ?>
				<div class="woocommerce-billing-fields">
					<div class="woocommerce-billing-fields__fields-wrapper">
						<p class="flux-address-button-wrapper flux-address-button-wrapper--billing-lookup">
							<button class="flux-address-button flux-address-button--lookup flux-address-button--billing-lookup">
								<?php esc_attr_e( 'Look up address', 'flux-checkout' ); ?>
							</button>
						</p>
						<?php
						// @todo dynamic block needed for each type of Woo field, plus additional custom fields.
						foreach ( Iconic_Flux_Helpers::get_billing_fields( $checkout ) as $key => $field ) {
							woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
						};

						?>
						<div class="clear"></div>
					</div>
				</div>
			</div>
		</div>

		<?php
		// @todo: this will be a wrapper block that will contain fields (shipping form wrapper block,
		// with option for address search. Fields can only be inserted into wrapper, so show hide works correctly).
		/**
		 * After checkout billing form.
		 *
		 * @param WC_Checkout $checkout Checkout object.
		 *
		 * @since 2.0.0
		 */
		do_action( 'woocommerce_after_checkout_billing_form', $checkout );
	}

	/**
	 * Get Payment Details.
	 *
	 * Get the payment details when page has not been defined.
	 */
	public static function render_payment_details() {
		$is_modern_theme = Iconic_Flux_Helpers::is_modern_theme();

		// @todo create payment form wrapper block so actions are able to file.

		/**
		 * Before checkout order review heading.
		 *
		 * @since 2.0.0
		 */
		do_action( 'woocommerce_checkout_before_order_review_heading' );
		?>

		<?php
		// @todo: this will be a title block.
		if ( $is_modern_theme ) {
			self::render_customer_review();
			?>
			<h2 class="flux-heading flux-heading--payment"><?php echo esc_html_x( 'Payment', 'Modern theme: payment heading', 'flux-checkout' ); ?></h2>
			<?php
		}

		// @todo create a block component for show/hide coupon code.
		if ( ! Iconic_Flux_Sidebar::is_sidebar_enabled() ) {
			self::render_coupon_form();
		}
		?>

		<?php
		// @todo: this will be a title block.
		$heading_class = Iconic_Flux_Sidebar::is_sidebar_enabled() ? '' : 'flux-heading--order-review';
		?>
		<h2 class="flux-heading <?php echo esc_attr( $heading_class ); ?>" id="order_review_heading">
			<?php esc_html_e( 'Payment', 'flux-checkout' ); ?>
		</h2>

		<?php
		/**
		 * Before checkout order review.
		 *
		 * @since 2.0.0
		 */
		do_action( 'woocommerce_checkout_before_order_review' );
		?>

		<div id="order_review" class="woocommerce-checkout-review-order">
			<?php
			/**
			 * Checkout order review.
			 *
			 * @since 2.0.0
			 */
			do_action( 'woocommerce_checkout_order_review' );
			?>
		</div>

		<?php
		/**
		 * After checkout order review.
		 *
		 * @since 2.0.0
		 */
		do_action( 'woocommerce_checkout_after_order_review' );
	}

	/**
	 * Render Login Button.
	 */
	public static function render_login_button() {
		if ( is_user_logged_in() || 'no' === get_option( 'woocommerce_enable_checkout_login_reminder' ) ) {
			return;
		}

		?>
		<button class="flux-checkout__login-button login-button" data-login type="button">
			<?php esc_html_e( 'Returning Customer?', 'flux-checkout' ); ?>
		</button>
		<?php
	}

	/**
	 * Render Login Button.
	 */
	public static function render_modern_login_button() {
		if ( is_user_logged_in() || 'no' === get_option( 'woocommerce_enable_checkout_login_reminder' ) ) {
			return;
		}

		?>
		<p class="flux-checkout__login"><?php esc_html_e( 'Already have an account?', 'flux-checkout' ); ?>
			<button class="flux-checkout__login-button login-button" data-login type="button"><?php esc_html_e( 'Log in', 'flux-checkout' ); ?> </button>
		</p>
		<?php
	}

	/**
	 * Render Account Form.
	 *
	 * @param object $checkout Checkout.
	 */
	public static function render_account_form( $checkout ) {
		if ( is_user_logged_in() || ! $checkout->enable_signup ) {
			return;
		}

		$style = $checkout->enable_guest_checkout ? 'display:none;' : 'display:block;'

		?>
		<div class="woocommerce-account-fields">
			<?php if ( $checkout->enable_guest_checkout ) : ?>

				<p class="form-row form-row-wide create-account">
					<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
						<?php
						/**
						 * Check the create account toggle by default.
						 *
						 * @param bool $checked Checked.
						 *
						 * @since 2.0.0
						 */
						$woocommerce_create_account_default_checked = apply_filters( 'woocommerce_create_account_default_checked', false );
						?>
						<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === $woocommerce_create_account_default_checked ) ), true ); ?> type="checkbox" name="createaccount" value="1" /><span class="toggle__ie11"></span> <span><?php esc_html_e( 'Create an account?', 'flux-checkout' ); ?></span>
					</label>
				</p>

			<?php endif; ?>

			<?php
			/**
			 * Before checkout registration form.
			 *
			 * @param WC_Checkout $checkout Checout object.
			 *
			 * @since 2.0.0
			 */
			do_action( 'woocommerce_before_checkout_registration_form', $checkout );
			?>

			<?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>

				<div class="create-account" style="<?php echo esc_attr( $style ); ?>">
					<p class="flux-text flux-text--subtle">
						<?php
						if ( 'no' === get_option( 'woocommerce_enable_checkout_login_reminder' ) ) {
							if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) {
								// Translators: %1$s = Opening login link, %2$s = Closing login link.
								printf( esc_html__( 'Create an account by entering the information below. If you are a returning customer please %1$slogin on your account page%2$s.', 'flux-checkout' ), '<a href="' . esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ) . '">', '</a>' );
							} else {
								esc_html_e( 'Create an account by entering the information below. If you are a returning customer please login.', 'flux-checkout' );
							}
						} else {
							esc_html_e( 'Create an account by entering the information below. If you are a returning customer please login at the top of the page.', 'flux-checkout' );
						}
						?>
					</p>
					<?php foreach ( $checkout->get_checkout_fields( 'account' ) as $key => $field ) : ?>
						<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
					<?php endforeach; ?>
					<div class="clear"></div>
				</div>

			<?php endif; ?>

			<?php
			/**
			 * After checkout registration form.
			 *
			 * @since 2.0.0
			 */
			do_action( 'woocommerce_after_checkout_registration_form', $checkout );
			?>
		</div>
		<?php
	}

	/**
	 * Render customer details review section.
	 *
	 * @param string $customer_details Customer Details.
	 */
	public static function render_customer_review( $customer_details = false ) {
		if ( empty( $customer_details ) ) {
			$customer_details = self::get_customer_details();
		}

		?>
		<div class="flux-review-customer flux-review-customer--checkout">
			<?php if ( ! empty( $customer_details['email'] ) ) { ?>
				<div class="flux-review-customer__row flux-review-customer__row--contact">
					<div class="flux-review-customer__label flux-review-customer__label">
						<label><?php esc_html_e( 'Contact', 'flux-checkout' ); ?></label>
					</div>
					<div class="flux-review-customer__content">
						<p><?php echo wp_kses( $customer_details['email'], Iconic_Flux_Helpers::get_kses_allowed_tags() ); ?></p>
					</div>
					<div class="flux-review-customer__buttons">
						<a href="#details|billing_first_name_field" data-stepper-goto='1'><?php esc_html_e( 'Edit', 'flux-checkout' ); ?></a>
					</div>
				</div>
			<?php } ?>
			<?php if ( ! empty( $customer_details['address'] ) ) { ?>
				<div class="flux-review-customer__row flux-review-customer__row--address">
					<div class="flux-review-customer__label">
						<label><?php esc_html_e( 'Address', 'flux-checkout' ); ?></label>
					</div>
					<div class="flux-review-customer__content flux-review-customer__content--address">
						<p><?php echo wp_kses( $customer_details['address'], Iconic_Flux_Helpers::get_kses_allowed_tags() ); ?></p>
					</div>
					<div class="flux-review-customer__buttons">
						<a href="#address|billing_country" data-stepper-goto='2'><?php esc_html_e( 'Edit', 'flux-checkout' ); ?></a>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render Address Search.
	 */
	public static function render_address_search() {
		$is_modern_theme  = Iconic_Flux_Helpers::is_modern_theme();
		$is_pre_populated = Iconic_Flux_Helpers::has_prepopulated_fields( 'billing' );

		// @todo billing form wrapper block, with option for address search. Fields can only be inserted into wrapper, so show hide works correctly.
		if ( Iconic_Flux_Helpers::use_autocomplete() || ( Iconic_Flux_Helpers::use_autocomplete() && ! $is_pre_populated ) ) {
			?>
			<div class="billing-address-search<?php echo $is_pre_populated ? ' billing-address-search--pre-populated' : ''; ?>">
				<p class="flux-address-search__hint">
					<?php esc_html_e( 'Start typing your address to search.', 'flux-checkout' ); ?>
					<span class="flux-tooltip" for="billing-address-info" aria-describedby="billing-address-info">
						<i class="flux-tooltip__icon" role="tooltip">
							<?php esc_html_e( 'Info', 'flux-checkout' ); ?>
						</i>
						<span class="flux-tooltip__tip" id="billing-address-info">
							<?php esc_html_e( 'Start with your house/apartment number then your street address.', 'flux-checkout' ); ?>
						</span>
					</span>
				</p>
				<p class="form-row form-row-wide is-active" id="billing_address_info">
					<label for="billing_address_search">
						<?php esc_html_e( 'Address', 'flux-checkout' ); ?>
					</label>
					<span class="woocommerce-input-wrapper" id="billing_address_search">
					</span>
					<span class="error"><?php esc_html_e( 'Please enter your address', 'flux-checkout' ); ?></span>
				</p>
				<p class="flux-address-button-wrapper flux-address-button-wrapper--billing-manual">
					<button class="flux-address-button flux-address-button--manual flux-address-button--billing-manual" id="billing_address_not_found">
						<?php esc_attr_e( 'Enter Address Manually', 'flux-checkout' ); ?>
					</button>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Render Shipping Search.
	 */
	public static function render_shipping_address_search() {
		$settings         = Iconic_Flux_Core_Settings::$settings;
		$is_modern        = isset( $settings['styles_theme_choose_theme'] ) && 'modern' === $settings['styles_theme_choose_theme'];
		$is_pre_populated = Iconic_Flux_Helpers::has_prepopulated_fields( 'shipping' );

		// @todo shipping form wrapper block, with option for address search. Fields can only be inserted into wrapper, so show hide works correctly.
		if ( ( $is_modern && Iconic_Flux_Helpers::use_autocomplete() ) || ( Iconic_Flux_Helpers::use_autocomplete() && ! $is_pre_populated ) ) {
			?>
			<div class="shipping-address-search<?php echo $is_pre_populated ? ' shipping-address-search--pre-populated' : ''; ?>">
				<p class="flux-address-search__hint">
					<?php esc_html_e( 'Start typing your address to search.', 'flux-checkout' ); ?>
					<span class="flux-tooltip" for="shipping-address-info" aria-describedby="shipping-address-info">
						<i class="flux-tooltip__icon" role="tooltip">
							<?php esc_html_e( 'Info', 'flux-checkout' ); ?>
						</i>
						<span class="flux-tooltip__tip" id="shipping-address-info">
							<?php esc_html_e( 'Start with your house/apartment number then your street address.', 'flux-checkout' ); ?>
						</span>
					</span>
				</p>
				<p class="form-row form-row-wide" id="shipping_address_info">
					<span class="woocommerce-input-wrapper">
						<div id="shipping_address_search"></div>
					</span>
				</p>
				<p class="flux-address-button-wrapper flux-address-button-wrapper--shipping-manual">
					<button class="flux-address-button flux-address-button--manual flux-address-button--shipping-manual" id="shipping_address_not_found">
						<?php esc_attr_e( 'Enter Address Manually', 'flux-checkout' ); ?>
					</button>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Render Coupon Form.
	 */
	public static function render_coupon_form() {
		if ( wc_coupons_enabled() ) {
			?>
			<?php
			/**
			 * Before coupon form.
			 *
			 * @since 2.7.0
			 */
			do_action( 'flux_before_coupon_form' );
			?>
			<div class="woocommerce-form-coupon__wrapper">
				<?php if ( ! Iconic_Flux_Helpers::is_modern_theme() ) { ?>
					<p class="enter-coupon woocommerce-form-coupon-toggle">
						<button class="enter-coupon__button showcoupon" id="enter_coupon_button" data-show-coupon>
							<?php esc_attr_e( 'Enter Coupon', 'flux-checkout' ); ?>
						</button>
					</p>
				<?php } ?>
				<?php woocommerce_checkout_coupon_form(); ?>
			</div>
			<?php
			/**
			 * After coupon form.
			 *
			 * @since 2.7.0
			 */
			do_action( 'flux_after_coupon_form' );
			?>
			<?php
		}
	}

	/**
	 * Print Back button.
	 *
	 * @param array $step_slug Step slug.
	 *
	 * @return void
	 */
	public static function back_button( $step_slug ) {
		$settings          = Iconic_Flux_Core_Settings::$settings;
		$show_back_to_shop = $settings['general_general_show_back_to_shop_btn'];

		if ( 'details' === $step_slug ) {
			if ( '0' !== $show_back_to_shop ) {
				$shop_id    = wc_get_page_id( 'shop' );
				$shop_title = get_the_title( $shop_id );
				?>
				<?php
				/**
				 * Filter to modify the URL for the back button.
				 *
				 * @param string $url Back button URL.
				 *
				 * @since 2.0.0
				 */
				$flux_checkout_back_button_href = apply_filters( 'flux_checkout_back_button_href', get_permalink( $shop_id ) );
				?>
				<a class="flux-step__back flux-step__back--back-shop" href="<?php echo esc_url( $flux_checkout_back_button_href ); ?>">
					<?php
					// Translators: Shop page title.
					echo sprintf( esc_html__( 'Back to %s', 'flux-checkout' ), esc_html( $shop_title ) );
					?>
				</a>
				<?php
			}
		} else {
			$prev_slug = self::get_prev_step_slug( $step_slug );
			?>
			<a class="flux-step__back flux-step__back--back-history" href="#<?php echo esc_attr( $prev_slug ); ?>">
				<?php
				esc_html_e( 'Back', 'flux-checkout' );
				?>
			</a>
			<?php
		}
	}

	/**
	 * Get shipping price row for mobile view.
	 */
	public static function get_shipping_row_mobile() {
		if ( empty( WC() ) || empty( WC()->shipping() ) || empty( WC()->session ) || empty( WC()->session->chosen_shipping_methods[0] ) ) {
			return '';
		}

		$packages               = WC()->shipping()->get_packages();
		$chosen_shipping_method = WC()->session->chosen_shipping_methods[0];

		if ( empty( $packages ) || empty( $packages[0]['rates'][ $chosen_shipping_method ] ) ) {
			return '';
		}

		// Do not show shipping if address is empty.
		$formatted_destination = WC()->countries->get_formatted_address( $packages[0]['destination'], ', ' );
		if ( empty( $formatted_destination ) ) {
			return;
		}

		$shipping_rate = $packages[0]['rates'][ $chosen_shipping_method ];
		$shipping_cost = wc_cart_totals_shipping_method_label( $shipping_rate );

		if ( empty( $shipping_cost ) ) {
			return '';
		}

		return sprintf( '<th>%s</th><td>%s</td>', esc_html__( 'Shipping', 'flux-checkout' ), $shipping_cost );
	}

	/**
	 * Get review customer frament.
	 *
	 * @return string|false
	 */
	public static function get_review_customer_fragment() {
		$customer_details = self::get_customer_details();

		ob_start();
		self::render_customer_review( $customer_details );
		return ob_get_clean();
	}

	/**
	 * Get customer details.
	 *
	 * @return array
	 */
	public static function get_customer_details() {
		$post_data = filter_input( INPUT_POST, 'post_data', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$fields    = filter_input( INPUT_POST, 'fields', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		if ( empty( $post_data ) ) {
			// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
			return apply_filters( 'flux_checkout_customer_review_details', array(), false );
		}

		$posted_data = array();
		parse_str( html_entity_decode( $post_data ), $posted_data );
		$data = array();

		if ( ! empty( $posted_data ) && is_array( $posted_data ) ) {
			$data = array(
				'name'    => ( isset( $posted_data['billing_first_name'] ) ? $posted_data['billing_first_name'] : '' ) . ' ' . ( isset( $posted_data['billing_last_name'] ) ? $posted_data['billing_last_name'] : '' ),
				'email'   => isset( $posted_data['billing_email'] ) ? $posted_data['billing_email'] : '',
				'phone'   => isset( $posted_data['billing_phone'] ) ? $posted_data['billing_phone'] : '',
				'address' => '',
			);
		}

		// Get address.
		$packages          = WC()->cart->get_shipping_packages();
		$formatted_address = '';

		if ( $packages && isset( $packages[0] ) ) {
			// Use updated package data.
			$destination = get_option( 'woocommerce_ship_to_destination' );
			$destination = 'billing_only' === $destination ? 'billing' : $destination;
			if ( ! empty( $posted_data['ship_to_different_address'] ) ) {
				$destination = 'shipping';
			} elseif ( ! empty( $posted_data['billing_same_billing_address'] ) ) {
				$destination = 'shipping';
			}

			$packages[0]['destination']['address_1'] = Iconic_Flux_Helpers::rgar( $posted_data, $destination . '_address_1' );
			$packages[0]['destination']['city']      = Iconic_Flux_Helpers::rgar( $posted_data, $destination . '_city' );
			$packages[0]['destination']['postcode']  = Iconic_Flux_Helpers::rgar( $posted_data, $destination . '_postcode' );
			$packages[0]['destination']['state']     = Iconic_Flux_Helpers::rgar( $posted_data, $destination . '_state' );
			$packages[0]['destination']['country']   = Iconic_Flux_Helpers::rgar( $posted_data, $destination . '_country' );

			$data['address_1'] = $packages[0]['destination']['address_1'];
			$data['city']      = $packages[0]['destination']['city'];
			$data['postcode']  = $packages[0]['destination']['postcode'];
			$data['state']     = $packages[0]['destination']['state'];
			$data['country']   = $packages[0]['destination']['country'];

			// Prepend street number to address.
			if ( '1' === Iconic_Flux_Core_Settings::$settings['general_general_separate_street_number_field'] ) {
				$street_number        = ! empty( $posted_data['ship_to_different_address'] ) ? Iconic_Flux_Helpers::rgar( $posted_data, 'shipping_street_number' ) : Iconic_Flux_Helpers::rgar( $posted_data, 'billing_street_number' );
				$data['street_number'] = $street_number;

				if ( ! empty( $street_number ) ) {
					$packages[0]['destination']['address_1'] = $street_number . ' ' . $packages[0]['destination']['address_1'];
				}
			}

			$formatted_address = WC()->countries->get_formatted_address( $packages[0]['destination'], "\n" );

			$data['address'] = $formatted_address;
		}

		/**
		 * Details for the Custom review box.
		 *
		 * @param array $data     Customer details.
		 * @param array $packages Shipping packages.
		 *
		 * @since 2.8.0
		 */
		return apply_filters( 'flux_checkout_customer_review_details', $data, $packages );
	}

	/**
	 * Get steps formatted for use in the stepper.
	 *
	 * @return array
	 */
	public static function get_steps_hashes() {
		$steps  = self::get_steps();
		$result = array();

		foreach ( $steps as $index => $step ) {
			$result[ $index + 1 ] = $step['slug'];
		}

		return $result;
	}

	/**
	 * Get slug of the previous step.
	 *
	 * @param string $current_step Current step.
	 *
	 * @return string
	 */
	public static function get_prev_step_slug( $current_step ) {
		$steps         = self::get_steps_hashes();
		$current_index = 0;

		foreach ( $steps as $index => $hash ) {
			if ( $hash === $current_step ) {
				$current_index = $index;
			}
		}

		$prev_index = --$current_index;

		return isset( $steps[ $prev_index ] ) ? $steps[ $prev_index ] : '';
	}
}
