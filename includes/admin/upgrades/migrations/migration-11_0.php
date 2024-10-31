<?php

class PDL__Migrations__11_0 extends PDL__Migration {

    public function migrate() {
        // Users upgrading from < 4.x get the pre-4.0 theme.
        update_option( 'pdl-active-theme', 'no_theme' );
    }

}
