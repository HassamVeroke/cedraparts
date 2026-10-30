<?php
/**
 * Search Results Template — Routes All Searches into the Option 2 Parts Catalog
 *
 * @package CedraParts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
get_template_part( 'template-parts/catalog-view' );
get_template_part( 'template-parts/help-band' );
get_footer();

