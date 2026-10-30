<?php
/**
 * Catalog Queries, SKU Search, & AJAX Endpoints
 *
 * @package CedraParts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Number of products displayed per catalog page (3,159 / 12 = 264 pages, matching Option 2 design).
 *
 * @return int
 */
function cedra_catalog_per_page() {
	return 12;
}

/**
 * Retrieve total published catalog products count.
 *
 * @return int
 */
function cedra_get_total_products_count() {
	$counts = wp_count_posts( 'product' );
	return isset( $counts->publish ) ? (int) $counts->publish : 0;
}

/**
 * Retrieve non-empty product categories for the glass filter rail.
 * Excludes catch-all `parts-catalog` and `uncategorized` since "All parts" is pill #1.
 *
 * @return array<int, array{slug: string, name: string, count: int, url: string}>
 */
function cedra_get_catalog_categories() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);

	if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
		return array();
	}

	$excluded = array( 'uncategorized', 'parts-catalog' );
	$list     = array();

	foreach ( $terms as $term ) {
		if ( in_array( $term->slug, $excluded, true ) || (int) $term->count <= 0 ) {
			continue;
		}
		$link   = get_term_link( $term );
		$list[] = array(
			'slug'  => $term->slug,
			'name'  => html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ),
			'count' => (int) $term->count,
			'url'   => is_wp_error( $link ) ? home_url( '/' ) : $link,
		);
	}

	return $list;
}

/**
 * Detect active brand taxonomy (`product_brand` in WooCommerce 9.6+ or `pa_brand` attribute).
 *
 * @return string Taxonomy name or empty string.
 */
function cedra_get_brand_taxonomy() {
	if ( taxonomy_exists( 'product_brand' ) ) {
		return 'product_brand';
	}
	if ( taxonomy_exists( 'pa_brand' ) ) {
		return 'pa_brand';
	}
	return '';
}

/**
 * Retrieve non-empty brands for the hero search bar dropdown.
 *
 * @return array<int, array{slug: string, name: string, count: int}>
 */
function cedra_get_catalog_brands() {
	$tax = cedra_get_brand_taxonomy();
	if ( '' === $tax ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => $tax,
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);

	if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
		return array();
	}

	$brands = array();
	foreach ( $terms as $term ) {
		$brands[] = array(
			'slug'  => $term->slug,
			'name'  => html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ),
			'count' => (int) $term->count,
		);
	}

	return $brands;
}

/**
 * Resolve current catalog filter state from URL query parameters and WP queried taxonomy object.
 *
 * @return array{cat: string, brand: string, q: string, sort: string, page: int}
 */
function cedra_get_active_catalog_state() {
	$cat   = '';
	$brand = '';
	$q     = '';
	$sort  = 'featured';
	$page  = max( 1, (int) get_query_var( 'paged', 1 ) );

	if ( is_tax( 'product_cat' ) ) {
		$obj = get_queried_object();
		if ( $obj && ! empty( $obj->slug ) && ! in_array( $obj->slug, array( 'parts-catalog', 'uncategorized' ), true ) ) {
			$cat = sanitize_title( $obj->slug );
		}
	}

	$brand_tax = cedra_get_brand_taxonomy();
	if ( $brand_tax && is_tax( $brand_tax ) ) {
		$obj = get_queried_object();
		if ( $obj && ! empty( $obj->slug ) ) {
			$brand = sanitize_title( $obj->slug );
		}
	}

	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['cat'] ) && '' !== $_GET['cat'] ) {
		$raw_cat = sanitize_title( wp_unslash( $_GET['cat'] ) );
		$cat     = ( 'all' === $raw_cat ) ? '' : $raw_cat;
	} elseif ( isset( $_GET['product_cat'] ) && '' !== $_GET['product_cat'] ) {
		$cat = sanitize_title( wp_unslash( $_GET['product_cat'] ) );
	}

	if ( isset( $_GET['brand'] ) && '' !== $_GET['brand'] ) {
		$brand = sanitize_title( wp_unslash( $_GET['brand'] ) );
	} elseif ( isset( $_GET['product_brand'] ) && '' !== $_GET['product_brand'] ) {
		$brand = sanitize_title( wp_unslash( $_GET['product_brand'] ) );
	}

	if ( isset( $_GET['q'] ) && '' !== $_GET['q'] ) {
		$q = sanitize_text_field( wp_unslash( $_GET['q'] ) );
	} elseif ( get_search_query() ) {
		$q = sanitize_text_field( get_search_query() );
	}

	if ( isset( $_GET['orderby'] ) && '' !== $_GET['orderby'] ) {
		$sort = sanitize_key( wp_unslash( $_GET['orderby'] ) );
	} elseif ( isset( $_GET['sort'] ) && '' !== $_GET['sort'] ) {
		$sort = sanitize_key( wp_unslash( $_GET['sort'] ) );
	}

	if ( isset( $_GET['cpage'] ) && (int) $_GET['cpage'] > 1 ) {
		$page = (int) $_GET['cpage'];
	}
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	return array(
		'cat'   => $cat,
		'brand' => $brand,
		'q'     => $q,
		'sort'  => $sort,
		'page'  => $page,
	);
}

