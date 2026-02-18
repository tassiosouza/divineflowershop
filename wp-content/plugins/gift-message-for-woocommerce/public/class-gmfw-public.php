<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://powerfulwp.com
 * @since      1.0.0
 *
 * @package    GMFW
 * @subpackage GMFW/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    GMFW
 * @subpackage GMFW/public
 * @author     powerfulwp <support@powerfulwp.com>
 */
class GMFW_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in GMFW_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The GMFW_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if ( is_checkout() ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/gmfw-public.css', array(), $this->version, 'all' );
			if ( gmfw_fs()->is__premium_only() ) {
				if ( gmfw_fs()->can_use_premium_code() ) {
					  wp_enqueue_style( 'gmfw-slider', plugin_dir_url( __FILE__ ) . 'css/owl.carousel.min.css', array(), $this->version, 'all' );
				}
			}
		}

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in GMFW_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The GMFW_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if ( is_checkout() ) {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/gmfw-public.js', array( 'jquery' ), $this->version, false );

			if ( gmfw_fs()->is__premium_only() ) {
				if ( gmfw_fs()->can_use_premium_code() ) {
					wp_localize_script( $this->plugin_name, 'gmfw_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
					wp_localize_script( $this->plugin_name, 'gmfw_nonce', array( 'nonce' => esc_js( wp_create_nonce( 'gmfw-nonce' ) ) ) );
					wp_enqueue_script( 'gmfw-slider', plugin_dir_url( __FILE__ ) . 'js/owl.carousel.min.js', array(), $this->version, false );
				}
			}
		}

	}


	/**
	 * Checkout occasions field function.
	 *
	 * @param object $checkout checkout object.
	 * @return void
	 */
	public function gmfw_checkout_occasions_field__premium_only( $checkout ) {

		$result = get_terms(
			array(
				'taxonomy'   => 'gmfw_occasions',
				'hide_empty' => false,
				'orderby'    => 'ASC',
				'order'      => 'ASC',
			)
		);

		if ( ! ( empty( $result ) ) ) {
			$options = array( '' => __( 'Select Occasion', 'gmfw' ) );

			foreach ( $result as $key => $taxonomy ) {
					$options[ $taxonomy->term_id ] = $taxonomy->name;
			}
			$options[ __( '0', 'gmfw' ) ] = __( 'Other', 'gmfw' );

			$gmfw_mandatory_occasion_field = get_option( 'gmfw_mandatory_occasion_field', '' );
			$required                      = '1' === $gmfw_mandatory_occasion_field ? true : false;

			$field_value = '';
			if ( ! empty( $checkout ) ) {
				$field_value = $checkout->get_value( 'gmfw_occasion' );
			}

			woocommerce_form_field(
				'gmfw_occasion',
				array(
					'type'        => 'select',
					'class'       => array( 'form-row-wide' ),
					'label'       => __( 'Select Occasion', 'gmfw' ),
					'options'     => $options,
					'placeholder' => __( 'Select Occasion', 'gmfw' ),
					'required'    => $required,
					'default'     => '',
				),
				$field_value
			);
		}

	}


	/**
	 * Checkout message field function.
	 *
	 * @param object $checkout checkout object.
	 * @return void
	 */
	public function gmfw_checkout_message_field( $checkout ) {

		wp_nonce_field( 'register_nonce' );

		$gmfw_mandatory_gift_message_field = get_option( 'gmfw_mandatory_gift_message_field', '' );
		$required                          = '1' === $gmfw_mandatory_gift_message_field ? true : false;

		$field_value = '';
		if ( ! empty( $checkout ) ) {
			$field_value = $checkout->get_value( 'gmfw_gift_message' );
		}

		$gmfw_gift_message_fee_text = '';
		$gmfw_gift_message_class    = '';
		if ( gmfw_fs()->is__premium_only() ) {
			if ( gmfw_fs()->can_use_premium_code() ) {
				$gift_message_fee = gmfw_get_gift_message_fee();
				if ( false !== $gift_message_fee ) {
					$gmfw_gift_message_fee_text = sprintf( esc_html( __( ' ( %s fee )', 'gmfw' ) ), wc_price( esc_html( $gift_message_fee ) ) );
					$gmfw_gift_message_class    = 'gift_message_fee';
				}
			}
		}

		woocommerce_form_field(
			'gmfw_gift_message',
			array(
				'type'        => 'textarea',
				'class'       => array( 'form-row-wide', $gmfw_gift_message_class ),
				'label'       => esc_html( __( 'Gift Message', 'gmfw' ) ) . $gmfw_gift_message_fee_text,
				'placeholder' => esc_html( __( 'Write your gift message here...', 'gmfw' ) ),
				'required'    => $required,
				'default'     => '',
			),
			$field_value
		);
	}



	/**
	 * Checkout suggestions function.
	 *
	 * @return void
	 */
	public function gmfw_checkout_gift_cards__premium_only() {
		$gmfw_gift_carts            = get_option( 'gmfw_gift_carts', '' );
		$gmfw_slide_number_of_items = '' === get_option( 'gmfw_slide_number_of_items', '' ) ? '2' : get_option( 'gmfw_slide_number_of_items', '' );
		if ( ! empty( $gmfw_gift_carts ) ) {

			echo '<div id = "gmfw_checkout_gift_section" data="' . esc_attr( $gmfw_slide_number_of_items ) . '" style="width:100%">';
			echo '<label id = "gmfw_checkout_gift_section_title" >' . esc_html( __( 'Add a Gift Message Card (optional)', 'gmfw' ) ) . '</label>';
			echo '<div class="gmfw-carousel gmfw-theme">';

			foreach ( $gmfw_gift_carts as $product_id ) {
				$product = wc_get_product( $product_id );
				if ( is_object( $product ) ) {

					// Check if item in cart.
					$product_in_cart = false;
					foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
						$cart_product_id = $cart_item['product_id'];

						if ( (int) $cart_product_id === (int) $product_id ) {
							$product_in_cart = true;
							break;
						}
					}

					$image_id  = $product->get_image_id();
					$image_url = wp_get_attachment_image_url( $image_id, 'thumbnail' );
					echo '<div class="gmfw_gift_item">
							<div class="gmfw_gift_image">';
					if ( empty( $image_url ) ) {
						$place_holder = wc_placeholder_img();
						echo $place_holder;
					} else {
						echo '<img src="' . esc_attr( $image_url ) . '" alt = "' . esc_attr( $product->get_name() ) . '">';
					}
						echo '</div>
								<h3 class="gmfw_gift_name" title="' . esc_attr( $product->get_name() ) . '">' . esc_html( $product->get_name() ) . '</h3>
								<p class="gmfw_gift_price">' . wc_price( $product->get_price() ) . '</p>';

								// Set buttons class.
								$add_to_cart_class      = '';
								$remove_from_cart_class = 'gmfw_hide';
					if ( $product_in_cart ) {
						$add_to_cart_class      = 'gmfw_hide';
						$remove_from_cart_class = '';
					}
								echo '
								<button type="button" name="add-to-cart" data="' . esc_attr( $product->get_id() ) . '" class="' . $add_to_cart_class . ' gmfw-add-to-cart single_add_to_cart_button button alt">' . esc_html( $product->single_add_to_cart_text() ) . '</button>
								<button type="button" name="remove-from-cart" data="' . esc_attr( $product->get_id() ) . '" class="' . $remove_from_cart_class . ' gmfw-remove-from-cart   button alt">' . esc_html( __( 'Remove from cart', 'gmfw' ) ) . '</button>
						</div>
						';
				}
			}

			echo '</div>';
			echo ' 
			</div>';
		}
	}


	/**
	 * Checkout suggestions function.
	 *
	 * @return void
	 */
	public function gmfw_checkout_suggestions__premium_only() {
		echo "<div id = 'gmfw_suggestions_section' style='display:none'>";
		echo "<div id = 'gmfw_suggestions_section_title' >" . esc_html( __( 'Need help writing a message?', 'gmfw' ) ) . '</div>';
		echo '<a href="#" id="gmfw_suggestions_btn"><span id="gmfw_suggestions_btn_icon"></span>' . esc_html( __( 'View Message Suggestions', 'gmfw' ) ) . '</a>';
		echo "<div id = 'gmfw_suggestions_wrap' style='display:none' >";
		$posts = get_posts(
			array(
				'numberposts' => -1,
				'post_type'   => 'gmfw_giftmessage',
			)
		);

		foreach ( $posts as $post ) {

			$term_objs = get_the_terms( $post->ID, 'gmfw_occasions' );
			if ( ! empty( $term_objs ) ) {
				foreach ( $term_objs as $term_obj ) {
					$data = $term_obj->term_id;
					echo "<div class='gmfw_suggestion_message' style='display:none' data='" . esc_attr( $data ) . "'>";
					echo "<input type='radio' style='display:none' name='gmfw_giftmessage_radio' class='gmfw_giftmessage_radio'><label for='gmfw_giftmessage_radio'>" . esc_html( $post->post_content ) . '</label>';
					echo '</div>';
				}
			}
		}
		echo '</div></div>';
	}

	/**
	 * Checkout function.
	 *
	 * @return void
	 */
	public function gmfw_checkout_gift_header() {
		echo '<div class="woocommerce-additional-fields__field-gift-message">
		<h3>' . esc_html( __( 'Gift Message', 'gmfw' ) ) . '</h3>';
	}
	/**
	 * Checkout function.
	 *
	 * @return void
	 */
	public function gmfw_checkout_gift_footer() {
		$gmfw_maximum_length = get_option( 'gmfw_maximum_length', '' );
		if ( '' === $gmfw_maximum_length ) {
			$gmfw_maximum_length = 160;
		}
		echo '<div id="gmfw_counter_wrap"><span id="gmfw_counter" data="' . esc_html( $gmfw_maximum_length ) . '">' . esc_html( $gmfw_maximum_length ) . '</span> ' . esc_html( __( 'characters left', 'gmfw' ) ) . '</div></div>';
	}
	/**
	 * Checkout function.
	 *
	 * @return void
	 */
	public function gmfw_validate_checkout_fields() {

		if ( gmfw_fs()->is__premium_only() ) {
			if ( gmfw_fs()->can_use_premium_code() ) {

				$gmfw_mandatory_occasion_field = get_option( 'gmfw_mandatory_occasion_field', '' );
				$gmfw_occasion_notice          = '';

				if ( isset( $_POST['gmfw_occasion'] ) ) {
					if ( '' === trim( sanitize_text_field( wp_unslash( $_POST['gmfw_occasion'] ) ) ) && '1' === $gmfw_mandatory_occasion_field ) {
						$gmfw_occasion_notice = '1';
					}
				} else {
					if ( '1' === $gmfw_mandatory_occasion_field ) {
						$gmfw_occasion_notice = '1';
					}
				}

				if ( '1' === $gmfw_occasion_notice ) {
					wc_add_notice( '<strong>' . esc_html( __( 'Occasion', 'gmfw' ) ) . '</strong> ' . esc_html( __( 'is a required field.', 'gmfw' ) ), 'error' );
				}
			}
		}

		$gmfw_mandatory_gift_message_field = get_option( 'gmfw_mandatory_gift_message_field', '' );
		$gmfw_gift_massage_notice          = '';
		if ( isset( $_POST['gmfw_gift_message'] ) ) {

			if ( '' === trim( sanitize_text_field( wp_unslash( $_POST['gmfw_gift_message'] ) ) ) && '1' === $gmfw_mandatory_gift_message_field ) {
				$gmfw_gift_massage_notice = '1';
			}

			// Check gift message length.
			$message_maximum_length = get_option( 'gmfw_maximum_length', '' );
			if ( is_numeric( $message_maximum_length ) ) {
				if ( strlen( sanitize_text_field( wp_unslash( $_POST['gmfw_gift_message'] ) ) ) > $message_maximum_length ) {
					wc_add_notice( '<strong>' . esc_html( __( 'Gift message', 'gmfw' ) ) . '</strong> ' . esc_html( __( 'cannot be more than', 'gmfw' ) ) . ' ' . $message_maximum_length . ' ' . esc_html( __( 'characters', 'gmfw' ) ), 'error' );
				}
			}
		} else {
			if ( '1' === $gmfw_mandatory_gift_message_field ) {
				$gmfw_gift_massage_notice = '1';
			}
		}

		if ( '1' === $gmfw_gift_massage_notice ) {
			wc_add_notice( '<strong>' . esc_html( __( 'Gift message', 'gmfw' ) ) . '</strong> ' . esc_html( __( 'is a required field.', 'gmfw' ) ), 'error' );
		}

	}

	/**
	 * Checkout function.
	 *
	 * @param number $order_id order number.
	 * @return void
	 */
	public function gmfw_update_checkout_fields( $order_id ) {

		$order = wc_get_order( $order_id );

		if ( gmfw_fs()->is__premium_only() ) {
			if ( gmfw_fs()->can_use_premium_code() ) {

				if ( isset( $_POST['gmfw_occasion'] ) ) {
					$gmfw_occasion_id = sanitize_text_field( wp_unslash( $_POST['gmfw_occasion'] ) );
					if ( '' !== trim( $gmfw_occasion_id ) ) {
						$term      = get_term_by( 'id', $gmfw_occasion_id, 'gmfw_occasions' );
						$term_name = $term->name;
						if ( '' !== $term_name ) {
							$order->update_meta_data( 'gmfw_occasion_name', $term_name );
							$order->update_meta_data( 'gmfw_occasion', $gmfw_occasion_id );

						}
						if ( '0' === $gmfw_occasion_id ) {

							$order->update_meta_data( 'gmfw_occasion_name', __( 'Other', 'gmfw' ) );
						}
					}
				}
			}
		}

		if ( isset( $_POST['gmfw_gift_message'] ) ) {
			$gmfw_gift_message = sanitize_textarea_field( wp_unslash( $_POST['gmfw_gift_message'] ) );
			if ( '' !== trim( $gmfw_gift_message ) ) {

				$order->update_meta_data( 'gmfw_gift_message', $gmfw_gift_message );

				if ( gmfw_fs()->is__premium_only() ) {
					if ( gmfw_fs()->can_use_premium_code() ) {
						$this->add_gift_message_order_fee( $order_id );
					}
				}
			}
		}
		$order->save();
	}

	/**
	 * Update cart fee function.
	 *
	 * @return void
	 */
	public function update_cart_fee() {
		if ( gmfw_fs()->is__premium_only() ) {
			if ( gmfw_fs()->can_use_premium_code() ) {
				wc()->cart->calculate_totals();
			}
		}
	}

	/**
	 * Set gift message cart fee function.
	 *
	 * @param object $cart
	 * @return void
	 */
	public function set_gift_message_cart_fee( $cart ) {
		if ( gmfw_fs()->is__premium_only() ) {
			if ( gmfw_fs()->can_use_premium_code() ) {

				if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
					return;
				}

				// Check if the update_order_review trigger has been fired.
				if ( isset( $_POST['post_data'] ) && strpos( $_POST['post_data'], 'update_order_review' ) !== false ) {

					$gift_message_fee = gmfw_get_gift_message_fee();
					if ( false === $gift_message_fee ) {
						return;
					}

					$post_data         = wp_parse_args( wp_unslash( $_POST['post_data'] ) );
					$gmfw_gift_message = $post_data['gmfw_gift_message'];

					if ( '' !== $gmfw_gift_message ) {
						wc()->cart->add_fee( __( 'Gift message fee', 'gmfw' ), $gift_message_fee );
					} else {
						wc()->cart->remove_coupon( __( 'Gift message fee', 'gmfw' ) );
					}
				}
			}
		}
	}


	/**
	 * Set gift message fee function.
	 *
	 * @param number $order_id order number.
	 * @return void
	 */
	public function add_gift_message_order_fee( $order_id ) {

		if ( gmfw_fs()->is__premium_only() ) {
			if ( gmfw_fs()->can_use_premium_code() ) {

				// Add gift message fee.
				$gmfw_gift_message_fee = gmfw_get_gift_message_fee();

				if ( false !== $gmfw_gift_message_fee ) {

						$order = wc_get_order( $order_id );

						// Get the customer country code.
						$country_code = $order->get_shipping_country();

						// Set the array for tax calculations.
						$calculate_tax_for = array(
							'country'  => $country_code,
							'state'    => '',
							'postcode' => '',
							'city'     => '',
						);

						// Get a new instance of the WC_Order_Item_Fee Object.
						$item_fee = new WC_Order_Item_Fee();
						$item_fee->set_name( __( 'Gift message fee', 'gmfw' ) );
						$item_fee->set_amount( $gmfw_gift_message_fee );
						$item_fee->set_tax_class( '' );
						$item_fee->set_tax_status( 'taxable' );
						$item_fee->set_total( $gmfw_gift_message_fee );

						// Calculating Fee taxes.
						$item_fee->calculate_taxes( $calculate_tax_for );

						// Add Fee item to the order.
						$order->add_item( $item_fee );

						$order->calculate_totals();

						$order->save();

				}
			}
		}
	}


	/**
	 * Email function.
	 *
	 * @param object $order order object.
	 * @param string $sent_to_admin send email.
	 * @param string $plain_text text.
	 * @param string $email email.
	 * @return void
	 */
	public function gmfw_checkout_fields_email( $order, $sent_to_admin, $plain_text, $email ) {

		$order_id = $order->get_id();

		if ( gmfw_fs()->is__premium_only() ) {
			if ( gmfw_fs()->can_use_premium_code() ) {

				$gmfw_occasion_name = $order->get_meta( 'gmfw_occasion_name' );
				if ( $gmfw_occasion_name ) {
					echo '<p><strong>' . esc_html( __( 'Occasion', 'gmfw' ) ) . ':</strong> ' . esc_html( $gmfw_occasion_name );
				}
			}
		}

		$gmfw_gift_message = $order->get_meta( 'gmfw_gift_message' );

		if ( $gmfw_gift_message ) {
			echo '<p><strong>' . esc_html( __( 'Gift message', 'gmfw' ) ) . ':</strong><br>
		    ' . nl2br( esc_html( $gmfw_gift_message ) ) . '</p>';
		}

	}

	/**
	 * Thank you function.
	 *
	 * @param number $order_id order number.
	 * @return void
	 */
	public function gmfw_thankyou( $order_id ) {

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$gmfw_occasion_name = $order->get_meta( 'gmfw_occasion_name' );
		if ( gmfw_fs()->is__premium_only() ) {
			if ( gmfw_fs()->can_use_premium_code() ) {

				if ( $gmfw_occasion_name ) {
					echo '<p class="gmfw_order_overview_occasion"><strong>' . esc_html( __( 'Occasion', 'gmfw' ) ) . ':</strong> ' . esc_html( $gmfw_occasion_name );
				}
			}
		}

		$gmfw_gift_message = $order->get_meta( 'gmfw_gift_message' );
		if ( $gmfw_gift_message ) {

			echo '<p class="gmfw_order_overview_gift_message"><strong>' . esc_html( __( 'Gift message', 'gmfw' ) ) . ':</strong><br>
		' . nl2br( esc_html( $gmfw_gift_message ) ) . '</p>';

		}
	}

	/**
	 * Details on view order.
	 *
	 * @param object $order order object.
	 * @param string $sent_to_admin send email.
	 * @param string $plain_text text.
	 * @param string $email email.
	 * @return void
	 */
	public function gmfw_details_after_order_table( $order, $sent_to_admin = '', $plain_text = '', $email = '' ) {
		if ( is_wc_endpoint_url( 'view-order' ) ) {

			if ( gmfw_fs()->is__premium_only() ) {
				if ( gmfw_fs()->can_use_premium_code() ) {
					$gmfw_occasion_name = $order->get_meta( 'gmfw_occasion_name', true );
					if ( $gmfw_occasion_name ) {
						echo '<p class="gmfw_order_overview_occasion"><strong>' . esc_html( __( 'Occasion', 'gmfw' ) ) . ':</strong> ' . esc_html( $gmfw_occasion_name );
					}
				}
			}

			$gmfw_gift_message = $order->get_meta( 'gmfw_gift_message', true );
			if ( $gmfw_gift_message ) {
				echo '<p class="gmfw_order_overview_gift_message"><strong>' . esc_html( __( 'Gift message', 'gmfw' ) ) . ':</strong><br>
				' . nl2br( esc_html( $gmfw_gift_message ) ) . '</p>';
			}
		}
	}
}
