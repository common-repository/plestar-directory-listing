<?php

class PDL__Migrations__18_1 extends PDL__Migration {

    public function migrate() {
        global $wpdb;

        // pdl_payments: move from 'created_on' column to 'created_at'.
        if ( pdl_column_exists( "{$wpdb->prefix}pdl_payments", 'created_on' ) ) {
            $wpdb->query( "UPDATE {$wpdb->prefix}pdl_payments SET created_at = FROM_UNIXTIME(UNIX_TIMESTAMP(created_on))" );
        }
    }

}
