"use strict";

/* Functions jquery ================================ */
/**
 * After Called Ajax
 * 
 * @param {type} $
 * @returns {undefined}
 */
function nasa_load_ajax_funcs($) {
    /**
     * Trigger Before load ajax function
     */
    $('body').trigger('nasa_after_ajax_funcs');
    
    /**
     * Compatible Jetpack
     */
    $('body').trigger('nasa_compatible_jetpack');
    
    /**
     * Trigger after load ajax function
     */
    $('body').trigger('nasa_after_ajax_funcs');
}

/**
 * support jetpack-lazy-image
 * @param {type} $
 * @returns {undefined}
 */
function nasa_compatible_jetpack($) {
    if ($('.jetpack-lazy-image').length) {
        $('.jetpack-lazy-image')
        .removeAttr('srcset')
        .removeAttr('data-lazy-src')
        .removeClass('jetpack-lazy-image');
    }
}

/**
 * Slick slider element
 * 
 * @param {type} $
 * @param {type} restart
 * @param {type} _wrap
 * @param {type} _now
 * @returns {undefined}
 */
function loading_slick_element($, restart, _wrap, _now) {
    var now = typeof _now !== 'undefined' ? _now : false;
    
    var _slider_class = now ? '.right-now .nasa-slick-slider' : '.nasa-slick-slider';
    
    var _sliders = typeof _wrap !== 'undefined' && $(_wrap).length ? $(_wrap).find(_slider_class) : $(_slider_class);
    
    if ($(_sliders).length) {
        
        $('body').trigger('nasa_compatible_jetpack');
        
        var _rtl = $('body').hasClass('nasa-rtl') ? true : false;
        var _restart = typeof restart === 'undefined' ? false : restart;
        
        $(_sliders).each(function() {
            var _this = $(this);
            
            /**
             * Restart
             */
            if (_restart) {
                $('body').trigger('nasa_unslick', [_this]);
            }
            
            /**
             * Remove slick item
             */
            if ($(_this).find('.nasa-slick-remove').length) {
                $(_this).find('.nasa-slick-remove').remove();
            }
            
            if (!$(_this).hasClass('slick-initialized')){
                var _cols = parseInt($(_this).attr('data-columns')),
                    _cols_small = parseInt($(_this).attr('data-columns-small')),
                    _cols_medium = parseInt($(_this).attr('data-columns-tablet')),
                    
                    _autoplay = $(_this).attr('data-autoplay') === 'true' ? true : false,

                    _loop = $(_this).attr('data-loop') === 'true' ? true : false,
                                      
                    _speed = parseInt($(_this).attr('data-speed')),
                    _delay = parseInt($(_this).attr('data-delay')),
                    
                    _dots = $(_this).attr('data-dot') === 'true' || $(_this).attr('data-dots') === 'true' ?
                        true : false,
                    
                    /**
                     * Height auto only for 1 column
                     */
                    _height_auto = $(_this).attr('data-height-auto') === 'true' ? true : false,
                    
                    _padding = $(_this).attr('data-padding'),
                    _padding_small = $(_this).attr('data-padding-small'),
                    _padding_medium = $(_this).attr('data-padding-medium'),
                    
                    _drag = $(_this).attr('data-disable-drag') === 'true' ? false : true,
                    _slide_all = $(_this).attr('data-slides-all') === 'true' ? true : false,
                    _start = parseInt($(_this).attr('data-start'));

                _cols = !_cols ? 4 : _cols;
                _cols_small = !_cols_small ? 2 : _cols_small;
                _cols_medium = !_cols_medium ? 3 : _cols_medium;
                    
                var _scroll = _slide_all ? _cols : 1,
                    _scroll_small = _slide_all ? _cols_small : 1,
                    _scroll_medium = _slide_all ? _cols_medium : 1;
                    
                _speed = !_speed ? 300: _speed;
                _delay = !_delay ? 6000: _delay;
                    
                _start = !_start ? 0 : _start;
                    
                var _setting = {
                    rtl: _rtl,
                    slidesToShow: _cols,
                    slidesToScroll: _scroll,
                    autoplay: _autoplay,
                    infinite: _loop,
                    autoplaySpeed: _delay,
                    speed: _speed,
                    initialSlide: _start,
                    adaptiveHeight: _height_auto,
                    dots: _dots,
                    swipe: _drag,
                    draggable: _drag,
                    pauseOnHover: true,
                    arrows: true,
                    touchThreshold: 10,
                    cssEase: 'ease-out',
                    prevArrow: '<a class="nasa-nav-arrow slick-prev" href="javascript:void(0);" rel="nofollow"><svg width="32" height="32" viewBox="0 0 32 32" fill="currentColor"><path d="M12.792 15.233l-0.754 0.754 6.035 6.035 0.754-0.754-5.281-5.281 5.256-5.256-0.754-0.754-3.013 3.013z"/></svg></a>',
                    nextArrow: '<a class="nasa-nav-arrow slick-next" href="javascript:void(0);" rel="nofollow"><svg width="32" height="32" viewBox="0 0 32 32" fill="currentColor"><path d="M19.159 16.767l0.754-0.754-6.035-6.035-0.754 0.754 5.281 5.281-5.256 5.256 0.754 0.754 3.013-3.013z"/></svg></a>'
                };
                
                if (!_rtl) {
                    _setting.swipeToSlide = true;
                }
                
                var _switchTablet = 768;
                var _switchDesktop = 1130;
                
                if ($(_this).attr('data-switch-tablet')) {
                    _switchTablet = parseInt($(_this).attr('data-switch-tablet'));
                }
                
                if ($(_this).attr('data-switch-desktop')) {
                    _switchDesktop = parseInt($(_this).attr('data-switch-desktop'));
                }
                
                var _setting_medium = {
                    slidesToShow: _cols_medium,
                    slidesToScroll: _scroll_medium
                };
                
                var _setting_small = {
                    slidesToShow: _cols_small,
                    slidesToScroll: _scroll_small
                };
                
                if (_padding) {
                    _setting.centerMode = true;
                    _setting.centerPadding = _padding;
                    _setting.infinite = true;
                    
                    if (!$(_this).hasClass('nasa-center-mode')) {
                        $(_this).addClass('nasa-center-mode');
                    }
                }
                
                if (_padding_small) {
                    _setting_small.centerMode = true;
                    _setting_small.centerPadding = _padding_small;
                    _setting_small.infinite = true;
                    
                    if (!$(_this).hasClass('nasa-small-center-mode')) {
                        $(_this).addClass('nasa-small-center-mode');
                    }
                } else {
                    _setting_small.centerMode = false;
                }
                
                if (_padding_medium) {
                    _setting_medium.centerMode = true;
                    _setting_medium.centerPadding = _padding_medium;
                    _setting_medium.infinite = true;
                    
                    if (!$(_this).hasClass('nasa-medium-center-mode')) {
                        $(_this).addClass('nasa-medium-center-mode');
                    }
                } else {
                    _setting_medium.centerMode = false;
                }
                
                var _responsive = [
                    {
                        breakpoint: _switchDesktop, // => Tablet Mode
                        settings: _setting_medium
                    },
                    {
                        breakpoint: _switchTablet, // => Mobile Mode
                        settings: _setting_small
                    }
                ];
                
                if ($(_this).attr('data-switch-custom')) {
                    var _switchCustom = parseInt($(_this).attr('data-switch-custom'));
                    
                    var _settingCustom = {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    };
                }
                
                if (_switchCustom) {
                    _responsive.push({
                        breakpoint: _switchCustom,
                        settings: _settingCustom
                    });
                }
                
                _setting.responsive = _responsive;
                
                $(_this).slick(_setting);
                
                /**
                 * Init Banner transition
                 */
                if ($(_this).find('.nasa-banner-image').length) {
                    $(_this).find('.slick-slide').each(function() {
                        var _item = $(this);
                        if ($(_item).find('.banner-inner').length > 0){
                            var _banner = $(_item).find('.banner-inner');
                            var animation = $(_banner).attr('data-animation');
                            $(_banner).removeClass('animated').removeClass(animation).removeAttr('style');
                            if ($(_item).hasClass('slick-active')){
                                setTimeout(function(){
                                    $(_banner).show();
                                    $(_banner).addClass('animated').addClass(animation).css({
                                        'visibility': 'visible',
                                        'animation-duration': '1s',
                                        'animation-delay': '0ms',
                                        'animation-name': animation
                                    });
                                }, 1000);
                            }
                        }
                    });
                }
                
                setTimeout(function() {
                    $('body').trigger('nasa_inited_slick', [_this]);
                }, 100);
            }
        });
    }
}

