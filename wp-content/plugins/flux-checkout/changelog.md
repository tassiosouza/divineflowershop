2026-01-27 - version 2.25.0  
[fix] Country restrictions not working in the Google Address Autocomplete widget  
[fix] Broken Alignment of Product Attributes on Order Review  
[fix] Autocomplete not working   
[fix] Bug where state is incorrect while autocompleting the address for Philippines  
[fix] Issue where the international phone field wouldn't consider fixed-line numbers as valid  
[fix] Warning during checkout when the Shipping destination is default to customer shipping address  
[fix] Email Address Error When Creating an Account During Checkout with "Default to Customer Shipping Address" and "Use Same Address for Billing?" Enabled  
[fix] Fix: Street address order issue for European addresses where street number comes after the address_1  

2025-12-09 - version 2.24.0  
[fix] Country restrictions not working in the Google Address Autocomplete widget  
[fix] Broken Alignment of Product Attributes on Order Review  
[fix] Address Autocomplete not working because of changes in the DOM  

2025-11-17 - version 2.23.0  
[update] Added JS filter `flux_address_components`  
[fix] Compatibility with SkyVerge Authorize.net plugin  
[fix] Compatibility with WooCommerce Tiered Price Table by U2Code  
[fix] Mini Address Bar not showing the address details correctly when "Use Same Address for Billing?" is checked  

2025-10-01 - version 2.22.0  
[update] Allow regex in `flux_checkout_allowed_sources` filter  
[fix] Seller's bank account details not showing on the Thank you page  
[fix] Issue where the quantity selector breaks when increasing a variable product with only 1 item left in stock  
[fix] WoodMart theme broken Checkout Order table issue  
[fix] Warning Passing null to parameter #1 ($path) of type string is deprecated issue  
[fix] Fix attribute fee alignment issue with WooCommerce Attribute Swatches  

2025-09-03 - version 2.21.0  
[update] Add header link action hooks for extended customization: `flux_before_header_link` & `flux_after_header_link`  
[update] Updated translation for Norwegian Bokmål   
[update] Use context for the translation of payment heading  
[update] Upgrade Google autocomplete widget  
[update] Minor style update to the disabled stepper button  
[fix] Compatibility with Breakdance builder  
[fix] Broken show password button  
[fix] Locale issue when changing step  
[fix] Bug where the state field isn't populated when country is changed in Google Address autocomplete  
[fix] Quantity spinner issue with the Enfold theme  
[fix] Cart contents shown before the order summary on the "Thank You" page on mobile  

2025-04-28 - version 2.20.1  
[update] Updated dependencies and added more safety checks to telemetry opt-ins/opt-outs  

2025-03-11 - version 2.20.0  
[update] Dutch translations  
[update] Added hooks to display content after each individual step  
[update] Compatibility with StellarPay  
[fix] Compatibility with Revolut payment method  
[fix] Fatal error in WooCommerce Store API caused by quantity field being string instead of int  
[fix] Bricks: bug where the quantity spinner do not show on the shop page  

2025-02-19 - version 2.19.0  
[update] Licensing.  

2025-01-29 - version 2.18.0  
[update] Disable `Skip Cart` setting by default  
[update] Add filter `flux_checkout_check_for_inline_errors`  
[update] Disable Skip Cart setting by default  
[fix] Compatibility between Checkout Elements and WPML  
[fix] Fix compatibility issues with Bricks theme  
[fix] Compatibility with Sequential Order plugins  
[fix] Fix bug where the state field is not populated when using address autocomplete  
[fix] Address autocomplete not working because of decodeEntities function not being present  
[fix] Bug where errors show on My Account > Address page  

2024-11-19 - version 2.17.0  
[update] Add regular price to cart review section for products on sale  
[fix] Issue causing the phone number Phone field numbers to jumble for RTL  
[fix] HTML character showing in address autocomplete  
[fix] Dequeue core-block-supports only on non Flux checkout pages  

