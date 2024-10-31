<?php
require_once( PDL_INC . 'class-fee-plan.php' );

if ( ! class_exists( 'PDL_Fees_API' ) ) {

class PDL_Fees_API {

    public function __construct() {
        $this->setup_default_fees();

        // Keep settings in sync with free plan.
        add_action( "pdl_setting_updated_listing-duration", array( $this, 'sync_setting_with_free_plan' ), 10, 3 );
        add_action( "pdl_setting_updated_free-images", array( $this, 'sync_setting_with_free_plan' ), 10, 3 );
        add_action( 'pdl_fee_save', array( $this, 'sync_fee_plan_with_settings' ), 10, 2 );
    }

    public function sync_setting_with_free_plan( $value, $old_value, $setting_id ) {
        if ( ! empty( $this->recursion_guard ) ) {
            return;
        }

        $free_plan = pdl_get_fee_plan( 'free' );

        switch ( $setting_id ) {
        case 'listing-duration':
            $free_plan->days = $value;
            break;
        case 'free-images':
            $free_plan->images = $value;
            break;
        default:
            break;
        }

        $free_plan->save( false );
    }

    public function sync_fee_plan_with_settings( $plan, $update ) {
        if ( empty( $plan->tag ) || 'free' != $plan->tag ) {
            return;
        }

        $this->recursion_guard = true;

        pdl_set_option( 'listing-duration', $plan->days );
        pdl_set_option( 'free-images', $plan->images );

        unset( $this->recursion_guard );
    }

    private function setup_default_fees() {
        global $wpdb;

        $count = intval( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}pdl_plans WHERE tag = %s", 'free' ) ) );

        if ( 0 === $count ) {
            // Add free fee to the DB.
            $wpdb->insert( $wpdb->prefix . 'pdl_plans',
                           array( 'id' => 0,
                                  'tag' => 'free',
                                  'label' => _x( 'Free Listing', 'fees-api', 'PDM' ),
                                  'amount' => 0.0,
                                  'images' => absint( pdl_get_option( 'free-images' ) ),
                                  'days' => absint( pdl_get_option( 'listing-duration' ) ),
                                  'supported_categories' => 'all',
                                  'pricing_model' => 'flat',
                                  'sticky' => 0,
                                  'enabled' => 1 ) );
            $fee_id = $wpdb->insert_id;

            // Update all "free fee" listings to use this.
            $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}pdl_listings SET fee_id = %d WHERE fee_id = %d OR fee_id IS NULL", $fee_id, 0 ) );
        } else if ( $count > 1 ) {
            // Delete "extra" plans. This shouldn't happen, but sometimes it happens :/
            $fee_ids  = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}pdl_plans WHERE tag = %s", 'free' ) );
            $first_id = $fee_ids[0];

            $fee_ids_str = implode( ',', $fee_ids );
            $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}pdl_listings SET fee_id = %d WHERE fee_id IN ({$fee_ids_str})", $first_id ) );

            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}pdl_plans WHERE tag = %s AND id != %d", 'free', $first_id ) );
        }
    }

    // TODO: check if this is being used.
    /**
     * @deprecated since 3.7.
     */
    public static function get_free_fee() { return false; }

    /**
     * @deprecated since 3.7. See {@link pdl_get_fee_plans()}.
     */
    public function get_fees( $categories = null ) {
        global $wpdb;

        if ( ! $categories )
            return pdl_get_fee_plans();

        $fees = array();
        foreach ( $categories as $cat_id ) {
            $category_fees = pdl_get_fee_plans( array( 'categories' => $cat_id ) );

            // XXX: For now, we keep the free plan a 'secret' when payments are enabled. This is for backwards compat.
            if ( pdl_payments_possible() ) {
                foreach ( $category_fees as $k => $v ) {
                    if ( 'free' == $v->tag || ! $v->enabled )
                        unset( $category_fees[ $k ] );
                }
            }

            // Do this so the first fee is at index 0.
            $category_fees = array_merge( array(), $category_fees );
            $fees[ $cat_id ] = $category_fees;
        }

        return $fees;
    }

}

}