/**
 * Countdown Nasa Core
 * 
 * @param {type} $
 * @returns {undefined}
 */
function load_count_down($) {
    if (typeof nasa_countdown_l10n !== 'undefined' && $('.countdown').length > 0) {
        $('.countdown').each(function() {
            var count = $(this);
            if (!$(count).hasClass('countdown-loaded')) {
                var austDay = new Date(count.data('countdown'));
                $(count).countdown({
                    until: austDay,
                    padZeroes: true
                });
                
                if ($(count).hasClass('pause')) {
                    $(count).countdown('pause');
                }

                // if ($(count).parents('.nasa-product-info-wrap').length) {
                //     var variations = $(count).parents('.nasa-product-info-wrap').find('.variations');
                //     $(variations).after($(count));
                // }
                
                $(count).addClass('countdown-loaded');
            }
        });
    }
}

/**
 * Function Loop through variations.
 * 
 * @param {type} $
 * @param {type} _each_vars
 * @param {type} currentAttributes
 * @param {type} variationData
 * @returns {undefined}
 */
function nasa_loop_through_variations($, _each_vars, currentAttributes, variationData) {
    // Loop through selects and disable/enable options based on selections.
    if (_each_vars !== null) {
        $(_each_vars).each(function () {
            var _this = $(this);
            var _prefix_attr = !$(_this).hasClass('nasa-attr_type_custom') ? 'attribute_pa_' : 'attribute_';
            var current_attr_name = _prefix_attr + $(_this).attr('data-pa_name');
            
            $(_this).find('.nasa-attr-ux-item').removeClass('nasa-disable');
            $(_this).find('.nasa-attr-ux-item').removeClass('nasa-unavailable');
            $(_this).find('.nasa-attr-ux-item').removeClass('attached');
            $(_this).find('.nasa-attr-ux-item').removeClass('enabled');
            
            var checkAttributes = $.extend(true, {}, currentAttributes);
            checkAttributes[current_attr_name] = '';
            var variations = nasa_matching_variations(variationData, checkAttributes);
            
            // Loop through variations init.
            for (var num in variations) {
                if (typeof (variations[num]) !== 'undefined') {
                    var variationAttributes = variations[num].attributes;

                    for (var attr_name in variationAttributes) {
                        if (variationAttributes.hasOwnProperty(attr_name)) {
                            var attr_val = variationAttributes[attr_name],
                                variation_active = '';
                            
                            // Decode entities.
                            attr_val = attr_val ? $('<div/>').html(attr_val).text() : false;
                            
                            if (attr_name === current_attr_name) {
                                if (variations[num].variation_is_active) {
                                    variation_active = ' enabled';
                                }
                                
                                if (attr_val) {
                                    // Attach to matching options by value. This is done to compare
                                    // TEXT values rather than any HTML entities.
                                    var $option_elements = $(_this).find('.nasa-attr-ux-item');
                                    
                                    if ($option_elements.length) {
                                        $option_elements.each(function () {
                                            var $option_element = $(this),
                                                option_value = $option_element.attr('data-value');

                                            if (attr_val === option_value) {
                                                $option_element.addClass('attached' + variation_active);
                                                return; // Break each.
                                            }
                                        });
                                    }
                                }
                                // attached all options
                                else {
                                    $(_this).find('.nasa-attr-ux-item').addClass('attached' + variation_active);
                                }
                            }
                        }
                    }
                }
            }
            
            // Disabled
            if ($(_this).find('.nasa-attr-ux-item:not(.attached)').length) {
                $(_this).find('.nasa-attr-ux-item:not(.attached)').addClass('nasa-disable');
            }
            
            // Unavailable
            if ($(_this).find('.nasa-attr-ux-item:not(.enabled)').length) {
                $(_this).find('.nasa-attr-ux-item:not(.enabled)').addClass('nasa-unavailable');
            }
        });
    }
}

