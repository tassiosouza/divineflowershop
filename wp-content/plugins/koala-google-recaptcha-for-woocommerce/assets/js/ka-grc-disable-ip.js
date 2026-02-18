jQuery( document ).ready( function ($){
	var current_ip = ka_excluded_ips.ipAddress;
	var excluded_ip = ka_excluded_ips.excludedIps;
	if (ka_excluded_ips.is_disabled === 'yes') {
    	$('button#place_order').prop('disabled', true);
    }

    // $(document).on('click', '#place_order', function(){
    // 	for (var i = 1; i <= 10; i++) {
    // 		setTimeout(function(){
	// 			ka_grc_cookie_set();
    // 		},1000 * i);
    // 	}
	// });

	// function ka_grc_cookie_set(){
	// 	let block_duration = getCookie('block_duration');

    // 	if (typeof block_duration !== 'undefined' && block_duration) {
    // 		$('button#place_order').prop('disabled', true);
    // 	}

    // 	setTimeout(function () {
	//         $('button#place_order').prop('disabled', false);
	//         if(block_duration){
	// 	        $('.woocommerce-NoticeGroup-checkout').html('');
	// 	    }
	//     }, block_duration * 1000);
	// }

	jQuery(document).on('updated_checkout', function () {
	    if (excluded_ip.indexOf(current_ip) !== -1) {
	        $('button#place_order').prop('disabled', true);
	    }
	    if (ka_excluded_ips.is_disabled === 'yes') {
	    	$('button#place_order').prop('disabled', true);
	    }
	});

	setTimeout( function(){
			var current_ip = ka_excluded_ips.ipAddress;
			var excluded_ip = ka_excluded_ips.excludedIps;
			var placeOrderButton = $('button.wc-block-components-checkout-place-order-button');
			if(placeOrderButton.length) {
				if (excluded_ip.indexOf(current_ip) !== -1) {
					$('button.wc-block-components-checkout-place-order-button').prop('disabled', true);
				}
				if (ka_excluded_ips.is_disabled === 'yes') {
					$('button.placewc-block-components-checkout-place-order-button_order').prop('disabled', true);
				}
			}
	}, 2000);


	function getCookie(name) {
	    const value = `; ${document.cookie}`;
	    const parts = value.split(`; ${name}=`);
	    if (parts.length === 2) return parts.pop().split(';').shift();
	}

});

