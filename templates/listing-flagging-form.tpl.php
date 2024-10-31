<?php
$user_flagged = PDL__Listing_Flagging::user_has_flagged( $listing->get_id(), get_current_user_id() );
$flagging_text = false !== $user_flagged ? _x( 'Unreport Listing', 'templates', 'PDM') : _x( 'Report Listing', 'templates', 'PDM');
?>

<div id="pdl-listing-flagging-page">
    <h3><?php echo $flagging_text; ?></h3>

    <form class="confirm-form" action="" method="post">
        <?php wp_nonce_field( 'flag listing report ' . $listing->get_id() ); ?>

        <p>
            <?php if ( false === $user_flagged ): ?>
                <?php printf( _x( 'You are about to report the listing "<b>%s</b>" as inappropriate.', 'flag listing', 'PDM' ), $listing->get_title() ); ?>
            <?php else: ?>
                <?php printf( _x( 'You are about to unreport the listing "<b>%s</b>" as inappropriate.', 'flag listing', 'PDM' ), $listing->get_title() ); ?>
            <?php endif; ?>
        </p>

        <?php if ( false === $user_flagged ) : ?>
            <?php if ( $flagging_options = PDL__Listing_Flagging::get_flagging_options() ): ?>
                <p><?php _ex( 'Please select the reasons to report this listing:', 'flag listing', 'PDM' ); ?></p>

                <div class="pdl-listing-flagging-options">
                    <?php foreach ( $flagging_options as $option ) : ?>
                        <p><label><input type="radio" name="flagging_option" value="<?php echo esc_attr( $option ); ?>"/> <span><?php echo esc_html( $option ); ?></span></label></p>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p><?php _ex( 'Please enter the reasons to report this listing:', 'flag listing', 'PDM' ); ?></p>
            <?php endif; ?>

            <textarea name="flagging_more_info" value="" placeholder="<?php _ex( 'Additional info.', 'flag listing', 'PDM' ); ?>" <?php echo $flagging_options ? '' : 'required' ?>></textarea>
            
            <?php echo $recaptcha; ?>
        <?php endif; ?>

        <p>
            <input type="button" onclick="location.href = '<?php echo pdl_url( 'main' ); ?>'; return false;" value="<?php _ex( 'Cancel', 'flag listing', 'PDM' ); ?>" class="pdl-button button" />
            <input class="pdl-submit pdl-button" type="submit" value="<?php echo esc_attr( $flagging_text ); ?>" />
        </p>
    </form>
</div>
