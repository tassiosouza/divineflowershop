// Update: version (1.3.0) Disable Place Order button on load.
jQuery( document ).ready(
	function ($) {
		jQuery( window ).on(
			"load",
			function () {
				if (ka_grc_captcha_v2_vars.ka_grc_is_checkout == 0) {
					ka_grc_readyFn();
				} else {
					if (ka_grc_captcha_v2_vars.ka_grc_checkout_captcha_enabled == 1) {
						jQuery( document )
						.find( "div.g-recaptcha.woo_checkout" )
						.closest( "form" )
						.find( 'button[type="submit"]' )
						.prop( "disabled", true );
					}
					if (ka_grc_captcha_v2_vars.ka_grc_payment_captcha_enabled == 1) {
						setTimeout(
							function () {
								jQuery( document )
								.find( "div.g-recaptcha.woo_payment" )
								.closest( "form" )
								.find( 'input[type="radio"]' )
								.prop( { disabled: true } );
							},
							1200
						);
					}
				}

				//Update: version(1.3.0) Ninja form render.
				if ($( ".nf-form-content" ).length) {
					grecaptcha.ready(
						function () {
							jQuery( document )
							.find( "#ka-grc-ninja-recaptcha" )
							.closest( "form" )
							.find( 'input[type="submit"]' )
							.prop( "disabled", true );
							if ($( "#ka-grc-ninja-recaptcha" ).length > 0) {
								grecaptcha.render(
									"ka-grc-ninja-recaptcha",
									{
										sitekey: ka_grc_captcha_v2_vars.ka_site_key,
										callback: ka_captcha_validation_success,
									}
								);
							}
						}
					);
				}

				//Update: version(1.3.0) Ultimate member
				if ($( "#um_account_submit_general" ).length) {
					let ka_grc_link = document.querySelectorAll( ".um-account-link" );

					ka_grc_link.forEach(
						function (link) {
							let ka_grc_observer = new MutationObserver(
								function (mutations) {
									mutations.forEach(
										function (mutation) {
											if (
											mutation.attributeName === "class" &&
											link.classList.contains( "current" )
											) {
												let ka_grc_tab = $( link ).data( "tab" );
												$( ".ka_google_recaptcha" )
												.detach()
												.insertBefore( `#um_account_submit_${ka_grc_tab}` );
											}
										}
									);
								}
							);

							let ka_grc_config = { attributes: true };
							ka_grc_observer.observe( link, ka_grc_config );
						}
					);

					let $ka_grc_current_link = $( ".um-account-link.current" );
					if ($ka_grc_current_link.data( "tab" ) === "general") {
							$( ".ka_google_recaptcha" )
							.detach()
							.insertBefore( "#um_account_submit_general" );
					} else if ($ka_grc_current_link.data( "tab" ) === "password") {
						$( ".ka_google_recaptcha" )
						.detach()
						.insertBefore( "#um_account_submit_password" );
					} else if ($ka_grc_current_link.data( "tab" ) === "privacy") {
						$( ".ka_google_recaptcha" )
						.detach()
						.insertBefore( "#um_account_submit_privacy" );
					} else if ($ka_grc_current_link.data( "tab" ) === "delete") {
						$( ".ka_google_recaptcha" )
						.detach()
						.insertBefore( "#um_account_submit_delete" );
					}
					$( ".um-account-link" ).on(
						"click",
						function () {
							grecaptcha.reset();
							ka_grc_readyFn();
						}
					);
				}
			}
		);

		// Update: version (1.3.0) Jetpack forms
		if ($( ".wp-block-jetpack-contact-form" ).length) {
			$( ".ka_grc_jetpack" ).detach().insertBefore( ".wp-block-jetpack-button" );
		}
	}
);

