<?php
if (!class_exists('WP_List_Table'))
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class PDL_FormFieldsTable extends WP_List_Table {

    public function __construct() {
        parent::__construct(array(
            'singular' => _x('form field', 'form-fields admin', 'PDM'),
            'plural' => _x('form fields', 'form-fields admin', 'PDM'),
            'ajax' => false
        ));
    }

    public function get_columns() {
        return array(
            'order' => _x('Order', 'form-fields admin', 'PDM'),
            'label' => _x('Label / Association', 'form-fields admin', 'PDM'),
            'type' => _x('Type', 'form-fields admin', 'PDM'),
            'validator' => _x('Validator', 'form-fields admin', 'PDM'),
            'tags' => _x( 'Field Attributes', 'form-fields admin', 'PDM' ),
        );
    }

    public function prepare_items() {
        $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());

        $formfields_api = PDL_FormFields::instance();
        $this->items = $formfields_api->get_fields();
    }

    /* Rows */
    public function column_order($field) {
        return sprintf( '<span class="pdl-drag-handle" data-field-id="%s"></span> <a href="%s"><strong>â†‘</strong></a> | <a href="%s"><strong>â†“</strong></a>',
                        $field->get_id(),
                        esc_url( add_query_arg( array('action' => 'fieldup', 'id' => $field->get_id() ) ) ) ,
                        esc_url( add_query_arg( array('action' => 'fielddown', 'id' => $field->get_id() ) ) )
                       );
    }

    public function column_label( $field ) {
        $actions = array();
        $actions['edit'] = sprintf( '<a href="%s">%s</a>',
                                    esc_url( add_query_arg( array( 'action' => 'editfield', 'id' => $field->get_id() ) ) ),
                                    _x( 'Edit', 'form-fields admin', 'PDM' ) );

        if ( ! $field->has_behavior_flag( 'no-delete' ) ) {
            $actions['delete'] = sprintf( '<a href="%s">%s</a>',
                                         esc_url( add_query_arg( array( 'action' => 'deletefield', 'id' => $field->get_id() ) ) ),
                                         _x( 'Delete', 'form-fields admin', 'PDM') );
        }

        $html = '';
        $html .= sprintf( '<strong><a href="%s">%s</a></strong> (as <i>%s</i>)',
                          esc_url( add_query_arg( array( 'action' => 'editfield', 'id' => $field->get_id() ) ) ),
                          esc_attr( $field->get_label() ),
                          $field->get_association() );
        $html .= $this->row_actions( $actions );

        return $html;
    }

    public function column_type( $field ) {
        return esc_html( $field->get_field_type()->get_name() );
    }

    public function column_validator( $field ) {
        return esc_html( implode( ',',  $field->get_validators() ) );
    }

    public function column_tags( $field ) {
        $html = '';

        if( $field->has_display_flag( 'private' ) ) {
            $html .= sprintf( '<span class="tag %s">%s</span>',
                'private',
                _x( 'Private', 'form-fields admin', 'PDM' ) );
        } else {
            $html .= sprintf( '<span class="tag %s">%s</span>',
                $field->is_required() ? 'required' : 'optional',
                $field->is_required() ? _x( 'Required', 'form-fields admin', 'PDM' ) : _x( 'Optional', 'form-fields admin', 'PDM' ) );

            if ( $field->display_in( 'excerpt' ) ) {
                $html .= sprintf( '<span class="tag in-excerpt" title="%s">%s</span>',
                    _x( 'This field value is shown in the excerpt view of a listing.', 'form-fields admin', 'PDM' ),
                    _x( 'In Excerpt', 'form-fields admin', 'PDM' ) );
            }

            if ( $field->display_in( 'listing' ) ) {
                $html .= sprintf( '<span class="tag in-listing" title="%s">%s</span>',
                    _x( 'This field value is shown in the single view of a listing.', 'form-fields admin', 'PDM' ),
                    _x( 'In Listing', 'form-fields admin', 'PDM' ) );
            }
        }

        return $html;
    }

}

class PDL_FormFieldsAdmin {

    public function __construct() {
        $this->api = pdl_formfields_api();
        $this->admin = pdl()->admin;

        add_action('admin_init', array($this, 'check_for_required_fields'));
    }

   /* Required fields check. */
    public function check_for_required_fields() {
        global $pdl;

        if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'pdl_admin_formfields' &&
             isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'createrequired' ) {
            // do not display the warning inside the page creating the required fields
            return;
        }

