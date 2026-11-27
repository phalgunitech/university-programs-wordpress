<?php
defined( 'ABSPATH' ) || exit;

function upm_register_program_routes() {
	add_rewrite_rule( '^programmes/(undergraduate|postgraduate)/?$', 'index.php?program-type=$matches[1]', 'top' );
	add_rewrite_rule( '^programmes/([^/]+)/?$', 'index.php?program=$matches[1]', 'top' );
}

add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'program_type';
	return $vars;
} );

add_action( 'pre_get_posts', function ( $query ) {
	if ( is_admin() || ! $query->is_main_query() || 'program' !== $query->get( 'post_type' ) || ! $query->get( 'program_type' ) ) {
		return;
	}
	$query->set( 'tax_query', array( array( 'taxonomy' => 'program-type', 'field' => 'slug', 'terms' => sanitize_title( $query->get( 'program_type' ) ) ) ) );
} );
