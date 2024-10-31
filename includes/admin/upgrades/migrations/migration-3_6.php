<?php

class PDL__Migrations__3_6 extends PDL__Migration {

    public function migrate() {
        global $wpdb;

        $wpdb->query( "ALTER TABLE {$wpdb->prefix}pdl_form_fields MODIFY id bigint(20) AUTO_INCREMENT" );
        $wpdb->query( "ALTER TABLE {$wpdb->prefix}pdl_fees MODIFY id bigint(20) AUTO_INCREMENT" );
        $wpdb->query( "ALTER TABLE {$wpdb->prefix}pdl_payments MODIFY id bigint(20) AUTO_INCREMENT" );
        $wpdb->query( "ALTER TABLE {$wpdb->prefix}pdl_listing_fees MODIFY id bigint(20) AUTO_INCREMENT" );

        update_option(PDL_Settings::PREFIX . "listings-per-page", get_option("posts_per_page"));
    }

}
