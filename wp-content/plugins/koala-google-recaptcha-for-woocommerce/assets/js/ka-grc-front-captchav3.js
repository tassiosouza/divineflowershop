jQuery( document ).ready(
	function () {
		let ka_grc_sitekey = ka_grc_php_vars.v3_sitekey;
		let sitlang        = ka_grc_php_vars.v3_lang;

		jQuery( ".g-grecaptcha , .grecaptcha_required" )
		.closest( "form" )
		.submit(
			function (e) {
				grecaptcha
				.execute( ka_grc_sitekey, { action: "submit" } )
				.then(
					function (token) {
						ka_grc_current_form = jQuery( e.target );

						jQuery.ajax(
							{
								url: ka_grc_php_vars.admin_url,
								type: "POST",
								data: {
									action: "validation_captchav3",
									nonce: ka_grc_php_vars.nonce,
									captcha_token: token,
									//Update: version( 1.3.0 ) Added api_key.
									captcha_api: ka_grc_php_vars.ka_grc_api,
								},
								success: function (response) {
									console.log( response );
									if (false == response["success"]) {
										e.preventDefault();
										ka_grc_current_form.before(
											'<ul class="woocommerce-error" role="alert"><li> Sorry Your Are A Robot.</li></ul>'
										);
									} else {
										if(ka_grc_php_vars.ka_current_page == 'checkout'){
											
										//Update: version(1.3.0) Submit on success.
											ka_grc_current_form.trigger( "submit" );

										}else{

											ka_grc_current_form.unbind( "submit" );
											ka_grc_current_form
											.find( 'input[type="submit"], button[type="submit"]' )
											.click();
										}
									}
								},
								error: function (response) {
									console.log( response );
								},
							}
						);
					}
				);
			}
		);

		jQuery( document ).ajaxComplete(
			function () {
				let captcha_button = jQuery( document )
				.find( "input.grecaptcha_required" )
				.closest( "form" )
				.find( 'button[type="submit"]' );
				if (captcha_button.hasClass( "g-recaptcha" )) {
					return;
				} else {
					captcha_button.addClass( "g-recaptcha" );
					captcha_button.attr( "data-sitekey", ka_grc_php_vars.v3_sitekey );
				}
			}
		);
	}
);
