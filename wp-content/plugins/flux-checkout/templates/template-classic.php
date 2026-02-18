<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />
	<link rel="profile" href="http://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<?php $sidebar_class = Iconic_Flux_Sidebar::is_sidebar_enabled() ? ' flux-checkout--has-sidebar' : ''; ?>

<body <?php body_class(); ?>>

<?php
/**
 * Flux before layout.
 *
 * @since 2.0.0
 */
do_action( 'flux_before_layout' );
?>

<div class="flux-checkout flux-checkout--classic<?php echo esc_attr( $sidebar_class ); ?>" data-effect="pure-effect-slide">

	<?php
	/**
	 * Flux before header.
	 *
	 * @since 2.0.0
	 */
	do_action( 'flux_before_header' );
	?>

	<header class="flux-checkout__header header">
		<div class="header__inner">
			<?php if ( Iconic_Flux_Helpers::get_header_text() ) { ?>
				<h1 class="header__title"><?php echo esc_html( Iconic_Flux_Helpers::get_header_text() ); ?></h1>
				<?php
			} else {
				$width = Iconic_Flux_Helpers::get_logo_width();
				?>
				<img class="header__image" src="<?php echo esc_url( Iconic_Flux_Helpers::get_logo_image() ); ?>" <?php echo ! empty( $width ) ? 'width="' . esc_attr( $width ) . 'px"' : ''; ?> />
			<?php } ?>

			<?php if ( is_wc_endpoint_url( 'order-pay' ) ) { ?>
				<a class="header__link" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><?php esc_html_e( 'Back to account', 'flux-checkout' ); ?></a>
			<?php } elseif ( Iconic_Flux_Sidebar::is_sidebar_enabled() ) { ?>
				<a class="header__link" href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"><?php esc_html_e( 'Back to shop', 'flux-checkout' ); ?></a>
			<?php } else { ?>
				<a class="header__link" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'Back to cart', 'flux-checkout' ); ?></a>
			<?php } ?>
		</div>
	</header>

	<?php Iconic_Flux_Steps::render_stepper(); ?>

	<?php if ( Iconic_Flux_Sidebar::is_sidebar_enabled() ) { ?>
	<button class="flux-checkout__sidebar-header">
		<div class="flux-checkout__sidebar-header-inner">
			<span class="flux-checkout__sidebar-header-link">
				<span class="flux-checkout__sidebar-header-link--show"><?php esc_html_e( 'Show order summary', 'flux-checkout' ); ?></span>
				<span class="flux-checkout__sidebar-header-link--hide"><?php esc_html_e( 'Hide order summary', 'flux-checkout' ); ?></span>
			</span>
			<span class="flux-checkout__sidebar-header-total"><?php wc_cart_totals_order_total_html(); ?></span>
		</div>
	</button>
	<?php } ?>

	<?php
	/**
	 * Flux after header.
	 *
	 * @since 2.0.0
	 */
	do_action( 'flux_after_header' );
	?>

	<main class="flux-checkout__content">
		<?php
		while ( have_posts() ) {
			the_post();
			the_content();
		}
		?>
	</main> <!-- slide out -->

	<?php
	/**
	 * Flux after content.
	 *
	 * @since 2.0.0
	 */
	do_action( 'flux_after_content' );
	?>
	<div class="flux-checkout__spinner"><img src="<?php echo bloginfo( 'wpurl' ); ?>/wp-includes/images/spinner-2x.gif"/></div>
</div>

<?php
/**
 * Flux after layout.
 *
 * @since 2.0.0
 */
do_action( 'flux_after_layout' );
?>

<?php wp_footer(); ?>
</body>
</html>