/**
 * Change info content product - In Grid
 * 
 * @param {type} $
 * @param {type} _variations_warp
 * @param {type} _alert
 * @returns {undefined}
 */
function change_content_product_variable($, _variations_warp, _alert) {
    _alert = (typeof _alert === 'undefined') ? false : _alert;
    
    var _count_attr = $(_variations_warp).find('.nasa-product-content-child').length,
        _selected_count = $(_variations_warp).find('.nasa-product-content-child .nasa-active').length,
        _product_item = $(_variations_warp).parents('.product-item');

    /**
     * With Webp
     * 
     * @type type
     */
    if ($(_product_item).find('.main-img [type="image/webp"]').length) {
        var _main_img_clone = $(_product_item).find('.main-img img').eq(0).clone();
        $(_product_item).find('.main-img').html('');
        $(_product_item).find('.main-img').append(_main_img_clone);
    }

    var _main_img = $(_product_item).find('.main-img img').eq(0);
    
    if ($(_main_img).length <= 0) {
        _main_img = $(_product_item).find('.product-img-wrap img').eq(0);
    }
    
    var _main_src = $(_main_img).attr('data-org_img'),
        _back_img = $(_product_item).find('.back-img img'),
        _back_src = $(_back_img).length ? $(_back_img).attr('data-org_img') : '',
        _add_wrap = $(_product_item).find('.add-to-cart-grid:not(.nasa-quick-add)'),
        _each_vars = _count_attr ? $(_variations_warp).find('.nasa-product-content-child') : null;

    var _main_srcset = $(_main_img).attr('srcset'),
        _back_srcset = $(_back_img).length ? $(_back_img).attr('srcset') : '';

    var _main_data_srcset = $(_main_img).attr('data-srcset'),
        _back_data_srcset = $(_back_img).length ? $(_back_img).attr('data-srcset') : '';
    
    var _variations = JSON.parse($(_variations_warp).attr('data-product_variations'));
    
    var _choseAttrs = nasa_chosen_attrs($, _variations_warp),
        currentAttributes = _choseAttrs.data;

    var _price_html = $(_product_item).find('.price');
    if ($(_price_html).length) {
        $(_product_item).find('.price').each(function() {
            if ($(this).parents('.price-wrap').length <= 0 && $(this).parents('.nasa-org-price').length <= 0) {
                $(this).wrap('<div class="price-wrap"></div>');
            }
        });
    }
    
    /**
     * Reset Out of Stock Badge
     */
    if (!$(_product_item).hasClass('outofstock')) {
        $(_product_item).find('.badge.out-of-stock-label').remove();
    }
    
    /**
     * Refresh attributes
     */
    nasa_loop_through_variations($, _each_vars, currentAttributes, _variations);
    
    /**
     * Refresh attributes accessories
     */
    if ($(_variations_warp).parents('.nasa-bought-together-wrap').length) {
        var pa = $(_variations_warp).parents('.nasa-accessories-product');
        var check = $('.nasa-accessories-check').find('.nasa-accessories-item-check input[value="' + $(pa).attr('data-product-id') + '"]');
        var variation_change = nasa_matching_variations(_variations, currentAttributes);

        $(check).attr({
            'data-display_price': variation_change[0].display_price,
            'data-display_regular_price': variation_change[0].display_regular_price
        });
       
        setTimeout(function(){
            if (variation_change.length > 1) {
                $(check).addClass('ns-getmin');
            } else {
                $(check).removeClass('ns-getmin');
            }
            
            $(check).trigger('change');
        }, 10);
    }
    
    /**
     * Old Price
     */
    if ($(_product_item).find('.nasa-org-price.hidden-tag').length <= 0 && $(_product_item).find('.price-wrap').length) {
        var _first_price = $(_product_item).find('.price-wrap').eq(0);
        $(_first_price).after('<div class="nasa-org-price hidden-tag">' + $(_first_price).html() + '</div>');
    }

    /**
     * Old Add to cart text
     */
    if ($(_add_wrap).hasClass('nasa-variable-add-to-cart-in-grid') && typeof $(_variations_warp).attr('data-select_text') === 'undefined') {
        $(_variations_warp).attr('data-select_text', $(_add_wrap).find('.add_to_cart_text').html());
    }

    var _select_text = $(_variations_warp).attr('data-select_text');

    if ($(_variations_warp).hasClass('ns-modern-8-content-variable-warp')) {
        $(_variations_warp).find('.nasa-product-content-child').each(function() {
            var _this = $(this);
            var _ux = $(this).find('.nasa-attr-ux-item.nasa-active');
            var _baseText = $(_this).find('.ns-attr-ux-title').attr('data-text');
            if ($(_ux).length) {
                var _text = $(_ux).attr('data-name');
                
                if ($(_ux).parents('.nasa-product-content-select-wrap').length) {
                    _text = $(_ux).attr('data-value');
                }
    
                $(_this).find('.ns-attr-ux-title').text(_baseText+': '+_text);
            } else {
                $(_this).find('.ns-attr-ux-title').text(_baseText);
            }
           
        });
    }

    /**
     * Not select full attributes
     */
    if (_count_attr !== _selected_count) {
        if (typeof _main_src !== 'undefined') {
            $(_main_img).attr('src', _main_src);
            
            if (_main_data_srcset) {
                $(_main_img).attr('srcset', _main_data_srcset);
            }
        }

        if ($(_back_img).length && typeof _back_src !== 'undefined') {
            $(_back_img).attr('src', _back_src);
            
            if (_back_data_srcset) {
                $(_back_img).attr('srcset', _back_data_srcset);
            }
        }

        /**
         * Button Select Options <=> Add To Cart
         */
        if ($(_add_wrap).hasClass('nasa-variable-add-to-cart-in-grid') && !$('body').hasClass('nasa-ywraq-hide-add-to-cart')) {
            $(_add_wrap).find('.add_to_cart_text').html(_select_text);
            $(_add_wrap).attr('title', _select_text);
            
            if ($(_add_wrap).hasClass('nasa-tip')) {
                $(_add_wrap).attr('data-tip', _select_text);

                if ($(_add_wrap).find('.nasa-tip-content').length) {
                    $(_add_wrap).find('.nasa-tip-content').html(_select_text);
                }
            }
            
            $(_add_wrap).attr('data-product_id', $(_variations_warp).attr('data-product_id')).addClass('product_type_variable').removeClass('product_type_variation').removeAttr('data-variation');
        }
        
        $(_product_item).find('.price-wrap').html($(_product_item).find('.nasa-org-price').html());
        $(_product_item).find('.add-to-cart-grid').removeClass('nasa-active');
        
        /**
         * Deal time
         */
        $(_product_item).removeClass('product-deals');
        
        if (!$(_product_item).find('.nasa-sc-pdeal-countdown').hasClass('hidden-tag')) {
            $(_product_item).find('.nasa-sc-pdeal-countdown').addClass('hidden-tag');
        }
        
        $(_product_item).find('.nasa-sc-pdeal-countdown').html('');
        
        /**
         * Bulk Badge
         */
        $(_product_item).find('.nasa-badges-wrap .bulk-label').remove();

        return;
    }
    
    /**
     * Select full Attributes
     */
    else {
        var _selected_attr = [];
        var _variation = {};
        $(_variations_warp).find('.nasa-product-content-child .nasa-active').each(function(){
            var _attr = $(this),
                _prefix_attr = !$(_attr).parents('.nasa-product-content-child').hasClass('nasa-attr_type_custom') ? 'attribute_pa_' : 'attribute_',
                _attr_name = _prefix_attr + $(_attr).attr('data-pa'),
                _attr_val = $(_attr).attr('data-value'),
                _attr_selected = {
                    'key': _attr_name,
                    'value': _attr_val
                };

            _variation[_attr_name] = _attr_val;
            _selected_attr.push(_attr_selected);
        });
        
        var _finded = false;
        var _variation_finded = null;
        for (var k in _variations) {
            var _attrs = _variations[k].attributes,
                _total_attr = 0;
            for (var k_attr in _attrs) {
                _total_attr++;
            }

            if (_count_attr !== _total_attr) {
                break;
            }

            for (var k_select in _selected_attr) {
                if (_attrs[_selected_attr[k_select].key] === '' || _attrs[_selected_attr[k_select].key] === _selected_attr[k_select].value) {
                    _finded = true;
                } else {
                    _finded = false;
                    break;
                }
            }

            if (_finded) {
                _variation_finded = _variations[k];
                break;
            }
        }

        /**
         * Found variation
         */
        if (_variation_finded) {
            
            /**
             * Change image show
             */
            var _org_img = _main_src ? _main_src : $(_main_img).attr('src');
            
            var _image_catalog = '';
            if (typeof _variation_finded.image_catalog !== 'undefined') {
                _image_catalog = _variation_finded.image_catalog;
            }

            if (
                typeof _variation_finded.image_catalog !== 'undefined' &&
                _variation_finded.image_catalog !== '' &&
                _image_catalog !== _org_img
            ) {
                if (typeof _main_src === 'undefined') {
                    $(_main_img).attr('data-org_img', $(_main_img).attr('src'));
                }

                if ($(_back_img).length && typeof _back_src === 'undefined') {
                    $(_back_img).attr('data-org_img', $(_back_img).attr('src'));
                }

                $(_main_img).attr('src', _variation_finded.image_catalog);
                
                if ($(_back_img).length) {
                    if (typeof _variation_finded.nasa_variation_back_img !== 'undefined' && _variation_finded.nasa_variation_back_img !== '') {
                        $(_back_img).attr('src', _variation_finded.nasa_variation_back_img);
                    } else {
                        $(_back_img).attr('src', _variation_finded.image_catalog);
                    }
                }
                
                if (_main_srcset) {
                    $(_main_img).removeAttr('srcset');
                    $(_main_img).attr('data-srcset', _main_srcset);
                }
                
                if ($(_back_img).length && _back_srcset) {
                    $(_back_img).removeAttr('srcset');
                    $(_back_img).attr('data-srcset', _back_srcset);
                }
                
                /**
                 * Check image loaded
                 */
                nasa_img_is_loaded($, _main_img, _back_img, _variation_finded.variation_id);
            }

            else {
                if (typeof _main_src !== 'undefined') {
                    $(_main_img).attr('src', _main_src);
                    
                    if (_main_data_srcset) {
                        $(_main_img).attr('srcset', _main_data_srcset);
                    }
                }

                if ($(_back_img).length && typeof _back_src !== 'undefined') {
                    $(_back_img).attr('src', _back_src);
                    
                    if (_back_data_srcset) {
                        $(_back_img).attr('srcset', _back_data_srcset);
                    }
                }
            }
            
            /**
             * Change Price
             */
            if (_variation_finded.price_html) {
                $(_product_item).find('.price-wrap').html(_variation_finded.price_html);
            } else {
                $(_product_item).find('.price-wrap').html($(_product_item).find('.nasa-org-price').html());
            }

            if ($(_product_item).find('.wcpbc-price.loading').length) {
                $(_product_item).find('.wcpbc-price.loading').removeClass('loading');
            }
            
            /**
             * Badge Out of Stock
             */
            if (_variation_finded.outofstock_badge && !$(_product_item).hasClass('outofstock')) {
                if ($(_product_item).find('.nasa-badges-wrap').length < 1) {
                    $(_product_item).find('.product-img-wrap').prepend('<div class="nasa-badges-wrap"></div>');
                }
                
                $(_product_item).find('.nasa-badges-wrap').append(_variation_finded.outofstock_badge);
            }

            /**
             * Deal time and add to cart button
             */
            if (
                _variation_finded.variation_id &&
                _variation_finded.is_in_stock &&
                _variation_finded.variation_is_visible &&
                _variation_finded.is_purchasable
            ) {
                
                if ($(_add_wrap).hasClass('nasa-variable-add-to-cart-in-grid') && !$('body').hasClass('nasa-ywraq-hide-add-to-cart')) {
                    var _add_text = typeof _variation_finded.add_to_cart_txt !== 'undefined' ? _variation_finded.add_to_cart_txt : $('input[name="add_to_cart_text"]').val();
                    
                    $(_add_wrap).find('.add_to_cart_text').html(_add_text);
                    $(_add_wrap).attr('title', _add_text);
                    
                    if ($(_add_wrap).hasClass('nasa-tip')) {
                        $(_add_wrap).attr('data-tip', _add_text);

                        if ($(_add_wrap).find('.nasa-tip-content').length) {
                            $(_add_wrap).find('.nasa-tip-content').html(_add_text);
                        }
                    }
                    
                    $(_product_item).find('.add-to-cart-grid').each(function() {
                        var _add_btn = $(this);
                        if (!$(_add_btn).hasClass('nasa-active')) {
                            $(_add_btn).addClass('nasa-active');
                        }
                    });
                }
                
                var _vari_obj = {};
                for(var attr_pa in _variation_finded.attributes) {
                    _vari_obj[attr_pa] = _variation[attr_pa];
                }

                if ($(_add_wrap).hasClass('nasa-variable-add-to-cart-in-grid') && !$('body').hasClass('nasa-ywraq-hide-add-to-cart')) {
                    $(_add_wrap)
                        .attr('data-product_id', _variation_finded.variation_id)
                        .removeClass('product_type_variable')
                        .addClass('product_type_variation')
                        .attr('data-variation', JSON.stringify(_vari_obj));
                }
                
                /**
                 * Deal time
                 */
                if (typeof _variation_finded.deal_time !== 'undefined' && _variation_finded.deal_time) {
                    var _deal = true;
                    var _date = new Date();
                    var _now = _date.getTime();
                    
                    if (typeof _variation_finded.deal_time.from !== 'undefined') {
                        if (_variation_finded.deal_time.from > _now) {
                            _deal = false;
                        }
                    }
                    
                    if (_deal) {
                        if (_variation_finded.deal_time.to < _now) {
                            _deal = false;
                        }
                    }
                    
                    if (_deal && typeof _variation_finded.deal_time.html !== 'undefined') {
                        if (!$(_product_item).hasClass('product-deals')) {
                            $(_product_item).addClass('product-deals');
                        }
                        
                        // $(_product_item).find('.nasa-sc-pdeal-countdown').html('');
                        $(_product_item).find('.nasa-sc-pdeal-countdown').replaceWith(_variation_finded.deal_time.html);
                        // $(_product_item).find('.nasa-sc-pdeal-countdown').removeClass('hidden-tag');
                        
                        /**
                         * Deal Time in list
                         */
                        if (
                            $(_product_item).parents('.products.list').length &&
                            $(_product_item).find('.nasa-sc-pdeal-countdown').length &&
                            $(_product_item).find('.product-info-wrap .nasa-sc-pdeal-countdown').length < 1
                        ) {
                            
                            var _cd_list = $(_product_item).find('.nasa-sc-pdeal-countdown').clone();

                            if ($(_product_item).find('.product-des-wrap').length) {
                                $(_product_item).find('.product-des-wrap').before(_cd_list);
                            } else {
                                $(_product_item).find('.product-info-wrap').append(_cd_list);
                            }
                        }
                        
                        $('body').trigger('nasa_load_countdown');
                    }
                } else {
                    /**
                     * Deal time
                     */
                    $(_product_item).removeClass('product-deals');
                    
                    if (!$(_product_item).find('.nasa-sc-pdeal-countdown').hasClass('hidden-tag')) {
                        $(_product_item).find('.nasa-sc-pdeal-countdown').addClass('hidden-tag');
                    }
                    
                    $(_product_item).find('.nasa-sc-pdeal-countdown').html('');
                }
                
                /**
                 * Bulk Discount Badge
                 */
                if (
                    typeof _variation_finded.nasa_custom_fields !== 'undefined' &&
                    typeof _variation_finded.nasa_custom_fields.dsct_allow !== 'undefined' &&
                    _variation_finded.nasa_custom_fields.dsct_allow === '1' &&
                    typeof _variation_finded.nasa_custom_fields.dsct_badge !== 'undefined' &&
                    _variation_finded.nasa_custom_fields.dsct_badge !== ''
                ) {
                    if ($(_product_item).find('.nasa-badges-wrap').length <= 0) {
                        $(_product_item).find('.product-img-wrap').prepend('<div class="nasa-badges-wrap"></div>');
                    }
                    
                    var _badges = $(_product_item).find('.nasa-badges-wrap');
                
                    if ($(_badges).find('.bulk-label').length <= 0) {
                        $(_badges).append(_variation_finded.nasa_custom_fields.dsct_badge);
                    }
                } else {
                    $(_product_item).find('.nasa-badges-wrap .bulk-label').remove();
                }
            }

            else {
                if ($(_add_wrap).hasClass('nasa-variable-add-to-cart-in-grid')) {
                    $(_add_wrap).find('.add_to_cart_text').html(_select_text);
                    $(_add_wrap).attr('title', _select_text);
                    $(_add_wrap)
                        .attr('data-product_id', $(_variations_warp).attr('data-product_id'))
                        .addClass('product_type_variable')
                        .removeClass('product_type_variation')
                        .removeAttr('data-variation');
                
                    if ($(_add_wrap).hasClass('nasa-tip')) {
                        $(_add_wrap).attr('data-tip', _select_text);

                        if ($(_add_wrap).find('.nasa-tip-content').length) {
                            $(_add_wrap).find('.nasa-tip-content').html(_select_text);
                        }
                    }
                    
                    // $(_product_item).find('.price-wrap').html($(_product_item).find('.nasa-org-price').html());
                    $(_product_item).find('.add-to-cart-grid').removeClass('nasa-active');
                }
                
                /**
                 * Deal time
                 */
                $(_product_item).removeClass('product-deals');
                
                if (!$(_product_item).find('.nasa-sc-pdeal-countdown').hasClass('hidden-tag')) {
                    $(_product_item).find('.nasa-sc-pdeal-countdown').addClass('hidden-tag');
                }
                
                $(_product_item).find('.nasa-sc-pdeal-countdown').html('');
                
                /**
                 * Bulk Badge
                 */
                $(_product_item).find('.nasa-badges-wrap .bulk-label').remove();
            }
            
            /**
             * has toggle nasa-toggle-variants
             */
            if ($(_product_item).find('.nasa-close-variants').length) {
                $(_product_item).find('.nasa-close-variants').trigger('click');
            }
        }
        
        /**
         * No match
         */
        else {
            if (typeof _main_src !== 'undefined') {
                $(_main_img).attr('src', _main_src);
                
                if (_main_data_srcset) {
                    $(_main_img).attr('srcset', _main_data_srcset);
                }
            }

            if ($(_back_img).length && typeof _back_src !== 'undefined') {
                $(_back_img).attr('src', _back_src);
                
                if (_back_data_srcset) {
                    $(_back_img).attr('srcset', _back_data_srcset);
                }
            }
            
            if ($(_add_wrap).hasClass('nasa-variable-add-to-cart-in-grid') && !$('body').hasClass('nasa-ywraq-hide-add-to-cart')) {
                $(_add_wrap).find('.add_to_cart_text').html(_select_text);
                $(_add_wrap).attr('title', _select_text);
                
                if ($(_add_wrap).hasClass('nasa-tip')) {
                    $(_add_wrap).attr('data-tip', _select_text);
                    
                    if ($(_add_wrap).find('.nasa-tip-content').length) {
                        $(_add_wrap).find('.nasa-tip-content').html(_select_text);
                    }
                }
                
                $(_add_wrap).attr('data-product_id', $(_variations_warp).attr('data-product_id')).addClass('product_type_variable').removeClass('product_type_variation').removeAttr('data-variation');
                $(_product_item).find('.add-to-cart-grid').removeClass('nasa-active');
            }
            
            $(_product_item).find('.price-wrap').html($(_product_item).find('.nasa-org-price').html());
            
            /**
             * Deal time
             */
            $(_product_item).removeClass('product-deals');
            
            if (!$(_product_item).find('.nasa-sc-pdeal-countdown').hasClass('hidden-tag')) {
                $(_product_item).find('.nasa-sc-pdeal-countdown').addClass('hidden-tag');
            }
            
            $(_product_item).find('.nasa-sc-pdeal-countdown').html('');
            
            /**
             * Bulk Badge
             */
            $(_product_item).find('.nasa-badges-wrap .bulk-label').remove();
            
            if (_alert) {
                var text_nomatch = (typeof wc_add_to_cart_variation_params !== 'undefined') ?
                    wc_add_to_cart_variation_params.i18n_no_matching_variations_text :
                    $('input[name="nasa_no_matching_variations"]').val();

                window.alert(text_nomatch);
            }
        }
    }
}

