<?php

class PDL__Views__Show_Listing extends PDL__View {

    public function dispatch() {
        if ( ! pdl_user_can( 'view', null ) )
            $this->_http_404();


        $html = '';
        if ( 'publish' != get_post_status( get_the_ID() ) && current_user_can( 'edit_posts' ) ) {
            $html .= pdl_render_msg( _x('This is just a preview. The listing has not been published yet.', 'preview', 'PDM') );
        }

/*        // Handle ?v=viewname argument for alternative views (other than 'single').
        $view = '';
        if ( isset( $_GET['v'] ) )
            $view = pdl_capture_action_array( 'pdl_listing_view_' . trim( $_GET['v'] ), array( $listing_id ) );*/

        $html .= pdl_render_listing( null, 'single', false, true );

        return $html;
    }

}
