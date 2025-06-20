"use strict";
var _eventMore = false;
var _nasa_clear_mess_error;

/* Functions base */
function after_load_ajax_list($, destroy_masonry) {
    var _destroy_masonry = typeof destroy_masonry !== 'undefined' ? destroy_masonry : false;
    
    /**
     * Trigger after load ajax - first event
     */
    $('body').trigger('nasa_after_load_ajax_first', [_destroy_masonry]);
    
    /**
     * Init Top Categories
     */
    $('body').trigger('nasa_init_topbar_categories');
    
    /**
     * Init widgets
     */
    init_widgets($);
    
    /*
     * Parallax Breadcrumb
     */
    if (!_eventMore) {
        $('body').trigger('nasa_parallax_breadcrum');
    }
    
    /**
     * init wishlist icons
     */
    init_wishlist_icons($);
    
    /**
     * init Compare icons
     */
    init_compare_icons($);
    
    _eventMore = false;
    
    $('body').trigger('nasa_after_load_ajax');
}

/**
 * Tabs slide
 * 
 * @param {type} $
 * @param {type} _this
 * @param {type} exttime
 * @returns {undefined}
 */
function nasa_tab_slide_style($, _this, exttime) {
    exttime = !exttime ? 500 : exttime;
    
    if ($(_this).find('.nasa-slide-tab').length <= 0) {
        $(_this).append('<li class="nasa-slide-tab"></li>');
    }
    
    var _tab = $(_this).find('.nasa-slide-tab');
    var _act = $(_this).find('.nasa-tab.active');
    
    if ($(_this).find('.nasa-tab-icon').length) {
        $(_this).find('.nasa-tab > a').css({'padding': '15px 30px'});
    }
    
    var _width_border = parseInt($(_this).css("border-top-width"));
    _width_border = !_width_border ? 0 : _width_border;
    
    var _pos = $(_act).position();
    $(_tab).show().animate({
        'height': $(_act).height() + (2*_width_border),
        'width': $(_act).width() + (2*_width_border),
        'top': _pos.top - _width_border,
        'left': _pos.left - _width_border
    }, exttime);
}

/**
 * Load Compare
 * 
 * @param {type} $
 * @returns {undefined}
 */
var _compare_init = false;
var _compare_loading = false;
function load_compare($) {
    if ($('#tmpl-nasa-mini-compare').length) {
        var _compare = $('#tmpl-nasa-mini-compare').html();
        $('#tmpl-nasa-mini-compare').replaceWith(_compare);
    }
    
    if ($('.nasa-compare-list-bottom').length && !_compare_init) {
        _compare_init = true;
        _compare_loading = true;
        
        if (
            typeof nasa_ajax_params !== 'undefined' &&
            typeof nasa_ajax_params.wc_ajax_url !== 'undefined'
        ) {
            var _urlAjax = nasa_ajax_params.wc_ajax_url.toString().replace('%%endpoint%%', 'nasa_load_compare');

            var _compare_table = $('.nasa-wrap-table-compare').length ? true : false;
            
            $.ajax({
                url: _urlAjax,
                type: 'post',
                dataType: 'json',
                cache: false,
                data: {
                    compare_table: _compare_table
                },
                beforeSend: function () {
                    /* if ($('.nasa-compare-list-bottom').find('.nasa-loader').length <= 0) {
                        $('.nasa-compare-list-bottom').append('<div class="nasa-loader"></div>');
                    } */
                },
                success: function (res) {
                    if (typeof res.success !== 'undefined' && res.success === '1') {
                        $('.nasa-compare-list-bottom').html(res.content);
                    }

                    // $('.nasa-compare-list-bottom').find('.nasa-loader').remove();
                    
                    _compare_loading = false;
                },
                error: function () {
                    _compare_loading = false;
                }
            });
        }
    }
}

/**
 * Add Compare
 * 
 * @param {type} _id
 * @param {type} $
 * @returns {undefined}
 */
function add_compare_product(_id, $) {
    if (
        typeof nasa_ajax_params !== 'undefined' &&
        typeof nasa_ajax_params.wc_ajax_url !== 'undefined'
    ) {
        if ($('#tmpl-nasa-mini-compare').length) {
            var _compare = $('#tmpl-nasa-mini-compare').html();
            $('#tmpl-nasa-mini-compare').replaceWith(_compare);
        }
        
        _compare_init = true;

        var _urlAjax = nasa_ajax_params.wc_ajax_url.toString().replace('%%endpoint%%', 'nasa_add_compare_product');
        
        var _compare_table = $('.nasa-wrap-table-compare').length ? true : false;
        
        if (_compare_loading) {
            setTimeout(function() {
                add_compare_product(_id, $);
            }, 200);
        } else {
            $.ajax({
                url: _urlAjax,
                type: 'post',
                dataType: 'json',
                cache: false,
                data: {
                    pid: _id,
                    compare_table: _compare_table
                },
                beforeSend: function () {
                    // load_compare($);
                    show_compare($);

                    if ($('.nasa-compare-list-bottom').find('.nasa-loader').length <= 0) {
                        $('.nasa-compare-list-bottom').append('<div class="nasa-loader"></div>');
                    }
                },
                success: function (res) {
                    if (typeof res.result_compare !== 'undefined' && res.result_compare === 'success') {

                        if (res.mini_compare !== 'no-change') {
                            if ($('.nasa-compare-list').length) {
                                $('.nasa-compare-list').replaceWith(res.mini_compare);
                            }

                            if ($('.nasa-mini-number.compare-number').length) {

                                $('.nasa-mini-number.compare-number').html(convert_count_items($, res.count_compare));
                                if (res.count_compare === 0) {
                                    if (!$('.nasa-mini-number.compare-number').hasClass('nasa-product-empty')) {
                                        $('.nasa-mini-number.compare-number').addClass('nasa-product-empty');
                                    }
                                } else {
                                    if ($('.nasa-mini-number.compare-number').hasClass('nasa-product-empty')) {
                                        $('.nasa-mini-number.compare-number').removeClass('nasa-product-empty');
                                    }
                                }
                            }

                            if (_compare_table) {
                                $('.nasa-wrap-table-compare').replaceWith(res.result_table);
                            }

                        }

                        if (!$('.nasa-compare[data-prod="' + _id + '"]').hasClass('added')) {
                            $('.nasa-compare[data-prod="' + _id + '"]').addClass('added');
                        }

                        if (!$('.nasa-compare[data-prod="' + _id + '"]').hasClass('nasa-added')) {
                            $('.nasa-compare[data-prod="' + _id + '"]').addClass('nasa-added');
                            $('.nasa-compare[data-prod="' + _id + '"]').find(".nasa-icon-text-wrap").animate({ scrollTop: 24 }, 400 );
                        }

                        $('body').trigger('nasa_added_compare_product');
                    }

                    $('.nasa-compare-list-bottom').find('.nasa-loader').remove();
                },
                error: function () {

                }
            });
        }
    }
}

/**
 * Remove Compare
 * 
 * @param {type} _id
 * @param {type} $
 * @returns {undefined}
 */
