<?php

class PDL_Admin_Listings {

    function __construct() {
        add_action('admin_init', array($this, 'add_metaboxes'));
        //add_action( 'pdl_admin_notices', array( $this, 'no_plan_edit_notice' ) );

        add_action( 'manage_' . PDL_POST_TYPE . '_posts_columns', array( &$this, 'modify_columns' ) );
        add_action( 'manage_' . PDL_POST_TYPE . '_posts_custom_column', array( &$this, 'listing_column' ), 10, 2 );
        add_filter( 'manage_edit-' . PDL_POST_TYPE . '_sortable_columns', array( &$this, 'sortable_columns' ) );

        add_filter( 'views_edit-' . PDL_POST_TYPE, array( &$this, 'listing_views' ) );
        add_filter( 'posts_clauses', array( &$this, 'listings_admin_filters' ) );

        add_filter( 'post_row_actions', array( &$this, 'row_actions' ), 10, 2 );

        add_action('admin_footer', array($this, '_add_bulk_actions'));
        add_action('admin_footer', array($this, '_fix_new_links'));

        add_action( 'pdl_save_listing', array( $this, 'maybe_save_fields' ) );
        add_action( 'pdl_save_listing', array( $this, 'maybe_update_plan' ) );

        // Filter by category.
        add_action( 'restrict_manage_posts', array( &$this, '_add_category_filter' ) );
        add_action( 'parse_query', array( &$this, '_apply_category_filter' ) );

        add_action( 'restrict_manage_posts', array( &$this, '_add_category_filter' ) );

        // Augment search with username search.
        add_filter( 'posts_search', array( $this, 'username_and_user_email_search_support' ), 10, 2 );
    }

    // Category filter. {{

    function _add_category_filter() {
        global $typenow;
        global $wp_query;

        if ( PDL_POST_TYPE != $typenow )
            return;

        wp_dropdown_categories( array(
            'show_option_all' => _x( 'All categories',  'admin category filter', 'PDM' ),
            'taxonomy' => PDL_CATEGORY_TAX,
            'name' => PDL_CATEGORY_TAX,
            'orderby' => 'name',
            'selected' => ( ! empty ( $wp_query->query[ PDL_CATEGORY_TAX ] ) ? $wp_query->query[ PDL_CATEGORY_TAX ] : 0 ),
            'hierarchical' => true,
            'hide_empty' => false,
            'depth' => 4
        ) );
    }

    function _apply_category_filter( $query ) {
        if ( ! is_admin() )
            return;

        if ( ! function_exists( 'get_current_screen' ) )
            return;

        $screen = get_current_screen();

        if ( ! $screen )
            return;

        if ( 'edit' != $screen->base ||  PDL_POST_TYPE != $screen->post_type )
            return;

        if ( empty( $query->query_vars[ PDL_CATEGORY_TAX ] ) || ! is_numeric( $query->query_vars[ PDL_CATEGORY_TAX ] ) )
            return;

        $term = get_term_by( 'id', $query->query_vars[ PDL_CATEGORY_TAX ], PDL_CATEGORY_TAX );

        if ( ! $term )
            return;

        $query->query_vars[ PDL_CATEGORY_TAX ] = $term->slug;
    }

    // }}

