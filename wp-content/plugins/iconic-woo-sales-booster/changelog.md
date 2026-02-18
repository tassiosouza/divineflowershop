**v1.26.0** (17 Nov 2025)  
[fix] Compatibility issue in Appearance > Editor > Styles > Blocks  

**v1.25.2** (07 Jul 2025)  
[fix] Add defensive check to support plugins loading before WooCommerce  

**v1.25.1** (28 Apr 2025)  
[update] Updated dependencies and added more safety checks to telemetry opt-ins/opt-outs  

**v1.25.0** (19 Feb 2025)  
[update] Licensing  

**v1.24.0** (03 Sep 2024)  
[update] Dev hooks' name  
[fix] Loading when a variation is selected in the Frequently Bought Together section  
[fix] Use Woocommerce thumbnail cropping settings on the Frequently Bought Together thumbnails  

**v1.23.0** (18 Jul 2024)  
[new] Compatibility wit WooCommerce Shipping & Tax  
[new] Developer filter: `iconic_wsb_is_valid_cart_discount`  
[fix] WPML Order Bumps not loading the translated version  

**v1.22.1** (25 Jun 2024)  
[fix] Missing frontend dependency  

**v1.22.0** (11 Jun 2024)  
[update] Sales Booster menu capibility to `manage_woocommerce`  

**v1.21.0** (30 Apr 2024)  
[new] WooCommerce Product Block editor compatibility  
[fix] `After Add to Cart` modal appearing on the checkout page  

**v1.20.0** (10 Apr 2024)  
[new] Compatibility with Booking & Appointment Plugin for WooCommerce  
[new] Hooks in After Add to Cart modal  
[update] Prevent updating quantity for the product (offer) added via Order bump  
[fix] PHP warning when the Frequently Bought Together products are retrieved  
[fix] Multiple "After Add to Cart" pop-ups showing  

**v1.19.0** (14 Mar 2024)  
[fix] Add to my order button on After Checkout modal  
[fix] Position field description  
[fix] After Add to Cart modal layout issues  

**v1.18.1** (26 Feb 2024)  
[fix] Missing frontend dependency  

**v1.18.0** (05 Feb 2024)  
[new] Compatibility with WooCommerce Checkout block  

**v1.17.3** (08 Jan 2024)  
[fix] More than one After Add to Cart Popup appearing when adding products via Frequently Bought Together section  
[fix] Currency symbol of the total price when using WooCommerce Multilingual & Multicurrency  

**v1.17.2** (11 Dec 2023)  
[update] Iconic dependencies  
[fix] Thumbnails showing when the option `Unchecked by Default` is checked  

**v1.17.1** (23 Oct 2023)  
[fix] Adding products to the cart that belong to the same category via AJAX  

**v1.17.0** (27 Sep 2023)  
[new] `Added Revenue` column to the Order Bumps and After Checkout list table  
[fix] Show bought together discount after items subtotal on the order page  

**v1.16.1** (31 Aug 2023)  
[new] Highlight products out of stock or without price when added as a bump  
[fix] Fix license validation issues and type errors  

**v1.16.0** (24 Aug 2023)  
[new] New licensing system  

**v1.15.3** (03 Aug 2023)  
[fix] Show shadow setting in Order Bump  

**v1.15.2** (21 Jul 2023)  
[update] Updated Iconic dependencies.  

**v1.15.1** (19 Jul 2023)  
[fix] Compatibility with WooCommerce Quantity Discounts, Rules & Swatches plugin  

**v1.15.0** (06 Jul 2023)  
[new] Compatibility with High-Performance Order Storage (HPOS)  

**v1.14.2** (05 Jul 2023)  
[update] Update dependencies  

**v1.14.1** (20 Jun 2023)  
[fix] Stock amount when selecting a product to be added to an order in the admin  

**v1.14.0** (26 Apr 2023)  
[new] Compatibility with YITH Added To Cart Pop-up  
[new] Hooks: `iconic_wsb_before_checkout_button_in_after_add_to_cart_modal`, `iconic_wsb_before_view_cart_link_in_after_add_to_cart_modal` and `iconic_wsb_after_view_cart_link_in_after_add_to_cart_modal`  
[fix] "Added to the cart" notice when Frequently Bought Together items are added  
[fix] After Checkout popup when the order is updated on the checkout page  

