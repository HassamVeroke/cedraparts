<?php
/**
 * Catalog Queries, Multi-Facet Filtering, SKU Search, Category Tile Thumbnails & AJAX Endpoints
 *
 * @package CedraParts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Number of products displayed per catalog page (12 per page -> 264 pages).
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
 * Find a unique representative product image URL for each category tile (cached in a transient).
 *
 * @param array<int, WP_Term> $terms Category terms.
 * @return array<string, string> Map of category slug => image URL (or empty string if none).
 */
function cedra_get_category_tile_images( $terms ) {
	$cached = get_transient( 'cedra_cat_tile_imgs_v3' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$map      = array();
	$used_ids = array();

	foreach ( $terms as $term ) {
		$thumb_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
		if ( $thumb_id ) {
			$src = wp_get_attachment_image_url( $thumb_id, 'woocommerce_thumbnail' );
			if ( $src && ! str_contains( strtolower( $src ), 'image_not_available' ) ) {
				$map[ $term->slug ] = $src;
				$used_ids[]         = $thumb_id;
				continue;
			}
		}

		// Find a product in this category that has a real image.
		$q = new WP_Query(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => 25,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => '_thumbnail_id',
						'compare' => 'EXISTS',
					),
				),
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => array( $term->term_id ),
					),
				),
			)
		);

		$chosen_src = '';
		$fallback   = '';
		if ( ! empty( $q->posts ) ) {
			foreach ( $q->posts as $pid ) {
				$img_id = (int) get_post_thumbnail_id( $pid );
				if ( ! $img_id ) {
					continue;
				}
				$src = wp_get_attachment_image_url( $img_id, 'woocommerce_thumbnail' );
				if ( ! $src ) {
					continue;
				}
				$lower = strtolower( $src );
				if ( str_contains( $lower, 'image_not_available' ) || str_contains( $lower, 'woocommerce-placeholder' ) ) {
					continue;
				}
				if ( '' === $fallback ) {
					$fallback = $src;
				}
				if ( ! in_array( $img_id, $used_ids, true ) ) {
					$chosen_src = $src;
					$used_ids[] = $img_id;
					break;
				}
			}
		}

		$map[ $term->slug ] = '' !== $chosen_src ? $chosen_src : $fallback;
	}

	// Pick a unique representative image for the "Parts Catalog" (all parts) tile.
	$all_q = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 30,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => '_thumbnail_id',
					'compare' => 'EXISTS',
				),
			),
		)
	);
	$all_src = '';
	if ( ! empty( $all_q->posts ) ) {
		foreach ( $all_q->posts as $pid ) {
			$img_id = (int) get_post_thumbnail_id( $pid );
			if ( ! $img_id || in_array( $img_id, $used_ids, true ) ) {
				continue;
			}
			$src = wp_get_attachment_image_url( $img_id, 'woocommerce_thumbnail' );
			if ( ! $src ) {
				continue;
			}
			$lower = strtolower( $src );
			if ( str_contains( $lower, 'image_not_available' ) || str_contains( $lower, 'woocommerce-placeholder' ) ) {
				continue;
			}
			$all_src = $src;
			break;
		}
	}
	$map['__all__'] = '' !== $all_src ? $all_src : ( get_template_directory_uri() . '/assets/images/hero-dse7320.webp' );

	set_transient( 'cedra_cat_tile_imgs_v3', $map, 6 * HOUR_IN_SECONDS );
	return $map;
}

/**
 * Flush category tile image cache when products or categories change.
 */
function cedra_flush_category_tile_cache() {
	delete_transient( 'cedra_cat_tile_imgs_v3' );
}
add_action( 'save_post_product', 'cedra_flush_category_tile_cache' );
add_action( 'edited_product_cat', 'cedra_flush_category_tile_cache' );