function remove_compare_product(_id, $) {
    if (
        typeof nasa_ajax_params !== 'undefined' &&
        typeof nasa_ajax_params.wc_ajax_url !== 'undefined'
    ) {
        if ($('#tmpl-nasa-mini-compare').length) {
            var _compare = $('#tmpl-nasa-mini-compare').html();
            $('#tmpl-nasa-mini-compare').replaceWith(_compare);
        }
        
        _compare_init = true;
        
        var _urlAjax = nasa_ajax_params.wc_ajax_url.toString().replace('%%endpoint%%', 'nasa_remove_compare_product');
        
        var _compare_table = $('.nasa-wrap-table-compare').length ? true : false;

        $.ajax({
            url: _urlAjax,
            type: 'post',
            dataType: 'json',
            cache: false,
            data: {
                pid: _id,
                compare_table: _compare_table
            },
            beforeSend: function () {
                if ($('.nasa-compare-list-bottom').find('.nasa-loader').length <= 0) {
                    $('.nasa-compare-list-bottom').append('<div class="nasa-loader"></div>');
                }
                
                if ($('table.nasa-table-compare tr.remove-item td.nasa-compare-view-product_' + _id).length) {
                    $('table.nasa-table-compare').css('opacity', '0.3').prepend('<div class="nasa-loader"></div>');
                }
            },
            success: function (res) {
                if (typeof res.result_compare !== 'undefined' && res.result_compare === 'success') {
                    
                    if (res.mini_compare !== 'no-change') {
                        if ($('.nasa-compare-list').length) {
                            $('.nasa-compare-list').replaceWith(res.mini_compare);
                        }
                        
                        $('.nasa-compare[data-prod="' + _id + '"]').removeClass('added');
                        $('.nasa-compare[data-prod="' + _id + '"]').removeClass('nasa-added');
                        $('.nasa-compare[data-prod="' + _id + '"]').find(".nasa-icon-text-wrap").animate({ scrollTop: 0 }, 400 );
                        
                        if ($('.nasa-mini-number.compare-number').length) {
                            $('.nasa-mini-number.compare-number').html(convert_count_items($, res.count_compare));
                            if (res.count_compare === 0) {
                                if (!$('.nasa-mini-number.compare-number').hasClass('nasa-product-empty')) {
                                    $('.nasa-mini-number.compare-number').addClass('nasa-product-empty');
                                }
                            } else {
                                if ($('.nasa-mini-number.compare-number').hasClass('nasa-product-empty')) {
                                    $('.nasa-mini-number.compare-number').removeClass('nasa-product-empty');
                                }
                            }
                        }

                        if (_compare_table) {
                            $('.nasa-wrap-table-compare').replaceWith(res.result_table);
                        }
                    }
                    
                    $('body').trigger('nasa_removed_compare_product');

                    setTimeout(function () {
                        if (res.count_compare === 0) {
                            $('.nasa-close-mini-compare').trigger('click');
                        }
                    }, 2000);
                }

                $('table.nasa-table-compare').find('.nasa-loader').remove();
                $('.nasa-compare-list-bottom').find('.nasa-loader').remove();
            },
            error: function() {

            }
        });
    }
}

/**
 * Remove All Compare
 * 
 * @param {type} $
 * @returns {undefined}
 */
function remove_all_compare_product($) {
    if (
        typeof nasa_ajax_params !== 'undefined' &&
        typeof nasa_ajax_params.wc_ajax_url !== 'undefined'
    ) {
        if ($('#tmpl-nasa-mini-compare').length) {
            var _compare = $('#tmpl-nasa-mini-compare').html();
            $('#tmpl-nasa-mini-compare').replaceWith(_compare);
        }
        
        if ($('#yith-woocompare-preview-bar').length) {
            $('#yith-woocompare-preview-bar').find('.remove>a').addClass('js-blocked');
        }

        _compare_init = true;
        
        var _urlAjax = nasa_ajax_params.wc_ajax_url.toString().replace('%%endpoint%%', 'nasa_remove_all_compare');
        
        var _compare_table = $('.nasa-wrap-table-compare').length ? true : false;
        if ($('#yith-woocompare-preview-bar').length) {
            _compare_table = true;
        }
        
        $.ajax({
            url: _urlAjax,
            type: 'post',
            dataType: 'json',
            cache: false,
            data: {
                compare_table: _compare_table
            },
            beforeSend: function () {
                if ($('.nasa-compare-list-bottom').find('.nasa-loader').length <= 0) {
                    $('.nasa-compare-list-bottom').append('<div class="nasa-loader"></div>');
                }
            },
            success: function (res) {
                if (res.result_compare === 'success') {
                    if (res.mini_compare !== 'no-change') {
                        if ($('#yith-woocompare-preview-bar').length) {
                            $('#yith-woocompare-preview-bar').replaceWith(res.mini_compare);
                            show_compare($);
                        } else if ($('.nasa-compare-list').length) {
                            $('.nasa-compare-list').replaceWith(res.mini_compare);
                        }
                        
                        $('.nasa-compare.ns-has-wrap.nasa-added').find(".nasa-icon-text-wrap").animate({scrollTop: 0}, 400);
                        $('.nasa-compare').removeClass('added');
                        $('.nasa-compare').removeClass('nasa-added');
                        
                        if ($('.nasa-mini-number.compare-number').length) {
                            $('.nasa-mini-number.compare-number').html('0');
                            if (!$('.nasa-mini-number.compare-number').hasClass('nasa-product-empty')) {
                                $('.nasa-mini-number.compare-number').addClass('nasa-product-empty');
                            }
                        }

                        if (_compare_table) {
                            $('.nasa-wrap-table-compare').replaceWith(res.result_table);
                        }
                    }
                    
                    $('body').trigger('nasa_removed_all_compare_product');

                    setTimeout(function () {
                        $('.nasa-compare-success').fadeOut(200);
                        $('.nasa-compare-exists').fadeOut(200);
                        $('.nasa-close-mini-compare').trigger('click');
                    }, 1000);
                }

                $('.nasa-compare-list-bottom').find('.nasa-loader').remove();
            },
            error: function() {

            }
        });
    }
}

/**
 * Show compare
 * 
 * @param {type} $
 * @returns {undefined}
 */
function show_compare($) {
    /**
     * Append stylesheet Off Canvas
     */
    $('body').trigger('nasa_append_style_off_canvas');
    var compareList  = $('#yith-woocompare-preview-bar').length ? $('#yith-woocompare-preview-bar') : $('.nasa-compare-list-bottom');
    
    if ($(compareList).length) {
        if($('.ns-cart-popup-wrap').length && $('.ns-cart-popup-wrap').hasClass('nasa-active')) {
            $('.ns-cart-popup-wrap .popup-cart-close').trigger('click');
        }

        $('.black-window').fadeIn(200).addClass('desk-window');
        
        if (!$('body').hasClass('m-ovhd')) {
            $('body').addClass('m-ovhd');
        }
        
        if ($('.nasa-show-compare').length && !$('.nasa-show-compare').hasClass('nasa-showed')) {
            $('.nasa-show-compare').addClass('nasa-showed');
        }
        
        if (!$(compareList).hasClass('nasa-active')) {
            $(compareList).addClass('nasa-active');
        }
    }
}

/**
 * Hide compare
 * 
 * @param {type} $
 * @returns {undefined}
 */
function hide_compare($) {
    var compareList  = $('#yith-woocompare-preview-bar').length ? $('#yith-woocompare-preview-bar') : $('.nasa-compare-list-bottom');

    if ($(compareList).length) {
        $('.black-window').removeClass('desk-window');
        $('.black-window').fadeOut(500);
        
        $('body').removeClass('m-ovhd');
        $('body').removeClass('yith-woocompare-popup-open');
        $('html, body').css('overflow', '');
        
        if ($('.nasa-show-compare').length) {
            $('.nasa-show-compare').removeClass('nasa-showed');
        }
        
        $(compareList).removeClass('nasa-active');
    }
}