**v1.13.1** (02 Jan 2023)  
[fix] After Add to Cart Modal when the option `Enable AJAX for "Add Selected to Cart" button?` is checked  
[fix] Buttons alignment in the 'Customers Also Bought' modal  
[fix] Frequently Bought Together product variations not being added to the cart  

**v1.13.0** (09 Aug 2022)  
[new] Action to duplicate Order Bumps  
[new] Export and import Iconic Sales Booster for WooCommerce data  
[new] Allow changing the order bump status  
[new] Filter `iconic_wsb_fbt_thumbnail_size` to change the thumbnail size in the FBT section  
[fix] Frequently Bought Together data copied by WPML  

**v1.12.0** (27 Jul 2022)  
[new] Compatibility with WooCommerce Multilingual & Multicurrency  
[fix] Frequently Bought Together when AJAX for "Add Selected to Cart" is disabled  
[fix] Number of offers shown in the After Add to Cart Modal  
[fix] Modal content after adding another product within the modal  
[fix] Compatibility issue with After Add to Cart Modal and page builders  

**v1.11.0** (27 Jun 2022)  
[new] Compatibility with WooCommerce eCurring gateway  
[new] Compatibility with WooCommerce Subscriptions  
[fix] Product variation offer when the condition is a product variation  
[fix] Image and name of the variation product on the After Add to Cart Modal  

**v1.10.0** (19 May 2022)  
[new] Order Bump shortcode `[iconic_wsb_order_bump]`  
[fix] Show the popup on the cart page if the option "Redirect to the cart page after successful addition" is enabled  
[fix] Product variations unavailable on the product page  

**v1.9.0** (25 Apr 2022)  
[new] Compatibility with Variation Swatches for WooCommerce by RadiusTheme  
[fix] Adding products to the cart when order bump is using "After Order Review" or "After Checkout Form" position  
[fix] Offered product to be added if it belongs to the excluded category added on the order bump condition  
[fix] Prevent PHP notice when preparing data to be added to the cart  

**v1.8.0** (4 Apr 2022)  
[fix] 'Frequently Bought Together' and 'Customers Also Bought' translation issue with WPML plugin   
[fix] Product variation titles on product search dropdown  
[fix] Prevent possible PHP warning on getting settings  
[fix] Adding "Frequently Bought Together" products multiple times  

**v1.7.0** (1 Mar 2022)  
[new] Getting started onboarding section  
[update] New setting to allow "frequently bought together" discount to be applied when items are added separately  
[fix] Allow "frequently bought together" products to be added multiple times  

**v1.6.0** (1 Mar 2022)  
[fix] Allow FBT parent item to be added seperately  
[fix] Fix none unique array when using FBT  
[Fix] Issue where order bump would not work when "only" condition is used  
[fix] Security fix  

**v1.5.0** (8 Dec 2021)  
[new] After Add to Cart modal can appear on archive pages  
[new] Filter order bump and after checkout cross sales by category and  introduced 'only' and 'none' sub filters  
[fix] Issue with Front End Asset Load  

**v1.4.0** (13 Oct 2021)  
[new] Additional locations for Frequently Bought Together  
[fix] FBT Panel updates when item added  
[fix] Order Bump not Accepting Decimal Discounts  
[fix] Fatal Error when setting session  
[fix] Offer text not applied to at checkout bumps  
[fix] Error if discount left empty  

**v1.3.0** (28 Sept 2021)  
[new] Option to show after add to cart modal when there are no cross sells  
[new] Allow order bump to include more of the same product  
[fix] If multiple items cannot be added to the cart, only last message is shown  
[fix] Ajax woo product search results not in correct format  
[fix] Fix warning when adding a product to cart through Order Bump  
[fix] Prepend Offer text to duplicate item in checkout  

