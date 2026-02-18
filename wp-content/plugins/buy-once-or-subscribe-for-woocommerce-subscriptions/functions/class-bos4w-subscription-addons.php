<?php
/**
 * Class BOS4W_Subscription_Addons
 *
 * Adds functionality to let users add products to existing subscriptions from the product page.
 * Only applicable for BOS products, using WooCommerce Subscriptions.
 *
 * @package Buy Once or Subscribe for WooCommerce Subscriptions
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

use Automattic\WooCommerce\Utilities\NumberUtil;

/**
 * Handles the functionality to add products to existing WooCommerce Subscriptions.
 */
class BOS4W_Subscription_Addons {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_after_add_to_cart_form', array( $this, 'render_add_to_subscription_ui' ), 45 );
		add_action( 'wp_footer', array( $this, 'enqueue_frontend_script' ) );
		add_action( 'wp_ajax_bos4w_get_user_subscriptions', array( $this, 'ajax_get_user_subscriptions' ) );
		add_action( 'wp_ajax_bos4w_add_product_to_subscription', array( $this, 'ajax_add_to_subscription' ) );
		add_filter( 'settings_tabs_bos4w_settings', array( $this, 'extend_general_settings' ), 99 );
	}

	/**
	 * Add our settings to the General tab.
	 *
	 * @param array $settings Existing settings.
	 *
	 * @return array Modified settings.
	 */
	public function extend_general_settings( $settings ) {
		$insert_after = array_search( 'section_end', array_column( $settings, 'type' ), true );
		if ( false === $insert_after ) {
			$insert_after = count( $settings );
		}

		$new_settings = array(
			array(
				'name' => __( 'Add products to subscriptions', 'bos4w' ),
				'type' => 'title',
				'id'   => 'bos4w_add_to_subscription_title',
			),
			array(
				'title'    => __( 'Add products with subscription plans', 'bos4w' ),
				'id'       => 'bos4w_add_with_plans',
				'type'     => 'checkbox',
				'default'  => 'no',
				'class'    => 'bos4w-fields',
				'tooltip'  => __( 'Allow customers to add products that have a subscription plan, including subscription-type products, to existing subscriptions.', 'bos4w' ),
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'Add products without subscription plans', 'bos4w' ),
				'id'       => 'bos4w_add_without_plans',
				'type'     => 'checkbox',
				'default'  => 'no',
				'class'    => 'bos4w-fields',
				'tooltip'  => __( 'Allow customers to add products that do not have a subscription plan to existing subscriptions.', 'bos4w' ),
				'desc_tip' => true,
			),
			array(
				'title'       => __( 'Add to subscription text', 'bos4w' ),
				'id'          => 'bos4w_add_to_subscription_text',
				'type'        => 'text',
				'class'       => 'bos4w-fields',
				'placeholder' => __( 'Add to an existing subscription?', 'bos4w' ),
				'desc'        => __( 'Text that prompts customers to add the product to an existing subscription.', 'bos4w' ),
				'desc_tip'    => true,
			),
			array(
				'type' => 'sectionend',
				'id'   => 'bos4w_add_to_subscription_title',
			),
		);

		array_splice( $settings, $insert_after, 0, $new_settings );

		return $settings;
	}

	/**
	 * Show checkbox and container for subscriptions.
	 */
	public function render_add_to_subscription_ui() {
		if ( ! is_user_logged_in() || is_admin() || ! is_product() ) {
			return;
		}

		global $product;

		$show_checkbox = false;
		$add_text      = __( 'Add to an existing subscription?', 'bos4w' );
		$text          = get_option( 'bos4w_add_to_subscription_text', 'Add to an existing subscription?' );
		if ( $text ) {
			$add_text = $text;
		}

		$get_product_plans       = new BOS4W_Front_End();
		$is_bos                  = $get_product_plans->product_has_subscription_plans( $product );
		$is_regular              = ! $is_bos && ! WC_Subscriptions_Product::is_subscription( $product );
		$is_subscription_product = WC_Subscriptions_Product::is_subscription( $product );

		if ( $is_bos && 'yes' === get_option( 'bos4w_add_with_plans', 'no' ) ) {
			$show_checkbox = true;
		} elseif ( $is_subscription_product && 'yes' === get_option( 'bos4w_add_with_plans', 'no' ) ) {
			$show_checkbox = true;
		} elseif ( $is_regular && 'yes' === get_option( 'bos4w_add_without_plans', 'no' ) ) {
			$show_checkbox = true;
		}

		if ( ! $show_checkbox ) {
			return;
		}

		echo '<div id="bos4w-add-to-subscription-wrap" class="woocommerce-add-to-subscription">';
		echo '<label><input type="checkbox" id="bos4w_add_to_subscription" name="bos4w_add_to_subscription" value="yes"> ' . esc_html( $add_text ) . '</label>';
		echo '<div id="bos4w-subscription-container" style="display:none; margin-top:10px;"></div>';
		echo '</div>';
	}

	/**
	 * Enqueue AJAX script on single product pages.
	 */
	public function enqueue_frontend_script() {
		if ( ! is_product() || ! is_user_logged_in() ) {
			return;
		}
		?>
		<script type="text/javascript">
			jQuery(function ($) {
				let $checkbox = $('#bos4w_add_to_subscription');
				let $container = $('#bos4w-subscription-container');

				$(document).on('change', '#bos4w_add_to_subscription', function () {
					if ($(this).is(':checked')) {
						let frequency = getSelectedFrequency();

						// Current product form
						let $wooForm = $('form.cart').first();

						// Handle both cases: the form *is* .variations_form or it *contains* it
						let $variationsForm = $wooForm.hasClass('variations_form')
							? $wooForm
							: $wooForm.find('.variations_form');

						let variationId = parseInt(
							$variationsForm.find('input[name="variation_id"]').val(),
							10
						) || 0;

						let variation = $variationsForm.length
							? $variationsForm.serializeArray()
							: [];

						$container.html('<p>Loading subscriptions...</p>').show();

						$.post('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
							action: 'bos4w_get_user_subscriptions',
							nonce: '<?php echo esc_attr( wp_create_nonce( 'bos4w_subscription_nonce' ) ); ?>',
							product_id: <?php echo absint( get_the_ID() ); ?>,
							product_frequency: frequency,
							variation_id: variationId,
							variation: variation
						}, function (response) {
							if (response.success) {
								$container.html(response.data.html);
							} else {
								$container.html('<p>' + response.data.message + '</p>');
							}
						});
					} else {
						$container.hide().html('');
					}
				});

				let toggleCheckboxVisibility = function () {
					setTimeout(function () {
						let $button = $('.single_add_to_cart_button').first();

						if ($button.length && !$button.hasClass('disabled')) {
							$('#bos4w-add-to-subscription-wrap').show();
						} else {
							$('#bos4w-add-to-subscription-wrap').hide();
							$checkbox.prop('checked', false);
							$container.hide().html('');
						}
					}, 300);
				};

				$('form.variations_form').on('woocommerce_variation_has_changed found_variation', function () {
					toggleCheckboxVisibility();
				});

				$('form.variations_form').on('found_variation woocommerce_variation_has_changed', function () {
					if ($('#bos4w_add_to_subscription').is(':checked')) {
						setTimeout(function () {
							$('#bos4w_add_to_subscription').trigger('change');
						}, 300);
					}
				});

				if ($('form.cart').hasClass('bundle_form')) {
					jQuery(document.body).on(
						'woocommerce-product-bundle-validation-status-changed',
						function (event, bundle) {
							toggleCheckboxVisibility();
						}
					);
				}

				if ($('form.cart').hasClass('composite_form')) {
					$('.composite_data').on('wc-composite-initializing', function (event, composite) {
						composite.actions.add_action('component_totals_changed', function () {
							toggleCheckboxVisibility();
						}, 51, this);

					});
				}

				$(document).on('change', '#bos4w-dropdown-plan', function () {
					if ($checkbox.is(':checked')) {
						$checkbox.trigger('change');
					}
				});

				$(document).on('change', '.bos4w-buy-type', function () {
					if ($('#bos4w_add_to_subscription').is(':checked')) {
						$('#bos4w_add_to_subscription').trigger('change');
					}
				});

				function getSelectedFrequency() {
					let purchaseType = $('.bos4w-buy-type:checked').val();

					if ('1' === purchaseType) {
						let productId = $('input[name="add-to-cart"]').val()
							|| $('input[name="product_id"]').val()
							|| $('button[name="add-to-cart"]').val()
							|| $('input[name="variation_id"]').val()
							|| $('.single_add_to_cart_button').val();

						if (!productId) {
							productId = $('form.cart').data('product_id');
						}

						if (productId) {
							let $dropdown = $('select[name="convert_to_sub_plan_' + productId + '"]');
							let $input = $('input[name="convert_to_sub_plan_' + productId + '"]');

							if ($dropdown.length) {
								let selectedValue = $dropdown.val();
								return selectedValue;
							}

							if ($input.length) {
								let selectedValue = $input.val();
								return selectedValue;
							}
						}

						let purchaseType = $('.bos4w-buy-type:checked').val();
						if ('1' === purchaseType) {
							let dropdown = $('.bos4w-frequency-dropdown');
							return dropdown.length ? dropdown.val() : '';
						}
					}

					return '';
				}

				function collectCompositePairs($form) {
					var kept = {};

					$form.find('input[name], select[name], textarea[name]').each(function () {
						var el = this;
						var name = el.name;
						if (!name) return;

						if (
							!/^wccp_/.test(name) &&
							name !== 'quantity' &&
							name !== 'add-to-cart'
						) return;

						var type = (el.type || '').toLowerCase();

						if ((type === 'radio' || type === 'checkbox') && !el.checked) return;

						var val = $(el).val();
						var enabled = !el.disabled;
						var hasValue = val !== '' && val != null;

						if (!kept[name]) {
							kept[name] = {value: val == null ? '' : val, enabled: enabled, hasValue: !!hasValue};
						} else {
							var cur = kept[name];
							var better =
								(!cur.hasValue && hasValue) ||
								(!cur.enabled && enabled && (hasValue || !cur.hasValue));

							if (better) {
								kept[name] = {value: val == null ? '' : val, enabled: enabled, hasValue: !!hasValue};
							}
						}
					});

					return Object.keys(kept).map(function (k) {
						return {name: k, value: kept[k].value};
					});
				}

				function resolveWooForm($fromForm, productId) {
					var $ctx = productId ? $('#product-' + productId) : $fromForm.closest('.product, .summary, body');
					var $woo = $ctx.find('form.cart').first();
					if (!$woo.length) $woo = $('form.cart:visible').first();
					return $woo;
				}

				$(document).on('click', '.bos4w-add-to-subscription-form button[type="submit"]', function (e) {
					e.preventDefault();
					$(this).closest('form').trigger('submit');
				});

				$(document).on('submit', '.bos4w-add-to-subscription-form', function (e) {
					e.preventDefault();

					var $form = $(this);
					var $submit = $form.find('button[type="submit"]').prop('disabled', true);

					var productId = $form.find('input[name="product_id"]').val();
					var $wooForm = $form.closest('div[id^="product-"]').find('form.cart');

					if (!$wooForm.length) {
						$form.prepend('<div class="woocommerce-error">Couldn’t find the product form.</div>');
						$submit.prop('disabled', false);
						return;
					}

					var isComposite = $wooForm.hasClass('composite_form');
					var isBundle = $wooForm.hasClass('bundle_form');

					var data = {
						action: 'bos4w_add_product_to_subscription',
						security: '<?php echo esc_attr( wp_create_nonce( 'bos4w_add_to_subscription' ) ); ?>',
						product_id: productId,
						variation_id: $wooForm.find('input[name=\"variation_id\"]').val() || 0,
						variation: $wooForm.find('.variations_form').length ? $wooForm.find('.variations_form').serializeArray() : [],
						subscription_id: $form.find('input[name="subscription_id"]').val(),
						quantity: $form.find('input[name="quantity"]').val() || 1
					};

					if (isComposite) {
						var compositePairs = collectCompositePairs($wooForm);

						var hasATC = compositePairs.some(function (p) {
							return p.name === 'add-to-cart';
						});
						if (!hasATC && productId) {
							compositePairs.push({name: 'add-to-cart', value: productId});
						}

						data.composite_data = compositePairs;
					}

					if (isBundle) {
						var bundlePairs = [];
						$wooForm.find('input[name], select[name], textarea[name]').each(function () {
							var el = this, name = el.name;
							if (!name) return;
							var type = (el.type || '').toLowerCase();
							if ((type === 'radio' || type === 'checkbox') && !el.checked) return;
							bundlePairs.push({name: name, value: $(el).val() == null ? '' : $(el).val()});
						});
						data.bundle_configuration = bundlePairs;
					}

					var purchaseType = $wooForm.find('.bos4w-buy-type:checked').val();
					if (typeof purchaseType !== 'undefined') data.purchase_type = purchaseType;

					$.ajax({
						url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
						type: 'POST',
						dataType: 'json',
						data: data
					})
						.done(function (response) {
							$form.find('.woocommerce-message, .woocommerce-error').remove();
							if (response && response.success) {
								var html = (response.data && (response.data.html || response.data.message)) || 'Added.';
								$form.prepend('<div class="woocommerce-message">' + html + '</div>');
							} else {
								var err = (response && response.data && (response.data.html || response.data.message)) || 'Unable to add to subscription.';
								$form.prepend('<div class="woocommerce-error">' + err + '</div>');
							}
						})
						.fail(function () {
							$form.find('.woocommerce-message, .woocommerce-error').remove();
							$form.prepend('<div class="woocommerce-error">Network error. Please try again.</div>');
						})
						.always(function () {
							$submit.prop('disabled', false);
							$(document.body).trigger('wc_fragment_refresh');
						});
				});

				toggleCheckboxVisibility();
			});
		</script>
		<?php
	}

	/**
	 * AJAX handler for loading subscriptions.
	 */
	public function ajax_get_user_subscriptions() {
		if ( ! check_ajax_referer( 'bos4w_subscription_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token sent.', 'bos4w' ) ) );
		}

		$user_id           = get_current_user_id();
		$product_id        = absint( $_POST['product_id'] ?? 0 );
		$product_frequency = isset( $_POST['product_frequency'] )
				? sanitize_text_field( wp_unslash( $_POST['product_frequency'] ) )
				: '';
		$product           = wc_get_product( $product_id );

		// Sanitize variation-related input.
		$variation_id    = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
		$variation_input = array();

		if ( isset( $_POST['variation'] ) && is_array( $_POST['variation'] ) ) {
			$raw_variation = wc_clean( stripslashes_deep( $_POST['variation'] ) );

			foreach ( $raw_variation as $key => $value ) {
				if ( is_array( $value ) && isset( $value['name'], $value['value'] ) ) {
					$variation_input[] = array(
						'name'  => sanitize_key( $value['name'] ),
						'value' => sanitize_text_field( $value['value'] ),
					);
				} else {
					// Case 2: associative array: [ 'attribute_pa_size' => 'large', ... ].
					$sanitized_key                    = sanitize_key( (string) $key );
					$variation_input[ $sanitized_key ] = sanitize_text_field( (string) $value );
				}
			}
		}

		if ( ! $user_id || ! $product ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'bos4w' ) ) );
		}

		$subscriptions = wcs_get_users_subscriptions( $user_id );

		$normalize_attrs = static function( $raw ) {
			$attrs = array();
			if ( is_array( $raw ) ) {
				// serializeArray style.
				if ( isset( $raw[0]['name'] ) && isset( $raw[0]['value'] ) ) {
					foreach ( $raw as $item ) {
						if ( ! isset( $item['name'], $item['value'] ) ) {
							continue;
						}
						$name = wc_clean( $item['name'] );
						$val  = wc_clean( $item['value'] );
						$name = preg_replace( '/^attribute_/', '', $name );
						if ( '' !== $val ) {
							$attrs[ 'attribute_' . $name ] = $val;
						}
					}
				} else {
					// Associative array style.
					foreach ( $raw as $k => $v ) {
						$k = wc_clean( (string) $k );
						$v = wc_clean( (string) $v );
						$k = preg_replace( '/^attribute_/', '', $k );
						if ( '' !== $v ) {
							$attrs[ 'attribute_' . $k ] = $v;
						}
					}
				}
			}

			return $attrs;
		};

		$selected_interval = '';
		$selected_period   = '';

		if ( ! empty( $product_frequency ) ) {
			$parts = explode( '_', $product_frequency );

			if ( count( $parts ) >= 2 ) {
				$selected_interval = absint( $parts[0] );
				$selected_period   = sanitize_text_field( $parts[1] );
			}
		}

		if ( ( '' === $selected_interval || '' === $selected_period ) && $product->is_type( 'subscription' ) ) {
			$selected_interval = absint( $product->get_meta( '_subscription_period_interval', true ) );
			$selected_period   = sanitize_text_field( $product->get_meta( '_subscription_period', true ) );
		}

		$target_variation  = null;
		$target_var_id     = 0;

		if ( ( '' === $selected_interval || '' === $selected_period ) && $product->is_type( 'variable-subscription' ) ) {
			$variation = null;

			if ( $variation_id ) {
				$maybe = wc_get_product( $variation_id );
				if ( $maybe && $maybe->is_type( 'subscription_variation' ) ) {
					$variation = $maybe;
				}
			}

			if ( ! $variation && ! empty( $variation_input ) ) {
				$attrs      = $normalize_attrs( $variation_input );
				$data_store = WC_Data_Store::load( 'product' );
				$match_id   = $attrs ? $data_store->find_matching_product_variation( $product, $attrs ) : 0;
				if ( $match_id ) {
					$maybe = wc_get_product( $match_id );
					if ( $maybe && $maybe->is_type( 'subscription_variation' ) ) {
						$variation = $maybe;
					}
				}
			}

			if ( ! $variation ) {
				$defaults = $product->get_default_attributes();
				if ( ! empty( $defaults ) ) {
					$attrs = array();
					foreach ( $defaults as $k => $v ) {
						$k = preg_replace( '/^attribute_/', '', $k );
						if ( '' !== $v ) {
							$attrs[ 'attribute_' . $k ] = $v;
						}
					}
					if ( $attrs ) {
						$data_store = WC_Data_Store::load( 'product' );
						$match_id   = $data_store->find_matching_product_variation( $product, $attrs );
						if ( $match_id ) {
							$maybe = wc_get_product( $match_id );
							if ( $maybe && $maybe->is_type( 'subscription_variation' ) ) {
								$variation = $maybe;
							}
						}
					}
				}
			}

			if ( ! $variation ) {
				foreach ( $product->get_children() as $child_id ) {
					$maybe = wc_get_product( $child_id );
					if ( $maybe && $maybe->is_type( 'subscription_variation' ) && $maybe->is_purchasable() ) {
						$variation = $maybe;
						break;
					}
				}
			}

			if ( $variation ) {
				$selected_interval = absint( $variation->get_meta( '_subscription_period_interval', true ) );
				$selected_period   = sanitize_text_field( $variation->get_meta( '_subscription_period', true ) );
				$target_variation  = $variation;
				$target_var_id     = $variation->get_id();
			}
		}

		if ( ! $target_var_id && $variation_id ) {
			$target_var_id = $variation_id;
		}

		ob_start();

		foreach ( $subscriptions as $subscription ) {
			if ( ! $subscription->has_status( 'active' ) ) {
				continue;
			}

			if ( ! empty( $selected_interval ) && ! empty( $selected_period ) ) {
				$sub_interval = $subscription->get_billing_interval();
				$sub_period   = $subscription->get_billing_period();

				if ( (int) $selected_interval !== (int) $sub_interval || $selected_period !== $sub_period ) {
					continue;
				}
			}

			$already_has = false;

			foreach ( $subscription->get_items() as $item ) {
				$item_product_id   = (int) $item->get_product_id();
				$item_variation_id = (int) $item->get_variation_id();

				if ( $target_var_id ) {
					if ( $item_variation_id && $item_variation_id === $target_var_id ) {
						$already_has = true;
						break;
					}
				} else {
					if ( $item_product_id === $product_id && 0 === $item_variation_id ) {
						$already_has = true;
						break;
					}
				}
			}

			if ( $already_has ) {
				continue;
			}

			$id    = $subscription->get_id();
			$total = $subscription->get_formatted_order_total();
			$next  = $subscription->get_time( 'next_payment' );

			$subscription_permalink = wc_get_endpoint_url( 'view-subscription', $id, wc_get_page_permalink( 'myaccount' ) );
			?>
			<div class="bos4w-subscription-card" style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
				<strong><?php esc_html_e( 'ID:', 'bos4w' ); ?></strong>
				<a href="<?php echo esc_url( $subscription_permalink ); ?>">#<?php echo esc_html( $id ); ?></a><br>

				<strong><?php esc_html_e( 'Products:', 'bos4w' ); ?></strong><br>
				<ul>
					<?php foreach ( $subscription->get_items() as $item ) : ?>
						<li><?php echo esc_html( $item->get_name() . ' × ' . $item->get_quantity() ); ?></li>
					<?php endforeach; ?>
				</ul>

				<strong><?php esc_html_e( 'Next Payment:', 'bos4w' ); ?></strong>
				<?php echo $next ? esc_html( date_i18n( wc_date_format(), $next ) ) : esc_html__( 'N/A', 'bos4w' ); ?><br>

				<strong><?php esc_html_e( 'Total:', 'bos4w' ); ?></strong>
				<?php echo wp_kses_post( $total ); ?><br>

				<form class="bos4w-add-to-subscription-form">
					<?php wp_nonce_field( 'bos4w_add_to_subscription', 'security' ); ?>
					<input type="hidden" name="subscription_id" value="<?php echo esc_attr( $id ); ?>"/>
					<input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ); ?>"/>
					<input type="number" name="quantity" value="1" min="1" class="quantity"/>
					<button type="submit" class="button"><?php esc_html_e( 'Add to subscription', 'bos4w' ); ?></button>
				</form>
			</div>
			<?php
		}

		$html = ob_get_clean();

		if ( empty( $html ) ) {
			$html = '<p>' . esc_html__( 'No matching subscriptions found.', 'bos4w' ) . '</p>';
		}

		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Handle form post to add product to subscription.
	 */
	public function ajax_add_to_subscription() {
		check_ajax_referer( 'bos4w_add_to_subscription', 'security' );

		$user_id         = get_current_user_id();
		$product_id      = absint( $_POST['product_id'] ?? 0 );
		$subscription_id = absint( $_POST['subscription_id'] ?? 0 );
		$quantity        = absint( $_POST['quantity'] ?? 1 );
		$variation_id    = absint( $_POST['variation_id'] ?? 0 );
		$purchase_type   = isset( $_POST['purchase_type'] )
				? sanitize_text_field( wp_unslash( $_POST['purchase_type'] ) )
				: '';

		if ( ! $user_id || ! $product_id || ! $subscription_id ) {
			wp_send_json_error( array( 'message' => __( 'Missing data.', 'bos4w' ) ) );
		}

		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			wp_send_json_error( array( 'message' => __( 'Product not found.', 'bos4w' ) ) );
		}

		try {
			switch ( $product->get_type() ) {
				case 'simple':
				case 'subscription':
					$this->bos4w_add_simple_product_form( $product_id, $subscription_id, $quantity, $purchase_type );
					break;

				case 'variable':
				case 'variable-subscription':
					if ( ! $variation_id ) {
						wp_send_json_error( array( 'message' => __( 'Please select product options before adding this product to your subscription.', 'bos4w' ) ) );
					}
					$this->bos4w_add_variable_product_form( $subscription_id, $product_id, $variation_id, $quantity, $purchase_type );
					break;

				case 'bundle':
					if ( ! isset( $_POST['bundle_configuration'] ) ) {
						wp_send_json_error( array( 'message' => __( 'Invalid bundle configuration.', 'bos4w' ) ) );
					}
					$bundle_configuration_raw = wc_clean( stripslashes_deep( $_POST['bundle_configuration'] ) );

					$this->bos4w_add_bundle_product_form( $product_id, $subscription_id, $quantity, $purchase_type, $bundle_configuration_raw );
					break;

				case 'composite':
					if ( method_exists( $this, 'bos4w_add_composite_product_form' ) ) {
						if ( ! isset( $_POST['composite_data'] ) ) {
							wp_send_json_error( array( 'message' => __( 'Invalid composite configuration.', 'bos4w' ) ) );
						}
						$composite_configuration_raw = wc_clean( stripslashes_deep( $_POST['composite_data'] ) );

						$this->bos4w_add_composite_product_form( $product_id, $subscription_id, $quantity, $purchase_type, $composite_configuration_raw );
					} else {
						wp_send_json_error( array( 'message' => __( 'Composite products are not supported.', 'bos4w' ) ) );
					}
					break;

				default:
					/* translators: %s: WooCommerce product type (e.g., simple, variable, bundle) */
					$message = sprintf(
					/* translators: %s: WooCommerce product type (e.g., simple, variable, bundle) */
						__( 'Product type "%s" is not supported.', 'bos4w' ),
						sanitize_text_field( (string) $product->get_type() )
					);

					wp_send_json_error(
						array(
							'message' => $message,
						)
					);
			}
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'message' => $e->getMessage(),
				)
			);
		}

		exit;
	}

	/**
	 * Add a new subscription product to order.
	 *
	 * @param int    $product_id      Product ID.
	 * @param int    $subscription_id Order ID.
	 * @param int    $qty Quantity.
	 * @param string $purchase_type Parse type.
	 * @throws Exception If product is not found.
	 */
	public function bos4w_add_simple_product_form( $product_id, $subscription_id, $qty, $purchase_type ) {
		$message = esc_html__( 'Product not added.', 'bos4w' );

		$order_id = absint( $subscription_id );
		$sub_id   = absint( $product_id );
		$qty      = absint( $qty );
		$product  = wc_get_product( $sub_id );

		if ( empty( $order_id ) ) {
			wp_send_json_error(
				array(
					'html' => sprintf( '<div class="bos4w-error-display-notification">%s</div>', esc_html__( 'Order ID is invalid.', 'bos4w' ) ),
				)
			);
		}

		if ( ! empty( $product ) && ! empty( $order_id ) ) {
			$order        = new WC_Subscription( $order_id );
			$coupon_codes = $order->get_coupon_codes();
			if ( empty( $order ) ) {
				wp_send_json_error(
					array(
						'html' => sprintf( '<div class="bos4w-error-display-notification">%s</div>', esc_html__( 'Order is invalid.', 'bos4w' ) ),
					)
				);
			}

			$interval = $order->get_billing_interval();
			$period   = $order->get_billing_period();
			$discount = $this->ssd_fetch_plan_discount( $interval, $period, $sub_id );

			if ( $discount && $purchase_type ) {
				if ( ! WC()->cart->display_prices_including_tax() ) {
					$product->set_price( wc_get_price_excluding_tax( $product, array( 'price' => $discount ) ) );
				} else {
					$product->set_price( wc_get_price_including_tax( $product, array( 'price' => $discount ) ) );
				}
			}

			$order->add_product( $product, $qty );

			foreach ( $coupon_codes as $coupon_code ) {
				$order->remove_coupon( $coupon_code );
			}
			$order->calculate_totals();

			// Applying all coupons.
			foreach ( $coupon_codes as $coupon_code ) {
				$order->apply_coupon( $coupon_code );
			}

			$order->save();
			$order->calculate_totals();

			$add_note = sprintf( 'Customer added "%s".', esc_attr( $product->get_name() ) );
			$order->add_order_note( $add_note );

			$email_notifications = WC()->mailer()->get_emails();
			// Sending the email.
			$email_notifications['WCS_Email_Completed_Switch_Order']->trigger( $order_id );

			$message = esc_html__( 'Product added to your subscription.', 'bos4w' );
		}

		wp_send_json_success(
			array(
				'html' => sprintf( '<div class="bos4w-display-notification">%s</div>', $message ),
			)
		);
	}

	/**
	 * Add a new variable subscription product to subscription
	 *
	 * @param int    $subscription_id Order ID.
	 * @param int    $product_id      Product ID.
	 * @param int    $variation_id    Variation ID.
	 * @param int    $qty Quantity.
	 * @param string $purchase_type Parse type.
	 * @throws Exception If product is not found.
	 */
	public function bos4w_add_variable_product_form( $subscription_id, $product_id, $variation_id, $qty, $purchase_type ) {
		$message = esc_html__( 'Product not added.', 'bos4w' );

		$order_id     = absint( $subscription_id );
		$sub_id       = absint( $product_id );
		$variation_id = absint( $variation_id );
		$qty          = absint( $qty );
		$product      = wc_get_product( $sub_id );

		if ( empty( $order_id ) ) {
			wp_send_json_error(
				array(
					'html' => sprintf( '<div class="bos4w-error-display-notification">%s</div>', esc_html__( 'Order ID is invalid.', 'bos4w' ) ),
				)
			);
		}

		if ( ! empty( $product ) && ! empty( $order_id ) ) {
			$order        = new WC_Subscription( $order_id );
			$coupon_codes = $order->get_coupon_codes();
			if ( empty( $order ) ) {
				wp_send_json_error(
					array(
						'html' => sprintf( '<div class="bos4w-error-display-notification">%s</div>', esc_html__( 'Order is invalid.', 'bos4w' ) ),
					)
				);
			}

			if ( ! $variation_id ) {
				wp_send_json_error(
					array(
						'html' => sprintf( '<div class="bos4w-error-display-notification">%s</div>', esc_html__( 'Selected variation is invalid.', 'bos4w' ) ),
					)
				);
			}

			if ( $variation_id ) {
				foreach ( $order->get_items() as $item ) {
					if ( $variation_id == $item->get_variation_id() ) {
						wp_send_json_error(
							array(
								'html' => sprintf( '<div class="bos4w-error-display-notification">%s</div>', esc_html__( 'This variation is already in your subscription.', 'bos4w' ) ),
							)
						);
					}
				}

				$product = new WC_Product_Variation( $variation_id );

				$interval = $order->get_billing_interval();
				$period   = $order->get_billing_period();
				$discount = $this->ssd_fetch_plan_discount( $interval, $period, $sub_id, $variation_id );

				if ( $discount && $purchase_type ) {
					if ( ! WC()->cart->display_prices_including_tax() ) {
						$product->set_price( wc_get_price_excluding_tax( $product, array( 'price' => $discount ) ) );
					} else {
						$product->set_price( wc_get_price_including_tax( $product, array( 'price' => $discount ) ) );
					}
				}

				$order_item_id = $order->add_product( $product, $qty );

				foreach ( $coupon_codes as $coupon_code ) {
					$order->remove_coupon( $coupon_code );
				}
				$order->calculate_totals();

				// Applying all coupons.
				foreach ( $coupon_codes as $coupon_code ) {
					$order->apply_coupon( $coupon_code );
				}

				$order->save();
				$order->calculate_totals();

				$add_note = sprintf( 'Customer added "%s".', esc_attr( $product->get_name() ) );
				$order->add_order_note( $add_note );

				// Remove if added!
				if ( wc_get_order_item_meta( $order_item_id, 'variation_id' ) ) {
					wc_delete_order_item_meta( $order_item_id, 'variation_id' );
				}

				$email_notifications = WC()->mailer()->get_emails();
				// Sending the email.
				$email_notifications['WCS_Email_Completed_Switch_Order']->trigger( $order_id );

				$message = esc_html__( 'Product added to your subscription.', 'bos4w' );
			}
		}

		wp_send_json_success(
			array(
				'html' => sprintf( '<div class="bos4w-display-notification">%s</div>', $message ),
			)
		);
	}

	/**
	 * Adds a bundled product configuration to an existing subscription.
	 *
	 * @param int    $product_id The ID of the product to be added as a bundle.
	 * @param int    $subscription_id The ID of the subscription to which the product is being added.
	 * @param int    $qty The quantity of the product being added.
	 * @param string $purchase_type Type of purchase (e.g., subscription type).
	 * @param string $bundle_configuration Encoded string containing the configuration of the bundled items.
	 *
	 * @return void This method does not return any specific value but sends a JSON response indicating success or failure.
	 */
	public function bos4w_add_bundle_product_form( $product_id, $subscription_id, $qty, $purchase_type, $bundle_configuration ) {
		$message = esc_html__( 'Product not added.', 'bos4w' );

		/**
		 * Filter bos_use_regular_price.
		 *
		 * @param bool false
		 *
		 * @since 2.0.2
		 */
		$display_the_price = apply_filters( 'bos_use_regular_price', false );

		$parameters = $this->bos4w_normalize_form_params( $bundle_configuration );

		$order_id = absint( $subscription_id );
		$sub_id   = absint( $product_id );
		$qty      = absint( $qty );
		$product  = wc_get_product( $sub_id );

		if ( empty( $order_id ) ) {
			wp_send_json_error(
				array(
					'html' => sprintf( '<div class="bos4w-error-display-notification">%s</div>', esc_html__( 'Order ID is invalid.', 'bos4w' ) ),
				)
			);
		}

		if ( ! empty( $product ) && ! empty( $order_id ) ) {
			$order        = new WC_Subscription( $order_id );
			$coupon_codes = $order->get_coupon_codes();
			if ( empty( $order ) ) {
				wp_send_json_error(
					array(
						'html' => sprintf( '<div class="bos4w-error-display-notification">%s</div>', esc_html__( 'Order is invalid.', 'bos4w' ) ),
					)
				);
			}

			$posted_configuration = WC_PB()->cart->get_posted_bundle_configuration( $product );

			foreach ( $parameters as $key => $val ) {
				if ( preg_match( '/^bundle_selected_optional_(\d+)$/', $key, $m ) ) {
					$bundled_id                                               = $m[1];
					$posted_configuration[ $bundled_id ]['optional_selected'] = 'yes';
					if ( isset( $parameters[ 'bundle_quantity_' . $bundled_id ] ) ) {
						$posted_configuration[ $bundled_id ]['quantity'] = max( 1, absint( $parameters[ 'bundle_quantity_' . $bundled_id ] ) );
					}
				}

				if ( preg_match( '/^bundle_quantity_(\d+)$/', $key, $m ) ) {
					$bundled_id                                      = $m[1];
					$posted_configuration[ $bundled_id ]['quantity'] = max( 1, absint( $val ) );
				}
			}

			foreach ( $parameters as $key => $val ) {
				if ( preg_match( '/^bundle_variation_id_(\d+)$/', $key, $m ) ) {
					$bundled_id   = $m[1];
					$variation_id = absint( $val );

					if ( $variation_id > 0 ) {
						$posted_configuration[ $bundled_id ]['variation_id'] = $variation_id;

						$selected_attributes = array();
						foreach ( $parameters as $k2 => $v2 ) {
							if ( preg_match( '/^bundle_attribute_([^_]+)_' . preg_quote( $bundled_id, '/' ) . '$/', $k2, $mm ) ) {
								$selected_attributes[ 'attribute_' . $mm[1] ] = wc_clean( $v2 );
							}
						}

						if ( empty( $selected_attributes ) ) {
							$attrs = wc_get_product_variation_attributes( $variation_id );
							if ( is_array( $attrs ) && ! empty( $attrs ) ) {
								$selected_attributes = $attrs;
							}
						}

						if ( ! empty( $selected_attributes ) ) {
							$posted_configuration[ $bundled_id ]['attributes'] = $selected_attributes;
						}
					}
				}
			}

			$result = WC_PB()->order->add_bundle_to_order(
				$product,
				$order,
				$qty,
				array(
					'configuration' => $posted_configuration,
				)
			);

			if ( is_int( $result ) ) {
				$interval          = $order->get_billing_interval();
				$period            = $order->get_billing_period();
				$discount_to_apply = $this->ssd_fetch_plan_discount_value( $interval, $period, $sub_id );

				$new_container_item = $order->get_item( $result );
				if ( $discount_to_apply && $purchase_type ) {
					$new_product = $new_container_item->get_product();

					$bundled_cart_items = wc_pb_get_bundled_order_items( $new_container_item, $order );

					if ( ! empty( $bundled_cart_items ) ) {
						$the_bundle = new WC_Product_Bundle( $new_product );
						foreach ( $bundled_cart_items as $bundled_item_id => $bundled_cart_item ) {
							$item_priced_individually = $bundled_cart_item->get_meta( '_bundled_item_priced_individually', true );

							if ( 'yes' === $item_priced_individually ) {
								$bun_product            = $bundled_cart_item->get_product();
								$bundled_item_raw_price = ! $display_the_price ? $bun_product->get_price() : $bun_product->get_regular_price();

								$bundled_item_price = WC_PB_Product_Prices::get_product_price(
									$bun_product,
									array(
										'price' => $bundled_item_raw_price,
										'calc'  => 'excl_tax',
										'qty'   => $bundled_cart_item->get_quantity(),
									)
								);

								$bundled_item = $the_bundle->get_bundled_item( $bundled_cart_item->get_meta( '_bundled_item_id', true ) );

								if ( $bundled_item->item_data['discount'] ) {
									$bundle_item_price = WC_PB_Product_Prices::get_discounted_price( $bundled_item_price, $bundled_item->item_data['discount'] );
								} else {
									$bundle_item_price = wc_format_decimal( (float) $bundled_item_price, wc_cp_price_num_decimals() );
								}

								$calculated_price = wc_format_decimal( $bundle_item_price - ( $bundle_item_price * ( (float) wc_format_decimal( $discount_to_apply ) / 100 ) ), wc_get_price_decimals() );

								$bundled_cart_item->set_subtotal( NumberUtil::round( $calculated_price, wc_get_price_decimals() ) );
								$bundled_cart_item->set_total( NumberUtil::round( $calculated_price, wc_get_price_decimals() ) );
								$bundled_cart_item->save();
							}
						}
					}

					$bundle_price = WC_PB_Product_Prices::get_product_price(
						$new_product,
						array(
							'price' => ! $display_the_price ? $new_product->get_price() : $new_product->get_regular_price(),
							'calc'  => 'excl_tax',
							'qty'   => 1,
						)
					);

					$bundle_discount = wc_format_decimal( $bundle_price - ( $bundle_price * ( (float) wc_format_decimal( $discount_to_apply ) / 100 ) ), wc_get_price_decimals() );

					$new_container_item->set_subtotal( NumberUtil::round( $bundle_discount * 1, wc_get_price_decimals() ) );
					$new_container_item->set_total( NumberUtil::round( $bundle_discount * 1, wc_get_price_decimals() ) );

					$bos_data = array(
						'selected_subscription' => $interval . '_' . $period . '_' . $discount_to_apply,
						'discounted_price'      => NumberUtil::round( $bundle_discount * 1, wc_get_price_decimals() ),
					);
					$new_container_item->add_meta_data( 'bos4w_data', $bos_data );

					$new_container_item->calculate_taxes();
					$new_container_item->save();
				}

				$add_note = sprintf( 'Customer added "%s".', esc_attr( $product->get_name() ) );
				$order->add_order_note( $add_note );

				$order = wc_get_order( $order_id );

				foreach ( $coupon_codes as $coupon_code ) {
					$order->remove_coupon( $coupon_code );
				}
				$order->calculate_totals();

				// Applying all coupons.
				foreach ( $coupon_codes as $coupon_code ) {
					$order->apply_coupon( $coupon_code );
				}

				$order->calculate_totals();
				$order->calculate_taxes();
				$order->save();

				$email_notifications = WC()->mailer()->get_emails();
				// Sending the email.
				$email_notifications['WCS_Email_Completed_Switch_Order']->trigger( $order_id );

				$message = esc_html__( 'Product added to your subscription.', 'bos4w' );
			}
		}

		wp_send_json_success(
			array(
				'html' => sprintf( '<div class="bos4w-display-notification">%s</div>', $message ),
			)
		);
	}

	/**
	 * Add a composite product to a subscription order.
	 *
	 * @param int    $product_id The ID of the composite product to be added.
	 * @param int    $subscription_id The ID of the subscription order.
	 * @param int    $qty The quantity of the product being added.
	 * @param string $purchase_type The type of purchase (e.g., subscription or one-time).
	 * @param string $composite_configuration The composite product configuration as a query string.
	 *
	 * @return void
	 */
	public function bos4w_add_composite_product_form( $product_id, $subscription_id, $qty, $purchase_type, $composite_configuration ) {
		/**
		 * Filter bos_use_regular_price.
		 *
		 * @param bool false
		 *
		 * @since 2.0.2
		 */
		$display_the_price = apply_filters( 'bos_use_regular_price', false );

		$message = esc_html__( 'Product not added.', 'bos4w' );

		$parameters = $this->bos4w_normalize_form_params( $composite_configuration );

		$order_id = absint( $subscription_id );
		$sub_id   = absint( $product_id );
		$qty      = absint( $qty );
		$product  = wc_get_product( $sub_id );

		if ( empty( $order_id ) ) {
			wp_send_json_error(
				array(
					'html' => sprintf( '<div class="bos4w-error-display-notification">%s</div>', esc_html__( 'Order ID is invalid.', 'bos4w' ) ),
				)
			);
		}

		if ( ! empty( $product ) && ! empty( $order_id ) ) {
			$order        = new WC_Subscription( $order_id );
			$coupon_codes = $order->get_coupon_codes();
			if ( empty( $order ) ) {
				wp_send_json_error(
					array(
						'html' => sprintf( '<div class="bos4w-error-display-notification">%s</div>', esc_html__( 'Order is invalid.', 'bos4w' ) ),
					)
				);
			}

			$posted_c_configuration = WC_CP()->cart->get_posted_composite_configuration( $product );

			if ( empty( $parameters['wccp_component_selection'] ) || ! is_array( $parameters['wccp_component_selection'] ) ) {
				wp_send_json_error(
					array(
						'html' => '<div class="bos4w-error-display-notification">' . esc_html__( 'Invalid composite configuration.', 'bos4w' ) . '</div>',
					)
				);
			}

			foreach ( $parameters['wccp_component_selection'] as $component_id => $sel_product_id ) {
				$component_id   = wc_clean( $component_id );
				$sel_product_id = absint( $sel_product_id );

				$posted_c_configuration[ $component_id ]['product_id'] = $sel_product_id;

				if ( isset( $parameters['wccp_component_quantity'][ $component_id ] ) ) {
					$posted_c_configuration[ $component_id ]['quantity'] = max( 1, absint( $parameters['wccp_component_quantity'][ $component_id ] ) );
				} elseif ( isset( $posted_c_configuration[ $component_id ]['quantity'] ) ) {
					$posted_c_configuration[ $component_id ]['quantity'] = max( 1, absint( $posted_c_configuration[ $component_id ]['quantity'] ) );
				}

				$var_id = isset( $parameters['wccp_variation_id'][ $component_id ] ) ? absint( $parameters['wccp_variation_id'][ $component_id ] ) : 0;
				if ( $var_id > 0 ) {
					$posted_c_configuration[ $component_id ]['variation_id'] = $var_id;

					foreach ( $parameters as $k => $vals ) {
						if ( 0 === strpos( $k, 'wccp_attribute_' ) && is_array( $vals ) && array_key_exists( $component_id, $vals ) ) {
							$slug = substr( $k, strlen( 'wccp_attribute_' ) );
							$val  = $vals[ $component_id ];
							if ( '' !== $val ) {
								$posted_c_configuration[ $component_id ]['attributes'][ 'attribute_' . $slug ] = wc_clean( $val );
							}
						}
					}
				}

				if ( empty( $posted_c_configuration[ $component_id ]['variation_id'] ) ) {
					list( $resolved_var_id, $resolved_attrs ) = $this->bos4w_resolve_variation( $sel_product_id );
					if ( $resolved_var_id ) {
						$posted_c_configuration[ $component_id ]['variation_id'] = $resolved_var_id;
						if ( ! empty( $resolved_attrs ) ) {
							$posted_c_configuration[ $component_id ]['attributes'] = $resolved_attrs;
						}
					}
				}
			}

			$result = WC_CP()->order->add_composite_to_order(
				$product,
				$order,
				$qty,
				array(
					'configuration' => $posted_c_configuration,
				)
			);

			if ( is_int( $result ) ) {
				$interval          = $order->get_billing_interval();
				$period            = $order->get_billing_period();
				$discount_to_apply = $this->ssd_fetch_plan_discount_value( $interval, $period, $sub_id );

				$new_container_item = $order->get_item( $result );
				if ( $discount_to_apply && $purchase_type ) {
					$new_product = $new_container_item->get_product();

					$bundled_cart_items = wc_cp_get_composited_order_items( $new_container_item, $order );

					if ( ! empty( $bundled_cart_items ) ) {
						$bundled_data = new WC_Product_Composite( $new_product->get_id() );
						foreach ( $bundled_cart_items as $bundle_id => $bundled_cart_item ) {
							$item_priced_individually = $bundled_cart_item->get_meta( '_component_priced_individually', true );
							if ( 'yes' === $item_priced_individually ) {
								$bun_product            = $bundled_cart_item->get_product();
								$bundled_item_raw_price = ! $display_the_price ? $bun_product->get_price() : $bun_product->get_regular_price();

								$bundled_item_price = WC_CP_Products::get_product_price(
									$bun_product,
									array(
										'price' => $bundled_item_raw_price,
										'calc'  => 'excl_tax',
										'qty'   => $bundled_cart_item->get_quantity(),
									)
								);

								$component_id = $bundled_cart_item->get_meta( '_composite_item' );
								$discount     = $bundled_data->get_component_discount( $component_id );

								if ( $discount ) {
									$bundle_item_price = WC_CP_Products::get_discounted_price( $bundled_item_price, $discount );
								} else {
									$bundle_item_price = wc_format_decimal( (float) $bundled_item_price, wc_cp_price_num_decimals() );
								}

								$calculated_price = wc_format_decimal( $bundle_item_price - ( $bundle_item_price * ( (float) wc_format_decimal( $discount_to_apply ) / 100 ) ), wc_get_price_decimals() );

								$bundled_cart_item->set_subtotal( NumberUtil::round( $calculated_price, wc_get_price_decimals() ) );
								$bundled_cart_item->set_total( NumberUtil::round( $calculated_price, wc_get_price_decimals() ) );
								$bundled_cart_item->save();
							}
						}
					}

					$bundle_price = WC_CP_Products::get_product_price(
						$new_product,
						array(
							'price' => ! $display_the_price ? $new_product->get_price() : $new_product->get_regular_price(),
							'calc'  => 'excl_tax',
							'qty'   => 1,
						)
					);

					$bundle_discount = wc_format_decimal( $bundle_price - ( $bundle_price * ( (float) wc_format_decimal( $discount_to_apply ) / 100 ) ), wc_get_price_decimals() );

					$new_container_item->set_subtotal( NumberUtil::round( $bundle_discount, wc_get_price_decimals() ) );
					$new_container_item->set_total( NumberUtil::round( $bundle_discount, wc_get_price_decimals() ) );

					$bos_data = array(
						'selected_subscription' => $interval . '_' . $period . '_' . $discount_to_apply,
						'discounted_price'      => NumberUtil::round( $bundle_discount, wc_get_price_decimals() ),
					);
					$new_container_item->add_meta_data( 'bos4w_data', $bos_data );

					$new_container_item->calculate_taxes();
					$new_container_item->save();
				}

				$add_note = sprintf( 'Customer added "%s".', esc_attr( $product->get_name() ) );
				$order->add_order_note( $add_note );

				$order = wc_get_order( $order_id );

				foreach ( $coupon_codes as $coupon_code ) {
					$order->remove_coupon( $coupon_code );
				}
				$order->calculate_totals();

				// Applying all coupons.
				foreach ( $coupon_codes as $coupon_code ) {
					$order->apply_coupon( $coupon_code );
				}

				$order->calculate_totals();
				$order->calculate_taxes();
				$order->save();

				$email_notifications = WC()->mailer()->get_emails();
				// Sending the email.
				$email_notifications['WCS_Email_Completed_Switch_Order']->trigger( $order_id );

				$message = esc_html__( 'Product added to your subscription.', 'bos4w' );
			}
		}

		wp_send_json_success(
			array(
				'html' => sprintf( '<div class="bos4w-display-notification">%s</div>', $message ),
			)
		);
	}

	/**
	 * Add a composite product to a subscription order.
	 *
	 * @param string $interval Subscription period interval.
	 * @param string $period Subscription period.
	 * @param int    $product_id Product ID.
	 * @param int    $variation_id Variation ID.
	 *
	 * @return false|int|mixed
	 */
	public function ssd_fetch_plan_discount( $interval, $period, $product_id, int $variation_id = 0 ): mixed {
		$discount = 0;

		$bos     = new BOS4W_Front_End();
		$product = wc_get_product( $product_id );

		// Get product plans, which now include variation-specific plans if available.
		$product_plans = $bos->get_discounted_prices( $product );

		if ( empty( $product_plans ) || ! is_array( $product_plans ) ) {
			return $discount; // Return 0 if no valid product plans exist.
		}

		// Check if variation-specific plans exist.
		if ( is_array( $product_plans ) && $variation_id > 0 ) {
			if ( isset( $product_plans[ $variation_id ] ) ) {
				foreach ( $product_plans[ $variation_id ] as $plan ) {
					// Ensure $plan is an array and has the necessary keys.
					if ( is_array( $plan ) && isset( $plan['subscription_period_interval'], $plan['subscription_period'], $plan['discounted_price'] ) ) {
						if ( $interval === $plan['subscription_period_interval'] && $period === $plan['subscription_period'] ) {
							$discount = $plan['discounted_price'];
							break;
						}
					}
				}
			} else {
				$plans = $bos->product_has_subscription_plans( $product );
				if ( ! $plans || empty( $plans ) ) {
					return false;
				}

				if ( ! empty( $plans['product'] ) ) {
					$product_plans = $plans['product'];
				} elseif ( ! empty( $plans['global'] ) ) {
					$product_plans = $plans['global'];
				} else {
					$product_plans = array();
				}

				if ( $product_plans ) {
					$variation = wc_get_product( $variation_id );
					/**
					 * Filter bos_use_regular_price.
					 *
					 * @param bool false
					 *
					 * @since 2.0.2
					 */
					$display_the_price = apply_filters( 'bos_use_regular_price', false );

					$original_price = ! $display_the_price ? $variation->get_price() : $variation->get_regular_price();

					$calculated_plans = $this->calculate_discounted_price( $product_plans, $original_price );
					foreach ( $calculated_plans as $key => $plan ) {
						// Ensure $plan is an array and has the necessary keys.
						if ( is_array( $plan ) && isset( $plan['subscription_period_interval'], $plan['subscription_period'], $plan['discounted_price'] ) ) {
							if ( $interval === $plan['subscription_period_interval'] && $period === $plan['subscription_period'] ) {
								$discount = $plan['discounted_price'];
								break;
							}
						}
					}
				}
			}
		} else {
			// Fallback to product-level discounts if no variation-level discount exists.
			foreach ( $product_plans as $plan ) {
				if ( is_array( $plan ) && isset( $plan['subscription_period_interval'], $plan['subscription_period'], $plan['discounted_price'] ) ) {
					if ( $interval === $plan['subscription_period_interval'] && $period === $plan['subscription_period'] ) {
						$discount = $plan['discounted_price'];
						break;
					}
				}
			}
		}

		return $discount;
	}

	/**
	 * Fetch plan discount value.
	 *
	 * @param string $interval Subscription period interval.
	 * @param string $period Subscription period.
	 * @param int    $product_id Product ID.
	 *
	 * @return int|mixed
	 */
	protected function ssd_fetch_plan_discount_value( $interval, $period, $product_id ) {
		$discount      = 0;

		$global_plans = get_option( 'bos4w_global_saved_subs' );
		if ( $global_plans && ssd_is_bos_active() ) {
			foreach ( $global_plans as $entry => $plan ) {
				if ( $plan['product_cat'] > 0 && ! has_term( $plan['product_cat'], 'product_cat', $product_id ) ) {
					unset( $global_plans[ $entry ] );
				}
			}
			$global_plans = array_merge( $global_plans );

			if ( ! empty( $global_plans ) ) {
				foreach ( $global_plans as $plan ) {
					if ( $interval === $plan['subscription_period_interval'] && $period === $plan['subscription_period'] ) {
						$discount = $plan['subscription_discount'];
					}
				}
			}
		}

		$product_plans = get_post_meta( $product_id, '_bos4w_saved_subs', true );
		if ( $product_plans && ssd_is_bos_active() ) {
			foreach ( $product_plans as $plan ) {
				if ( $interval === $plan['subscription_period_interval'] && $period === $plan['subscription_period'] ) {
					$discount = $plan['subscription_discount'];
				}
			}
		}

		return $discount;
	}

	/**
	 * Calculate the discounted price for a given product based on the plans and original price.
	 *
	 * @param array $plans The list of plans to apply to the product.
	 * @param float $original_price The original price of the product.
	 *
	 * @return array The list of plans with their respective discounted prices.
	 */
	private function calculate_discounted_price( $plans, $original_price ) {
		$discounted_plans = array();

		foreach ( $plans as $plan ) {
			$discounted_price = $original_price;

			// Percentage discount.
			if ( isset( $plan['subscription_discount'] ) && $plan['subscription_discount'] > 0 ) {
				$discounted_price = $original_price - ( $original_price * ( $plan['subscription_discount'] / 100 ) );
			}

			// Fixed value discount.
			if ( isset( $plan['subscription_price'] ) && $plan['subscription_price'] > 0 ) {
				$discounted_price = wc_format_decimal( $discounted_price - (float) wc_format_decimal( $plan['subscription_price'] ), wc_get_price_decimals() );
			}

			$plan['discounted_price']             = wc_format_decimal( $discounted_price, wc_get_price_decimals() );
			$plan['subscription_period_interval'] = isset( $plan['subscription_period_interval'] ) ? $plan['subscription_period_interval'] : '';
			$plan['subscription_period']          = isset( $plan['subscription_period'] ) ? $plan['subscription_period'] : '';

			$discounted_plans[] = $plan;
		}

		return $discounted_plans;
	}

	/**
	 * Normalize params.
	 *
	 * @param array $raw Raw.
	 *
	 * @return array
	 */
	private function bos4w_normalize_form_params( $raw ): array {
		$raw = wp_unslash( $raw );

		// If it's already a string like "a[b]=1&c=2" -> parse into array.
		if ( is_string( $raw ) ) {
			parse_str( $raw, $params );

			return is_array( $params ) ? $params : array();
		}

		// If it's an array of {name, value} pairs (serializeArray), rebuild a query and parse.
		if ( is_array( $raw ) ) {
			$parts = array();
			foreach ( $raw as $pair ) {
				if ( isset( $pair['name'], $pair['value'] ) ) {
					$parts[] = $pair['name'] . '=' . rawurlencode( $pair['value'] );
				}
			}
			parse_str( implode( '&', $parts ), $params );

			return is_array( $params ) ? $params : array();
		}

		return array();
	}

	/**
	 * Resolves variation attributes for a given product.
	 *
	 * @param int   $product_id The ID of the product to resolve variation for.
	 * @param array $attrs Optional. An array of attributes to match a variation. Default is an empty array.
	 *
	 * @return array An array containing the variation ID (or 0 if none is matched) and the resolved attributes.
	 */
	private function bos4w_resolve_variation( $product_id, $attrs = array() ): array {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return array( 0, array() );
		}

		// If already a variation, return its attrs.
		if ( $product->is_type( 'variation' ) ) {
			$var = $product;

			return array( $var->get_id(), wc_get_product_variation_attributes( $var->get_id() ) );
		}

		// If variable, compute a matching variation using defaults + supplied attrs.
		if ( $product->is_type( 'variable' ) ) {
			$defaults = (array) $product->get_default_attributes(); // keys like 'pa_size'.
			$attrs    = array_filter( wp_parse_args( (array) $attrs, $defaults ) );

			// Normalize keys to 'attribute_{slug}'.
			$normalized = array();
			foreach ( $attrs as $k => $v ) {
				$k                = ( 0 === strpos( $k, 'attribute_' ) ) ? $k : 'attribute_' . $k;
				$normalized[ $k ] = $v;
			}

			$data_store   = WC_Data_Store::load( 'product' );
			$variation_id = $data_store->find_matching_product_variation( $product, $normalized );

			// Fallback: pick first available variation if defaults don’t match anything.
			if ( ! $variation_id ) {
				$children = $product->get_children();
				if ( ! empty( $children ) ) {
					$variation_id = (int) $children[0];
					$normalized   = wc_get_product_variation_attributes( $variation_id );
				}
			}

			return array( (int) $variation_id, $normalized );
		}

		return array( 0, array() );
	}
}

// Instantiate the class.
new BOS4W_Subscription_Addons();
