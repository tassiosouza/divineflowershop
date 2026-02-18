<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />
	<link rel="profile" href="http://gmpg.org/xfn/11">

	<?php
	wp_head();
	?>
</head>

<body <?php body_class(); ?>>

<?php
/**
 * Flux before layout.
 *
 * @since 2.0.0
 */
do_action( 'flux_before_layout' );
?>

<div class="flux-checkout flux-checkout--modern flux-checkout--has-sidebar" data-effect="pure-effect-slide">

	<main class="flux-checkout__content">
		<?php
		while ( have_posts() ) {
			the_post();
			the_content();
		}
		?>
	</main>

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
