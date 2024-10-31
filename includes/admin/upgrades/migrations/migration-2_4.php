<?php

class PDL__Migrations__2_4 extends PDL__Migration {

    public function migrate() {
        global $wpdb;
        global $pdl;

        $fields = $pdl->formfields->get_fields();

        foreach ($fields as &$field) {
            $query = $wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_key = %s WHERE meta_key = %s AND {$wpdb->postmeta}.post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = %s)",
                                    '_pdl[fields][' . $field->get_id() . ']', $field->get_label(), 'pdm-directory');
            $wpdb->query($query);
        }
    }

}
