<?php
// THIS TEMPLATE IS DEPRECATED. DO NOT USE.
// See http://plestar.net/docs/matching-the-design-to-your-theme/ for info on Plestar Directory Listing templates.
?>
<?php get_header(); ?>
<div id="content">

<div id="pdl-category-page" class="pdl-category-page plestardirectory-category plestardirectory pdl-page">
    <div class="pdl-bar cf">
        <?php pdl_the_main_links(); ?>
        <?php pdl_the_search_form(); ?>
    </div>

    <h2 class="category-name"><?php echo wpdirlist_post_catpage_title(); ?></h2>
    <?php echo pdl_render('plestardirectory-listings'); ?>
</div>

</div>
<?php get_footer(); ?>