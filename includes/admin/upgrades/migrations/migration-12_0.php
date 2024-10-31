<?php

class PDL__Migrations__12_0 extends PDL__Migration {

    public function migrate() {
        delete_transient( 'pdl-themes-updates' );
    }

}
