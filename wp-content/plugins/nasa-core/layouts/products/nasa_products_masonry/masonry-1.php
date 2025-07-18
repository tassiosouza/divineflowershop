<?php
defined('ABSPATH') || exit;

$k = 1;
$class_small = 'large-2 medium-3 small-6 columns';
$class_large = 'large-4 medium-6 small-6 columns';
$custom_class = isset($custom_class) ? $custom_class : '';

$description_info = apply_filters('nasa_loop_short_description_show', false);

$array_pos = array(5, 6, 7, 8, 13, 14);

while ($loop->have_posts()) :
    $loop->the_post();
    
    global $product;
    if (empty($product) || !$product->is_visible()) :
        continue;
    endif;
    
    $class_wrap = in_array($k, $array_pos) ? $class_large : $class_small;
    $class_wrap .= $custom_class ? ' ' . $custom_class : '';
    ?>
    <div class="nasa-masonry-item padding-left-5 padding-right-5 <?php echo $class_wrap; ?>">
        <?php 
        wc_get_template(
            'content-product.php',
            array(
                'wrapper' => 'div',
                'show_in_list' => false,
                'description_info' => $description_info
            )
        );
        ?>
    </div>
<?php 
$k++;
endwhile;
wp_reset_postdata();
