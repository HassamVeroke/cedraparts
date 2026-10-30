<?php
/**
 * Reusable Template Tags & UI Renderers
 *
 * @package CedraParts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compute initial Eastern Time (America/Toronto) Open/Closed status.
 * Client-side JS also keeps this live in the visitor's browser.
 *
 * @return array{open: bool, label: string, dot: string}
 */
function cedra_get_et_status() {
	try {
		$tz   = new DateTimeZone( 'America/Toronto' );
		$now  = new DateTime( 'now', $tz );
		$day  = (int) $now->format( 'N' ); // 1 (Mon) through 7 (Sun).
		$hour = (int) $now->format( 'G' ); // 0-23.
		$open = ( $day >= 1 && $day <= 5 && $hour >= 9 && $hour < 17 );
	} catch ( Exception $e ) {
		$open = false;
	}

	return array(
		'open'  => $open,
		'label' => $open ? __( 'Open now', 'cedraparts' ) : __( 'Closed now', 'cedraparts' ),
		'dot'   => $open ? '#5BD69A' : '#F0A35E',
	);
}

/**
 * Return the list of Authorized Canadian Distributor logos bundled in the theme.
 *
 * @return array<int, array{src: string, alt: string, h: int}>
 */
function cedra_get_distributor_logos() {
	$base = get_template_directory_uri() . '/assets/images/';
	return array(
		array(
			'src' => $base . 'logo-maxiforce.webp',
			'alt' => 'MaxiForce',
			'h'   => 30,
		),
		array(
			'src' => $base . 'logo-dayco.webp',
			'alt' => 'Dayco',
			'h'   => 34,
		),
		array(
			'src' => $base . 'logo-kmp.webp',
			'alt' => 'KMP Brand',
			'h'   => 40,
		),
		array(
			'src' => $base . 'logo-slp.webp',
			'alt' => 'SLP',
			'h'   => 34,
		),
		array(
			'src' => $base . 'logo-ipd.webp',
			'alt' => 'IPD',
			'h'   => 44,
		),
		array(
			'src' => $base . 'logo-fleetguard.webp',
			'alt' => 'Fleetguard',
			'h'   => 42,
		),
	);
}

/**
 * Clean a product card title by optionally stripping a redundant trailing `(SKU)` suffix.
 *
 * @param WC_Product $product Product object.
 * @return string Cleaned display title.
 */
function cedra_get_clean_product_title( $product ) {
	$title = html_entity_decode( wp_strip_all_tags( $product->get_name() ), ENT_QUOTES, 'UTF-8' );
	if ( ! cedra_get_option( 'strip_trailing_sku' ) ) {
		return $title;
	}

	$sku = trim( (string) $product->get_sku() );
	// Remove trailing parenthetical part number e.g. " (9476-01)" when SKU badge is already displayed.
	if ( preg_match( '/\s*\(([A-Za-z0-9\-_\/\.]+)\)\s*$/', $title, $m ) ) {
		$paren = $m[1];
		if ( '' === $sku || false !== stripos( $sku, $paren ) || false !== stripos( $paren, $sku ) || strlen( $paren ) >= 4 ) {
			$cleaned = trim( substr( $title, 0, -strlen( $m[0] ) ) );
			if ( '' !== $cleaned ) {
				return $cleaned;
			}
		}
	}

	return $title;
}

/**
 * Check if a product has a real photograph (not empty and not the "image_not_available" placeholder).
 *
 * @param WC_Product $product Product object.
 * @return int|false Valid image attachment ID, or false if unavailable/placeholder.
 */
function cedra_get_real_product_image_id( $product ) {
	$image_id = (int) $product->get_image_id();
	if ( ! $image_id ) {
		return false;
	}

	$src = wp_get_attachment_image_url( $image_id, 'woocommerce_thumbnail' );
	if ( ! $src ) {
		return false;
	}

	if ( cedra_get_option( 'use_svg_fallback' ) ) {
		$lower = strtolower( $src );
		if ( str_contains( $lower, 'image_not_available' ) || str_contains( $lower, 'woocommerce-placeholder' ) ) {
			return false;
		}
	}

	return $image_id;
}

/**
 * Return artwork HTML (real image or technical vector illustration) for a product.
 *
 * @param WC_Product $product Product object.
 * @param string     $size    Image size name.
 * @return string HTML markup.
 */
function cedra_get_product_artwork_html( $product, $size = 'woocommerce_thumbnail' ) {
	$image_id = cedra_get_real_product_image_id( $product );

	if ( $image_id ) {
		return wp_get_attachment_image(
			$image_id,
			$size,
			false,
			array(
				'class'   => 'cedra-product-photo',
				'loading' => 'lazy',
				'alt'     => esc_attr( cedra_get_clean_product_title( $product ) ),
			)
		);
	}

	$kind = cedra_detect_product_kind( $product );
	return cedra_get_technical_svg( $kind );
}

/**
 * Render a single Option 2 Frosted-Glass Product Card.
 *
 * @param WC_Product $product Product instance.
 * @param int        $index   1-based sequential display index.
 * @return void
 */
