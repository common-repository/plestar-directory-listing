<?php

class PDL__Migrations__3_5 extends PDL__Migration {

    public function migrate() {
        global $wpdb;
        $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->term_taxonomy} SET taxonomy = %s WHERE taxonomy = %s", PDL_CATEGORY_TAX, 'pdm-category' ) );
        $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->term_taxonomy} SET taxonomy = %s WHERE taxonomy = %s", PDL_TAGS_TAX, 'pdm-tags' ) );
    }

}