/**
 * check image loaded
 * 
 * @param {type} $
 * @param {type} _main_img
 * @param {type} _back_img
 * @param {type} variation_id
 * @returns {undefined}
 */
function nasa_img_is_loaded($, _main_img, _back_img, variation_id) {
    if (img_loaded_array.indexOf(variation_id) === -1) {
        var _main = $(_main_img).parents('.main-img');
        var _back = $(_back_img).length ? $(_back_img).parents('.back-img') : null;
        
        if ($(_main).length || _back) {
            if ($(_main).length && !$(_main).hasClass('nasa-img-loading')) {
                $(_main).addClass('nasa-img-loading');
            }

            if (_back && !$(_back).hasClass('nasa-img-loading')) {
                $(_back).addClass('nasa-img-loading');
            }

            var img_main_loaded = setInterval(function() {
                _main_loaded = false;
                var _main_src = $(_main_img).attr('src');
                var _img_main = new Image();

                _img_main.onload = function() {
                    _main_loaded = true;
                    clearInterval(img_main_loaded);
                };

                _img_main.src = _main_src;

            }, 100);

            var img_back_loaded = setInterval(function() {
                if ($(_back).length) {
                    if (_main_loaded) {
                        _back_loaded = false;
                        var _back_src = $(_back_img).attr('src');
                        var _img_back = new Image();
    
                        _img_back.onload = function() {
                            _back_loaded = true;
                            check_img_loaded = 1;
                            img_loaded_array.push(variation_id);
                            nasa_img_clear_loaded($);
                            clearInterval(img_back_loaded);
                        };
    
                        _img_back.src = _back_src;
    
                    }
                } else {
                    if (_main_loaded) {
                        check_img_loaded = 1;
                        img_loaded_array.push(variation_id);
                        nasa_img_clear_loaded($);
                        clearInterval(img_back_loaded);
                    }
                }
            }, 100);
            
        }
    }
}