function ka_grc_readyFn() {

	jQuery( document )
	.find( "div.g-recaptcha" )
	.closest( "form" )
	.find( 'button[type="submit"]' )
	.addClass( "disabled" );
	jQuery( document )
	.find( "div.g-recaptcha" )
	.closest( "form" )
	.find( 'input[type="submit"]' )
	.addClass( "disabled" );
	jQuery( document )
	.find( "div.g-recaptcha" )
	.closest( "form" )
	.find( 'button[type="submit"]' )
	.click(
		function (event) {
			event.preventDefault();
		}
	);
	jQuery( document )
	.find( "div.g-recaptcha" )
	.closest( "form" )
	.find( 'input[type="submit"]' )
	.click(
		function (event) {
			event.preventDefault();
		}
	);
	jQuery( document ).find( "login" ).closest( ".g-recaptcha" ).addClass( "login" );
	jQuery( document )
	.find( "register" )
	.closest( ".g-recaptcha" )
	.addClass( "register" );
	jQuery( document ).find( "login" ).closest( ".g-recaptcha" ).addClass( "login" );
	jQuery( document ).find( "submit" ).closest( ".g-recaptcha" ).addClass( "submit" );
	jQuery( document )
	.find( ".woo_payment" )
	.next( "#payment" )
	.find( 'input[type="radio"]' )
	.prop(
		{
			disabled: true,
		}
	);
	jQuery( window ).load(
		function () {
			jQuery( document )
			.find( ".woo_payment" )
			.next( "#payment" )
			.find( 'input[type="radio"]' )
			.prop(
				{
					disabled: true,
				}
			);
			jQuery( document )
			.find( ".woo_payment" )
			.siblings( "div" )
			.find( "input[type=radio]" )
			.prop(
				{
					disabled: true,
				}
			);
			jQuery( "div.woo_payment" )
			.closest( "form" )
			.find( 'label[id="ka_captcha_failed"]' )
			.css( "display", "" );
	// 		jQuery( document )
	// .find( "div.g-recaptcha" )
	// .closest( "#order_review" )
	// .find( 'button#place_order' )
	// .prop("disabled", true);
		}
	);
}
jQuery( document ).ready( ka_grc_readyFn() );

function ka_captcha_validation_expired(response) {
	jQuery( document )
	.find( "div.g-recaptcha" )
	.closest( "form" )
	.find( 'button[type="submit"]' )
	.addClass( "disabled" );
	jQuery( document )
	.find( "div.g-recaptcha" )
	.closest( "form" )
	.find( 'input[type="submit"]' )
	.addClass( "disabled" );
	jQuery( document )
	.find( "div.g-recaptcha" )
	.closest( "#order_review" )
	.find( 'button#place_order' )
	.addClass( "disabled" );
	jQuery( document )
	.find( "div.g-recaptcha" )
	.closest( "form" )
	.find( 'button[type="submit"]' )
	.click(
		function (event) {
			event.preventDefault();
		}
	);
	jQuery( document )
	.find( "div.g-recaptcha" )
	.closest( "form" )
	.find( 'input[type="submit"]' )
	.click(
		function (event) {
			event.preventDefault();
		}
	);
	jQuery( "div.g-recaptcha" )
	.closest( "form" )
	.find( 'label[id="ka_captcha_failed"]' )
	.css( "display", "" );
}
function ka_captcha_validation_failed(response) {
	jQuery( document )
	.find( "div.g-recaptcha" )
	.closest( "form" )
	.find( 'button[type="submit"]' )
	.addClass( "disabled" );
	jQuery( document )
	.find( "div.g-recaptcha" )
	.closest( "form" )
	.find( 'input[type="submit"]' )
	.addClass( "disabled" );
	jQuery( document )
	.find( "div.g-recaptcha" )
	.closest( "form" )
	.find( 'button[type="submit"]' )
	.click(
		function (event) {
			event.preventDefault();
		}
	);
	jQuery( document )
	.find( "div.g-recaptcha" )
	.closest( "form" )
	.find( 'input[type="submit"]' )
	.click(
		function (event) {
			event.preventDefault();
		}
	);
	$( ".rc-anchor-error-msg-container" ).show();
}

