<?php

/**
 * Main Plestar Directory Listing class.
 */
final class PDL {

    public $_query_stack = array();


    public function __construct() {
        $this->setup_constants();
        $this->includes();
        $this->hooks();
    }

    private function setup_constants() {
        define( 'PDL_VERSION', '1.0' );

        define( 'PDL_PATH', wp_normalize_path( plugin_dir_path( PDL_PLUGIN_FILE ) ) );
        define( 'PDL_INC', trailingslashit( PDL_PATH . 'includes' ) );
        define( 'PDL_URL', trailingslashit( plugins_url( '/', PDL_PLUGIN_FILE ) ) );
        define( 'PDL_TEMPLATES_PATH', PDL_PATH . 'templates' );

        define( 'PDL_POST_TYPE', 'pdl_listing' );
        define( 'PDL_CATEGORY_TAX', 'pdl_category' );
        define( 'PDL_TAGS_TAX', 'pdl_tag' );
    }

    private function includes() {
        // Make DBO framework available to everyone.
        require_once( PDL_INC . 'db/class-db-model.php' );

        require_once( PDL_INC . 'class-view.php' );

        require_once( PDL_INC . 'class-modules.php' );
        require_once( PDL_INC . 'licensing.php' );

        require_once( PDL_INC . 'form-fields.php' );
        require_once( PDL_INC . 'payment.php' );
        require_once( PDL_PATH . 'includes/class-payment-gateways.php' );
        require_once( PDL_INC . 'installer.php' );

        require_once( PDL_INC . 'class-cron.php' );

        require_once( PDL_INC . 'admin/settings/class-settings.php' );

        require_once( PDL_INC . 'functions.php' );
        require_once( PDL_INC . 'utils.php' );

        require_once( PDL_INC . 'helpers/listing_flagging.php' );

        require_once( PDL_INC . 'class-cpt-integration.php' );
        require_once( PDL_INC . 'class-listing-expiration.php' );
        require_once( PDL_INC . 'class-listing-email-notification.php' );
        require_once( PDL_INC . 'class-abandoned-payment-notification.php' );

        require_once( PDL_INC . 'compatibility/class-compat.php' );
        require_once( PDL_INC . 'class-rewrite.php' );


        require_once( PDL_INC . 'class-assets.php' );
        require_once( PDL_INC . 'class-meta.php' );
        require_once( PDL_INC . 'widgets/class-widgets.php' );

        if ( pdl_is_request( 'frontend' ) ) {
            require_once( PDL_INC . 'templates-ui.php' );
            require_once( PDL_INC . 'template-sections.php' );
            require_once( PDL_INC . 'class-shortcodes.php' );
            require_once( PDL_INC . 'class-recaptcha.php' );
            require_once( PDL_INC . 'class-query-integration.php' );
            require_once( PDL_INC . 'class-dispatcher.php' );
            require_once( PDL_INC . 'class-wordpress-template-integration.php' );
            require_once( PDL_INC . 'seo.php' );
        }

        require_once( PDL_INC . 'themes.php' );

        if ( pdl_is_request( 'admin' ) ) {
            require_once( PDL_INC . 'admin/tracking.php' );
            require_once( PDL_INC . 'admin/class-admin.php' );

            require_once( PDL_INC . 'admin/class-listings-with-no-fee-plan-view.php' );
        }

        require_once( PDL_INC . 'helpers/class-access-keys-sender.php' );
    }

