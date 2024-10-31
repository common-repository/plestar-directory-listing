<?php
pdl_the_listing_sort_options();
?>

<div class="listings pdl-listings-list list">
    <?php if ( ! $query->have_posts() ): ?>
        <span class="no-listings">
            <?php _ex("No listings found.", 'templates', "PDM"); ?>
        </span>
    <?php else: ?>
        <?php
        while ( $query->have_posts() ) :
            $query->the_post();
            echo pdl_render_listing( null, 'excerpt' );
        endwhile;
        ?>

        <div class="pdl-pagination">
        <?php
        if ( function_exists('wp_pagenavi' ) ) :
            wp_pagenavi( array( 'query' => $query ) );
        else:
        ?>
            <span class="prev"><?php previous_posts_link( _x( '&laquo; Previous ', 'templates', 'PDM' ) ); ?></span>
            <span class="next"><?php next_posts_link( _x( 'Next &raquo;', 'templates', 'PDM' ), $query->max_num_pages ); ?></span>
        <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