/**
 * Single add to cart
 * 
 * @param {type} $
 * @param {type} _this
 * @param {type} _id
 * @param {type} _quantity
 * @param {type} _type
 * @param {type} _variation_id
 * @param {type} _variation
 * @param {type} _data_wishlist
 * @returns {undefined|Boolean}
 */
function nasa_single_add_to_cart($, _this, _id, _quantity, _type, _variation_id, _variation, _data_wishlist) {
    var _form = $(_this).parents('form.cart');
    var _from_mini_cart = false;
    if (_type === 'grouped') {
        if ($(_form).length) {
            if ($(_form).find('.nasa-custom-fields input[name="nasa_cart_sidebar"]').length) {
                $(_form).find('.nasa-custom-fields input[name="nasa_cart_sidebar"]').val('1');
            } else {
                $(_form).find('.nasa-custom-fields').append('<input type="hidden" name="nasa_cart_sidebar" value="1" />');
            }
            
            $(_form).submit();
        }
        
        return;
    }
    else {
        if (
            typeof nasa_ajax_params !== 'undefined' &&
            typeof nasa_ajax_params.wc_ajax_url !== 'undefined'
        ) {
            var _urlAjax = nasa_ajax_params.wc_ajax_url.toString().replace('%%endpoint%%', 'nasa_single_add_to_cart');
            var _data_cart_item_key = null;
            var _data = {
                product_id: _id,
                quantity: _quantity,
                product_type: _type,
                variation_id: _variation_id,
                variation: _variation,
                data_wislist: _data_wishlist
            };
            
            if ($(_this).hasClass('btn-add-from-minicart')) {
                _from_mini_cart = true;
                _data_cart_item_key = $(_this).attr('data-cart_item_key');
                if ($(_this).parents('#cart-sidebar').length) {
                    var _pa = $(_this).parents('#cart-sidebar');
                    var _remove_btn = $(_pa).find('.nasa-minicart-items .remove_from_cart_button[data-cart_item_key="'+_data_cart_item_key+'"]');

                    $(_pa).addClass('nasa_update_from_mini_cart');
                    $(_remove_btn).parents('.mini-cart-item').remove();
                }
            }

            $('body').trigger('adding_to_cart', [_this,_data]);

            if ($(_form).length) {
                if (_type === 'simple') {
                    $(_form).find('.nasa-custom-fields').append('<input type="hidden" name="add-to-cart" value="' + _id + '" />');
                }
                
                _data = $(_form).serializeArray();
                $(_form).find('.nasa-custom-fields [name="add-to-cart"]').remove();
            }
            
            if (_from_mini_cart) {
                $(_this).parents('#cart-sidebar').find('.ext-node.active .nasa-close-node').trigger('click');
                $('.nasa-change_variation_mini_cart').addClass('nasa-cart-variation-updating');
                $.ajax({
                    type: 'POST',
                    url: nasa_ajax_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'remove_from_cart' ),
                    data: {
                        cart_item_key : _data_cart_item_key
                    },
                    success: function(response) {
                        nasa_ajax_single_add_to_cart($,_urlAjax,_data,_this,_id);
                    },
                    error: function() {
                    },
                    dataType: 'json'
                });
            } else {
                nasa_ajax_single_add_to_cart($,_urlAjax,_data,_this,_id);
            }
        }
    }
    
    return false;
}
/**
 * Ajax add to cart
 */
function nasa_ajax_single_add_to_cart($,_urlAjax,_data,_this,_id) {
    $.ajax({
        url: _urlAjax,
        type: 'post',
        dataType: 'json',
        cache: false,
        data: _data,
        beforeSend: function () {
            $(_this).removeClass('added');
            $(_this).removeClass('nasa-added');
            $(_this).addClass('loading');
            
            if (
                $('.ns_btn-fixed .single_add_to_cart_button').length &&
                !$('.ns_btn-fixed .single_add_to_cart_button').hasClass('loading')
            ) {
                $('.ns_btn-fixed .single_add_to_cart_button').addClass('loading');
            }
        },
        success: function (res) {
            $(_this).removeClass('loading');

            if (res.error) {
                if ($(_this).hasClass('add-to-cart-grid') && !$(_this).hasClass('btn-from-wishlist')) {
                    window.location.href = res.product_url;
                } else {
                    set_nasa_notice($, res.message);

                    if (typeof _nasa_clear_mess_error !== 'undefined') {
                        clearTimeout(_nasa_clear_mess_error);
                    }

                    _nasa_clear_mess_error = setTimeout(function () {
                        if ($('.nasa-close-notice').length) {
                            $('.nasa-close-notice').trigger('click');
                        }
                    }, 5000);
                   
                    if ($('.ns-cart-popup-wrap').length) {
                        $('.ns-cart-popup-wrap').removeClass('crazy-loading').find('.nasa-stclose.popup-cart-close').trigger('click');
                    }

                    if ($('#cart-sidebar').length && $('#cart-sidebar').hasClass('nasa-active')) {
                        $('#cart-sidebar').find('.nasa-sidebar-close a').trigger('click');
                        $('#cart-sidebar').removeClass('crazy-loading');
                        $('body').trigger('wc_fragment_refresh');
                    }
                    
                    if ($('.single_add_to_cart_button').length) {
                        $('.single_add_to_cart_button').removeClass('loading');
                    }
                }
            } else {
                if (typeof res.redirect !== 'undefined' && res.redirect) {
                    window.location.href = res.redirect;
                } else {
                    var fragments = res.fragments;
                    if (fragments) {
                        $.each(fragments, function (key, value) {
                            $(key).addClass('updating');
                            $(key).replaceWith(value);
                        });

                        if (!$(_this).hasClass('added')) {
                            $(_this).addClass('added');
                        }

                        if (!$(_this).hasClass('nasa-added')) {
                            $(_this).addClass('nasa-added');
                        }
                    }

                    if ($('.wishlist_sidebar').length) {
                        if (typeof res.wishlist !== 'undefined') {
                            $('.wishlist_sidebar').replaceWith(res.wishlist);

                            setTimeout(function() {
                                init_wishlist_icons($, true);
                            }, 350);

                            if ($('.nasa-mini-number.wishlist-number').length) {
                                var sl_wislist = parseInt(res.wishlistcount);
                                $('.nasa-mini-number.wishlist-number').html(convert_count_items($, sl_wislist));
                                
                                if (sl_wislist > 0) {
                                    $('.nasa-mini-number.wishlist-number').removeClass('nasa-product-empty');
                                }
                                else if (sl_wislist === 0 && !$('.wishlist-number').hasClass('nasa-product-empty')) {
                                    $('.nasa-mini-number.wishlist-number').addClass('nasa-product-empty');
                                }
                            }

                            if ($('.add-to-wishlist-' + _id).length) {
                                $('.add-to-wishlist-' + _id).find('.yith-wcwl-add-button').show();
                                $('.add-to-wishlist-' + _id).find('.yith-wcwl-wishlistaddedbrowse').hide();
                                $('.add-to-wishlist-' + _id).find('.yith-wcwl-wishlistexistsbrowse').hide();
                            }
                        }
                    }

                    if ($('.page-shopping-cart').length === 1) {
                        $.ajax({
                            url: window.location.href,
                            type: 'get',
                            dataType: 'html',
                            cache: false,
                            data: {},
                            success: function (res) {
                                var $html = $.parseHTML(res);

                                if ($('.nasa-shopping-cart-form').length === 1) {
                                    var $new_form   = $('.nasa-shopping-cart-form', $html);
                                    var $new_totals = $('.cart_totals', $html);
                                    var $notices    = $('.woocommerce-error, .woocommerce-message, .woocommerce-info', $html);
                                    $('.nasa-shopping-cart-form').replaceWith($new_form);

                                    if ($notices.length) {
                                        $('.nasa-shopping-cart-form').before($notices);
                                    }
                                    $('.cart_totals').replaceWith($new_totals);

                                } else {
                                    var $new_content = $('.page-shopping-cart', $html);
                                    $('.page-shopping-cart').replaceWith($new_content);
                                }

                                $('body').trigger('updated_cart_totals');
                                $('body').trigger('updated_wc_div');
                                $('.nasa-shopping-cart-form').find('[name=update_cart]').prop('disabled', true);
                            }
                        });
                    }

                    $('body').trigger('added_to_cart', [res.fragments, res.cart_hash, _this]);
                }
            }
        }
    });
}

