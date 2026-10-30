<?php
/**
 * WooCommerce Single Product Template (Option 2 Frosted-Glass Design)
 *
 * @package CedraParts
 * @version 1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$shop_url    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$quote_url   = cedra_get_option( 'quote_url' );
$contact_url = cedra_get_option( 'contact_url' );

while ( have_posts() ) :
	the_post();
	global $product;

	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		$product = wc_get_product( get_the_ID() );
	}
	if ( ! $product ) {
		continue;
	}

	$product_id   = $product->get_id();
	$sku          = trim( (string) $product->get_sku() );
	$clean_title  = cedra_get_clean_product_title( $product );
	$price_html   = $product->get_price_html();
	$in_stock     = $product->is_in_stock();
	$is_simple    = $product->is_type( 'simple' ) && $product->is_purchasable() && $in_stock;
	$short_desc   = $product->get_short_description();
	$full_desc    = $product->get_description();
	$main_img_id  = cedra_get_real_product_image_id( $product );
	$gallery_ids  = $product->get_gallery_image_ids();
	$all_img_ids  = array();
	if ( $main_img_id ) {
		$all_img_ids[] = $main_img_id;
	}
	if ( ! empty( $gallery_ids ) ) {
		foreach ( $gallery_ids as $gid ) {
			$gid = (int) $gid;
			if ( $gid && ! in_array( $gid, $all_img_ids, true ) ) {
				$all_img_ids[] = $gid;
			}
		}
	}

	// Primary category (excluding catch-all parts-catalog).
	$cat_terms   = get_the_terms( $product_id, 'product_cat' );
	$primary_cat = null;
	$cat_names   = array();
	if ( is_array( $cat_terms ) ) {
		foreach ( $cat_terms as $ct ) {
			if ( in_array( $ct->slug, array( 'parts-catalog', 'uncategorized' ), true ) ) {
				continue;
			}
			if ( ! $primary_cat ) {
				$primary_cat = $ct;
			}
			$cat_names[] = html_entity_decode( $ct->name, ENT_QUOTES, 'UTF-8' );
		}
	}

	// Brand term.
	$brand_tax  = cedra_get_brand_taxonomy();
	$brand_term = null;
	if ( $brand_tax ) {
		$bterms = get_the_terms( $product_id, $brand_tax );
		if ( is_array( $bterms ) && ! empty( $bterms ) ) {
			$brand_term = reset( $bterms );
		}
	}

	$quote_with_part = add_query_arg(
		'part',
		rawurlencode( $sku ? $sku : $clean_title ),
		$quote_url
	);
	?>

	<section class="cedra-pdp-section">
		<div class="wrap">
			<?php if ( function_exists( 'woocommerce_output_all_notices' ) ) : ?>
				<div class="cedra-wc-notices">
					<?php woocommerce_output_all_notices(); ?>
				</div>
			<?php endif; ?>

			<!-- Breadcrumb Pill -->
			<nav class="cedra-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'cedraparts' ); ?>">
				<div class="glass cedra-breadcrumb__pill">
					<a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Products', 'cedraparts' ); ?></a>
					<?php if ( $primary_cat ) : ?>
						<span aria-hidden="true">/</span>
						<a href="<?php echo esc_url( add_query_arg( 'cat', $primary_cat->slug, $shop_url ) ); ?>">
							<?php echo esc_html( html_entity_decode( $primary_cat->name, ENT_QUOTES, 'UTF-8' ) ); ?>
						</a>
					<?php endif; ?>
					<span aria-hidden="true">/</span>
					<span class="cedra-breadcrumb__current"><?php echo esc_html( $sku ? $sku : $clean_title ); ?></span>
				</div>
			</nav>

			<!-- 2-Column Product Hero -->
			<div class="cedra-pdp">
				<!-- Media Column -->
				<div class="glass-strong cedra-pdp__media">
					<div class="cedra-pdp__stage">
						<?php if ( '' !== $sku ) : ?>
							<span class="cedra-card__sku"><?php echo esc_html( 'SKU · ' . $sku ); ?></span>
						<?php endif; ?>
						<?php if ( $brand_term ) : ?>
							<a
								href="<?php echo esc_url( add_query_arg( 'brand', $brand_term->slug, $shop_url ) ); ?>"
								class="cedra-pdp__brand-chip"
							>
								<?php echo esc_html( html_entity_decode( $brand_term->name, ENT_QUOTES, 'UTF-8' ) ); ?>
							</a>
						<?php endif; ?>

						<div class="cedra-pdp__main-art js-pdp-main-art">
							<?php
							if ( $main_img_id ) {
								echo wp_get_attachment_image(
									$main_img_id,
									'woocommerce_single',
									false,
									array(
										'class'         => 'cedra-product-photo cedra-pdp__photo js-pdp-main-img',
										'fetchpriority' => 'high',
										'alt'           => esc_attr( $clean_title ),
									)
								);
							} else {
								echo cedra_get_technical_svg( cedra_detect_product_kind( $product ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							?>
						</div>
					</div>

					<?php if ( count( $all_img_ids ) > 1 ) : ?>
						<div class="cedra-pdp__thumbs" role="list" aria-label="<?php esc_attr_e( 'Product gallery thumbnails', 'cedraparts' ); ?>">
							<?php foreach ( $all_img_ids as $t_idx => $img_id ) : ?>
								<?php
								$full_src = wp_get_attachment_image_url( $img_id, 'woocommerce_single' );
								$srcset   = wp_get_attachment_image_srcset( $img_id, 'woocommerce_single' );
								?>
								<button
									type="button"
									class="cedra-pdp__thumb js-pdp-thumb<?php echo 0 === $t_idx ? ' is-active' : ''; ?>"
									data-full-src="<?php echo esc_url( (string) $full_src ); ?>"
									data-srcset="<?php echo esc_attr( (string) $srcset ); ?>"
									aria-label="<?php echo esc_attr( sprintf( __( 'View image %d', 'cedraparts' ), $t_idx + 1 ) ); ?>"
								>
									<?php echo wp_get_attachment_image( $img_id, 'woocommerce_gallery_thumbnail' ); ?>
								</button>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<!-- Summary / Buy Box Column -->
				<div class="glass-strong cedra-pdp__summary">
					<div class="cedra-pdp__badges">
						<?php if ( $primary_cat ) : ?>
							<a href="<?php echo esc_url( add_query_arg( 'cat', $primary_cat->slug, $shop_url ) ); ?>" class="glass cedra-eyebrow-pill cedra-eyebrow-pill--sm">
								<span class="cedra-pulse-dot" aria-hidden="true"></span>
								<?php echo esc_html( html_entity_decode( $primary_cat->name, ENT_QUOTES, 'UTF-8' ) ); ?>
							</a>
						<?php endif; ?>

						<span class="glass cedra-stock-pill<?php echo $in_stock ? ' is-instock' : ' is-out'; ?>">
							<span class="cedra-stock-pill__dot" aria-hidden="true"></span>
							<?php
							if ( $in_stock ) {
								esc_html_e( 'In stock · Beamsville, ON', 'cedraparts' );
							} else {
								esc_html_e( 'Contact for availability', 'cedraparts' );
							}
							?>
						</span>
					</div>

					<h1 class="cedra-pdp__title"><?php echo esc_html( $clean_title ); ?></h1>

					<?php if ( '' !== $sku || $brand_term ) : ?>
						<div class="cedra-pdp__partline">
							<?php if ( '' !== $sku ) : ?>
								<span><?php esc_html_e( 'Part No.', 'cedraparts' ); ?> <strong class="cedra-mono"><?php echo esc_html( $sku ); ?></strong></span>
							<?php endif; ?>
							<?php if ( $brand_term ) : ?>
								<span><?php esc_html_e( 'Manufacturer:', 'cedraparts' ); ?> <strong><?php echo esc_html( html_entity_decode( $brand_term->name, ENT_QUOTES, 'UTF-8' ) ); ?></strong></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<div class="cedra-pdp__price-box glass">
						<div class="cedra-pdp__price">
							<?php
							if ( '' !== $price_html ) {
								echo wp_kses_post( $price_html );
							} else {
								esc_html_e( 'Quote on Request', 'cedraparts' );
							}
							?>
						</div>
						<span class="cedra-pdp__currency"><?php echo esc_html( get_woocommerce_currency() . ' · Excl. tax & shipping' ); ?></span>
					</div>

					<?php if ( '' !== trim( $short_desc ) || '' !== trim( $full_desc ) ) : ?>
						<div class="cedra-pdp__excerpt">
							<?php
							$summary_text = '' !== trim( $short_desc ) ? $short_desc : wp_trim_words( wp_strip_all_tags( $full_desc ), 38 );
							echo wp_kses_post( wpautop( $summary_text ) );
							?>
						</div>
					<?php endif; ?>

					<?php if ( $is_simple ) : ?>
						<form class="cedra-pdp__cart-form js-pdp-cart-form" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype="multipart/form-data">
							<div class="cedra-pdp__buy-row">
								<div class="glass cedra-pdp__qty">
									<button type="button" class="js-pdp-qty-step" data-step="-1" aria-label="<?php esc_attr_e( 'Decrease quantity', 'cedraparts' ); ?>">−</button>
									<label for="cedra-pdp-qty" class="sr"><?php esc_html_e( 'Quantity', 'cedraparts' ); ?></label>
									<input
										id="cedra-pdp-qty"
										type="number"
										name="quantity"
										value="<?php echo esc_attr( (string) $product->get_min_purchase_quantity() ); ?>"
										min="<?php echo esc_attr( (string) $product->get_min_purchase_quantity() ); ?>"
										<?php if ( $product->get_max_purchase_quantity() > 0 ) : ?>
											max="<?php echo esc_attr( (string) $product->get_max_purchase_quantity() ); ?>"
										<?php endif; ?>
										step="1"
										class="js-pdp-qty-input"
									>
									<button type="button" class="js-pdp-qty-step" data-step="1" aria-label="<?php esc_attr_e( 'Increase quantity', 'cedraparts' ); ?>">+</button>
								</div>

								<button
									type="submit"
									name="add-to-cart"
									value="<?php echo esc_attr( (string) $product_id ); ?>"
									data-product-id="<?php echo esc_attr( (string) $product_id ); ?>"
									class="cedra-btn cedra-btn--accent cedra-btn--lg cedra-pdp__add-btn js-pdp-ajax-add"
								>
									<span><?php esc_html_e( 'Add to Cart', 'cedraparts' ); ?></span>
									<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
										<path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"></path>
									</svg>
								</button>
							</div>
						</form>
					<?php else : ?>
						<div class="cedra-pdp__wc-native-form">
							<?php woocommerce_template_single_add_to_cart(); ?>
						</div>
					<?php endif; ?>

					<div class="cedra-pdp__secondary-actions">
						<a href="<?php echo esc_url( $quote_with_part ); ?>" class="glass cedra-btn cedra-btn--glass">
							<?php esc_html_e( 'Request Bulk / Fleet Quote', 'cedraparts' ); ?> <span aria-hidden="true">↗</span>
						</a>
						<a href="<?php echo esc_url( $contact_url ); ?>" class="cedra-pdp__text-link">
							<?php esc_html_e( 'Need cross-reference verification?', 'cedraparts' ); ?>
						</a>
					</div>

					<div class="cedra-pdp__trust">
						<div class="glass cedra-pdp__trust-item">
							<strong class="cedra-mono"><?php esc_html_e( 'CANADA-WIDE', 'cedraparts' ); ?></strong>
							<span><?php esc_html_e( 'Dispatched from Beamsville, ON', 'cedraparts' ); ?></span>
						</div>
						<div class="glass cedra-pdp__trust-item">
							<strong class="cedra-mono"><?php esc_html_e( 'POWER GEN', 'cedraparts' ); ?></strong>
							<span><?php esc_html_e( 'Industrial genset grade components', 'cedraparts' ); ?></span>
						</div>
					</div>
				</div>
			</div>

			<!-- Specifications & Full Description Bento -->
			<div class="cedra-pdp-details">
				<?php if ( '' !== trim( $full_desc ) ) : ?>
					<div class="glass-strong cedra-pdp-details__card">
						<span class="cedra-eyebrow-mono"><?php esc_html_e( 'TECHNICAL OVERVIEW', 'cedraparts' ); ?></span>
						<h2 class="cedra-pdp-details__heading">
							<?php esc_html_e( 'Part', 'cedraparts' ); ?>
							<span class="cedra-serif-accent"><?php esc_html_e( 'description', 'cedraparts' ); ?></span>
						</h2>
						<div class="cedra-prose">
							<?php echo wp_kses_post( wpautop( $full_desc ) ); ?>
						</div>
					</div>
				<?php endif; ?>

				<div class="glass-strong cedra-pdp-details__card">
					<span class="cedra-eyebrow-mono"><?php esc_html_e( 'SPECIFICATIONS', 'cedraparts' ); ?></span>
					<h2 class="cedra-pdp-details__heading">
						<?php esc_html_e( 'Identification', 'cedraparts' ); ?>
						<span class="cedra-serif-accent"><?php esc_html_e( '& data', 'cedraparts' ); ?></span>
					</h2>
					<dl class="cedra-spec-list">
						<?php if ( '' !== $sku ) : ?>
							<div class="cedra-spec-list__row">
								<dt><?php esc_html_e( 'Part Number / SKU', 'cedraparts' ); ?></dt>
								<dd class="cedra-mono"><?php echo esc_html( $sku ); ?></dd>
							</div>
						<?php endif; ?>
						<?php if ( $brand_term ) : ?>
							<div class="cedra-spec-list__row">
								<dt><?php esc_html_e( 'Brand', 'cedraparts' ); ?></dt>
								<dd><?php echo esc_html( html_entity_decode( $brand_term->name, ENT_QUOTES, 'UTF-8' ) ); ?></dd>
							</div>
						<?php endif; ?>
						<?php if ( ! empty( $cat_names ) ) : ?>
							<div class="cedra-spec-list__row">
								<dt><?php esc_html_e( 'Category', 'cedraparts' ); ?></dt>
								<dd><?php echo esc_html( implode( ', ', $cat_names ) ); ?></dd>
							</div>
						<?php endif; ?>
						<?php if ( $product->has_weight() ) : ?>
							<div class="cedra-spec-list__row">
								<dt><?php esc_html_e( 'Weight', 'cedraparts' ); ?></dt>
								<dd class="cedra-mono"><?php echo esc_html( wc_format_weight( $product->get_weight() ) ); ?></dd>
							</div>
						<?php endif; ?>
						<?php if ( $product->has_dimensions() ) : ?>
							<div class="cedra-spec-list__row">
								<dt><?php esc_html_e( 'Dimensions', 'cedraparts' ); ?></dt>
								<dd class="cedra-mono"><?php echo esc_html( wc_format_dimensions( $product->get_dimensions( false ) ) ); ?></dd>
							</div>
						<?php endif; ?>
						<div class="cedra-spec-list__row">
							<dt><?php esc_html_e( 'Distribution Hub', 'cedraparts' ); ?></dt>
							<dd><?php esc_html_e( 'Beamsville, Ontario, Canada', 'cedraparts' ); ?></dd>
						</div>
					</dl>
				</div>
			</div>

			<!-- Related Parts -->
			<?php
			$related_ids = wc_get_related_products( $product_id, 4 );
			if ( ! empty( $related_ids ) ) :
				?>
				<div class="cedra-related">
					<div class="cedra-catalog__header">
						<h2 class="cedra-catalog__title">
							<?php esc_html_e( 'Related', 'cedraparts' ); ?>
							<span class="cedra-catalog__count"><?php esc_html_e( '— compatible parts', 'cedraparts' ); ?></span>
						</h2>
						<a href="<?php echo esc_url( $shop_url ); ?>" class="glass pill cedra-page-pill">
							<?php esc_html_e( 'VIEW FULL CATALOG →', 'cedraparts' ); ?>
						</a>
					</div>

					<div class="cedra-grid">
						<?php
						$r_idx = 1;
						foreach ( $related_ids as $rel_id ) {
							$rel_product = wc_get_product( $rel_id );
							if ( $rel_product ) {
								cedra_render_product_card( $rel_product, $r_idx );
								++$r_idx;
							}
						}
						?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php
endwhile;

get_template_part( 'template-parts/help-band' );

get_footer();
