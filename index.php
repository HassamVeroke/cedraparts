<?php
/**
 * Main Index Fallback Template
 *
 * @package CedraParts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Route WooCommerce catalog or front-page catalog directly to Option 2 Catalog View.
if ( ( function_exists( 'is_shop' ) && is_shop() ) || is_front_page() || is_home() ) {
	get_header();
	get_template_part( 'template-parts/catalog-view' );
	get_template_part( 'template-parts/help-band' );
	get_footer();
	return;
}

get_header();
?>
<section class="cedra-page-section">
	<div class="wrap">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<div class="cedra-page-head">
					<h1 class="cedra-page-title"><?php the_title(); ?></h1>
				</div>
				<div class="glass-strong cedra-page-surface cedra-prose">
					<?php the_content(); ?>
				</div>
			<?php endwhile; ?>
		<?php else : ?>
			<div class="glass-strong cedra-page-surface">
				<h1><?php esc_html_e( 'Nothing Found', 'cedraparts' ); ?></h1>
				<p><?php esc_html_e( 'Please search our parts catalog above.', 'cedraparts' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
get_template_part( 'template-parts/help-band' );
get_footer();

