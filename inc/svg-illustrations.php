<?php
/**
 * Technical Vector Illustrations & UI SVG Icons (Option 2 Design System)
 *
 * @package CedraParts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determine the illustration kind from a WC_Product's categories and title.
 *
 * @param WC_Product $product Product instance.
 * @return string Illustration kind slug.
 */
function cedra_detect_product_kind( $product ) {
	$cat_slugs = array();
	$terms     = get_the_terms( $product->get_id(), 'product_cat' );
	if ( is_array( $terms ) ) {
		foreach ( $terms as $term ) {
			$cat_slugs[] = $term->slug;
		}
	}

	$haystack = strtolower( implode( ' ', $cat_slugs ) . ' ' . $product->get_name() );

	if ( in_array( 'generator-controllers', $cat_slugs, true ) || str_contains( $haystack, 'controller' ) ) {
		return 'controller';
	}
	if ( in_array( 'battery-chargers', $cat_slugs, true ) || str_contains( $haystack, 'charger' ) ) {
		return 'charger';
	}
	if ( in_array( 'voltage-regulators', $cat_slugs, true ) || str_contains( $haystack, 'voltage regulator' ) || str_contains( $haystack, 'avr' ) ) {
		return 'avr';
	}
	if ( in_array( 'generator-communications', $cat_slugs, true ) || str_contains( $haystack, 'communication' ) || str_contains( $haystack, 'rs485' ) || str_contains( $haystack, 'gateway' ) ) {
		return 'comm';
	}
	if ( in_array( 'oil-fuel-air-filters', $cat_slugs, true ) || str_contains( $haystack, 'filter' ) || str_contains( $haystack, 'element' ) ) {
		return 'filter';
	}
	if ( in_array( 'hoses-gaskets', $cat_slugs, true ) || str_contains( $haystack, 'gasket' ) || str_contains( $haystack, 'seal' ) || str_contains( $haystack, 'o-ring' ) || str_contains( $haystack, 'hose' ) ) {
		return 'gasket';
	}
	if ( in_array( 'belts', $cat_slugs, true ) || in_array( 'belts-hoses-gaskets', $cat_slugs, true ) || str_contains( $haystack, 'belt' ) ) {
		return 'belt';
	}
	if ( in_array( 'engine-parts', $cat_slugs, true ) ) {
		return 'engine';
	}

	return 'engine';
}

/**
 * Return technical vector illustration SVG markup matching the Option 2 palette.
 *
 * @param string $kind Illustration kind slug.
 * @return string SVG HTML markup.
 */
