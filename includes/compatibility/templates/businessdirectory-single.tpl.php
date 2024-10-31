<?php get_header(); ?>

<?php the_post(); ?>

<div id="content">
    <?php // Customize the output of this function using the template "plestardirectory-listing.tpl.php"; ?>
    <?php echo pdl_render_listing(null, 'single'); ?>
</div>

<?php get_footer(); ?>