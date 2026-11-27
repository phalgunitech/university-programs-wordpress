<?php
defined( 'ABSPATH' ) || exit;

function upm_register_tracking_route() {
	register_rest_route( 'programs/v1', '/track', array(
		'methods' => WP_REST_Server::CREATABLE,
		'callback' => 'upm_track_event',
		'permission_callback' => '__return_true',
		'args' => array( 'type' => array( 'required' => true, 'sanitize_callback' => 'sanitize_key' ), 'event' => array( 'required' => true, 'sanitize_callback' => 'sanitize_key' ) ),
	) );
}

function upm_track_event( WP_REST_Request $request ) {
	$raw_body = $request->get_body();
	if ( strlen( $raw_body ) > 4096 ) { return new WP_Error( 'tracking_payload_too_large', 'Tracking payload is too large.', array( 'status' => 413 ) ); }
	$data = json_decode( $raw_body, true );
	if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $data ) ) { return new WP_Error( 'invalid_tracking_json', 'Tracking payload must be valid JSON.', array( 'status' => 400 ) ); }
	if ( array_diff( array_keys( $data ), array( 'type', 'event', 'link', 'button', 'content_data' ) ) ) { return new WP_Error( 'unknown_tracking_keys', 'Tracking payload contains unknown keys.', array( 'status' => 400 ) ); }
	if ( 'click' !== ( $data['type'] ?? '' ) || ! in_array( $data['event'] ?? '', array( 'link', 'button' ), true ) ) { return new WP_Error( 'invalid_tracking_payload', 'Invalid tracking payload.', array( 'status' => 400 ) ); }
	$key = $data['event'];
	if ( ! isset( $data[ $key ] ) || ! is_array( $data[ $key ] ) || array_diff( array_keys( $data[ $key ] ), array( 'label' ) ) ) { return new WP_Error( 'invalid_tracking_entity', 'Invalid tracking entity.', array( 'status' => 400 ) ); }
	$label = sanitize_key( $data[ $key ]['label'] ?? '' );
	$content = $data['content_data'] ?? array();
	if ( ! is_array( $content ) || array_diff( array_keys( $content ), array( 'sectionName', 'uiComponent' ) ) ) { return new WP_Error( 'invalid_tracking_context', 'Invalid tracking context.', array( 'status' => 400 ) ); }
	if ( ! $label || strlen( $label ) > 60 || ! preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $label ) ) { return new WP_Error( 'invalid_tracking_label', 'Invalid tracking label.', array( 'status' => 400 ) ); }
	$section = sanitize_key( $content['sectionName'] ?? '' );
	$component = sanitize_key( $content['uiComponent'] ?? '' );
	if ( strlen( $section ) > 60 || strlen( $component ) > 60 ) { return new WP_Error( 'invalid_tracking_context', 'Tracking context is too long.', array( 'status' => 400 ) ); }
	$rate_key = 'upm_track_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? 'unknown' ) );
	$rate_count = (int) get_transient( $rate_key );
	if ( $rate_count >= 60 ) { return new WP_Error( 'tracking_rate_limited', 'Too many tracking requests.', array( 'status' => 429 ) ); }
	set_transient( $rate_key, $rate_count + 1, MINUTE_IN_SECONDS );
	$payload = array( 'type' => 'click', 'event' => $data['event'], $key => array( 'label' => $label ), 'content_data' => array( 'sectionName' => $section, 'uiComponent' => $component ) );
	$upload = wp_upload_dir();
	$dir = trailingslashit( $upload['basedir'] ) . 'logs';
	if ( ! wp_mkdir_p( $dir ) ) { return new WP_Error( 'tracking_storage_error', 'Tracking storage unavailable.', array( 'status' => 500 ) ); }
	if ( ! file_exists( $dir . '/index.php' ) ) { wp_filesystem(); global $wp_filesystem; if ( $wp_filesystem ) { $wp_filesystem->put_contents( $dir . '/index.php', "<?php\n", FS_CHMOD_FILE ); } }
	$file = apply_filters( 'upm_tracking_log_path', $dir . '/data-track.log' );
	$line = wp_json_encode( array( 'timestamp' => gmdate( 'c' ), 'page_url' => esc_url_raw( $request->get_header( 'referer' ) ), 'payload' => $payload ) ) . PHP_EOL;
	if ( false === file_put_contents( $file, $line, FILE_APPEND | LOCK_EX ) ) { return new WP_Error( 'tracking_write_error', 'Tracking event could not be stored.', array( 'status' => 500 ) ); }
	return new WP_REST_Response( array( 'recorded' => true ), 202 );
}
