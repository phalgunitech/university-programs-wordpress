<?php
defined( 'ABSPATH' ) || exit;

function upm_register_program_meta() {
	$auth = array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'show_in_rest' => true, 'single' => true );
	register_post_meta( 'program', '_upm_academic_year', $auth );
	register_post_meta( 'program', '_upm_duration', $auth );
	register_post_meta( 'program', '_upm_sections', array(
		'type' => 'array', 'single' => true, 'show_in_rest' => array( 'schema' => array( 'type' => 'array', 'items' => array( 'type' => 'object' ) ) ),
		'sanitize_callback' => 'upm_sanitize_sections',
	) );
}

function upm_sanitize_sections( $sections ) {
	if ( ! is_array( $sections ) ) { return array(); }
	$out = array();
	foreach ( array_slice( $sections, 0, 20 ) as $section ) {
		if ( ! is_array( $section ) ) { continue; }
		$out[] = array(
			'title' => sanitize_text_field( $section['title'] ?? '' ),
			'description' => wp_kses_post( $section['description'] ?? '' ),
			'image' => absint( $section['image'] ?? 0 ),
		);
	}
	return $out;
}

function upm_add_program_meta_box() {
	add_meta_box( 'upm_program_details', 'Program details', 'upm_render_program_meta_box', 'program', 'normal', 'high' );
}

function upm_render_program_meta_box( $post ) {
	wp_nonce_field( 'upm_save_program', 'upm_program_nonce' );
	$sections = get_post_meta( $post->ID, '_upm_sections', true );
	if ( ! is_array( $sections ) ) { $sections = array(); }
	?>
	<div id="upm-program-editor" data-sections="<?php echo esc_attr( wp_json_encode( $sections ) ); ?>" data-year="<?php echo esc_attr( get_post_meta( $post->ID, '_upm_academic_year', true ) ); ?>" data-duration="<?php echo esc_attr( get_post_meta( $post->ID, '_upm_duration', true ) ); ?>"></div>
	<?php
}

function upm_save_program_meta( $post_id, $post ) {
	if ( ! isset( $_POST['upm_program_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['upm_program_nonce'] ) ), 'upm_save_program' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
	if ( isset( $_POST['upm_academic_year'] ) ) { update_post_meta( $post_id, '_upm_academic_year', sanitize_text_field( wp_unslash( $_POST['upm_academic_year'] ) ) ); }
	if ( isset( $_POST['upm_duration'] ) ) { update_post_meta( $post_id, '_upm_duration', sanitize_text_field( wp_unslash( $_POST['upm_duration'] ) ) ); }
	if ( isset( $_POST['upm_sections'] ) ) {
		$raw = json_decode( wp_unslash( $_POST['upm_sections'] ), true );
		update_post_meta( $post_id, '_upm_sections', upm_sanitize_sections( $raw ) );
	}
}