function cedra_render_product_card( $product, $index = 1 ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	$product_id  = $product->get_id();
	$permalink   = get_permalink( $product_id );
	$clean_title = cedra_get_clean_product_title( $product );
	$sku         = trim( (string) $product->get_sku() );
	$num_label   = str_pad( (string) $index, 2, '0', STR_PAD_LEFT );
	$price_html  = $product->get_price_html();
	$can_ajax    = $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock();
	?>
	<article class="card glass cedra-card" data-product-id="<?php echo esc_attr( (string) $product_id ); ?>">
		<a href="<?php echo esc_url( $permalink ); ?>" class="cedra-card__stage">
			<?php if ( '' !== $sku ) : ?>
				<span class="cedra-card__sku"><?php echo esc_html( $sku ); ?></span>
			<?php endif; ?>
			<span class="cedra-card__num"><?php echo esc_html( $num_label ); ?></span>
			<div class="art cedra-card__art">
				<?php echo cedra_get_product_artwork_html( $product, 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</a>
		<div class="cedra-card__meta">
			<div class="cedra-card__info">
				<a href="<?php echo esc_url( $permalink ); ?>" class="cedra-card__title"><?php echo esc_html( $clean_title ); ?></a>
				<span class="cedra-card__price">
					<?php
					if ( '' !== $price_html ) {
						echo wp_kses_post( $price_html );
					} else {
						esc_html_e( 'Request Quote', 'cedraparts' );
					}
					?>
				</span>
			</div>
			<?php if ( $can_ajax ) : ?>
				<button
					type="button"
					class="plus cedra-card__plus js-cedra-add-to-cart"
					data-product-id="<?php echo esc_attr( (string) $product_id ); ?>"
					data-product-name="<?php echo esc_attr( $clean_title ); ?>"
					aria-label="<?php echo esc_attr( sprintf( __( 'Add %s to cart', 'cedraparts' ), $clean_title ) ); ?>"
				>
					<svg class="cedra-icon-plus" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
						<path d="M12 4v16M4 12h16" stroke="#FFFFFF" stroke-width="2.2" stroke-linecap="round"></path>
					</svg>
					<svg class="cedra-icon-check" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
						<path d="M5 13l4 4L19 7" fill="none" stroke="#FFFFFF" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"></path>
					</svg>
				</button>
			<?php else : ?>
				<a
					href="<?php echo esc_url( $permalink ); ?>"
					class="plus cedra-card__plus"
					aria-label="<?php echo esc_attr( sprintf( __( 'View %s details', 'cedraparts' ), $clean_title ) ); ?>"
				>
					<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
						<path d="M7 17L17 7M9 7h8v8" fill="none" stroke="#FFFFFF" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path>
					</svg>
				</a>
			<?php endif; ?>
		</div>
	</article>
	<?php
}

/**
 * Render the interior HTML of the Slide-Over Frosted-Glass Mini-Cart Drawer.
 *
 * @return string HTML markup.
 */
function cedra_get_mini_cart_drawer_inner_html() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '';
	}

	ob_start();
	$items    = WC()->cart->get_cart();
	$count    = WC()->cart->get_cart_contents_count();
	$subtotal = WC()->cart->get_cart_subtotal();
	$cart_url = wc_get_cart_url();
	$chk_url  = wc_get_checkout_url();
	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
	?>
	<div class="cedra-drawer__inner" data-cart-count="<?php echo esc_attr( (string) $count ); ?>">
		<div class="cedra-drawer__head">
			<div>
				<span class="cedra-eyebrow-mono"><?php esc_html_e( 'YOUR ORDER', 'cedraparts' ); ?></span>
				<h2 class="cedra-drawer__title">
					<?php esc_html_e( 'Parts Cart', 'cedraparts' ); ?>
					<span class="cedra-serif-italic">(<?php echo esc_html( (string) $count ); ?>)</span>
				</h2>
			</div>
			<button type="button" class="cedra-drawer__close js-close-cart-drawer" aria-label="<?php esc_attr_e( 'Close cart drawer', 'cedraparts' ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
					<path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
				</svg>
			</button>
		</div>

		<?php if ( empty( $items ) ) : ?>
			<div class="cedra-drawer__empty">
				<div class="cedra-drawer__empty-icon glass">
					<svg width="28" height="28" viewBox="0 0 24 24" aria-hidden="true">
						<path d="M5 8h14l-1.2 11.2a1.5 1.5 0 0 1-1.5 1.3H7.7a1.5 1.5 0 0 1-1.5-1.3z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"></path>
						<path d="M9 8V6a3 3 0 0 1 6 0v2" fill="none" stroke="currentColor" stroke-width="1.6"></path>
					</svg>
				</div>
				<h3><?php esc_html_e( 'Your parts cart is empty', 'cedraparts' ); ?></h3>
				<p><?php esc_html_e( 'Search by OEM part number or browse our aftermarket power generation catalog.', 'cedraparts' ); ?></p>
				<a href="<?php echo esc_url( $shop_url ); ?>" class="cedra-btn cedra-btn--dark js-close-cart-drawer">
					<?php esc_html_e( 'Browse Catalog', 'cedraparts' ); ?>
				</a>
			</div>
		<?php else : ?>
			<div class="cedra-drawer__items">
				<?php
				foreach ( $items as $cart_item_key => $cart_item ) :
					$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
					if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) {
						continue;
					}
					$item_name  = cedra_get_clean_product_title( $_product );
					$item_sku   = $_product->get_sku();
					$item_price = WC()->cart->get_product_price( $_product );
					$item_link  = $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '';
					$qty        = (int) $cart_item['quantity'];
					?>
					<div class="cedra-drawer__item glass" data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>">
						<a href="<?php echo esc_url( $item_link ? $item_link : '#' ); ?>" class="cedra-drawer__thumb">
							<?php echo cedra_get_product_artwork_html( $_product, 'woocommerce_gallery_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
						<div class="cedra-drawer__item-body">
							<?php if ( $item_sku ) : ?>
								<span class="cedra-drawer__item-sku"><?php echo esc_html( $item_sku ); ?></span>
							<?php endif; ?>
							<a href="<?php echo esc_url( $item_link ? $item_link : '#' ); ?>" class="cedra-drawer__item-name">
								<?php echo esc_html( $item_name ); ?>
							</a>
							<div class="cedra-drawer__item-row">
								<span class="cedra-drawer__item-price"><?php echo wp_kses_post( $item_price ); ?></span>
								<div class="cedra-qty-pill">
									<button type="button" class="js-drawer-qty" data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>" data-qty="<?php echo esc_attr( (string) max( 0, $qty - 1 ) ); ?>" aria-label="<?php esc_attr_e( 'Decrease quantity', 'cedraparts' ); ?>">−</button>
									<span><?php echo esc_html( (string) $qty ); ?></span>
									<button type="button" class="js-drawer-qty" data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>" data-qty="<?php echo esc_attr( (string) ( $qty + 1 ) ); ?>" aria-label="<?php esc_attr_e( 'Increase quantity', 'cedraparts' ); ?>">+</button>
								</div>
							</div>
						</div>
						<button type="button" class="cedra-drawer__remove js-drawer-qty" data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>" data-qty="0" aria-label="<?php esc_attr_e( 'Remove item', 'cedraparts' ); ?>">
							<svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true">
								<path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
							</svg>
						</button>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="cedra-drawer__foot glass-strong">
				<div class="cedra-drawer__subtotal">
					<span><?php esc_html_e( 'Subtotal', 'cedraparts' ); ?></span>
					<strong><?php echo wp_kses_post( $subtotal ); ?></strong>
				</div>
				<p class="cedra-drawer__note"><?php esc_html_e( 'Shipping & taxes calculated at checkout. Ships from Beamsville, ON.', 'cedraparts' ); ?></p>
				<div class="cedra-drawer__actions">
					<a href="<?php echo esc_url( $cart_url ); ?>" class="cedra-btn cedra-btn--glass">
						<?php esc_html_e( 'View Cart', 'cedraparts' ); ?>
					</a>
					<a href="<?php echo esc_url( $chk_url ); ?>" class="cedra-btn cedra-btn--accent">
						<?php esc_html_e( 'Checkout', 'cedraparts' ); ?> <span aria-hidden="true">↗</span>
					</a>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Retrieve primary navigation items (uses WP Menu `primary` if assigned, otherwise Option 2 defaults).
 *
 * @return array<int, array{label: string, url: string, active: bool}>
 */
function cedra_get_nav_items() {
	$shop_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
	$is_catalog = ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) || is_front_page() || is_home() || is_search();

	$locations = get_nav_menu_locations();
	if ( ! empty( $locations['primary'] ) ) {
		$menu_items = wp_get_nav_menu_items( $locations['primary'] );
		if ( ! empty( $menu_items ) && is_array( $menu_items ) ) {
			$out         = array();
			$current_url = trailingslashit( home_url( add_query_arg( array() ) ) );
			foreach ( $menu_items as $item ) {
				if ( (int) $item->menu_item_parent !== 0 ) {
					continue;
				}
				$item_url = $item->url;
				$active   = ( trailingslashit( $item_url ) === $current_url ) || in_array( 'current-menu-item', (array) $item->classes, true );
				$out[]    = array(
					'label'  => $item->title,
					'url'    => $item_url,
					'active' => $active,
				);
			}
			if ( ! empty( $out ) ) {
				return $out;
			}
		}
	}

	return array(
		array(
			'label'  => __( 'Home', 'cedraparts' ),
			'url'    => cedra_get_option( 'home_url' ),
			'active' => false,
		),
		array(
			'label'  => __( 'Products', 'cedraparts' ),
			'url'    => $shop_url,
			'active' => $is_catalog,
		),
		array(
			'label'  => __( 'About Us', 'cedraparts' ),
			'url'    => cedra_get_option( 'about_url' ),
			'active' => false,
		),
		array(
			'label'  => __( 'Contact', 'cedraparts' ),
			'url'    => cedra_get_option( 'contact_url' ),
			'active' => false,
		),
	);
}

