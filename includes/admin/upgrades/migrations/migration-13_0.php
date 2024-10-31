<?php

class PDL__Migrations__13_0 extends PDL__Migration {

    public function migrate() {
        // Make sure no field shortnames conflict.
         $fields = pdl_get_form_fields();

         foreach ( $fields as $f )
             $f->save();
    }

}