    function username_and_user_email_search_support( $search, $query ) {
        global $wpdb;

        if ( ! function_exists( 'get_current_screen' ) || ! is_admin() ) {
            return $search;
        }

        $screen = get_current_screen();

        if ( ! $screen || 'edit' != $screen->base || PDL_POST_TYPE != $screen->post_type ) {
            return $search;
        }

        if ( ! isset( $_GET['s'] ) || ! $query->is_search ) {
            return $search;
        }

        $search_term = wp_unslash( $_GET['s'] );
        $search_term = '%' . $wpdb->esc_like( $search_term ) . '%';

        $sql = 'SELECT ID FROM ' . $wpdb->users . ' ';
        $sql.= 'LEFT JOIN ' . $wpdb->usermeta . " AS first_name ON ( first_name.user_id = ID AND first_name.meta_key = 'first_name' ) ";
        $sql.= 'LEFT JOIN ' . $wpdb->usermeta . " AS last_name ON ( last_name.user_id = ID AND last_name.meta_key = 'last_name' ) ";
        $sql.= 'WHERE user_nicename LIKE %s OR user_email LIKE %s OR display_name LIKE %s OR first_name.meta_value LIKE %s OR last_name.meta_value LIKE %s';

        $sql = $wpdb->prepare(
            $sql,
            $search_term,
            $search_term,
            $search_term,
            $search_term,
            $search_term
        );

        $user_ids = $wpdb->get_col( $sql );

        if ( ! $user_ids ) {
            return $search;
        }

        $author_condition = sprintf( 'post_author IN (%s)', esc_sql( implode( ', ', $user_ids ) ) );
        $search = preg_replace( '/\(\(\((.*)\)\)\)/', '((' . $author_condition . ' OR (\1)))', $search );

        return $search;
    }

    function no_plan_edit_notice() {
        if ( ! function_exists( 'get_current_screen' ) )
            return;

        $screen = get_current_screen();

        if ( ! $screen || PDL_POST_TYPE != $screen->id || 'add' == $screen->action  )
            return;

        global $post;

        if ( ! $post )
            return;

        $listing = PDL_Listing::get( $post->ID );

        if ( ! $listing )
            return;

        if( ! $listing->has_fee_plan() )
            pdl_admin_message( _x( 'This listing doesn\'t have a fee plan assigned. This is required in order to determine the features available to this listing, as well as handling renewals.', 'admin listings', 'PDM' ), 'error' );
    }

    function add_metaboxes() {
       add_meta_box( 'pdl-listing-plan',
                      __( 'Listing Information', 'PDM' ),
                      array( $this, '_metabox_listing_info' ),
                      PDL_POST_TYPE,
                      'side',
                      'core' );
        add_meta_box( 'pdl-listing-timeline',
                      __( 'Listing Timeline', 'PDM' ),
                      array( $this, '_metabox_listing_timeline' ),
                      PDL_POST_TYPE,
                      'side',
                      'core' );
        add_meta_box( 'pdl-listing-fields',
                      _x( 'Directory Listing Fields / Images', 'admin', 'PDM' ),
                      array( 'PDL_Admin_Listing_Fields_Metabox', 'metabox_callback' ),
                      PDL_POST_TYPE,
                      'normal',
                      'core' );

        if ( pdl_get_option( 'enable-listing-flagging' ) ) {
            add_meta_box(
                'pdl-listing-flagging',
                __( 'Listing Reports', 'PDM' ),
                array( $this, '_metabox_listing_flagging' ),
                PDL_POST_TYPE,
                'normal',
                'core'
            );
        }
    }

    public function _metabox_listing_info( $post ) {
        require_once( PDL_PATH . 'includes/admin/helpers/class-listing-information-metabox.php' );
        $metabox = new PDL__Admin__Metaboxes__Listing_Information( $post->ID );
        echo $metabox->render();
    }

    public function _metabox_listing_timeline( $post ) {
        require_once( PDL_PATH . 'includes/admin/helpers/class-listing-timeline.php' );
        $timeline = new PDL__Listing_Timeline( $post->ID );

        echo $timeline->render();
    }

    public function _metabox_listing_flagging( $post ) {
        require_once( PDL_PATH . 'includes/admin/helpers/class-listing-flagging-metabox.php' );
        $flagging_metabox = new PDL__Admin__Metaboxes__Listing_Flagging( $post->ID );

        echo $flagging_metabox->render();
    }

    // {{{ Custom columns.

