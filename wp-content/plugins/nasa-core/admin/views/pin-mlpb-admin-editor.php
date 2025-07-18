<?php
defined('ABSPATH') or die();
?>

<style>
    .composer-switch,
    #wpb_visual_composer,
    .wp-editor-expand {
        display: none !important;
    }
    
    .nasa_pin_mlpb_image_wrap {
        position: relative;
        text-align: center;
        width: 100%;
        overflow: auto;
        min-height: 70vh;
    }
    
    .nasa-wrap-relative-image {
        display: inline-block;
    }
    
    .nasa-wrap-relative-image img.nasa_pin_mlpb_image,
    .select2-container {
        display: block;
    }
    
    .select2-container.inited {
        margin-top: 10px;
    }
    
    .select2-container.inited .select2-choice .select2-arrow {
        background: none;
        border: none;
    }
    
    .easy-delete {
        z-index: 9999;
    }
    
    .easy-submit {
        display: block;
        width: 100%;
        margin: 10px auto 10px;
        cursor: pointer;
    }

    .list_title {
        display: block;
        width: 100%;
        margin: 20px auto 10px;
    }
    
    .container-wrap-nasa-pin-pb{
        text-align: left;
    }
    
    #nasa_media_manager{
        margin: 10px auto;
        width: 200px;
        cursor: pointer;
    }
    
    .nasa-media-btn {
        text-align: center;
    }

    .nasa_pin_mlpb_product_list {
        max-height: 100px;
        overflow: auto;
    }

    a.nasa_product_list_remove {
        margin: 2px 0;
        display: block;
        padding: 5px;
        text-align: start;
        text-decoration: none;
        color: inherit;
        transition: all 300ms ease;
        border: 1px solid #aaa;
        border-radius: 5px;
        position: relative;
    }

    a.nasa_product_list_remove svg {
        position: absolute;
        opacity: 0;
        visibility: hidden;
        left: 0px;
        top: 5px;
        transition: all 300ms ease;
    }

    a.nasa_product_list_remove:hover {
        padding-left: 30px;
    }

    a.nasa_product_list_remove:hover svg {
        left: 5px;
        opacity: 1;
        visibility: visible;
    }