/**
 * Bundle Yith popup
 * 
 * @param {type} $
 * @param {type} _this
 * @returns {undefined}
 */
function load_combo_popup($, _this) {
    if (
        typeof nasa_ajax_params !== 'undefined' &&
        typeof nasa_ajax_params.wc_ajax_url !== 'undefined'
    ) {
        var _urlAjax = nasa_ajax_params.wc_ajax_url.toString().replace('%%endpoint%%', 'nasa_combo_products');
        
        var item = $(_this).parents('.product-item');
        if (!$(_this).hasClass('nasaing')) {
            $('.btn-combo-link').addClass('nasaing');
            var pid = $(_this).attr('data-prod');
            if (pid) {
                $.ajax({
                    url: _urlAjax,
                    type: 'post',
                    dataType: 'json',
                    cache: false,
                    data: {
                        id: pid,
                        'title_columns': 2
                    },
                    beforeSend: function () {
                        $(item).append('<div class="nasa-loader" style="top:50%"></div>');
                        $(item).find('.product-inner').css('opacity', '0.3');
                    },
                    success: function (res) {
                        /**
                         * Open Magnific
                         */
                        $('body').trigger('ns_magnific_popup_open', [{
                            mainClass: 'my-mfp-slide-bottom nasa-combo-popup-wrap',
                            closeMarkup: '<a class="nasa-mfp-close nasa-stclose" href="javascript:void(0);" title="' + $('input[name="nasa-close-string"]').val() + '"></a>',
                            items: {
                                src: '<div class="row nasa-combo-popup nasa-combo-row comboed-row zoom-anim-dialog" data-prod="' + pid + '">' + res.content + '</div>',
                                type: 'inline'
                            },
                            removalDelay: 300,
                            callbacks: {
                                afterClose: function() {

                                }
                            }
                        }]);

                        $('body').trigger('nasa_load_slick_slider');

                        setTimeout(function () {
                            $('.btn-combo-link').removeClass('nasaing');
                            $(item).find('.nasa-loader').remove();
                            $(item).find('.product-inner').css('opacity', '1');
                            if (typeof wow_enable !== 'undefined' && wow_enable) {
                                var _data_animate, _delay;
                                $('.nasa-combo-popup').find('.product-item').each(function() {
                                    var _this = $(this);
                                    _data_animate = $(_this).attr('data-wow');
                                    _delay = parseInt($(_this).attr('data-wow-delay'));
                                    $(_this).css({
                                        'visibility': 'visible',
                                        'animation-delay': _delay + 'ms',
                                        'animation-name': _data_animate
                                    }).addClass('animated');
                                });
                            } else {
                                $('.nasa-combo-popup').find('.product-item').css({'visibility': 'visible'});
                            }
                        }, 500);
                    },
                    error: function () {
                        $('.btn-combo-link').removeClass('nasaing');
                    }
                });
            }
        }
    }
}

/**
 * 
 * @param {type} $
 * @param {type} _menu_item
 * @returns {undefined}
 */
function recursive_convert_item($, _menu_item) {
    var _cursor = $(_menu_item).next();
    
    if ($(_cursor).length && !$(_cursor).hasClass('nasa-main') && !$(_cursor).hasClass('nasa-wrap-mains')) {
        $(_menu_item).find('.sub-menu').append(_cursor);
        
        recursive_convert_item($, _menu_item);
    }
}

/**
 * Convert Mega menu
 * 
 * @param {type} $
 * @param {type} _menu
 * @returns {jQuery}
 */
function convert_mega_menu($, _menu) {
    var _mega = $(_menu).clone();

    if ($(_mega).find('.nav-column-links > .sub-menu > .menu-item.nasa-main').length) {
        $(_mega).find('.nav-column-links > .sub-menu > .menu-item.nasa-main').each(function() {
            var _this = $(this);
            
            var _sub_parent = $(_this).parent();
            
            if ($(_sub_parent).find('.nasa-wrap-mains').length < 1) {
                $(_sub_parent).append('<li class="nasa-wrap-mains hidden-tag"></li>');
            }
            
            if (!$(_this).hasClass('menu-item-has-children')) {
                $(_this).addClass('menu-item-has-children');
            }
            
            if (!$(_this).hasClass('menu-parent-item')) {
                $(_this).addClass('menu-parent-item');
            }
            
            if ($(_this).find('.sub-menu').length < 1) {
                $(_this).append('<div class="nav-column-links"><ul class="sub-menu"></ul></div>');
            }
            
            recursive_convert_item($, _this);
            
            $(_sub_parent).find('.nasa-wrap-mains').append(_this);
        });
        
        $(_mega).find('.nav-column-links > .sub-menu > .nasa-wrap-mains').each(function() {
            var _parent = $(this).parent().parent().parent();
            
            $(_parent).after($(this).html());
            
            $(this).remove();
        });
    }

    if ($(_mega).find('.nasa-megamenu.root-item.nasa-mega-static-block ').length) {
        $(_mega).find('.nasa-megamenu.root-item.nasa-mega-static-block ').each(function() {
            var _this = $(this);
            $(_this).find('.nav-dropdown').remove();
        });
    }
    
    return $(_mega).html();
}

/**
 * Mobile Menu
 * 
 * @type init_menu_mobile.mini_acc|init_menu_mobile.head_menu|String
 * @param {type} $
 * @returns {undefined}
 */
