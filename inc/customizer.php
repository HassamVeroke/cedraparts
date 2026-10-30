<?php
/**
 * Cedra Parts Customizer Settings & Theme Defaults (Option 2 Design)
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
		'carousel_autoplay'    => true,
		'use_svg_fallback'     => true,
		'strip_trailing_sku'   => true,
		'top_tagline'          => "Canada's dedicated power gen parts specialist",
		'sales_email'          => 'sales@cedraparts.com',
		'sales_phone_display'  => '+1 (289) 337-2541',
		'sales_phone_href'     => '+12893372541',
		'home_url'             => 'https://cedraparts.com/',
		'about_url'            => 'https://cedraparts.com/about',
		'contact_url'          => 'https://cedraparts.com/contact',
		'quote_url'            => 'https://cedraparts.com/contact',
		'address_line'         => '5005 King Street, Beamsville, ON L0R 1B0',
		'hours_line'           => 'Mon – Fri, 9 am – 5 pm',
		'footer_hq_line'       => 'Headquarters & Distribution — Beamsville, Ontario, Canada',
		'linkedin_url'         => '#',
		'facebook_url'         => '#',
		'instagram_url'        => '#',
		'youtube_url'          => '#',
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

	// Section: Visual & Catalog Preferences.
	$wp_customize->add_section(
		'cedra_appearance_section',
		array(
			'title'       => __( 'Cedra Theme & Catalog Options', 'cedraparts' ),
			'description' => __( 'Configure accent color, hero carousel autoplay, and catalog card display.', 'cedraparts' ),
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
		'cedra_carousel_autoplay',
		array(
			'default'           => $defaults['carousel_autoplay'],
			'sanitize_callback' => 'rest_sanitize_boolean',
		)
	);
	$wp_customize->add_control(
		'cedra_carousel_autoplay',
		array(
			'label'   => __( 'Autoplay Hero Carousel (7s interval)', 'cedraparts' ),
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
			'description' => __( 'Replaces generic "Image Not Available" placeholders with category-matched technical vector illustrations.', 'cedraparts' ),
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
			'description' => __( 'Since SKU is displayed in the card header row, removes duplicate trailing (SKU) suffixes.', 'cedraparts' ),
			'section'     => 'cedra_appearance_section',
			'type'        => 'checkbox',
		)
	);

	// Section: Contact, Links & Socials.
	$wp_customize->add_section(
		'cedra_contact_section',
		array(
			'title'    => __( 'Cedra Contact, Links & Socials', 'cedraparts' ),
			'priority' => 26,
		)
	);

	$text_fields = array(
		'top_tagline'         => __( 'Utility Bar Tagline', 'cedraparts' ),
		'sales_email'         => __( 'Sales Email Address', 'cedraparts' ),
		'sales_phone_display' => __( 'Phone Number (Display)', 'cedraparts' ),
		'sales_phone_href'    => __( 'Phone Number (tel: Link)', 'cedraparts' ),
		'home_url'            => __( 'Main Corporate Home URL', 'cedraparts' ),
		'about_url'           => __( 'About Us URL', 'cedraparts' ),
		'contact_url'         => __( 'Contact Us URL', 'cedraparts' ),
		'quote_url'           => __( 'Get a Quote URL', 'cedraparts' ),
		'address_line'        => __( 'Footer Address Line', 'cedraparts' ),
		'hours_line'          => __( 'Footer Business Hours Line', 'cedraparts' ),
		'footer_hq_line'      => __( 'Footer Bottom Location Line', 'cedraparts' ),
		'linkedin_url'        => __( 'LinkedIn URL', 'cedraparts' ),
		'facebook_url'        => __( 'Facebook URL', 'cedraparts' ),
		'instagram_url'       => __( 'Instagram URL', 'cedraparts' ),
		'youtube_url'         => __( 'YouTube URL', 'cedraparts' ),
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
}
add_action( 'customize_register', 'cedra_customize_register' );