2024-10-17 - version 2.16.0  
[new] Multiple conditional logic groups are now available for Flux Checkout Elements (backwards compatible with existing conditions)  
[new] Compatibility with global headers/footers created with Bricks, Beaver Builder, Breakdance and Visual Composer  
[update] Added updated translations for Dutch (Netherlands)  
[fix] Order review section no longer appears twice on the Payment step when `Germanized for WooCommerce` is active  
[fix] Phone field now honours the optional/required setting in the WC section of the customizer  
[fix] Phone field direction now correctly honours the RTL setting for the active language  
[fix] Fixed several string translation issues relating to cross-sells  

2024-08-28 - version 2.15.0  
[fix] Email field missing when WooPayments is active  
[fix] Map not showing on the Thank You page in rare cases  
[fix] Disable password strength check when the `wc-password-strength-meter` script is dequeued  
[fix] Make the "Add" button translatable in the cross-sell block  
[fix] Do not ask the user to log in when the "Assign Guest User" setting is enabled  
[fix] Phone field blocks the checkout process when used with Checkout blocks  
[fix] Bug where login doesn't work when the password contains special characters  

2024-07-18 - version 2.14.0  
[new] New Cross-sell products block and setting  
[update] Thank you page heading: replace H1 tag with H2 to prevent double H1 tags  
[fix] Double shipping address heading  
[fix] Coupon button bug in classic theme  

2024-05-23 - version 2.13.0  
[update] Add page builder support for Checkout Elements  
[update] Update `Intl-tel-input` JS library  
[fix] Reverse shipping destination changes  

2024-05-16 - version 2.12.0  
[new] Show shipping fields on top when shipping destination is shipping address   
[update] Added new JS filter `flux_google_autocomplete_options` to allow third party to modify Google autocomplete options  
[fix] Fatal error with Elementor Page builder  
[fix] Unwanted HTML showing on email field when stripe payment gateway is active  
[fix] Email specific coupons not working  

2024-04-30 - version 2.11.0  
[update] Add a JS filter to modify the shipping row element - `flux_checkout_move_shipping_row_element`  
[update] Translation for the shipping address search field placeholder  
[update] Styling for read-only quantity input  
[fix] Fatal error caused in Avada Compatibility code  
[fix] Incorrect phone field status is returned when `woocommerce_checkout_phone_field` option is empty  
[fix] Bug where Avada inline CSS would conflict with Flux layout  
[fix] Display notice obove the phone field when checkout page uses blocks  
[fix] Double images appear when Lazy loading is enabled in SiteGround Speed Optimizer plugin  
[fix] Add Polish translations  
[fix] Incomplete address in the customer review box on the payment step  

2024-03-20 - version 2.10.0  
[new] New setting to change the position of Coupon field on mobile devices  
[update] Compatibility with Smart Home theme by Fuel Themes  
[update] Use single DI container for Telemetry  
[update] Load Google Maps API asynchronously  
[update] Add address lookup button to the classic theme  
[update] Use inserter option of block.json instead of the PHP code to disable placeholder block  
[fix] Tooltip mobile issue for the address field on mobile  
[fix] JS error when Order Notes are disabled  
[fix] Delay validating the telephone filed when user is still typing  
[fix] Fatal error that sometimes happens on the Thank you page when using Delivery Slots  
[fix] Compatibility with Stripe plugin by Payment Plugins  
[fix] Compatibility with Checkout block  
[fix] Disable browser autofill for address auto complete field  
[fix] Do not store order notes in the Local Storage  
[fix] PHP error in find_menu_index function  
[fix] Issue where WDS edit timeslot fields not appearing on the Flux Thank you page  
[fix] Admin: load assets on Flux Pages only  
[fix] Move Paypal and Google pay express checkout button(Payment Plugins) to Flux Express section  
[fix] Change redirect status to 301 when cart is empty  
[fix] JS error when the checkout elements is not a Block Editor page  
[fix] Checkbox box-sizing issue when bootstrap.css is enqueued  
[fix] Layout conflict with WooCommerce module of Astra Pro plugin  