function init_menu_mobile($, reset) {
    var _reset = typeof reset === 'undefined' ? false : reset;
    
    if (_reset) {
        $('#nasa-menu-sidebar-content .nasa-menu-for-mobile').remove();
    }
    
    $('body').trigger('nasa_before_init_menu_mobile');
    
    if ($('#nasa-menu-sidebar-content .nasa-menu-for-mobile').length <= 0) {
        var _mobileDetect = $('body').hasClass('nasa-in-mobile') ? true : false;
        
        var _mobile_menu = '';
        var _main_menu = '';

        if ($('.nasa-main-menu').length) {
            var _mega = $('.nasa-main-menu');
            _main_menu += convert_mega_menu($, _mega);
            
            if (_mobileDetect) {
                $(_mega).remove();
            }
        }

        if ($('.header-type-builder .header-nav, #masthead ul.hfe-nav-menu').length) {
            $('.header-type-builder .header-nav, #masthead ul.hfe-nav-menu').each(function() {
                var _this = $(this);
                if (!$(_this).hasClass('ns-included')) {
                    var _sticky = $(_this).parents('.elementor-element.elementor-sticky').length ? $(_this).parents('.elementor-element.elementor-sticky') : false;
                    
                    if (_sticky) {
                        var _dataid = $(_sticky).attr('data-id');
                        if (_dataid && $('.elementor-element[data-id="' + _dataid + '"]').length) {
                            $('.elementor-element[data-id="' + _dataid + '"]').find('.header-nav, ul.hfe-nav-menu').addClass('ns-included');
                        }
                    } else {
                        $(_this).addClass('ns-included');
                    }
                    
                    var _mega = $(_this);
                    _main_menu += convert_mega_menu($, _mega);
                }
            });
        }

        /**
         * Vertical menu in Header
         */
        if ($('.nasa-vertical-header .vertical-menu-wrapper').length){
            var _vmega = $('.nasa-vertical-header .vertical-menu-wrapper');
            var ver_menu = convert_mega_menu($, _vmega);
            
            var ver_menu_title = $('.nasa-vertical-header .nasa-title-vertical-menu').html();
            var ver_menu_warp = '<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children menu-parent-item default-menu root-item nasa-menu-none-event li_accordion"><a href="javascript:void(0);">' + ver_menu_title + '</a><div class="nav-dropdown-mobile"><ul class="sub-menu">' + ver_menu + '</ul></div></li>';
            
            if ($('.nasa-vertical-header').hasClass('nasa-focus-menu')) {
                _mobile_menu = ver_menu_warp + _main_menu;
            } else {
                _mobile_menu += _main_menu + ver_menu_warp;
            }
            
            if (_mobileDetect) {
                $('.nasa-vertical-header').remove();
            }
        }
        
        /**
         * Had not Vertical menu in Header
         */
        else {
            _mobile_menu = _main_menu;
        }

        /**
         * Heading
         */
        if ($('#heading-menu-mobile').length) {
            _mobile_menu = '<li class="menu-item root-item menu-item-heading nasa-menu-heading">' + $('#heading-menu-mobile').html() + '</li>' + _mobile_menu;
        }

        /**
         * Vertical Menu in content page
         */
        if ($('.nasa-shortcode-menu.vertical-menu').length) {
            $('.nasa-shortcode-menu.vertical-menu').each(function() {
                var _this = $(this);
                
                var ver_menu_title_sc = $(_this).find('.section-title').length ? $(_this).find('.section-title').html() : $(_this).find('.nasa-title-menu:eq(0)').html();
                var ver_menu_sc = $(_this).find('.vertical-menu-wrapper').html();
                
                if (!$('#nasa-menu-sidebar-content').hasClass('nasa-standard')) {
                    ver_menu_title_sc = '<h5 class="menu-item-heading margin-top-35 margin-bottom-10">' + ver_menu_title_sc + '</h5>';
                } else {
                    ver_menu_title_sc = '<a href="javascript:void(0);" rel="nofollow">' + ver_menu_title_sc + '</a>';
                }
                
                var ver_menu_warp_sc = '<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children menu-parent-item default-menu root-item nasa-menu-none-event li_accordion">' + ver_menu_title_sc + '<div class="nav-dropdown-mobile"><ul class="sub-menu">' + ver_menu_sc + '</ul></div></li>';
                
                _mobile_menu += ver_menu_warp_sc;
                
                if (_mobileDetect) {
                    $(_this).remove();
                }
            });
        }
        
        /**
         * Topbar menu
         */
        if ($('.nasa-topbar-menu').length) {
            _mobile_menu += $('.nasa-topbar-menu').html();
            
            if (_mobileDetect) {
                $('.nasa-topbar-menu').remove();
            }
        }

        /**
         * Mobile account
         */
        if ($('#tmpl-nasa-mobile-account').length) {
            if ($('#nasa-menu-sidebar-content').hasClass('nasa-standard') && $('#tmpl-nasa-mobile-account').find('.nasa-menu-item-account').length) {
                _mobile_menu += '<li class="menu-item root-item menu-item-account nasa-m-account menu-item-has-children root-item">' + $('#tmpl-nasa-mobile-account').find('.nasa-menu-item-account').html() + '</li>';
            } else {
                _mobile_menu += '<li class="menu-item root-item menu-item-account nasa-m-account">' + $('#tmpl-nasa-mobile-account').html() + '</li>';
            }
            
            $('#tmpl-nasa-mobile-account').remove();
        }

        /**
         * Switch language
         */
        var switch_lang = '';
        if ($('.header-switch-languages').length) {
            switch_lang = $('.header-switch-languages').html();
            if (_mobileDetect) {
                $('.header-switch-languages').remove();
            }
        }

        if ($('.header-multi-languages').length) {
            switch_lang = $('.header-multi-languages').html();
            if (_mobileDetect) {
                $('.header-multi-languages').remove();
            }
        }

        if ($('#nasa-menu-sidebar-content').hasClass('nasa-standard')) {
            _mobile_menu = '<ul id="mobile-navigation" class="header-nav nasa-menu-accordion nasa-menu-for-mobile">' + _mobile_menu + switch_lang + '</ul>';
        } else {
            _mobile_menu = '<ul id="mobile-navigation" class="header-nav nasa-menu-accordion nasa-menu-for-mobile">' + switch_lang + _mobile_menu + '</ul>';
        }

        if ($('#nasa-menu-sidebar-content #mobile-navigation').length) {
            $('#nasa-menu-sidebar-content #mobile-navigation').replaceWith(_mobile_menu);
        } else {
            $('#nasa-menu-sidebar-content .nasa-mobile-nav-wrap').append(_mobile_menu);
        }
        
        var _nav = $('#nasa-menu-sidebar-content #mobile-navigation');
        
        if ($(_nav).find('.nasa-select-currencies').length) {
            var _currency = $(_nav).find('.nasa-select-currencies');
            
            /**
             * For WPML - Multi Currencies
             */
            if ($(_currency).find('.wcml_currency_switcher').length) {
                var _class = $(_currency).find('.wcml_currency_switcher').attr('class');
                
                _class += ' menu-item-has-children root-item li_accordion nasa-select-currencies';
                var _currencyObj = $(_currency).find('.wcml-cs-active-currency').clone();
                $(_currencyObj).addClass(_class);
                $(_currencyObj).find('.wcml-cs-submenu').addClass('sub-menu');
                if (!$(_currencyObj).find('.wcml-cs-submenu').hasClass('wcml_currency_switcher')) {
                    $(_currencyObj).find('.wcml-cs-submenu').addClass('wcml_currency_switcher');
                }

                $(_nav).find('.nasa-select-currencies').replaceWith(_currencyObj);
            }
            
            /**
             * For Others
             */
            $('body').trigger('nasa_after_render_currencies_switcher', [_currency]);
        }

        /**
         * Re-render Attrs
         */
        $(_nav).find('.root-item > a').removeAttr('style');
        $(_nav).find('.nav-dropdown').attr('class', 'nav-dropdown-mobile').removeAttr('style');
        $(_nav).find('.nav-column-links').addClass('nav-dropdown-mobile');

        /**
         * Fix for nasa-core not active.
         */
        $(_nav).find('.sub-menu').each(function() {
            if (!$(this).parent('.nav-dropdown-mobile').length) {
                $(this).wrap('<div class="nav-dropdown-mobile"></div>');
            }
        });

        $(_nav).find('.nav-dropdown-mobile').find('.sub-menu').removeAttr('style');
        $(_nav).find('hr.hr-nasa-megamenu').remove();
        $(_nav).find('li').each(function(){
            if ($(this).find('.sub-menu').length){
                $(this).addClass('li_accordion');
                
                if ($(this).hasClass('current-menu-ancestor') || $(this).hasClass('current-menu-parent') || $(this).hasClass('current-menu-item')){
                    $(this).addClass('active');
                    $(this).prepend('<a href="javascript:void(0);" class="accordion"></a>');
                } else {
                    $(this).prepend('<a href="javascript:void(0);" class="accordion"></a>').find('>.nav-dropdown-mobile').hide();
                }
            }
        });
        
        $(_nav).find('a').removeAttr('style');
        $(_nav).find('.menu-show-more').remove();
        
        $('body').trigger('nasa_after_load_mobile_menu');
    }
}

