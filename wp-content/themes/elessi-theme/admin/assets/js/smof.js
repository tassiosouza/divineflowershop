/**
 * SMOF js
 *
 * contains the core functionalities to be used
 * inside SMOF
 */
jQuery.noConflict();

_msie = false;
_msie_version = 0;
if (typeof navigator.userAgentData === 'undefined' && navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
    _msie = true;
    _msie_version = RegExp.$1;
}

var _disable_save = false;

/** Fire up jQuery - let's dance! 
 */
jQuery(document).ready(function($) {

    //(un)fold options in a checkbox-group
    $('.fld').on('click', function () {
        var $fold = '.f_' + this.id;
        $($fold).slideToggle('normal', "swing");
    });

    //Color picker
    $('.of-color').wpColorPicker();

    //hides warning if js is enabled			
    $('#js-warning').hide();

    //Tabify Options			
    $('.group').hide();

    // Get the URL parameter for tab
    function getURLParameter(name) {
        return decodeURI((RegExp(name + '=' + '(.+?)(&|$)').exec(location.search) || [, ''])[1]);
    }

    // If the $_GET param of tab is set, use that for the tab that should be open
    if (getURLParameter('tab') != "") {
        $.cookie('of_current_opt', '#' + getURLParameter('tab'), {expires: 7, path: '/'});
    }

    // Display last current tab	
    if ($.cookie("of_current_opt") === null) {
        $('.group:first-child').fadeIn(200);
        $('#of-nav li:first-child').addClass('current');
    } else {
        var hooks = $('#hooks').html();
        hooks = JSON.parse(hooks);

        $.each(hooks, function (key, value) {
            if ($.cookie("of_current_opt") == '#of-option-' + value) {
                $('.group#of-option-' + value).fadeIn(200);
                $('#of-nav li.' + value).addClass('current');
            }
        });
    }

    //Current Menu Class
    $('#of-nav li a').on('click', function (evt) {
        evt.preventDefault();

        $('#of-nav li').removeClass('current');
        $(this).parent().addClass('current');
        var clicked_group = $(this).attr('href');
        $.cookie('of_current_opt', clicked_group, {expires: 7, path: '/'});
        $('.group').hide();
        $(clicked_group).fadeIn(200);
        
        return false;
    });

    //Expand Options 
    var flip = 0;

    $('#expand_options').on('click', function () {
        if (flip == 0) {
            flip = 1;
            $('#of_container #of-nav').hide();
            $('#of_container #content').width('100%');
            $('#of_container .group').add('#of_container .group h2').show();

            $(this).removeClass('expand');
            $(this).addClass('close');
            $(this).text('Close');
        } else {
            flip = 0;
            $('#of_container #of-nav').show();
            $('#of_container #content').width('75%');
            $('#of_container .group').add('#of_container .group h2').hide();
            $('#of_container .group:first-child').show();
            $('#of_container #of-nav li').removeClass('current');
            $('#of_container #of-nav li:first-child').addClass('current');

            $(this).removeClass('close');
            $(this).addClass('expand');
            $(this).text('Expand');
            $('#content').removeClass('opt-searching');
            $('body').trigger('clear-search-otp');
        }
    });

    //Update Message popup
    $.fn.center = function () {
        this.animate({"top": ($(window).height() - this.height() - 200) / 2 + $(window).scrollTop() + "px"}, 100);
        this.css("left", ($('#of_container').length && $('#of_container').width() > 270) ? (($('#of_container').width() - 270) / 2) : 250);
        return this;
    };

    // $('#of-popup-save').center();
    // $('#of-popup-reset').center();
    // $('#of-popup-fail').center();

    // $(window).on('scroll', function () {
    //     $('#of-popup-save').center();
    //     $('#of-popup-reset').center();
    //     $('#of-popup-fail').center();
    // });

    //Masked Inputs (images as radio buttons)
    /* $('.of-radio-img-img').on('click', function () {
        $(this).parent().parent().find('.of-radio-img-img').removeClass('of-radio-img-selected');
        $(this).addClass('of-radio-img-selected');
    }); */
    
    $('body').on('click', '.ns-radio-img-a', function() {
        var _this = $(this);
        var _wrap = $(_this).parents('.of-radio-img-wrap');
        var _wrap_all = $(_this).parents('.controls');
        $(_wrap_all).find('.ns-radio-img-a').removeClass('ns-radio-img-selected');
        $(_wrap_all).find('input[type="radio"]').prop('checked', false);
        
        if (!$(_this).hasClass('ns-radio-img-selected')) {
            $(_this).addClass('ns-radio-img-selected');
        }
        
        $(_wrap).find('input[type="radio"]').prop('checked', true).trigger('change');

        if ($(_wrap).find('input[type="radio"]').val() == 'new-3') {
            var _target_wrap = $('input[value="accordion"]').parents('.of-radio-img-wrap');
            $(_target_wrap).find('.ns-radio-img-a').trigger('click');
        }
    });
    
    $('.of-radio-img-label').hide();
    $('.of-radio-img-img').show();
    $('.of-radio-img-radio').hide();

    //Masked Inputs (background images as radio buttons)
    $('.of-radio-tile-img').on('click', function () {
        $(this).parent().parent().find('.of-radio-tile-img').removeClass('of-radio-tile-selected');
        $(this).addClass('of-radio-tile-selected');
    });
    
    $('.of-radio-tile-label').hide();
    $('.of-radio-tile-img').show();
    $('.of-radio-tile-radio').hide();

    // Style Select
    (function ($) {
        styleSelect = {
            init: function () {
                $('.select_wrapper').each(function () {
                    $(this).prepend('<span>' + $(this).find('.select option:selected').text() + '</span>');
                });
                $('body').on('change', '.select', function () {
                    $(this).prev('span').replaceWith('<span>' + $(this).find('option:selected').text() + '</span>');
                });
                $('.select').on('change', function (event) {
                    $(this).prev('span').replaceWith('<span>' + $(this).find('option:selected').text() + '</span>');
                });
            }
        };
        $(document).ready(function () {
            styleSelect.init();
        });
    })(jQuery);

    /** Aquagraphite Slider MOD */

    //Hide (Collapse) the toggle containers on load
    $(".slide_body").hide();

    //Switch the "Open" and "Close" state per click then slide up/down (depending on open/close state)
    $('body').on('click', ".slide_edit_button", function () {
        /*
         //display as an collapses
         $(".slide_header").removeClass("active");	
         $(".slide_body").slideUp("fast");
         */
        //toggle for each
        $(this).parent().toggleClass("active").next().slideToggle("fast");
        
        return false; //Prevent the browser jump to the link anchor
    });

    // Update slide title upon typing		
    function update_slider_title(e) {
        var element = e;
        if (this.timer) {
            clearTimeout(element.timer);
        }
        this.timer = setTimeout(function () {
            $(element).parent().prev().find('strong').text(element.value);
        }, 100);
        return true;
    }

    $('body').on('keyup', '.of-slider-title', function () {
        update_slider_title(this);
    });

    //Remove individual slide
    $('body').on('click', '.slide_delete_button', function () {
        // event.preventDefault();
        var agree = confirm("Are you sure you wish to delete this slide?");
        if (agree) {
            var $trash = $(this).parents('li');
            //$trash.slideUp('slow', function(){ $trash.remove(); }); //chrome + confirm bug made slideUp not working...
            $trash.animate({
                opacity: 0.25,
                height: 0
            }, 500, function () {
                $(this).remove();
            });
            return false; //Prevent the browser jump to the link anchor
        } else {
            return false;
        }
    });

    //Add new slide
    $('body').on('click', ".slide_add_button", function () {
        var slidesContainer = $(this).prev();
        var sliderId = slidesContainer.attr('id');

        var numArr = $('#' + sliderId + ' li').find('.order').map(function () {
            var str = this.id;
            str = str.replace(/\D/g, '');
            str = parseFloat(str);
            return str;
        }).get();

        var maxNum = Math.max.apply(Math, numArr);
        if (maxNum < 1) {
            maxNum = 0;
        }
        var newNum = maxNum + 1;

        var newSlide = '<li class="temphide"><div class="slide_header"><strong>Slide ' + newNum + '</strong><input type="hidden" class="slide of-input order" name="' + sliderId + '[' + newNum + '][order]" id="' + sliderId + '_slide_order-' + newNum + '" value="' + newNum + '"><a class="slide_edit_button" href="#">Edit</a></div><div class="slide_body" style="display: none; "><label>Title</label><input class="slide of-input of-slider-title" name="' + sliderId + '[' + newNum + '][title]" id="' + sliderId + '_' + newNum + '_slide_title" value=""><label>Image URL</label><input class="upload slide of-input" name="' + sliderId + '[' + newNum + '][url]" id="' + sliderId + '_' + newNum + '_slide_url" value=""><div class="upload_button_div"><span class="button media_upload_button" id="' + sliderId + '_' + newNum + '">Upload</span><span class="button remove-image hide" id="reset_' + sliderId + '_' + newNum + '" title="' + sliderId + '_' + newNum + '">Remove</span></div><div class="screenshot"></div><label>Link URL (optional)</label><input class="slide of-input" name="' + sliderId + '[' + newNum + '][link]" id="' + sliderId + '_' + newNum + '_slide_link" value=""><label>Description (optional)</label><textarea class="slide of-input" name="' + sliderId + '[' + newNum + '][description]" id="' + sliderId + '_' + newNum + '_slide_description" cols="8" rows="8"></textarea><a class="slide_delete_button" href="#">Delete</a><div class="clear"></div></div></li>';

        slidesContainer.append(newSlide);
        var nSlide = slidesContainer.find('.temphide');
        nSlide.fadeIn('fast', function () {
            $(this).removeClass('temphide');
        });

        optionsframework_file_bindings(); // re-initialise upload image..

        return false; //prevent jumps, as always..
    });

    //Sort slides
    $('.slider').find('ul').each(function () {
        var id = $(this).attr('id');
        $('#' + id).sortable({
            placeholder: "placeholder",
            opacity: 0.6,
            handle: ".slide_header",
            cancel: "a"
        });
    });

    /**	Sorter (Layout Manager) */
    $('.sorter').each(function () {
        var id = $(this).attr('id');
        $('#' + id).find('ul').sortable({
            items: 'li',
            placeholder: "placeholder",
            connectWith: '.sortlist_' + id,
            opacity: 0.6,
            update: function () {
                $(this).find('.position').each(function () {
                    var listID = $(this).parent().attr('id');
                    var parentID = $(this).parent().parent().attr('id');
                    parentID = parentID.replace(id + '_', '');
                    var optionID = $(this).parent().parent().parent().attr('id');
                    $(this).prop("name", optionID + '[' + parentID + '][' + listID + ']');
                });
            }
        });
    });

    /**	Ajax Backup & Restore MOD */
    //backup button
    $('body').on('click', '#of_backup_button', function () {
        var answer = confirm("Click OK to backup your current saved options.")

        if (answer) {
            var clickedObject = $(this);
            var clickedID = $(this).attr('id');

            var nonce = $('#security').val();

            var data = {
                action: 'of_ajax_post_action',
                type: 'backup_options',
                security: nonce
            };

            $.post(ajaxurl, data, function (response) {
                //check nonce
                if (response == -1) { //failed
                    var fail_popup = $('#of-popup-fail');
                    fail_popup.addClass('active');
                    window.setTimeout(function () {
                        fail_popup.removeClass('active');
                    }, 2000);
                } else {
                    var success_popup = $('#of-popup-save');
                    success_popup.addClass('active');
                    window.setTimeout(function () {
                        location.reload();
                    }, 1000);
                }
            });
        }
        
        return false;
    });

    //restore button
    $('body').on('click', '#of_restore_button', function () {
        var answer = confirm("'Warning: All of your current options will be replaced with the data from your last backup! Proceed?");
        if (answer) {

            var clickedObject = $(this);
            var clickedID = $(this).attr('id');

            var nonce = $('#security').val();

            var data = {
                action: 'of_ajax_post_action',
                type: 'restore_options',
                security: nonce
            };

            $.post(ajaxurl, data, function (response) {
                //check nonce
                if (response == -1) { //failed
                    var fail_popup = $('#of-popup-fail');
                    fail_popup.addClass('active');
                    window.setTimeout(function () {
                        fail_popup.removeClass('active');
                    }, 2000);
                } else {
                    var success_popup = $('#of-popup-save');
                    success_popup.addClass('active');
                    window.setTimeout(function () {
                        location.reload();
                    }, 1000);
                }
            });
        }

        return false;
    });

    /**	Ajax Transfer (Import/Export) Option */
    $('body').on('click', '#of_import_button', function () {
        var answer = confirm("Click OK to import options.");

        if (answer) {
            var clickedObject = $(this);
            var clickedID = $(this).attr('id');

            var nonce = $('#security').val();

            var import_data = $('#export_data').val();

            var data = {
                action: 'of_ajax_post_action',
                type: 'import_options',
                security: nonce,
                data: import_data
            };

            $.post(ajaxurl, data, function (response) {
                var fail_popup = $('#of-popup-fail');
                var success_popup = $('#of-popup-save');

                //check nonce
                if (response == -1) { //failed
                    fail_popup.addClass('active');
                    window.setTimeout(function () {
                        fail_popup.removeClass('active');
                    }, 2000);
                } else {
                    success_popup.addClass('active');
                    window.setTimeout(function () {
                        location.reload();
                    }, 1000);
                }
            });
        }

        return false;
    });

    /** AJAX Save Options */
    $('body').on('click', '.nasa-of_save', function () {
        if (!_disable_save) {
            _disable_save = true;
            
            var nonce = $('#security').val();

            $('.ajax-loading-img').fadeIn();

            //get serialized data from all our option fields			
            var serializedReturn = $('#of_form :input[name][name!="security"][name!="of_reset"]').serialize();

            $('#of_form :input[type=checkbox]').each(function () {
                if (!this.checked) {
                    serializedReturn += '&' + this.name + '=0';
                }
            });

            var data = {
                type: 'save',
                action: 'of_ajax_post_action',
                security: nonce,
                data: serializedReturn
            };

            $.post(ajaxurl, data, function (response) {
                var success = $('#of-popup-save');
                var fail = $('#of-popup-fail');
                var loading = $('.ajax-loading-img');
                loading.fadeOut();

                if (response == 1) {
                    success.addClass('active');
                    
                    _disable_save = false;
                } else {
                    fail.addClass('active');
                    
                    _disable_save = false;
                }

                window.setTimeout(function () {
                    success.removeClass('active');
                    fail.removeClass('active');
                }, 2000);
            });
        }

        return false;
    });

    /* AJAX Options Reset */
    $('#of_reset').on('click', function () {
        if (!_disable_save) {
            _disable_save = true;
            
            //confirm reset
            var answer = confirm("Click OK to reset. All settings will be lost and replaced with default settings!");

            //ajax reset
            if (answer) {
                var nonce = $('#security').val();

                $('.ajax-reset-loading-img').fadeIn();

                var data = {
                    type: 'reset',
                    action: 'of_ajax_post_action',
                    security: nonce
                };

                $.post(ajaxurl, data, function (response) {
                    var success = $('#of-popup-reset');
                    var fail = $('#of-popup-fail');
                    var loading = $('.ajax-reset-loading-img');
                    loading.fadeOut();

                    if (response == 1) {
                        success.addClass('active');
                        window.setTimeout(function () {
                            location.reload();
                        }, 1000);
                    } else {
                        fail.addClass('active');
                        window.setTimeout(function () {
                            fail.removeClass('active');
                        }, 2000);
                    }
                });

            } else {
                _disable_save = false;
            }
        }

        return false;
    });

    /**	Tipsy @since v1.3 */
    /* if ($().tipsy) {
        $('.tooltip, .typography-size, .typography-height, .typography-face, .typography-style, .of-typography-color').tipsy({
            fade: true,
            gravity: 's',
            opacity: 0.7
        });
    } */

    /**
     * JQuery UI Slider function
     * Dependencies 	 : jquery, jquery-ui-slider
     * Feature added by : Smartik - http://smartik.ws/
     * Date 			 : 03.17.2013
     */
    $('.smof_sliderui').each(function () {
        var obj = $(this);
        var sId = "#" + obj.data('id');
        var val = parseInt(obj.data('val'));
        var min = parseInt(obj.data('min'));
        var max = parseInt(obj.data('max'));
        var step = parseInt(obj.data('step'));

        //slider init
        obj.slider({
            value: val,
            min: min,
            max: max,
            step: step,
            range: "min",
            slide: function (event, ui) {
                $(sId).val(ui.value);
            }
        });
    });

    /**
     * Switch
     * Dependencies 	 : jquery
     * Feature added by : Smartik - http://smartik.ws/
     * Date 			 : 03.17.2013
     */
    $(".cb-enable").on('click', function () {
        var parent = $(this).parents('.switch-options');
        $('.cb-disable', parent).removeClass('selected');
        $(this).addClass('selected');
        $('.main_checkbox', parent).attr('checked', true);

        //fold/unfold related options
        var obj = $(this);
        var $fold = '.f_' + obj.data('id');
        $($fold).slideDown('normal', "swing");
        
        $('.main_checkbox', parent).trigger('change');
    });
    
    $(".cb-disable").on('click', function () {
        var parent = $(this).parents('.switch-options');
        $('.cb-enable', parent).removeClass('selected');
        $(this).addClass('selected');
        $('.main_checkbox', parent).attr('checked', false);

        //fold/unfold related options
        var obj = $(this);
        var $fold = '.f_' + obj.data('id');
        $($fold).slideUp('normal', "swing");
        
        $('.main_checkbox', parent).trigger('change');
    });
    
    $('body').on('change', '#section-f_buildin .main_checkbox', function() {
        var is_check = $(this).is(':checked');
        
        if (!is_check) {
            var _current = $('#section-footer_mode #footer_mode').val();
            $('#section-footer_mode #footer_mode option[value="build-in"]').attr('disabled', true);
            
            if (_current === 'build-in') {
                if ($('#section-footer_mode #footer_mode option[value="builder"]').length) {
                    $('#section-footer_mode #footer_mode').val('builder').trigger('change');
                } else {
                    if ($('#section-footer_mode #footer_mode option[value="builder-e"]').length) {
                        $('#section-footer_mode #footer_mode').val('builder-e').trigger('change');
                    }
                }
            }
        } else {
            $('#section-footer_mode #footer_mode option[value="build-in"]').attr('disabled', false);
        }
    });
    
    if ($('#section-f_buildin .main_checkbox').length) {
        $('#section-f_buildin .main_checkbox').trigger('change');
    }
    
    $('body').on('click', '.of-uploaded-image', function() {
        var _wrap = $(this).parents('.controls');
        if ($(_wrap).length && $(_wrap).find('.media_upload_button').length) {
            $(_wrap).find('.media_upload_button').trigger('click');
        }
    });

    /**
     * Google Fonts
     * Dependencies 	: google.com, jquery
     * Feature added by : Smartik - http://smartik.ws/
     * Date             : 03.17.2013
     */
    function google_font_select(slctr, mainID) {
        var _selected = $(slctr).val(); //get current value - selected and saved
        var _linkclass = 'style_link_' + mainID;
        var _previewer = mainID + '_ggf_previewer';

        if (_selected) { //if var exists and isset

            $('.' + _previewer).fadeIn();

            //Check if selected is not equal with "Select a font" and execute the script.
            if (_selected !== 'none' && _selected !== 'Select a font') {

                //remove other elements crested in <head>
                $('.' + _linkclass).remove();

                //replace spaces with "+" sign
                var the_font = _selected.replace(/\s+/g, '+'),
                    font_not = ['Arial', 'Trebuchet', 'Times', 'Tahoma', 'Helvetica'];

                if(font_not.indexOf(the_font) === -1) {
                    //add reference to google font family
                    $('head').append('<link href="https://fonts.googleapis.com/css?family=' + the_font + '" rel="stylesheet" type="text/css" class="' + _linkclass + '" />');
                }

                //show in the preview box the font
                $('.' + _previewer).css('font-family', _selected + ', sans-serif');
            } else {
                //if selected is not a font remove style "font-family" at preview box
                $('.' + _previewer).css('font-family', '');
                $('.' + _previewer).fadeOut();
            }
        }

    }

    //init for each element
    $('.google_font_select').each(function () {
        var mainID = $(this).attr('id');
        google_font_select(this, mainID);
    });

    //init when value is changed
    $('.google_font_select').change(function () {
        var mainID = $(this).attr('id');
        google_font_select(this, mainID);
    });

    /**
     * Media Uploader
     * Dependencies 	 : jquery, wp media uploader
     * Feature added by : Smartik - http://smartik.ws/
     * Date 			 : 05.28.2013
     */
    function optionsframework_add_file(event, selector) {
        var upload = $(".uploaded-file"), frame;
        var $el = $(this);

        event.preventDefault();

        // If the media frame already exists, reopen it.
        if (frame) {
            frame.open();
            return;
        }

        // Create the media frame.
        frame = wp.media({
            // Set the title of the modal.
            title: $el.data('choose'),

            // Customize the submit button.
            button: {
                // Set the text of the button.
                text: $el.data('update'),
                // Tell the button not to close the modal, since we're
                // going to refresh the page when the image is selected.
                close: false
            }
        });

        // When an image is selected, run a callback.
        frame.on('select', function () {
            // Grab the selected attachment.
            var attachment = frame.state().get('selection').first();
            frame.close();
            // selector.find('.upload').val(attachment.attributes.id);
            selector.find('.upload').val(attachment.attributes.url);
            if (attachment.attributes.type == 'image') {
                selector.find('.screenshot').empty().hide().append('<img class="of-option-image" src="' + attachment.attributes.url + '" />').slideDown('fast');
            }
            selector.find('.media_upload_button').trigger('off');
            selector.find('.remove-image').show().removeClass('hide');//show "Remove" button
            selector.find('.of-background-properties').slideDown();
            optionsframework_file_bindings();
        });

        // Finally, open the modal.
        frame.open();
    }

    function optionsframework_remove_file(selector) {
        selector.find('.remove-image').hide().addClass('hide');//hide "Remove" button
        selector.find('.upload').val('');
        selector.find('.of-background-properties').hide();
        selector.find('.screenshot').slideUp();
        selector.find('.remove-file').trigger('off');
        // We don't display the upload button if .upload-notice is present
        // This means the user doesn't have the WordPress 3.5 Media Library Support
        if ($('.section-upload .upload-notice').length > 0) {
            $('.media_upload_button').remove();
        }
        optionsframework_file_bindings();
    }

    function optionsframework_file_bindings() {
        $('.remove-image, .remove-file').on('click', function () {
            optionsframework_remove_file($(this).parents('.section-upload, .section-media, .slide_body'));
        });

        $('.media_upload_button').off('click').on('click', function (event) {
            optionsframework_add_file(event, $(this).parents('.section-upload, .section-media, .slide_body'));
        });
    }

    optionsframework_file_bindings();

}); //end doc ready