2024-02-06 - version 2.9.1  
[fix] Login popup design issues  

2024-01-30 - version 2.9.0  
[new] Flux Checkout Elements - use the block editor to create custom elements and place them anywhere on the checkout page  
[new] Divi & Elementor Compatibility - show custom built headers/footers on the checkout page; go to Flux Settings > Checkout Page to enable  
[fix] Error "Incorrect use of <label for=FORM_ELEMENT>"  
[fix] Fatal error on edit post screen when WooPayments is installed   

2024-01-09 - version 2.8.0  
[new] Add filter `woocommerce_update_cart_validation`  
[new] Added filter `flux_checkout_disabled`  
[update] PHPStan fixes  
[update] Improve french translations  
[update] Change breakpoint to 1024, instead of 1023  
[update] Allow Street Number field even when autocomplete is disabled  
[update] Refactor custom details box and add filter `flux_checkout_customer_review_details`  
[update] Improve address auto complete for Taiwan addresses  
[fix] Enable state field in digital mode for tax purposes  
[fix] Remove loading button class on credit card error  
[fix] Double error messages shown for Paypal Payment method  
[fix] Fix Avada Off-screen menu not working issue  
[fix] Move the `woocommerce_after_checkout_billing_form` filter to correct location  
[fix] Use 301 permanent redirect for cart to checkout redirection  
[fix] Fatal error when product ID is invalid in cart overview  
[fix] fatal error when % is present in the URL of thumbnail  
[fix] Extra gap above country field when address autocomplete is disabled  
[fix] Address autocomplete for Mexico  
[fix] Issue where delivery slots fields are not validated on step change  
[fix] Make google address search widget placeholder translatable  
[fix] Issue where when checkout button resets when already selected payment method is clicked again  

2023-08-31 - version 2.7.1  
[fix] JS error when weak password is entered and next button is pressed  
[fix] Fix license validation issues and type errors  

2023-08-23 - version 2.7.0  
[new] New licensing system  
[new] Add subscriptions data to thank you page  
[update] Support for partial quantity plugins  
[update] Thank you page hooks and minor style changes  
[update] Compatibility with MailChimp for WooCommerce  
[update] Payment plugins compatibility  
[fix] Address autocomplete for Taiwan addresses  
[fix] Add support for WooCommerce Phone and Address line 2 field  
[fix] Issue where Apple pay/Google pay buttons remain hidden sometimes  
[fix] JS error with Stripe's Link express payment button  

2023-07-25 - version 2.6.1  
[fix] Express checkout section styling  
[fix] Add filter `flux_shipping_fields`  

2023-07-21 - version 2.6.0  
[new] Standard wrapper for all Express Checkout payment buttons  
[new] Downloads table on the Thank you page  
[update] Add RTL support  
[fix] International phone dropdown not working on mobile  
[fix] Shipping fields don't show error when `Ship to a different address` checkbox is checked and fields are empty  
[fix] Hide address lookup field when Google address API is not loaded  

2023-06-27 - version 2.5.0  
[update] Declare compatibility with HPOS  

