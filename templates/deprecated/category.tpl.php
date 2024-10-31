<?php
$in_shortcode = ! isset( $in_shortcode ) ? false : (bool) $in_shortcode;
?>
<div id="pdl-category-page" class="pdl-category-page plestardirectory-category plestardirectory pdl-page">
    <?php if ( empty( $only_listings ) && ! $in_shortcode ): ?>
    <div class="pdl-bar cf">
        <?php pdl_the_main_links(); ?>
        <?php pdl_the_search_form(); ?>
    </div>
    <?php endif; ?>

    <?php echo $__page__['before_content']; ?>

    <?php if ( $title ): ?>
        <h2 class="category-name">
            <?php echo $title; ?>
        </h2>
    <?php endif; ?>

    <?php do_action( 'pdl_before_category_page', $category ); ?>
    <?php
    	echo apply_filters( 'pdl_category_page_listings', pdl_render('plestardirectory-listings', array('excludebuttons' => true)), $category );
    ?>
    <?php do_action( 'pdl_after_category_page', $category ); ?>

</div>
