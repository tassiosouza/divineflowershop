<?php
/**
 * Template para página de produto usando layout Bloom.
 */
$theme_dir = get_stylesheet_directory();
$theme_uri = get_stylesheet_directory_uri();
// Carrega o HTML base do tema Bloom
$html = file_get_contents( $theme_dir . '/bloom/product-detail.html' );

// Prefix paths
$html = str_replace( 'href="assets/', 'href="' . $theme_uri . '/bloom/assets/', $html );
$html = str_replace( 'src="assets/', 'src="' . $theme_uri . '/bloom/assets/', $html );
$html = str_replace( "url('assets/", "url('" . $theme_uri . '/bloom/assets/', $html );
$html = str_replace( 'url("assets/', 'url("' . $theme_uri . '/bloom/assets/', $html );

// Obtém o produto atual do WooCommerce
global $product;

if ( ! $product ) {
    wc_get_template_part( 'content', 'single-product' );
    return;
}

// Dados principais do produto
$name        = $product->get_name();
$description = wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() );
$regular     = (float) $product->get_regular_price();
$sale        = (float) $product->get_sale_price();
$price       = $product->get_price();
$sku         = $product->get_sku() ?: 'N/A';
$tags        = wp_get_post_terms( $product->get_id(), 'product_tag', [ 'fields' => 'names' ] );
$stock_text  = $product->is_in_stock() ? 'In Stock' : 'Out of Stock';
$stock_class = $product->is_in_stock() ? 'green-tag' : 'red-tag';

// Monta bloco de preço
if ( $sale && $regular && $sale < $regular ) {
    $discount    = intval( ( $regular - $sale ) / $regular * 100 );
    $price_block = '<div class="price"><del class="h6 dark-gray">' . wc_price( $regular ) . '</del><h3>' . wc_price( $sale ) . '</h3></div><p class="red-tag">' . $discount . '% off</p>';
} else {
    $price_block = '<div class="price"><h3>' . wc_price( $price ) . '</h3></div>';
}

// Imagens
$main_image  = wp_get_attachment_url( $product->get_image_id() );
$gallery_ids = $product->get_gallery_image_ids();
$thumbs      = '';
foreach ( $gallery_ids as $id ) {
    $src    = wp_get_attachment_url( $id );
    $thumbs .= '<div class="detail-img-block"><img alt="" src="' . esc_url( $src ) . '"></div>';
}
if ( empty( $thumbs ) && $main_image ) {
    $thumbs = '<div class="detail-img-block"><img alt="" src="' . esc_url( $main_image ) . '"></div>';
}

// Monta lista de tags
$tags_html = '';
foreach ( $tags as $index => $tag_name ) {
    $tags_html .= '<span class="me-1">' . esc_html( $tag_name );
    if ( $index < count( $tags ) - 1 ) {
        $tags_html .= ', </span>';
    } else {
        $tags_html .= '</span>';
    }
}

$add_to_cart_url = wc_get_cart_url() . '?add-to-cart=' . $product->get_id();

// Realiza substituições no HTML base usando placeholders
$replacements = [
    '{{PRODUCT_NAME}}' => esc_html( $name ),
    '{{STOCK_TEXT}}'   => esc_html( $stock_text ),
    '{{STOCK_CLASS}}'  => esc_attr( $stock_class ),
    '{{SKU}}'          => esc_html( $sku ),
    '{{PRICE_BLOCK}}'  => $price_block,
    '{{DESCRIPTION}}'  => esc_html( $description ),
    '{{MAIN_IMAGE}}'   => esc_url( $main_image ),
    '{{GALLERY}}'      => $thumbs,
    '{{TAGS}}'         => $tags_html,
    '{{ADD_TO_CART_URL}}' => esc_url( $add_to_cart_url ),
];

$html = str_replace( array_keys( $replacements ), array_values( $replacements ), $html );

echo $html;
