<?php
require_once( PDL_PATH . 'includes/views/submit_listing.php' );


class PDL__Views__Edit_Listing extends PDL__Views__Submit_Listing {

    public function __construct( $args = null ) {
        parent::__construct();
        $this->editing = true;
    }

}

