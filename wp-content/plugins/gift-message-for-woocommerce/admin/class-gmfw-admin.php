<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://powerfulwp.com
 * @since      1.0.0
 *
 * @package    GMFW
 * @subpackage GMFW/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    GMFW
 * @subpackage GMFW/admin
 * @author     powerfulwp <support@powerfulwp.com>
 */
class GMFW_Admin {

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
	 * @param string $plugin_name       The name of this plugin.
	 * @param string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/gmfw-admin.css', array( 'woocommerce_admin_styles' ), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/gmfw-admin.js', array( 'jquery', 'wc-enhanced-select' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'gmfw_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		wp_localize_script( $this->plugin_name, 'gmfw_nonce', array( 'nonce' => esc_js( wp_create_nonce( 'gmfw-nonce' ) ) ) );

	}

	/**
	 * Plugin submenu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function gmfw_admin_menu() {
		// add menu to main menu.
		add_menu_page( 'gmfw-settings', esc_html( __( 'Gift Message', 'gmfw' ) ), 'manage_options', 'gmfw-settings', array( &$this, 'gmfw_settings' ) );

		// add sub menu to main menu.
		add_submenu_page( 'gmfw-settings', esc_html( __( 'General settings', 'gmfw' ) ), esc_html( __( 'General settings', 'gmfw' ) ), 'manage_options', 'gmfw-settings' );

		if ( gmfw_fs()->is__premium_only() ) {
			if ( gmfw_fs()->can_use_premium_code() ) {

				// add sub menu to main menu.
				add_submenu_page( 'gmfw-settings', esc_html( __( 'Occasions', 'gmfw' ) ), esc_html( __( 'Occasions', 'gmfw' ) ), 'manage_options', 'edit-tags.php?taxonomy=gmfw_occasions&post_type=gmfw_giftmessage' );

				// add sub menu to main menu.
				add_submenu_page( 'gmfw-settings', esc_html( __( 'Gift Messages', 'gmfw' ) ), esc_html( __( 'Gift Messages', 'gmfw' ) ), 'manage_options', 'edit.php?post_type=gmfw_giftmessage' );

			}
		}
	}

	/**
	 * Plugin settings.
	 *
	 * @since 1.0.0
	 */
	public function gmfw_settings() {
		?>
		<form action='options.php' method='post'>
			<h1><?php echo esc_html( __( 'General Settings', 'gmfw' ) ); ?></h1>
			<?php
			echo self::gmfw_admin_plugin_bar();
			settings_fields( 'gmfw' );
			do_settings_sections( 'gmfw' );
			submit_button();
			?>
		</form>
		<?php
	}

	/**
	 * Plugin register settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function gmfw_settings_init() {

		register_setting( 'gmfw', 'gmfw_enable_gift_message' );
		register_setting( 'gmfw', 'gmfw_mandatory_gift_message_field' );
		register_setting( 'gmfw', 'gmfw_maximum_length', array( $this, 'gmfw_sanitize_maximum_length' ) );
		register_setting( 'gmfw', 'gmfw_woocommerce_checkout_section' );

		if ( gmfw_fs()->is__premium_only() ) {
			if ( gmfw_fs()->can_use_premium_code() ) {
				register_setting( 'gmfw', 'gmfw_enable_gift_message_suggestions' );
				register_setting( 'gmfw', 'gmfw_gift_message_fee' );
				register_setting( 'gmfw', 'gmfw_mandatory_occasion_field' );
				register_setting( 'gmfw', 'gmfw_import_data' );
				register_setting( 'gmfw', 'gmfw_gift_carts' );
				register_setting( 'gmfw', 'gmfw_slide_number_of_items' );

				$gmfw_import_data = get_option( 'gmfw_import_data', '' );
				if ( '2' === $gmfw_import_data ) {
					add_action( 'admin_notices', array( $this, 'gmfw_import_data_admin_notice__premium_only' ) );
					update_option( 'gmfw_import_data', '' );
				}

				/**
				 *  If occasions array is empty, we add import checkbox.
				 */
				$terms = get_terms(
					array(
						'taxonomy'   => 'gmfw_occasions',
						'hide_empty' => false,
					)
				);