/**
 * Retrieve non-empty product categories for the Shop tile grid and sidebar checkboxes.
 * Excludes catch-all `parts-catalog` and `uncategorized` (since "Parts Catalog" / All parts is Tile #1).
 *
 * @return array<int, array{slug: string, name: string, count: int, url: string, img: string, kind: string}>
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
	$filtered = array();
	foreach ( $terms as $term ) {
		if ( in_array( $term->slug, $excluded, true ) || (int) $term->count <= 0 ) {
			continue;
		}
		$filtered[] = $term;
	}

	$img_map = cedra_get_category_tile_images( $filtered );
	$list    = array();

	foreach ( $filtered as $term ) {
		$slug = $term->slug;
		$kind = 'engine';
		if ( 'generator-controllers' === $slug ) {
			$kind = 'controller';
		} elseif ( 'battery-chargers' === $slug ) {
			$kind = 'charger';
		} elseif ( 'voltage-regulators' === $slug ) {
			$kind = 'avr';
		} elseif ( 'generator-communications' === $slug ) {
			$kind = 'comm';
		} elseif ( 'oil-fuel-air-filters' === $slug ) {
			$kind = 'filter';
		} elseif ( 'hoses-gaskets' === $slug ) {
			$kind = 'gasket';
		} elseif ( 'belts' === $slug || 'belts-hoses-gaskets' === $slug ) {
			$kind = 'belt';
		}

		$link   = get_term_link( $term );
		$list[] = array(
			'slug'  => $slug,
			'name'  => html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ),
			'count' => (int) $term->count,
			'url'   => is_wp_error( $link ) ? home_url( '/' ) : $link,
			'img'   => isset( $img_map[ $slug ] ) ? $img_map[ $slug ] : '',
			'kind'  => $kind,
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
 * Retrieve catalog brands (WooCommerce taxonomy brands + Authorized Distributor brands).
 *
 * @return array<int, array{slug: string, name: string, count: int, is_tax: bool, src: string, h: int, meta: string}>
 */
function cedra_get_all_brand_entries() {
	$entries   = array();
	$seen_name = array();
	$tax       = cedra_get_brand_taxonomy();

	if ( '' !== $tax ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $tax,
				'hide_empty' => true,
				'orderby'    => 'count',
				'order'      => 'DESC',
			)
		);
		if ( ! is_wp_error( $terms ) && is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				$name        = html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' );
				$seen_name[] = strtolower( $name );
				$entries[]   = array(
					'slug'   => $term->slug,
					'name'   => $name,
					'count'  => (int) $term->count,
					'is_tax' => true,
					'src'    => '',
					'h'      => 30,
					'meta'   => sprintf(
						/* translators: %s: formatted count */
						__( '%s parts', 'cedraparts' ),
						number_format_i18n( (int) $term->count )
					),
				);
			}
		}
	}

	foreach ( cedra_get_distributor_logos() as $dist ) {
		if ( in_array( strtolower( $dist['name'] ), $seen_name, true ) ) {
			continue;
		}
		$entries[] = array(
			'slug'   => $dist['slug'],
			'name'   => $dist['name'],
			'count'  => 0,
			'is_tax' => false,
			'src'    => $dist['src'],
			'h'      => $dist['h'],
			'meta'   => __( 'Authorized distributor', 'cedraparts' ),
		);
	}

	return $entries;
}

/**
 * Return available price filter tiers matching Option 2 design.
 *
 * @return array<int, array{id: string, label: string, min: float|null, max: float|null}>
 */
function cedra_get_price_tiers() {
	return array(
		array(
			'id'    => 'any',
			'label' => __( 'Any price', 'cedraparts' ),
			'min'   => null,
			'max'   => null,
		),
		array(
			'id'    => 'u250',
			'label' => __( 'Under $250', 'cedraparts' ),
			'min'   => null,
			'max'   => 250.0,
		),
		array(
			'id'    => '250',
			'label' => __( '$250 – $500', 'cedraparts' ),
			'min'   => 250.0,
			'max'   => 500.0,
		),
		array(
			'id'    => '500',
			'label' => __( '$500 – $1,000', 'cedraparts' ),
			'min'   => 500.0,
			'max'   => 1000.0,
		),
		array(
			'id'    => '1000',
			'label' => __( '$1,000 and up', 'cedraparts' ),
			'min'   => 1000.0,
			'max'   => null,
		),
	);
}