/**
 * position Mobile menu
 * 
 * @param {type} $
 * @returns {undefined}
 */
function position_menu_mobile($) {
    if ($('#nasa-menu-sidebar-content').length && $('#mobile-navigation').length) {
        if ($('#mobile-navigation').length && $('#mobile-navigation').attr('data-show') !== '1') {
            $('#nasa-menu-sidebar-content').removeClass('nasa-active');
                
            var _h_adminbar = $('#wpadminbar').length ? $('#wpadminbar').height() : 0;

            if (_h_adminbar > 0) {
                $('#nasa-menu-sidebar-content').css({'top': _h_adminbar});
            }
        }
    }
}

/**
 * Init Mini Wishlist Icon
 * 
 * @param {type} $
 * @returns {undefined}
 */
function init_mini_wishlist($) {
    if ($('input[name="nasa_wishlist_cookie_name"]').length) {
        var _wishlistArr = get_wishlist_ids($);
        if (_wishlistArr.length) {
            if ($('.nasa-mini-number.wishlist-number').length) {
                var sl_wislist = _wishlistArr.length;
                $('.nasa-mini-number.wishlist-number').html(convert_count_items($, sl_wislist));
                
                if (sl_wislist > 0) {
                    $('.nasa-mini-number.wishlist-number').removeClass('nasa-product-empty');
                }
                
                if (sl_wislist === 0 && !$('.wishlist-number').hasClass('nasa-product-empty')) {
                    $('.nasa-mini-number.wishlist-number').addClass('nasa-product-empty');
                }
            }
        }
    }
}

/**
 * init Wishlist icons
 * 
 * @param {type} $
 * @param {type} init
 * @returns {undefined}
 */
function init_wishlist_icons($, init) {
    var _init = typeof init === 'undefined' ? false : init;
    
    /**
     * NasaTheme Wishlist
     */
    if ($('input[name="nasa_wishlist_cookie_name"]').length) {
        var _wishlistArr = get_wishlist_ids($);
        
        if (_wishlistArr.length) {
            $('.btn-wishlist').each(function() {
                var _this = $(this);
                var _prod = $(_this).attr('data-prod');

                if (_wishlistArr.indexOf(_prod) !== -1) {
                    if (!$(_this).hasClass('nasa-added')) {
                        $(_this).addClass('nasa-added');
                       
                        if($(_this).hasClass('ns-has-wrap')) {
                            $(_this).find(".nasa-icon-text-wrap").animate({
                                scrollTop: 24
                            }, 400);
                        }
                    }

                    if (!$(_this).find('.wishlist-icon').hasClass('added')) {
                        $(_this).find('.wishlist-icon').addClass('added');
                    }
                }

                else if (_init) {
                    if ($(_this).hasClass('nasa-added')) {
                        $(_this).removeClass('nasa-added');
                    }

                    if ($(_this).find('.wishlist-icon').hasClass('added')) {
                        $(_this).find('.wishlist-icon').removeClass('added');
                    }
                }
            });
        }
    }
}

/**
 * init Compare icons
 * 
 * @param {type} $
 * @param {type} _init
 * @returns {undefined}
 */
function init_compare_icons($, _init) {
    var init = typeof _init !== 'undefined' ? _init : false;
    var _comparetArr = get_compare_ids($);
    
    if (init && $('.nasa-mini-number.compare-number').length) {
        var _slCompare = _comparetArr.length;
        $('.nasa-mini-number.compare-number').html(convert_count_items($, _slCompare));
        
        if (_slCompare <= 0) {
            if (!$('.nasa-mini-number.compare-number').hasClass('nasa-product-empty')) {
                $('.nasa-mini-number.compare-number').addClass('nasa-product-empty');
            }
        } else {
            $('.nasa-mini-number.compare-number').removeClass('nasa-product-empty');
        }
    }

    if (_comparetArr.length && $('.btn-compare').length) {
        $('.btn-compare').each(function() {
            var _this = $(this);
            var _prod = $(_this).attr('data-prod');

            if (_comparetArr.indexOf(_prod) !== -1) {
                if (!$(_this).hasClass('added')) {
                    if (!$('body').hasClass('nasa-has-yth-compare-3_0')) {
                        $(_this).addClass('added');
                    }
                    
                    $(_this).addClass('added');
                }
                
                if (!$(_this).hasClass('nasa-added')) {
                    if (!$('body').hasClass('nasa-has-yth-compare-3_0')) {
                        $(_this).addClass('nasa-added');
                    }
                    
                    $(_this).find(".nasa-icon-text-wrap").animate({scrollTop: 24}, 400);
                }
            } else {
                $(_this).removeClass('added');
                $(_this).removeClass('nasa-added');
                $(_this).find(".nasa-icon-text-wrap").animate({scrollTop: 0}, 400);
            }
        });
    }
}

/**
 * Event after added to cart
 * Popup Your Order
 * 
 * @param {type} $
 * @returns {undefined}
 */
function after_added_to_cart($) {
    if ($('.ns-cart-popup-wrap').length) {
        /**
         * Check has items
         */
        if ($('.ns-cart-popup-wrap .woocommerce-cart-form__cart-item').length || $('.ns-cart-popup-wrap .ns-cart-popup-v2').length) {
            if ($('.nasa-static-sidebar').hasClass('nasa-active')) {
                $('.nasa-static-sidebar').removeClass('nasa-active');
            }

            var _event_add = $('.ns-cart-popup-wrap .ns-cart-popup-v2').length ? 'popup_2' : 'popup';

            if (_event_add === 'popup') {
                $('.ns-cart-popup-wrap').show();
                if ($('.ns-cart-popup-wrap .nasa-slick-slider').length) {
                    $('body').trigger('nasa_reload_slick_slider_private', [$('.ns-cart-popup-wrap')]);
                }
            }
            
            setTimeout(function() {
                
                if (_event_add === 'popup') {
                    $('.ns-cart-popup-wrap').addClass('nasa-active');
                    $('.black-window').fadeIn(200).addClass('desk-window');
                }
               
                $('body').trigger('get_content_popup_v2', [true]);
            }, 50);
        }
        
        /**
         * With Empty item in cart
         */
        else {
            $('.ns-cart-popup-wrap .popup-cart-close').trigger('click');
        }
    }
}

/**
 * Reload MiniCart
 * 
 * @param {type} $
 * @returns {undefined}
 */
function reload_mini_cart($) {
    $('body').trigger('wc_fragment_refresh');
}

/**
 * Init Shipping free notification
 * 
 * @param {type} $
 * @returns {undefined}
 */
function init_shipping_free_notification($, confetti) {
    if ($('.nasa-total-condition').length) {
        
        var _confetti = typeof confetti !== 'undefined' ? confetti : false;
       
        if ($('form.nasa-shopping-cart-form').length && $('#cart-sidebar .nasa-total-condition').length) {
            $('#cart-sidebar .nasa-total-condition').remove();
        }
        
        if ($('.ns-cart-popup').length && $('.ns-cart-popup').parents('.mfp-container').find('#nasa-confetti').length <= 0) {
            if ($('#cart-sidebar').find('#nasa-confetti').length) {
                $('.ns-cart-popup').parents('.mfp-container').append('<canvas id="nasa-confetti" style="display: none;">');
            }
        }

        $('.nasa-total-condition').each(function() {
            if (!$(this).hasClass('nasa-active')) {

                $(this).addClass('nasa-active');
                var _per = $(this).attr('data-per');

                $.cookie('nasa_curent_per_shipping', _per, { expires: _cookie_live, path: '/' });

                $(this).find('.nasa-subtotal-condition').animate({
                    'width': _per + '%'
                }, {
                    duration: 350
                });
                
                
                if (_per >= 100) {
                    $(this).parents('.nasa-total-condition-wrap').addClass('free');
                    
                    if (!_confetti_run) {
                        _confetti_run = true;
                        if (_confetti) {
                            $('body').trigger('nasa_confetti_init');
                            $('#nasa-confetti').each(function(){
                                $('body').trigger('nasa_confetti_restart', [2500]);
                            });
                        }
                    }
                } else {
                    _confetti_run = false;
                }
            }
        });

    }

}