function ka_captcha_validation_success_jetpack(response) {
	ka_captcha_validation_success_general( "div.ka_grc_jetpack" );
}
function ka_captcha_validation_success_general(captcha_div) {
	jQuery( captcha_div )
	.closest( "p" )
	.find( 'button[type="submit"]' )
	.removeClass( "disabled" )
	.unbind( "click" );
	jQuery( captcha_div )
	.closest( "form" )
	.find( 'input[type="submit"]' )
	.removeClass( "disabled" )
	.unbind( "click" );
	jQuery( captcha_div )
	.closest( "form" )
	.find( 'button[type="submit"]' )
	.removeClass( "disabled" )
	.unbind( "click" );
	jQuery( captcha_div ).closest( "form" ).find( "#ka_captcha_failed" ).hide();
	jQuery( document )
	.find( captcha_div )
	.closest( "form" )
	.find( 'button[type="submit"]' )
	.prop( "disabled", false );
	jQuery( document )
	.find( captcha_div )
	.closest( "form" )
	.find( 'input[type="submit"]' )
	.prop( "disabled", false );
}
function ka_captcha_validation_success(response) {
	jQuery( "div.g-recaptcha" )
	.closest( "p" )
	.find( 'button[type="submit"]' )
	.removeClass( "disabled" )
	.unbind( "click" );
	jQuery( "div.g-recaptcha" )
	.closest( "form" )
	.find( 'input[type="submit"]' )
	.removeClass( "disabled" )
	.unbind( "click" );
	jQuery( "div.g-recaptcha" )
	.closest( "form" )
	.find( 'button[type="submit"]' )
	.removeClass( "disabled" )
	.unbind( "click" );
	jQuery( ".g-recaptcha" ).closest( "form" ).find( "#ka_captcha_failed" ).hide();
	ka_grc_enable_submit_button();

	//Update: version(1.3.0) Ninja form enable submit button.
	jQuery( "#ka-grc-ninja-recaptcha" )
	.closest( "form" )
	.find( 'input[type="submit"]' )
	.prop( "disabled", false );
	jQuery( document )
	.find( "div.g-recaptcha" )
	.closest( "#order_review" )
	.find( 'button#place_order' )
	.prop("disabled", false);
}

function post_coment_ka_captcha_validation_success(response) {
	jQuery( "div.word_post_button" )
	.closest( "form" )
	.find( 'button[type="submit"]' )
	.removeClass( "disabled" )
	.unbind( "click" );
	jQuery( "div.word_post_button" )
	.closest( "form" )
	.find( 'input[type="submit"]' )
	.removeClass( "disabled" )
	.unbind( "click" );

	jQuery( "div.word_post_button" )
	.closest( "form" )
	.find( 'label[id="ka_captcha_failed"]' )
	.css( "display", "none" );
	ka_grc_enable_submit_button();
}