        if ( $missing = $pdl->formfields->get_missing_required_fields() ) {
            if (count($missing) > 1) {
                $message = sprintf(_x('<b>Plestar Directory Listing</b> requires fields with the following associations in order to work correctly: <b>%s</b>.', 'admin', 'PDM'), join(', ', $missing));
            } else {
                $message = sprintf(_x('<b>Plestar Directory Listing</b> requires a field with a <b>%s</b> association in order to work correctly.', 'admin', 'PDM'), array_pop( $missing ) );
            }

            $message .= '<br />';
            $message .= _x('You can create these custom fields by yourself inside "Manage Form Fields" or let Plestar Directory Listing do this for you automatically.', 'admin', 'PDM');
            $message .= '<br /><br />';
            $message .= sprintf('<a href="%s">%s</a> | ',
                                admin_url('admin.php?page=pdl_admin_formfields'),
                                _x('Go to "Manage Form Fields"', 'admin', 'PDM'));
            $message .= sprintf('<a href="%s">%s</a>',
                                admin_url('admin.php?page=pdl_admin_formfields&action=createrequired'),
                                _x('Create these required fields for me', 'admin', 'PDM'));

            $this->messages[] = array($message, 'error');
        }
    }

    public function dispatch() {
        $action = pdl_getv($_REQUEST, 'action');
        $_SERVER['REQUEST_URI'] = remove_query_arg(array('action', 'id'), $_SERVER['REQUEST_URI']);

        switch ($action) {
            case 'addfield':
            case 'editfield':
                $this->processFieldForm();
                break;
            case 'deletefield':
                $this->deleteField();
                break;
            case 'fieldup':
            case 'fielddown':
                if ( $field = $this->api->get_field( $_REQUEST['id'] ) ) {
                    $field->reorder( $action == 'fieldup' ? 1 : -1 );
                }
                $this->fieldsTable();
                break;
            case 'previewform':
                $this->previewForm();
                break;
            case 'createrequired':
                $this->createRequiredFields();
                break;
            case 'updatetags':
                $this->update_field_tags();
                break;
            default:
                $this->fieldsTable();
                break;
        }
    }

    public static function admin_menu_cb() {
        $instance = new PDL_FormFieldsAdmin();
        $instance->dispatch();
    }

    public static function _render_field_settings() {
        $api = pdl_formfields_api();

        $association = pdl_getv( $_REQUEST, 'association', false );
        $field_type = $api->get_field_type( pdl_getv( $_REQUEST, 'field_type', false ) );
        $field_id = pdl_getv( $_REQUEST, 'field_id', 0 );

        $response = array( 'ok' => false, 'html' => '' );

        if ( $field_type && in_array( $association, $field_type->get_supported_associations(), true ) ) {
            $field = $api->get_field( $field_id );

            $field_settings = '';
            $field_settings .= $field_type->render_field_settings( $field, $association );

            ob_start();
            do_action_ref_array( 'pdl_form_field_settings', array( &$field, $association ) );
            $field_settings .= ob_get_contents();
            ob_end_clean();

            $response['ok'] = true;
            $response['html'] = $field_settings;
        }

        echo json_encode( $response );
        exit;
    }

    /* preview form */
    private function previewForm() {
        require_once( PDL_INC . 'views/submit_listing.php' );

        $html  = '';
        $html .= pdl_admin_header(_x('Form Preview', 'form-fields admin', 'PDM'), 'formfields-preview', array(
            array(_x('â†� Return to "Manage Form Fields"', 'form-fields admin', 'PDM'), esc_url(remove_query_arg('action')))
        ));
        $html .= '<div id="pdl-listing-form-preview">';
        $html .= pdl_admin_notices();
        $html .= pdl_capture_action( 'pdl_admin_form_fields_before_preview' );

        require_once( PDL_INC . 'helpers/class-dummy-listing.php' );
        $listing = new PDL__Dummy_Listing();
        do_action( 'pdl_preview_form_setup_listing', $listing );

        $html .= PDL__Views__Submit_Listing::preview_form( $listing );

        $html .= pdl_capture_action( 'pdl_admin_form_fields_after_preview' );
        $html .= '</div>';
        $html .= pdl_admin_footer();

        echo $html;
    }

    /* field list */
    private function fieldsTable() {
        $table = new PDL_FormFieldsTable();
        $table->prepare_items();

        pdl_render_page(PDL_PATH . 'templates/admin/form-fields.tpl.php',
                          array('table' => $table),
                          true);
    }

    private function processFieldForm() {
        $api = PDL_FormFields::instance();


        if ( isset( $_POST['field'] ) ) {
            $field = new PDL_FormField( stripslashes_deep( $_POST['field'] ) );
            $res = $field->save();

            if ( !is_wp_error( $res ) ) {
                $this->admin->messages[] = _x( 'Form fields updated.', 'form-fields admin', 'PDM' );
                return $this->fieldsTable();
            } else {
                $errmsg = '';

                foreach ( $res->get_error_messages() as $err ) {
                    $errmsg .= sprintf( '&#149; %s<br />', $err );
                }

                $this->admin->messages[] = array( $errmsg, 'error' );
            }
        } else {
            $field = isset( $_GET['id'] ) ? PDL_FormField::get( $_GET['id'] ) : new PDL_FormField( array( 'display_flags' => array( 'excerpt', 'search', 'listing' ) ) );
        }

        if ( ! pdl_get_option( 'override-email-blocking' ) && $field->has_validator( 'email' ) && ( $field->display_in( 'excerpt' ) || $field->display_in( 'listing' ) )  ) {
            $msg = _x( '<b>Important</b>: Since the "<a>Display email address fields publicly?</a>" setting is disabled, display settings below will not be honored and this field will not be displayed on the frontend. If you want e-mail addresses to show on the frontend, you can <a>enable public display of e-mails</a>.',
                       'form-fields admin',
                       'PDM' );
            $msg = str_replace( '<a>',
                                '<a href="' . admin_url( 'admin.php?page=pdl_settings&tab=email' ) . '">',
                                $msg );
            pdl_admin_message( $msg, 'error' );
        }

        pdl_render_page( PDL_PATH . 'templates/admin/form-fields-addoredit.tpl.php',
                           array(
                            'field' => $field,
                            'field_associations' => $api->get_associations_with_flags(),
                            'field_types' => $api->get_field_types(),
                            'validators' => $api->get_validators(),
                            'association_field_types' => $api->get_association_field_types()
                           ),
                           true );
    }

    private function deleteField() {
        global $wpdb;

        $field = PDL_FormField::get( $_REQUEST['id'] );

        if ( !$field || $field->has_behavior_flag( 'no-delete' ) )
            return;

        if ( isset( $_POST['doit'] ) ) {
            $ret = $field->delete();

            if ( is_wp_error( $ret ) ) {
                $this->admin->messages[] = array( $ret->get_error_message(), 'error' );
            } else {
                $this->admin->messages[] = _x( 'Field deleted.', 'form-fields admin', 'PDM' );

                $quick_search_fields = pdl_get_option( 'quick-search-fields' );
                $quick_search_fields = array_diff( $quick_search_fields, array( $_REQUEST['id'] ) );

                pdl_set_option( 'quick-search-fields', $quick_search_fields );
            }

            return $this->fieldsTable();
        }

        pdl_render_page( PDL_PATH . 'templates/admin/form-fields-confirm-delete.tpl.php',
                           array( 'field' => $field ),
                           true );
    }

    private function createRequiredFields() {
        global $pdl;

        if ( $missing = $pdl->formfields->get_missing_required_fields() ) {
            $pdl->formfields->create_default_fields( $missing );
            $this->admin->messages[] = _x('Required fields created successfully.', 'form-fields admin', 'PDM');
        }

        return $this->fieldsTable();
    }

    private function update_field_tags() {
        global $pdl;

        // Before starting, check if we need to update tags.
        $pdl->formfields->maybe_correct_tags();

        $special_tags = array(
            'title' => _x( 'Title', 'form-fields admin', 'PDM' ),
            'category' => _x( 'Category', 'form-fields admin', 'PDM' ),
            'excerpt' => _x( 'Excerpt', 'form-fields admin', 'PDM' ),
            'content' => _x( 'Content', 'form-fields admin', 'PDM' ),
            'tags' => _x( 'Tags', 'form-fields admin', 'PDM' ),
            'address' => _x( 'Address', 'form-fields admin', 'PDM' ),
            'city' => _x( 'City', 'form-fields admin', 'PDM' ),
            'state' => _x( 'State', 'form-fields admin', 'PDM' ),
            'country' => _x( 'Country', 'form-fields admin', 'PDM' ),
            'zip' => _x( 'ZIP Code', 'form-fields admin', 'PDM' ),
            'fax' => _x( 'FAX Number', 'form-fields admin', 'PDM' ),
            'phone' => _x( 'Phone Number', 'form-fields admin', 'PDM' ),
            'ratings' => _x( 'Ratings Field', 'form-fields admin', 'PDM' ),
            'twitter' => _x( 'Twitter', 'form-fields admin', 'PDM' ),
            'website' => _x( 'Website', 'form-fields admin', 'PDM' )
        );
        $fixed_tags = array( 'title', 'category', 'excerpt', 'content', 'tags', 'ratings' );
        $field_tags = array();

        if ( isset( $_POST['field_tags'] ) ) {
            global $wpdb;

            $posted = $_POST['field_tags'];

            foreach ( $posted as $tag => $field_id ) {
                if ( in_array( $tag, $fixed_tags, true ) )
                    continue;

                $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}pdl_form_fields SET tag = %s WHERE tag = %s",
                                              '', $tag ) );
                $wpdb->query ($wpdb->prepare( "UPDATE {$wpdb->prefix}pdl_form_fields SET tag = %s WHERE id = %d",
                                              $tag,
                                              $field_id ) );
            }

            pdl_admin_message( _x( 'Tags updated.', 'form-fields admin', 'PDM' ) );
        }

        $missing_fields = $pdl->themes->missing_suggested_fields( 'label' );

        foreach ( $special_tags as $t => $td ) {
            $f = PDL_Form_Field::find_by_tag( $t );

            $field_tags[] = array( 'tag' => $t,
                                   'description' => $td,
                                   'field_id' => ( $f ? $f->get_id() : 0 ),
                                   'fixed' => ( in_array( $t, $fixed_tags, true ) ? true : false ) );
        }

        echo pdl_render_page( PDL_PATH . 'templates/admin/form-fields-tags.tpl.php',
                                array( 'field_tags' => $field_tags, 'missing_fields' => $missing_fields ) );
    }

}
