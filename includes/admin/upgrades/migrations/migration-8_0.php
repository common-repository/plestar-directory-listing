<?php

class PDL__Migrations__8_0 extends PDL__Migration {

    public function migrate() {
        if ( get_option( PDL_Settings::PREFIX . 'show-search-form-in-results', false ) )
            update_option( PDL_Settings::PREFIX . 'search-form-in-results', 'above' );
        delete_option( PDL_Settings::PREFIX . 'show-search-form-in-results' );
    }

}