    private function hooks() {
        register_activation_hook( PDL_PLUGIN_FILE, array( $this, 'plugin_activation' ) );
        register_deactivation_hook( PDL_PLUGIN_FILE, array( $this, 'plugin_deactivation' ) );

        add_action( 'init', array( $this, 'init' ), 0 );
        add_action( 'init', array( $this, 'session_init' ), 1 );
        add_action('wp_logout', array( $this, 'end_session' ));
        add_action('wp_login', array( $this, 'end_session' ));
        add_action('end_session_action', array( $this, 'end_session' ));
        
        add_filter( 'plugin_action_links_' . plugin_basename( PDL_PLUGIN_FILE ), array( $this, 'plugin_action_links' ) );

        // Clear cache of page IDs when a page is saved.
        if ( version_compare( phpversion(), '5.3.0', '>=' ) ) {
            $handler = function() {
                delete_transient( 'pdl-page-ids' );
            };
        } else {
            $handler = create_function( '$x = false', 'delete_transient("pdl-page-ids");' );
        }

        add_action( 'save_post_page', $handler );

        // AJAX actions.
        // TODO: Use Dispatcher AJAX support instead of hardcoding these actions here.
        add_action( 'wp_ajax_pdl-listing-submit-image-upload', array( &$this, 'ajax_listing_submit_image_upload' ) );
        add_action( 'wp_ajax_nopriv_pdl-listing-submit-image-upload', array( &$this, 'ajax_listing_submit_image_upload' ) );
        add_action( 'wp_ajax_pdl-listing-submit-image-delete', array( &$this, 'ajax_listing_submit_image_delete' ) );
        add_action( 'wp_ajax_nopriv_pdl-listing-submit-image-delete', array( &$this, 'ajax_listing_submit_image_delete' ) );
        
        add_action( 'wp_ajax_pdl-approve-pending-directory', array( &$this, 'ajax_approve_pending_directory' ) );

        add_action( 'plugins_loaded', array( $this, 'register_cache_groups' ) );
        add_action( 'switch_blog', array( $this, 'register_cache_groups' ) );
    }
    
    public function session_init(){
    	if(!session_id()) {
    		session_start();
    	}
    }
    
    public function end_session(){
    	session_destroy();
    }

    public function init() {
        $this->load_textdomain();

        $this->form_fields = PDL_FormFields::instance();
        $this->formfields = $this->form_fields; // Backwards compat.

        $this->settings = new PDL__Settings();
        $this->settings->bootstrap();

        $this->cpt_integration = new PDL__CPT_Integration();

        $this->licensing = new PDL_Licensing();
        $this->modules = new PDL__Modules();

        $this->themes = new PDL_Themes();

        $this->installer = new PDL_Installer();
        try {
            $this->installer->install();
        } catch ( Exception $e ) {
            $this->installer->show_installation_error( $e );
            return;
        }

        $this->fees = new PDL_Fees_API();

        if ( $manual_upgrade = get_option( 'pdl-manual-upgrade-pending', array() ) ) {
            if ( $this->installer->setup_manual_upgrade() ) {
                add_shortcode( 'plestardirectory', array( $this, 'frontend_manual_upgrade_msg' ) );
                add_shortcode( 'directory-listing', array( $this, 'frontend_manual_upgrade_msg' ) );

                // XXX: Temporary fix to disable features until a pending Manual
                // Upgrades have been performed.
                //
                // Ideally, these hooks would be registered later, making the following
                // lines unnecessary.
                remove_action( 'wp_footer', array( $this->themes, 'fee_specific_coloring' ), 999 );
                remove_action( 'admin_notices', array( &$this->licensing, 'admin_notices' ) );

                return;
            }
        }

        $this->modules->load_i18n();
        $this->modules->init(); // Change to something we can fire in PDL__Modules to register modules.

        $this->payment_gateways = new PDL__Payment_Gateways();

        do_action('pdl_modules_loaded');

        do_action_ref_array( 'pdl_register_settings', array( &$this->settings ) );
        do_action('pdl_register_fields', $this->formfields);
        do_action('pdl_modules_init');

        $this->listings = new PDL_Listings_API();
        $this->payments = new PDL_PaymentsAPI();

        $this->cpt_integration->register_hooks();

        $this->cron = new PDL__Cron();

        $this->setup_email_notifications();

        $this->assets = new PDL__Assets();
        $this->widgets = new PDL__Widgets();

        // We need to ask for frontend requests first, because
        // pdl_is_request( 'admin' ) or is_admin() return true for ajax
        // requests made from the frontend.
        if ( pdl_is_request( 'frontend' ) ) {
            $this->query_integration = new PDL__Query_Integration();
            $this->dispatcher = new PDL__Dispatcher();
            $this->shortcodes = new PDL__Shortcodes();
            $this->template_integration = new PDL__WordPress_Template_Integration();

            $this->meta = new PDL__Meta();
            $this->recaptcha = new PDL_reCAPTCHA();
        }

        if ( pdl_is_request( 'admin' ) ) {
            $this->admin = new PDL_Admin();
        }

        $this->compat = new PDL_Compat();
        $this->rewrite = new PDL__Rewrite();


        do_action( 'pdl_loaded' );
    }

