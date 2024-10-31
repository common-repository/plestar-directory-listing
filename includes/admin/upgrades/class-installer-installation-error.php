<?php

class PDL__Installer__Installation_Error {

    private $exception;

    public function __construct( $exception ) {
        $this->exception = $exception;

        add_action( 'admin_notices', array( $this, 'installation_error_notice' ) );
    }

    public function installation_error_notice() {
        print '<div class="notice notice-error"><p>';
        print '<strong>' . __( 'Plestar Directory Listing - Installation Failed', 'PDM' ) . '</strong>';
        print '<br />';
        print  __( 'Plestar Directory Listing installation failed. An exception with following message was generated:', 'PDM' );
        print '<br/><br/>';
        print '<i>' . $this->exception->getMessage() . '</i>';
        print '<br /><br />';

        $message = __( 'Please <contact-link>contact customer support</a>.', 'PDM' );
        $message = str_replace( '<contact-link>', sprintf( '<a href="%s">', 'http://plestar.net/contact/' ), $message );

        print $message;
        print '</p></div>';
    }
}
