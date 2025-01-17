<?php

class PDL__Migrations__2_5 extends PDL__Migration {

    public function migrate() {
        global $wpdb;

        pdl_log('Updating payment/sticky status values.');
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_key = %s WHERE meta_key = %s", '_pdl[sticky]', '_pdl_sticky'));
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = %s WHERE meta_key = %s AND meta_value = %s", 'sticky', '_pdl[sticky]', 'approved'));
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = %s WHERE meta_key = %s AND meta_value != %s", 'pending', '_pdl[sticky]', 'approved'));
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_key = %s WHERE meta_key = %s", '_pdl[payment_status]', '_pdl_paymentstatus'));
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = %s WHERE meta_key = %s AND meta_value != %s", 'not-paid', '_pdl[payment_status]', 'paid'));

        // Misc updates
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s", '_pdl_totalallowedimages'));
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s", '_pdl_termlength'));
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s", '_pdl_costoflisting'));
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s", '_pdl_listingfeeid'));

        pdl_log('Updating listing images to new framework.');

        $old_images = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->postmeta} WHERE meta_key = %s", '_pdl_image'));
        foreach ($old_images as $old_image) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $uploads = wp_upload_dir();
            $filename = $uploads['baseurl'] . '/pdm/' . $old_image->meta_value;

            $wp_filetype = wp_check_filetype(basename($filename), null);
            
            $attachment_id = wp_insert_attachment(array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                'post_content' => '',
                'post_status' => 'inherit'
            ), $filename, $old_image->post_id);
            $attach_data = wp_generate_attachment_metadata( $attachment_id, $filename );
            wp_update_attachment_metadata( $attachment_id, $attach_data );
        }
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s", '_pdl_image'));
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s", '_pdl_thumbnail'));        
    }

}
