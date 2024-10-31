<?php
/**
 * Template parameters:
 *  $query - The WP_Query object for this page. Do not call query_posts() in this template.
 */
$query = isset( $query ) ? $query : pdl_current_query();
?>
<div id="pdl-view-listings-page" class="pdl-view-listings-page pdl-page <?php echo join(' ', $__page__['class']); ?>">

    <?php if (!isset($stickies)) $stickies = null; ?>
    <?php if (!isset($excludebuttons)) $excludebuttons = true; ?>

    <?php if (!$excludebuttons): ?>
        <div class="pdl-bar cf">
            <?php pdl_the_main_links(); ?>
            <?php pdl_the_search_form(); ?>
        </div>
    <?php endif; ?>

    <?php echo $__page__['before_content']; ?>

    <div class="pdl-page-content <?php echo join(' ', $__page__['content_class']); ?>">

        <?php pdl_the_listing_sort_options(); ?>

        <?php if ( ! $query->have_posts()): ?>
            <?php _ex("No listings found.", 'templates', "PDM"); ?>
        <?php else: ?>
            <div class="listings pdl-listings-list">
                <?php while ( $query->have_posts() ): $query->the_post(); ?>
                    <?php echo pdl_render_listing(null, 'excerpt'); ?>
                <?php endwhile; ?>

                <div class="pdl-pagination">
                <?php if (function_exists('wp_pagenavi')) : ?>
                        <?php wp_pagenavi( array( 'query' => $query ) ); ?>
                <?php else: ?>
                    <span class="prev"><?php previous_posts_link( _x( '&laquo; Previous ', 'templates', 'PDM' ) ); ?></span>
                    <span class="next"><?php next_posts_link( _x( 'Next &raquo;', 'templates', 'PDM'), $query->max_num_pages ); ?></span>
                <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>

</div>
