<?php
/**
 * Cedra Parts Customizer Settings & Theme Defaults
 *
 * @package CedraParts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default theme option values matching the Option 2 design specification.
 *
 * @return array<string, mixed>
 */
function cedra_get_option_defaults() {
	return array(
		'accent_color'         => '#7024AB',
		'enable_spotlight'     => true,
		'use_svg_fallback'     => true,
		'strip_trailing_sku'   => true,
		'top_tagline'          => "Canada's Dedicated Power Gen Parts Specialist",
		'sales_email'          => 'sales@cedraparts.com',
		'sales_phone_display'  => '+1 (289) 337-2541',
		'sales_phone_href'     => '+12893372541',
		'home_url'             => 'https://cedraparts.com/',
		'about_url'            => 'https://cedraparts.com/about',
		'contact_url'          => 'https://cedraparts.com/contact',
		'quote_url'            => 'https://cedraparts.com/contact',
		'hero_eyebrow'         => 'Product Catalog',
		'hero_title_line1'     => 'Power Gen Parts',
		'hero_title_serif'     => '& Components',
		'hero_badge_ring'      => 'POWER GEN EXCLUSIVE · BEAMSVILLE, ONTARIO · ',
		'hero_description'     => 'Browse our comprehensive inventory of aftermarket parts for all major genset engine brands. Genset spare parts, filters, consumables, PM kits, electrical, and more.',
		'distributor_label'    => 'Authorized Canadian Distributor',
		'address_line1'        => '5005 King Street, Beamsville,',
		'address_line2'        => 'Ontario L0R 1B0, Canada',
		'hours_weekday'        => '9 am – 5 pm',
		'hours_weekend'        => 'Closed',
		'emergency_notice'     => 'Emergency parts support available for critical infrastructure clients. Contact us for priority service.',
		'footer_hq_line'       => 'Headquarters & Distribution — Beamsville, Ontario, Canada',
	);
}

/**
 * Retrieve a theme mod with its default fallback.
 *
 * @param string $key Option key.
 * @return mixed
 */
function cedra_get_option( $key ) {
	$defaults = cedra_get_option_defaults();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	return get_theme_mod( 'cedra_' . $key, $default );
}

