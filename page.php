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

$eyebrow = __( 'Cedra Parts', 'cedraparts' );
if ( $is_cart ) {
	$eyebrow = __( 'Order summary', 'cedraparts' );
} elseif ( $is_chk ) {
	$eyebrow = __( 'Secure checkout', 'cedraparts' );
} elseif ( $is_acct ) {
	$eyebrow = __( 'Customer portal', 'cedraparts' );
}

while ( have_posts() ) :
	the_post();
	?>
	<section class="cedra-page-section">
		<div class="wrap">
			<div class="cedra-page-head">
				<span class="cedra-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<div class="cedra-page-head__row">
					<h1 class="cedra-page-title"><?php the_title(); ?></h1>
					<a href="<?php echo esc_url( $shop_url . '#results' ); ?>" class="cedra-view-all">
						<span aria-hidden="true">←</span> <?php esc_html_e( 'Continue shopping', 'cedraparts' ); ?>
					</a>
				</div>
			</div>

			<div class="cedra-page-surface">
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
