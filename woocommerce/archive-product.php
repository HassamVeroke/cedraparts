<?php
/**
 * WooCommerce Product Archive Template (Shop, Category, Brand, Tag, & Product Search)
 *
 * @package CedraParts
 * @version 8.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

get_template_part( 'template-parts/catalog-view' );
get_template_part( 'template-parts/help-band' );

get_footer();
