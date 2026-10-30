<?php
/**
 * 404 Not Found Template (Option 2 Frosted-Glass Design)
 *
 * @package CedraParts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$shop_url    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$contact_url = cedra_get_option( 'contact_url' );
?>
<section class="cedra-page-section cedra-404">
	<div class="wrap">
		<div class="glass-strong cedra-404__card">
			<div class="glass cedra-eyebrow-pill">
				<span class="cedra-pulse-dot" aria-hidden="true"></span>
				<?php esc_html_e( 'ERROR 404', 'cedraparts' ); ?>
			</div>

			<h1 class="cedra-404__title">
				<?php esc_html_e( 'Part or page', 'cedraparts' ); ?>
				<span class="cedra-serif-accent"><?php esc_html_e( 'not found.', 'cedraparts' ); ?></span>
			</h1>

			<p class="cedra-404__desc">
				<?php esc_html_e( 'The link you followed may have moved, or the part number can be cross-referenced directly in our live catalog.', 'cedraparts' ); ?>
			</p>

			<form role="search" method="get" action="<?php echo esc_url( $shop_url ); ?>" class="glass cedra-search cedra-404__search">
				<svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" class="cedra-search__icon">
					<circle cx="11" cy="11" r="7" fill="none" stroke="#4C5560" stroke-width="1.8"></circle>
					<path d="M20 20l-4-4" stroke="#4C5560" stroke-width="1.8" stroke-linecap="round"></path>
				</svg>
				<label for="cedra-404-q" class="sr"><?php esc_html_e( 'Search products', 'cedraparts' ); ?></label>
				<input
					id="cedra-404-q"
					name="q"
					type="search"
					placeholder="<?php esc_attr_e( 'Search by part number, model or keyword', 'cedraparts' ); ?>"
					class="cedra-search__input"
				>
				<button type="submit" class="cedra-search__btn"><?php esc_html_e( 'Search Catalog', 'cedraparts' ); ?></button>
			</form>

			<div class="cedra-404__actions">
				<a href="<?php echo esc_url( $shop_url ); ?>" class="cedra-btn cedra-btn--dark">
					<?php esc_html_e( 'Return to Product Catalog', 'cedraparts' ); ?>
				</a>
				<a href="<?php echo esc_url( $contact_url ); ?>" class="glass cedra-btn cedra-btn--glass">
					<?php esc_html_e( 'Send a part number', 'cedraparts' ); ?> <span aria-hidden="true">↗</span>
				</a>
			</div>
		</div>
	</div>
</section>
<?php
get_template_part( 'template-parts/help-band' );
get_footer();

