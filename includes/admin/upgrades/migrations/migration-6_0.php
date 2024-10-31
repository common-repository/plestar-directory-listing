<?php

class PDL__Migrations__6_0 extends PDL__Migration {

    public function migrate() {
        global $wpdb;

        $wpdb->query( "ALTER TABLE {$wpdb->prefix}pdl_payments MODIFY created_on TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP" );
        $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}pdl_payments SET processed_on = NULL WHERE processed_on = %s", '0000-00-00 00:00:00' ) );
    }
}

