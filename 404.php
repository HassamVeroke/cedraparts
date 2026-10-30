<?php
/**
 * 404 Not Found Template (Option 2 Clean Industrial Design)
 *
 * @package CedraParts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$shop_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$quote_url = cedra_get_option( 'quote_url' );
?>
<section class="cedra-page-section cedra-404">
	<div class="wrap">
		<div class="cedra-404__card">
			<span class="cedra-eyebrow"><?php esc_html_e( 'Error 404', 'cedraparts' ); ?></span>

			<h1 class="cedra-404__title"><?php esc_html_e( 'Part or page not found', 'cedraparts' ); ?></h1>

			<p class="cedra-404__desc">
				<?php esc_html_e( 'The link you followed may have moved, or you can search by OEM part number directly in our catalog below.', 'cedraparts' ); ?>
			</p>

			<form role="search" method="get" action="<?php echo esc_url( $shop_url ); ?>" class="cedra-404__form">
				<div class="cedra-searchbox">
					<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" class="cedra-searchbox__icon">
						<circle cx="11" cy="11" r="7" fill="none" stroke="#5B646F" stroke-width="1.8"></circle>
						<path d="M20 20l-4-4" stroke="#5B646F" stroke-width="1.8" stroke-linecap="round"></path>
					</svg>
					<label for="cedra-404-q" class="sr"><?php esc_html_e( 'Search products', 'cedraparts' ); ?></label>
					<input
						id="cedra-404-q"
						name="q"
						type="search"
						placeholder="<?php esc_attr_e( 'Search by part number, model or keyword', 'cedraparts' ); ?>"
						class="cedra-searchbox__input"
					>
				</div>
				<button type="submit" class="cedra-btn cedra-btn--accent cedra-btn--46"><?php esc_html_e( 'Search catalog', 'cedraparts' ); ?></button>
			</form>

			<div class="cedra-404__actions">
				<a href="<?php echo esc_url( $shop_url ); ?>" class="cedra-btn cedra-btn--dark">
					<?php esc_html_e( 'Shop all parts', 'cedraparts' ); ?>
				</a>
				<a href="<?php echo esc_url( $quote_url ); ?>" class="cedra-btn cedra-btn--outline">
					<?php esc_html_e( 'Cross-reference a part', 'cedraparts' ); ?>
				</a>
			</div>
		</div>
	</div>
</section>
<?php
get_template_part( 'template-parts/help-band' );
get_footer();