/**
 * Init Widgets Toggle
 * 
 * @param {type} $
 * @returns {undefined}
 */
function init_widgets($) {
    if ($('.widget').length && !$('body').hasClass('nasa-disable-toggle-widgets')) {
        $('.widget').each(function() {
            var _this = $(this);
            if (!$(_this).hasClass('nasa-inited')) {

                var _key = $(_this).attr('id');

                var _title = '';

                if ($(_this).find('.widget-title').length) {
                    _title = $(_this).find('.widget-title').clone();
                    $(_this).find('.widget-title').remove();
                }

                if (_key && _title !== '') {
                    var _cookie = $.cookie(_key);

                    if ($('.nasa-toggle-widgets-alc').length && $('.ns-sticky-scroll-sidebar').length <= 0) {
                        _cookie = 'hide';
                    }

                    if ($('.ns-sticky-scroll-sidebar').length) {
                        if (_cookie === null || typeof _cookie === 'undefined') {
                            if ($(_this).index() !== 0) {
                                _cookie = 'hide';
                            }
                        }
                    }

                    var _a_toggle = '<a href="javascript:void(0);" class="nasa-toggle-widget"></a>';
                    var _wrap = '<div class="nasa-open-toggle"></div>';
                    if (_cookie === 'hide') {
                        _a_toggle = '<a href="javascript:void(0);" class="nasa-toggle-widget nasa-hide"></a>';
                        _wrap = '<div class="nasa-open-toggle widget-hidden"></div>';
                    }
                    
                    $(_this).wrapInner(_wrap);
                    
                    $(_this).prepend(_a_toggle);
                    $(_this).prepend(_title);
                }

                $(_this).addClass('nasa-inited');
            }
        });
    }
}

/**
 * init Notices
 * 
 * @param {type} $
 * @returns {undefined}
 */
function init_nasa_notices($) {
    if (!$('body').hasClass('woocommerce-cart') && !$('body').hasClass('woocommerce-checkout')) {
        if ($('.woocommerce-notices-wrapper').length) {
            $('.woocommerce-notices-wrapper').each(function() {
                var _this = $(this);
                if ($(_this).find('.cart-empty').length) {
                    var _cart_empty = $(_this).find('.cart-empty');
                    $(_this).after(_cart_empty);
                }

                if ($(_this).find('*').length && $(_this).find('.nasa-close-notice').length <= 0) {
                    $(_this).append('<a class="nasa-close-notice" href="javascript:void(0);">' + ns_close_svg + '</a>');
                }

                if ($(_this).find('*').length && $(_this).find('.ns-check-svg').length <= 0) {
                    $(_this).find('.woocommerce-message').prepend(ns_check_svg);
                }
            });
        }
    }
}

/**
 * set Notice
 * 
 * @param {type} $
 * @param {type} content
 * @returns {undefined}
 */
function set_nasa_notice($, content) {
    if (!$('body').hasClass('woocommerce-checkout')) {
        if ($('.woocommerce-notices-wrapper').length <= 0) {
            $('body').append('<div class="woocommerce-notices-wrapper"></div>');
        }

        $('.woocommerce-notices-wrapper').html(content);
        init_nasa_notices($);
    }
}

/**
 * 
 * @param {type} $
 * @returns {undefined}get Compare ids
 */
function get_compare_ids($) {
    var _cookie_compare = (typeof yith_woocompare !== 'undefined' && typeof yith_woocompare.cookie_name !== 'undefined') ?
        yith_woocompare.cookie_name : $('input[name="nasa_woocompare_cookie_name"]').val();
    
    if (!_cookie_compare) { 
        return [];
    }

    var _pids = $.cookie(_cookie_compare);
    if (_pids) {
        _pids = _pids.replace('[','').replace(']','').split(",").map(String);
        
        if (_pids.length === 1 && !_pids[0]) {
            return [];
        }
    }
    
    return typeof _pids !== 'undefined' && _pids.length ? _pids : [];
}

/**
 * 
 * @param {type} $
 * @returns {undefined}get Wishlist ids
 */
function get_wishlist_ids($) {
    if ($('input[name="nasa_wishlist_cookie_name"]').length) {
        var _cookie_wishlist = $('input[name="nasa_wishlist_cookie_name"]').val();
        var _pids = $.cookie(_cookie_wishlist);
        if (_pids) {
            _pids = _pids.replace('[', '').replace(']', '').split(",").map(String);
            
            if (_pids.length === 1 && !_pids[0]) {
                return [];
            }
        }
        
        return typeof _pids !== 'undefined' && _pids.length ? _pids : [];
    } else {
        return [];
    }
}

/**
 * Load Wishlist
 */
var _wishlist_init = false;
function load_wishlist($) {
    if ($('#nasa-wishlist-sidebar-content').length && !_wishlist_init) {
        _wishlist_init = true;
        
        if (
            typeof nasa_ajax_params !== 'undefined' &&
            typeof nasa_ajax_params.wc_ajax_url !== 'undefined'
        ) {
            var _urlAjax = nasa_ajax_params.wc_ajax_url.toString().replace('%%endpoint%%', 'nasa_load_wishlist');
            $.ajax({
                url: _urlAjax,
                type: 'post',
                dataType: 'json',
                cache: false,
                data: {},
                beforeSend: function () {
                    
                },
                success: function (res) {
                    if (typeof res.success !== 'undefined' && res.success === '1') {
                        $('#nasa-wishlist-sidebar-content').replaceWith(res.content);
                        
                        if ($('.nasa-tr-wishlist-item.item-invisible').length) {
                            var _remove = [];
                            $('.nasa-tr-wishlist-item.item-invisible').each(function() {
                                var product_id = $(this).attr('data-row-id');
                                if (product_id) {
                                    _remove.push(product_id);
                                }
                                
                                $(this).remove();
                            });
                            
                            var _urlAjax = nasa_ajax_params.wc_ajax_url.toString().replace('%%endpoint%%', 'nasa_remove_wishlist_hidden');
                            
                            $.ajax({
                                url: _urlAjax,
                                type: 'post',
                                dataType: 'json',
                                cache: false,
                                data: {
                                    product_ids: _remove
                                },
                                beforeSend: function () {

                                },
                                success: function (response) {
                                    if (typeof response.success !== 'undefined' && response.success === '1') {
                                        var sl_wislist = parseInt(response.count);
                                        $('.nasa-mini-number.wishlist-number').html(convert_count_items($, sl_wislist));
                                        if (sl_wislist > 0) {
                                            $('.nasa-mini-number.wishlist-number').removeClass('nasa-product-empty');
                                        }
                                        else if (sl_wislist === 0 && !$('.nasa-mini-number.wishlist-number').hasClass('nasa-product-empty')) {
                                            $('.nasa-mini-number.wishlist-number').addClass('nasa-product-empty');
                                        }

                                        $('body').trigger('init_carousel_pro_empty_sidebar',[$('#nasa-wishlist-sidebar')]);
                                    }
                                },
                                error: function () {

                                }
                            });
                        }

                        $('body').trigger('init_carousel_pro_empty_sidebar',[$('#nasa-wishlist-sidebar')]);
                    }
                },
                error: function () {

                }
            });
        }
    }
}