/**
 * Clear check image load
 * 
 * @param {type} $
 * @returns {undefined}
 */
function nasa_img_clear_loaded($) {
    if (typeof check_img_loaded !== 'undefined' && check_img_loaded != 0) {
        if ($('.product-item .main-img.nasa-img-loading').length) {
            $('.product-item .main-img.nasa-img-loading').removeClass('nasa-img-loading');
        }

        if ($('.product-item .back-img.nasa-img-loading').length) {
            $('.product-item .back-img.nasa-img-loading').removeClass('nasa-img-loading');
        }
        
        check_img_loaded = 0;
    }
}

/**
 * Attributes selected
 * 
 * @param {type} $
 * @param {type} _variations_warp
 * @returns {}
 */
function nasa_chosen_attrs($, _variations_warp) {
    var data = {};
    var count = 0;
    var chosen = 0;

    $(_variations_warp).find('.nasa-product-content-child').each( function() {
        var name = !$(this).hasClass('nasa-attr_type_custom') ? 'attribute_pa_' : 'attribute_';
        var value = '';
        
        var k = 0;
        $(this).find('.nasa-attr-ux-item').each(function() {
            if (k === 0) {
                name += $(this).attr('data-pa');
            }
            
            if ($(this).hasClass('nasa-active')) {
                value = $(this).attr('data-value');
            }
            
            k++;
        });

        if (value.length > 0) {
            chosen++;
        }

        count++;
        data[name] = value;
    });

    return {
        'count': count,
        'chosenCount': chosen,
        'data': data
    };
}

