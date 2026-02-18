(function($) {
    'use strict';

    function gmfw_counter() {
        var $gmfw_textarea = $("#gmfw_gift_message");
        var $gmfw_massage = $gmfw_textarea.val();
        var $gmfw_massage_len = $gmfw_massage.length;
        var $gmfw_counter = $('#gmfw_counter');
        var $gmfw_massage_limit = $gmfw_counter.attr("data");

        if ($gmfw_massage_len >= $gmfw_massage_limit) {
            $gmfw_massage = $gmfw_massage.substring(0, $gmfw_massage_limit);
            $gmfw_textarea.val($gmfw_massage);
        } else {
            $gmfw_counter.text($gmfw_massage_limit - $gmfw_massage_len);
        }
    };




    /* <fs_premium_only> */
    function load_gift_card_slider() {
        if ($(".gmfw-carousel").length > 0) {
            $('.gmfw-carousel').gmfwCarousel({
                loop: false,
                margin: 10,
                nav: true,
                responsive: {
                    0: {
                        items: 2
                    },
                    600: {
                        items: 2
                    },
                    1000: {
                        items: $('#gmfw_checkout_gift_section').attr("data")
                    }
                }
            });
        }
    }
    /* </fs_premium_only> */

    $(document).ready(function() {
        /* <fs_premium_only> */

        $("body").on("change", "#gmfw_occasion", function() {
            $(".gmfw_suggestion_message").hide();
            var gmfw_occasion_id = $(this).val();
            var gmfw_has_suggestion = false;
            $(".gmfw_suggestion_message").each(function() {
                var gmfw_obj = $(this);
                if (gmfw_obj.attr("data") == gmfw_occasion_id) {
                    gmfw_obj.show();
                    gmfw_has_suggestion = true;
                }
            });
            if (gmfw_has_suggestion) {
                $("#gmfw_suggestions_section").show();
            } else {
                $("#gmfw_suggestions_section").hide();
            }

        });

        $("body").on("click", "#gmfw_suggestions_btn", function() {
            if ($(this).hasClass("active")) {
                $(this).removeClass("active");
                $("#gmfw_suggestions_wrap").hide();
            } else {
                $(this).addClass("active");
                $("#gmfw_suggestions_wrap").show();
            }
            return false;
        });

        $("body").on("change", ".gmfw_giftmessage_radio", function() {
            var gmfw_message = $(this).parent().text();
            $("#gmfw_gift_message").val(gmfw_message);
            gmfw_counter();
        });

        $("body").on("click", ".gmfw_suggestion_message label", function() {
            $(this).parent().find(".gmfw_giftmessage_radio").trigger("click");
        });



        $('body').on('updated_checkout', function() {
            load_gift_card_slider();
        });

        load_gift_card_slider();

        if ($(".gmfw-add-to-cart").length > 0) {
            $("body").on("click", ".gmfw-add-to-cart", function() {
                let gmfw_product_id = $(this).attr("data");
                let gmfw_add_to_cart = $(this);
                let gmfw_remove_from_cart = $(this).parent().find('.gmfw-remove-from-cart');
                $.ajax({
                    type: "POST",
                    url: gmfw_ajax.ajaxurl,
                    data: {
                        action: 'gmfw_ajax',
                        gmfw_service: 'gmfw_add_to_cart',
                        gmfw_value: gmfw_product_id,
                        gmfw_wpnonce: gmfw_nonce.nonce,
                    },
                    success: function(response) {
                        if (response.error !== 'undefined' && response.error) {
                            return true;
                        } else if (response.status === 200) {
                            //console.log("gmfw_add_to_cart");
                            gmfw_remove_from_cart.removeClass('gmfw_hide');
                            gmfw_add_to_cart.addClass('gmfw_hide');
                            $('body').trigger('update_checkout');
                        }
                    }
                });

                return false;
            });
        }

        if ($(".gmfw-remove-from-cart").length > 0) {
            $("body").on("click", ".gmfw-remove-from-cart", function() {
                let gmfw_product_id = $(this).attr("data");
                let gmfw_remove_from_cart = $(this);
                let gmfw_add_to_cart = $(this).parent().find('.gmfw-add-to-cart');
                $.ajax({
                    type: "POST",
                    url: gmfw_ajax.ajaxurl,
                    data: {
                        action: 'gmfw_ajax',
                        gmfw_service: 'gmfw_remove_from_cart',
                        gmfw_value: gmfw_product_id,
                        gmfw_wpnonce: gmfw_nonce.nonce,
                    },
                    success: function(response) {
                        if (response.error !== 'undefined' && response.error) {
                            return true;
                        } else if (response.status === 200) {
                            //console.log("gmfw_remove_from_cart");
                            gmfw_add_to_cart.removeClass('gmfw_hide');
                            gmfw_remove_from_cart.addClass('gmfw_hide');
                            $('body').trigger('update_checkout');

                        }
                    }
                });

                return false;
            });
        }

        $("body").on("change", ".gift_message_fee #gmfw_gift_message", function() {
            $('body').trigger('update_checkout');
        });

        /* </fs_premium_only> */

        $("body").on("keyup", "#gmfw_gift_message", function() {
            gmfw_counter();
        });

        $("#gmfw_gift_message").bind('paste', function(e) {
            gmfw_counter();
        });

      
 
    });

})(jQuery);