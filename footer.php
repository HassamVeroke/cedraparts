<?php
/**
 * Theme Footer: 4-Column Dark Navy Industrial Footer (Option 2 Design)
 *
 * @package CedraParts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shop_url      = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$home_url      = cedra_get_option( 'home_url' );
$about_url     = cedra_get_option( 'about_url' );
$contact_url   = cedra_get_option( 'contact_url' );
$quote_url     = cedra_get_option( 'quote_url' );
$sales_email   = cedra_get_option( 'sales_email' );
$phone_disp    = cedra_get_option( 'sales_phone_display' );
$phone_href    = cedra_get_option( 'sales_phone_href' );
$address_line  = cedra_get_option( 'address_line' );
$hours_line    = cedra_get_option( 'hours_line' );
$hq_line       = cedra_get_option( 'footer_hq_line' );
$linkedin_url  = cedra_get_option( 'linkedin_url' );
$facebook_url  = cedra_get_option( 'facebook_url' );
$instagram_url = cedra_get_option( 'instagram_url' );
$youtube_url   = cedra_get_option( 'youtube_url' );
$logo_src      = CEDRA_THEME_URI . '/assets/images/cedra-logo.webp';
?>
</main>

<!-- Footer -->
<footer id="contact" class="cedra-footer">
	<div class="wrap foot">
		<div class="cedra-footer__brand">
			<img src="<?php echo esc_url( $logo_src ); ?>" alt="<?php esc_attr_e( 'Cedra Parts', 'cedraparts' ); ?>" width="82" height="36" class="cedra-footer__logo">
			<p class="cedra-footer__desc">
				<?php esc_html_e( "Canada's dedicated aftermarket parts specialist, exclusively focused on the Power Generation sector.", 'cedraparts' ); ?>
			</p>
			<div class="cedra-footer__socials">
				<a href="<?php echo esc_url( $linkedin_url ? $linkedin_url : '#linkedin' ); ?>" aria-label="<?php esc_attr_e( 'Cedra Parts on LinkedIn', 'cedraparts' ); ?>" class="cedra-social-btn">
					<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
						<path d="M5 9v10M5 5.5v.5M10 19v-6a3 3 0 0 1 6 0v6M10 9v10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
					</svg>
				</a>
				<a href="<?php echo esc_url( $facebook_url ? $facebook_url : '#facebook' ); ?>" aria-label="<?php esc_attr_e( 'Cedra Parts on Facebook', 'cedraparts' ); ?>" class="cedra-social-btn">
					<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
						<path d="M14 21v-8h3l.5-3.5H14V7.5c0-1 .4-1.7 1.8-1.7H18V2.6c-.4-.1-1.6-.2-2.9-.2-2.9 0-4.6 1.7-4.6 4.8v2.3H7.5V13h3v8" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"></path>
					</svg>
				</a>
				<a href="<?php echo esc_url( $instagram_url ? $instagram_url : '#instagram' ); ?>" aria-label="<?php esc_attr_e( 'Cedra Parts on Instagram', 'cedraparts' ); ?>" class="cedra-social-btn">
					<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
						<rect x="3.5" y="3.5" width="17" height="17" rx="5" fill="none" stroke="currentColor" stroke-width="1.7"></rect>
						<circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.7"></circle>
						<circle cx="17.2" cy="6.8" r="1" fill="currentColor"></circle>
					</svg>
				</a>
				<a href="<?php echo esc_url( $youtube_url ? $youtube_url : '#youtube' ); ?>" aria-label="<?php esc_attr_e( 'Cedra Parts on YouTube', 'cedraparts' ); ?>" class="cedra-social-btn">
					<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
						<rect x="2.5" y="5.5" width="19" height="13" rx="4" fill="none" stroke="currentColor" stroke-width="1.7"></rect>
						<path d="M10 9.5v5l4.5-2.5z" fill="currentColor"></path>
					</svg>
				</a>
			</div>
		</div>

		<div>
			<div class="cedra-footer__heading"><?php esc_html_e( 'Shop', 'cedraparts' ); ?></div>
			<ul class="cedra-footer__list">
				<li><a href="<?php echo esc_url( $shop_url . '#results' ); ?>" class="js-pick-cat" data-cat="all"><?php esc_html_e( 'All parts', 'cedraparts' ); ?></a></li>
				<li><a href="<?php echo esc_url( add_query_arg( 'cats', 'generator-controllers', $shop_url ) . '#results' ); ?>" class="js-pick-cat" data-cat="generator-controllers"><?php esc_html_e( 'Generator Controllers', 'cedraparts' ); ?></a></li>
				<li><a href="<?php echo esc_url( add_query_arg( 'cats', 'battery-chargers', $shop_url ) . '#results' ); ?>" class="js-pick-cat" data-cat="battery-chargers"><?php esc_html_e( 'Battery Chargers', 'cedraparts' ); ?></a></li>
				<li><a href="<?php echo esc_url( add_query_arg( 'cats', 'engine-parts', $shop_url ) . '#results' ); ?>" class="js-pick-cat" data-cat="engine-parts"><?php esc_html_e( 'Engine Parts', 'cedraparts' ); ?></a></li>
				<li><a href="<?php echo esc_url( $shop_url . '#shop' ); ?>" class="js-show-brands"><?php esc_html_e( 'Shop by brand', 'cedraparts' ); ?></a></li>
			</ul>
		</div>

		<div>
			<div class="cedra-footer__heading"><?php esc_html_e( 'Company', 'cedraparts' ); ?></div>
			<ul class="cedra-footer__list">
				<li><a href="<?php echo esc_url( $home_url ); ?>"><?php esc_html_e( 'Home', 'cedraparts' ); ?></a></li>
				<li><a href="<?php echo esc_url( $about_url ); ?>"><?php esc_html_e( 'About Us', 'cedraparts' ); ?></a></li>
				<li><a href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Contact Us', 'cedraparts' ); ?></a></li>
				<li><a href="<?php echo esc_url( $quote_url ); ?>"><?php esc_html_e( 'Get a Quote', 'cedraparts' ); ?></a></li>
			</ul>
		</div>

		<div>
			<div class="cedra-footer__heading"><?php esc_html_e( 'Contact', 'cedraparts' ); ?></div>
			<ul class="cedra-footer__list cedra-footer__list--contact">
				<?php if ( $sales_email ) : ?>
					<li><a href="mailto:<?php echo esc_attr( $sales_email ); ?>"><?php echo esc_html( $sales_email ); ?></a></li>
				<?php endif; ?>
				<?php if ( $phone_disp ) : ?>
					<li><a href="tel:<?php echo esc_attr( $phone_href ); ?>"><?php echo esc_html( $phone_disp ); ?></a></li>
				<?php endif; ?>
				<?php if ( $address_line ) : ?>
					<li><?php echo esc_html( $address_line ); ?></li>
				<?php endif; ?>
				<?php if ( $hours_line ) : ?>
					<li><?php echo esc_html( $hours_line ); ?></li>
				<?php endif; ?>
			</ul>
		</div>
	</div>

	<div class="cedra-footer__bottom">
		<div class="wrap cedra-footer__bottom-inner">
			<span>
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: current year */
						__( '© %s Cedra Parts Inc. All Rights Reserved.', 'cedraparts' ),
						gmdate( 'Y' )
					)
				);
				?>
			</span>
			<span><?php echo esc_html( $hq_line ); ?></span>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