</style>
<input type="hidden" id="nasa_pin_slug" name="nasa_pin_slug" value="<?php echo esc_attr($post->post_name); ?>" />
<script>
    var nasa_pin_mlpb = {
        'url_search': "<?php echo admin_url('admin-ajax.php?action=woocommerce_json_search_products_and_variations'); ?>",
        '_nonce': "<?php echo wp_create_nonce('search-products'); ?>"
    };
    
    function initSelect2($, _obj) {
        if (!$(_obj).hasClass('inited')) {
            
            $(_obj).select2({
                minimumInputLength: 3,
                ajax: {
                    url: nasa_pin_mlpb.url_search,
                    dataType: 'json',
                    delay: 250,
                    data: function(terms) {
                        return {
                            term: terms,
                            security: nasa_pin_mlpb._nonce
                        };
                    },
                    results: function(data) {
                        var results = [];

                        for (var id in data) {
                            results.push({
                                id: id,
                                text: data[id].replace('&ndash;', ' - ')
                            });
                        }

                        return {
                            results: results
                        };
                    }
                },
                initSelection: function(element, callback) {
                    var id = $(element).val();

                    if (id !== '') {
                        $.ajax(nasa_pin_mlpb.url_search + '&term=' + id + '&security=' + nasa_pin_mlpb._nonce, {
                            dataType: 'json'
                        }).done(function(data) {
                            callback(data);

                            if (data && typeof data[id] !== 'undefined') {
                                $(_obj).parents('.modalContext').find('.select2-container.select_product .select2-chosen').html(data[id]);
                                
                            }
                        });
                    }
                }
            });

            $(_obj).addClass('inited');
            
            $(_obj).change(function() {
                var theText = $(_obj).select2('data').text;
                var _textarea = theText;
                var modalBody = $(_obj).parents('.modalContext').find('.modal-mlpb');
                $(_obj).parents('.modalContext').find('textarea').val(_textarea);

                if ($(modalBody).hasClass('modal-mlpb')) {
                    var product_list = $(modalBody).find('.product_list_add');
                    var data_list = [];
                    var _content = $(modalBody).find('textarea[name="content"]').val();
                    var _product_id = $(modalBody).find('input[name="product_id"]').val();
                    var html ='';
                    var svg ='<svg height="20" width="20" viewBox="0 0 384 512"><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg>';
                    var mlpb = {
                        content :_content,
                        product_id :_product_id,
                    };

                    if ($(product_list).val() != '') {
                        data_list = JSON.parse($(product_list).val());
                    }

                    var exists = data_list.some(function(item) {
                        return item.product_id === mlpb.product_id || item.content === mlpb.content;
                    });

                    if (!exists && _content!= '' && _product_id != '') {
                        data_list.push(mlpb);
                        $(modalBody).find('.product_list_add').val(JSON.stringify(data_list));
                    }

                    data_list.forEach(function(item) {
                        html += '<a class="nasa_product_list_remove" href="javascript:void(0);" data-id-product="' + item.product_id + '">' + item.content + svg + '</a>';
                    });

                    $(modalBody).find('.nasa_pin_mlpb_product_list').html(html);
                }
            });
        }
    }
    
    jQuery(document).ready(function ($) {
        $('#nasa_pin_mlpb_editor .inside').append($('#wrap-nasa-pin-pb').html());
        $('#wpb_visual_composer').remove();
        $('.composer-switch').hide();

        var _instance = null;
        if (!$('.nasa_pin_mlpb_image').hasClass('no-image')) {
            _instance = $('.nasa_pin_mlpb_image').easypin({
                init: <?php echo $_init; ?>,
                markerSrc: '<?php echo NASA_CORE_PLUGIN_URL . 'assets/images/plus-marker.png'; ?>',
                editSrc: '<?php echo NASA_CORE_PLUGIN_URL . 'assets/images/edit.png'; ?>',
                deleteSrc: '<?php echo NASA_CORE_PLUGIN_URL . 'assets/images/remove.png'; ?>',
                modalWidth: 400,
                
                /**
                 * Fixed position when drop
                 */
                drop: function(x, y, element) {
                    x = x + 15;
                    y = y + 48;
                    element.attr('data-x', x);
                    element.attr('data-y', y);
                },
                        
                done: function() {
                    return true;
                }
            });
        }
        
        $('.nasa_pin_mlpb_image_wrap').on('click', '.easy-edit', function () {
            $('.nasa-wrap-relative-image .select_product').each(function() {
                initSelect2($, this);
            });
        });
        
        $('body').on('click', '#publishing-action input', function(e) {
            _instance.easypin.event("get.coordinates", function(_instance, data, params) {
                var options = JSON.parse(data);

                if (options !== null && typeof options.nasa_pin_mlpb !== 'undefined') {
                    var _key;
                    var _pins = [];
                    for(_key in options.nasa_pin_mlpb) {
                        if (_key !== 'canvas') {
                            _pins.push(options.nasa_pin_mlpb[_key]);
                        } else {
                            $('#nasa_image_width').val(options.nasa_pin_mlpb[_key].width);
                            $('#nasa_image_height').val(options.nasa_pin_mlpb[_key].height);
                        }
                    }

                    $('#nasa_options_pin').val(JSON.stringify(_pins));
                } else {
                    $('#nasa_image_width').val('');
                    $('#nasa_image_height').val('');
                    $('#nasa_options_pin').val('');
                }
            });
            
            _instance.easypin.fire("get.coordinates", {}, function(data) {
                return JSON.stringify(data);
            });
        });

        if (typeof wp !== 'undefined') {
            $('body').on('click', 'input#nasa_media_manager', function (e) {
                e.preventDefault();
                var image = wp.media({ 
                    title: 'Upload Image',
                    // mutiple: true if you want to upload multiple files at once
                    multiple: false
                }).open()
                .on('select', function(e){
                    // This will return the selected image from the Media Uploader, the result is an object
                    var uploaded_image = image.state().get('selection').first();
                    // We convert uploaded_image to a JSON object to make accessing it easier
                    // Output to the console uploaded_image
                    var imgObj = uploaded_image.toJSON();
                    
                    // Let's assign the url value to the input field
                    $('#nasa_image_url').val(imgObj.id);
                    $('.nasa_pin_mlpb_image_wrap .nasa-wrap-relative-image').html('<img class="nasa_pin_mlpb_image" src="' + imgObj.url + '" data-easypin_id="nasa_pin_mlpb" />');
                    _instance = $('.nasa_pin_mlpb_image').easypin({
                        init: '{}',
                        markerSrc: '<?php echo NASA_CORE_PLUGIN_URL . 'assets/images/plus-marker.png'; ?>',
                        editSrc: '<?php echo NASA_CORE_PLUGIN_URL . 'assets/images/edit.png'; ?>',
                        deleteSrc: '<?php echo NASA_CORE_PLUGIN_URL . 'assets/images/remove.png'; ?>',
                        modalWidth: 400,
                        /**
                         * Fixed position when drop
                         */
                        drop: function(x, y, element) {
                            x = x + 15;
                            y = y + 48;
                            element.attr('data-x', x);
                            element.attr('data-y', y);
                        },
                        
                        done: function() {
                            return true;
                        }
                    });
                });
            });
        }
    });
