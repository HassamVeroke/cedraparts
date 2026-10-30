<?php
/**
 * 3-Column Services Strip (`#quote` & `#support` — Option 2 Design)
 *
 * @package CedraParts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$quote_url  = cedra_get_option( 'quote_url' );
$phone_disp = cedra_get_option( 'sales_phone_display' );
$phone_href = cedra_get_option( 'sales_phone_href' );
?>
<!-- Services strip -->
<section id="quote" class="cedra-svc-section">
	<div class="wrap svc">
		<div class="svc__item svc__item--first">
			<svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" class="svc__icon">
				<circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="1.7"></circle>
				<path d="M20 20l-4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"></path>
			</svg>
			<div>
				<div class="svc__title"><?php esc_html_e( "Can't find your part?", 'cedraparts' ); ?></div>
				<p class="svc__desc"><?php esc_html_e( 'We cross-reference any part number across all major genset brands.', 'cedraparts' ); ?></p>
				<a href="<?php echo esc_url( $quote_url ); ?>" class="svc__link"><?php esc_html_e( 'Send a part number →', 'cedraparts' ); ?></a>
			</div>
		</div>

		<div class="svc__item svc__item--mid">
			<svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" class="svc__icon">
				<path d="M3 8l9-5 9 5-9 5-9-5zM3 8v8l9 5 9-5V8M12 13v8" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"></path>
			</svg>
			<div>
				<div class="svc__title"><?php esc_html_e( 'Custom PM kits & bulk orders', 'cedraparts' ); ?></div>
				<p class="svc__desc"><?php esc_html_e( 'Volume pricing for service companies and facility managers.', 'cedraparts' ); ?></p>
				<a href="<?php echo esc_url( $quote_url ); ?>" class="svc__link"><?php esc_html_e( 'Request a quote →', 'cedraparts' ); ?></a>
			</div>
		</div>

		<div id="support" class="svc__item svc__item--last">
			<svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" class="svc__icon">
				<path d="M13 2L4 14h7l-1 8 9-12h-7l1-8z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"></path>
			</svg>
			<div>
				<div class="svc__title"><?php esc_html_e( 'Emergency parts support', 'cedraparts' ); ?></div>
				<p class="svc__desc"><?php esc_html_e( 'Priority service for critical infrastructure clients.', 'cedraparts' ); ?></p>
				<a href="tel:<?php echo esc_attr( $phone_href ); ?>" class="svc__link cedra-mono"><?php echo esc_html( $phone_disp ); ?></a>
			</div>
		</div>
	</div>
</section>