    function modify_columns( $columns ) {
        // Hide comments and author column.
        unset( $columns['author'], $columns['comments'] );

        $new_columns = array();

        foreach ( $columns as $c_key => $c_label ) {
            // Insert category and expiration date after title.
            if ( 'title' == $c_key ) {
                $new_columns['title'] = $c_label;
                $new_columns['category'] = _x( 'Categories', 'admin', 'PDM' );
                $new_columns['expiration_date'] = __( 'Expires on', 'PDM' );
                continue;
            }

            $new_columns[ $c_key ] = $c_label;
        }

        // Attributes goes last.
        $new_columns['attributes'] = __( 'Attributes', 'PDM' );

        $new_columns = apply_filters( 'pdl_admin_directory_columns', $new_columns );
        return $new_columns;
    }

    function sortable_columns( $columns ) {
        $columns['title_'] = 'title';
        return $columns;
    }

    function listing_column( $column, $post_id ) {
        if ( ! method_exists( $this, 'listing_column_' . $column ) )
            return do_action( 'pdl_admin_directory_column_' . $column, $post_id );

        call_user_func( array( &$this, 'listing_column_' . $column ), $post_id );
    }

    function listing_column_category( $post_id ) {
        $terms = wp_get_post_terms( $post_id, PDL_CATEGORY_TAX );
        foreach ( $terms as $i => $term ) {
            printf( '<a href="%s">%s</a>', get_term_link( $term->term_id, PDL_CATEGORY_TAX ), $term->name );

            if ( ( $i + 1 ) != count( $terms ) )
                echo ', ';
        }
    }

    public function listing_column_expiration_date( $post_id ) {
        $listing = PDL_Listing::get( $post_id );
        $exp_date = $listing->get_expiration_date();

        if ( $exp_date )
            echo date_i18n( get_option( 'date_format' ), strtotime( $exp_date ) );
        else
            echo _x( 'Never', 'admin listings', 'PDM' );
    }

    public function listing_column_attributes( $post_id ) {
        $listing = PDL_Listing::get( $post_id );
        $plan = $listing->get_fee_plan();

        $attributes = array();

        if ( ! $plan ) {
            $attributes['no-fee-plan'] = '<span class="pdl-tag pdl-listing-attr-no-fee-plan">' . _x( 'No Fee Plan', 'listing attribute', 'PDM' ) . '</span>';
        }

        $listing_status = $listing->get_status();

        if ( ! in_array( $listing_status, array( 'unknown', 'legacy', 'complete' ) ) ) {
            $attributes[ 'listing-status' ] = '<span class="pdl-tag pdl-listing-status-' . $listing_status . '">' . $listing->get_status_label() . '</span>';
        }

        foreach ( $listing->get_flags() as $f ) {
            $attributes[ $f ] = '<span class="pdl-tag pdl-listing-attr-' . $f . '">' . ucwords( str_replace( '-', ' ', $f ) ) . '</span>';
        }

        if ( $plan ) {
            if ( $plan->is_sticky )
                $attributes['featured'] = '<span class="pdl-tag pdl-listing-attr-featured">' . _x( 'Featured', 'admin listings', 'PDM' ) . '</span>';

            if ( $plan->is_recurring )
                $attributes['recurring'] = '<span class="pdl-tag pdl-listing-attr-recurring">' . _x( 'Recurring', 'admin listings', 'PDM' ) . '</span>';

            if ( 0.0 == $plan->fee_price )
                $attributes['free'] = '<span class="pdl-tag pdl-listing-attr-free">' . _x( 'Free', 'admin listings', 'PDM' ) . '</span>';
            elseif ( 'pending_payment' != $listing->get_status() )
                $attributes['paid'] = '<span class="pdl-tag pdl-listing-attr-paid">' . _x( 'Paid', 'admin listings', 'PDM' ) . '</span>';
        }

        if ( count( PDL__Listing_flagging::get_flagging_meta( $listing->get_id() ) ) ) {
            $attributes['reported'] = '<span class="pdl-tag pdl-listing-attr-reported">' . _x( 'Reported', 'admin listings', 'PDM' ) . '</span>';
        }

        $attributes = apply_filters( 'pdl_admin_directory_listing_attributes', $attributes, $listing );

        foreach ( $attributes as $attr ) {
            echo $attr;
        }
    }


