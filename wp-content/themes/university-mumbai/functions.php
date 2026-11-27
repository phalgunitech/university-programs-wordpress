<?php
defined( 'ABSPATH' ) || exit;

function um_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'gallery', 'caption', 'style', 'script' ) );
}
add_action( 'after_setup_theme', 'um_theme_setup' );

function um_enqueue_assets() {
	wp_enqueue_style( 'um-base', get_template_directory_uri() . '/css/style.css', array(), '1.0.0' );
	wp_enqueue_style( 'um-theme', get_stylesheet_uri(), array( 'um-base' ), '1.0.0' );
	wp_enqueue_script( 'um-main', get_template_directory_uri() . '/js/main.js', array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'um_enqueue_assets' );

function um_asset( $file ) { return get_template_directory_uri() . '/assets/images/' . ltrim( $file, '/' ); }

function um_programs_url( $type = '' ) {
	$url = get_post_type_archive_link( 'program' );
	return $type ? trailingslashit( $url ) . sanitize_title( $type ) . '/' : $url;
}