**v1.2.0** (23 Aug 2021)  
[new] Added filter `iconic_wsb_order_bump_image_size`  
[new] Additional hooks for order bump  
[fix] Add support for subscription product variations to order bumps and after checkout popup  
[fix] Product variation changes not updating Frequently Bought Together  
[fix] Remove the discounted product added with order bump if that's the only product in cart    
[fix] Variation Product added to Frequently Bought Together showing empty dropdown  
[fix] Prevent item from showing in after cart popup if already in cart  
[fix] Bring success messages inline with default Woocommerce  
[fix] Sales bump removes itself on same product  
[fix] Better formatting of product selectors  
[fix] Sometimes the same cart item would be listed twice instead of increasing quantity  

**v1.1.6** (6 May 2021)  
[update] Allow decimal discount values  
[update] Allow comma as decimal seperator  
[update] Remove markup from the price in FBT discount message  
[update] Allow empty value in FBT discount field  
[update] Add success/error notice for Frequently Bought Together AJAX call  
[update] Update dependencies  
[fix] Fix issue with variations in After Checkout popup  
[fix] FBT: Fix price not updating issue  
[fix] FBT: Tax exclusive discount for "other" locations  
[new] Option to show Order Bump even if the offer product is already in the cart  
[new] FBT: set unchecked setting  
[fix] Fix compatibility issue with WooCommerce Attribute Swatches by IconicWP  
[fix] Ensure variation name is used when creating an order bump or after checkout offer  
[fix] Loco translate compatibility  
[fix] FBT: Fix issue with adding products with custom attributes to the cart  

**v1.1.5** (19 Aug 2020)  
[new] Ability to change title of "frequenty bought together" products, per-product  
[update] Add compatibility with Flux Checkout  
[update] Add new filter `iconic_wsb_order_bump_position`  
[fix] Compatibility with WP 5.5 and jQuery v1.12.4 excluding jQuery Migrate 1.x  
[fix] Handle non-latin characters in terms and taxonomies  
[fix] Fix checkout bump issue when discount is 100 percent  

**v1.1.4** (18 Mar 2020)  
[update] Version compatibility  

**v1.1.3** (19 Feb 2020)  
[new] Setting to show hidden products in FBT  
[update] Add .pot file  
[update] AJAX for Frequently Bought Together  
[update] Show/hide images in Frequently Bought Together  
[update] Sales Pitch  
[update] Show alert when clicked on button 'Add selected to cart' in FBT  
[update] Update dependencies  
[fix] Ensure decimals are counted in FBT totals  
[fix] Issue with non-existent FBT products  
[fix] Order Bump not showing at checkout in WooCommerce 3.9+  
[fix] Prevent PHP warning in FBT  
[fix] Issue that prevented removing variable products from order bump at checkout  
[fix] After Add to cart Popup: Show price, image and title from the child/variation product  
[fix] Issue with pricing when tax settings were enabled  
[fix] FBT label bug for Non-English locale bug  
[fix] Sorting issue  

**v1.1.2** (29 Nov 2019)  
[update] Allow hidden products in bumps  
[fix] Call to undefined method get_variation_attributes()  

**v1.1.1** (21 Nov 2019)  
[update] Auto select order bump checkbox after choosing a variation  
[fix] Ensure FBT calculations are correct when tax is involved  
[fix] Ensure variation is selected when adding from FBT  
[fix] Fix pricing in "customers also bought" modal and cart count  
[fix] Use own AJAX Url  

**v1.1.0** (4 Nov 2019)  
[new] Offer variable products and variations within all cross-sell areas  
[new] Offer discounts for "frequently bought together" products  
[update] Add compatibility with WooCommerce Attribute Swatches by Iconic  
[update] Update dependencies  
[fix] Save cross-sells when product edit field is empty  
[fix] Trashed products should no longer be shown in bumps  
[fix] Ensure tax is accounted for (if enabled) on After Checkout and Order Bumps  
[fix] Fix issue preventing WP CLI commands from running  

**v1.0.1** (1 July 2019)  
[Fix] Updated Freemius integration and dependencies.  

**v1.0.0** (3 June 2019)  
Initial Release
