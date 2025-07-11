<?php
/**
 * Post type nasa_block
 */
add_action('init', 'nasa_register_block');
function nasa_register_block() {
    $labels = array(
        'name' => __('Static Blocks', 'nasa-core'),
        'singular_name' => __('Static Blocks', 'nasa-core'),
        'add_new' => __('Add New Block', 'nasa-core'),
        'add_new_item' => __('Add New Block', 'nasa-core'),
        'edit_item' => __('Edit Block', 'nasa-core'),
        'new_item' => __('New Block', 'nasa-core'),
        'view_item' => __('View Block', 'nasa-core'),
        'search_items' => __('Search Blocks', 'nasa-core'),
        'not_found' => __('No Blocks found', 'nasa-core'),
        'not_found_in_trash' => __('No Blocks found in Trash', 'nasa-core'),
        'parent_item_colon' => __('Parent Block:', 'nasa-core'),
        'menu_name' => __('Static Blocks', 'nasa-core')
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        'description' => __('List Blocks', 'nasa-core'),
        'supports' => array('title', 'editor'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => false,
        'menu_position' => 7,
        'show_in_nav_menus' => false,
        'publicly_queryable' => false,
        'exclude_from_search' => false,
        'has_archive' => false,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => false,
        'menu_icon' => 'dashicons-layout'
    );
    
    register_post_type('nasa_block', $args);

    if ($options = get_option('wpb_js_content_types')) {
        $check = true;
        foreach ($options as $key => $value) {
            if ($value == 'nasa_block') {
                $check = false;
                break;
            }
        }
        if ($check) {
            $options[] = 'nasa_block';
        }
    } else {
        $options = array('page', 'nasa_block');
    }
    
    update_option('wpb_js_content_types', $options);
}

/**
 * Register Menu
 */
add_action('admin_menu', 'nasa_register_block_menu');
function nasa_register_block_menu() {
    if (defined('NASA_ADMIN_PAGE_SLUG')) {
        add_submenu_page(
            NASA_ADMIN_PAGE_SLUG,
            'Static Blocks' . (defined('NASA_WPB_ACTIVE') && NASA_WPB_ACTIVE ? ' (WPBakery)' : ''),
            'Static Blocks' . (defined('NASA_WPB_ACTIVE') && NASA_WPB_ACTIVE ? ' (WPBakery)' : ''),
            'edit_pages',
            'edit.php?post_type=nasa_block'
        );
    }
}

add_action('manage_blocks_posts_custom_column', 'nasa_manage_blocks_columns', 10, 2);
function nasa_manage_blocks_columns($column, $post_id) {
    switch ($column) {
        case 'shortcode' :
        default :
            echo (int) $post_id ? '<span style="background:#eee;font-weight:bold;"> [nasa_static_block id="' . $post_id . '"] </span>' : '';
            break;
    }
}

add_action('admin_head', 'nasa_block_scripts');
function nasa_block_scripts() {
    global $typenow;
    if ('nasa_block' == $typenow) {
        ?>
        <script>
            jQuery(document).ready(function ($) {
                <?php if (isset($_GET["post"]) && $_GET["post"]): ?>
                    var block_id = $('input#post_ID').val();
                    if ($('#original_post_status').val() === 'publish') {
                        $('#submitpost #minor-publishing').append('<div class="misc-pub-section shortcode-info"><span><i class="fa fa-code" style="font-size: 1.2em; margin-right: 5px;"></i> Shortcode: <b>[nasa_static_block id="' + block_id + '"]</b></span></div>');
                    }
                <?php endif; ?>
                if ($('#posts-filter').length) {
                    if ($('input[name="post_status"]').val() !== 'trash') {
                        $('#posts-filter table.wp-list-table thead tr').append('<td scope="col" id="shortcode" class="manage-column" style="width: 170px;"><span>Shortcode</span></td>');
                        $('#posts-filter table.wp-list-table tfoot tr').append('<td scope="col" id="shortcode" class="manage-column"><span>Shortcode</span></td>');

                        $('#posts-filter table.wp-list-table tbody tr').each(function () {
                            if ($(this).hasClass('status-publish')) {
                                var _post_id = ($(this).attr('id')).replace('post-', '');
                                $(this).append('<td data-colname="Shortcode"><b>[nasa_static_block id="' + _post_id + '"]</b></td>');
                            } else {
                                $(this).append('<td></td>');
                            }
                        });
                    }
                }
            });
        </script>
        <?php
    }
}

/**
 * Short-code Block Static
 */
add_shortcode('nasa_static_block', 'nasa_block_shortcode');
function nasa_block_shortcode($atts = array(), $content = null) {
    extract(shortcode_atts(array(
        'id' => ''
    ), $atts));
    
    $real_id = (int) $id;
    
    $content = '';
    
    if ($real_id) {
        $post = get_post($real_id);
        
        /**
         * With Multi Languages
         */
        if (function_exists('icl_object_id')) {
            $postLangID = icl_object_id($real_id, 'nasa_block', true);

            if ($postLangID && $postLangID != $real_id) {
                $postLang = get_post($postLangID);
                $post = $postLang && $postLang->post_status == 'publish' ? $postLang : $post;
                $real_id = $postLangID;
            }
        }
        
        /**
         * Output
         */
        if ($post && isset($post->post_content)) {
            $content .= nasa_get_custom_style($real_id);
            $content .= do_shortcode($post->post_content);
        }
    }
    
    return $content;
}
