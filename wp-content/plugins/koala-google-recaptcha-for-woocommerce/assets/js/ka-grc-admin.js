jQuery( document ).ready(
	function ($) {
		$( ".captcha_user_role_enalbe" ).select2();
		$( ".captcha_country_select_opt" ).select2();
		$( ".pay_order_add_captcha_user_role" ).select2();
		$( ".p_method_add_captcha_user_role" ).select2();
		$( ".captcha_product_review_user_role" ).select2();
		$( ".wp_comment_captcha_user_role" ).select2();
		$( ".ka_grc_sc_captcha_user_role_enable" ).select2();
		$("#ka_grc_rate_exclude_user_role").select2();
		v3_or_v2();

		jQuery( document ).on(
			"change",
			"input[name=captcha_type_option]",
			function () {
				v3_or_v2();
			}
		);

		function v3_or_v2() {
			jQuery( "input[name=captcha_custom_score_opt]" ).closest( "tr" ).hide();
			if ("v3" == jQuery( "input[name=captcha_type_option]:checked" ).val()) {
				jQuery( "input[name=captcha_custom_score_opt]" ).closest( "tr" ).show();
			}
		}
		// Update: version (1.3.0) Shortcode visible on checkbox enable/disable.
		if ($( ".ka-grc-shortcode-checkbox" ).is( ":checked" )) {
			$( ".ka-grc-shortcode-show" ).show();
		} else {
			$( ".ka-grc-shortcode-show" ).hide();
		}
		$( ".ka-grc-shortcode-checkbox" ).click(
			function () {
				if ($( this ).is( ":checked" )) {
					$( ".ka-grc-shortcode-show" ).show();
				} else {
					$( ".ka-grc-shortcode-show" ).hide();
				}
			}
		);
	}
);

jQuery(document).ready(function ($) {
    const container = $('.ka-attempt-limits');

    // Add new rate limit
    container.on('click', '.ka-add-rate-limit', function () {
        const index = container.find('.ka-rate-limit-item').length;
        const newItem = `
            <tr class="ka-rate-limit-item" data-index="${index}">
                <td class="ka-attempt-per">
                    <input class="ka-grc-limit" type="number" name="ka_grc_set_rate_limit[${index}]" value="">
                </td>
                <td class="ka-second-per">
                    <input class="ka-grc-limit" type="number" name="ka_grc_set_rate_seconds[${index}]" value="">
                </td>
        	    <td><button type="button" class="button ka-remove-rate-limit button-primary">Remove</button></td>
            </tr>
        `;
         $(this).closest('tr').before(newItem); // Append the new item
    });

    // Remove rate limit
    container.on('click', '.ka-remove-rate-limit', function () {
        $(this).closest('.ka-rate-limit-item').remove(); // Remove the parent item
    });

    // limter timer

    $(document).on('change', '.ka-grc-limit-checkbox', function () {
        ka_grc_limiter_setting();
    });

    ka_grc_limiter_setting();
    function ka_grc_limiter_setting(){
    	if($('.ka-grc-limit-checkbox').prop('checked')){
    		$('.ka-grc-limit-checkbox').closest('tr').nextAll().show();
    	}else{
    		$('.ka-grc-limit-checkbox').closest('tr').nextAll().hide();
    	}
    }
});