<div id="pdl-manage-listings-page" class="pdl-manage-listings-page plestardirectory-manage-listings plestardirectory pdl-page">
    <?php if ( $query-> have_posts() ): ?>
        <p><?php _ex("Your current listings are shown below. To edit a listing click the edit button. To delete a listing click the delete button.", 'templates', "PDM"); ?></p>
        <?php echo pdl_x_part( 'listings' ); ?>
    <?php else: ?>
        <p><?php _ex('You do not currently have any listings in the directory.', 'templates', 'PDM'); ?></p>
        <?php echo sprintf('<a href="%s">%s</a>.', pdl_get_page_link('main'),
                           _x('Return to directory', 'templates', 'PDM')); ?>     
    <?php endif; ?>
</div>
