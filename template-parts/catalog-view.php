<?php
/**
 * Option 2 Landing & Catalog View:
 * 1) 4-Slide Hero Carousel (Featured, OEM Cross-Reference, Volume Pricing, Authorized Brands)
 * 2) Shop by Categories / Brands Tabbed Visual Tile Grid
 * 3) Live Search Toolbar, Active Filter Chips, Multi-Facet Sticky Sidebar & Product Grid
 *
 * @package CedraParts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shop_url       = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$quote_url      = cedra_get_option( 'quote_url' );
$total_products = cedra_get_total_products_count();
$categories     = cedra_get_catalog_categories();
$all_brands     = cedra_get_all_brand_entries();
$dist_logos     = cedra_get_distributor_logos();
$price_tiers    = cedra_get_price_tiers();
$state          = cedra_get_active_catalog_state();
$query          = cedra_query_catalog_products( $state );
$found_posts    = (int) $query->found_posts;
$max_pages      = max( 1, (int) $query->max_num_pages );
$chips          = cedra_get_active_filter_chips( $state );
$active_count   = count( $chips );

// Representative image for Tile #1 ("Parts Catalog" / All parts).
$tile_img_cache = get_transient( 'cedra_cat_tile_imgs_v3' );
$all_tile_img   = ( is_array( $tile_img_cache ) && ! empty( $tile_img_cache['__all__'] ) )
	? $tile_img_cache['__all__']
	: ( CEDRA_THEME_URI . '/assets/images/hero-dse7320.webp' );

// Initial active tab in #shop ("brand" if visiting a brand archive/query with no category filter, else "cat").
$initial_tab = ( ! empty( $state['brands'] ) && empty( $state['cats'] ) ) ? 'brand' : 'cat';

// Resolve live product links for hero spotlight cards when matching SKUs exist in WooCommerce.
$hero_8610_id  = function_exists( 'wc_get_product_id_by_sku' ) ? wc_get_product_id_by_sku( '8610' ) : 0;
$hero_8610_url = $hero_8610_id ? get_permalink( $hero_8610_id ) : '#results';
$hero_9476_id  = function_exists( 'wc_get_product_id_by_sku' ) ? wc_get_product_id_by_sku( 'DSE9476-01' ) : 0;
$hero_9476_url = $hero_9476_id ? get_permalink( $hero_9476_id ) : '#results';
$hero_7320_id  = function_exists( 'wc_get_product_id_by_sku' ) ? wc_get_product_id_by_sku( 'DESE7320' ) : 0;
$hero_7320_url = $hero_7320_id ? get_permalink( $hero_7320_id ) : '#results';
$hero_a109_id  = function_exists( 'wc_get_product_id_by_sku' ) ? wc_get_product_id_by_sku( 'A109-01' ) : 0;
$hero_a109_url = $hero_a109_id ? get_permalink( $hero_a109_id ) : '#results';

$img_base = CEDRA_THEME_URI . '/assets/images/';
?>

<!-- Hero carousel -->
<section id="top" class="cedra-hero" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Featured', 'cedraparts' ); ?>">
	<div class="wrap">
		<div class="cedra-carousel js-hero-carousel" data-dark="true">
			<div class="track js-carousel-track">

				<!-- Slide 1 -->
				<div class="slide slide--dark" role="group" aria-roledescription="slide" aria-label="<?php esc_attr_e( '1 of 4', 'cedraparts' ); ?>">
					<div class="slide__copy">
						<span class="slide__eyebrow slide__eyebrow--dark"><?php esc_html_e( 'Power Gen Exclusive', 'cedraparts' ); ?></span>
						<h1 class="h-slide"><?php esc_html_e( 'Power gen parts & components', 'cedraparts' ); ?></h1>
						<p class="slide__desc slide__desc--dark">
							<?php esc_html_e( 'Aftermarket parts for all major genset engine brands — spare parts, filters, consumables, PM kits and electrical.', 'cedraparts' ); ?>
						</p>
						<div class="slide__actions">
							<a href="#results" class="cedra-btn cedra-btn--white cedra-btn--46"><?php esc_html_e( 'Shop all parts', 'cedraparts' ); ?></a>
							<a href="<?php echo esc_url( $quote_url ); ?>" class="cedra-btn cedra-btn--ghost-light cedra-btn--46"><?php esc_html_e( 'Get a Quote', 'cedraparts' ); ?></a>
						</div>
					</div>
					<a href="<?php echo esc_url( $hero_8610_url ); ?>" class="slide__spotlight hide-m" aria-label="<?php esc_attr_e( 'DSE8610 MKII load share controller', 'cedraparts' ); ?>">
						<img src="<?php echo esc_url( $img_base . 'hero-dse8610.webp' ); ?>" alt="<?php esc_attr_e( 'DSE8610 MKII load share controller', 'cedraparts' ); ?>" class="slide__spotlight-img">
						<span class="slide__spotlight-caption"><?php esc_html_e( 'DSE8610 MKII · from $1,455.50', 'cedraparts' ); ?></span>
					</a>
				</div>

				<!-- Slide 2 -->
				<div class="slide slide--lavender" role="group" aria-roledescription="slide" aria-label="<?php esc_attr_e( '2 of 4', 'cedraparts' ); ?>">
					<div class="slide__copy">
						<span class="slide__eyebrow"><?php esc_html_e( 'Part cross-reference', 'cedraparts' ); ?></span>
						<h2 class="h-slide"><?php esc_html_e( "Can't find your part?", 'cedraparts' ); ?></h2>
						<p class="slide__desc">
							<?php esc_html_e( 'Our technical team can cross-reference any part number across all major genset brands.', 'cedraparts' ); ?>
						</p>
						<form class="slide__xref-form js-xref-form" action="<?php echo esc_url( $quote_url ); ?>" method="get">
							<label for="xref" class="sr"><?php esc_html_e( 'OEM part number', 'cedraparts' ); ?></label>
							<input id="xref" name="part" type="text" placeholder="<?php esc_attr_e( 'OEM part number', 'cedraparts' ); ?>" class="slide__xref-input js-xref-input">
							<button type="submit" class="cedra-btn cedra-btn--accent cedra-btn--46 slide__xref-btn"><?php esc_html_e( 'Cross-reference', 'cedraparts' ); ?></button>
						</form>
					</div>
					<ol class="slide__steps hide-m">
						<li><span class="slide__step-num">01</span><span><?php esc_html_e( 'Send us the OEM part number', 'cedraparts' ); ?></span></li>
						<li><span class="slide__step-num">02</span><span><?php esc_html_e( 'We match it across major genset brands', 'cedraparts' ); ?></span></li>
						<li><span class="slide__step-num">03</span><span><?php esc_html_e( 'You get a quote for the right part', 'cedraparts' ); ?></span></li>
					</ol>
				</div>

				<!-- Slide 3 -->
				<div class="slide slide--neutral" role="group" aria-roledescription="slide" aria-label="<?php esc_attr_e( '3 of 4', 'cedraparts' ); ?>">
					<div class="slide__copy">
						<span class="slide__eyebrow"><?php esc_html_e( 'Volume pricing', 'cedraparts' ); ?></span>
						<h2 class="h-slide"><?php esc_html_e( 'Custom PM kits & bulk orders', 'cedraparts' ); ?></h2>
						<p class="slide__desc">
							<?php esc_html_e( 'Volume pricing available for service companies and facility managers.', 'cedraparts' ); ?>
						</p>
						<div class="slide__actions">
							<a href="<?php echo esc_url( $quote_url ); ?>" class="cedra-btn cedra-btn--dark cedra-btn--46"><?php esc_html_e( 'Request volume pricing', 'cedraparts' ); ?></a>
						</div>
					</div>
					<div class="slide__trio hide-m">
						<a href="<?php echo esc_url( $hero_9476_url ); ?>" class="slide__trio-card">
							<img src="<?php echo esc_url( $img_base . 'hero-dse9476.webp' ); ?>" alt="<?php esc_attr_e( 'DSE9476-01 battery charger', 'cedraparts' ); ?>">
						</a>
						<a href="<?php echo esc_url( $hero_7320_url ); ?>" class="slide__trio-card">
							<img src="<?php echo esc_url( $img_base . 'hero-dse7320.webp' ); ?>" alt="<?php esc_attr_e( 'DSE7320 MKII controller', 'cedraparts' ); ?>">
						</a>
						<a href="<?php echo esc_url( $hero_a109_url ); ?>" class="slide__trio-card">
							<img src="<?php echo esc_url( $img_base . 'hero-dsea109.webp' ); ?>" alt="<?php esc_attr_e( 'DSE A109 voltage regulator', 'cedraparts' ); ?>">
						</a>
					</div>
				</div>

				<!-- Slide 4 -->
				<div class="slide slide--bordered" role="group" aria-roledescription="slide" aria-label="<?php esc_attr_e( '4 of 4', 'cedraparts' ); ?>">
					<div class="slide__copy slide__copy--narrow">
						<span class="slide__eyebrow"><?php esc_html_e( 'Authorized Canadian distributor', 'cedraparts' ); ?></span>
						<h2 class="h-slide"><?php esc_html_e( 'The brands we distribute', 'cedraparts' ); ?></h2>
						<div class="slide__actions">
							<a href="#shop" class="cedra-btn cedra-btn--dark cedra-btn--46 js-show-brands"><?php esc_html_e( 'Shop by brand', 'cedraparts' ); ?></a>
						</div>
					</div>
					<div class="slide__logos hide-m">
						<?php foreach ( $dist_logos as $logo ) : ?>
							<a
								href="<?php echo esc_url( add_query_arg( 'brands', $logo['slug'], $shop_url ) . '#results' ); ?>"
								class="slide__logo-tile js-pick-brand"
								data-brand="<?php echo esc_attr( $logo['slug'] ); ?>"
								aria-label="<?php echo esc_attr( $logo['name'] ); ?>"
							>
								<img
									src="<?php echo esc_url( $logo['src'] ); ?>"
									alt="<?php echo esc_attr( $logo['alt'] ); ?>"
									style="height: <?php echo esc_attr( (string) $logo['h'] ); ?>px;"
								>
							</a>
						<?php endforeach; ?>
					</div>
				</div>

			</div>

			<!-- Carousel controls -->
			<div class="cedra-carousel__ctrls">
				<div class="cedra-carousel__dots">
					<?php for ( $i = 0; $i < 4; $i++ ) : ?>
						<button
							type="button"
							class="cedra-carousel__dot js-carousel-dot<?php echo 0 === $i ? ' is-active' : ''; ?>"
							data-slide="<?php echo esc_attr( (string) $i ); ?>"
							aria-label="<?php echo esc_attr( sprintf( __( 'Go to slide %d', 'cedraparts' ), $i + 1 ) ); ?>"
							aria-current="<?php echo 0 === $i ? 'true' : 'false'; ?>"
						>
							<span></span>
						</button>
					<?php endfor; ?>
					<span class="cedra-carousel__counter js-carousel-counter" aria-live="polite">01 / 04</span>
				</div>
				<div class="cedra-carousel__arrows">
					<button type="button" class="cedra-carousel__arrow js-carousel-prev" aria-label="<?php esc_attr_e( 'Previous slide', 'cedraparts' ); ?>">
						<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
							<path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
						</svg>
					</button>
					<button type="button" class="cedra-carousel__arrow js-carousel-next" aria-label="<?php esc_attr_e( 'Next slide', 'cedraparts' ); ?>">
						<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
							<path d="M9 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
						</svg>
					</button>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Shop by category / brand -->
<section id="shop" class="cedra-shop-section">
	<div class="wrap">
		<div class="cedra-shop-head">
			<div class="cedra-shop-head__left">
				<h2 class="cedra-shop-title"><?php esc_html_e( 'Shop', 'cedraparts' ); ?></h2>
				<div role="tablist" aria-label="<?php esc_attr_e( 'Browse by', 'cedraparts' ); ?>" class="cedra-tabs">
					<button
						type="button"
						id="tab-cat"
						role="tab"
						aria-selected="<?php echo 'cat' === $initial_tab ? 'true' : 'false'; ?>"
						aria-controls="panel-cat"
						class="cedra-tab js-shop-tab<?php echo 'cat' === $initial_tab ? ' is-active' : ''; ?>"
						data-tab="cat"
					>
						<?php esc_html_e( 'Categories', 'cedraparts' ); ?>
						<span class="cedra-tab__count"><?php echo esc_html( (string) ( 1 + count( $categories ) ) ); ?></span>
					</button>
					<button
						type="button"
						id="tab-brand"
						role="tab"
						aria-selected="<?php echo 'brand' === $initial_tab ? 'true' : 'false'; ?>"
						aria-controls="panel-brand"
						class="cedra-tab js-shop-tab<?php echo 'brand' === $initial_tab ? ' is-active' : ''; ?>"
						data-tab="brand"
					>
						<?php esc_html_e( 'Brands', 'cedraparts' ); ?>
						<span class="cedra-tab__count"><?php echo esc_html( (string) count( $all_brands ) ); ?></span>
					</button>
				</div>
			</div>

			<a href="#results" class="cedra-view-all js-pick-cat" data-cat="all">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: total formatted parts count */
						__( 'View all %s parts', 'cedraparts' ),
						number_format_i18n( $total_products )
					)
				);
				?>
				<span aria-hidden="true">→</span>
			</a>
		</div>

		<!-- Categories Tab Panel -->
		<div
			id="panel-cat"
			role="tabpanel"
			aria-labelledby="tab-cat"
			class="cedra-tiles js-shop-panel"
			data-panel="cat"
			<?php echo 'cat' !== $initial_tab ? 'hidden' : ''; ?>
		>
			<!-- Tile 1: Parts Catalog (All Parts) -->
			<a href="#results" class="tile js-pick-cat" data-cat="all">
				<div class="tile__stage tile__stage--cat">
					<img src="<?php echo esc_url( $all_tile_img ); ?>" alt="" class="tile__img tile__img--cat" loading="lazy">
				</div>
				<div class="tile__body">
					<span class="tile__label"><?php esc_html_e( 'Parts Catalog', 'cedraparts' ); ?></span>
					<span class="tile__meta">
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: formatted count */
								__( '%s parts', 'cedraparts' ),
								number_format_i18n( $total_products )
							)
						);
						?>
					</span>
				</div>
			</a>

			<?php foreach ( $categories as $cat ) : ?>
				<a
					href="<?php echo esc_url( add_query_arg( 'cats', $cat['slug'], $shop_url ) . '#results' ); ?>"
					class="tile js-pick-cat"
					data-cat="<?php echo esc_attr( $cat['slug'] ); ?>"
				>
					<div class="tile__stage tile__stage--cat">
						<?php if ( '' !== $cat['img'] ) : ?>
							<img src="<?php echo esc_url( $cat['img'] ); ?>" alt="" class="tile__img tile__img--cat" loading="lazy">
						<?php else : ?>
							<div class="tile__svg-wrap">
								<?php echo cedra_get_technical_svg( $cat['kind'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						<?php endif; ?>
					</div>
					<div class="tile__body">
						<span class="tile__label"><?php echo esc_html( $cat['name'] ); ?></span>
						<span class="tile__meta">
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: formatted count */
									__( '%s parts', 'cedraparts' ),
									number_format_i18n( $cat['count'] )
								)
							);
							?>
						</span>
					</div>
				</a>
			<?php endforeach; ?>
		</div>

		<!-- Brands Tab Panel -->
		<div
			id="panel-brand"
			role="tabpanel"
			aria-labelledby="tab-brand"
			class="cedra-tiles js-shop-panel"
			data-panel="brand"
			<?php echo 'brand' !== $initial_tab ? 'hidden' : ''; ?>
		>
			<?php foreach ( $all_brands as $brand ) : ?>
				<?php $max_logo_h = round( ( $brand['h'] ? $brand['h'] : 30 ) * 1.4 ); ?>
				<a
					href="<?php echo esc_url( add_query_arg( 'brands', $brand['slug'], $shop_url ) . '#results' ); ?>"
					class="tile js-pick-brand"
					data-brand="<?php echo esc_attr( $brand['slug'] ); ?>"
				>
					<div class="tile__stage tile__stage--brand">
						<?php if ( '' !== $brand['src'] ) : ?>
							<img
								src="<?php echo esc_url( $brand['src'] ); ?>"
								alt=""
								class="tile__img tile__img--brand"
								style="max-height: <?php echo esc_attr( (string) $max_logo_h ); ?>px;"
								loading="lazy"
							>
						<?php else : ?>
							<span class="tile__wordmark"><?php echo esc_html( $brand['name'] ); ?></span>
						<?php endif; ?>
					</div>
					<div class="tile__body">
						<span class="tile__label"><?php echo esc_html( $brand['name'] ); ?></span>
						<span class="tile__meta"><?php echo esc_html( $brand['meta'] ); ?></span>
					</div>
				</a>
			<?php endforeach; ?>

			<!-- CTA Tile: Other brands -->
			<a href="<?php echo esc_url( $quote_url ); ?>" class="tile tile--cta js-xref-cta">
				<div class="tile__stage tile__stage--cta">
					<svg width="44" height="44" viewBox="0 0 24 24" aria-hidden="true">
						<circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="1.5"></circle>
						<path d="M20 20l-4-4M8 11h6M11 8v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
					</svg>
				</div>
				<div class="tile__body">
					<span class="tile__label"><?php esc_html_e( 'Other brands', 'cedraparts' ); ?></span>
					<span class="tile__meta"><?php esc_html_e( 'Cross-reference any part', 'cedraparts' ); ?></span>
				</div>
			</a>
		</div>
	</div>