/**
 * Find product IDs matching a search string by SKU or title/content.
 *
 * @param string $search_term Raw search keyword or part number.
 * @return array<int>|null Array of matching product IDs, or null when no search term is given.
 */
function cedra_find_matching_product_ids( $search_term ) {
	global $wpdb;

	$search_term = trim( $search_term );
	if ( '' === $search_term ) {
		return null;
	}

	$like = '%' . $wpdb->esc_like( $search_term ) . '%';

	// 1. Match SKUs in wc_product_meta_lookup and postmeta.
	$sku_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_sku' AND meta_value LIKE %s LIMIT 1500",
			$like
		)
	);

	// 2. Match post_title, post_excerpt, or post_content on published products.
	$text_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts}
			 WHERE post_type = 'product'
			   AND post_status = 'publish'
			   AND (post_title LIKE %s OR post_excerpt LIKE %s OR post_content LIKE %s)
			 LIMIT 2000",
			$like,
			$like,
			$like
		)
	);

	$merged = array_unique( array_map( 'intval', array_merge( (array) $sku_ids, (array) $text_ids ) ) );
	return empty( $merged ) ? array( 0 ) : array_values( $merged );
}

/**
 * Build WP_Query arguments for the Cedra Parts catalog.
 *
 * @param array{cat?: string, brand?: string, q?: string, sort?: string, page?: int, per_page?: int} $state Filter state.
 * @return WP_Query
 */
function cedra_query_catalog_products( $state ) {
	$cat      = isset( $state['cat'] ) && 'all' !== $state['cat'] ? sanitize_title( $state['cat'] ) : '';
	$brand    = isset( $state['brand'] ) && 'all' !== $state['brand'] ? sanitize_title( $state['brand'] ) : '';
	$q        = isset( $state['q'] ) ? sanitize_text_field( $state['q'] ) : '';
	$sort     = isset( $state['sort'] ) ? sanitize_key( $state['sort'] ) : 'featured';
	$page     = isset( $state['page'] ) ? max( 1, (int) $state['page'] ) : 1;
	$per_page = isset( $state['per_page'] ) ? max( 1, (int) $state['per_page'] ) : cedra_catalog_per_page();

	$tax_query = array( 'relation' => 'AND' );

	// Exclude out-of-catalog hidden items.
	$tax_query[] = array(
		'taxonomy' => 'product_visibility',
		'field'    => 'name',
		'terms'    => array( 'exclude-from-catalog' ),
		'operator' => 'NOT IN',
	);

	if ( '' !== $cat ) {
		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => array( $cat ),
		);
	}

	$brand_tax = cedra_get_brand_taxonomy();
	if ( '' !== $brand && '' !== $brand_tax ) {
		$tax_query[] = array(
			'taxonomy' => $brand_tax,
			'field'    => 'slug',
			'terms'    => array( $brand ),
		);
	}

	$args = array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'posts_per_page'      => $per_page,
		'paged'               => $page,
		'ignore_sticky_posts' => true,
		'tax_query'           => $tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	);

	// Sorting.
	switch ( $sort ) {
		case 'price':
		case 'price_asc':
			$args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['orderby']  = array(
				'meta_value_num' => 'ASC',
				'ID'             => 'DESC',
			);
			break;

		case 'price-desc':
		case 'price_desc':
			$args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['orderby']  = array(
				'meta_value_num' => 'DESC',
				'ID'             => 'DESC',
			);
			break;

		case 'title':
		case 'name_asc':
			$args['orderby'] = array(
				'title' => 'ASC',
				'ID'    => 'DESC',
			);
			break;

		case 'featured':
		default:
			$args['orderby'] = array(
				'date' => 'DESC',
				'ID'   => 'DESC',
			);
			break;
	}

	// SKU + Keyword Search.
	if ( '' !== $q ) {
		$matching_ids     = cedra_find_matching_product_ids( $q );
		$args['post__in'] = $matching_ids;
	}

	return new WP_Query( $args );
}

/**
 * Determine human-readable catalog heading title based on active filters.
 *
 * @param array{cat: string, brand: string, q: string} $state Filter state.
 * @return string
 */
