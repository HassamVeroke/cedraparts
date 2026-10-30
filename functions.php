<?php
/**
 * Cedra Parts Theme Functions & Definitions
 *
 * @package CedraParts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CEDRA_THEME_VERSION', '1.0.0' );
define( 'CEDRA_THEME_DIR', get_template_directory() );
define( 'CEDRA_THEME_URI', get_template_directory_uri() );

require_once CEDRA_THEME_DIR . '/inc/customizer.php';
require_once CEDRA_THEME_DIR . '/inc/svg-illustrations.php';
require_once CEDRA_THEME_DIR . '/inc/template-tags.php';
require_once CEDRA_THEME_DIR . '/inc/catalog-query.php';

/**
 * Set up theme defaults and register support for WordPress & WooCommerce features.
 */
function cedra_theme_setup() {
	load_theme_textdomain( 'cedraparts', CEDRA_THEME_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );

	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 120,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 540,
			'single_image_width'    => 900,
			'product_grid'          => array(
				'default_rows'    => 3,
				'min_rows'        => 1,
				'default_columns' => 4,
				'min_columns'     => 1,
				'max_columns'     => 4,
			),
		)
	);

	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Floating Glass Menu', 'cedraparts' ),
			'footer'  => __( 'Footer Bento Menu', 'cedraparts' ),
		)
	);
}
add_action( 'after_setup_theme', 'cedra_theme_setup' );

/**
 * Add preconnect resource hints for Google Fonts.
 *
 * @param array<int, string|array<string, string>> $urls          URLs to print for resource hints.
 * @param string                                   $relation_type Relation type.
 * @return array<int, string|array<string, string>>
 */
function cedra_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'cedra_resource_hints', 10, 2 );

/**
 * Enqueue fonts, styles, and scripts.
 */
function cedra_enqueue_assets() {
	wp_enqueue_style(
		'cedra-google-fonts',
		'https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&family=Geist+Mono:wght@400;500&family=Instrument+Serif:ital@0;1&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'cedra-main',
		CEDRA_THEME_URI . '/assets/css/main.css',
		array( 'cedra-google-fonts' ),
		CEDRA_THEME_VERSION
	);

	wp_enqueue_style(
		'cedra-woocommerce-pages',
		CEDRA_THEME_URI . '/assets/css/woocommerce-pages.css',
		array( 'cedra-main' ),
		CEDRA_THEME_VERSION
	);

	$accent = sanitize_hex_color( cedra_get_option( 'accent_color' ) );
	if ( ! $accent ) {
		$accent = '#7024AB';
	}
	wp_add_inline_style(
		'cedra-main',
		':root{--cedra-accent:' . $accent . ';}'
	);

	wp_enqueue_script(
		'cedra-main',
		CEDRA_THEME_URI . '/assets/js/main.js',
		array(),
		CEDRA_THEME_VERSION,
		true
	);

	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
	$cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
	$chk_url  = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' );

	wp_localize_script(
		'cedra-main',
		'CedraConfig',
		array(
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( 'cedra_nonce' ),
			'shopUrl'   => $shop_url,
			'cartUrl'   => $cart_url,
			'checkoutUrl' => $chk_url,
			'spotlight' => (bool) cedra_get_option( 'enable_spotlight' ),
			'perPage'   => cedra_catalog_per_page(),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'cedra_enqueue_assets', 20 );

/**
 * Add helpful body classes for Cedra frosted-glass styling.
 *
 * @param array<int, string> $classes Body classes.
 * @return array<int, string>
 */
function cedra_body_classes( $classes ) {
	$classes[] = 'cedra-glass-theme';
	return $classes;
}
add_filter( 'body_class', 'cedra_body_classes' );

