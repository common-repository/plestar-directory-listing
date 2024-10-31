<?php
$admin = isset( $admin ) ? $admin : false;
$listing_id = isset( $listing_id ) ? $listing_id : 0;

$action = add_query_arg( array( 'action' => 'pdl-listing-submit-image-upload',
                                'listing_id' => $listing_id ),
                         admin_url( 'admin-ajax.php' ) );
?>
<div class="image-upload-wrapper">
    <h4><?php _ex( 'Upload Images', 'templates', 'PDM' ); ?></h4>

    <div class="area-and-conditions cf">
    <div id="image-upload-dnd-area" class="pdl-dnd-area <?php echo $admin ? 'no-conditions' : ''; ?>" data-action="<?php echo esc_url( wp_nonce_url( $action, 'listing-' . $listing_id . '-image-upload') ); ?>" data-admin-nonce="<?php echo $admin ? '1' : ''; ?>" >
            <div class="dnd-area-inside">
                <p class="dnd-message"><?php _ex( 'Drop files here', 'templates', 'PDM' ); ?></p>
                <p><?php _ex( 'or', 'templates image upload', 'PDM' ); ?></p>
                <p class="dnd-buttons"><span class="upload-button"><a><?php _ex( 'Select images from your hard drive', 'templates', 'PDM' ); ?></a><input type="file" name="images[]" multiple="multiple" /></span></p>
            </div>
            <div class="dnd-area-inside-working" style="display: none;">
                <p><?php echo sprintf( _x( 'Uploading %s file(s)... Please wait.', 'templates', 'PDM' ), '<span>0</span>' ); ?></p>
            </div>
            <div class="dnd-area-inside-error" style="display: none;">
                <p id="noslots-message" style="display: none;"><?php _ex( 'Your image slots are all full at this time.  You may click "Continue" if you are done, or "Delete Image" to upload a new image in place of a current one.', 'templates', 'PDM' ); ?></p>
            </div>
        </div>

        <?php if ( ! $admin ): ?>
        <div id="image-upload-conditions">
            <dl class="image-conditions">
                <dt><?php _ex( 'Image slots available:', 'templates', 'PDM' ); ?></dt>
                <dd>
                    <span id="image-slots-remaining"><?php echo $slots_available; ?></span> / <span id="image-slots-total"><?php echo $slots; ?></span>
                </dd>

                <?php if ( $min_file_size || $max_file_size ): ?>
                <dt><?php _ex( 'File size:', 'templates', 'PDM' ); ?></dt>
                <dd>
                    <?php echo $min_file_size; ?> - <?php echo $max_file_size ? $max_file_size : _x( 'No limit', 'templates', 'PDM' ); ?>
                </dd>
                <?php endif; ?>

                <?php if ( $image_min_width || $image_max_width ): ?>
                <dt><?php _ex( 'Image width:', 'templates', 'PDM' ); ?></dt>
                <dd>
                    <?php echo $image_min_width . 'px'; ?> - <?php echo $image_max_width ? $image_max_width . 'px' : _x( 'No limit', 'templates', 'PDM' ); ?>
                </dd>
                <?php endif; ?>

                <?php if ( $image_min_height || $image_max_height ): ?>
                <dt><?php _ex( 'Image height:', 'templates', 'PDM' ); ?></dt>
                <dd>
                    <?php echo $image_min_height . 'px'; ?> - <?php echo $image_max_height ? $image_max_height . 'px' : _x( 'No limit', 'templates', 'PDM' ); ?>
                </dd>
                <?php endif; ?>
            </dl>
        </div>
        <?php endif; ?>
    </div>
</div>

<br />
