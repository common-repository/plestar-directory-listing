<?php

class PDL__Migrations__18_2 extends PDL__Migration {

    public function migrate() {
        delete_site_transient( 'pdl_updates' );
        delete_transient( 'pdl_updates' );
        set_site_transient( 'update_plugins', null );
    }

}
