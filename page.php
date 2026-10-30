<?php
/**
 * Standard & WooCommerce Page Template (Cart, Checkout, My Account, & Content Pages)
 *
 * @package CedraParts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$is_cart  = function_exists( 'is_cart' ) && is_cart();
$is_chk   = function_exists( 'is_checkout' ) && is_checkout();
$is_acct  = function_exists( 'is_account_page' ) && is_account_page();

$eyebrow = __( 'CEDRA PARTS', 'cedraparts' );
if ( $is_cart ) {
	$eyebrow = __( 'ORDER SUMMARY', 'cedraparts' );
} elseif ( $is_chk ) {
	$eyebrow = __( 'SECURE CHECKOUT', 'cedraparts' );
} elseif ( $is_acct ) {
	$eyebrow = __( 'CUSTOMER PORTAL', 'cedraparts' );
}

while ( have_posts() ) :
	the_post();
	?>
	<section class="cedra-page-section">
		<div class="wrap">
			<div class="cedra-page-head">
				<div class="glass cedra-eyebrow-pill">
					<span class="cedra-pulse-dot" aria-hidden="true"></span>
					<?php echo esc_html( $eyebrow ); ?>
				</div>
				<div class="cedra-page-head__row">
					<h1 class="cedra-page-title"><?php the_title(); ?></h1>
					<a href="<?php echo esc_url( $shop_url ); ?>" class="glass pill cedra-back-catalog">
						← <?php esc_html_e( 'Continue browsing parts', 'cedraparts' ); ?>
					</a>
				</div>
			</div>

			<div class="glass-strong cedra-page-surface">
				<?php the_content(); ?>
			</div>
		</div>
	</section>
	<?php
endwhile;

if ( ! $is_chk ) {
	get_template_part( 'template-parts/help-band' );
}

get_footer();