2023-05-24 - version 2.4.0  
[new] Added a new filter to modify the required field error message  
[new] Added a new filter before order notes  
[new] Maintain the current step on page reload  
[new] New setting to optimize the checkout when the cart contains only virtual/digital products  
[new] Add filter `woocommerce_ship_to_different_address_checked`  
[new] Add new hook `flux_before_billing_address_heading`  
[update] Add JS filter to disable the checkout button animation  
[update] Compatibility with Conditional Fields of Checkout Fields Manager  
[update] Added hook `flux_thankyou_after_content`  
[update] Add hooks to modern template  
[update] Allow form and input tag to be inserted in the thank you page content  
[update] Thank you page map: use shipping address when shipping and billing address are different  
[update] Compatibility with WooCommerce Subscriptions  
[fix] Compatibility with Breakdance page builder  
[fix] Compatiblity with Flatsome cookie notice  
[fix] Compatibility with Force Sells for WooCommerce  
[fix] Issue where custom css with special characters (<, ") won't work  
[fix] Double shipping method issue  
[fix] Improve address autocomplete for Italy addresses  
[fix] Duplicate heading issue on Classic theme with Sidebar enabled  
[fix] Don't hide the cart button on the mini cart when Cart redirect setting is disabled  
[fix] Issue where shipping row goes missing in mobile view  
[fix] Fix customer address details not updating in iOS devices when address is filled by Address autocomplete on keyboard  
[fix] Hide Quantity field until page is loaded  
[fix] Issue with address autocomplete when postcode is changed but shipping methods aren't updated  
[fix] Fix compatibility issue with Sales Booster After checkout popup   
[fix] Add action `woocommerce_after_order_notes`  
[fix] House number field not showing in the customer details box on payment step  
[fix] Compatibility with Sala theme  
[fix] Street number validation issue for Express checkout  
[fix] Label background for coupon field on mobile   
[fix] Prevent fatal error on the Thank you page  
[fix] Shipping method not appearing at 1024 screen width  
[fix] Validate the phone field while typing  
[fix] Fix phone field on page reload  
[fix] Fix issue where login form wont appear when clicked on the message to login  

2023-03-07 - version 2.3.2  
[new] Add setting to toggle Cart to Checkout redirection  
[update] Add missing hook `woocommerce_thankyou`  
[update] Update language files and fix bug where Text Domain wont load  
[update] Don't trigger `update_checkout` on step change  
[update] Add filter `flux_order_items_class`  
[update] Further UI improvements  
[update] Improve compatibility with Avada theme  
[update] Add translations for  `Norwegian Bokmål` language  
[fix] Dont clear notices when form is submitted by third party plugin form  
[fix] Do not modify custom checkout templates created by third party templates  
[fix] Fix fatal error caused by calling is_checkout() too early  
[fix] JS error related to international phone field  
[fix] Issue where `Auto Apply Coupon` won't work on Multi-site WP  
[fix] Issue with "undefined" payment failure error when using WooCommerce payments  
[fix] Improve the position of the empty cart message section.   
[fix] Spinner not showing for classic theme  
[fix] Missing shipping method on window resize event  
[fix] Improve responsiveness for below 400px devices  
[fix] Minor CSS fix for the footer  

2023-01-30 - version 2.3.1  
[fix] Remove button on cart not working  
[update] Disable redirect when `add-to-cart` argument is used along with `coupon` on checkout page  
[fix] JS warning related to onActiveField method  
[fix] Bug where themes can override Flux templates  
[fix] Button within notice CSS issue  
[fix] Prevent `woocommerce_before_cart_item_quantity_zero` deprecated warning  

2023-01-25 - version 2.3.0  
[new] Hide coupon setting  
[new] Order Pay page design  
[new] Use AJAX for the login form  
[new] International phone field  
[new] Optimized Account Creation Process  
[new] Accessibility and UI improvements  
[fix] Allow repositioning of the shipping method section  
[fix] Prevent double AJAX when product quantity is updated  
[fix] Implement icons with mask-image CSS property so their colors can be easily changed  
[fix] Fix issue where `flux_allowed_template_overrides` filter didn't work  
[fix] Login popup issue for the Classic theme  
[fix] Issue where Stripe IBAN field won't work  
[fix] Flux Checkout prevents delivery slots data (data & time) from appearing in the Order received Email  
[fix] Improve address autocomplete for the addresses with sub-premise  
[fix] Compatibility with the Blocksy theme  
[fix] Issue where PHP warning broke Elementor builder  

2022-11-12 - version 2.2.2  
[new] Empty cart state template  
[update] Add filter `woocommerce_cart_item_thumbnail`  
[update] Compatibility with Fastcart plugin by Barn2  
[update] Add CSS properties to align images in Thank you content box  
[update] Add filter to modify the shop URL hyperlink `flux_checkout_shop_page_url`  
[update] Fix compatibility with WooCommerce Checkout Add-ons plugin  
[update] Compatibility with Kadence Theme  
[update] Prefix Flux AJAX requests to prevent conflict with other plugins  
[fix] Issue where admin style doesn't load for non-English languages  
[fix] Allow quantity of product more than the stock availability when the product is in backorder  
[fix] Clear WooCommerce template cache on plugin deactivation  
[fix] Update cart item count heading when item quantity is changed on the Checkout page  
[fix] Prevent JS errors on the Thank you page  
[fix] Fix issue where the "Place Order" button would reset to default on changing the Payment method  
[fix] Styling Fixes  

2022-11-12 - version 2.2.1  
[fix] Allow oEmbed in the Thank you content  
[fix] Fix fatal error if last order was a refund

2022-11-11 - version 2.2.0  
[new] Thank you page  
[update] Improve address search result  
[fix] Double AJAX issue on step change  
[fix] Compatibility with Wildrobot Logistra plugin  
[fix] Show error notice if user tries to increase quantity beyond available stock  
[fix] Issue with validation error  
[fix] Escape field label  
[fix] Use full URL for spinner image  

2022-10-13 - version 2.1.0  
[update] Compatibility with Woo Product bundles  
[update] Improve the notice that appears after removing a coupon on the checkout page  
[update] Compatibility with PW WooCommerce Gift  
[update] Compatibility with Auros theme  
[fix] Do not show the shipping data if address is not yet entered  
[fix] Issue which caused the Coupon success message to be hidden right after it appears  
[fix] Text domain issues  
[fix] Fix outstanding PHPCS issues  
[fix] Hide <sup> tag when checkout button is in loading state  
[fix] Disable button loading for stripe payment gateway  
[fix] Apply color to lost password link  
[fix] Don't automatically redirect to checkout of there are any issues in the cart  
[fix] Styling issues with Paypal credit card fields  
[fix] Issue where shipping method selector doesn't appear on mobile for classic theme  
[fix] PHPCS optimizations  

2022-08-24 - version 2.0.2  
[fix] Creating a new account in the checkout page  

2022-08-2 - version 2.0.1  
[new] Compatibility with Checkout Field Editor for WooCommerce  
[update] Add filter `flux_checkout_logo_href` to modify the hyperlink on logo  
[update] Add filter `flux_checkout_back_button_href`  
[fix] Issue where total breaks into 2 lines when thousand seperator is a space  
[fix] Modern theme: Color setting not working  
[fix] Issue where street number is not prepended to the street address  

2022-08-15 - version 2.0.0  
[new] New! Modern Theme — A brand-new way to use Flux Checkout. Test it out today on a staging site!  
[new] Flux has been rewritten from the ground up to ensure compatibility with modern themes and frameworks.  
[update] Improve email validation check to support .church domain email addresses  
[update] Compatibility with Flatsome Terms and conditions popup  
[fix] Checkout styling issues with Divi theme  
[fix] Compatibility with NorthWP theme  
[fix] Improve compatibility with Divi theme  

2022-03-01 - version 1.8.0  
[update] Compatibility with Salient theme  
[update] Improve email validation  
[fix] Bug where Iconic Delivery slots timeslot field remains disabled when auto-select is enabled  
[fix] Issue where place order button would remain hidden after selecting paypal payment gateway  
[fix] Validation errors on pageload  
[Fix] Validation issue with Iconic Delivery slots fields  
[fix] Disable validation errors on updated_checkout event  

2021-12-08 - version 1.7.0  
[fix] Snackbar not appearing if Stripe payment fails  
[fix] RTL Support  
[fix] Add progress indicator on checkout  

2021-10-13 - version 1.6.0  
[fix] Label overlapping country field  
[fix] Shipping address house number not passing through  
[fix] Support for Gift Vouchers and Packages by Codemenschen  

2021-09-28 - version 1.5.1  
[fix] Cannot finalise Paypal payment when triggered from Cart page  

2021-09-15 - version 1.5.0  
[fix] Paypal Payments button not showing when changing payment method  
[fix] Google Autocomplete not filling out city  
[fix] State validation not highlighting  
[fix] Vertical scroll locked when county changed  
[fix] Issues with Select2 CSS  

2021-08-25 - version 1.4.1  
[fix] Street number field appearing last  

2021-08-23 - version 1.4.0  
[new] Compatibility with Fast Checkout  
[new] Support for search within dropdown fields  
[update] Compatibility with Klarna Payment gateway  
[fix] Plugin unable to activate on Multisite Network (function wp_is_mobile() is not defined)  
[fix] Update cart if error messages are displayed  
[fix] Checkout page 'Continue' button not working with Divi theme active  
[fix] Removes duplicate order notes  
[fix] Message appearing twice when using `woocommerce_review_order_before_cart_contents` hook  
[fix] Woo Subscriptions Pay from My Order causes JS error  
[fix] Added filter `flux_localstorage_fields`  
[fix] Fix issue where "undefined" is added to address field when there is no street number  
[fix] Compatibility issue with Checkout Field Editor  

2021-05-21 - version 1.3.0  
[new] Added browser history for checkout steps  
[new] New setting for seperate street number field  
[update] Compatibility with Tokoo theme  
[update] Compatibility with Virtue theme  
[update] Set street number from autocomplete API to street number field instead of street address  
[update] Respect WooCommerce's `Shipping destination` setting  
[update] Fix text domain for house number/name field  
[update] Check if the user already has an account with provided email, if it does then prompt user to login  
[update] Compatibility with Smart Coupons by StoreApps  
[update] Include subpremise in address auto-complete  
[update] Compatibility with `Advanced noCaptcha & invisible Captcha` plugin  
[fix] House number field priority when using checkout field editor plugins  
[fix] Replace deprecated $.load() event  
[fix] Compatibility with Loco translate  
[fix] Fix the issue where the login form disappears after a failed login attempt  
[fix] Dropdown field placeholder overlapping with the first option text  
[fix] Correct position of `House Number/Name` field under shipping  
[fix] Replace payment data if checkout fails (fixes Apple Pay if error occurs)  
[fix] Remove missing source map references  
[fix] Fix wpColorPickerL10n JS error on settings page  

2020-12-17 - version 1.2.5  
[new] Added browser history for checkout steps  
[update] Hide coupon field if disabled in WooCommerce setting  
[update] Replace Mobile Detect library with wp_is_mobile() function  
[update] Separate Field for Street number  
[update] POT file  
[update] Update Italian translation  
[update] Added hook `woocommerce_checkout_before_customer_details`  
[update] Added filter `woocommerce_ship_to_destination`  
[update] Compatibility with WordPress 5.6  
[fix] Divi hashchange issue  

2020-08-12 - version 1.2.4  
[fix] Repackage to include missing files

2020-08-12 - version 1.2.3  
[update] Compatibility with WordPress 5.5  
[update] Update dependencies  
[update] New filter `flux_has_prepopulated_fields`  
[fix] Fix issue when Woo setting is ship to specific country  
[fix] Dequeue theme scripts  

2020-08-01 - version 1.2.2  
[update] Payment gateway styling.  
[update] Add `flux_checkout_allowed_sources` filter to allow certain theme styles to load at checkout.  
[update] Add Siteground compatibility.  
[update] Change field underline method to cater for field descriptions.  
[update] Add free trial capabilities.  
[update] Only run compatibility classes if flux is active.  
[update] Add `flux_checkout_details_fields` filter to define which billing fields show in the first step.  
[fix] Disable back to cart button in Germanized.  
[fix] Braintree CC compatibility.  
[fix] Payment gateway jumping glitch when opening.  
[fix] Add after order notes field to fix Metorik tracking.  
[fix] Prevent styles from loading outside of checkout.  
[fix] Ensure checkout submits properly.  
[fix] Ensure checkboxes and radios inherit the right classes.  
[fix] Ensure scripts is loaded last.  
[fix] ID not set on archive pages when checking if is checkout.  

2020-08-01 - version 1.2.1  
[update] Compatibility with SendCloud plugin.  
[update] Compatibility with WooCommerce Social Login by SkyVerge.  
[update] Compatibility with Shoptimizer theme.  
[update] Compatibility with Woodmart theme.  
[update] Compatibility with Martfury theme.  
[update] Dequeue theme styles only.  
[update] Disable order notes section when woocommerce_enable_order_notes_field is false.  
[update] Dequeue Select2 at checkout.  
[update] Add Germanized compatibility.  
[update] Apply checkbox and radio styling to all elements.  
[update] Don't allow the same primary and accent colors to be selected.  
[update] Remove scss compiler requirement.  
[fix] Issue when saving custom styles.  
[fix] Add T&C text to textdomain.  
[fix] Logo size issue when using an SVG.  
[fix] Remove double (optional) label for address 2.  
[fix] Ensure labels float correctly for all fields.  
[fix] Grid layout issue at checkout.  
[fix] Error with autocomplete when only shipping to one country.  
[fix] Remove focus glitch on radio/checkboxes.  
[fix] Create account fields styling.  
[fix] IE CustomEvent polyfill error.  
[fix] Step indicator wrong width in IE11.  
[fix] Move label more quickly when autocompleting.  

2020-08-01 - version 1.2.0  
[new] Freemius licensing.  
[update] Fix deprecated warnings.  
[update] Add Astra theme compatibility.  
[update] Add Avada theme compatibility.  
[update] Add Flatsome theme compatibility.  
[update] Add Shopkeeper theme compatibility.  
[update] Improved checkout flow and speed.  
[update] Improved checkout styling.  
[update] Remove autofill background color.  
[update] Order review styling.  
[update] Move settings page to WooCommerce > Flux Checkout.  
[update] Improve step validation.  
[update] Limit address autocomplete to specific countries.  
[update] Add some hooks to the main template file.  
[update] Display any outstanding errors on page load.  
[update] Better payment gateway icon sizing.  
[fix] Template override priority.  
[fix] Disable browser autocomplete on address search fields.  
[fix] Some tags weren't closed.  
[fix] Update order pay endpoint.  

2020-08-01 - version 1.1.1  
[new] Custom CSS section.  
[new] 3rd party plugin compatability fixes.  
[new] Plugin option enabled for desktop.  
[new] Styling added for desktop screen resolutions.  
[new] Tool tip added to address lookup text.  
[update] Enter key press disabled.  
[update] Gradients list updated to latest version.  
[update] Terms and conditions missing translation issue fixed.  
[update] Deprecated function: WC_Cart::get_cart_url() fixed.  
[update] Cart slide out removed and replaced with cart icon link.  
[update] "NOT FOUND?" text updated to "ENTER ADDRESS MANUALLY".  
[update] Field validation issue fixed.  

2020-08-01 - version 1.1.0  
[new] Translations added for French, Danish, Italian, Portuguese (Brazil), Greek, Dutch, Russian, Turkish, Portuguese, Malaysian, German, Mexican and Norwegian.  
[update] Shipping options removed from cart slide out.  
[update] CSS improvements made to the cart slide out, select fields and order review section.  
[update] Address autocomplete function updated to resolve UK postal town accurately.  
[update] Titanframe work color picker issue resolved.  
[update] Android field focus issue fixed.  
[update] Front and back-end text made translatable.  
[update] Removed redundant files within /woocommerce/  
[update] Disabling the returning customer login reminder now removes 'Returning Customer?' button.  
[update] Phone field validation now accepts spaces, '+' and '()'.  
[update] PHP 7.1.6 compatibility issue when installing plugin.  
[update] Input type="password" now inherits MDL style.  
[update] Removed redundant styles and scripts.  
[update] Template loading priority fixed for /woocommerce/ folder.  
[update] Script loading priority fixed for plugin asset files.  
[update] Added various theme and plugin compatibility CSS.  

2020-08-01 - version 1.0.0  
[new] Release of v1.0.0  