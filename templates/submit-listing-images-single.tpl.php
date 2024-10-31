<?php
$is_thumbnail = isset( $is_thumbnail ) ? $is_thumbnail : false;

if ( isset( $image ) ) {
    $image_id = $image->id;
    $weight = $image->weight;
    $caption = $image->caption;
}
?>

<div class="pdl-image" data-imageid="<?php echo $image_id; ?>">
    <input type="hidden" name="images_meta[<?php echo $image_id; ?>][order]" value="<?php echo ( isset( $weight ) ? $weight : 0 ); ?>" />

    <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'pdl-listing-submit-image-delete', 'image_id' => $image_id, 'listing_id' => $listing_id ), admin_url( 'admin-ajax.php' ) ), 'delete-listing-' . $listing_id . '-image-' . $image_id ) ); ?>" class="pdl-image-delete-link"><?php _ex( 'Delete image', 'submit listing', 'PDM' ); ?></a>

    <div class="pdl-image-img">
        <?php echo wp_get_attachment_image( $image_id, 'pdl-mini' ); ?>
    </div>

    <div class="pdl-image-extra">
        <input type="text" name="images_meta[<?php echo $image_id; ?>][caption]" value="<?php echo ( isset( $caption ) ? esc_attr( $caption ) : '' ); ?>" placeholder="<?php _ex( 'Image caption or description', 'submit listing', 'PDM' ); ?>" />
        <label>
            <input type="radio" name="thumbnail_id" value="<?php echo $image_id; ?>" <?php echo $is_thumbnail ? 'checked="checked"' : ''; ?> />
            <?php _ex('Set this image as the listing thumbnail.', 'templates', 'PDM'); ?>
        </label>
    </div>

</div>
