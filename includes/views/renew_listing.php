<?php

require_once( PDL_PATH . 'includes/helpers/class-authenticated-listing-view.php' );

/**
 * Renew listing view.
 */
class PDL__Views__Renew_Listing extends PDL__Authenticated_Listing_View {

    private $plan = null;
    private $payment_id = 0;


    public function dispatch() {
        global $wpdb;

        if ( ! pdl_get_option( 'listing-renewal' ) )
            return pdl_render_msg( _x( 'Listing renewal is disabled at this moment. Please try again later.', 'renewal', 'PDM' ), 'error' );

        $renewal_id = ! empty( $_GET['renewal_id'] ) ? $_GET['renewal_id'] : 0;

        if ( ! ( $this->listing = PDL_Listing::get( $renewal_id ) ) )
            return pdl_render_msg( _x( 'Your renewal ID is invalid. Please use the URL you were given on the renewal e-mail message.', 'renewal', 'PDM' ), 'error' );

        $this->_auth_required();

        $this->plan = $this->listing->get_fee_plan();

        $payment = $this->listing->get_latest_payment();

        if ( $payment && 'initial' == $payment->payment_type && 'pending' == $payment->status ) {
            return $this->_redirect( $payment->get_checkout_url() );
        }

        if ( 'pending_renewal' == $this->listing->get_status() ) {
            // Check to see if there's a pending payment for this renewal. If there is, move to checkout.
            if ( $payment = PDL_Payment::objects()->get( array( 'listing_id' => $this->listing->get_id(), 'payment_type' => 'renewal', 'status' => 'pending' ) ) ) {
                return $this->_redirect( $payment->get_checkout_url() );
            }
        }

        if ( $this->plan->is_recurring ) {
            return $this->render_manage_subscription_page( $this->listing, $this->plan );
        }

        if ( isset( $_POST['cancel-renewal'] ) ) {
            if ( $this->listing->delete() ) {
                return pdl_render_msg( _x( 'Your listing has been removed from the directory.', 'renewal', 'PDM' ) );
            } else {
                return pdl_render_msg( _x( 'Could not remove listing from directory.', 'renewal', 'PDM' ), 'error' );
            } 
        }

        if ( isset( $_POST['listing_plan'] ) ) {
            if ( $fee = pdl_get_fee( absint( $_POST['listing_plan'] ) ) ) {
                $payment = new PDL_Payment( array( 'listing_id' => $this->listing->get_id(), 'payment_type' => 'renewal' ) );

                $payment->payment_items[] = array(
                    'type' => 'plan',
                    'description' => sprintf( _x( 'Fee "%s" renewal.', 'listings', 'PDM' ), $fee->label ),
                    'amount' => $fee->amount,
                    'fee_id' => $fee->id,
                    'fee_days' => $fee->days,
                    'fee_images' => $fee->images,
                    'is_renewal' => true
                );

                if ( $payment->save() ) {
                    $this->listing->set_status( 'pending_renewal' );
                }

                $this->payment_id = $payment->id;

                return $this->_redirect( $payment->get_checkout_url() );
            }
        }
        
        return $this->render_plan_selection( $this->plan );
    }

    private function render_manage_subscription_page( $listing, $current_plan ) {
        $params = array(
            'listing' => $listing,
            'plan' => $current_plan,
            'show_cancel_subscription_button' => $this->should_show_cancel_subscription_buton( $listing ),
        );

        return pdl_render( 'renew-listing-manage-subscription', $params );
    }

    private function should_show_cancel_subscription_buton( $listing ) {
        try {
            $subscription = $listing->get_subscription();
        } catch ( Exception $e ) {
            return false;
        }

        $payment = $subscription->get_parent_payment();

        if ( ! $payment || ! $payment->gateway ) {
            return false;
        }

        return true;
    }

    private function render_plan_selection( $current_plan ) {
        $params = array(
            'listing' => $this->listing,
            'current_plan' => $current_plan,
            'plans' => pdl_get_fee_plans(),
        );

        return pdl_render( 'renew-listing', $params );
    }
}