function cedra_get_catalog_heading_title( $state ) {
	$parts = array();

	if ( ! empty( $state['cat'] ) && 'all' !== $state['cat'] ) {
		$term = get_term_by( 'slug', $state['cat'], 'product_cat' );
		if ( $term && ! is_wp_error( $term ) ) {
			$parts[] = html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' );
		}
	}

	if ( ! empty( $state['brand'] ) && 'all' !== $state['brand'] ) {
		$brand_tax = cedra_get_brand_taxonomy();
		if ( $brand_tax ) {
			$bterm = get_term_by( 'slug', $state['brand'], $brand_tax );
			if ( $bterm && ! is_wp_error( $bterm ) ) {
				$parts[] = html_entity_decode( $bterm->name, ENT_QUOTES, 'UTF-8' );
			}
		}
	}

	if ( empty( $parts ) ) {
		$title = __( 'All parts', 'cedraparts' );
	} else {
		$title = implode( ' · ', $parts );
	}

	if ( ! empty( $state['q'] ) ) {
		$title .= sprintf( ' (“%s”)', $state['q'] );
	}

	return $title;
}

/**
 * Render empty state card when no products match a search/filter combination.
 *
 * @param string $heading_title Current filter title.
 * @return string HTML markup.
 */
function cedra_get_empty_catalog_html( $heading_title ) {
	$contact_url = cedra_get_option( 'contact_url' );
	ob_start();
	?>
	<div class="span2 glass cedra-empty-card">
		<span class="cedra-eyebrow-mono"><?php esc_html_e( 'CROSS-REFERENCE DESK', 'cedraparts' ); ?></span>
		<div class="cedra-empty-card__title">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: active filter/search label */
					__( 'No online matches in %s', 'cedraparts' ),
					$heading_title
				)
			);
			?>
		</div>
		<p class="cedra-empty-card__desc">
			<?php esc_html_e( 'Many OEM & aftermarket genset part numbers can be cross-referenced directly by our technical parts specialists in Beamsville, Ontario.', 'cedraparts' ); ?>
		</p>
		<div class="cedra-empty-card__actions">
			<button type="button" class="cedra-btn cedra-btn--glass js-cedra-reset-filters">
				<?php esc_html_e( 'Reset filters', 'cedraparts' ); ?>
			</button>
			<a href="<?php echo esc_url( $contact_url ); ?>" class="cedra-btn cedra-btn--dark">
				<?php esc_html_e( 'Send a part number', 'cedraparts' ); ?> <span aria-hidden="true">↗</span>
			</a>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * AJAX Handler: Filter, Sort, Search, and Load More catalog products.
 */
