<?php
/**
 * Theme Footer — Rounded Dark Navy Bento Grid, Live ET Hours, Giant Watermark & Copyright
 *
 * @package CedraParts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sales_email   = cedra_get_option( 'sales_email' );
$phone_display = cedra_get_option( 'sales_phone_display' );
$phone_href    = cedra_get_option( 'sales_phone_href' );
$quote_url     = cedra_get_option( 'quote_url' );
$home_url      = cedra_get_option( 'home_url' );
$about_url     = cedra_get_option( 'about_url' );
$contact_url   = cedra_get_option( 'contact_url' );
$shop_url      = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$address_line1 = cedra_get_option( 'address_line1' );
$address_line2 = cedra_get_option( 'address_line2' );
$hours_weekday = cedra_get_option( 'hours_weekday' );
$hours_weekend = cedra_get_option( 'hours_weekend' );
$hq_line       = cedra_get_option( 'footer_hq_line' );
$et_status     = cedra_get_et_status();
$logo_url      = CEDRA_THEME_URI . '/assets/images/cedra-logo.webp';
$custom_logo   = get_theme_mod( 'custom_logo' );
if ( $custom_logo ) {
	$custom_logo_src = wp_get_attachment_image_url( $custom_logo, 'full' );
	if ( $custom_logo_src ) {
		$logo_url = $custom_logo_src;
	}
}
?>
	</main><!-- #main-content -->

	<!-- Footer -->
	<footer id="contact" class="cedra-footer">
		<div class="orb cedra-footer__orb" aria-hidden="true"></div>
		<div class="wrap cedra-footer__inner">

			<!-- Footer headline -->
			<div class="cedra-footer__hero">
				<h2 class="h1 cedra-footer__headline">
					<?php esc_html_e( 'Keeping critical', 'cedraparts' ); ?><br>
					<?php esc_html_e( 'power', 'cedraparts' ); ?>
					<span class="cedra-footer__serif"><?php esc_html_e( 'ready.', 'cedraparts' ); ?></span>
				</h2>
				<div class="cedra-footer__cta-group">
					<a href="<?php echo esc_url( $quote_url ); ?>" class="cedra-btn cedra-btn--accent cedra-btn--lg">
						<?php esc_html_e( 'Get a Quote', 'cedraparts' ); ?> <span aria-hidden="true">↗</span>
					</a>
					<?php if ( $sales_email ) : ?>
						<a href="mailto:<?php echo esc_attr( $sales_email ); ?>" class="glass-dark cedra-btn cedra-btn--dark-glass cedra-btn--lg">
							<?php esc_html_e( 'Email sales', 'cedraparts' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<!-- Footer bento -->
			<div class="foot cedra-footer__bento">
				<div class="glass-dark span2 cedra-bento-card cedra-bento-card--brand">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="cedra-footer__logo" loading="lazy">
					<p class="cedra-footer__mission">
						<?php esc_html_e( "Canada's dedicated aftermarket parts specialist, exclusively focused on the", 'cedraparts' ); ?>
						<span class="cedra-footer__serif"><?php esc_html_e( 'Power Generation', 'cedraparts' ); ?></span>
						<?php esc_html_e( 'sector.', 'cedraparts' ); ?>
					</p>
					<nav aria-label="<?php esc_attr_e( 'Footer', 'cedraparts' ); ?>" class="cedra-footer__pills">
						<a href="<?php echo esc_url( $home_url ); ?>"><?php esc_html_e( 'Home', 'cedraparts' ); ?></a>
						<a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Products', 'cedraparts' ); ?></a>
						<a href="<?php echo esc_url( $about_url ); ?>"><?php esc_html_e( 'About Us', 'cedraparts' ); ?></a>
						<a href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Contact Us', 'cedraparts' ); ?></a>
					</nav>
				</div>

				<div class="glass-dark cedra-bento-card">
					<span class="cedra-footer__label"><?php esc_html_e( 'TALK TO US', 'cedraparts' ); ?></span>
					<?php if ( $sales_email ) : ?>
						<a href="mailto:<?php echo esc_attr( $sales_email ); ?>" class="cedra-footer__contact-row">
							<span class="cedra-footer__icon-box">
								<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
									<rect x="3" y="5" width="18" height="14" rx="3" fill="none" stroke="#D6C1EF" stroke-width="1.6"></rect>
									<path d="M4 7l8 6 8-6" fill="none" stroke="#D6C1EF" stroke-width="1.6" stroke-linejoin="round"></path>
								</svg>
							</span>
							<span><?php echo esc_html( $sales_email ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( $phone_display ) : ?>
						<a href="tel:<?php echo esc_attr( $phone_href ); ?>" class="cedra-footer__contact-row">
							<span class="cedra-footer__icon-box">
								<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
									<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a1 1 0 0 1-1 1A16 16 0 0 1 4 5a1 1 0 0 1 1-1z" fill="none" stroke="#D6C1EF" stroke-width="1.6" stroke-linejoin="round"></path>
								</svg>
							</span>
							<span class="cedra-mono"><?php echo esc_html( $phone_display ); ?></span>
						</a>
					<?php endif; ?>
					<p class="cedra-footer__subnote">
						<?php esc_html_e( 'Emergency parts support for critical infrastructure clients.', 'cedraparts' ); ?>
					</p>
				</div>

				<div class="glass-dark cedra-bento-card cedra-bento-card--hours">
					<div class="cedra-footer__hours-top">
						<span class="cedra-footer__label"><?php esc_html_e( 'HOURS (ET)', 'cedraparts' ); ?></span>
						<span class="cedra-open-pill js-et-status-pill">
							<span class="cedra-open-pill__dot js-et-status-dot" style="background: <?php echo esc_attr( $et_status['dot'] ); ?>"></span>
							<span class="js-et-status-label"><?php echo esc_html( $et_status['label'] ); ?></span>
						</span>
					</div>
					<div class="cedra-footer__hours-row cedra-footer__hours-row--active">
						<span><?php esc_html_e( 'Mon – Fri', 'cedraparts' ); ?></span>
						<span><?php echo esc_html( $hours_weekday ); ?></span>
					</div>
					<div class="cedra-footer__hours-row">
						<span><?php esc_html_e( 'Sat – Sun', 'cedraparts' ); ?></span>
						<span><?php echo esc_html( $hours_weekend ); ?></span>
					</div>
					<div class="cedra-footer__address">
						<?php echo esc_html( $address_line1 ); ?><br>
						<?php echo esc_html( $address_line2 ); ?>
					</div>
				</div>
			</div>

			<!-- Giant watermark -->
			<div class="cedra-footer__watermark" aria-hidden="true">
				<img src="<?php echo esc_url( $logo_url ); ?>" alt="" loading="lazy">
			</div>

			<!-- Copyright bar -->
			<div class="cedra-footer__bottom">
				<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Cedra Parts Inc. <?php esc_html_e( 'All Rights Reserved.', 'cedraparts' ); ?></span>
				<span class="hide-m"><?php echo esc_html( $hq_line ); ?></span>
				<a href="#top" class="glass-dark cedra-back-top">
					<?php esc_html_e( 'Back to top', 'cedraparts' ); ?> <span aria-hidden="true">↑</span>
				</a>
			</div>
		</div>
	</footer>

</div><!-- .cedra-site-shell -->

<?php wp_footer(); ?>
</body>
</html>

