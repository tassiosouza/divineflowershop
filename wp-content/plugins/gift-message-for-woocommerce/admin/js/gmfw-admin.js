(function($) {
    'use strict';

    $(document).ready(function() {

        $("body").on("click", ".gmfw_premium_close", function() {
            $(this).parent().hide();
            return false;
        });
        $("body").on("click", ".gmfw_star_button", function() {
            if ($(this).next().is(":visible")) {
                $(this).next().hide();
            } else {
                $(".gmfw_premium_feature_note").hide();
                $(this).next().show();
            }
            return false;
        });

        $("body").on("keyup", "#gmfw_gift_message", function() {
            gmfw_counter();
        });

        $("#gmfw_gift_message").bind('paste', function(e) {
            gmfw_counter();
        });
        if ($("#gmfw_gift_message").length) {
            gmfw_counter();
        }

        $("body").on("click", "#gmfw_review_notice .notice-dismiss", function() {
            gmfw_review_action('dismiss');
        });
        $("body").on("click", "#gmfw_review_notice .gmfw_action", function() {
            var gmfw_value = $(this).attr("data");
            var $gmfw_el = $("#gmfw_review_notice");
            gmfw_review_action(gmfw_value);
            $gmfw_el.fadeTo(100, 0, function() {
                $gmfw_el.slideUp(100, function() {
                    $gmfw_el.remove();
                });
            });
            if (gmfw_value == 'ok-rate') {
                return true;
            } else {
                return false;
            }
        });

        /* <fs_premium_only> */
        $(".column-gmfw_gift_message .gmfw_copy_text").click(function(e) {
            var $gmfw_this = $(this);
            var $gmfw_success_text = $gmfw_this.attr("data-success");
            var $gmfw_btn_text = $gmfw_this.attr("data-btn");
            var $gmfw_message = $gmfw_this.parent().find(".gift_message_textarea").val();
            var $tempElement = $("<textarea>");
            $("body").append($tempElement);
            $tempElement.val($gmfw_message).select();
            document.execCommand("Copy");
            $tempElement.remove();

            $gmfw_this.html($gmfw_success_text);
            setTimeout(function() {
                $gmfw_this.html($gmfw_btn_text);
            }, 4000);
            return false;
        });

        $(".column-gmfw_gift_message").mouseenter(function() {
                $(this).addClass("active");
            })
            .mouseleave(function() {
                $(this).removeClass("active");
            });

        $("#gmfw_copy_text").click(function() {
            var $gmfw_this = $(this);
            var $gmfw_success_text = $gmfw_this.attr("data-success");
            var $gmfw_btn_text = $gmfw_this.attr("data-btn");
            $("#gmfw_order_gift_message").addClass("active");
            var $tempElement = $("<textarea>");

            $("body").append($tempElement);
            $tempElement.val($("#gmfw_gift_message").val()).select();
            document.execCommand("Copy");
            $tempElement.remove();

            $("#gmfw_copy_text").html($gmfw_success_text);
            setTimeout(function() {
                $("#gmfw_copy_text").html($gmfw_btn_text);
            }, 4000);
            return false;
        });

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
        /* </fs_premium_only> */
    });

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

    function gmfw_review_action(gmfw_value) {
        jQuery.post(
            gmfw_ajax.ajaxurl, {
                action: 'gmfw_ajax',
                gmfw_service: 'gmfw_review_action',
                gmfw_value: gmfw_value,
                gmfw_wpnonce: gmfw_nonce.nonce,
            }
        );
    }
})(jQuery);