/**
 * Parse a comma-separated or array query parameter into clean sanitized slugs.
 *
 * @param mixed $raw Raw value.
 * @return array<int, string>
 */
function cedra_parse_slug_list( $raw ) {
	if ( is_array( $raw ) ) {
		$items = $raw;
	} elseif ( is_string( $raw ) && '' !== trim( $raw ) ) {
		$items = explode( ',', $raw );
	} else {
		return array();
	}

	$out = array();
	foreach ( $items as $it ) {
		$s = sanitize_title( wp_unslash( (string) $it ) );
		if ( '' !== $s && 'all' !== $s && ! in_array( $s, $out, true ) ) {
			$out[] = $s;
		}
	}
	return $out;
}

/**
 * Resolve current catalog filter state from URL query parameters and WP queried taxonomy object.
 *
 * @return array{cats: array<int,string>, brands: array<int,string>, volts: array<int,string>, price: string, q: string, sort: string, page: int}
 */
function cedra_get_active_catalog_state() {
	$cats   = array();
	$brands = array();
	$volts  = array();
	$price  = 'any';
	$q      = '';
	$sort   = 'featured';
	$page   = max( 1, (int) get_query_var( 'paged', 1 ) );

	if ( is_tax( 'product_cat' ) ) {
		$obj = get_queried_object();
		if ( $obj && ! empty( $obj->slug ) && ! in_array( $obj->slug, array( 'parts-catalog', 'uncategorized' ), true ) ) {
			$cats[] = sanitize_title( $obj->slug );
		}
	}

	$brand_tax = cedra_get_brand_taxonomy();
	if ( $brand_tax && is_tax( $brand_tax ) ) {
		$obj = get_queried_object();
		if ( $obj && ! empty( $obj->slug ) ) {
			$brands[] = sanitize_title( $obj->slug );
		}
	}

	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['cats'] ) ) {
		$cats = cedra_parse_slug_list( $_GET['cats'] );
	} elseif ( isset( $_GET['cat'] ) ) {
		$cats = cedra_parse_slug_list( $_GET['cat'] );
	} elseif ( isset( $_GET['product_cat'] ) ) {
		$cats = cedra_parse_slug_list( $_GET['product_cat'] );
	}

	if ( isset( $_GET['brands'] ) ) {
		$brands = cedra_parse_slug_list( $_GET['brands'] );
	} elseif ( isset( $_GET['brand'] ) ) {
		$brands = cedra_parse_slug_list( $_GET['brand'] );
	} elseif ( isset( $_GET['product_brand'] ) ) {
		$brands = cedra_parse_slug_list( $_GET['product_brand'] );
	}

	if ( isset( $_GET['volts'] ) ) {
		$raw_v = is_array( $_GET['volts'] ) ? $_GET['volts'] : explode( ',', sanitize_text_field( wp_unslash( $_GET['volts'] ) ) );
		foreach ( $raw_v as $rv ) {
			$rv = trim( (string) $rv );
			if ( in_array( $rv, array( '12 V', '24 V', '12v', '24v' ), true ) ) {
				$norm = str_starts_with( strtolower( $rv ), '12' ) ? '12 V' : '24 V';
				if ( ! in_array( $norm, $volts, true ) ) {
					$volts[] = $norm;
				}
			}
		}
	}

	if ( isset( $_GET['price'] ) ) {
		$p_cand = sanitize_key( wp_unslash( $_GET['price'] ) );
		if ( in_array( $p_cand, array( 'any', 'u250', '250', '500', '1000' ), true ) ) {
			$price = $p_cand;
		}
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
		'cats'   => $cats,
		'brands' => $brands,
		'volts'  => $volts,
		'price'  => $price,
		'q'      => $q,
		'sort'   => $sort,
		'page'   => $page,
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

	$sku_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_sku' AND meta_value LIKE %s LIMIT 1500",
			$like
		)
	);

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
 * Find product IDs matching selected DC voltage options (`12 V`, `24 V`, including dual `12/24 V` units).
 *
 * @param array<int, string> $volts Selected voltage labels.
 * @return array<int>|null
 */