/**
 * Register Customizer panels, sections, and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function cedra_customize_register( $wp_customize ) {
	$defaults = cedra_get_option_defaults();

	// Section: Visual & Brand Appearance.
	$wp_customize->add_section(
		'cedra_appearance_section',
		array(
			'title'       => __( 'Cedra Glass Design & Brand', 'cedraparts' ),
			'description' => __( 'Configure accent color, interactive hero spotlight, and artwork preferences.', 'cedraparts' ),
			'priority'    => 25,
		)
	);

	$wp_customize->add_setting(
		'cedra_accent_color',
		array(
			'default'           => $defaults['accent_color'],
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'cedra_accent_color',
			array(
				'label'   => __( 'Primary Accent Color', 'cedraparts' ),
				'section' => 'cedra_appearance_section',
			)
		)
	);

	$wp_customize->add_setting(
		'cedra_enable_spotlight',
		array(
			'default'           => $defaults['enable_spotlight'],
			'sanitize_callback' => 'rest_sanitize_boolean',
		)
	);
	$wp_customize->add_control(
		'cedra_enable_spotlight',
		array(
			'label'   => __( 'Enable Hero Cursor Spotlight Glow', 'cedraparts' ),
			'section' => 'cedra_appearance_section',
			'type'    => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'cedra_use_svg_fallback',
		array(
			'default'           => $defaults['use_svg_fallback'],
			'sanitize_callback' => 'rest_sanitize_boolean',
		)
	);
	$wp_customize->add_control(
		'cedra_use_svg_fallback',
		array(
			'label'       => __( 'Use Technical Vector Art When Product Photo Is Unavailable', 'cedraparts' ),
			'description' => __( 'Replaces generic "Image Not Available" placeholders with category-matched vector illustrations.', 'cedraparts' ),
			'section'     => 'cedra_appearance_section',
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'cedra_strip_trailing_sku',
		array(
			'default'           => $defaults['strip_trailing_sku'],
			'sanitize_callback' => 'rest_sanitize_boolean',
		)
	);
	$wp_customize->add_control(
		'cedra_strip_trailing_sku',
		array(
			'label'       => __( 'Clean Redundant Trailing (SKU) from Card Titles', 'cedraparts' ),
			'description' => __( 'Since SKU is displayed in the top-left badge on cards, removes duplicate trailing (SKU) suffixes.', 'cedraparts' ),
			'section'     => 'cedra_appearance_section',
			'type'        => 'checkbox',
		)
	);

	// Section: Contact, Navigation Links & Business Details.
	$wp_customize->add_section(
		'cedra_contact_section',
		array(
			'title'    => __( 'Cedra Contact, Links & Hours', 'cedraparts' ),
			'priority' => 26,
		)
	);

	$text_fields = array(
		'top_tagline'         => __( 'Top Bar Tagline', 'cedraparts' ),
		'sales_email'         => __( 'Sales Email Address', 'cedraparts' ),
		'sales_phone_display' => __( 'Phone Number (Display)', 'cedraparts' ),
		'sales_phone_href'    => __( 'Phone Number (tel: Link)', 'cedraparts' ),
		'home_url'            => __( 'Main Corporate Home URL', 'cedraparts' ),
		'about_url'           => __( 'About Us URL', 'cedraparts' ),
		'contact_url'         => __( 'Contact Us URL', 'cedraparts' ),
		'quote_url'           => __( 'Get a Quote URL', 'cedraparts' ),
		'address_line1'       => __( 'Address Line 1', 'cedraparts' ),
		'address_line2'       => __( 'Address Line 2', 'cedraparts' ),
		'hours_weekday'       => __( 'Mon – Fri Hours (ET)', 'cedraparts' ),
		'hours_weekend'       => __( 'Sat – Sun Hours (ET)', 'cedraparts' ),
		'emergency_notice'    => __( 'Emergency Support Notice', 'cedraparts' ),
		'footer_hq_line'      => __( 'Footer Bottom Location Line', 'cedraparts' ),
	);

	foreach ( $text_fields as $field_key => $label ) {
		$is_url   = str_ends_with( $field_key, '_url' );
		$is_email = ( 'sales_email' === $field_key );
		$sanitize = $is_url ? 'esc_url_raw' : ( $is_email ? 'sanitize_email' : 'sanitize_text_field' );

		$wp_customize->add_setting(
			'cedra_' . $field_key,
			array(
				'default'           => $defaults[ $field_key ],
				'sanitize_callback' => $sanitize,
			)
		);
		$wp_customize->add_control(
			'cedra_' . $field_key,
			array(
				'label'   => $label,
				'section' => 'cedra_contact_section',
				'type'    => $is_url ? 'url' : 'text',
			)
		);
	}

	// Section: Catalog Hero Copy.
	$wp_customize->add_section(
		'cedra_hero_section',
		array(
			'title'    => __( 'Cedra Catalog Hero Content', 'cedraparts' ),
			'priority' => 27,
		)
	);

	$hero_fields = array(
		'hero_eyebrow'       => array( __( 'Eyebrow Badge Text', 'cedraparts' ), 'text' ),
		'hero_title_line1'   => array( __( 'Hero Heading (Main Line)', 'cedraparts' ), 'text' ),
		'hero_title_serif'   => array( __( 'Hero Heading (Italic Serif Line)', 'cedraparts' ), 'text' ),
		'hero_badge_ring'    => array( __( 'Rotating Seal Ring Text', 'cedraparts' ), 'text' ),
		'hero_description'   => array( __( 'Hero Description Paragraph', 'cedraparts' ), 'textarea' ),
		'distributor_label'  => array( __( 'Logo Marquee Label', 'cedraparts' ), 'text' ),
	);

	foreach ( $hero_fields as $field_key => $cfg ) {
		$wp_customize->add_setting(
			'cedra_' . $field_key,
			array(
				'default'           => $defaults[ $field_key ],
				'sanitize_callback' => 'textarea' === $cfg[1] ? 'sanitize_textarea_field' : 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			'cedra_' . $field_key,
			array(
				'label'   => $cfg[0],
				'section' => 'cedra_hero_section',
				'type'    => $cfg[1],
			)
		);
	}
}
add_action( 'customize_register', 'cedra_customize_register' );

