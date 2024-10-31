<?php
/**
 * Listing contact page.
 * @since 3.4
 */
class PDL__Views__Listing_Contact extends PDL__View {

    private $errors = array();

    private $name = '';
    private $email = '';
    private $message = '';


    private function prepare_input() {
        if ( $_POST )
            $_POST = stripslashes_deep( $_POST );

        $current_user = is_user_logged_in() ? wp_get_current_user() : null;

        $this->name = wp_strip_all_tags( $current_user ? $current_user->data->user_login : ( isset( $_POST['commentauthorname'] ) ? trim( $_POST['commentauthorname'] ) : '' ) );
        $this->email = sanitize_email( $current_user ? $current_user->data->user_email : ( isset( $_POST['commentauthoremail'] ) ? trim( $_POST['commentauthoremail'] ) : '' ) );
        $this->message = isset( $_POST['commentauthormessage'] ) ? trim( wp_kses( $_POST['commentauthormessage'], array() ) ) : '';
    }

    private function validate() {
        $this->errors = array();

        if ( ! isset( $_REQUEST['listing_id'] ) )
            die();

        // Verify nonce.
        if ( ! isset( $_POST['_wpnonce'] )
             || ! isset( $_POST['_wp_http_referer'] )
             || ! wp_verify_nonce( $_POST['_wpnonce'], 'contact-form-' . $_REQUEST['listing_id'] ) )
            die();

        if ( ! $this->name )
            $this->errors[] = _x( 'Please enter your name.', 'contact-message', 'PDM' );

        if ( ! pdl_validate_value( $this->email, 'email' ) )
            $this->errors[] = _x( "Please enter a valid email.", 'contact-message', "PDM" );

        if ( ! $this->message )
            $this->errors[] = _x( 'You did not enter a message.', 'contact-message', 'PDM' );

        if ( pdl_get_option( 'recaptcha-on' ) && ! pdl_recaptcha_check_answer() )
            $this->errors[] = _x( "The reCAPTCHA wasn't entered correctly.", 'contact-message', 'PDM' );

        $this->errors = apply_filters( 'pdl_contact_form_validation_errors', $this->errors );

        return empty( $this->errors );
    }

    private function can_submit( $listing_id = 0, &$error_msg = '' ) {
        if ( pdl_get_option( 'contact-form-require-login' ) && ! is_user_logged_in() ) {
            $error_msg = str_replace( '<a>',
                                      '<a href="' . wp_login_url( site_url( $_SERVER['REQUEST_URI'] ) ) . '">',
                                      _x( 'Please <a>log in</a> to be able to send messages to the listing owner.', 'contact form', 'PDM' ) );
            return false;
        }

        $daily_limit = max( 0, intval( pdl_get_option( 'contact-form-daily-limit' ) ) );

        if ( ! $daily_limit )
            return true;

        $today = date( 'Ymd', current_time( 'timestamp' ) );
        $data = get_post_meta( $listing_id, '_pdl_contact_limit', true );

        if ( ! $data || ! is_array( $data ) )
            $data = array( 'last_date' => $today, 'count' => 0 );

        if ( $today != $data['last_date'] )
            $data['count'] = 0;

        if ( $data['count'] >= $daily_limit ) {
            $error_msg = _x( 'This contact form is temporarily disabled. Please try again later.', 'contact form', 'PDM' );
            return false;
        }

        return true;
    }

    private function update_contacts( $listing_id ) {
        $daily_limit = max( 0, intval( pdl_get_option( 'contact-form-daily-limit' ) ) );

        if ( ! $daily_limit )
            return;

        $today = date( 'Ymd', current_time( 'timestamp' ) );
        $data = get_post_meta( $listing_id, '_pdl_contact_limit', true );

        if ( ! $data || ! is_array( $data ) )
            $data = array( 'last_date' => $today, 'count' => 0 );

        if ( $today != $data['last_date'] )
            $data['count'] = 0;

        $data['count'] = $data['count'] + 1;
        $data['last_date'] = $today;

        update_post_meta( $listing_id, '_pdl_contact_limit', $data );
    }