function cedra_find_voltage_product_ids( $volts ) {
	global $wpdb;

	if ( empty( $volts ) ) {
		return null;
	}

	$clauses = array();
	$params  = array();

	foreach ( $volts as $v ) {
		if ( '12 V' === $v ) {
			$clauses[] = '(post_title LIKE %s OR post_title LIKE %s OR post_title LIKE %s OR post_content LIKE %s)';
			$params[]  = '%12 V%';
			$params[]  = '%12V%';
			$params[]  = '%12/24%';
			$params[]  = '%12 V%';
		} elseif ( '24 V' === $v ) {
			$clauses[] = '(post_title LIKE %s OR post_title LIKE %s OR post_title LIKE %s OR post_content LIKE %s)';
			$params[]  = '%24 V%';
			$params[]  = '%24V%';
			$params[]  = '%12/24%';
			$params[]  = '%24 V%';
		}
	}

	if ( empty( $clauses ) ) {
		return null;
	}

	$where_or = implode( ' OR ', $clauses );
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$sql = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish' AND ($where_or) LIMIT 1500", $params );
	$ids = array_map( 'intval', (array) $wpdb->get_col( $sql ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

	return empty( $ids ) ? array( 0 ) : $ids;
}

/**
 * Build and run WP_Query for the Cedra Parts catalog supporting all Option 2 facets.
 *
 * @param array<string, mixed> $state Filter state.
 * @return WP_Query
 */
function cedra_query_catalog_products( $state ) {
	$cats     = isset( $state['cats'] ) ? (array) $state['cats'] : array();
	$brands   = isset( $state['brands'] ) ? (array) $state['brands'] : array();
	$volts    = isset( $state['volts'] ) ? (array) $state['volts'] : array();
	$price    = isset( $state['price'] ) ? sanitize_key( (string) $state['price'] ) : 'any';
	$q        = isset( $state['q'] ) ? sanitize_text_field( (string) $state['q'] ) : '';
	$sort     = isset( $state['sort'] ) ? sanitize_key( (string) $state['sort'] ) : 'featured';
	$page     = isset( $state['page'] ) ? max( 1, (int) $state['page'] ) : 1;
	$per_page = isset( $state['per_page'] ) ? max( 1, (int) $state['per_page'] ) : cedra_catalog_per_page();

	$tax_query  = array( 'relation' => 'AND' );
	$meta_query = array( 'relation' => 'AND' );
	$id_sets    = array();

	$tax_query[] = array(
		'taxonomy' => 'product_visibility',
		'field'    => 'name',
		'terms'    => array( 'exclude-from-catalog' ),
		'operator' => 'NOT IN',
	);

	if ( ! empty( $cats ) ) {
		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => $cats,
			'operator' => 'IN',
		);
	}

	// Brands: support both WooCommerce taxonomy brand slugs and distributor brand names.
	$brand_tax = cedra_get_brand_taxonomy();
	if ( ! empty( $brands ) ) {
		$tax_slugs    = array();
		$keyword_ids  = array();
		$has_kw_brand = false;

		foreach ( $brands as $b_slug ) {
			$term = $brand_tax ? get_term_by( 'slug', $b_slug, $brand_tax ) : false;
			if ( $term && ! is_wp_error( $term ) ) {
				$tax_slugs[] = $term->slug;
			} else {
				$has_kw_brand = true;
				$kw_label     = str_replace( '-', ' ', $b_slug );
				$found_kw     = cedra_find_matching_product_ids( $kw_label );
				if ( is_array( $found_kw ) && array( 0 ) !== $found_kw ) {
					$keyword_ids = array_merge( $keyword_ids, $found_kw );
				}
			}
		}

		if ( ! empty( $tax_slugs ) && ! $has_kw_brand ) {
			$tax_query[] = array(
				'taxonomy' => $brand_tax,
				'field'    => 'slug',
				'terms'    => $tax_slugs,
				'operator' => 'IN',
			);
		} else {
			// Combine taxonomy product IDs + keyword brand product IDs via OR.
			$brand_union = $keyword_ids;
			if ( ! empty( $tax_slugs ) && $brand_tax ) {
				$tax_pids = get_posts(
					array(
						'post_type'      => 'product',
						'post_status'    => 'publish',
						'posts_per_page' => 3500,
						'fields'         => 'ids',
						'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
							array(
								'taxonomy' => $brand_tax,
								'field'    => 'slug',
								'terms'    => $tax_slugs,
							),
						),
					)
				);
				$brand_union = array_merge( $brand_union, array_map( 'intval', $tax_pids ) );
			}
			$brand_union = array_values( array_unique( $brand_union ) );
			$id_sets[]   = empty( $brand_union ) ? array( 0 ) : $brand_union;
		}
	}

	// Price tier filter.
	foreach ( cedra_get_price_tiers() as $tier ) {
		if ( $tier['id'] === $price && 'any' !== $price ) {
			if ( null !== $tier['min'] && null !== $tier['max'] ) {
				$meta_query[] = array(
					'key'     => '_price',
					'value'   => array( $tier['min'], $tier['max'] - 0.001 ),
					'type'    => 'DECIMAL(12,2)',
					'compare' => 'BETWEEN',
				);
			} elseif ( null !== $tier['max'] ) {
				$meta_query[] = array(
					'key'     => '_price',
					'value'   => $tier['max'],
					'type'    => 'DECIMAL(12,2)',
					'compare' => '<',
				);
			} elseif ( null !== $tier['min'] ) {
				$meta_query[] = array(
					'key'     => '_price',
					'value'   => $tier['min'],
					'type'    => 'DECIMAL(12,2)',
					'compare' => '>=',
				);
			}
			break;
		}
	}

	// Search query.
	if ( '' !== $q ) {
		$q_ids = cedra_find_matching_product_ids( $q );
		if ( is_array( $q_ids ) ) {
			$id_sets[] = $q_ids;
		}
	}

	// DC voltage filter.
	if ( ! empty( $volts ) ) {
		$v_ids = cedra_find_voltage_product_ids( $volts );
		if ( is_array( $v_ids ) ) {
			$id_sets[] = $v_ids;
		}
	}

	$args = array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'posts_per_page'      => $per_page,
		'paged'               => $page,
		'ignore_sticky_posts' => true,
		'tax_query'           => $tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	);

	if ( count( $meta_query ) > 1 ) {
		$args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	}

	if ( ! empty( $id_sets ) ) {
		$intersect = array_shift( $id_sets );
		foreach ( $id_sets as $set ) {
			$intersect = array_values( array_intersect( $intersect, $set ) );
		}
		$args['post__in'] = empty( $intersect ) ? array( 0 ) : $intersect;
	}

	// Sorting.
	switch ( $sort ) {
		case 'low':
		case 'price':
		case 'price_asc':
			$args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['orderby']  = array(
				'meta_value_num' => 'ASC',
				'ID'             => 'DESC',
			);
			break;

		case 'high':
		case 'price-desc':
		case 'price_desc':
			$args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['orderby']  = array(
				'meta_value_num' => 'DESC',
				'ID'             => 'DESC',
			);
			break;

		case 'sku':
			$args['meta_key'] = '_sku'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['orderby']  = array(
				'meta_value' => 'ASC',
				'title'      => 'ASC',
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

	return new WP_Query( $args );
}

/**
 * Build active filter chip descriptors for the active filters bar.
 *
 * @param array<string, mixed> $state Filter state.
 * @return array<int, array{type: string, value: string, label: string}>
 */
function cedra_get_active_filter_chips( $state ) {
	$chips = array();

	if ( ! empty( $state['q'] ) ) {
		$chips[] = array(
			'type'  => 'q',
			'value' => $state['q'],
			'label' => '“' . $state['q'] . '”',
		);
	}

	if ( ! empty( $state['cats'] ) ) {
		foreach ( (array) $state['cats'] as $c_slug ) {
			$term    = get_term_by( 'slug', $c_slug, 'product_cat' );
			$label   = ( $term && ! is_wp_error( $term ) ) ? html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ) : $c_slug;
			$chips[] = array(
				'type'  => 'cat',
				'value' => $c_slug,
				'label' => $label,
			);
		}
	}

	if ( ! empty( $state['brands'] ) ) {
		$all_brands = cedra_get_all_brand_entries();
		$b_map      = array();
		foreach ( $all_brands as $b ) {
			$b_map[ $b['slug'] ] = $b['name'];
		}
		foreach ( (array) $state['brands'] as $b_slug ) {
			$chips[] = array(
				'type'  => 'brand',
				'value' => $b_slug,
				'label' => isset( $b_map[ $b_slug ] ) ? $b_map[ $b_slug ] : ucwords( str_replace( '-', ' ', $b_slug ) ),
			);
		}
	}

	if ( ! empty( $state['volts'] ) ) {
		foreach ( (array) $state['volts'] as $v ) {
			$chips[] = array(
				'type'  => 'volt',
				'value' => $v,
				'label' => $v,
			);
		}
	}

	if ( ! empty( $state['price'] ) && 'any' !== $state['price'] ) {
		foreach ( cedra_get_price_tiers() as $tier ) {
			if ( $tier['id'] === $state['price'] ) {
				$chips[] = array(
					'type'  => 'price',
					'value' => $tier['id'],
					'label' => $tier['label'],
				);
				break;
			}
		}
	}

	return $chips;
}

