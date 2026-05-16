<?php
/**
 * Template Name: Custom Homepage (With Header/Footer)
 * Template Post Type: page
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); ?>

<div class="custom-homepage">
    <?php
    $custom_hero_bg = get_post_meta( get_the_ID(), 'custom_home_hero_bg', true );
    $hero_bg_url   = $custom_hero_bg ? esc_url( $custom_hero_bg ) : get_theme_file_uri( '/assets/images/home-hero-bg.jpg' );
    ?>

    <section class="custom-home-hero" style="background-image: url('<?php echo $hero_bg_url; ?>');">
        <div class="custom-home-hero-overlay"></div>
        <div class="custom-home-hero-inner">
            <div class="custom-home-hero-copy">
                <h1>Pronađi idealno mjesto za kafu i uživanje</h1>
                <p class="custom-home-hero-text">Pronađi mjesto za jutarnju kafu, rad iz kafića ili opuštanje sa društvom.</p>
            </div>

            <div class="custom-home-hero-shortcode">
                <?php echo do_shortcode( '[27-search-form listing_types="coffee-shop" tabs_mode="transparent" types_display="tabs" box_shadow="no"]' ); ?>
            </div>
        </div>
    </section>

    <section class="custom-home-section">
        <div class="custom-home-section-inner">
            <div class="custom-home-section-header">
                <span>Mjesta koja vrijedi posjetiti</span>
                <h2>Naša preporuka za kafu, odmor i uživanje</h2>
            </div>

            <div class="custom-home-listings-grid">
            <?php
            $listings_query = new WP_Query([
                'post_type'      => 'job_listing',
                'post_status'    => 'publish',
                'posts_per_page' => 6,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ]);

            if ( $listings_query->have_posts() ) :
                while ( $listings_query->have_posts() ) : $listings_query->the_post();
                    if ( function_exists( '\MyListing\get_preview_card' ) ) {
                        echo \MyListing\get_preview_card( get_the_ID() );
                    } else {
                        ?>
                        <article class="custom-home-listing-card">
                            <a href="<?php the_permalink(); ?>">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <div class="custom-home-listing-image"><?php the_post_thumbnail( 'medium' ); ?></div>
                                <?php endif; ?>
                                <h3><?php the_title(); ?></h3>
                            </a>
                        </article>
                        <?php
                    }
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p class="custom-home-empty">Nema aktivnih lokala za prikaz.</p>';
            endif;
            ?>
            </div>
        </div>
    </section>

    <section class="custom-home-section">
        <div class="custom-home-section-inner">
            <div class="custom-home-section-header">
                <span>Gdje na kafu u Crnoj Gori?</span>
                <h2>Pronađi kafiće, coffee barove, picerije i pabove u svom omiljenom gradu.</h2>
            </div>

            <div class="cg-cities-grid custom-home-cities-grid" style="--cg-cols:4; --cg-cols-tablet:2;">
            <?php
            $city_slugs = [
                'podgorica', 'budva', 'bar', 'niksic',
                'herceg-novi', 'kotor', 'tivat', 'ulcinj',
            ];

            $region_terms = get_terms([
                'taxonomy'   => 'region',
                'slug'       => $city_slugs,
                'hide_empty' => false,
            ]);

            $terms_by_slug = [];
            foreach ( $region_terms as $term ) {
                $terms_by_slug[ $term->slug ] = $term;
            }

            foreach ( $city_slugs as $slug ) {
                $term = $terms_by_slug[ $slug ] ?? null;
                if ( ! $term ) {
                    continue;
                }

                $name      = esc_html( $term->name );
                $url       = esc_url( get_term_link( $term ) );
                $img_id    = get_term_meta( $term->term_id, 'image', true );
                $img_url   = $img_id ? esc_url( wp_get_attachment_image_url( $img_id, 'large' ) ) : '';
                $counts_raw = get_term_meta( $term->term_id, 'listings_full_count', true );
                $counts_arr = $counts_raw ? json_decode( $counts_raw, true ) : [];
                $count      = $counts_arr['coffee-shop'] ?? $term->count;
                $bg         = $img_url ? 'background-image:url(' . $img_url . ');' : 'background:#c8a97e;';
                ?>
                <a class="cg-city-card custom-home-city-card" href="<?php echo $url; ?>" style="position:relative;display:flex;align-items:flex-end;text-decoration:none;overflow:hidden;<?php echo esc_attr( $bg ); ?>background-size:cover;background-position:center;">
                    <div class="cg-city-overlay" style="position:absolute;inset:0;background:rgba(0,0,0,.45);"></div>
                    <div class="cg-city-info" style="position:relative;z-index:1;padding:18px 20px;color:#fff;width:100%;">
                        <div class="cg-city-name" style="font-size:18px;font-weight:700;line-height:1.2;"><?php echo $name; ?></div>
                        <div class="cg-city-count" style="font-size:13px;opacity:0.85;margin-top:4px;"><?php echo esc_html( $count ); ?> mjesta</div>
                    </div>
                </a>
                <?php
            }
            ?>
            </div>
        </div>
    </section>

    <section class="custom-home-section custom-home-cta-section" style="background-image:url('<?php echo esc_url( $hero_bg_url ); ?>');">
        <div class="custom-home-cta-inner">
            <div class="custom-home-cta-copy">
                <span>Imate lokal koji zaslužuje da bude viđen?</span>
                <h2>Dodajte svoj lokal na <span class="custom-home-highlight">Espresso4.me</span> vodič najboljih mjesta za kafu</h2>
                <a href="<?php echo esc_url( home_url( '/dodaj-kafic/' ) ); ?>" class="custom-home-cta-button">Dodaj svoj lokal</a>
            </div>

            <div class="custom-home-cta-cards">
                <div class="custom-home-cta-card">
                    <h3>Veća vidljivost</h3>
                    <p>Vaš lokal će biti prikazan korisnicima koji aktivno traže novo mjesto za kafu, odmor i uživanje u Crnoj Gori.</p>
                </div>
                <div class="custom-home-cta-card">
                    <h3>Više gostiju</h3>
                    <p>Doprite do novih gostiju i predstavite svoj lokal ljudima koji žele da otkriju kvalitetnu i zanimljivu ponudu.</p>
                </div>
                <div class="custom-home-cta-card">
                    <h3>Jednostavna prijava</h3>
                    <p>Podijelite osnovne informacije o lokalu, a mi ćemo vaš lokal dodati na preglednu listu i dostupnost.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="custom-home-section">
        <div class="custom-home-section-inner">
            <div class="custom-home-section-header">
                <span>Preporuke i priče iz coffee svijeta</span>
                <h2>Savjeti, preporuke i zanimljive priče iz coffee svijeta</h2>
            </div>

            <div class="custom-home-posts-grid">
            <?php
            $posts_query = new WP_Query([
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => 6,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ]);

            if ( $posts_query->have_posts() ) :
                while ( $posts_query->have_posts() ) : $posts_query->the_post();
                    $card_image = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'large' ) : '';
                    ?>
                    <article class="custom-home-post-card">
                        <a href="<?php the_permalink(); ?>">
                            <?php if ( $card_image ) : ?>
                                <div class="custom-home-post-image" style="background-image:url(<?php echo esc_url( $card_image ); ?>);"></div>
                            <?php endif; ?>
                            <div class="custom-home-post-content">
                                <?php if ( get_the_category() ) : ?>
                                    <span class="custom-home-post-tag"><?php echo esc_html( get_the_category()[0]->name ); ?></span>
                                <?php endif; ?>
                                <h3><?php the_title(); ?></h3>
                                <p><?php echo wp_kses_post( wp_trim_words( get_the_excerpt(), 18, '...' ) ); ?></p>
                                <span class="custom-home-post-readmore">PROČITAJ VIŠE</span>
                            </div>
                        </a>
                    </article>
                    <?php
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p class="custom-home-empty">Nema članaka za prikaz.</p>';
            endif;
            ?>
            </div>
        </div>
    </section>
</div>

<?php get_footer();
