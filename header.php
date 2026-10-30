<?php
/**
 * Theme Header — Ambient Purple Orbs, Top Utility Bar, Sticky Floating Glass Pill Navbar, & Mini-Cart Drawer
 *
 * @package CedraParts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$top_tagline   = cedra_get_option( 'top_tagline' );
$sales_email   = cedra_get_option( 'sales_email' );
$phone_display = cedra_get_option( 'sales_phone_display' );
$phone_href    = cedra_get_option( 'sales_phone_href' );
$quote_url     = cedra_get_option( 'quote_url' );
$shop_url      = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$account_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );
$cart_url      = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
$cart_count    = ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
$nav_items     = cedra_get_nav_items();
$logo_url      = CEDRA_THEME_URI . '/assets/images/cedra-logo.webp';
$custom_logo   = get_theme_mod( 'custom_logo' );
if ( $custom_logo ) {
	$custom_logo_src = wp_get_attachment_image_url( $custom_logo, 'full' );
	if ( $custom_logo_src ) {
		$logo_url = $custom_logo_src;
	}
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="sr cedra-skip-link" href="#main-content"><?php esc_html_e( 'Skip to main content', 'cedraparts' ); ?></a>

<div id="top" class="cedra-site-shell">

	<!-- Ambient colour behind the frosted glass -->
	<div class="orb cedra-orb--1" aria-hidden="true"></div>
	<div class="orb cedra-orb--2" aria-hidden="true"></div>
	<div class="orb cedra-orb--3" aria-hidden="true"></div>
	<div class="orb cedra-orb--4" aria-hidden="true"></div>

	<!-- Top line -->
	<div class="cedra-topbar">
		<div class="wrap cedra-topbar__inner">
			<span><?php echo esc_html( $top_tagline ); ?></span>
			<span class="hide-m cedra-topbar__links">
				<?php if ( $sales_email ) : ?>
					<a href="mailto:<?php echo esc_attr( $sales_email ); ?>"><?php echo esc_html( $sales_email ); ?></a>
				<?php endif; ?>
				<?php if ( $phone_display ) : ?>
					<a href="tel:<?php echo esc_attr( $phone_href ); ?>"><?php echo esc_html( $phone_display ); ?></a>
				<?php endif; ?>
			</span>
		</div>
	</div>

	<!-- Nav (sticky floating glass bar) -->
	<header class="cedra-header">
		<div class="wrap">
			<div class="glass-strong cedra-nav">
				<a href="<?php echo esc_url( $shop_url ); ?>" class="cedra-nav__brand" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) . ' home' ); ?>">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="cedra-nav__logo" fetchpriority="high">
				</a>

				<nav class="hide-m cedra-nav__links" aria-label="<?php esc_attr_e( 'Primary', 'cedraparts' ); ?>">
					<?php foreach ( $nav_items as $item ) : ?>
						<a
							href="<?php echo esc_url( $item['url'] ); ?>"
							class="cedra-nav__link<?php echo $item['active'] ? ' is-active' : ''; ?>"
							<?php echo $item['active'] ? 'aria-current="page"' : ''; ?>
						>
							<?php echo esc_html( $item['label'] ); ?>
						</a>
					<?php endforeach; ?>
				</nav>

				<div class="cedra-nav__actions">
					<a href="<?php echo esc_url( $account_url ); ?>" class="cedra-nav__icon-btn" aria-label="<?php esc_attr_e( 'My account', 'cedraparts' ); ?>">
						<svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
							<circle cx="12" cy="8" r="4" fill="none" stroke="currentColor" stroke-width="1.6"></circle>
							<path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"></path>
						</svg>
					</a>

					<a
						href="<?php echo esc_url( $cart_url ); ?>"
						class="cedra-nav__cart js-open-cart-drawer"
						aria-label="<?php echo esc_attr( sprintf( __( 'Cart, %d items', 'cedraparts' ), $cart_count ) ); ?>"
					>
						<svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
							<path d="M5 8h14l-1.2 11.2a1.5 1.5 0 0 1-1.5 1.3H7.7a1.5 1.5 0 0 1-1.5-1.3z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"></path>
							<path d="M9 8V6a3 3 0 0 1 6 0v2" fill="none" stroke="currentColor" stroke-width="1.6"></path>
						</svg>
						<span class="cedra-nav__cart-count js-cedra-cart-count">(<?php echo esc_html( (string) $cart_count ); ?>)</span>
					</a>

					<a href="<?php echo esc_url( $quote_url ); ?>" class="cedra-nav__quote hide-xs">
						<?php esc_html_e( 'Get a Quote', 'cedraparts' ); ?> <span aria-hidden="true">↗</span>
					</a>

					<button
						type="button"
						class="cedra-nav__burger show-m js-toggle-mobile-menu"
						aria-expanded="false"
						aria-controls="cedra-mobile-menu"
						aria-label="<?php esc_attr_e( 'Toggle navigation menu', 'cedraparts' ); ?>"
					>
						<svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
							<path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
						</svg>
					</button>
				</div>
			</div>

			<!-- Mobile Glass Navigation Dropdown -->
			<div id="cedra-mobile-menu" class="glass-strong cedra-mobile-menu" hidden>
				<nav aria-label="<?php esc_attr_e( 'Mobile navigation', 'cedraparts' ); ?>" class="cedra-mobile-menu__links">
					<?php foreach ( $nav_items as $item ) : ?>
						<a
							href="<?php echo esc_url( $item['url'] ); ?>"
							class="cedra-mobile-menu__link<?php echo $item['active'] ? ' is-active' : ''; ?>"
						>
							<?php echo esc_html( $item['label'] ); ?>
						</a>
					<?php endforeach; ?>
				</nav>
				<div class="cedra-mobile-menu__foot">
					<a href="<?php echo esc_url( $quote_url ); ?>" class="cedra-btn cedra-btn--dark cedra-btn--full">
						<?php esc_html_e( 'Get a Quote', 'cedraparts' ); ?> <span aria-hidden="true">↗</span>
					</a>
					<div class="cedra-mobile-menu__contact">
						<?php if ( $sales_email ) : ?>
							<a href="mailto:<?php echo esc_attr( $sales_email ); ?>"><?php echo esc_html( $sales_email ); ?></a>
						<?php endif; ?>
						<?php if ( $phone_display ) : ?>
							<a href="tel:<?php echo esc_attr( $phone_href ); ?>"><?php echo esc_html( $phone_display ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</header>

	<!-- Slide-Over Frosted-Glass Mini-Cart Drawer -->
	<div id="cedra-cart-drawer" class="cedra-drawer" aria-hidden="true" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Shopping cart', 'cedraparts' ); ?>">
		<div class="cedra-drawer__backdrop js-close-cart-drawer"></div>
		<aside class="cedra-drawer__panel glass-strong js-cedra-drawer-body">
			<?php echo cedra_get_mini_cart_drawer_inner_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</aside>
	</div>

	<main id="main-content" class="cedra-main">

