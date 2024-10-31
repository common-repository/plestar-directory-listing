<?php

class PDL__Migrations__2_2 extends PDL__Migration {

    public function migrate() {
        global $wpdb;
        $wpdb->query("ALTER TABLE {$wpdb->prefix}pdl_form_fields CHARACTER SET utf8 COLLATE utf8_general_ci");
        $wpdb->query("ALTER TABLE {$wpdb->prefix}pdl_form_fields CHANGE `label` `label` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
        $wpdb->query("ALTER TABLE {$wpdb->prefix}pdl_form_fields CHANGE `description` `description` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL");
    }

}
