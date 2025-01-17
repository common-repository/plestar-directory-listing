<?php do_action( 'pdl_before_submit_listing_page', $listing ); ?>
<div id="pdl-submit-listing" class="pdl-submit-page pdl-page">
    <form action="" method="post" data-ajax-url="<?php echo pdl_ajax_url(); ?>" enctype="multipart/form-data">
        <?php wp_nonce_field( 'listing submit' ); ?>
        <input type="hidden" name="listing_id" value="<?php echo $listing->get_id(); ?>" />
        <input type="hidden" name="editing" value="<?php echo $editing ? '1' : '0'; ?>" />
        <input type="hidden" name="save_listing" value="1" />
        <input type="hidden" name="reset" value="" />

            <h2><?php _ex( 'Submit A Listing', 'submit listing', 'PDM' ); ?></h2>
            <?php echo $messages['general']; ?>

            <?php foreach ( $sections as $section ): ?>
                <?php echo pdl_render( 'submit-listing-section',
                                         array( 'section' => $section,
                                                'messages' => ( ! empty( $messages[ $section['id'] ] ) ? $messages[ $section['id'] ] : '' ) ) );
                ?>
            <?php endforeach; ?>

        <div class="pdl-submit-listing-form-actions">
            <input type="reset" value="<?php _ex( 'Clear Form', 'submit listing', 'PDM' ); ?>" />
            <?php if ( $is_admin || ! pdl_payments_possible() || $submit->skip_plan_payment ): ?>
            <input type="submit" value="<?php _ex( 'Complete Listing', 'submit listing', 'PDM' ); ?>" id="pdl-submit-listing-submit-btn" />
            <?php else: ?>
                <?php if ( $editing ): ?>
                <input type="submit" value="<?php _ex( 'Save Changes', 'submit listing', 'PDM' ); ?>" id="pdl-submit-listing-submit-btn" />
                <?php else: ?>
                <input type="submit" value="<?php _ex( 'Continue to Payment', 'submit listing', 'PDM' ); ?>" id="pdl-submit-listing-submit-btn" />
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </form>
</div>
<?php do_action( 'pdl_after_submit_listing_page', $listing ); ?>
