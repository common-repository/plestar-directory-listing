<?php

class PDL__Migrations__3_1 extends PDL__Migration {

    public function migrate() {
        global $wpdb;

        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->posts} SET post_type = %s WHERE post_type = %s", PDL_POST_TYPE, 'pdm-directory'));

        if (function_exists('flush_rewrite_rules'))
            flush_rewrite_rules(false);
    }

}