/**
 * Add wishlist item NasaTheme Wishlist
 * @param {type} $
 * @param {type} _pid
 * @returns {undefined}
 */
var _nasa_clear_notice_wishlist;
function nasa_process_wishlist($, _pid, _action) {
    if (
        typeof nasa_ajax_params !== 'undefined' &&
        typeof nasa_ajax_params.wc_ajax_url !== 'undefined'
    ) {
        var _urlAjax = nasa_ajax_params.wc_ajax_url.toString().replace('%%endpoint%%', _action);
        
        var _data = {
            product_id: _pid
        };
        
        if ($('.widget_shopping_wishlist_content').length) {
            _data['show_content'] = '1';
        }
        
        $.ajax({
            url: _urlAjax,
            type: 'post',
            dataType: 'json',
            cache: false,
            data: _data,
            beforeSend: function () {
                if ($('.nasa-close-notice').length) {
                    $('.nasa-close-notice').trigger('click');
                }
                
                if (typeof _nasa_clear_notice_wishlist !== 'undefined') {
                    clearTimeout(_nasa_clear_notice_wishlist);
                }
            },
            success: function (res) {
                if (typeof res.success !== 'undefined' && res.success === '1') {
                    var sl_wislist = parseInt(res.count);
                    $('.nasa-mini-number.wishlist-number').html(convert_count_items($, sl_wislist));
                    if (sl_wislist > 0) {
                        $('.nasa-mini-number.wishlist-number').removeClass('nasa-product-empty');
                    }
                    else if (sl_wislist === 0 && !$('.nasa-mini-number.wishlist-number').hasClass('nasa-product-empty')) {
                        $('.nasa-mini-number.wishlist-number').addClass('nasa-product-empty');
                    }
                    
                    if (_action === 'nasa_add_to_wishlist') {
                        $('.btn-wishlist[data-prod="' + _pid + '"]').each(function() {
                            if (!$(this).hasClass('nasa-added')) {
                                $(this).addClass('nasa-added');
                            }
                        });
                    }
                    
                    if (_action === 'nasa_remove_from_wishlist') {
                        $('.btn-wishlist[data-prod="' + _pid + '"]').removeClass('nasa-added');
                    }
                    
                    if ($('.widget_shopping_wishlist_content').length && typeof res.content !== 'undefined' && res.content) {
                        $('.widget_shopping_wishlist_content').replaceWith(res.content);
                    }

                    if (typeof res.mess !== 'undefined' && res.mess) {
                        set_nasa_notice($, res.mess);
                    }

                    _nasa_clear_notice_wishlist = setTimeout(function() {
                        if ($('.nasa-close-notice').length) {
                            $('.nasa-close-notice').trigger('click');
                        }
                    }, 5000);
                    
                    $('body').trigger('nasa_processed_wishlist', [_pid, _action]);
                }
                
                $('.btn-wishlist').removeClass('nasa-disabled');
            },
            error: function () {
                $('.btn-wishlist').removeClass('nasa-disabled');
            }
        });
    }
}

/**
 * Convert Count Items
 * 
 * @param {type} number
 * @returns {String}
 */
function convert_count_items($, number) {
    var _number = parseInt(number);
    if ($('input[name="nasa_less_total_items"]').length && $('input[name="nasa_less_total_items"]').val() === '1') {
        return _number > 9 ? '9+' : _number.toString();
    } else {
        return _number.toString();
    }
}

/**
 * Animate Scroll to Top
 * 
 * @param {type} $
 * @param {type} _dom
 * @param {type} _ms
 * @returns {undefined}
 */
function animate_scroll_to_top($, _dom, _ms) {
    var ms = typeof _ms === 'undefined' ? 500 : _ms;
    var _pos_top = 0;
    if (typeof _dom !== 'undefined' && _dom && $(_dom).length) {
        _pos_top = $(_dom).offset().top;
    }

    if (_pos_top) {
        if ($('body').find('.nasa-header-sticky').length && $('.sticky-wrapper').length) {
            _pos_top = _pos_top - 100;
        }

        if ($('#wpadminbar').length) {
            _pos_top = _pos_top - $('#wpadminbar').height();
        }
        
        _pos_top = _pos_top - 10;
    }

    $('html, body').animate({scrollTop: _pos_top}, ms);
}

/**
 * init accordion
 */
function init_accordion($) {
    if ($('.nasa-accordions-content .nasa-accordion-title a').length) {
        $('.nasa-accordions-content').each(function() {
            if (!$(this).hasClass('nasa-inited')) {
                $(this).addClass('nasa-inited');
                
                if ($(this).hasClass('nasa-accodion-first-hide')) {
                    $(this).find('.nasa-accordion.first').removeClass('active');
                    $(this).find('.nasa-panel.first').removeClass('active');
                    $(this).removeClass('nasa-accodion-first-hide');
                } else {
                    $(this).find('.nasa-panel.first.active').slideDown(200);
                }
            }
        });
    }
}

/**
 * 
 * @param {type} $
 * @returns {undefined}
 */
function init_bottom_bar_mobile($) {
    if ($('.top-bar-wrap-type-1').length) {
        $('body').addClass('nasa-top-bar-in-mobile');
    }
    
    if ($('#tmpl-nasa-bottom-bar').length) {
        var _contents = $('#tmpl-nasa-bottom-bar').html();
        $('#tmpl-nasa-bottom-bar').replaceWith(_contents);
    }

    if (
        $('.toggle-topbar-shop-mobile, .nasa-toggle-top-bar-click, .toggle-sidebar-shop, .toggle-sidebar').length ||
        ($('.dokan-single-store').length && $('.dokan-store-sidebar').length)
    ) {
        $('.nasa-bot-item.nasa-bot-item-sidebar').removeClass('hidden-tag');
    } else {
        if ($('.nasa-bot-item.nasa-bot-item-search').length) {
            $('.nasa-bot-item.nasa-bot-item-search').removeClass('hidden-tag');
        }
        
        if ($('.nasa-bot-item.item-other').length) {
            $('.nasa-bot-item.item-other').removeClass('hidden-tag');
        }
    }

    if ($('.nasa-bottom-bar .nasa-bottom-bar-icons .nasa-bot-item').length) {
        var col = $('.nasa-bottom-bar .nasa-bot-item').length - $('.nasa-bottom-bar .nasa-bot-item.hidden-tag').length;
        if (col) {
            $('.nasa-bottom-bar .nasa-bottom-bar-icons').addClass('nasa-' + col.toString() + '-columns');
        }
    }
    
    if ($('.header-type-builder .nasa-search').length) {
        if ($('.nasa-bot-item.nasa-bot-item-search').length && !$('.nasa-bot-item.nasa-bot-item-search').hasClass('hidden-tag')) {
            $('.nasa-bot-item.nasa-bot-item-search').addClass('hidden-tag');
        }
    }
    
    if (!$('.nasa-bottom-bar').hasClass('nasa-active')) {
        $('.nasa-bottom-bar').addClass('nasa-active');
        
        var _h = $('.nasa-bottom-bar').height();
        
        if (_h) {
            $('body').css({'padding-bottom': $('.nasa-bottom-bar').outerHeight()});
        }
    }
}

function ns_round_2_step(value, step) {
  const precision = 1 / step;
  return Math.round(value * precision) / precision;
}