    // }}}


    // {{{ List views.

    function listing_views( $views ) {
        if ( ! current_user_can( 'administrator' ) && ! current_user_can( 'editor' ) ) {
            if ( current_user_can( 'contributor' ) && isset( $views_['mine'] ) )
                return array( $views_['mine'] );

            return array();
        }

        $post_statuses = get_post_statuses();
        $post_statuses = array_keys( get_post_statuses() );
        $post_statuses_string = implode( ',', $post_statuses );

        foreach ( PDL_Listing::get_stati() as $status_id => $status_label ) {
            if ( in_array( $status_id, array( 'unknown', 'legacy', 'complete' ) ) ) {
                continue;
            }

            $count = absint( PDL_Listing::count_listings( array( 'status' => $status_id, 'post_status' => $post_statuses_string ) ) );
            if ( 0 == $count ) {
                continue;
            }

            $count = number_format_i18n( $count );

            $current_class = ( ! empty( $_GET['listing_status'] ) && $status_id == $_GET['listing_status'] ) ? 'current' : '';
            $views[ 'pdl-status-' . $status_id ] = "<a href='" . remove_query_arg( array( 'post_status', 'author', 'all_posts', 'pdmfilter' ), add_query_arg( 'listing_status', $status_id ) ) . "' class='{$current_class}'>${status_label} <span class='count'>({$count})</span></a>";
        }
      
        if ( pdl_get_option( 'enable-listing-flagging' ) ) {
            global $wpdb;

            $post_statuses_string = "'publish', 'draft', 'pending'";
            $count = absint( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(p.ID) FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id WHERE p.post_type = %s AND p.post_status IN ({$post_statuses_string}) AND pm.meta_key = %s AND pm.meta_value = %d", PDL_POST_TYPE, '_pdl_flagged', 1 ) ) );

            if ( $count > 0 ) {
                $views['reported'] = sprintf(
                    '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>',
                    esc_url( add_query_arg( 'listing_status', 'reported' ) ),
                    pdl_getv( $_REQUEST, 'listing_status' ) == 'reported' ? 'current' : '',
                    _x( 'Reported', 'listing status', 'PDM' ),
                    number_format_i18n( $count )
                );
            }
        }      

        $views = apply_filters( 'pdl_admin_directory_views', $views, "'" . implode( "','", $post_statuses ) . "'" );

        return $views;
    }

    function listings_admin_filters( $pieces ) {
        global $wpdb;

        if ( ! is_admin() || ( ! isset( $_REQUEST['listing_status'] ) && ! isset( $_REQUEST['pdmfilter'] ) ) || ! function_exists( 'get_current_screen' ) || ! get_current_screen() || 'edit-' . PDL_POST_TYPE != get_current_screen()->id )
            return $pieces;


        $status_filter = pdl_getv( $_REQUEST, 'listing_status', 'all' );
        $other_filter = pdl_getv( $_REQUEST, 'pdmfilter', '' );

        if ( in_array( $status_filter, array_keys( PDL_Listing::get_stati() ), true ) ) {
            $pieces['join']  .= " LEFT JOIN {$wpdb->prefix}pdl_listings ls ON ls.listing_id = {$wpdb->posts}.ID ";
            $pieces['where'] .= $wpdb->prepare( "AND ls.listing_status = %s", $status_filter );
        }

        if ( 'reported' == $status_filter ) {
            $pieces['join']  .= " LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = {$wpdb->posts}.ID ";
            $pieces['where'] .= $wpdb->prepare( " AND pm.meta_key = %s AND pm.meta_value = %d", '_pdl_flagged', 1 );
        }

        if ( $other_filter )
            $pieces = apply_filters( 'pdl_admin_directory_filter', $pieces, $other_filter );

        return $pieces;
    }

    // }}}


    public function row_actions($actions, $post) {
        if ( PDL_POST_TYPE != $post->post_type )
            return $actions;

        if (current_user_can('contributor') && ! current_user_can( 'administrator' ) ) {
            if (pdl_user_can('edit', $post->ID))
                $actions['edit'] = sprintf('<a href="%s">%s</a>',
                                            pdl_url( 'edit_listing', $post->ID ),
                                            _x('Edit Listing', 'admin actions', 'PDM'));

            if ( pdl_user_can( 'delete', $post->ID ) )
                $actions['delete'] = sprintf( '<a href="%s">%s</a>', pdl_url( 'delete_listing', $post->ID ), _x( 'Delete Listing', 'admin actions', 'PDM' ) );
        }

        if ( ! current_user_can( 'administrator' ) )
            return $actions;

        $listing = pdl_get_listing( $post->ID );
        $payments = $listing->get_payments();
        if ( $payments->count() > 1 ) {
            $actions['view-payments'] = '<a href="' . esc_url( admin_url( 'admin.php?page=pdl_admin_payments&listing=' . $listing->get_id() ) ) . '">' . _x( 'View Payments', 'admin actions', 'PDM' ) . '</a>';
        } else {
            $payment = $payments->get();

            if ( $payment )
                $actions['view-payments'] = '<a href="' . esc_url( $payment->get_admin_url() ) . '">' . _x( 'View Payment', 'admin actions', 'PDM' ) . '</a>';
        }

        $listing = pdl_get_listing( $post->ID );
        $actions = apply_filters( 'pdl_admin_directory_row_actions', $actions, $listing );

        return $actions;
    }

    /**
     * @since 5.0
     */
    public function maybe_save_fields( $post_id ) {
        if ( ! is_admin() )
            return;

        $nonce = isset( $_POST['pdl-admin-listing-fields-nonce'] ) ? $_POST['pdl-admin-listing-fields-nonce'] : '';

        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'save listing fields' ) || empty( $_POST['listingfields'] ) )
            return;

