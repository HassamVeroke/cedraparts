<?php
/**
 * Option 2 Frosted-Glass Catalog View (Hero, Search, Distributor Marquee, Filter Rail, & Product Grid)
 *
 * @package CedraParts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shop_url          = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$hero_eyebrow      = cedra_get_option( 'hero_eyebrow' );
$hero_line1        = cedra_get_option( 'hero_title_line1' );
$hero_serif        = cedra_get_option( 'hero_title_serif' );
$hero_ring         = cedra_get_option( 'hero_badge_ring' );
$hero_desc         = cedra_get_option( 'hero_description' );
$distributor_label = cedra_get_option( 'distributor_label' );
$logos             = cedra_get_distributor_logos();

$state          = cedra_get_active_catalog_state();
$categories     = cedra_get_catalog_categories();
$brands         = cedra_get_catalog_brands();
$total_all      = cedra_get_total_products_count();
$per_page       = cedra_catalog_per_page();
$catalog_query  = cedra_query_catalog_products( $state );
$found_posts    = (int) $catalog_query->found_posts;
$max_pages      = max( 1, (int) $catalog_query->max_num_pages );
$current_page   = min( $max_pages, max( 1, (int) $state['page'] ) );
$shown_count    = min( $found_posts, ( ( $current_page - 1 ) * $per_page ) + (int) $catalog_query->post_count );
$progress_pct   = $found_posts > 0 ? min( 100, max( 4, round( ( $shown_count / $found_posts ) * 100, 2 ) ) ) : 0;
$heading_title  = cedra_get_catalog_heading_title( $state );
$has_filters    = ( '' !== $state['cat'] || '' !== $state['brand'] || '' !== $state['q'] );
$page_pill_text = sprintf(
	'PAGE %s / %s',
	str_pad( (string) $current_page, 2, '0', STR_PAD_LEFT ),
	str_pad( (string) $max_pages, 2, '0', STR_PAD_LEFT )
);
?>

<!-- Hero -->
<section class="cedra-hero js-hero-section">
	<div class="cedra-spotlight js-hero-spotlight" aria-hidden="true"></div>

	<div class="wrap hero cedra-hero__grid">
		<div>
			<div class="glass cedra-eyebrow-pill">
				<span class="cedra-pulse-dot" aria-hidden="true"></span>
				<?php echo esc_html( $hero_eyebrow ); ?>
			</div>
			<h1 class="h1 cedra-hero__h1">
				<?php echo esc_html( $hero_line1 ); ?><br>
				<span class="cedra-hero__serif"><?php echo esc_html( $hero_serif ); ?></span>
			</h1>
		</div>

		<div class="cedra-hero__aside">
			<a
				href="#catalog"
				class="hide-m badge glass cedra-seal"
				aria-label="<?php esc_attr_e( 'Power Gen Exclusive, Beamsville, Ontario — scroll to products', 'cedraparts' ); ?>"
			>
				<svg class="spin cedra-seal__ring" width="136" height="136" viewBox="0 0 120 120" aria-hidden="true">
					<defs>
						<path id="cedraRing" d="M60 60m-45 0a45 45 0 1 1 90 0a45 45 0 1 1-90 0"></path>
					</defs>
					<text class="cedra-seal__text" fill="#0B1C2C">
						<textPath href="#cedraRing"><?php echo esc_html( $hero_ring ); ?></textPath>
					</text>
				</svg>
				<svg class="cedra-seal__center" width="136" height="136" viewBox="0 0 120 120" aria-hidden="true">
					<circle cx="60" cy="60" r="22" fill="var(--cedra-accent, #7024AB)"></circle>
					<path d="M53 49l14 11-14 11" fill="none" stroke="#FFFFFF" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" transform="rotate(90 60 60)"></path>
				</svg>
			</a>

			<p class="cedra-hero__desc"><?php echo esc_html( $hero_desc ); ?></p>
		</div>
	</div>

	<!-- Search + Distributor Marquee -->
	<div class="wrap cedra-search-wrap">
		<form role="search" method="get" action="<?php echo esc_url( $shop_url ); ?>" class="glass-strong cedra-search js-catalog-search-form">
			<svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" class="cedra-search__icon">
				<circle cx="11" cy="11" r="7" fill="none" stroke="#4C5560" stroke-width="1.8"></circle>
				<path d="M20 20l-4-4" stroke="#4C5560" stroke-width="1.8" stroke-linecap="round"></path>
			</svg>

			<label for="cedra-q" class="sr"><?php esc_html_e( 'Search products', 'cedraparts' ); ?></label>
			<input
				id="cedra-q"
				name="q"
				type="search"
				value="<?php echo esc_attr( $state['q'] ); ?>"
				placeholder="<?php esc_attr_e( 'Search by part number, model or keyword', 'cedraparts' ); ?>"
				class="cedra-search__input js-catalog-q"
				autocomplete="off"
			>

			<?php if ( ! empty( $brands ) ) : ?>
				<label for="cedra-brand" class="sr"><?php esc_html_e( 'Brand', 'cedraparts' ); ?></label>
				<select id="cedra-brand" name="brand" class="cedra-search__brand js-catalog-brand">
					<option value="all"><?php esc_html_e( 'All brands', 'cedraparts' ); ?></option>
					<?php foreach ( $brands as $b ) : ?>
						<option value="<?php echo esc_attr( $b['slug'] ); ?>" <?php selected( $state['brand'], $b['slug'] ); ?>>
							<?php echo esc_html( sprintf( '%s (%s)', $b['name'], number_format_i18n( $b['count'] ) ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>

			<button type="submit" class="cedra-search__btn">
				<?php esc_html_e( 'Search', 'cedraparts' ); ?>
			</button>
		</form>

		<div class="cedra-marquee-row">
			<span class="cedra-marquee-row__label"><?php echo esc_html( $distributor_label ); ?></span>
			<div class="marq cedra-marquee">
				<div class="marq-track">
					<?php foreach ( $logos as $logo ) : ?>
						<span class="glass cedra-logo-chip">
							<img
								src="<?php echo esc_url( $logo['src'] ); ?>"
								alt="<?php echo esc_attr( $logo['alt'] ); ?>"
								style="height: <?php echo esc_attr( (string) $logo['h'] ); ?>px"
								loading="lazy"
							>
						</span>
					<?php endforeach; ?>
					<?php foreach ( $logos as $logo ) : ?>
						<span class="glass cedra-logo-chip" aria-hidden="true">
							<img
								src="<?php echo esc_url( $logo['src'] ); ?>"
								alt=""
								style="height: <?php echo esc_attr( (string) $logo['h'] ); ?>px"
								loading="lazy"
							>
						</span>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Filter rail (sticky frosted glass) -->
<div class="cedra-rail-sticky">
	<div class="wrap">
		<div class="glass cedra-rail-bar">
			<div class="rail cedra-rail" role="toolbar" aria-label="<?php esc_attr_e( 'Product category', 'cedraparts' ); ?>">
				<?php $all_selected = ( '' === $state['cat'] || 'all' === $state['cat'] ); ?>
				<button
					type="button"
					class="cedra-cat-pill js-catalog-cat<?php echo $all_selected ? ' is-selected' : ' pill'; ?>"
					data-cat="all"
					aria-pressed="<?php echo $all_selected ? 'true' : 'false'; ?>"
				>
					<span><?php esc_html_e( 'All parts', 'cedraparts' ); ?></span>
					<span class="cedra-cat-pill__count"><?php echo esc_html( number_format_i18n( $total_all ) ); ?></span>
				</button>

				<?php foreach ( $categories as $cat_item ) : ?>
					<?php $is_sel = ( $state['cat'] === $cat_item['slug'] ); ?>
					<button
						type="button"
						class="cedra-cat-pill js-catalog-cat<?php echo $is_sel ? ' is-selected' : ' pill'; ?>"
						data-cat="<?php echo esc_attr( $cat_item['slug'] ); ?>"
						aria-pressed="<?php echo $is_sel ? 'true' : 'false'; ?>"
					>
						<span><?php echo esc_html( $cat_item['name'] ); ?></span>
						<span class="cedra-cat-pill__count"><?php echo esc_html( number_format_i18n( $cat_item['count'] ) ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>

			<label for="cedra-sort" class="sr"><?php esc_html_e( 'Sort', 'cedraparts' ); ?></label>
			<select id="cedra-sort" class="hide-m cedra-sort-select js-catalog-sort">
				<option value="featured" <?php selected( $state['sort'], 'featured' ); ?>><?php esc_html_e( 'Featured', 'cedraparts' ); ?></option>
				<option value="price" <?php selected( $state['sort'], 'price' ); ?>><?php esc_html_e( 'Price: low to high', 'cedraparts' ); ?></option>
				<option value="price-desc" <?php selected( $state['sort'], 'price-desc' ); ?>><?php esc_html_e( 'Price: high to low', 'cedraparts' ); ?></option>
				<option value="title" <?php selected( $state['sort'], 'title' ); ?>><?php esc_html_e( 'Name: A to Z', 'cedraparts' ); ?></option>
			</select>
		</div>
	</div>
</div>

<!-- Product Catalog Grid -->
<section
	id="catalog"
	class="cedra-catalog js-catalog-root"
	data-cat="<?php echo esc_attr( '' !== $state['cat'] ? $state['cat'] : 'all' ); ?>"
	data-brand="<?php echo esc_attr( '' !== $state['brand'] ? $state['brand'] : 'all' ); ?>"
	data-q="<?php echo esc_attr( $state['q'] ); ?>"
	data-sort="<?php echo esc_attr( $state['sort'] ); ?>"
	data-page="<?php echo esc_attr( (string) $current_page ); ?>"
	data-max-pages="<?php echo esc_attr( (string) $max_pages ); ?>"
>
	<div class="wrap">
		<div class="cedra-catalog__header">
			<h2 class="cedra-catalog__title">
				<span class="js-catalog-title"><?php echo esc_html( $heading_title ); ?></span>
				<span class="cedra-catalog__count">
					— <span class="js-catalog-count"><?php echo esc_html( number_format_i18n( $found_posts ) ); ?></span> <?php esc_html_e( 'parts', 'cedraparts' ); ?>
				</span>
			</h2>

			<div class="cedra-catalog__meta-right">
				<button
					type="button"
					class="glass pill cedra-clear-pill js-cedra-reset-filters"
					<?php echo $has_filters ? '' : 'hidden'; ?>
				>
					<?php esc_html_e( 'Clear filters', 'cedraparts' ); ?> <span aria-hidden="true">×</span>
				</button>
				<span class="glass cedra-page-pill js-catalog-page-pill"><?php echo esc_html( $page_pill_text ); ?></span>
			</div>
		</div>

		<div class="cedra-grid js-catalog-grid" aria-live="polite">
			<?php
			if ( $catalog_query->have_posts() ) {
				$idx = ( ( $current_page - 1 ) * $per_page ) + 1;
				while ( $catalog_query->have_posts() ) {
					$catalog_query->the_post();
					$product = wc_get_product( get_the_ID() );
					if ( $product ) {
						cedra_render_product_card( $product, $idx );
						++$idx;
					}
				}
				wp_reset_postdata();
			} else {
				echo cedra_get_empty_catalog_html( $heading_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</div>

		<div class="cedra-loadmore js-catalog-footer" <?php echo ( 0 === $found_posts ) ? 'hidden' : ''; ?>>
			<span class="cedra-loadmore__status">
				<?php esc_html_e( 'Showing', 'cedraparts' ); ?>
				<span class="js-catalog-shown"><?php echo esc_html( number_format_i18n( $shown_count ) ); ?></span>
				<?php esc_html_e( 'of', 'cedraparts' ); ?>
				<span class="js-catalog-total"><?php echo esc_html( number_format_i18n( $found_posts ) ); ?></span>
				<?php esc_html_e( 'parts', 'cedraparts' ); ?>
			</span>

			<div class="cedra-progress" aria-hidden="true">
				<div class="cedra-progress__bar js-catalog-progress" style="width: <?php echo esc_attr( (string) $progress_pct ); ?>%"></div>
			</div>

			<button
				type="button"
				class="glass-strong cedra-loadmore__btn js-catalog-loadmore"
				<?php echo ( $current_page >= $max_pages ) ? 'hidden' : ''; ?>
			>
				<?php esc_html_e( 'Load more parts', 'cedraparts' ); ?>
			</button>

			<noscript>
				<?php if ( $current_page < $max_pages ) : ?>
					<a href="<?php echo esc_url( add_query_arg( 'cpage', $current_page + 1 ) ); ?>" class="glass-strong cedra-loadmore__btn">
						<?php esc_html_e( 'Next page', 'cedraparts' ); ?>
					</a>
				<?php endif; ?>
			</noscript>
		</div>
	</div>
</section>