    public function render_form( $listing_id = 0, $validation_errors = array() ) {
        $listing_id = absint( $listing_id );

        if ( ! $listing_id || ! apply_filters('pdl_show_contact_form', pdl_get_option( 'show-contact-form' ), $listing_id ) )
            return '';

        $html  = '';

        $html .= '<div class="pdl-listing-contact-form">';

        if ( ! $_POST ) {
            $html .= '<input type="button" class="pdl-show-on-mobile send-message-button pdl-button" value="' . _x( 'Contact listing owner', 'templates', 'PDM' ) . '" />';
            $html .= '<div class="pdl-hide-on-mobile contact-form-wrapper">';
        }

        $html .= '<h3>' . _x('Send Message to listing owner', 'templates', 'PDM') . '</h3>';

        $form = '';

        if ( ! $this->can_submit( $listing_id, $error_msg ) ) {
            $form = pdl_render_msg( $error_msg );
        } else {
            $form = pdl_render( 'listing-contactform', array(
                                        'validation_errors' => $validation_errors,
                                        'listing_id' => $listing_id,
                                        'current_user' => is_user_logged_in() ? wp_get_current_user() : null,
                                        'recaptcha' => pdl_get_option( 'recaptcha-on' ) ? pdl_recaptcha( 'pdl-contact-form-recaptcha' ) : '',
                                  false ) );
        }

        $html .= $form;

        if ( ! $_POST )
            $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    public function dispatch() {
        $listing_id = intval( isset( $_REQUEST['listing_id'] ) ? $_REQUEST['listing_id'] : 0 );

        if ( ! $listing_id )
            return '';

        if ( ! $this->can_submit( $listing_id, $error_msg ) )
            return pdl_render_msg( $error_msg, 'error' );

        $this->listing_id = $listing_id;
        $this->prepare_input();

        if ( ! $this->validate() )
            return $this->render_form( $listing_id, $this->errors );

        // Compose e-mail message.
        $replacements = array( 'listing-url' => get_permalink( $listing_id  ),
                               'listing'     => get_the_title( $listing_id ),
                               'name'        => $this->name,
                               'email'       => $this->email,
                               'message'     => $this->message,
                               'date'        => date_i18n( __('l F j, Y \a\t g:i a'), current_time( 'timestamp' ) ) );
        $email = pdl_email_from_template( 'email-templates-contact', $replacements );
        $email->body = apply_filters( 'pdl_contact_form_email_body', $email->body );
        $email->to = wpdirlist_get_the_business_email( $listing_id );
        $email->reply_to = "{$this->name} <{$this->email}>";
        $email->template = 'plestardirectory-email';

        $html = '';

        if( $email->send() ) {
            $html .= pdl_render_msg( _x( 'Your message has been sent.', 'contact-message', 'PDM' ) );
            $this->update_contacts( $listing_id );

            // Notify admin.
            if ( in_array( 'listing-contact', pdl_get_option( 'admin-notifications' ), true ) ) {
                // $replacements[ 'listing-url' ] = sprintf( _x( '%s (admin: %s)', 'contact-message', 'PDM' ),
                //                                           $replacements['listing-url'],
                //                                           get_edit_post_link( $listing_id ) );
                // $admin_email = pdl_email_from_template( 'email-templates-contact', $replacements );
                $admin_email = new PDL_Email();
                $admin_email->subject = $email->subject;
                $admin_email->body = $email->body;
                $admin_email->to = get_bloginfo( 'admin_email' );

                if ( pdl_get_option( 'admin-notifications-cc' ) )
                    $admin_email->cc[] = pdl_get_option( 'admin-notifications-cc' );

                $admin_email->template = 'plestardirectory-email';
                $admin_email->send();
            }
        } else {
            $html .= pdl_render_msg( _x("There was a problem encountered. Your message has not been sent", 'contact-message', "PDM"), 'error' );
        }

        $html .= sprintf('<p><a href="%s">%s</a></p>', get_permalink($listing_id), _x('Return to listing.', 'contact-message', "PDM"));
        return $html;
    }

}

