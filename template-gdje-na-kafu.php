<?php
/**
 * Template Name: Gdje na kafu — Region
 *
 * Hero slika grada + mapa sa listom ispod (inline iframe na /region/{slug}/).
 * Meta polje na stranici: _edm_region_slug (slug region taksonomije, npr. "tivat")
 * Featured image: stranica → region term image → tamni fallback
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

// Explore URL — direktno na region taxonomy (isto što je /region/tivat/)
$explore_url = $region_slug ? home_url( '/region/' . $region_slug . '/' ) : '';

get_header();
?>

<div class="edm-region-page">

	<!-- ============================================================
	     HERO — isti izgled kao single blog post (.post-hero CSS)
	     ============================================================ -->
	<div class="post-hero<?php echo $hero_image ? '' : ' post-hero--no-image'; ?>"
		<?php if ( $hero_image ) : ?>
			style="background-image: url('<?php echo esc_url( $hero_image ); ?>');"
		<?php endif; ?>
	>
		<div class="post-hero-overlay"></div>
		<div class="post-hero-content">
			<div class="post-hero-meta">
				<span class="post-hero-tag">
					<i class="fa fa-coffee"></i>
					Gdje na kafu
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

	<!-- ============================================================
	     MAPA — inline iframe na /region/{slug}/
	     JS uklanja header/footer unutar iframe-a (isti domen)
	     ============================================================ -->
	<?php if ( $explore_url ) : ?>
	<div class="edm-region-explore">
		<iframe
			id="edm-explore-iframe"
			src="<?php echo esc_url( $explore_url ); ?>"
			title="<?php echo esc_attr( get_the_title() . ' — mapa' ); ?>"
			frameborder="0"
			loading="eager"
		></iframe>
	</div>
	<?php endif; ?>

</div>

<script>
(function () {
	var iframe = document.getElementById('edm-explore-iframe');
	if (!iframe) return;

	function injectStyle(iDoc) {
		if (!iDoc || !iDoc.head) return;
		if (iDoc.getElementById('edm-embed-css')) return;

		var s = iDoc.createElement('style');
		s.id = 'edm-embed-css';
		s.textContent =
			// Sakrij header i footer unutar iframe-a
			'.c27-main-header { display:none !important; }' +
			'header.site-header, #masthead { display:none !important; }' +
			'#wpadminbar { display:none !important; }' +
			'footer, .site-footer, #colophon { display:none !important; }' +
			// Ukloni padding koji ostavlja prazno mjesto umjesto headera
			'body.admin-bar { margin-top:0 !important; padding-top:0 !important; }' +
			'#page, body { padding-top:0 !important; }' +
			// Sakrij archive heading ("Regions > Tivat" naslov) unutar explore
			'.archive-page .archive-heading, .explore-page > .i-section:first-child { display:none !important; }';

		iDoc.head.appendChild(s);
	}

	iframe.addEventListener('load', function () {
		try {
			var iDoc = iframe.contentDocument || iframe.contentWindow.document;
			injectStyle(iDoc);
		} catch (e) { /* samo isti domen */ }
	});
})();
</script>

<?php get_footer(); ?>