document.addEventListener('DOMContentLoaded', function () {
    let clickCount = 0;
    const rateSections = ka_excluded_ips.rate_limiter;
    let currentSectionIndex = 0;
    let firstClickTime = 0;
    const cookieName = 'ka_button_disabled_until';

    // Handle button click
    function handleButtonClick(e) {
        // console.log('cookieName');
        const currentTime = new Date().getTime();
        const disabledUntil = getCookieBlock(cookieName);
        if (disabledUntil && currentTime < parseInt(disabledUntil, 10)) {
            e.preventDefault();
            const remainingTime = Math.ceil((parseInt(disabledUntil, 10) - currentTime) / 1000);
            Ka_google_recapcha_showNotice(`Maximum checkout attempts reached. Please wait ${remainingTime} seconds before trying again.`);
            return false;
        }

        if (clickCount === 0) {
            firstClickTime = currentTime;
        }

        clickCount++;
        const section = rateSections[currentSectionIndex];
        const timeWindow = section.seconds * 1000;

        if (clickCount > section.limit) {
            const timeSinceFirstClick = currentTime - firstClickTime;
            if (timeSinceFirstClick <= timeWindow) {
                if (currentSectionIndex == rateSections.length - 1) {
                    e.preventDefault();
                    sendAjaxBlockRequest();
                    return false;
                }else{
                    const holdDuration = (section.seconds - (timeSinceFirstClick / 1000)) * 1000;
                    setCookie(cookieName, currentTime + holdDuration, holdDuration / 60000);
                    disablePlaceOrderButton(holdDuration);
                    e.preventDefault();
                    Ka_google_recapcha_showNotice(`Maximum checkout attempts reached. Please wait ${Math.ceil(holdDuration / 1000)} seconds before trying again.`);
                    return false;
                }
            } else {
                clickCount = 1;
                firstClickTime = currentTime;
                currentSectionIndex = Math.min(currentSectionIndex + 1, rateSections.length - 1);
            }
        }
    }

    // Show WooCommerce notice
    function Ka_google_recapcha_showNotice(message) {
        const noticeContainer_shortcode = document.querySelector('.woocommerce-NoticeGroup-checkout');
        setTimeout(function(){
            const noticeContainer = document.querySelector('.wc-block-components-notice-banner');
            if (noticeContainer) {
                jQuery('.wc-block-components-notice-banner').html('');
				jQuery('.wc-block-components-notice-banner').show();
                const noticeMessage = document.createElement('div');
                noticeMessage.innerHTML = `
                    <div class="wc-block-components-notice-banner__content">
                        <div>${message}</div>
                    </div>
                `;
                noticeContainer.appendChild(noticeMessage);
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth',
                });
            }else{
                const noticeContainer = jQuery('.wc-block-components-notices').first();
				jQuery('.wc-block-components-notice-banner').show();
                const noticeMessage = `
                        <div class="wc-block-store-notice wc-block-components-notice-banner is-error is-dismissible">
                            <div>${message}</div>
                        </div>
                    `;
                noticeContainer.html(noticeMessage);
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth',
                });
            }
        }, 2000);
        
        if(noticeContainer_shortcode){
            noticeContainer_shortcode.innerHTML = '';
            const noticeMessage = document.createElement('div');
            noticeMessage.classList.add(
                'woocommerce-error',
                'woocommerce-message',
                'is-error'
            );
            noticeMessage.innerHTML = `
                <div class="woocommerce-error-message">
                    <div>${message}</div>
                </div>
            `;
            noticeContainer_shortcode.appendChild(noticeMessage);
            window.scrollTo({
                top: 0,
                behavior: 'smooth',
            });
        }

    }

    // Send AJAX request when final attempt limit is exceeded
    function sendAjaxBlockRequest() {
        const holdDuration = ka_excluded_ips.disbale_time;
        const disableDuration = holdDuration * 60000;
        const currentTime = new Date().getTime();
        let billingEmail = '';
        const billingEmailblock = document.querySelector('.wc-block-components-address-form__email #email');
        if(billingEmailblock){
            billingEmail = billingEmailblock.value;
        }else{
            const billingEmailshortcode = document.querySelector('#billing_email');
            if (billingEmailshortcode) {
                billingEmail = billingEmailshortcode.value;
            }
        }
        
        
        setCookie(cookieName, currentTime + disableDuration, disableDuration / 60000);

        fetch(ka_excluded_ips.admin_url, {
            method: 'POST',
            body: new URLSearchParams({
                action: 'ka_grc_block_limiter_time',
                nonce : ka_excluded_ips.nonce,
                billing_email: billingEmail,
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.data.message) {
                
                setTimeout(() => {
                    disablePlaceOrderButton(disableDuration);
                const noticeContainer_shortcode = document.querySelector('.woocommerce-NoticeGroup-checkout');
                if (noticeContainer_shortcode) {
                    noticeContainer_shortcode.innerHTML = '';
                    const noticeMessage = document.createElement('div');
                    noticeMessage.classList.add(
                        'woocommerce-error', 
                        'woocommerce-message',
                        data.success ? 'is-success' : 'is-error'
                    );
                    noticeMessage.innerHTML = `
                        <div class="woocommerce-error-message">
                            <div>${data.data.message}</div>
                        </div>
                    `;
                    console.log(data.data.message);
                    noticeContainer_shortcode.appendChild(noticeMessage);
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth',
                    });
                }
                }, 200);
				
				setTimeout(() => {
					const noticeContainer = document.querySelector('.wc-block-components-notice-banner');
                 if (noticeContainer) {
                    jQuery('.wc-block-components-notice-banner').html('');
					 jQuery('.wc-block-components-notice-banner').show();
                    const noticeMessage = document.createElement('div');
                    noticeMessage.innerHTML = `
                        <div class="wc-block-components-notice-banner__content">
                            <div>${data.data.message}</div>
                        </div>
                    `;
                    noticeContainer.appendChild(noticeMessage);
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth',
                    });
                }
				}, 300);
            }

        });
    }

    // Function to disable the Place Order button
    function disablePlaceOrderButton(duration) {
        setTimeout(() => {
            const placeOrderButton = document.querySelector('.wc-block-components-checkout-place-order-button');
            if (placeOrderButton) {
                placeOrderButton.disabled = true;
                placeOrderButton.setAttribute('disabled', 'true');
            }
            const place_order = document.getElementById('place_order');
            if (place_order) {
                place_order.disabled = true;
                place_order.setAttribute('disabled', 'true');
            }
           
        }, 1000);
    
        setTimeout(() => {
            enablePlaceOrderButton();
        }, duration);
    }

    // Function to enable the Place Order button
    function enablePlaceOrderButton() {
        const place_order = document.querySelector('#place_order');
        const placeOrderButton = document.querySelector('.wc-block-components-checkout-place-order-button');
        if (placeOrderButton) {
            jQuery('.wc-block-components-notice-banner').html('');
            jQuery('.wc-block-components-notice-banner').hide();
            placeOrderButton.disabled = false;
            placeOrderButton.removeAttribute('disabled');
        }
        
        if(place_order){
            const noticeContainer_short = document.querySelector('.woocommerce-NoticeGroup-checkout');
            if(noticeContainer_short){
                noticeContainer_short.innerHTML = '';
            }
            place_order.disabled = false;
            place_order.removeAttribute('disabled');
        }
    }

    // Function to set a cookie
    function setCookie(name, value, minutes) {
        const date = new Date();
        date.setTime(date.getTime() + minutes * 60000);
        document.cookie = `${name}=${encodeURIComponent(value)}; expires=${date.toUTCString()}; path=/`;
    }

    // Function to get a cookie value
    function getCookieBlock(name) {
        const cookies = document.cookie.split('; ');
        for (let cookie of cookies) {
            const [key, value] = cookie.split('=');
            if (key === name) {
                return decodeURIComponent(value);
            }
        }
        return null;
    }

    // Check for existing cookie and disable button if needed
    setTimeout(function(){
		 const disabledUntil = getCookieBlock(cookieName);
    if (disabledUntil && new Date().getTime() < parseInt(disabledUntil, 10)) {
        disablePlaceOrderButton(parseInt(disabledUntil, 10) - new Date().getTime());
    }
	}, 1000);
   

    // Attach event listener to the Place Order button
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'childList') {
                const placeOrderButton = document.querySelector('.wc-block-components-checkout-place-order-button');
                const place_order = document.querySelector('#place_order');
                if (placeOrderButton) {
                    placeOrderButton.removeEventListener('click', handleButtonClick);
                    placeOrderButton.addEventListener('click', handleButtonClick);
                }
                if(place_order){
                    place_order.removeEventListener('click', handleButtonClick);
                    place_order.addEventListener('click', handleButtonClick);
                }
            }
        });
    });

    // Start observing the body for changes
    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });
});

