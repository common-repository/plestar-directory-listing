<?php
//require_once( PDL_PATH . 'includes/class-pending.php' );

/**
 * @since 5.0
 */
class PDL__Admin__Pendings extends PDL__Admin__Controller {

    function _enqueue_scripts() {
        wp_enqueue_script( 'jquery-ui-datepicker' );
        pdl_enqueue_jquery_ui_style();
        
        wp_register_script( 'hdwpending', PDL_URL . 'assets/js/admin-pending.js', 'jquery', false );
        wp_enqueue_script( 'hdwpending' );
        
        parent::_enqueue_scripts();
    }

    function index() {    	
    	echo pdl_admin_header(null, 'admin-pendings');
        require_once( PDL_INC . 'admin/helpers/class-pendings-table.php' );

        $table = new PDL__Admin__Pendings_Table();
        $table->prepare_items();
        $table->display();
    }
}