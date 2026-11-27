<?php
/**
 * Plugin Name: University Programs Module
 * Description: Programs content model, admin fields, routing, sample data, and click tracking.
 * Version: 1.0.0
 */

defined( 'ABSPATH' ) || exit;

define( 'UPM_VERSION', '1.0.0' );
define( 'UPM_FILE', __FILE__ );
define( 'UPM_DIR', plugin_dir_path( __FILE__ ) );

require_once UPM_DIR . 'includes/post-type.php';
require_once UPM_DIR . 'includes/meta.php';
require_once UPM_DIR . 'includes/routing.php';
require_once UPM_DIR . 'includes/tracking.php';
require_once UPM_DIR . 'includes/seeder.php';

register_activation_hook( __FILE__, 'upm_activate' );
function upm_activate() {
	upm_register_program_content();
	upm_register_program_meta();
	upm_register_program_routes();
	flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'upm_deactivate' );
function upm_deactivate() {
	flush_rewrite_rules();
}

add_action( 'init', 'upm_register_program_content' );
add_action( 'init', 'upm_register_program_meta' );
add_action( 'init', 'upm_register_program_routes' );
add_action( 'rest_api_init', 'upm_register_tracking_route' );
add_action( 'add_meta_boxes_program', 'upm_add_program_meta_box' );
add_action( 'save_post_program', 'upm_save_program_meta', 10, 2 );
add_action( 'admin_enqueue_scripts', 'upm_enqueue_admin_assets' );
add_action( 'wp_enqueue_scripts', 'upm_enqueue_tracking_asset' );

function upm_enqueue_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post-new.php', 'post.php' ), true ) || 'program' !== get_current_screen()->post_type ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'upm-admin', plugins_url( 'admin/admin.js', __FILE__ ), array( 'wp-element', 'wp-api-fetch' ), UPM_VERSION, true );
	wp_enqueue_style( 'upm-admin', plugins_url( 'admin/admin.css', __FILE__ ), array(), UPM_VERSION );
}

function upm_enqueue_tracking_asset() {
	wp_enqueue_script( 'upm-tracking', plugins_url( 'assets/tracking.js', __FILE__ ), array(), UPM_VERSION, true );
	wp_localize_script( 'upm-tracking', 'upmTracking', array( 'endpoint' => rest_url( 'programs/v1/track' ) ) );
}

function upm_track_attr( $event, $label, $section, $component ) {
	$payload = array(
		'type'         => 'click',
		'event'        => sanitize_key( $event ),
		$event         => array( 'label' => sanitize_key( $label ) ),
		'content_data' => array(
			'sectionName' => sanitize_key( $section ),
			'uiComponent' => sanitize_key( $component ),
		),
	);
	return 'data-track="' . esc_attr( wp_json_encode( $payload ) ) . '"';
}

function upm_program_card( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( ! $post_id ) {
		return;
	}
	$terms = get_the_terms( $post_id, 'program-type' );
	$term  = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
	$url   = get_permalink( $post_id );
	?>
	<article class="programme-card upm-program-card">
		<a class="upm-card-link" href="<?php echo esc_url( $url ); ?>" <?php echo upm_track_attr( 'link', 'program-card', 'programme-listing', 'componentCard' ); ?>>
			<div class="programme-card__media">
				<?php echo get_the_post_thumbnail( $post_id, 'large', array( 'class' => 'programme-card__img' ) ); ?>
			</div>
			<div class="programme-card__body">
				<h3 class="programme-card__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
				<span class="gold-underline underline-block"></span>
				<p class="programme-card__desc"><?php echo esc_html( get_post_meta( $post_id, '_upm_duration', true ) ); ?> · <?php echo esc_html( get_post_meta( $post_id, '_upm_academic_year', true ) ); ?></p>
				<?php if ( $term ) : ?><span class="text-gold"><?php echo esc_html( $term->name ); ?></span><?php endif; ?>
			</div>
		</a>
	</article>
	<?php
}
