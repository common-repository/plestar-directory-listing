<?php

class PDL__Migrations__3_4 extends PDL__Migration {

    public function migrate() {
        global $wpdb;

        $query = $wpdb->prepare( "UPDATE {$wpdb->prefix}pdl_listing_fees SET email_sent = %d WHERE email_sent = %d", 2, 1 );
        $wpdb->query( $query );
    }

}
