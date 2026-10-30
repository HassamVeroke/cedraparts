<?php
/**
 * Cross-Reference & Volume Pricing Help Band (Option 2 Design)
 *
 * @package CedraParts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$contact_url      = cedra_get_option( 'contact_url' );
$quote_url        = cedra_get_option( 'quote_url' );
$phone_display    = cedra_get_option( 'sales_phone_display' );
$phone_href       = cedra_get_option( 'sales_phone_href' );
$emergency_notice = cedra_get_option( 'emergency_notice' );
?>
<!-- Help (light lavender band with frosted-glass panels) -->
<section id="quote" class="cedra-help">
	<div class="cedra-help__dots" aria-hidden="true"></div>
	<div class="orb cedra-help__orb--left" aria-hidden="true"></div>
	<div class="orb cedra-help__orb--right" aria-hidden="true"></div>

	<div class="wrap hero cedra-help__grid">
		<div class="glass-strong cedra-help__card">
			<div class="cedra-eyebrow-mono"><?php esc_html_e( 'CROSS-REFERENCE', 'cedraparts' ); ?></div>
			<div>
				<h2 class="cedra-help__heading">
					<?php esc_html_e( "Can't find", 'cedraparts' ); ?>
					<span class="cedra-serif-accent"><?php esc_html_e( 'your part?', 'cedraparts' ); ?></span>
				</h2>
				<p class="cedra-help__copy">
					<?php esc_html_e( 'Our technical team can cross-reference any part number across all major genset brands.', 'cedraparts' ); ?>
				</p>
			</div>
			<a href="<?php echo esc_url( $contact_url ); ?>" class="cedra-btn cedra-btn--dark">
				<?php esc_html_e( 'Send a part number', 'cedraparts' ); ?> <span aria-hidden="true">↗</span>
			</a>
		</div>

		<div class="glass-strong cedra-help__card cedra-help__card--tinted">
			<div class="cedra-eyebrow-mono"><?php esc_html_e( 'VOLUME PRICING', 'cedraparts' ); ?></div>
			<div>
				<h2 class="cedra-help__heading">
					<?php esc_html_e( 'Custom PM kit', 'cedraparts' ); ?>
					<span class="cedra-serif-accent"><?php esc_html_e( 'or bulk order?', 'cedraparts' ); ?></span>
				</h2>
				<p class="cedra-help__copy">
					<?php esc_html_e( 'Volume pricing available for service companies and facility managers.', 'cedraparts' ); ?>
				</p>
			</div>
			<a href="<?php echo esc_url( $quote_url ); ?>" class="cedra-btn cedra-btn--accent">
				<?php esc_html_e( 'Get a Quote', 'cedraparts' ); ?> <span aria-hidden="true">↗</span>
			</a>
		</div>
	</div>

	<div class="wrap cedra-help__emergency-wrap">
		<div class="glass cedra-help__emergency">
			<span class="cedra-help__emergency-text">
				<span class="cedra-pulse-dot" aria-hidden="true"></span>
				<?php echo esc_html( $emergency_notice ); ?>
			</span>
			<?php if ( $phone_display ) : ?>
				<a href="tel:<?php echo esc_attr( $phone_href ); ?>" class="cedra-help__phone-pill">
					<?php echo esc_html( $phone_display ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>