/**
 * Is match variation
 * 
 * @param {type} variation_attributes
 * @param {type} attributes
 * @returns {Boolean}
 */
function nasa_isMatch_variation(variation_attributes, attributes) {
    var match = true;
    for (var attr_name in variation_attributes) {
        if (typeof variation_attributes[attr_name] !== 'undefined') {
            var val1 = variation_attributes[attr_name];
            var val2 = attributes[attr_name];
            if (
                val1 !== undefined &&
                val2 !== undefined &&
                val1.length !== 0 &&
                val2.length !== 0 &&
                val1 !== val2
            ) {
                match = false;
            }
        }
    }
    
    return match;
}

/**
 * Matching variation
 * 
 * @param {type} variations
 * @param {type} attributes
 * @returns {Array|nasa_matching_variations.matching}
 */
function nasa_matching_variations(variations, attributes) {
    var matching = [];
    for (var i = 0; i < variations.length; i++) {
        var variation = variations[i];

        if (nasa_isMatch_variation(variation.attributes, attributes)) {
            matching.push(variation);
        }
    }
    
    return matching;
}

/**
 * Init variations by ajax
 * @param {type} $
 * @returns {undefined}
 */
function init_variables_products($) {
    if (
        typeof nasa_ajax_params !== 'undefined' &&
        typeof nasa_ajax_params.wc_ajax_url !== 'undefined'
    ) {
        var _urlAjax = nasa_ajax_params.wc_ajax_url.toString().replace('%%endpoint%%', 'nasa_render_variables');
        
        if ($('.nasa-product-variable-call-ajax').length > 0) {
            var _pids = [];
            
            $('.nasa-product-variable-call-ajax').each(function() {
                if (!$(this).hasClass('nasa-process')) {
                    $(this).addClass('nasa-process');
                    if (_pids.indexOf($(this).attr('data-product_id')) === -1) {
                        _pids.push($(this).attr('data-product_id'));
                    }
                }
            });

            if (_pids.length > 0) {
                $.ajax({
                    url : _urlAjax,
                    type: 'post',
                    dataType: 'json',
                    cache: false,
                    data: {
                        'pids': _pids
                    },
                    success: function(res){
                        if (typeof res.empty !== 'undefined' && res.empty === '0') {
                            for (var pid in res.products) {
                                $('.nasa-product-variable-call-ajax.nasa-product-variable-' + pid).replaceWith(res.products[pid]);
                            }

                            if ($('.nasa-product-content-variable-warp').length) {
                                $('.nasa-product-content-variable-warp').each(function() {
                                    var _this = $(this);
                                    if (!$(_this).hasClass('nasa-inited')) {
                                        $(_this).addClass('nasa-inited');
                                        change_content_product_variable($, _this, false);
                                    }
                                });
                            }
                        }
                    },
                    error: function() {
                        $('.nasa-product-variable-call-ajax').remove();
                    }
                });
            }
        }
    }
}