    public function ajax_approve_pending_directory(){
    	global $wpdb;    	
    	if(isset($_POST['cid']) && !empty($_POST['cid'])){
    		$cid = array_map('intval',$_POST['cid']);    		
    		foreach($cid as $id){
    			$post = $_SESSION['pending_directory']->data[$id];    			
    			$new_post = array (
    					'post_type' => PDL_POST_TYPE,
    					'post_title' => $post->post_title,
    					'post_content' => $post->post_content,
    					'post_excerpt' => $post->post_excerpt,
    					'post_status' => 'publish',
    					'comment_status' => 'closed',
    					'ping_status' => 'closed');    			
    			if(isset($post->formfields->tags)){
    				$new_post['tags_input'] = $post->formfields->tags->value;
    			}    			
    			
    			$post_id = wp_insert_post($new_post);
    			
    			if(isset($post->formfields->category)){
    				$cats = array();
    				foreach($post->formfields->category->value as $category){
    					$parent = 0;    					
    					if($category->parent && !empty($category->parentObject)){
    						$term = get_terms(PDL_CATEGORY_TAX,array('parent'=>0,'name'=>$category->parentObject->name));    						
    						if(!empty($term)){
    							$parent = $term[0]->term_id;
    						}else{
    							$categ = array(
    								'cat_ID' => 0,
    								'cat_name' => $category->parentObject->name,
    								'category_description' => $category->parentObject->description,
    								'taxonomy' => PDL_CATEGORY_TAX );
    							$categ = wp_insert_category($categ);
    							$parent = $categ;   							
    						}
    					}
    					
    					$term = get_terms(PDL_CATEGORY_TAX,array('parent'=>$parent,'name'=>$category->name));    					
    					if(!empty($term)){
    						$cats[] = $term[0]->term_id;
    					}else{
    						$categ = array(
    							'cat_ID' => 0,
    							'cat_name' => $category->name,
    							'category_description' => $category->description,
    							'category_parent' => $parent,
    							'taxonomy' => PDL_CATEGORY_TAX );
    						$categ = wp_insert_category($categ);
    						if($categ)$cats[] = $categ;
    					}
    				}
    				    				
    				if(!empty($cats))wp_set_object_terms($post_id,$cats,PDL_CATEGORY_TAX);
    			}
    			
    			if(isset($post->formfields->meta)){
    				$formfields = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}pdl_form_fields","ARRAY_A");
    				$formfieldsIds = array_column($formfields,'label');
    				$formfields = array_combine($formfieldsIds,$formfields);
    				
    				foreach($post->formfields->meta as $meta){
    					if(in_array($meta->label, $formfieldsIds)){
    						$mid = add_post_meta($post_id,'_pdl[fields][' . $formfields[$meta->label]['id'] . ']',$meta->value);
    					}
    				}
    			}
    			
    			$mid = add_post_meta($post_id,'_pdl_approved',$post->ID);
    		}
    		
    		echo json_encode(array('success'=>true));
    		exit;
    	}
    }
    
    public function setup_email_notifications() {
        global $wpdb;

        $this->listing_expiration = new PDL__Listing_Expiration();
        $this->listing_email_notification = new PDL__Listing_Email_Notification();

        if ( $this->settings->get_option( 'payment-abandonment' ) ) {
            $abandoned_payment_notification = new PDL__Abandoned_Payment_Notification( $this->settings, $wpdb );
            add_action( 'pdl_hourly_events', array( $abandoned_payment_notification, 'send_abandoned_payment_notifications' ) );
        }
    }

    public function register_cache_groups() {
        if ( ! function_exists( 'wp_cache_add_non_persistent_groups' ) ) {
            return;
        }

        wp_cache_add_non_persistent_groups( array( 'pdl pages', 'pdl formfields', 'pdl fees', 'pdl submit state', 'pdl' ) );
    }

    private function load_textdomain() {
        //        $languages_dir = str_replace( trailingslashit( WP_PLUGIN_DIR ), '', PDL_PATH . 'languages' );

        $languages_dir = trailingslashit( basename( PDL_PATH ) ) . 'languages';
        load_plugin_textdomain( 'PDM', false, $languages_dir );
    }

    public function plugin_activation() {
        if ( function_exists( 'flush_rewrite_rules' ) ) {
            add_action( 'shutdown', 'flush_rewrite_rules' );
        }
        delete_transient( 'pdl-page-ids' );        
        
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
        	$ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
        	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
        	$ip = $_SERVER['REMOTE_ADDR'];
        }
        
        $data = array();
        $data["action"] = "pdl_add_domain";
        $data["domain"] = site_url();
        $data["ip_address"] = $ip;
        $data["ajax"] = site_url();
                
        $args = array("body"=>$data,
        		'timeout' => '5',
        		'redirection' => '5',
        		'httpversion' => '1.0',
        		'blocking' => true);
        //$response = wp_remote_post("http://localhost/wordpress",$args);
        $response = wp_remote_post("http://directory.plestar.net",$args);
        $res = json_decode(wp_remote_retrieve_body($response));
    }

    public function plugin_deactivation() {
        wp_clear_scheduled_hook( 'pdl_hourly_events' );
        wp_clear_scheduled_hook( 'pdl_daily_events' );
        
        $data = array();
        $data["action"] = "pdl_remove_domain";
        $data["domain"] = site_url();
        
        $response = wp_remote_post("http://directory.plestar.net/",$data);
    }

    public function plugin_action_links( $links ) {
        $links = array_merge(
            array( 'settings' => '<a href="' . admin_url( 'admin.php?page=pdl_settings' ) . '">' . _x( 'Settings', 'admin plugins', 'PDM' ) . '</a>' ),
            $links
        );

        return $links;
    }

    public function is_plugin_page() {
        if ( pdl_current_view() ) {
            return true;
        }

        global $wp_query;

        if ( ! empty( $wp_query->pdl_our_query ) || ! empty( $wp_query->pdl_view ) )
            return true;

        global $post;

        if ( $post && ( 'page' == $post->post_type || 'post' == $post->post_type ) ) {
            foreach ( array_keys( $this->shortcodes->get_shortcodes() ) as $shortcode ) {
                if ( pdl_has_shortcode( $post->post_content, $shortcode ) ) {
                    return true;
                    break;
                }
            }
        }

        if ( $post && PDL_POST_TYPE == $post->post_type )
            return true;

        return false;
    }

    public function get_post_type() {
        return PDL_POST_TYPE;
    }

    public function get_post_type_category() {
        return PDL_CATEGORY_TAX;
    }

    public function get_post_type_tags() {
        return PDL_TAGS_TAX;
    }

    /**
     * @deprecated since fees-revamp. Remove when found, kept for backwards compat.
     */
    public function is_debug_on() {
        return false;
    }

    // TODO: better validation.
    public function ajax_listing_submit_image_upload() {
        $res = new PDL_Ajax_Response();

        $listing_id = intval( $_REQUEST['listing_id'] );

        if ( ! $listing_id )
            return $res->send_error();

        $content_range = null;
        $size = null;

        if ( isset( $_SERVER['HTTP_CONTENT_RANGE'] ) ) {
            $content_range = preg_split('/[^0-9]+/', $_SERVER['HTTP_CONTENT_RANGE']);
            $size =  $content_range ? $content_range[3] : null;
        }

        $attachments = array();
        $files = pdl_flatten_files_array( isset( $_FILES['images'] ) ? $_FILES['images'] : array() );
        $errors = array();

        $listing = PDL_Listing::get( $listing_id );
        $slots_available = 0;

        if ( $plan = $listing->get_fee_plan() )
            $slots_available = absint( $plan->fee_images ) - count( $listing->get_images() );

        if ( ! current_user_can( 'administrator' ) ) {
            if ( ! $slots_available ) {
                return $res->send_error( _x( 'Can not upload any more images for this listing.', 'listing image upload', 'PDM' ) );
            } elseif ( $slots_available < count( $files ) ) {
                return $res->send_error(
                    sprintf(
                        _nx(
                            'You\'re trying to upload %d images, but only have %d slot available. Please adjust your selection.',
                            'You\'re trying to upload %d images, but only have %d slots available. Please adjust your selection.',
                            $slots_available,
                            'listing image upload',
                            'PDM'
                        ),
                        count( $files ),
                        $slots_available
                    )
                );
            }
        }

        foreach ( $files as $i => $file ) {
            $image_error = '';
            $attachment_id = pdl_media_upload( $file,
                                                 true,
                                                 true,
                                                 array( 'image' => true,
                                                        'min-size' => intval( pdl_get_option( 'image-min-filesize' ) ) * 1024,
                                                        'max-size' => intval( pdl_get_option( 'image-max-filesize' ) ) * 1024,
                                                        'min-width' => pdl_get_option( 'image-min-width' ),
                                                        'min-height' => pdl_get_option( 'image-min-height' )
                                                     ),
                                                 $image_error ); // TODO: handle errors.

            if ( $image_error )
                $errors[ $file['name'] ] = $image_error;
            else
                $attachments[] = $attachment_id;
        }


        $html = '';
        foreach ( $attachments as $attachment_id ) {
            $html .= pdl_render( 'submit-listing-images-single',
                                   array( 'image_id' => $attachment_id, 'listing_id' => $listing_id ),
                                   false );
        }

        $listing->set_images( $attachments, true );

        if ( $errors ) {
            $error_msg = '';

            foreach ( $errors as $fname => $error )
                $error_msg .= sprintf( '&#149; %s: %s', $fname, $error ) . '<br />';

            $res->add( 'uploadErrors', $error_msg );
        }

        $res->add( 'attachmentIds', $attachments );
        $res->add( 'html', $html );
        $res->send();
    }

    public function ajax_listing_submit_image_delete() {
        $res = new PDL_Ajax_Response();

        $image_id = intval( $_REQUEST['image_id'] );
        $listing_id = intval( $_REQUEST['listing_id'] );
        $nonce = $_REQUEST['_wpnonce'];

        if ( ! $image_id || ! $listing_id || ! wp_verify_nonce( $nonce, 'delete-listing-' . $listing_id . '-image-' . $image_id ) )
            $res->send_error();

        $parent_id = (int) wp_get_post_parent_id( $image_id );
        if ( $parent_id != $listing_id )
            $res->send_error();

        $listing = pdl_get_listing( $listing_id );

        if ( ! $listing ) {
            $res->send_error();
        }

        $thumbnail_id = $listing->get_thumbnail_id();

        if ( false !== wp_delete_attachment( $image_id, true ) && $image_id == $thumbnail_id ) {
            $listing->set_thumbnail_id( 0 );
        }

        $res->add( 'imageId', $image_id );
        $res->send();
    }

    public function frontend_manual_upgrade_msg() {
        wp_enqueue_style( 'pdl-base-css' );

        if ( current_user_can( 'administrator' ) ) {
            return pdl_render_msg(
                str_replace(
                    '<a>',
                    '<a href="' . admin_url( 'admin.php?page=pdl-upgrade-page' ) . '">',
                    __( 'The directory features are disabled at this time because a <a>manual upgrade</a> is pending.', 'PDM' )
                ),
                'error'
            );
        }

        return pdl_render_msg(
            __( 'The directory is not available at this time. Please try again in a few minutes or contact the administrator if the problem persists.', 'PDM' ),
            'error'
        );
    }

}
