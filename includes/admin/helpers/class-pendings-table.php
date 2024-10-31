<?php
if ( ! class_exists( 'WP_List_Table' ) )
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

/**
 * @since 5.0
 */
class PDL__Admin__Pendings_Table extends WP_List_Table {

    public function __construct() {
    	global $status, $page;
        parent::__construct( array(
            'singular' => "pending",
            'plural' => "pendings",'ordering' => 'order',
            'ajax' => false
        ) );
    }
    
    function column_default($item, $column_name){    	
    	switch($column_name) {
    		case 'action' :
    			return '<div style="margin-top:4px;"><a class="button-primary pending-approve" onclick="" title="Approve">Approve</a></div>';
    			break;
    		case 'category':
    			$category = $item->formfields->category->value[0]->name;
    			return '<div style="margin-top:4px;">'.$category.'</div>';
    			break;
    		case 'date':
    			$date = $item->post_date;
    			return '<div style="margin-top:4px;">'.date('d-m-Y',strtotime($date)).'</div>';
    			break;
    		default :
    			return '<div style="margin-top:4px;">'.$item->$column_name.'</div>';
    			break;
    	}
    }    

    public function no_items() {
        echo _x( 'No pendings found.', 'pendings admin', 'PDM' );
    }

    public function get_columns() {
        $cols = array(
            'cb'          => '<input type="checkbox" />',
            'post_title' => "Title",
            'category' => "Category",
            'date' => "Date",
            'action' => "Action"
        );

        return $cols;
    }
    

    function column_cb($item){
    	return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->ID );
    }

    public function prepare_items() {
        $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
        $args = array();
        $current_page = $this->get_pagenum();
        $total_items = $this->getItems($current_page);        
        $per_page = 10;        
        $this->i = ($current_page-1)*$per_page;
        
        //$this->items = array_slice($this->items,(($current_page-1)*$per_page),$per_page);
        
        $this->set_pagination_args( array( 'total_items' => $total_items, 'per_page' => $per_page, 'total_pages' => ceil($total_items/$per_page) ) );
    }
    
    private function getItems($current_page){
    	$ad = $this->get_meta_values();
    	/* if(isset($_SESSION['pending_directory'])){
    		$this->items = $_SESSION['pending_directory'];
    		echo "<pre>";
    		print_r($_SESSION['pending_directory']);
    		echo "</pre>";
    		return false;
    	} */
    	$args = array("body"=>array("action"=>"pdl-get-pending-items","domain"=>site_url(),"cid"=>$ad,"paged"=>$current_page),
			    'timeout' => '5',
			    'redirection' => '5',
			    'httpversion' => '1.0',
			    'blocking' => true);
    	//$response = wp_remote_post("http://localhost/wordpress/wp-admin/admin-ajax.php",$args);
    	$response = wp_remote_post("http://directory.plestar.net/wp-admin/admin-ajax.php",$args);
    	/* echo "<pre>";
    	print_r($response);
    	echo "</pre>";  */
    	$res = json_decode(wp_remote_retrieve_body($response));
    	
    	$this->items = $res->data;
    	$_SESSION['pending_directory'] = $res;
    	
    	return $res->count;
    }
    
    private function get_meta_values( $key = '_pdl_approved', $type = PDL_POST_TYPE, $status = 'publish' ) {	
	    global $wpdb;	
	    if( empty( $key ) )
	        return;	
	    $r = $wpdb->get_col( $wpdb->prepare( "
	        SELECT pm.meta_value FROM {$wpdb->postmeta} pm
	        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
	        WHERE pm.meta_key = '%s' 
	        AND p.post_status = '%s' 
	        AND p.post_type = '%s'
	    ", $key, $status, $type ));	
	    return $r;
	}
}