</section>

<!-- Results: search + filters -->
<section id="results" class="cedra-results-section">
	<div class="wrap">

		<!-- Search toolbar -->
		<div class="cedra-toolbar">
			<div class="cedra-searchbox">
				<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" class="cedra-searchbox__icon">
					<circle cx="11" cy="11" r="7" fill="none" stroke="#5B646F" stroke-width="1.8"></circle>
					<path d="M20 20l-4-4" stroke="#5B646F" stroke-width="1.8" stroke-linecap="round"></path>
				</svg>
				<label for="q" class="sr"><?php esc_html_e( 'Search by part number or keyword', 'cedraparts' ); ?></label>
				<input
					id="q"
					type="search"
					value="<?php echo esc_attr( $state['q'] ); ?>"
					placeholder="<?php esc_attr_e( 'Search by part number, model or keyword', 'cedraparts' ); ?>"
					class="cedra-searchbox__input js-catalog-search"
					autocomplete="off"
				>
				<button
					type="button"
					class="cedra-searchbox__clear js-clear-search"
					aria-label="<?php esc_attr_e( 'Clear search', 'cedraparts' ); ?>"
					<?php echo '' === $state['q'] ? 'hidden' : ''; ?>
				>
					<svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true">
						<path d="M6 6l12 12M18 6L6 18" stroke="#0B1C2C" stroke-width="2" stroke-linecap="round"></path>
					</svg>
				</button>
			</div>

			<button type="button" class="filters-open js-open-filters">
				<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
					<path d="M4 6h16M7 12h10M10 18h4" stroke="#0B1C2C" stroke-width="1.8" stroke-linecap="round"></path>
				</svg>
				<span><?php esc_html_e( 'Filters', 'cedraparts' ); ?></span>
				<span class="cedra-filters-badge js-active-count" <?php echo 0 === $active_count ? 'hidden' : ''; ?>>
					<?php echo esc_html( (string) $active_count ); ?>
				</span>
			</button>

			<label for="sort" class="sr"><?php esc_html_e( 'Sort by', 'cedraparts' ); ?></label>
			<select id="sort" class="cedra-sort js-catalog-sort">
				<option value="featured" <?php selected( $state['sort'], 'featured' ); ?>><?php esc_html_e( 'Sort: Featured', 'cedraparts' ); ?></option>
				<option value="low" <?php selected( $state['sort'], 'low' ); ?>><?php esc_html_e( 'Price: low to high', 'cedraparts' ); ?></option>
				<option value="high" <?php selected( $state['sort'], 'high' ); ?>><?php esc_html_e( 'Price: high to low', 'cedraparts' ); ?></option>
				<option value="sku" <?php selected( $state['sort'], 'sku' ); ?>><?php esc_html_e( 'Part number A–Z', 'cedraparts' ); ?></option>
			</select>
		</div>

		<!-- Active filters row -->
		<div class="cedra-active-bar">
			<div class="cedra-active-bar__left">
				<span class="cedra-result-summary">
					<strong class="js-result-count"><?php echo esc_html( number_format_i18n( $found_posts ) ); ?></strong>
					<span class="js-result-noun"><?php echo esc_html( 1 === $found_posts ? __( 'result', 'cedraparts' ) : __( 'results', 'cedraparts' ) ); ?></span>
				</span>
				<div class="cedra-chips-wrap js-chips-container">
					<?php echo cedra_render_filter_chips_html( $chips ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		</div>

		<div class="results">

			<!-- Filters Scrim (Tablet/Mobile) -->
			<button type="button" class="scrim js-close-filters" aria-label="<?php esc_attr_e( 'Close filters', 'cedraparts' ); ?>"></button>

			<!-- Filters Sidebar / Slide-in Drawer -->
			<aside class="filters js-filters-drawer" aria-label="<?php esc_attr_e( 'Filters', 'cedraparts' ); ?>">
				<div class="filters__head">
					<span class="filters__title"><?php esc_html_e( 'Filters', 'cedraparts' ); ?></span>
					<div class="filters__head-actions">
						<button type="button" class="filters__reset js-cedra-reset-filters"><?php esc_html_e( 'Reset', 'cedraparts' ); ?></button>
						<button type="button" class="filters-close filters__close-btn js-close-filters" aria-label="<?php esc_attr_e( 'Close filters', 'cedraparts' ); ?>">
							<svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true">
								<path d="M6 6l12 12M18 6L6 18" stroke="#0B1C2C" stroke-width="2" stroke-linecap="round"></path>
							</svg>
						</button>
					</div>
				</div>

				<details open class="filters__group">
					<summary class="filters__summary">
						<span><?php esc_html_e( 'Category', 'cedraparts' ); ?></span>
						<svg class="chev" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true">
							<path d="M6 9l6 6 6-6" fill="none" stroke="#0B1C2C" stroke-width="2" stroke-linecap="round"></path>
						</svg>
					</summary>
					<div class="filters__opts">
						<?php foreach ( $categories as $cat ) : ?>
							<label class="opt">
								<input
									type="checkbox"
									class="js-filter-cat"
									value="<?php echo esc_attr( $cat['slug'] ); ?>"
									<?php checked( in_array( $cat['slug'], $state['cats'], true ) ); ?>
								>
								<span class="opt__label"><?php echo esc_html( $cat['name'] ); ?></span>
								<span class="opt__count"><?php echo esc_html( number_format_i18n( $cat['count'] ) ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</details>

				<details open class="filters__group">
					<summary class="filters__summary">
						<span><?php esc_html_e( 'Brand', 'cedraparts' ); ?></span>
						<svg class="chev" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true">
							<path d="M6 9l6 6 6-6" fill="none" stroke="#0B1C2C" stroke-width="2" stroke-linecap="round"></path>
						</svg>
					</summary>
					<div class="filters__opts">
						<?php foreach ( $all_brands as $brand ) : ?>
							<label class="opt">
								<input
									type="checkbox"
									class="js-filter-brand"
									value="<?php echo esc_attr( $brand['slug'] ); ?>"
									<?php checked( in_array( $brand['slug'], $state['brands'], true ) ); ?>
								>
								<span class="opt__label"><?php echo esc_html( $brand['name'] ); ?></span>
								<span class="opt__count"><?php echo $brand['count'] > 0 ? esc_html( number_format_i18n( $brand['count'] ) ) : ''; ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</details>

				<details open class="filters__group">
					<summary class="filters__summary">
						<span><?php esc_html_e( 'Price', 'cedraparts' ); ?></span>
						<svg class="chev" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true">
							<path d="M6 9l6 6 6-6" fill="none" stroke="#0B1C2C" stroke-width="2" stroke-linecap="round"></path>
						</svg>
					</summary>
					<div class="filters__opts">
						<?php foreach ( $price_tiers as $tier ) : ?>
							<label class="opt">
								<input
									type="radio"
									name="price"
									class="js-filter-price"
									value="<?php echo esc_attr( $tier['id'] ); ?>"
									<?php checked( $state['price'], $tier['id'] ); ?>
								>
								<span class="opt__label"><?php echo esc_html( $tier['label'] ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</details>

				<details open class="filters__group">
					<summary class="filters__summary">
						<span><?php esc_html_e( 'DC voltage', 'cedraparts' ); ?></span>
						<svg class="chev" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true">
							<path d="M6 9l6 6 6-6" fill="none" stroke="#0B1C2C" stroke-width="2" stroke-linecap="round"></path>
						</svg>
					</summary>
					<div class="filters__volts">
						<?php foreach ( array( '12 V', '24 V' ) as $volt_opt ) : ?>
							<?php $v_on = in_array( $volt_opt, $state['volts'], true ); ?>
							<button
								type="button"
								class="cedra-volt-btn js-filter-volt<?php echo $v_on ? ' is-pressed' : ''; ?>"
								data-volt="<?php echo esc_attr( $volt_opt ); ?>"
								aria-pressed="<?php echo $v_on ? 'true' : 'false'; ?>"
							>
								<?php echo esc_html( $volt_opt ); ?>
							</button>
						<?php endforeach; ?>
					</div>
				</details>

				<div class="filters-close filters__apply-bar">
					<button type="button" class="filters__apply-btn js-close-filters">
						<?php esc_html_e( 'Show', 'cedraparts' ); ?>
						<span class="js-result-count"><?php echo esc_html( number_format_i18n( $found_posts ) ); ?></span>
						<span class="js-result-noun"><?php echo esc_html( 1 === $found_posts ? __( 'result', 'cedraparts' ) : __( 'results', 'cedraparts' ) ); ?></span>
					</button>
				</div>
			</aside>

			<!-- Product grid -->
			<div class="cedra-results-main">
				<div class="js-results-container">
					<?php if ( $query->have_posts() ) : ?>
						<div class="cedra-pgrid">
							<?php
							while ( $query->have_posts() ) :
								$query->the_post();
								$product = wc_get_product( get_the_ID() );
								if ( $product ) {
									cedra_render_product_card( $product );
								}
							endwhile;
							wp_reset_postdata();
							?>
						</div>
					<?php else : ?>
						<?php echo cedra_get_empty_catalog_html( $state['q'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endif; ?>
				</div>

				<div class="js-pagination-container">
					<?php
					if ( $found_posts > 0 ) {
						echo cedra_get_pagination_html( $state['page'], $max_pages ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>
				</div>
			</div>
		</div>
	</div>
</section>
