<?php

class PDL__Migrations__2_0 extends PDL__Migration {

    public function migrate() {
        global $wpdb;
        global $pdl;

        $pdl->settings->upgrade_options();
        pdl_log('PDL settings updated to 2.0-style');

        // make directory-related metadata hidden
        $old_meta_keys = array(
            'termlength', 'image', 'listingfeeid', 'sticky', 'thumbnail', 'paymentstatus', 'buyerfirstname', 'buyerlastname',
            'paymentflag', 'payeremail', 'paymentgateway', 'totalallowedimages', 'costoflisting'
        );

        foreach ($old_meta_keys as $meta_key) {
            $query = $wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_key = %s WHERE meta_key = %s AND {$wpdb->postmeta}.post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = %s)",
                                    '_pdl_' . $meta_key, $meta_key, 'pdm-directory');
            $wpdb->query($query);
        }

        pdl_log('Made PDL directory metadata hidden attributes');
    }
}
