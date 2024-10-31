<?php
class PDL__Views__Main extends PDL__View {

    private function warnings() {
        $html = '';

        if ( count(get_terms(PDL_CATEGORY_TAX, array('hide_empty' => 0))) == 0 ) {
            if (is_user_logged_in() && current_user_can('install_plugins')) {
                $html .= pdl_render_msg( _x('There are no categories assigned to the directory yet. You need to assign some categories to the directory. Only admins can see this message. Regular users are seeing a message that there are currently no listings in the directory. Listings cannot be added until you assign categories to the directory.', 'templates', 'PDM'), 'error' );
            } else {
                $html .= "<p>" . _x('There are currently no listings in the directory.', 'templates', 'PDM') . "</p>";
            }
        }

        if ( current_user_can( 'administrator' ) && pdl_get_option( 'hide-empty-categories' ) &&
             wp_count_terms( PDL_CATEGORY_TAX, 'hide_empty=0' ) > 0 && wp_count_terms( PDL_CATEGORY_TAX, 'hide_empty=1' ) == 0 ) {
            $msg = _x( 'You have "Hide Empty Categories" on and some categories that don\'t have listings in them. That means they won\'t show up on the front end of your site. If you didn\'t want that, click <a>here</a> to change the setting.',
                       'templates',
                       'PDM' );
            $msg = str_replace( '<a>',
                                '<a href="' . admin_url( 'admin.php?page=pdl_settings&tab=listings#hide-empty-categories' ) . '">',
                                $msg );
            $html .= pdl_render_msg( $msg );
        }
    }

    public function dispatch() {
        global $pdl;

        $html = '';

        // Warnings and messages for admins.
        $html .= $this->warnings();

        // Listings under categories?
        if ( pdl_get_option( 'show-listings-under-categories' ) ) {
            require_once ( PDL_PATH . 'includes/views/all_listings.php' );
            $v = new PDL__Views__All_Listings( array( 'menu' => false ) );
            $listings = $v->dispatch();
        } else {
            $listings = '';
        }

        $html = $this->_render_page( 'main_page', array( 'listings' => $listings ) );

        return $html;
    }

}
