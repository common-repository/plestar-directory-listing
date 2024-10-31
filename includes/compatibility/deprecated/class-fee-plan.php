<?php

/**
 * @deprecated since 5.0. Use {@link PDL__Fee_Plan} instead. This is just kept as to not break premium modules for a while.
 */
class PDL_Fee_Plan {

    public static function for_category( $category_id ) {
        return pdl_get_fee_plans( array( 'categories' => $category_id, 'enabled' => 'all' ) );
    }

    public static function active_fees_for_category( $category_id ) {
        return pdl_get_fee_plans( array( 'categories' => $category_id, 'enabled' => 1 ) );
    }

    public static function active_fees() {
        return pdl_get_fee_plans();
    }

    public static function get_free_plan() {
        return pdl_get_fee_plan( 'free' );
    }

}