				if ( empty( $terms ) ) {
					add_settings_field(
						'gmfw_import_data',
						__( 'Import occasions and gift messages', 'gmfw' ),
						array( $this, 'gmfw_import_data__premium_only' ),
						'gmfw',
						'gmfw_setting_occasion'
					);
				}
			}
		}

		add_settings_section(
			'gmfw_setting_section',
			__( 'Gift Message', 'lddfw' ),
			'',
			'gmfw'
		);

		add_settings_field(
			'gmfw_enable_gift_message',
			__( 'Enable gift message on checkout', 'gmfw' ),
			array( $this, 'gmfw_enable_gift_message' ),
			'gmfw',
			'gmfw_setting_section'
		);

		add_settings_field(
			'gmfw_mandatory_gift_message_field',
			__( 'Gift message field is required', 'gmfw' ),
			array( $this, 'gmfw_mandatory_gift_message_field' ),
			'gmfw',
			'gmfw_setting_section'
		);

		add_settings_field(
			'gmfw_maximum_length',
			__( 'Maximum length', 'gmfw' ),
			array( $this, 'gmfw_maximum_length' ),
			'gmfw',
			'gmfw_setting_section'
		);

		add_settings_field(
			'gmfw_gift_message_fee',
			__( 'Gift message fee', 'gmfw' ),
			array( $this, 'gmfw_gift_message_fee' ),
			'gmfw',
			'gmfw_setting_section'
		);

		add_settings_field(
			'gmfw_woocommerce_checkout_section',
			__( 'Checkout section', 'gmfw' ),
			array( $this, 'gmfw_woocommerce_checkout_section' ),
			'gmfw',
			'gmfw_setting_section'
		);

		add_settings_field(
			'gmfw_enable_gift_message_suggestions',
			__( 'Enable gift message suggestions on checkout', 'gmfw' ),
			array( $this, 'gmfw_enable_gift_message_suggestions' ),
			'gmfw',
			'gmfw_setting_section'
		);

		if ( gmfw_is_free() ) {
			add_settings_field(
				'gmfw_gift_message_orders_column',
				__( 'Gift message orders column', 'gmfw' ),
				array( $this, 'gmfw_feature_settings' ),
				'gmfw',
				'gmfw_setting_section'
			);
			add_settings_field(
				'gmfw_gift_messages_suggestions',
				'72 ' . __( 'New gift message suggestions', 'gmfw' ),
				array( $this, 'gmfw_feature_settings' ),
				'gmfw',
				'gmfw_setting_section'
			);

			add_settings_field(
				'gmfw_copy_gift_messages',
				__( 'Copy gift message in one click', 'gmfw' ),
				array( $this, 'gmfw_feature_settings' ),
				'gmfw',
				'gmfw_setting_section'
			);

			add_settings_field(
				'gmfw_new_occasions',
				'25 ' . __( 'New occasions', 'gmfw' ),
				array( $this, 'gmfw_feature_settings' ),
				'gmfw',
				'gmfw_setting_occasion'
			);
		}

		add_settings_section(
			'gmfw_setting_occasion',
			__( 'Occasions', 'lddfw' ),
			'',
			'gmfw'
		);

		add_settings_field(
			'gmfw_mandatory_occasion_field',
			__( 'Occasion field is required', 'gmfw' ),
			array( $this, 'gmfw_mandatory_occasion_field' ),
			'gmfw',
			'gmfw_setting_occasion'
		);

		add_settings_section(
			'gmfw_setting_gift_card',
			__( 'Gift Message Cards', 'lddfw' ),
			'',
			'gmfw'
		);

		add_settings_field(
			'gmfw_gift_carts',
			__( 'Gift message cards', 'gmfw' ),
			array( $this, 'gmfw_gift_carts' ),
			'gmfw',
			'gmfw_setting_gift_card'
		);

		add_settings_field(
			'gmfw_slide_number_of_items',
			__( 'Slide number of items', 'gmfw' ),
			array( $this, 'gmfw_slide_number_of_items' ),
			'gmfw',
			'gmfw_setting_gift_card'
		);

	}




	/**
	 * Plugin settings.
	 *
	 * @since 1.0.0
	 */
	public function gmfw_feature_settings() {
		if ( gmfw_is_free() ) {
			echo gmfw_premium_feature( '' );
		}
	}

	/**
	 * Plugin settings.
	 *
	 * @since 1.0.0
	 */
	public function gmfw_maximum_length() {
		$gmfw_maximum_length = get_option( 'gmfw_maximum_length', '' );
		?>
		<label for="gmfw_maximum_length" class='checkbox_toggle'>
			<input type='text' maxlength="5" class='regular-text' name='gmfw_maximum_length' id='gmfw_maximum_length' value='<?php echo esc_attr( $gmfw_maximum_length ); ?>'>
			<br><?php echo esc_html( __( 'The maximum length of the gift message', 'gmfw' ) ); ?>
		</label>
		<?php
	}




	/**
	 * Plugin settings.
	 *
	 * @since 1.0.0
	 */
	public function gmfw_slide_number_of_items() {
		if ( gmfw_fs()->is__premium_only() ) {
			if ( gmfw_fs()->can_use_premium_code() ) {
				$gmfw_slide_number_of_items = get_option( 'gmfw_slide_number_of_items', '' );
				?>
					<p class="form-field">
						<select name="gmfw_slide_number_of_items" >
							<?php
							 echo '<option value="2"' . selected( '2', $gmfw_slide_number_of_items, false ) . '>2</option>';
							 echo '<option value="3"' . selected( '3', $gmfw_slide_number_of_items, false ) . '>3</option>';
							 echo '<option value="4"' . selected( '4', $gmfw_slide_number_of_items, false ) . '>4</option>';
							 echo '<option value="5"' . selected( '5', $gmfw_slide_number_of_items, false ) . '>5</option>';
							 echo '<option value="6"' . selected( '6', $gmfw_slide_number_of_items, false ) . '>6</option>';
							?>
						</select>
					</p>
				<?php
			}
		}
		echo gmfw_premium_feature( '' );
	}

	/**
	 * Plugin settings.
	 *
	 * @since 1.0.0
	 */
	public function gmfw_gift_carts() {

		if ( gmfw_fs()->is__premium_only() ) {
			if ( gmfw_fs()->can_use_premium_code() ) {

				$gmfw_gift_carts = get_option( 'gmfw_gift_carts', '' );
				?>
					<p class="form-field">
						<select class="wc-product-search" multiple="multiple" style="width: 50%;" name="gmfw_gift_carts[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products_and_variations">
							<?php
							if ( ! empty( $gmfw_gift_carts ) ) {
								foreach ( $gmfw_gift_carts as $product_id ) {
									$product = wc_get_product( $product_id );
									if ( is_object( $product ) ) {
										echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ) . '</option>';
									}
								}
							}
							?>
						</select>
					</p>
				<?php
			}
		}
		 echo gmfw_premium_feature( '' );
		?>
		<p>
		<?php
		echo esc_html( __( 'Select gift message cards suggestions for your customers at the checkout.', 'gmfw' ) );
		?>
		</p>
		<?php
	}



	/**
	 * Import dada function.
	 *
	 * Import start only if checkbox is checked and there are no occasions.
	 *
	 * @since 1.0.0
	 */
	public function gmfw_start_import_data__premium_only() {

		$gmfw_import_data = get_option( 'gmfw_import_data', '' );
		if ( '1' === $gmfw_import_data ) {
			/**
			 *  Get occasions query
			*/

			$terms = get_terms(
				array(
					'taxonomy'   => 'gmfw_occasions',
					'hide_empty' => false,
				)
			);
			/**
			 *  If occasions array is empty, we start import.
			 */
			if ( empty( $terms ) ) {
				/**
				 * Array of gift messages.
				 */
				$array = array(
					__( 'Birthday', 'gmfw' )         => array(
						__( 'Happy birthday!! I hope your day is filled with lots of love and laughter! May all of your birthday wishes come true.', 'gmfw' ),
						__( 'Happy birthday!!! I hope this is the beginning of your greatest, most wonderful year ever!', 'gmfw' ),
						__( 'May life’s brightest joys illuminate your path, and may each day’s journey bring you closer to your dreams!', 'gmfw' ),
					),
					__( 'Funeral', 'gmfw' )          => array(
						__( 'Please accept my deepest sympathy for your loss.', 'gmfw' ),
						__( 'We are deeply sorry for your loss.', 'gmfw' ),
						__( 'Please know we are thinking of you and your family during this difficult time.', 'gmfw' ),
					),
					__( 'Love And Romance', 'gmfw' ) => array(
						__( 'I just wanted to tell you how gorgeous you are.', 'gmfw' ),
						__( 'I love you with all of my heart.', 'gmfw' ),
						__( 'Have a beautiful day. I\'ll have one just thinking of you.', 'gmfw' ),
					),
					__( 'Anniversary', 'gmfw' )      => array(
						__( 'Happy Anniversary, to my partner, my best friend.', 'gmfw' ),
						__( 'Still in this together. Love that.', 'gmfw' ),
						__( 'Happy anniversary to us! You bowled me over then and you still do!', 'gmfw' ),
					),
					__( 'Thank you', 'gmfw' )        => array(
						__( 'You’re the best.', 'gmfw' ),
						__( 'I couldn\'t have done it without you. Thank you.', 'gmfw' ),
						__( 'Thanks for everything.', 'gmfw' ),
					),
					__( 'Congratulations', 'gmfw' )  => array(
						__( 'Congratulations and BRAVO!', 'gmfw' ),
						__( 'You did it! So proud of you!', 'gmfw' ),
						__( 'Congratulations on your well-deserved success.', 'gmfw' ),
					),
					__( 'Graduation', 'gmfw' )       => array(
						__( 'You did it! Congrats!', 'gmfw' ),
						__( 'Happy Graduation Day!', 'gmfw' ),
						__( 'What an impressive achievement!', 'gmfw' ),
					),
					__( 'Get Well', 'gmfw' )         => array(
						__( 'Hope you feel better soon!', 'gmfw' ),
						__( 'Miss seeing you around here. Get well soon!', 'gmfw' ),
						__( 'Have the speediest of recoveries!', 'gmfw' ),
					),
					__( 'I\'m Sorry', 'gmfw' )       => array(
						__( 'I just wanted to say that I am sorry for everything', 'gmfw' ),
						__( 'It was all my fault, please accept my apology.', 'gmfw' ),
						__( 'I wish I could turn back the time and make things right… Please forgive me!', 'gmfw' ),
					),
					__( 'Just Because', 'gmfw' )     => array(
						__( 'Hope this brightens your day.', 'gmfw' ),
						__( 'Because of you, everything seems possible.', 'gmfw' ),
						__( 'You’re in my thoughts today.', 'gmfw' ),
					),
					__( 'New Home', 'gmfw' )         => array(
						__( 'Wishing you all the best in turning your new house into a home.', 'gmfw' ),
						__( 'Yay – you made it into your new home! Wishing you all the best.', 'gmfw' ),
						__( 'Congrats on your new home. Great adulting!', 'gmfw' ),
					),
					__( 'New Baby', 'gmfw' )         => array(
						__( 'So happy for you two! That’s going to be one lucky baby.', 'gmfw' ),
						__( 'What a very lucky baby. Congratulations!', 'gmfw' ),
						__( 'Can’t wait to see that sweet little smile.', 'gmfw' ),
					),
					__( 'Fathers Day', 'gmfw' )      => array(
						__( 'You’re the best, Dad. I love you!', 'gmfw' ),
						__( 'God gave me such a good gift when he gave me you for a father.', 'gmfw' ),
						__( 'Dad, you’re in all my favorite memories.', 'gmfw' ),
					),
					__( 'Wedding', 'gmfw' )          => array(
						__( 'Congratulations on your wedding day and best wishes for a happy life together!', 'gmfw' ),
						__( 'Wishing you lots of love and happiness.', 'gmfw' ),
						__( 'Best wishes on this blessed day.', 'gmfw' ),
					),
					__( 'Back to School', 'gmfw' )   => array(
						__( 'Be cool at school. Learn. Laugh. And have fun!', 'gmfw' ),
						__( 'Live. Love. Learn. Have an amazing school year.', 'gmfw' ),
						__( 'Happy Back to School! I hope this new year is full of exciting stories, cool new facts, and so much fun!', 'gmfw' ),
					),
					__( 'Boss\'s Day', 'gmfw' )      => array(
						__( 'If someone has to be the boss of me, I’m glad it’s you!', 'gmfw' ),
						__( 'I can’t think of anyone I’d rather have for a boss.', 'gmfw' ),
						__( 'Thanks for everything you do for us. Hope you have a great day!', 'gmfw' ),
					),
					__( 'Cheer Someone Up', 'gmfw' ) => array(
						__( 'In one minute a life can change, we are hoping that your life can change for the good in one of those minutes.', 'gmfw' ),
						__( 'We feel sunshine, we see sunshine. May you feel and see sunshine again. You deserve it.', 'gmfw' ),
						__( 'Even during times of trouble, you inspire me. You are truly a soul this world needs. Hang in there, I\'m here for you always.', 'gmfw' ),
					),
					__( 'Engagement', 'gmfw' )       => array(
						__( 'May your joining together bring you more joy than you can imagine.', 'gmfw' ),
						__( 'All the best with your wedding plans and for the future.', 'gmfw' ),
						__( 'Of all the big life events we’ve celebrated together, this one tops the list. Congratulations!', 'gmfw' ),
					),
					__( 'For Myself', 'gmfw' )       => array(
						__( 'I love myself.', 'gmfw' ),
						__( 'Today, I will celebrate me.', 'gmfw' ),
						__( 'I choose to make today amazing.', 'gmfw' ),
					),
					__( 'Good Luck', 'gmfw' )        => array(
						__( 'You can totally do this!', 'gmfw' ),
						__( 'Good luck and don’t dare give up.', 'gmfw' ),
						__( 'Don’t stress. Do your best. Forget the rest.', 'gmfw' ),
					),
					__( 'Halloween', 'gmfw' )        => array(
						__( 'Happy Halloween from your friend.', 'gmfw' ),
						__( 'Don\'t be yourself! It\'s Halloween!', 'gmfw' ),
						__( 'I am hoping you have a great Halloween. Stay safe.', 'gmfw' ),
					),
					__( 'Retirement', 'gmfw' )       => array(
						__( 'Congratulations on your retirement!', 'gmfw' ),
						__( 'Wishing you the best on your life after this retirement. We’ll miss you.', 'gmfw' ),
						__( 'Best wishes and good luck for a fulfilling retirement.', 'gmfw' ),
					),
					__( 'Sympathy', 'gmfw' )         => array(
						__( 'Our family is keeping your family in our thoughts and prayers.', 'gmfw' ),
						__( 'Holding you close in my thoughts and hoping you are doing OK.', 'gmfw' ),
						__( 'Sending healing prayers and comforting hugs.', 'gmfw' ),
					),
					__( 'Spring', 'gmfw' )           => array(
						__( 'Sending you warm wishes for a lovely sunny Spring.', 'gmfw' ),
						__( 'Wishing you a glorious Spring!', 'gmfw' ),
						__( 'Spring is the perfect time to think of people like you – a season full of hope and happiness.', 'gmfw' ),
					),

				);
				/**
				 * Create occasions and gift messages posts.
				 */
				foreach ( $array as $key => $arr ) {
					$term = $key;
					wp_insert_term( $term, 'gmfw_occasions' );
					$counter = 1;
					foreach ( $arr as $message ) {
						$post_id = wp_insert_post(
							array(
								'post_type'    => 'gmfw_giftmessage',
								'post_title'   => $term . ' message ' . $counter,
								'post_content' => $message,
								'post_status'  => 'publish',
							)
						);
						wp_set_object_terms( $post_id, $term, 'gmfw_occasions', false );
						$counter++;
					}
				}

				wp_insert_term( 'Business Gifts', 'gmfw_occasions' );
				update_option( 'gmfw_import_data', '2' );
			}
		}
	}

	/**
	 * Admin notice.
	 *
	 * @since 1.0.0
	 */
	public function gmfw_import_data_admin_notice__premium_only() {
		?>
		<div class = "notice notice-success is-dismissible">
			<p><?php echo esc_html( __( 'Congratulations, occasions, and gift messages were successfully imported. Note that in order to import the occasions and the gift messages again you need to delete them and the option to import will reappear', 'gmfw' ) ); ?></p>
		</div>
		<?php
	}

	/**
	 * Plugin settings.
	 *
	 * @since 1.0.0
	 */
	public function gmfw_woocommerce_checkout_section() {

		if ( gmfw_fs()->is__premium_only() ) {
			if ( gmfw_fs()->can_use_premium_code() ) {

				$gmfw_woocommerce_checkout_section = get_option( 'gmfw_woocommerce_checkout_section', '' );
				if ( '' === $gmfw_woocommerce_checkout_section ) {
					$gmfw_woocommerce_checkout_section = 'woocommerce_after_order_notes';
				}
				?>

		<label for="gmfw_woocommerce_checkout_section">
		<select name='gmfw_woocommerce_checkout_section' id='gmfw_woocommerce_checkout_section'>

			<option value="woocommerce_checkout_before_customer_details" <?php selected( esc_attr( $gmfw_woocommerce_checkout_section ), 'woocommerce_checkout_before_customer_details' ); ?>><?php echo esc_html( __( 'Before customer details' ) ); ?></option>
			<option value="woocommerce_checkout_after_customer_details" <?php selected( esc_attr( $gmfw_woocommerce_checkout_section ), 'woocommerce_checkout_after_customer_details' ); ?>><?php echo esc_html( __( 'After customer details' ) ); ?></option>

			<option value="woocommerce_before_checkout_billing_form" <?php selected( esc_attr( $gmfw_woocommerce_checkout_section ), 'woocommerce_before_checkout_billing_form' ); ?>><?php echo esc_html( __( 'Before checkout billing form' ) ); ?></option>
			<option value="woocommerce_after_checkout_billing_form" <?php selected( esc_attr( $gmfw_woocommerce_checkout_section ), 'woocommerce_after_checkout_billing_form' ); ?>><?php echo esc_html( __( 'After checkout billing form' ) ); ?></option>

			<option value="woocommerce_before_checkout_shipping_form" <?php selected( esc_attr( $gmfw_woocommerce_checkout_section ), 'woocommerce_before_checkout_shipping_form' ); ?>><?php echo esc_html( __( 'Before checkout shipping form' ) ); ?></option>
			<option value="woocommerce_after_checkout_shipping_form" <?php selected( esc_attr( $gmfw_woocommerce_checkout_section ), 'woocommerce_after_checkout_shipping_form' ); ?>><?php echo esc_html( __( 'After checkout shipping form' ) ); ?></option>

			<option value="woocommerce_before_order_notes" <?php selected( esc_attr( $gmfw_woocommerce_checkout_section ), 'woocommerce_before_order_notes' ); ?>><?php echo esc_html( __( 'Before order notes' ) ); ?></option>
			<option value="woocommerce_after_order_notes" <?php selected( esc_attr( $gmfw_woocommerce_checkout_section ), 'woocommerce_after_order_notes' ); ?>><?php echo esc_html( __( 'After order notes' ) ); ?></option>

			<option value="woocommerce_review_order_before_payment" <?php selected( esc_attr( $gmfw_woocommerce_checkout_section ), 'woocommerce_review_order_before_payment' ); ?>><?php echo esc_html( __( 'Before payment' ) ); ?></option>
			<option value="woocommerce_review_order_after_payment" <?php selected( esc_attr( $gmfw_woocommerce_checkout_section ), 'woocommerce_review_order_after_payment' ); ?>><?php echo esc_html( __( 'After payment' ) ); ?></option>

			<option value="woocommerce_checkout_before_order_review" <?php selected( esc_attr( $gmfw_woocommerce_checkout_section ), 'woocommerce_checkout_before_order_review' ); ?>><?php echo esc_html( __( 'Before order review' ) ); ?></option>
			<option value="woocommerce_checkout_after_order_review" <?php selected( esc_attr( $gmfw_woocommerce_checkout_section ), 'woocommerce_checkout_after_order_review' ); ?>><?php echo esc_html( __( 'After order review' ) ); ?></option>

			<option value="woocommerce_review_order_before_submit" <?php selected( esc_attr( $gmfw_woocommerce_checkout_section ), 'woocommerce_review_order_before_submit' ); ?>><?php echo esc_html( __( 'Before submit' ) ); ?></option>
		</select>
		</label>
		
				<?php
			}
		}

		echo gmfw_premium_feature( '' );
		?>
		<p><?php echo esc_html( __( 'Select where do you want to show the gift message at checkout.', 'gmfw' ) ); ?></p>
		<?php
	}

	/**
	 * Plugin settings.
	 *
	 * @since 1.0.0
	 */
	public function gmfw_mandatory_occasion_field() {
		if ( gmfw_fs()->is__premium_only() ) {
			if ( gmfw_fs()->can_use_premium_code() ) {
				$gmfw_mandatory_occasion_field = get_option( 'gmfw_mandatory_occasion_field', '' );
				$checked                       = '1' === $gmfw_mandatory_occasion_field ? 'checked' : '';
				?>
				<label for="gmfw_mandatory_occasion_field" class='checkbox_toggle'>
					<input <?php echo esc_attr( $checked ); ?> type='checkbox' class='regular-text' name='gmfw_mandatory_occasion_field' id='gmfw_mandatory_occasion_field' value='1'>
				</label>
				<?php
			}
		}
		echo gmfw_premium_feature( '' );
	}

	/**
	 * Plugin settings.
	 *
	 * @since 1.0.0
	 */
	public function gmfw_import_data__premium_only() {
		$gmfw_import_data = get_option( 'gmfw_import_data', '' );
		$checked          = '1' === $gmfw_import_data ? 'checked' : '';
		?>
		<label for="gmfw_import_data" class='checkbox_toggle'>
			<input <?php echo esc_attr( $checked ); ?> type='checkbox' class='regular-text' name='gmfw_import_data' id='gmfw_import_data' value='1'>
		</label>
		<?php
	}

	/**
	 * Plugin settings.
	 *
	 * @since 1.0.0
	 */
	public function gmfw_enable_gift_message_suggestions() {
		if ( gmfw_fs()->is__premium_only() ) {
			if ( gmfw_fs()->can_use_premium_code() ) {
				$gmfw_enable_gift_message_suggestions = get_option( 'gmfw_enable_gift_message_suggestions', '' );
				$checked                              = '1' === $gmfw_enable_gift_message_suggestions ? 'checked' : '';
				?>
				<label for="gmfw_enable_gift_message_suggestions" class='checkbox_toggle'>
					<input <?php echo esc_attr( $checked ); ?> type='checkbox' class='regular-text' name='gmfw_enable_gift_message_suggestions' id='gmfw_enable_gift_message_suggestions' value='1'>
				</label>
				<?php
			}
		}
		echo gmfw_premium_feature( '' );
	}

	/**
	 * Plugin settings.
	 *
	 * @since 1.0.0
	 */
	public function gmfw_enable_gift_message() {
		$gmfw_enable_gift_message = get_option( 'gmfw_enable_gift_message', '' );
		$checked                  = '1' === $gmfw_enable_gift_message ? 'checked' : '';
		?>
		<label for="gmfw_enable_gift_message" class='checkbox_toggle'>
			<input <?php echo esc_attr( $checked ); ?> type='checkbox' class='regular-text' name='gmfw_enable_gift_message' id='gmfw_enable_gift_message' value='1'>
		</label>
		<?php
	}




	/**
	 * Plugin settings.
	 *
	 * @since 1.0.0
	 */
	public function gmfw_gift_message_fee() {
		if ( gmfw_fs()->is__premium_only() ) {
			if ( gmfw_fs()->can_use_premium_code() ) {

				$gmfw_gift_message_fee = get_option( 'gmfw_gift_message_fee', '' );

				$gmfw_gift_message_fee = is_numeric( $gmfw_gift_message_fee ) ? $gmfw_gift_message_fee : '';

				?>
				<label for="gmfw_gift_message_fee" class='checkbox_toggle'>
					<?php echo get_woocommerce_currency_symbol(); ?>
					<input type='text' class='small-text'   name='gmfw_gift_message_fee' id='gmfw_gift_message_fee' value='<?php echo esc_attr( $gmfw_gift_message_fee ); ?>'>
					<p><?php echo esc_html( __( 'Set a fee for the gift message. If you want to offer it for free, simply leave the field blank.', 'gmfw' ) ); ?></p>
				</label>
				<?php

			}
		}
		echo gmfw_premium_feature( '' );
	}

	/**
	 * Plugin settings.
	 *
	 * @since 1.0.0
	 */
	public function gmfw_mandatory_gift_message_field() {
		$gmfw_mandatory_gift_message_field = get_option( 'gmfw_mandatory_gift_message_field', '' );
		$checked                           = '1' === $gmfw_mandatory_gift_message_field ? 'checked' : '';
		?>
		<label for="gmfw_mandatory_gift_message_field" class='checkbox_toggle'>
			<input <?php echo esc_attr( $checked ); ?> type='checkbox' class='regular-text' name='gmfw_mandatory_gift_message_field' id='gmfw_mandatory_gift_message_field' value='1'>
		</label>
		<?php
	}


	/**
	 * Plugin post type.
	 *
	 * @since 1.0.0
	 */
	public function gmfw_gift_message_posttype__premium_only() {

			// Set UI labels for Custom Post Type.
			$labels = array(
				'name'               => _x( 'Gift messages', 'Post Type General Name', 'gmfw' ),
				'singular_name'      => _x( 'Gift messages', 'Post Type Singular Name', 'gmfw' ),
				'menu_name'          => __( 'Gift messages', 'gmfw' ),
				'parent_item_colon'  => __( 'Parent Rule', 'gmfw' ),
				'all_items'          => __( 'All Gift messages', 'gmfw' ),
				'view_item'          => __( 'View Gift message', 'gmfw' ),
				'add_new_item'       => __( 'Add New gift message', 'gmfw' ),
				'add_new'            => __( 'Add New', 'gmfw' ),
				'edit_item'          => __( 'Edit gift message', 'gmfw' ),
				'update_item'        => __( 'Update gift message', 'gmfw' ),
				'search_items'       => __( 'Search gift messages', 'gmfw' ),
				'not_found'          => __( 'Not Found', 'gmfw' ),
				'not_found_in_trash' => __( 'Not found in Trash', 'gmfw' ),
			);

			// Set other options for Custom Post Type.
			$args = array(
				'label'               => __( 'Gift messages', 'gmfw' ),
				'description'         => __( 'Gift messages', 'gmfw' ),
				'labels'              => $labels,
				'supports'            => array( 'title', 'editor' ),
				'taxonomies'          => array( 'gmfw_occasions' ),
				'hierarchical'        => false,
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => 5,
				'can_export'          => false,
				'has_archive'         => false,
				'exclude_from_search' => false,
				'publicly_queryable'  => false,
				'capability_type'     => 'page',
				'rewrite'             => false,
			);

			// Registering message post type.
			register_post_type( 'gmfw_giftmessage', $args );

			// Set UI labels for taxonomy.
			$labels = array(
				'all_items'          => __( 'All occasions', 'gmfw' ),
				'view_item'          => __( 'View occasion', 'gmfw' ),
				'add_new_item'       => __( 'Add New Occasion', 'gmfw' ),
				'add_new'            => __( 'Add New', 'gmfw' ),
				'edit_item'          => __( 'Edit occasion', 'gmfw' ),
				'update_item'        => __( 'Update occasion', 'gmfw' ),
				'search_items'       => __( 'Search occasions', 'gmfw' ),
				'not_found'          => __( 'Not Found', 'gmfw' ),
				'not_found_in_trash' => __( 'Not found in Trash', 'gmfw' ),
			);
			// Registering taxonomy.
			register_taxonomy(
				'gmfw_occasions',
				array( 'gmfw_giftmessage' ),
				array(
					'show_in_menu'   => false,
					'hierarchical'   => false,
					'label'          => 'Occasions',
					'labels'         => $labels,
					'singular_label' => 'Occasion',
					'rewrite'        => array(
						'slug'       => 'gmfw_occasions',
						'with_front' => false,
					),
					'meta_box_cb'    => 'post_categories_meta_box',
				)
			);

			register_taxonomy_for_object_type( 'gmfw_occasions', 'gmfw_giftmessage' );

	}


	/**
	 * Hide parent dropdown
	 *
	 * @param [type] $args object.
	 */
	public function hide_parent_dropdown_select__premium_only( $args ) {
		if ( 'occasions' === $args['taxonomy'] ) {
			$args['echo'] = false;
		}
		return $args;
	}

	/**
	 * Maximum_length function
	 *
	 * @param int $value number.
	 * @return int
	 */
	public function gmfw_sanitize_maximum_length( $value ) {
		if ( is_numeric( $value ) === false ) {
			return 160;
		} else {
			return $value;
		}
	}

	/**
	 * Admin plugin bar.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	static function gmfw_admin_plugin_bar() {
		return '<div class="gmfw_admin_bar">' . esc_html( __( 'Developed by', 'gmfw' ) ) . ' <a href="https://powerfulwp.com/" target="_blank">PowerfulWP</a> | <a href="https://powerfulwp.com/gift-message-for-woocommerce-premium/" target="_blank" >' . esc_html( __( 'Premium', 'gmfw' ) ) . '</a> | <a href="https://powerfulwp.com/docs/woocommerce-gift-message-premium/" target="_blank" >' . esc_html( __( 'Documents', 'gmfw' ) ) . '</a></div>';
	}

	/**
	 * occasions field function.
	 *
	 * @param object $order order object.
	 * @return void
	 */
	public function gmfw_occasions_field__premium_only( $order ) {

		$result = get_terms(
			array(
				'taxonomy'   => 'gmfw_occasions',
				'hide_empty' => false,
				'orderby'    => 'id',
				'order'      => 'ASC',
			)
		);

		if ( ! ( empty( $result ) ) ) {
			$options = array( '' => __( 'Select Occasion', 'gmfw' ) );

			foreach ( $result as $key => $taxonomy ) {
					$options[ $taxonomy->term_id ] = $taxonomy->name;
			}
			$options[ __( '0', 'gmfw' ) ] = __( 'Other', 'gmfw' );

			$field_value = '';
			if ( ! empty( $order ) ) {
				$field_value = $order->get_meta( 'gmfw_occasion' );
			}

			woocommerce_form_field(
				'gmfw_occasion',
				array(
					'type'        => 'select',
					'class'       => array( 'form-row-wide' ),
					'label'       => __( 'Select Occasion', 'gmfw' ),
					'options'     => $options,
					'placeholder' => __( 'Select Occasion', 'gmfw' ),
					'default'     => '',
				),
				$field_value
			);
		}

	}

	/**
	 * Message field function.
	 *
	 * @param object $order order object.
	 * @return void
	 */
	public function gmfw_message_field( $order ) {

		$field_value = '';
		if ( ! empty( $order ) ) {
			$field_value = $order->get_meta( 'gmfw_gift_message' );
		}

		woocommerce_form_field(
			'gmfw_gift_message',
			array(
				'type'        => 'textarea',
				'class'       => array( 'form-row-wide' ),
				'label'       => esc_html( __( 'Gift Message', 'gmfw' ) ),
				'placeholder' => esc_html( __( 'Write your gift message here...', 'gmfw' ) ),
				'default'     => '',
			),
			$field_value
		);
	}

	/**
	 * Suggestions function.
	 *
	 * @return void
	 */
	public function gmfw_suggestions__premium_only() {
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
	 * Exclude_custom_fields
	 *
	 * @since 1.3.0
	 */
	public function gmfw_exclude_custom_fields( $protected, $meta_key, $meta_type ) {

		if ( in_array( $meta_key, array( 'gmfw_occasion', 'gmfw_occasion_name', 'gmfw_gift_message' ) ) ) {
			return true;
		}

		  return $protected;
	}

	/**
	 * Plugin review.
	 */
	public function plugin_review() {

		// Get activation date.
		$gmfw_activation_date = get_option( 'gmfw_activation_date', '' );
		if ( '' === $gmfw_activation_date ) {
			// Set activation date.
			$gmfw_activation_date = date_i18n( 'Y-m-d H:i:s' );
			update_option( 'gmfw_activation_date', $gmfw_activation_date );
		}

		// Get review user action.
		$gmfw_review_action = get_option( 'gmfw_review_action', '' );
		if ( '' === $gmfw_review_action ) {
			$date = strtotime( '+7 days' );
			if ( $date < strtotime( $gmfw_activation_date ) ) {
				add_action( 'admin_notices', array( $this, 'plugin_review_notice' ) );
			}
		}
	}

	/**
	 * Plugin review notice.
	 */
	public function plugin_review_notice() {
		$plugin_name        = 'Gift Message for WooCommerce';
		$plugin_link        = 'https://wordpress.org/plugins/gift-message-for-woocommerce/';
		$plugin_review_link = 'https://wordpress.org/support/plugin/gift-message-for-woocommerce/reviews/?filter=5#new-post';
		?>
			<div id="gmfw_review_notice" class = "notice notice-success is-dismissible">
				<p>
					<?php
						echo sprintf( esc_html( __( 'Awesome, you\'ve been using %s Plugin for a while. Could you please do us a BIG favor and give it a 5-star rating on WordPress? Just to help us spread the word and boost our motivation.', 'gmfw' ) ), '<a target="_blank" href="' . esc_attr( $plugin_link ) . '">' . $plugin_name . '</a>' );
					?>
				</p>
				<p>
					<a target="_blank" class="button is-primary gmfw_action" data="ok-rate" href="<?php echo esc_attr( $plugin_review_link ); ?>"><?php echo esc_html( __( 'Ok, you deserve it', 'gmfw' ) ); ?></a>
					&nbsp; &nbsp;
					<a target="_blank" data="already-did" class="gmfw_action" href="#"><?php echo esc_html( __( 'I already did', 'gmfw' ) ); ?></a>
					&nbsp; &nbsp;
					<a target="_blank" data="not-good" class="gmfw_action" href="#"><?php echo esc_html( __( 'No, not good enough', 'gmfw' ) ); ?></a>
				</p>
			</div>
		<?php
	}


	/**
	 * The function that handles ajax requests.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function gmfw_ajax() {
		/**
		 * Security check.
		 */
		if ( ! isset( $_POST['gmfw_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gmfw_wpnonce'] ) ), 'gmfw-nonce' ) ) {
			exit;
		}			
 
		$gmfw_service = ( isset( $_POST['gmfw_service'] ) ) ? sanitize_text_field( wp_unslash( $_POST['gmfw_service'] ) ) : '';
		$gmfw_value   = ( isset( $_POST['gmfw_value'] ) ) ? sanitize_text_field( wp_unslash( $_POST['gmfw_value'] ) ) : '';
 
		// Review action.
		if ( 'gmfw_review_action' === $gmfw_service ) {
			update_option( 'gmfw_review_action', $gmfw_value );
		}

		// Add to cart.
		if ( 'gmfw_add_to_cart' === $gmfw_service ) {
			$cart = new GMFW_CART();
			echo $cart->add_to_cart( $gmfw_value );
			exit;
		}
		// Remove from cart.
		if ( 'gmfw_remove_from_cart' === $gmfw_service ) {
			$cart = new GMFW_CART();
			echo $cart->remove_from_cart( $gmfw_value );
			exit;
		}

	}

	/**
	 * Columns order
	 *
	 * @param array $columns columns array.
	 * @since 1.0.0
	 * @return array
	 */
	public function orders_list_columns_order__premium_only( $columns ) {
		$reordered_columns = array();

		// Inserting columns to a specific location.
		foreach ( $columns as $key => $column ) {
			$reordered_columns[ $key ] = $column;
			if ( 'shipping_address' === $key ) {
				// Inserting after "Status" column.
				$reordered_columns['gmfw_gift_message'] = __( 'Gift Message', 'pdfclw' );
			}
		}
		return $reordered_columns;
	}

	/**
	 * Print gift message in column
	 *
	 * @param string $column column name.
	 * @param int    $post_id post number.
	 * @since 1.0.0
	 */
	public function orders_list_columns__premium_only( $column, $post_id ) {

		switch ( $column ) {
			case 'gmfw_gift_message':
				$order = wc_get_order( $post_id );
				if ( $order ) {
					$gift_message = $order->get_meta( 'gmfw_gift_message', true );
					if ( ! empty( $gift_message ) ) {
						echo wp_kses_post( $gift_message );
						echo '<textarea class="gift_message_textarea" style="display:none">' . esc_html( $gift_message ) . '</textarea>';
						echo '<button style="display:none;" class="button button-primary gmfw_copy_text" href="#"   data-btn="' . esc_attr( __( 'Copy gift message', 'gmfw' ) ) . '" data-success="' . esc_attr( __( 'Copied successfully', 'gmfw' ) ) . '" >' . esc_html( __( 'Copy gift message', 'gmfw' ) ) . '</button>';
					}
				}
				break;
		}
	}

		/**
		 * Update default options
		 *
		 * @return void
		 */
	public function update_default_options() {

		if ( false === get_option( 'gmfw_enable_gift_message', false ) ) {
			 update_option( 'gmfw_enable_gift_message', '1' );
		}

		if ( gmfw_fs()->is__premium_only() ) {
			if ( gmfw_fs()->can_use_premium_code() ) {

				if ( false === get_option( 'gmfw_enable_gift_message_suggestions', false ) ) {
					update_option( 'gmfw_enable_gift_message_suggestions', '1' );
				}
			}
		}

	}


}
