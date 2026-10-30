<?php
/**
 * Theme Header: Dark Utility Bar, Sticky White Header & Mini-Cart Drawer (Option 2 Design)
 *
 * @package CedraParts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shop_url     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$cart_url     = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
$account_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
$cart_count   = ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
$nav_items    = cedra_get_nav_items();
$tagline      = cedra_get_option( 'top_tagline' );
$sales_email  = cedra_get_option( 'sales_email' );
$phone_disp   = cedra_get_option( 'sales_phone_display' );
$phone_href   = cedra_get_option( 'sales_phone_href' );
$quote_url    = cedra_get_option( 'quote_url' );
$logo_src     = CEDRA_THEME_URI . '/assets/images/cedra-logo.webp';
$is_cat_view  = ( function_exists( 'is_shop' ) && is_shop() ) || ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) || is_front_page() || is_home();
$search_href  = $is_cat_view ? '#results' : ( $shop_url . '#results' );
$support_href = '#support';
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

<a class="sr cedra-skip-link" href="#main-content"><?php esc_html_e( 'Skip to content', 'cedraparts' ); ?></a>

<!-- Utility bar -->
<div class="cedra-util">
	<div class="wrap cedra-util__inner">
		<span><?php echo esc_html( $tagline ); ?></span>
		<span class="cedra-util__links hide-m">
			<?php if ( $sales_email ) : ?>
				<a href="mailto:<?php echo esc_attr( $sales_email ); ?>"><?php echo esc_html( $sales_email ); ?></a>
			<?php endif; ?>
			<?php if ( $phone_disp ) : ?>
				<a href="tel:<?php echo esc_attr( $phone_href ); ?>"><?php echo esc_html( $phone_disp ); ?></a>
			<?php endif; ?>
			<a href="<?php echo esc_url( $support_href ); ?>" class="cedra-util__emergency"><?php esc_html_e( 'Emergency support', 'cedraparts' ); ?></a>
		</span>
	</div>
</div>

<!-- Header -->
<header class="cedra-header">
	<div class="wrap cedra-header__inner">
		<a href="<?php echo esc_url( $shop_url ); ?>" class="cedra-header__brand" aria-label="<?php esc_attr_e( 'Cedra Parts home', 'cedraparts' ); ?>">
			<?php if ( has_custom_logo() ) : ?>
				<?php
				$custom_logo_id = get_theme_mod( 'custom_logo' );
				$custom_src     = $custom_logo_id ? wp_get_attachment_image_url( $custom_logo_id, 'full' ) : '';
				?>
				<img src="<?php echo esc_url( $custom_src ? $custom_src : $logo_src ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="91" height="40" class="cedra-header__logo">
			<?php else : ?>
				<img src="<?php echo esc_url( $logo_src ); ?>" alt="<?php esc_attr_e( 'Cedra Parts', 'cedraparts' ); ?>" width="91" height="40" class="cedra-header__logo">
			<?php endif; ?>
		</a>

		<nav class="cedra-nav hide-t" aria-label="<?php esc_attr_e( 'Primary', 'cedraparts' ); ?>">
			<?php foreach ( $nav_items as $item ) : ?>
				<a
					href="<?php echo esc_url( $item['url'] ); ?>"
					class="navlink<?php echo $item['active'] ? ' is-active' : ''; ?>"
					<?php echo $item['active'] ? 'aria-current="page"' : ''; ?>
				>
					<?php echo esc_html( $item['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<div class="cedra-header__actions">
			<a
				href="<?php echo esc_url( $search_href ); ?>"
				class="cedra-icon-btn hide-m js-focus-search"
				aria-label="<?php esc_attr_e( 'Search parts', 'cedraparts' ); ?>"
			>
				<svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
					<circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="1.7"></circle>
					<path d="M20 20l-4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"></path>
				</svg>
			</a>

			<a
				href="<?php echo esc_url( $account_url ); ?>"
				class="cedra-icon-btn"
				aria-label="<?php esc_attr_e( 'My account', 'cedraparts' ); ?>"
			>
				<svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
					<circle cx="12" cy="8" r="4" fill="none" stroke="currentColor" stroke-width="1.7"></circle>
					<path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"></path>
				</svg>
			</a>

			<a
				href="<?php echo esc_url( $cart_url ); ?>"
				class="cedra-icon-btn cedra-cart-btn js-open-cart-drawer"
				aria-label="<?php echo esc_attr( sprintf( __( 'Cart, %d items', 'cedraparts' ), $cart_count ) ); ?>"
			>
				<svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
					<path d="M5 8h14l-1.2 11.2a1.5 1.5 0 0 1-1.5 1.3H7.7a1.5 1.5 0 0 1-1.5-1.3z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"></path>
					<path d="M9 8V6a3 3 0 0 1 6 0v2" fill="none" stroke="currentColor" stroke-width="1.7"></path>
				</svg>
				<span class="cedra-header__badge js-cedra-cart-count"><?php echo esc_html( (string) $cart_count ); ?></span>
			</a>

			<a href="<?php echo esc_url( $quote_url ); ?>" class="cedra-header__quote">
				<?php esc_html_e( 'Get a Quote', 'cedraparts' ); ?>
			</a>

			<button
				type="button"
				class="cedra-icon-btn cedra-mobile-toggle js-mobile-nav-toggle"
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

	<!-- Tablet & Mobile Collapsible Navigation -->
	<div id="cedra-mobile-menu" class="cedra-mobile-menu" hidden>
		<div class="wrap cedra-mobile-menu__inner">
			<?php foreach ( $nav_items as $item ) : ?>
				<a
					href="<?php echo esc_url( $item['url'] ); ?>"
					class="cedra-mobile-menu__link<?php echo $item['active'] ? ' is-active' : ''; ?>"
				>
					<?php echo esc_html( $item['label'] ); ?>
				</a>
			<?php endforeach; ?>
			<?php if ( $phone_disp ) : ?>
				<a href="tel:<?php echo esc_attr( $phone_href ); ?>" class="cedra-mobile-menu__link cedra-mono">
					<?php echo esc_html( $phone_disp ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</header>

<!-- Slide-Over Mini-Cart Drawer -->
<div id="cedra-cart-drawer" class="cedra-drawer" aria-hidden="true" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Shopping cart', 'cedraparts' ); ?>">
	<button type="button" class="cedra-drawer__backdrop js-close-cart-drawer" tabindex="-1" aria-label="<?php esc_attr_e( 'Close cart', 'cedraparts' ); ?>"></button>
	<div class="cedra-drawer__panel js-cedra-drawer-panel">
		<?php echo cedra_get_mini_cart_drawer_inner_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
</div>

<main id="main-content" class="cedra-main">
