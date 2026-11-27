<?php
/**
 * Programme archive and programme-type archive template.
 * Used for /programmes/, /programmes/undergraduate/, and /programmes/postgraduate/.
 */
get_header();
$term_slug = get_query_var( 'program_type' );
$queried   = get_queried_object();
if ( $queried instanceof WP_Term && 'program-type' === $queried->taxonomy ) {
	$term_slug = $queried->slug;
}
$term = $term_slug ? get_term_by( 'slug', sanitize_title( $term_slug ), 'program-type' ) : false;
$query_args = array(
	'post_type'      => 'program',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'orderby'        => 'title',
	'order'          => 'ASC',
);
if ( $term && ! is_wp_error( $term ) ) {
	$query_args['tax_query'] = array(
		array(
			'taxonomy' => 'program-type',
			'field'    => 'term_id',
			'terms'    => $term->term_id,
		),
	);
}
$programs = new WP_Query( $query_args );
$title    = $term ? $term->name . ' Programmes' : 'All Programmes';
?>
<main class="upm-page">
	<header class="upm-page-header">
		<div class="container">
			<nav class="upm-breadcrumbs" aria-label="Breadcrumbs">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
				<span aria-hidden="true"> / </span><span>Programmes</span>
			</nav>
			<h1><?php echo esc_html( $title ); ?></h1>
			<p>Explore our undergraduate and postgraduate opportunities.</p>
		</div>
	</header>
	<div class="container">
		<nav class="upm-filter-bar" aria-label="Filter programmes">
			<?php
			$filters = array(
				'all'           => array( 'label' => 'All', 'url' => um_programs_url() ),
				'undergraduate' => array( 'label' => 'Undergraduate', 'url' => um_programs_url( 'undergraduate' ) ),
				'postgraduate'  => array( 'label' => 'Postgraduate', 'url' => um_programs_url( 'postgraduate' ) ),
			);
			foreach ( $filters as $slug => $filter ) :
				$is_active = ( 'all' === $slug && ! $term ) || ( $term && $term->slug === $slug );
				?>
				<a class="upm-filter <?php echo $is_active ? 'is-active' : ''; ?>" href="<?php echo esc_url( $filter['url'] ); ?>">
					<?php echo esc_html( $filter['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
		<?php if ( $programs->have_posts() ) : ?>
			<div class="upm-program-grid">
				<?php while ( $programs->have_posts() ) : $programs->the_post(); ?>
					<?php if ( function_exists( 'upm_program_card' ) ) { upm_program_card( get_the_ID() ); } ?>
				<?php endwhile; ?>
			</div>
		<?php else : ?>
			<p class="upm-empty">No programmes found in this category.</p>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</div>
</main>
<?php get_footer(); ?>
