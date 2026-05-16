<?php
get_header();
?>

<section class="custom-home-section">
    <div class="custom-home-section-inner">
        <div class="custom-home-section-header">
            <?php
            $archive_label = is_category() ? 'Kategorija' : ( is_tag() ? 'Oznaka' : ( is_author() ? 'Autor' : 'Arhiva' ) );
            $archive_title = '';
            if ( is_category() || is_tag() ) {
                $archive_title = single_term_title( '', false );
            } elseif ( is_post_type_archive() ) {
                $archive_title = post_type_archive_title( '', false );
            } else {
                $archive_title = get_the_archive_title();
            }
            ?>
            <span><?php echo esc_html( $archive_label ); ?>:</span>
            <h2><?php echo esc_html( $archive_title ); ?></h2>
        </div>

        <div class="custom-home-posts-grid">
            <?php
            $paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
            $posts_query = new WP_Query( array(
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => 6,
                'paged'          => $paged,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ) );

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
                ?>
        </div>

        <div class="custom-home-pagination">
            <?php
                $arrow_left = '<span class="pagination-arrow" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" xmlns="http://www.w3.org/2000/svg"><path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6z" fill="currentColor"/></svg></span>';
                $arrow_right = '<span class="pagination-arrow" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" xmlns="http://www.w3.org/2000/svg"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6z" fill="currentColor"/></svg></span>';

                $links = paginate_links( array(
                    'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                    'format'    => '?paged=%#%',
                    'current'   => max( 1, $paged ),
                    'total'     => $posts_query->max_num_pages,
                    'type'      => 'array',
                    'mid_size'  => 1,
                    'prev_text' => $arrow_left,
                    'next_text' => $arrow_right,
                ) );

                if ( is_array( $links ) ) {
                    echo '<nav class="navigation pagination" role="navigation"><div class="nav-links">' . implode( '', $links ) . '</div></nav>';
                }
            ?>
        </div>
        <?php
                wp_reset_postdata();
            else :
                echo '<p class="custom-home-empty">Nema članaka za prikaz.</p>';
            endif;
            ?>
        
    </div>
</section>

<?php
get_footer();
