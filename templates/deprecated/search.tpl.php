<?php
$api = pdl_formfields_api();
?>
<div id="pdl-search-page" class="pdl-search-page plestardirectory-search plestardirectory pdl-page">
    <div class="pdl-bar cf"><?php pdl_the_main_links(); ?></div>
    <h2 class="title"><?php _ex('Search', 'search', 'PDM'); ?></h2>

    <?php if ( 'none' == $search_form_position || 'above' == $search_form_position ): ?>
    <?php echo $search_form; ?>
    <?php endif; ?>

    <!-- Results -->
    <?php if ($searching): ?>    
        <h3><?php _ex('Search Results', 'search', 'PDM'); ?></h3>    

        <?php do_action( 'pdl_before_search_results' ); ?>
        <div class="search-results">
        <?php if (have_posts()): ?>
            <?php echo pdl_render('plestardirectory-listings'); ?>
        <?php else: ?>
            <?php _ex("No listings found.", 'templates', "PDM"); ?>
            <br />
            <?php echo sprintf('<a href="%s">%s</a>.', pdl_get_page_link('main'),
                               _x('Return to directory', 'templates', 'PDM')); ?>    
        <?php endif; ?>
        </div>
        <?php do_action( 'pdl_after_search_results' ); ?>
    <?php endif; ?>

    <?php if ( 'below' == $search_form_position ): ?>
    <?php echo $search_form; ?>
    <?php endif; ?>

</div>
