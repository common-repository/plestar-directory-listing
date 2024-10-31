<div class="listing-actions cf">
<?php if ( $view == 'single' ) : ?>
    <?php if ( pdl_user_can( 'edit', $listing_id) ) : ?>
    <form action="<?php echo pdl_url( 'edit_listing', $listing_id ); ?>" method="post"><input type="submit" name="" value="<?php _ex( 'Edit', 'templates', 'PDM'); ?>" class="button pdl-button edit-listing" /></form>
    <?php endif; ?>
    <?php if ( pdl_get_option( 'enable-listing-flagging' ) && pdl_user_can( 'flagging', $listing_id ) ) : ?>
    <form action="<?php echo pdl_url( 'flag_listing', $listing_id ); ?>" method="post"><input type="submit" name="" value="<?php echo apply_filters( 'pdl_listing_flagging_button_text', _x( 'Flag Listing', 'templates', 'PDM' ) ); ?>" class="button pdl-button report-listing" /></form>
    <?php endif; ?>
    <?php if ( pdl_user_can( 'delete', $listing_id ) ) : ?>
    <form action="<?php echo pdl_url( 'delete_listing', $listing_id ); ?>" method="post"><input type="submit" name="" value="<?php _ex( 'Delete', 'templates', 'PDM' ); ?>" class="button pdl-button delete-listing" data-confirmation-message="<?php _ex( 'Are you sure you wish to delete this listing?', 'templates', 'PDM' ); ?>" /></form>
    <?php endif; ?>
    <?php if ( pdl_get_option( 'show-directory-button' ) ) :?>
     <input type="button" value="<?php echo __( 'â†� Return to Directory', 'PDM' ); ?>" onclick="window.location.href = '<?php echo pdl_url( '/' ); ?>'" class="pdl-hide-on-mobile button back-to-dir pdl-button" />
     <input type="button" value="â†�" onclick="window.location.href = '<?php echo pdl_url( '/' ); ?>'" class="pdl-show-on-mobile button back-to-dir pdl-button" />
    <?php endif; ?>
<?php elseif ( $view == 'excerpt' ): ?>
    <?php if ( pdl_user_can( 'view', $listing_id ) ) : ?><a class="pdl-button button view-listing" href="<?php the_permalink(); ?>" <?php if ( pdl_get_option( 'listing-link-in-new-tab' ) ): ?>target="_blank" rel="noopener" <?php endif; ?>><?php _ex('View', 'templates', 'PDM'); ?></a><?php endif; ?>
    <?php if ( pdl_user_can( 'edit', $listing_id ) ) : ?><a class="pdl-button button edit-listing" href="<?php echo pdl_url( 'edit_listing', $listing_id ); ?>"><?php _ex('Edit', 'templates', 'PDM'); ?></a><?php endif; ?>
    <?php if ( pdl_get_option( 'enable-listing-flagging' ) && pdl_user_can( 'flagging', $listing_id ) ): ?><a class="pdl-button button report-listing" href="<?php echo esc_url( pdl_url( 'flag_listing', $listing_id ) ); ?>"><?php echo apply_filters( 'pdl_listing_flagging_button_text', _x( 'Flag Listing', 'templates', 'PDM' ) ); ?></a><?php endif; ?>
    <?php if ( pdl_user_can( 'delete', $listing_id ) ) : ?><a class="pdl-button button delete-listing" href="<?php echo pdl_url( 'delete_listing', $listing_id ); ?>"><?php _ex( 'Delete', 'templates', 'WPPDL"M' ); ?></a><?php endif; ?>
<?php endif; ?>
</div>