</script>

<script id="wrap-nasa-pin-pb" type="text/template">
<div class="container-wrap-nasa-pin-pb">
    <input id="nasa_image_url" type="hidden" name="nasa_pin_mlpb_image_url" value="<?php echo (int) $attachment_id; ?>" />
    <input id="nasa_image_width" type="hidden" name="nasa_pin_mlpb_image_width" value="<?php echo (int) $_width; ?>" />
    <input id="nasa_image_height" type="hidden" name="nasa_pin_mlpb_image_height" value="<?php echo (int) $_height; ?>" />
    <input id="nasa_options_pin" type="hidden" name="nasa_pin_mlpb_options" value="<?php echo esc_attr($_options); ?>" />
    <div class="nasa-media-btn">
        <input type="button" id="nasa_media_manager" value="Media" />
    </div>
    
    <div class="nasa_pin_mlpb_image_wrap">
        <span class="nasa-wrap-relative-image">
            <img class="nasa_pin_mlpb_image<?php echo $no_image ? ' no-image' : ''; ?>" src="<?php echo esc_url($image_src); ?>" data-easypin_id="nasa_pin_mlpb" />
        </span>
        
        <div class="easy-modal modal-mlpb" style="display:none;" modal-position="free">
            <p><?php echo esc_html__('list Title', 'nasa-core'); ?></p>
            <input class="list_title" name="list_title" type="text" value="" />
            
            <?php echo esc_html__('Find Product', 'nasa-core'); ?>
            <textarea name="content" class="hidden-tag"></textarea>
            <input class="select_product" name="product_id" type="text" value="" />
            <input class="product_list_add" name="product_list" type="hidden" value="" />

            <p><?php echo esc_html__('Product List', 'nasa-core'); ?></p>
            <div class="nasa_pin_mlpb_product_list"></div>
            
            <p><?php echo esc_html__('Display Title Position', 'nasa-core'); ?></p>
            <select name="position_show" style="display: block; width: 100%;">
                <option value="top"><?php echo esc_html__('Top of Pin', 'nasa-core'); ?></option>
                <option value="left"><?php echo esc_html__('Left of Pin', 'nasa-core'); ?></option>
                <option value="right"><?php echo esc_html__('Right of Pin', 'nasa-core'); ?></option>
                <option value="bottom"><?php echo esc_html__('Bottom of Pin', 'nasa-core'); ?></option>
            </select>
            <input type="button" value="<?php echo esc_html__('Save', 'nasa-core'); ?>" class="easy-submit" />
        </div>
    </div>
</div>
</script>
