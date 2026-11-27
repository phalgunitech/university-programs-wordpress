<?php
defined( 'ABSPATH' ) || exit;

function upm_register_program_content() {
	register_post_type( 'program', array(
		'labels' => array( 'name' => 'Programs', 'singular_name' => 'Program', 'add_new_item' => 'Add Program' ),
		'public' => true,
		'has_archive' => true,
		'show_in_rest' => true,
		'menu_icon' => 'dashicons-welcome-learn-more',
		'rewrite' => array( 'slug' => 'programmes', 'with_front' => false ),
		'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
	) );

	register_taxonomy( 'program-type', 'program', array(
		'labels' => array( 'name' => 'Program Types', 'singular_name' => 'Program Type' ),
		'public' => true,
		'show_in_rest' => true,
		'hierarchical' => true,
		'rewrite' => array( 'slug' => 'programmes', 'with_front' => false ),
	) );
}
