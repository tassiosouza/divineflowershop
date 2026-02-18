/**
 * External dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';

console.log(kgr_php_vars.checkout.title);

export const Block = ({ checkoutExtensionData }) => {
	const { setExtensionData } = checkoutExtensionData;


	const [isReCaptchaValid, setIsReCaptchaValid] = useState(false); // For checkout reCAPTCHA
	const [isReCaptchaPaymentValid, setIsReCaptchaPaymentValid] = useState(false); // For payment reCAPTCHA
	const isCheckoutEnabled = kgr_php_vars.checkout;
	const isPaymentEnabled = kgr_php_vars.payment;
	if(isCheckoutEnabled){
		var KgrSiteKey = kgr_php_vars.checkout.google_recapta_site_key;
		var KgrTitle = kgr_php_vars.checkout.title;
		var KaThemeColor = kgr_php_vars.checkout.themeColor;
		var KaSize = kgr_php_vars.checkout.size;
		var KaCapchaPosition = kgr_php_vars.checkout.ka_captcha_position;
		var dynamicCallback = kgr_php_vars.checkout.dynamicCallback;
		var ka_grc_classes = kgr_php_vars.checkout.ka_grc_classes;
	}
	
	if (isPaymentEnabled) {
		var KgrSiteKeyPayment = kgr_php_vars.payment.google_recapta_site_key;
		var KgrPaymentTitle = kgr_php_vars.payment.title;
		var KaPaymentThemeColor = kgr_php_vars.payment.themeColor;
		var KaPaymentSize = kgr_php_vars.payment.size;
		var KaPaymentCapchaPosition = kgr_php_vars.payment.ka_captcha_position;
		var PaymentdynamicCallback = kgr_php_vars.payment.dynamicCallback;
		var ka_Payment_classes = kgr_php_vars.payment.ka_grc_classes;
	}



	if (!isCheckoutEnabled && !isPaymentEnabled) {
		return null;
	}
	useEffect(() => {
		if(isCheckoutEnabled){
		const $captchaWrap = jQuery('.kl-ga-main-wrap');
		jQuery('.kl-ga-main-wrap').not($captchaWrap.first()).remove();

	// Proceed only if it exists
	if ($captchaWrap.length === 0) return;

			setTimeout(() => {
				switch (KaCapchaPosition) {
					case 'woocommerce_checkout_before_customer_details':
			          jQuery('.kl-ga-main-wrap').insertBefore('.wc-block-checkout__use-address-for-billing');
			          jQuery('.kl-ga-main-wrap').css("width", "100%");
			          break;
			        case 'woocommerce_before_checkout_billing_form':
			          jQuery('.kl-ga-main-wrap').insertBefore('.wp-block-woocommerce-checkout-contact-information-block');
			          break;
			        case 'woocommerce_before_order_notes':
			          jQuery('.kl-ga-main-wrap').insertBefore('.wc-block-checkout__order-notes');
			          break;
			        case 'woocommerce_after_order_notes':
			          jQuery('.kl-ga-main-wrap').insertAfter('.wc-block-checkout__order-notes');
			          break;
			        case 'woocommerce_review_order_before_payment':
			          jQuery('.kl-ga-main-wrap').insertBefore('.wc-block-checkout__payment-method');
			          break;
			        case 'woocommerce_review_order_before_submit':
			          jQuery('.kl-ga-main-wrap').insertBefore('.wp-block-woocommerce-checkout-actions-block');
			          break;
			        case 'woocommerce_review_order_after_submit':
			          jQuery('.kl-ga-main-wrap').insertAfter('.wp-block-woocommerce-checkout-actions-block');
			          break;
			        default:
			          jQuery('.kl-ga-main-wrap').insertBefore('.wp-block-woocommerce-checkout-actions-block');
				}
				
			}, 1000);
		}
		if (isPaymentEnabled) {
			setTimeout(() => {
				jQuery('.kl-ga-payment-wrap').insertBefore('.wc-block-checkout__payment-method');
			}, 1000);
		}
	}, [KaCapchaPosition, isPaymentEnabled, isCheckoutEnabled]);

	// Render the reCAPTCHA widgets
	useEffect(() => {
    const renderReCaptcha = () => {
        if (window.grecaptcha) {
            // Render checkout reCAPTCHA
            if (isCheckoutEnabled && !document.getElementById("kl-ga-main-wrap").childElementCount) {
                window.grecaptcha.render('kl-ga-main-wrap', {
                    sitekey: KgrSiteKey,
                    theme: KaThemeColor,
                    size: KaSize,
                    callback: onCheckoutReCaptchaSuccess,
                    'expired-callback': onCheckoutReCaptchaExpired,
                });
            }

            // Render payment reCAPTCHA
            if (isPaymentEnabled && !document.getElementById("kl-ga-payment-wrap").childElementCount) {
                window.grecaptcha.render('kl-ga-payment-wrap', {
                    sitekey: KgrSiteKeyPayment,
                    theme: KaPaymentThemeColor,
                    size: KaPaymentSize,
                    callback: onPaymentReCaptchaSuccess,
                    'expired-callback': onPaymentReCaptchaExpired,
                });
            }
        }
    };

    // Load reCAPTCHA script
    const script = document.createElement('script');
    script.src = 'https://www.google.com/recaptcha/api.js?onload=onReCaptchaLoad&render=explicit';
    script.async = true;
    script.defer = true;
    document.body.appendChild(script);

    window.onReCaptchaLoad = renderReCaptcha;

    // Cleanup
    return () => {
        document.body.removeChild(script);
        delete window.onReCaptchaLoad;
    };
}, [isCheckoutEnabled, isPaymentEnabled]);


	// Handle success for checkout reCAPTCHA
	const onCheckoutReCaptchaSuccess = (response) => {
		setIsReCaptchaValid(true);
		setExtensionData('kgr_google_recaptcha', 'kgr_checkout_recaptcha', response);
	};

	// Handle expiration for checkout reCAPTCHA
	const onCheckoutReCaptchaExpired = () => {
		setIsReCaptchaValid(false);
		setExtensionData('kgr_google_recaptcha', 'kgr_checkout_recaptcha', '');
	};

	// Handle success for payment reCAPTCHA
	const onPaymentReCaptchaSuccess = (response) => {
		setIsReCaptchaPaymentValid(true);
		setExtensionData('kgr_google_recaptcha', 'kgr_payment_recaptcha', response);
		const payemnt_place_order = document.querySelector('.wc-block-components-checkout-place-order-button');
		setTimeout(() => {
		var paymentcookies = getCookieBlock('ka_button_disabled_until');
			if(paymentcookies ){
				 payemnt_place_order.disabled = true;
		    }
	    }, 2000);
	};

	// Handle expiration for payment reCAPTCHA
	const onPaymentReCaptchaExpired = () => {
		setIsReCaptchaPaymentValid(false);
		setExtensionData('kgr_google_recaptcha', 'kgr_payment_recaptcha', '');
	};

	// Disable/enable payment methods and Place Order button
	useEffect(() => {
		setTimeout(() => {
			const placeOrderButton = document.querySelector('.wc-block-components-checkout-place-order-button');
			const paymentRadioButtons = document.querySelectorAll(
				'.wc-block-components-radio-control-accordion-option input[type="radio"]'
			);
			const paymentRadioButtonsShipping = document.querySelectorAll(
				'.wc-block-components-shipping-rates-control input[type="radio"]'
			);

			// Disable/enable Place Order button based on checkout reCAPTCHA
			if (placeOrderButton ) {
				placeOrderButton.disabled = isCheckoutEnabled && !isReCaptchaValid;
			}

			// Disable/enable payment methods based on payment reCAPTCHA
			if (isPaymentEnabled && paymentRadioButtons.length > 0 || paymentRadioButtonsShipping > 0) {
				paymentRadioButtons.forEach((radio) => {
					radio.disabled = !isReCaptchaPaymentValid;
				});
				paymentRadioButtonsShipping.forEach((radio) => {
					radio.disabled = !isReCaptchaPaymentValid;
				});
			}
		}, 1000);
	}, [isReCaptchaValid, isReCaptchaPaymentValid, isPaymentEnabled, isCheckoutEnabled]);

	// Attach click event for Place Order button to send reCAPTCHA responses
	useEffect(() => {
		const placeOrderButton = document.querySelector('.wc-block-components-checkout-place-order-button');

		const handlePlaceOrderClick = () => {
			const checkoutResponse = window.grecaptcha?.getResponse(0); // Get checkout reCAPTCHA response
			const paymentResponse = window.grecaptcha?.getResponse(1); // Get payment reCAPTCHA response

			setExtensionData('kgr_google_recaptcha', 'kgr_checkout_recaptcha', checkoutResponse || '');
			setExtensionData('kgr_google_recaptcha', 'kgr_payment_recaptcha', paymentResponse || '');
		};

		if (placeOrderButton) {
			placeOrderButton.addEventListener('click', handlePlaceOrderClick);
		}

		return () => {
			if (placeOrderButton) {
				placeOrderButton.removeEventListener('click', handlePlaceOrderClick);
			}
		};
	}, []);

	// Attach click event for Place Order button to send reCAPTCHA responses
	useEffect(() => {
		const placeCheckoutButton = document.querySelector('.wp-block-woocommerce-checkout');
		
		const handleCheckoutClick = () => {
		    if (jQuery('.kl-ga-inner-wrap').length) {
		    	setTimeout( function(){
		    		const paymentDefuatOrderButton = document.querySelector('.wc-block-components-checkout-place-order-button');
			    	if(!isReCaptchaValid){
			    		paymentDefuatOrderButton.disabled = isCheckoutEnabled && !isReCaptchaValid;
			    	}

		    	}, 2000);
		    }

		    setTimeout( function(){
		    	const defaultPlaceOrderButton = document.querySelector('.wc-block-components-checkout-place-order-button');
			    var paymentdisabledUntil = getCookieBlock('ka_button_disabled_until');
	    		if(paymentdisabledUntil ){
	    			 defaultPlaceOrderButton.disabled = true;
			    }
			}, 2000);
		};

		if (placeCheckoutButton) {
			placeCheckoutButton.addEventListener('click', handleCheckoutClick);
		}

		return () => {
			if (placeCheckoutButton) {
				placeCheckoutButton.removeEventListener('click', handleCheckoutClick);
			}
		};
		}, [isReCaptchaValid]);

		useEffect(() => {
			setTimeout( function(){
				const placeOrderButton = document.querySelector('.wc-block-components-checkout-place-order-button');
				 const disabledUntil = getCookieBlock('ka_button_disabled_until');
				 const isCheckoutPage = document.querySelector('.woo_checkout');
				if (placeOrderButton) {
					if (disabledUntil) {
						placeOrderButton.disabled = true;
					}else{
						if(isCheckoutPage){
							placeOrderButton.disabled = !isReCaptchaValid;
						}
					}
				}
			}, 2000);
			
		}, [isReCaptchaValid]);

		function getCookieBlock(cookieName) {
		    const name = cookieName + "=";
		    const decodedCookies = decodeURIComponent(document.cookie);
		    const cookieArray = decodedCookies.split(';');
		    for (let i = 0; i < cookieArray.length; i++) {
		        let cookie = cookieArray[i].trim();
		        if (cookie.indexOf(name) === 0) {
		            return cookie.substring(name.length, cookie.length);
		        }
		    }
		    return null; // Return null if the cookie does not exist
		}

	return (
		<>
			{/* Checkout reCAPTCHA */}
			{isCheckoutEnabled && (
				<div className="kl-ga-main-wrap">
					<label className="kgr_title">{KgrTitle}</label>
					<div id="kl-ga-main-wrap" className={`kl-ga-inner-wrap ${ka_grc_classes}`}></div>
				</div>
			)}

			{/* Payment reCAPTCHA */}
			{isPaymentEnabled && (
				<div className="kl-ga-payment-wrap">
					<label className="kgr_payment_title">{KgrPaymentTitle}</label>
					<div id="kl-ga-payment-wrap" className={`kl-ga-inner-wrap ${ka_Payment_classes}`}></div>
				</div>
			)}
		</>
	);
};