        $fields = pdl_get_form_fields( array( 'association' => 'meta' ) );
        foreach ( $fields as $field ) {
            if ( isset( $_POST['listingfields'][ $field->get_id() ] ) ) {
                $value = $field->convert_input( $_POST['listingfields'][ $field->get_id() ] );
                $field->store_value( $post_id, $value );
            } else {
                $field->store_value( $post_id, $field->convert_input( null ) );
            }
        }

        $listing = pdl_get_listing( $post_id );

        // Update image information.
        if ( ! empty ( $_POST['thumbnail_id'] ) )
            $listing->set_thumbnail_id( intval($_POST['thumbnail_id']) );

        // Images info.
        if ( ! empty( $_POST['images_meta'] ) ) {
            $meta = $_POST['images_meta'];

            foreach ( $meta as $img_id => $img_meta ) {
                update_post_meta( $img_id, '_pdl_image_weight', absint( $img_meta[ 'order' ] ) );
                update_post_meta( $img_id, '_pdl_image_caption', strval( $img_meta[ 'caption' ] ) );
            }
        }
    }

    /**
     * @since 5.0
     */
    public function maybe_update_plan( $post_id ) {
        if ( ! is_admin() )
            return;

        $nonce = isset( $_POST['pdl-admin-listing-plan-nonce'] ) ? $_POST['pdl-admin-listing-plan-nonce'] : '';

        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'update listing plan' ) )
            return;

        global $wpdb;

        $listing = pdl_get_listing( $post_id );
        $new_plan = ! empty( $_POST['listing_plan' ] ) ? $_POST['listing_plan'] : array();
        $current_plan = $listing->get_fee_plan();

        if ( ! $current_plan && empty( $new_plan['fee_id'] ) ) {
            return;
        }

        if ( ! $current_plan || (int) $current_plan->fee_id != (int) $new_plan['fee_id'] ) {
            $payment = $listing->set_fee_plan_with_payment( intval($new_plan['fee_id']) );

            if ( $payment )
                $payment->process_as_admin();
        }

        if ( ! $current_plan ) {
            $listing->set_flag( 'admin-posted' );
        }

        // Update plan attributes.
        $row = array();
        $row['expiration_date'] = '' == $new_plan['expiration_date'] ? null : sanitize_text_field($new_plan['expiration_date']);
        $row['fee_images'] = absint( $new_plan['fee_images'] );

        $wpdb->update( $wpdb->prefix . 'pdl_listings', $row, array( 'listing_id' => $post_id ) );

        // Check if the status needs to be changed.
        if ( 'expired' == $listing->get_status() ) {
            if ( null === $row['expiration_date'] || strtotime( $new_plan['expiration_date'] ) > current_time( 'timestamp' ) ) {
                $listing->get_status( true, true );
            }
        }
    }

    public function _add_bulk_actions() {
        if (!current_user_can('administrator'))
            return;

        if ($screen = get_current_screen()) {
            if ($screen->id == 'edit-' . PDL_POST_TYPE) {
                if (isset($_GET['post_type']) && $_GET['post_type'] == PDL_POST_TYPE) {
                    $bulk_actions = array('sep0' => '–',
                                          'change-to-publish' => _x('Publish listings', 'admin actions', 'PDM'),
                                          'change-to-pending' => _x( 'Mark as "Pending Review"', 'admin actions', 'PDM' ),
                                          'change-to-draft' => _x( 'Hide from directory (mark as "Draft")', 'admin actions', 'PDM' ),
                                          'sep1' => '–',
                                          'renewlisting' => _x( 'Renew listings', 'admin actions', 'PDM' ),
                                          'change-to-expired' => _x( 'Set listings as "Expired"', 'admin actions', 'PDM' ),
                                          /* Disabled as per https://github.com/drodenbaugh/PlestarDirectoryPlugin/issues/3279. */
                                          /*'approve-payments' => _x( 'Approve pending payments', 'admin actions', 'PDM' ),*/
                                      );

                    if ( pdl_get_option( 'enable-key-access' ) ) {
                        $bulk_actions['send-access-keys'] = _x( 'Send access keys', 'admin actions', 'PDM' );
                    }

                    $bulk_actions = apply_filters( 'pdl_admin_directory_bulk_actions', $bulk_actions );

                    // the 'bulk_actions' filter doesn't really work for this until this bug is fixed: http://core.trac.wordpress.org/ticket/16031
                    echo '<script type="text/javascript">';

                    foreach ($bulk_actions as $action => $text) {
                        echo sprintf('jQuery(\'select[name="%s"]\').append(\'<option value="%s" data-uri="%s">%s</option>\');',
                                    'action', 'listing-' . $action, esc_url( add_query_arg('pdmaction', $action) ), $text);
                        echo sprintf('jQuery(\'select[name="%s"]\').append(\'<option value="%s" data-uri="%s">%s</option>\');',
                                    'action2', 'listing-' . $action, esc_url( add_query_arg('pdmaction', $action) ), $text);
                    }

                    echo '</script>';
                }
            }
        }
    }

    public function _fix_new_links() {
        // 'contributors' should still use the frontend to add listings (editors, authors and admins are allowed to add things directly)
        // XXX: this is kind of hacky but is the best we can do atm, there aren't hooks to change add links
        if (current_user_can('contributor') && isset($_GET['post_type']) && $_GET['post_type'] == PDL_POST_TYPE) {
            echo '<script type="text/javascript">';
            echo sprintf('jQuery(\'a.add-new-h2\').attr(\'href\', \'%s\');', pdl_get_page_link('add-listing'));
            echo '</script>';
        }
    }

}
