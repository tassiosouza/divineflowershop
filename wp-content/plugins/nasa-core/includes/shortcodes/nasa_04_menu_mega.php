<?php
/**
 * Shortcode [nasa_mega_menu ...]
 * 
 * @param type $atts
 * @param type $content
 * @return type
 */
function nasa_sc_mega_menu($atts = array(), $content = null) {
    $dfAttr = array(
        'title' => '',
        'menu' => '',
        'el_class' => ''
    );
    extract(shortcode_atts($dfAttr, $atts));
    
    $content = '';
    if ($menu) {
        $nasa_main_menu = wp_nav_menu(array(
            'echo' => false,
            'menu' => $menu,
            'container' => false,
            'items_wrap' => '%3$s',
            'depth' => (int) apply_filters('nasa_max_depth_main_menu', 3),
            'walker' => new Nasa_Nav_Menu()
        ));
        
        $content .= '<div class="hide-for-small nasa-nav-sc-mega-menu' . ($el_class != '' ? ' ' . esc_attr($el_class) : '') . '">';
        if ($title) :
            $content .= 
            '<h5 class="section-title">' .
                esc_html($title) .
            '</h5>';
        endif;

        $content .= '<div class="nasa-menus-wrapper-reponsive" data-padding_y="20" data-padding_x="15">';
        $content .= '<div class="nav-wrapper inline-block main-menu-warpper">';
        $content .= '<ul class="header-nav">';
        $content .= $nasa_main_menu;
        $content .= '</ul>';
        $content .= '</div><!-- nav-wrapper -->';
        $content .= '</div><!-- nasa-menus-wrapper-reponsive -->';
        $content .= '</div><!-- nasa-nav-sc-menu -->';
    }
    
    return $content;
}

// **********************************************************************// 
// ! Register New Element: Mega Menu
// **********************************************************************//   
function nasa_register_mega_menu_shortcode() {
    $params = array(
        "name" => "Mega Menu",
        "base" => "nasa_mega_menu",
        'icon' => 'icon-wpb-nasatheme',
        'description' => __("Display shortcode mega menu.", 'nasa-core'),
        "category" => 'Header Builder',
        "params" => array(
            array(
                "type" => "textfield",
                "heading" => __("Title", 'nasa-core'),
                "param_name" => "title"
            ),
            array(
                'type' => 'dropdown',
                'heading' => __('Menu', 'nasa-core'),
                'param_name' => 'menu',
                "value" => nasa_get_menu_options(),
                "admin_label" => true
            ),

            array(
                "type" => "textfield",
                "heading" => __("Extra Class", 'nasa-core'),
                "param_name" => "el_class",
                "description" => __('If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'nasa-core')
            )
        )
    );

    vc_map($params);
}