function cedra_ajax_filter_catalog() {
	check_ajax_referer( 'cedra_nonce', 'nonce' );

	$cat    = isset( $_POST['cat'] ) ? sanitize_title( wp_unslash( $_POST['cat'] ) ) : '';
	$brand  = isset( $_POST['brand'] ) ? sanitize_title( wp_unslash( $_POST['brand'] ) ) : '';
	$q      = isset( $_POST['q'] ) ? sanitize_text_field( wp_unslash( $_POST['q'] ) ) : '';
	$sort   = isset( $_POST['sort'] ) ? sanitize_key( wp_unslash( $_POST['sort'] ) ) : 'featured';
	$page   = isset( $_POST['page'] ) ? max( 1, (int) $_POST['page'] ) : 1;
	$append = ! empty( $_POST['append'] ) && 'true' === $_POST['append'];

	if ( 'all' === $cat ) {
		$cat = '';
	}
	if ( 'all' === $brand ) {
		$brand = '';
	}

	$state = array(
		'cat'   => $cat,
		'brand' => $brand,
		'q'     => $q,
		'sort'  => $sort,
		'page'  => $page,
	);

	$per_page      = cedra_catalog_per_page();
	$query         = cedra_query_catalog_products( $state );
	$found         = (int) $query->found_posts;
	$max_pages     = max( 1, (int) $query->max_num_pages );
	$start_index   = ( ( $page - 1 ) * $per_page ) + 1;
	$heading_title = cedra_get_catalog_heading_title( $state );

	ob_start();
	if ( $query->have_posts() ) {
		$idx = $start_index;
		while ( $query->have_posts() ) {
			$query->the_post();
			$product = wc_get_product( get_the_ID() );
			if ( $product ) {
				cedra_render_product_card( $product, $idx );
				++$idx;
			}
		}
		wp_reset_postdata();
	} elseif ( ! $append ) {
		echo cedra_get_empty_catalog_html( $heading_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	$cards_html = (string) ob_get_clean();

	$shown_now = min( $found, ( ( $page - 1 ) * $per_page ) + (int) $query->post_count );
	$progress  = $found > 0 ? min( 100, max( 4, round( ( $shown_now / $found ) * 100, 2 ) ) ) : 0;

	wp_send_json_success(
		array(
			'html'          => $cards_html,
			'found'         => $found,
			'foundFormatted'=> number_format_i18n( $found ),
			'shown'         => $shown_now,
			'shownFormatted'=> number_format_i18n( $shown_now ),
			'page'          => $page,
			'maxPages'      => $max_pages,
			'pageLabel'     => sprintf(
				'PAGE %s / %s',
				str_pad( (string) $page, 2, '0', STR_PAD_LEFT ),
				str_pad( (string) $max_pages, 2, '0', STR_PAD_LEFT )
			),
			'catTitle'      => $heading_title,
			'hasMore'       => $page < $max_pages,
			'progressPct'   => $progress,
			'hasFilters'    => ( '' !== $cat || '' !== $brand || '' !== $q ),
		)
	);
}
add_action( 'wp_ajax_cedra_filter_catalog', 'cedra_ajax_filter_catalog' );
add_action( 'wp_ajax_nopriv_cedra_filter_catalog', 'cedra_ajax_filter_catalog' );

/**
 * AJAX Handler: Add to Cart, Update Drawer Item Quantity, or Fetch Drawer State.
 */
function cedra_ajax_cart_action() {
	check_ajax_referer( 'cedra_nonce', 'nonce' );

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => __( 'Cart is unavailable.', 'cedraparts' ) ) );
	}

	$op = isset( $_POST['op'] ) ? sanitize_key( wp_unslash( $_POST['op'] ) ) : 'get';

	if ( 'add' === $op ) {
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$quantity   = isset( $_POST['quantity'] ) ? max( 1, absint( $_POST['quantity'] ) ) : 1;

		if ( ! $product_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid product.', 'cedraparts' ) ) );
		}

		$added = WC()->cart->add_to_cart( $product_id, $quantity );
		if ( ! $added ) {
			wp_send_json_error(
				array(
					'message'  => __( 'Could not add item to cart. Please view product options.', 'cedraparts' ),
					'redirect' => get_permalink( $product_id ),
				)
			);
		}
	} elseif ( 'update' === $op ) {
		$cart_key = isset( $_POST['cart_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_key'] ) ) : '';
		$quantity = isset( $_POST['quantity'] ) ? max( 0, (int) $_POST['quantity'] ) : 0;

		if ( '' !== $cart_key && isset( WC()->cart->cart_contents[ $cart_key ] ) ) {
			if ( 0 === $quantity ) {
				WC()->cart->remove_cart_item( $cart_key );
			} else {
				WC()->cart->set_quantity( $cart_key, $quantity, true );
			}
		}
	}

	WC()->cart->calculate_totals();

	wp_send_json_success(
		array(
			'count'      => (int) WC()->cart->get_cart_contents_count(),
			'drawerHtml' => cedra_get_mini_cart_drawer_inner_html(),
		)
	);
}
add_action( 'wp_ajax_cedra_cart_action', 'cedra_ajax_cart_action' );
add_action( 'wp_ajax_nopriv_cedra_cart_action', 'cedra_ajax_cart_action' );

/**
 * Keep header `(count)` badge synchronized with native WooCommerce AJAX cart fragments.
 *
 * @param array<string, string> $fragments Cart HTML fragments.
 * @return array<string, string>
 */
function cedra_cart_count_fragments( $fragments ) {
	if ( function_exists( 'WC' ) && WC()->cart ) {
		$count = (int) WC()->cart->get_cart_contents_count();
		$fragments['.js-cedra-cart-count'] = '<span class="cedra-nav__cart-count js-cedra-cart-count">(' . esc_html( (string) $count ) . ')</span>';
	}
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'cedra_cart_count_fragments' );

/**
 * Enhance native WordPress `?s=...&post_type=product` main query to also match SKUs.
 *
 * @param WP_Query $query Query instance.
 */
function cedra_extend_main_product_search( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_search() && 'product' === $query->get( 'post_type' ) ) {
		$term = $query->get( 's' );
		if ( is_string( $term ) && '' !== trim( $term ) ) {
			$ids = cedra_find_matching_product_ids( $term );
			if ( is_array( $ids ) ) {
				$query->set( 's', '' );
				$query->set( 'post__in', $ids );
			}
		}
	}
}
add_action( 'pre_get_posts', 'cedra_extend_main_product_search' );