/**
 * Refresh variations single product
 * 
 * @param {type} $
 * @param {type} $form
 * @returns {undefined}
 */
function nasa_refresh_attrs($, $form) {
    $form.find('.nasa-attr-ux_wrap').each(function() {
        var _this = $(this);
        var _attr_name = $(_this).attr('data-attribute_name');
        
        if ($('select[name="' + _attr_name + '"]').length) {
            $(_this).find('.nasa-attr-ux').each(function() {
                var _item = $(this);
                var _value = $(_item).attr('data-value');
                if ($('select[name="' + _attr_name + '"]').find('option[value="' + _value + '"]').length <= 0) {
                    if (!$(_item).hasClass('nasa-disable')) {
                        $(_item).addClass('nasa-disable');
                    }
                }
                
                else {
                    var _option = $('select[name="' + _attr_name + '"]').find('option[value="' + _value + '"]').attr('disabled');
                    if (typeof _option !== 'undefined') {
                        if (!$(_item).hasClass('nasa-unavailable')) {
                            $(_item).addClass('nasa-unavailable');
                        }
                    }
                    $(_item).removeClass('nasa-disable');
                }
            });
        }
    });
}

/**
 * For Nasa Tabs not set
 * 
 * @param {type} $
 */
function nasa_tabs_not_set($) {
    if ($('.nasa-tabs-not-set').length) {
        $('.nasa-tabs-not-set').each(function() {
            var _this = $(this);
            var _titles = $(_this).find('.nasa-tabs');
            var _first = true;
            $(_this).find('.nasa-panels .nasa-panel').each(function() {
                var _tab_this = $(this);
                var _title = $(_tab_this).find('.nasa-move-tab-title').html();
                var _class = 'nasa-tab';
                if (_first) {
                    _class += ' active first';
                    $(_tab_this).addClass('active first');
                    
                    _first = false;
                }
                $(_titles).append('<li class="' + _class + '">' + _title + '</li>');
                $(_tab_this).find('.nasa-move-tab-title').remove();
            });
            
            $(_titles).find('.nasa-tab:last-child').addClass('last');
            
            $(_this).removeClass('nasa-tabs-not-set');
        });
    }
}

/**
 * Check is Function exsist
 * 
 * @param {type} func
 * @returns {Boolean}
 */
function _isFunction(func){
    return typeof func === "function";
}

/**
 * ontouchstart
 * 
 * @returns {window|String}
 */
function nasa_ontouchstart() {
    return ('ontouchstart' in window) || (navigator.maxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0);
}
