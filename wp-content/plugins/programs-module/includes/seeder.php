<?php
defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', function () {
	if ( ! isset( $_GET['upm_seed'] ) || ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'upm_seed' ) ) { return; }
	upm_seed_programs();
	wp_safe_redirect( admin_url( 'edit.php?post_type=program&seeded=1' ) );
	exit;
} );

function upm_seed_programs() {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	$terms = array();
	foreach ( array( 'Undergraduate', 'Postgraduate' ) as $name ) { $term = term_exists( $name, 'program-type' ); $terms[ $name ] = $term ? (int) $term['term_id'] : (int) wp_insert_term( $name, 'program-type' )['term_id']; }
	$names = array( 'Computer Science', 'Business Management', 'Biomedical Sciences', 'International Relations', 'Creative Design', 'MBA Leadership', 'MSc Data Science', 'MSc Finance', 'MSc Sustainable Energy', 'MSc Digital Marketing' );
	foreach ( $names as $i => $name ) {
		if ( get_page_by_title( $name, OBJECT, 'program' ) ) { continue; }
		$id = wp_insert_post( array( 'post_type' => 'program', 'post_status' => 'publish', 'post_title' => $name, 'post_content' => 'Explore a future-focused programme designed for ambitious students.', 'post_excerpt' => 'A practical, globally connected programme at Aberdeen Mumbai.' ) );
		if ( is_wp_error( $id ) ) { continue; }
		wp_set_object_terms( $id, $i < 5 ? $terms['Undergraduate'] : $terms['Postgraduate'], 'program-type' );
		update_post_meta( $id, '_upm_academic_year', '2025-26' ); update_post_meta( $id, '_upm_duration', $i < 5 ? '3 Years' : '1 Year' );
		update_post_meta( $id, '_upm_sections', array( array( 'title' => 'Programme overview', 'description' => 'Build relevant knowledge, practical skills, and an international perspective.', 'image' => 0 ) ) );
		upm_attach_seed_image( $id, $i < 5 ? 'programme-ug.png' : 'programme-pg.png' );
	}
}

function upm_attach_seed_image( $post_id, $filename ) {
	$source = get_template_directory() . '/assets/images/' . $filename;
	if ( ! file_exists( $source ) ) { return; }
	$bits = wp_upload_bits( $filename, null, file_get_contents( $source ) );
	if ( ! empty( $bits['error'] ) ) { return; }
	$type = wp_check_filetype( $filename, null );
	$attachment_id = wp_insert_attachment( array( 'post_mime_type' => $type['type'], 'post_title' => sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ), 'post_status' => 'inherit' ), $bits['file'], $post_id );
	if ( $attachment_id && ! is_wp_error( $attachment_id ) ) {
		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $bits['file'] ) );
		set_post_thumbnail( $post_id, $attachment_id );
	}
}
