<?php
$action = isset( $action ) ? $action : '';

$reasons = array(
    '1' => _x( 'It doesn\'t work with my theme/plugins/site', 'uninstall', 'PDM' ),
    '2' => _x( 'I can\'t set it up/Too complicated', 'uninstall', 'PDM' ),
    '3' => _x( 'Doesn\'t solve my problem', 'uninstall', 'PDM' ),
    '4' => _x( 'Don\'t need it anymore/Not using it', 'uninstall', 'PDM' ),
    '0' => _x( 'Other', 'uninstall', 'PDM' )
);
?>

<form id="pdl-uninstall-capture-form" action="<?php echo $action; ?>" method="post">
    <?php wp_nonce_field( 'uninstall pdl' ); ?>

    <p><?php _ex( 'We\'re sorry to see you leave. Could you take 10 seconds and answer one question for us to help us make the product better for everyone in the future?',
                  'uninstall',
                  'PDM' ); ?></p>
    <p><b><?php _ex( 'Why are you deleting Plestar Directory Listing?', 'uninstall', 'PDM' ); ?></b></p>

    <div class="pdl-validation-error no-reason pdl-hidden">
        <?php _ex( 'Please choose an option.', 'uninstall', 'PDM' ); ?>
    </div>

    <div class="reasons">
        <?php foreach ( $reasons as $r => $l ): ?>
        <div class="reason">
            <label>
                <input type="radio" name="uninstall[reason_id]" value="<?php echo $r; ?>" /> <?php echo $l; ?>
            </label>

            <?php if ( 0 == $r ): ?>
            <div class="custom-reason">
                <textarea name="uninstall[reason_text]" placeholder="<?php _ex( 'Please tell us why are you deleting Plestar Directory Listing.', 'uninstall', 'PDM' ); ?>"></textarea>

                <div class="pdl-validation-error no-reason-text pdl-hidden">
                    <?php _ex( 'Please enter your reasons.', 'uninstall', 'PDM' ); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <p class="buttons">
        <input type="submit" value="<?php _ex( 'Uninstall Plugin', 'uninstall', 'PDM'); ?>" class="button button-primary" />
    </p>
</form>