/**
 * Render active filter chips HTML.
 *
 * @param array<int, array{type: string, value: string, label: string}> $chips Chips array.
 * @return string HTML markup.
 */
function cedra_render_filter_chips_html( $chips ) {
	if ( empty( $chips ) ) {
		return '';
	}

	ob_start();
	foreach ( $chips as $ch ) :
		?>
		<button
			type="button"
			class="chip-x js-remove-chip"
			data-chip-type="<?php echo esc_attr( $ch['type'] ); ?>"
			data-chip-val="<?php echo esc_attr( $ch['value'] ); ?>"
			aria-label="<?php echo esc_attr( sprintf( __( 'Remove filter %s', 'cedraparts' ), $ch['label'] ) ); ?>"
		>
			<?php echo esc_html( $ch['label'] ); ?>
			<svg width="12" height="12" viewBox="0 0 24 24" aria-hidden="true">
				<path d="M6 6l12 12M18 6L6 18" stroke="#4E1880" stroke-width="2.4" stroke-linecap="round"></path>
			</svg>
		</button>
		<?php
	endforeach;
	?>
	<button type="button" class="cedra-clear-all js-cedra-reset-filters">
		<?php esc_html_e( 'Clear all', 'cedraparts' ); ?>
	</button>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render Option 2 Numbered Pagination HTML (`Page 1 of 264` + page buttons).
 *
 * @param int $current_page Current 1-based page.
 * @param int $max_pages    Total pages.
 * @return string HTML markup.
 */
function cedra_get_pagination_html( $current_page, $max_pages ) {
	if ( $max_pages <= 1 ) {
		return '';
	}

	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );

	// Compute visible page numbers with ellipsis.
	$pages = array( 1 );
	$start = max( 2, $current_page - 1 );
	$end   = min( $max_pages - 1, max( 3, $current_page + 1 ) );

	if ( $start > 2 ) {
		$pages[] = '...';
	}
	for ( $i = $start; $i <= $end; $i++ ) {
		$pages[] = $i;
	}
	if ( $end < $max_pages - 1 ) {
		$pages[] = '...';
	}
	if ( $max_pages > 1 ) {
		$pages[] = $max_pages;
	}

	ob_start();
	?>
	<nav class="cedra-pagination" aria-label="<?php esc_attr_e( 'Pagination', 'cedraparts' ); ?>">
		<span class="cedra-pagination__status">
			<?php
			echo esc_html(
				sprintf(
					/* translators: 1: current page, 2: total pages */
					__( 'Page %1$s of %2$s', 'cedraparts' ),
					number_format_i18n( $current_page ),
					number_format_i18n( $max_pages )
				)
			);
			?>
		</span>

		<div class="cedra-pagination__btns">
			<?php if ( $current_page > 1 ) : ?>
				<a
					href="<?php echo esc_url( add_query_arg( 'cpage', $current_page - 1, $shop_url ) . '#results' ); ?>"
					class="cedra-page-btn cedra-page-btn--step js-catalog-page"
					data-page="<?php echo esc_attr( (string) ( $current_page - 1 ) ); ?>"
				>
					<span aria-hidden="true">←</span> <?php esc_html_e( 'Prev', 'cedraparts' ); ?>
				</a>
			<?php endif; ?>

			<?php foreach ( $pages as $p ) : ?>
				<?php if ( '...' === $p ) : ?>
					<span class="cedra-page-ellipsis">…</span>
				<?php elseif ( (int) $p === $current_page ) : ?>
					<a
						href="<?php echo esc_url( add_query_arg( 'cpage', $p, $shop_url ) . '#results' ); ?>"
						aria-current="page"
						class="cedra-page-btn is-current js-catalog-page"
						data-page="<?php echo esc_attr( (string) $p ); ?>"
					>
						<?php echo esc_html( number_format_i18n( (int) $p ) ); ?>
					</a>
				<?php else : ?>
					<a
						href="<?php echo esc_url( add_query_arg( 'cpage', $p, $shop_url ) . '#results' ); ?>"
						class="cedra-page-btn js-catalog-page"
						data-page="<?php echo esc_attr( (string) $p ); ?>"
					>
						<?php echo esc_html( number_format_i18n( (int) $p ) ); ?>
					</a>
				<?php endif; ?>
			<?php endforeach; ?>

			<?php if ( $current_page < $max_pages ) : ?>
				<a
					href="<?php echo esc_url( add_query_arg( 'cpage', $current_page + 1, $shop_url ) . '#results' ); ?>"
					class="cedra-page-btn cedra-page-btn--step js-catalog-page"
					data-page="<?php echo esc_attr( (string) ( $current_page + 1 ) ); ?>"
				>
					<?php esc_html_e( 'Next', 'cedraparts' ); ?> <span aria-hidden="true">→</span>
				</a>
			<?php endif; ?>
		</div>
	</nav>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render Option 2 Empty State dashed card when no parts match active filters.
 *
 * @param string $search_q Optional search query to prefill cross-reference link.
 * @return string HTML markup.
 */
function cedra_get_empty_catalog_html( $search_q = '' ) {
	$quote_url = cedra_get_option( 'quote_url' );
	if ( '' !== $search_q ) {
		$quote_url = add_query_arg( 'part', rawurlencode( $search_q ), $quote_url );
	}
	ob_start();
	?>
	<div class="cedra-no-results">
		<span class="cedra-no-results__title"><?php esc_html_e( 'No parts match these filters', 'cedraparts' ); ?></span>
		<p class="cedra-no-results__desc">
			<?php esc_html_e( "Try removing a filter or searching a different part number. Still stuck? Send us the OEM number and we'll cross-reference it for you.", 'cedraparts' ); ?>
		</p>
		<div class="cedra-no-results__actions">
			<button type="button" class="cedra-btn cedra-btn--outline js-cedra-reset-filters">
				<?php esc_html_e( 'Reset filters', 'cedraparts' ); ?>
			</button>
			<a href="<?php echo esc_url( $quote_url ); ?>" class="cedra-btn cedra-btn--accent">
				<?php esc_html_e( 'Cross-reference a part', 'cedraparts' ); ?>
			</a>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * AJAX Handler: Multi-Facet Filter, Sort, Search & Paginate Catalog Products.
 */
function cedra_ajax_filter_catalog() {
	check_ajax_referer( 'cedra_nonce', 'nonce' );

	$cats   = isset( $_POST['cats'] ) ? cedra_parse_slug_list( wp_unslash( $_POST['cats'] ) ) : array();
	$brands = isset( $_POST['brands'] ) ? cedra_parse_slug_list( wp_unslash( $_POST['brands'] ) ) : array();
	$price  = isset( $_POST['price'] ) ? sanitize_key( wp_unslash( $_POST['price'] ) ) : 'any';
	$q      = isset( $_POST['q'] ) ? sanitize_text_field( wp_unslash( $_POST['q'] ) ) : '';
	$sort   = isset( $_POST['sort'] ) ? sanitize_key( wp_unslash( $_POST['sort'] ) ) : 'featured';
	$page   = isset( $_POST['page'] ) ? max( 1, (int) $_POST['page'] ) : 1;

	$volts = array();
	if ( isset( $_POST['volts'] ) && '' !== $_POST['volts'] ) {
		$raw_volts = explode( ',', sanitize_text_field( wp_unslash( $_POST['volts'] ) ) );
		foreach ( $raw_volts as $rv ) {
			$rv = trim( $rv );
			if ( in_array( $rv, array( '12 V', '24 V' ), true ) ) {
				$volts[] = $rv;
			}
		}
	}

	if ( ! in_array( $price, array( 'any', 'u250', '250', '500', '1000' ), true ) ) {
		$price = 'any';
	}

	$state = array(
		'cats'   => $cats,
		'brands' => $brands,
		'volts'  => $volts,
		'price'  => $price,
		'q'      => $q,
		'sort'   => $sort,
		'page'   => $page,
	);

	$query     = cedra_query_catalog_products( $state );
	$found     = (int) $query->found_posts;
	$max_pages = max( 1, (int) $query->max_num_pages );
	$chips     = cedra_get_active_filter_chips( $state );

	ob_start();
	if ( $query->have_posts() ) {
		echo '<div class="cedra-pgrid">';
		while ( $query->have_posts() ) {
			$query->the_post();
			$product = wc_get_product( get_the_ID() );
			if ( $product ) {
				cedra_render_product_card( $product );
			}
		}
		echo '</div>';
		wp_reset_postdata();
	} else {
		echo cedra_get_empty_catalog_html( $q ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	$results_html = (string) ob_get_clean();

	wp_send_json_success(
		array(
			'resultsHtml'    => $results_html,
			'paginationHtml' => $found > 0 ? cedra_get_pagination_html( $page, $max_pages ) : '',
			'chipsHtml'      => cedra_render_filter_chips_html( $chips ),
			'activeCount'    => count( $chips ),
			'found'          => $found,
			'foundFormatted' => number_format_i18n( $found ),
			'resultNoun'     => 1 === $found ? __( 'result', 'cedraparts' ) : __( 'results', 'cedraparts' ),
			'page'           => $page,
			'maxPages'       => $max_pages,
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
 * Keep header cart badge synchronized with native WooCommerce AJAX cart fragments.
 *
 * @param array<string, string> $fragments Cart HTML fragments.
 * @return array<string, string>
 */
function cedra_cart_count_fragments( $fragments ) {
	if ( function_exists( 'WC' ) && WC()->cart ) {
		$count = (int) WC()->cart->get_cart_contents_count();
		$fragments['.js-cedra-cart-count'] = '<span class="cedra-header__badge js-cedra-cart-count">' . esc_html( (string) $count ) . '</span>';
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
