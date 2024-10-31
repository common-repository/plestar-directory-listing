<?php
/**
 * @since 5.1.6
 */
class PDL__Views__Flag_Listing extends PDL__View {

    private $listing_id = 0;
    private $listing = null;
    private $errors  = array();


    public function dispatch() {
        if ( ! pdl_get_option( 'enable-listing-flagging' ) ) {
            exit;
        }

        $this->listing_id = absint( $_REQUEST['listing_id'] );
        $this->listing = pdl_get_listing( $this->listing_id );

        if ( ! $this->listing ) {
            exit;
        }

        if ( ! pdl_user_can( 'flagging', $this->listing->get_id() ) ) {
            $this->_auth_required();
        }

        $nonce = isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : '';
        $html  = '';

        if ( wp_verify_nonce( $nonce, 'flag listing report ' . $this->listing_id ) ) {
            // Try to add report.
            $report = $this->sanitize_report();

            if ( $report ) {
                $result = PDL__Listing_Flagging::add_flagging( $this->listing_id, $report );

                if ( is_wp_error( $result ) ) {
                    $flagging_msg = _x( 'Something went wrong, please try again. If error persists contact site admin', 'flag listing', 'PDM' );
                    return pdl_render_msg( $flagging_msg, 'error' );
                }

                $flagging_msg = _x( 'The listing <i>%s</i> has been reported. <a>Return to directory</a>', 'flag listing', 'PDM' );
                $flagging_msg = sprintf( $flagging_msg, $this->listing->get_title() );
                $flagging_msg = str_replace( '<a>', '<a href="'. esc_url( pdl_url( 'main' ) ) .'">', $flagging_msg );

                return pdl_render_msg( $flagging_msg );
            }
        } else if ( wp_verify_nonce( $nonce, 'flag listing unreport ' . $this->listing_id ) ) {
            // Remove report.
            // $flagging_pos = PDL__Listing_Flagging::user_has_flagged( $listing_id, $current_user );

            // PDL__Listing_Flagging::remove_flagging( $listing_id, $flagging_pos );
            //
            // $flagging_msg = _x( 'The listing <i>%s</i> has been unreported. <a>Return to listing</a>', 'flag listing', 'PDM' );
            // $flagging_msg = sprintf( $flagging_msg, $this->listing->get_title() );
            // $flagging_msg = str_replace( '<a>', '<a href="' . $this->listing->get_permalink() . '">', $flagging_msg );
            //
            // return pdl_render_msg( $flagging_msg );
        }

        foreach ( $this->errors as $err_msg ) {
            $html .= pdl_render_msg( $err_msg, 'error' );
        }

        $html .= pdl_render(
            'listing-flagging-form',
            array(
                'listing' => $this->listing,
                'recaptcha' => pdl_get_option( 'recaptcha-for-flagging' ) ? pdl_recaptcha( 'pdl-listing-flagging-recaptcha' ) : ''
            )
        );
        

        $current_user = get_current_user_id();
        $error = array();

        return $html;
    }

    public function sanitize_report() {
        $posted_values = stripslashes_deep( $_POST );

        $report = array();
        $report['user_id'] = get_current_user_id();
        $report['ip'] = pdl_get_client_ip_address();
        $report['date'] = time();
        $report['reason'] = ! empty( $posted_values['flagging_option'] ) ? trim( $posted_values['flagging_option'] ) : '';
        $report['comments'] = ! empty( $posted_values['flagging_more_info'] ) ? trim( $posted_values['flagging_more_info'] ) : '';

        if ( false !== PDL__Listing_Flagging::ip_has_flagged( $this->listing_id, $report[ 'ip' ] ) ) {
            $this->errors[] = _x( 'Your current IP address already reported this listing.', 'flag listing', 'PDM' );
            return false;
        }

        if ( pdl_get_option( 'recaptcha-for-flagging' ) && ! pdl_recaptcha_check_answer() ) {
            $this->errors[] = _x( "The reCAPTCHA wasn't entered correctly.", 'flag listing', 'PDM' );
            return false;
        }

        if ( ! $report['reason'] && ! $report['comments'] ) {
            $this->errors[] = _x( 'You must select or enter the reasons to report this listing as inappropriate.', 'flag listing', 'PDM' );
            return false;
        }

        return $report;
    }

}