<?php

class PDL__Migrations__4_0 extends PDL__Migration {

    public function migrate() {
        $o = (bool) get_option( PDL_Settings::PREFIX . 'send-email-confirmation', false );

        if ( ! $o ) {
            update_option( PDL_Settings::PREFIX . 'user-notifications', array( 'listing-published' ) );
        }
        delete_option( PDL_Settings::PREFIX . 'send-email-confirmation' );
    }
}

