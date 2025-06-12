<?php
/**
 * Template para página de produto usando layout Bloom.
 */
$theme_dir = get_stylesheet_directory();
$theme_uri = get_stylesheet_directory_uri();
$html = file_get_contents($theme_dir . '/bloom/product-detail.html');

// Prefix paths
$html = str_replace('href="assets/', 'href="' . $theme_uri . '/bloom/assets/', $html);
$html = str_replace('src="assets/', 'src="' . $theme_uri . '/bloom/assets/', $html);
$html = str_replace("url('assets/", "url('" . $theme_uri . '/bloom/assets/', $html);
$html = str_replace('url("assets/', 'url("' . $theme_uri . '/bloom/assets/', $html);

// Get current WooCommerce product
global $product;

if ( ! $product ) {
    wc_get_template_part( 'content', 'single-product' );
    return;
}

ob_start();
?>

<!-- Custom HTML replacing Bloom placeholders -->

<h1><?= esc_html( $product->get_name() ); ?></h1>
<p><?= wp_kses_post( $product->get_description() ); ?></p>
<p><?= $product->is_in_stock() ? 'In stock' : 'Out of stock'; ?></p>
<p><?= wc_price( $product->get_price() ); ?></p>
<img src="<?= esc_url( wp_get_attachment_url( $product->get_image_id() ) ); ?>" alt="">

<!-- Adicione aqui botões ou estrutura de add-to-cart, se quiser -->

<?php
$product_html = ob_get_clean();

// Substituir marcador em `product-detail.html`
$html = preg_replace('/<!-- PRODUCT_CONTENT_START -->.*<!-- PRODUCT_CONTENT_END -->/s', '<!-- PRODUCT_CONTENT_START -->' . $product_html . '<!-- PRODUCT_CONTENT_END -->', $html);

echo $html;
