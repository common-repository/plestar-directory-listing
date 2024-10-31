<div id="<?php echo $listing_css_id; ?>" class="<?php echo $listing_css_class; ?>">
    <div class="listing-title">
        <h2><?php echo $title; ?></h2>
    </div>
    <?php echo $sticky_tag; ?>

    <?php echo pdl_render('parts/listing-buttons', array( 'listing_id' => $listing_id, 'view' => 'single' ), false ); ?>
    <?php pdl_x_part( 'single_content' ); ?>

</div>