function cedra_get_technical_svg( $kind = 'controller' ) {
	$accent = esc_attr( cedra_get_option( 'accent_color' ) );

	switch ( $kind ) {
		case 'charger':
			return '<svg viewBox="0 0 240 180" class="cedra-tech-svg" role="img" aria-label="Battery charger illustration"><ellipse cx="120" cy="170" rx="66" ry="5" fill="#0B1C2C" opacity=".1"></ellipse><rect x="72" y="14" width="96" height="148" rx="14" fill="#0B1C2C"></rect><path d="M88 36h64M88 46h64M88 56h64M88 66h64M88 76h64M88 86h64" stroke="#24394F" stroke-width="4" stroke-linecap="round"></path><rect x="88" y="102" width="64" height="28" rx="6" fill="#1D3248"></rect><circle cx="100" cy="116" r="4.5" fill="#C9B0E6"></circle><circle cx="114" cy="116" r="4.5" fill="#3B5068"></circle><rect x="90" y="144" width="14" height="10" rx="2" fill="#5B646F"></rect><rect x="113" y="144" width="14" height="10" rx="2" fill="#5B646F"></rect><rect x="136" y="144" width="14" height="10" rx="2" fill="' . $accent . '"></rect></svg>';

		case 'avr':
			return '<svg viewBox="0 0 240 180" class="cedra-tech-svg" role="img" aria-label="Voltage regulator illustration"><ellipse cx="120" cy="160" rx="90" ry="5" fill="#0B1C2C" opacity=".1"></ellipse><rect x="30" y="36" width="180" height="112" rx="12" fill="#0B1C2C"></rect><circle cx="70" cy="74" r="14" fill="#1D3248" stroke="#C9B0E6" stroke-width="2"></circle><path d="M70 74l6-8" stroke="#C9B0E6" stroke-width="2.5" stroke-linecap="round"></path><circle cx="112" cy="74" r="14" fill="#1D3248" stroke="#C9B0E6" stroke-width="2"></circle><path d="M112 74l-7-7" stroke="#C9B0E6" stroke-width="2.5" stroke-linecap="round"></path><rect x="144" y="56" width="50" height="36" rx="6" fill="#1D3248"></rect><circle cx="184" cy="66" r="3.5" fill="#9F61CF"></circle><rect x="42" y="116" width="156" height="20" rx="4" fill="#24394F"></rect></svg>';

		case 'comm':
			return '<svg viewBox="0 0 240 180" class="cedra-tech-svg" role="img" aria-label="Communication interface illustration"><ellipse cx="120" cy="150" rx="70" ry="5" fill="#0B1C2C" opacity=".1"></ellipse><rect x="60" y="50" width="120" height="88" rx="16" fill="#0B1C2C"></rect><rect x="44" y="82" width="18" height="24" rx="3" fill="#5B646F"></rect><rect x="178" y="74" width="18" height="40" rx="3" fill="' . $accent . '"></rect><circle cx="84" cy="70" r="4" fill="#C9B0E6"></circle><circle cx="98" cy="70" r="4" fill="#3B5068"></circle><path d="M80 112h64M80 122h40" stroke="#24394F" stroke-width="4" stroke-linecap="round"></path></svg>';

		case 'filter':
			return '<svg viewBox="0 0 240 180" class="cedra-tech-svg" role="img" aria-label="Filtration element illustration"><ellipse cx="120" cy="168" rx="62" ry="5" fill="#0B1C2C" opacity=".1"></ellipse><rect x="78" y="20" width="84" height="18" rx="6" fill="#1D3248"></rect><rect x="72" y="34" width="96" height="122" rx="14" fill="#0B1C2C"></rect><rect x="72" y="54" width="96" height="12" fill="' . $accent . '"></rect><path d="M90 82h60M90 96h44M90 110h52" stroke="#24394F" stroke-width="4" stroke-linecap="round"></path><circle cx="100" cy="28" r="3" fill="#C9B0E6"></circle><circle cx="120" cy="28" r="4" fill="#C9B0E6"></circle><circle cx="140" cy="28" r="3" fill="#C9B0E6"></circle></svg>';

		case 'belt':
			return '<svg viewBox="0 0 240 180" class="cedra-tech-svg" role="img" aria-label="Drive belt and pulley assembly illustration"><ellipse cx="120" cy="166" rx="82" ry="5" fill="#0B1C2C" opacity=".1"></ellipse><path d="M68 48a38 38 0 1 0 0 76h104a28 28 0 1 0 0-56z" fill="none" stroke="#0B1C2C" stroke-width="14" stroke-linejoin="round"></path><path d="M68 48a38 38 0 1 0 0 76h104a28 28 0 1 0 0-56z" fill="none" stroke="#24394F" stroke-width="4" stroke-dasharray="8 8"></path><circle cx="68" cy="86" r="24" fill="#1D3248" stroke="#C9B0E6" stroke-width="2.5"></circle><circle cx="68" cy="86" r="8" fill="' . $accent . '"></circle><circle cx="172" cy="96" r="16" fill="#1D3248" stroke="#C9B0E6" stroke-width="2"></circle><circle cx="172" cy="96" r="5" fill="#C9B0E6"></circle></svg>';

		case 'gasket':
			return '<svg viewBox="0 0 240 180" class="cedra-tech-svg" role="img" aria-label="Gasket and sealing assembly illustration"><ellipse cx="120" cy="164" rx="88" ry="5" fill="#0B1C2C" opacity=".1"></ellipse><rect x="34" y="38" width="172" height="104" rx="18" fill="#0B1C2C"></rect><circle cx="76" cy="90" r="22" fill="#EDE6F7" stroke="#24394F" stroke-width="3"></circle><circle cx="124" cy="90" r="22" fill="#EDE6F7" stroke="#24394F" stroke-width="3"></circle><circle cx="170" cy="90" r="16" fill="#1D3248" stroke="' . $accent . '" stroke-width="3"></circle><circle cx="48" cy="52" r="4" fill="#C9B0E6"></circle><circle cx="192" cy="52" r="4" fill="#C9B0E6"></circle><circle cx="48" cy="128" r="4" fill="#C9B0E6"></circle><circle cx="192" cy="128" r="4" fill="#C9B0E6"></circle></svg>';

		case 'engine':
			return '<svg viewBox="0 0 240 180" class="cedra-tech-svg" role="img" aria-label="Genset engine component illustration"><ellipse cx="120" cy="166" rx="84" ry="5" fill="#0B1C2C" opacity=".1"></ellipse><rect x="46" y="44" width="148" height="96" rx="14" fill="#0B1C2C"></rect><rect x="64" y="28" width="112" height="20" rx="6" fill="#1D3248"></rect><circle cx="94" cy="94" r="24" fill="#1D3248" stroke="#C9B0E6" stroke-width="2.5"></circle><circle cx="94" cy="94" r="9" fill="' . $accent . '"></circle><rect x="132" y="68" width="44" height="12" rx="4" fill="#24394F"></rect><rect x="132" y="88" width="44" height="12" rx="4" fill="#24394F"></rect><rect x="132" y="108" width="30" height="12" rx="4" fill="#C9B0E6" opacity=".8"></rect></svg>';

		case 'controller':
		default:
			return '<svg viewBox="0 0 240 180" class="cedra-tech-svg" role="img" aria-label="Controller illustration"><ellipse cx="120" cy="168" rx="86" ry="5" fill="#0B1C2C" opacity=".1"></ellipse><rect x="30" y="26" width="180" height="128" rx="14" fill="#0B1C2C"></rect><rect x="46" y="42" width="104" height="64" rx="6" fill="#1D3248"></rect><path d="M58 58h48M58 70h76M58 82h32" stroke="#C9B0E6" stroke-width="3" stroke-linecap="round"></path><circle cx="178" cy="58" r="7" fill="#24394F"></circle><circle cx="178" cy="80" r="7" fill="#24394F"></circle><circle cx="178" cy="102" r="7" fill="#24394F"></circle><rect x="46" y="120" width="18" height="14" rx="3" fill="#24394F"></rect><rect x="70" y="120" width="18" height="14" rx="3" fill="#24394F"></rect><rect x="94" y="120" width="18" height="14" rx="3" fill="#24394F"></rect><rect x="118" y="120" width="18" height="14" rx="3" fill="#24394F"></rect><circle cx="182" cy="128" r="10" fill="' . $accent . '"></circle></svg>';
	}
}