function login_ka_captcha_validation_success(response) {
	jQuery( "div.woo_login" )
	.closest( "form" )
	.find( "button" )
	.removeClass( "disabled" )
	.unbind( "click" );

	jQuery( "div.woo_login" )
	.closest( "form" )
	.find( 'label[id="ka_captcha_failed"]' )
	.css( "display", "none" );
	ka_grc_enable_submit_button();
}
function regs_ka_captcha_validation_success(response) {
	jQuery( "div.woo_regs" )
	.closest( "form" )
	.find( "button" )
	.removeClass( "disabled" )
	.unbind( "click" );

	jQuery( "div.woo_regs" )
	.closest( "form" )
	.find( 'label[id="ka_captcha_failed"]' )
	.css( "display", "none" );
	ka_grc_enable_submit_button();
}
function ka_checkout_captcha_validation_success(response) {
	jQuery( "div.woo_checkout" )
	.closest( "form" )
	.find( "button" )
	.removeClass( "disabled" )
	.unbind( "click" );

	jQuery( "div.woo_checkout" )
	.siblings( "div" )
	.find( 'label[id="ka_captcha_failed"]' )
	.css( "display", "none" );
	// Update: version (1.3.0) Enable Place Order button on captcha success.
	jQuery( "#payment #place_order" ).prop( "disabled", false );
	ka_grc_enable_submit_button();
}
function pay_ka_captcha_validation_expired(response) {
	jQuery( "div.woo_payment" )
	.closest( "form" )
	.find( 'label[id="ka_captcha_failed"]' )
	.css( "display", "" );
	jQuery( document )
	.find( ".woo_payment" )
	.siblings( "div" )
	.find( "input[type=radio]" )
	.prop(
		{
			disabled: true,
		}
	);
}
function pay_ka_captcha_validation_failed(response) {
	jQuery( "div.woo_payment" )
	.closest( "form" )
	.find( 'label[id="ka_captcha_failed"]' )
	.css( "display", "" );
	jQuery( document )
	.find( ".woo_payment" )
	.siblings( "div" )
	.find( "input[type=radio]" )
	.prop(
		{
			disabled: true,
		}
	);
}
function pay_ka_captcha_validation_success(response) {
	jQuery( ".woo_payment" )
	.closest( "form" )
	.find( "#ka_captcha_failed" )
	.css( "display", "none" );
	jQuery( document )
	.find( ".woo_payment" )
	.siblings( "div" )
	.find( "input[type=radio]" )
	.prop(
		{
			disabled: false,
		}
	);
	jQuery("div.g-recaptcha.woo_payment")
        .closest("form").each(function () {
        jQuery(this)
            .find('input[type="radio"]')
            .prop('disabled', false) // Disable all radios first

        jQuery(this)
            .find('#payment input[type="radio"]')
            .first()
            .prop({ disabled: false, checked:true });
        jQuery(this)
            .find('#shipping_method input[type="radio"]')
            .first()
            .prop({ disabled: false });
    });
}
if (jQuery( "#comment_post_ID" ).length) {
	jQuery( ".ka_google_recaptcha" ).hide();
	let ka_grc_text = jQuery( ".ka_google_recaptcha" ).html();
	jQuery( "#comment_post_ID" ).closest( "p.form-submit" ).before( ka_grc_text );
}
// Update: version (1.3.0) Enable submit button.
function ka_grc_enable_submit_button() {
	jQuery( document )
	.find( "div.g-recaptcha" )
	.closest( "form" )
	.find( 'button[type="submit"]' )
	.prop( "disabled", false );
	jQuery( document )
	.find( "div.g-recaptcha" )
	.closest( "form" )
	.find( 'input[type="submit"]' )
	.prop( "disabled", false );
}
jQuery(document).ready(function($) {
    if (typeof ka_grc_captcha_v2_vars !== 'undefined') {
        var nonce = ka_grc_captcha_v2_vars.ka_grc_recaptcha_nonce;

        // Handle login form
        var $loginForm = $('form#loginform');
        if ($loginForm.length && $loginForm.find('input[name="recaptcha_nonce"]').length === 0) {
            var $loginNonce = $('<input>', {
                type: 'hidden',
                name: 'recaptcha_nonce',
                value: nonce
            });
            $loginForm.append($loginNonce);
        }

        // Handle registration form (BuddyPress, WooCommerce, or custom)
        var $registerForm = $('form#registerform, form.register, form#custom-register-form');
        if ($registerForm.length && $registerForm.find('input[name="recaptcha_nonce"]').length === 0) {
            var $registerNonce = $('<input>', {
                type: 'hidden',
                name: 'recaptcha_nonce',
                value: nonce
				
            });
            $registerForm.append($registerNonce);
        }
    }
});

