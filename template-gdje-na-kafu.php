<?php
/**
 * Template Name: Gdje na kafu — Region
 *
 * Hero slika grada + grid preview kartica (tema's get_preview_card, SEO-friendly).
 * Meta polje na stranici: _edm_region_slug (slug region taksonomije, npr. "tivat")
 * Featured image: stranica → region term image → tamni fallback
 *
 * Text domain: espresso4me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

the_post();

$post_id     = get_the_ID();
$region_slug = get_post_meta( $post_id, '_edm_region_slug', true );

// Slika: 1) featured image stranice, 2) region term image, 3) nema
$hero_image = '';
if ( has_post_thumbnail( $post_id ) ) {
	$hero_image = get_the_post_thumbnail_url( $post_id, 'full' );
} elseif ( $region_slug ) {
	$term = get_term_by( 'slug', $region_slug, 'region' );
	if ( $term && ! is_wp_error( $term ) ) {
		$img_id = get_term_meta( $term->term_id, 'image', true );
		if ( $img_id ) {
			$hero_image = wp_get_attachment_image_url( $img_id, 'full' );
		}
	}
}

get_header();
?>

<div class="edm-region-page">

	<div class="post-hero<?php echo $hero_image ? '' : ' post-hero--no-image'; ?>"
		<?php if ( $hero_image ) : ?>
			style="background-image: url('<?php echo esc_url( $hero_image ); ?>');"
		<?php endif; ?>
	>
		<div class="post-hero-overlay"></div>
		<div class="post-hero-content edm-hero-content">
			<div class="post-hero-meta">
				<span class="post-hero-tag">
					<i class="fa fa-coffee"></i>
					<?php echo esc_html_x( 'Gdje na kafu', 'hero badge', 'espresso4me' ); ?>
				</span>
				<?php if ( $region_slug ) :
					$term_label = get_term_by( 'slug', $region_slug, 'region' );
				?>
					<span class="post-hero-tag">
						<i class="mi place"></i>
						<?php echo $term_label ? esc_html( $term_label->name ) : esc_html( ucfirst( $region_slug ) ); ?>
					</span>
				<?php endif; ?>
			</div>

			<h1 class="post-hero-title"><?php the_title(); ?></h1>
		</div>
	</div>

</div>

<?php
if ( $region_slug ) :
	$region_term = get_term_by( 'slug', $region_slug, 'region' );
	$region_name = $region_term ? $region_term->name : ucfirst( $region_slug );

	$listings_q = new WP_Query( [
		'post_type'              => 'job_listing',
		'post_status'            => 'publish',
		'posts_per_page'         => 24,
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'update_post_meta_cache' => true,
		'tax_query'              => [ [
			'taxonomy' => 'region',
			'field'    => 'slug',
			'terms'    => $region_slug,
		] ],
	] );

	if ( $listings_q->have_posts() ) :
		$schema_items = [];
		$position     = 1;
?>

<section class="edm-listings-section">
	<div class="edm-listings-inner">

		<!-- ============================================================
		     BREADCRUMBS (stavka 7) — BreadcrumbList schema
		     ============================================================ -->
		<nav class="edm-breadcrumbs" aria-label="<?php echo esc_attr_x( 'Navigacioni put', 'aria-label', 'espresso4me' ); ?>">
			<ol itemscope itemtype="https://schema.org/BreadcrumbList">
				<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<a itemprop="item" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<span itemprop="name"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
					</a>
					<meta itemprop="position" content="1" />
				</li>
				<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<span itemprop="name"><?php the_title(); ?></span>
					<link itemprop="item" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" />
					<meta itemprop="position" content="2" />
				</li>
			</ol>
		</nav>

		<!-- Card grid -->
		<div class="edm-cards-grid">
		<?php while ( $listings_q->have_posts() ) : $listings_q->the_post();
			$lid = get_the_ID();
			$schema_items[] = [
				'@type'    => 'ListItem',
				'position' => $position++,
				'url'      => get_permalink(),
				'name'     => get_the_title(),
			];
			echo \MyListing\get_preview_card( $lid ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		endwhile;
		wp_reset_postdata(); ?>
		</div>

	</div>
</section>

<?php
	// ItemList JSON-LD schema
	if ( ! empty( $schema_items ) ) :
		/* translators: %s: naziv grada/regiona */
		$schema_name = sprintf( _x( 'Kafići u %s', 'schema ItemList name', 'espresso4me' ), $region_name );
		/* translators: %s: naziv grada/regiona */
		$schema_desc = get_post_meta( $post_id, '_yoast_wpseo_metadesc', true )
			?: sprintf( _x( 'Pronađi kafiće i mjesta za kafu u %s.', 'schema ItemList description', 'espresso4me' ), $region_name );

		$schema = [
			'@context'        => 'https://schema.org',
			'@type'           => 'ItemList',
			'name'            => $schema_name,
			'description'     => $schema_desc,
			'url'             => get_permalink( $post_id ),
			'numberOfItems'   => count( $schema_items ),
			'itemListElement' => $schema_items,
		];
		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "</script>\n";
	endif;
	?>

<?php endif; // have_posts ?>

<!-- ============================================================
     INTERNI LINKOVI — ostali gradovi (stavka 8)
     Distribuiše link authority između region stranica
     ============================================================ -->
<?php
$all_regions   = ML_Region_Pages::get_regions();
$other_regions = array_filter( $all_regions, function( $r ) use ( $region_slug ) {
	return $r['region_slug'] !== $region_slug;
} );

if ( ! empty( $other_regions ) ) : ?>
<section class="edm-other-cities">
	<div class="edm-listings-inner">
		<h2 class="edm-other-cities-heading">
			<?php echo esc_html_x( 'Gdje na kafu u Crnoj Gori', 'other cities heading', 'espresso4me' ); ?>
		</h2>
		<ul class="edm-other-cities-list">
		<?php foreach ( $other_regions as $r ) :
			$other_term = get_term_by( 'slug', $r['region_slug'], 'region' );
			if ( ! $other_term || is_wp_error( $other_term ) ) continue;
			$other_url = get_term_link( $other_term );
			if ( is_wp_error( $other_url ) ) continue;
		?>
			<li>
				<a href="<?php echo esc_url( $other_url ); ?>">
					<?php
					/* translators: %s: naziv grada u lokativu (npr. "Podgorici") */
					printf(
						esc_html_x( 'Gdje na kafu u %s', 'other city link', 'espresso4me' ),
						esc_html( $r['region'] )
					);
					?>
				</a>
			</li>
		<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php endif; ?>

<?php endif; // region_slug ?>

<?php get_footer(); ?>
