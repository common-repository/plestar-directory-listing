<?php
/**
 * Form fields API.
 */

if (!class_exists('PDL_FormFields')) {

require_once( PDL_PATH . 'includes/fields/class-form-field.php' );
require_once( PDL_PATH . 'includes/fields/form-fields-types.php' );


class PDL_FormFields {

    private $associations = array();
    private $association_flags = array();
    private $association_field_types = array();

    private $field_types = array();

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct() {
        // register core associations
        $this->register_association( 'title', _x( 'Post Title', 'form-fields api', 'PDM' ), array( 'required', 'unique' ) );
        $this->register_association( 'content', _x( 'Post Content', 'form-fields api', 'PDM' ), array( 'required', 'unique', 'optional' ) );
        $this->register_association( 'excerpt', _x( 'Post Excerpt', 'form-fields api', 'PDM' ), array( 'unique' ) );
        $this->register_association( 'category', _x( 'Post Category', 'form-fields api', 'PDM' ), array( 'required', 'unique' ) );
        $this->register_association( 'tags', _x( 'Post Tags', 'form-fields api', 'PDM' ), array( 'unique' ) );
        $this->register_association( 'meta', _x( 'Post Metadata', 'form-fields api', 'PDM' ) );

        $this->register_association( 'custom', _x('Custom', 'form-fields api', 'PDM'), array( 'private' ) );

        // register core field types
        $this->register_field_type( 'PDL_FieldTypes_TextField', 'textfield' );
        $this->register_field_type( 'PDL_FieldTypes_Select', 'select' );
        $this->register_field_type( 'PDL_FieldTypes_URL', 'url' );
        $this->register_field_type( 'PDL_FieldTypes_TextArea', 'textarea' );
        $this->register_field_type( 'PDL_FieldTypes_RadioButton', 'radio' );
        $this->register_field_type( 'PDL_FieldTypes_MultiSelect', 'multiselect' );
        $this->register_field_type( 'PDL_FieldTypes_Checkbox', 'checkbox' );
        $this->register_field_type( 'PDL_FieldTypes_Twitter', 'social-twitter' );
        $this->register_field_type( 'PDL_FieldTypes_Facebook', 'social-facebook' );
        $this->register_field_type( 'PDL_FieldTypes_LinkedIn', 'social-linkedin' );
        $this->register_field_type( 'PDL_FieldTypes_Image', 'image' );
        $this->register_field_type( 'PDL_FieldTypes_Date', 'date' );
        $this->register_field_type( 'PDL_FieldTypes_Phone_Number' );
    }

    /**
     * Registers a new association within the form fields API.
     * @param string $association association id
     * @param string $name human-readable name
     * @param array $flags association flags
     */
    public function register_association( $association, $name='', $flags=array() ) {
        if ( isset( $this->associations[$association] ) )
            return false;

        $this->associations[ $association ] = $name ? $name : $association;
        $this->association_flags[ $association ] = is_array( $flags ) ? $flags : array( strval( $flags ) );

        if ( !isset( $this->association_field_types[ $association ] ) )
            $this->association_field_types[ $association ] = array();
    }

    /**
     * Returns the known form field associations.
     * @return array associative array with key/name pairs
     */
    public function &get_associations() {
        return $this->associations;
    }

    public function get_association_field_types( $association=null ) {
        if ( $association ) {
            if ( in_array( $association, array_keys( $this->associations ), true ) ) {
                return $this->association_field_types[ $association ];
            } else {
                return null;
            }
        }

        return $this->association_field_types;
    }

    public function get_association_flags( $association ) {
        if ( array_key_exists( $association, $this->associations )  )
            return $this->association_flags[ $association ];

        return array();
    }

    /**
     * Returns associations marked with the given flags.
     * @param string|array $flags flags to be checked
     * @param boolean $any if True associations marked with any (and not all) of the flags will also be returned
     * @return array
     */
    public function &get_associations_with_flag( $flags, $any=false ) {
        if ( is_string( $flags ) )
            $flags = array( $flags );

        $res = array();

        foreach ( $this->association_flags as $association => $association_flags ) {
            $intersection = array_intersect( $flags, $association_flags );

            if ( ( $any && ( count( $intersection ) > 0 ) ) || ( !$any && ( count( $intersection ) == count( $flags ) )  ) )
                $res[] = $association;
        }

        return $res;
    }

    /**
     * Get associations with their flags at the same time.
     *
     * @since 3.4
     */
    public function &get_associations_with_flags() {
        $res = array();

        foreach ( $this->associations as $assoc_id => $assoc_label ) {
            $flags = $this->association_flags[ $assoc_id ];
            $res[ $assoc_id ] = (object) array( 'id' => $assoc_id, 'label' => $assoc_label, 'flags' => $flags );
        }

        return $res;
    }

    public function &get_required_field_associations() {
        return $this->get_associations_with_flag( 'required' );
    }

    public function &get_field_type( $field_type ) {
        $field_type_obj = pdl_getv( $this->field_types, $field_type, null );
        return $field_type_obj;
    }

    public function &get_field_types() {
        return $this->field_types;
    }

    public function get_validators() {
        $validators = PDL_FieldValidation::instance()->get_validators();
        return $validators;
    }

    public function register_field_type( $field_type_class, $alias=null ) {
        $field_type = new $field_type_class();

        if ( ! $alias )
            $alias = $field_type->get_id();

        if ( ! $alias )
            $alias = $field_type_class;

        $this->field_types[ $alias ? $alias : $field_type_class ] = $field_type;

        foreach ( $field_type->get_supported_associations() as $association ) {
            $this->association_field_types[ $association ] = array_merge( isset( $this->association_field_types[ $association ] ) ? $this->association_field_types[ $association ] : array(), array( $alias ? $alias : $field_type_class ) );
        }
    }

    public function &get_field( $id=0 ) {
        $field = PDL_FormField::get( $id );
        return $field;
    }

    public function &get_fields( $lightweight = false ) {
        global $wpdb;

        if ( $lightweight ) {
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}pdl_form_fields ORDER BY weight DESC" );
            return $results;
        }

        $res = array();
        $field_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->prefix}pdl_form_fields ORDER BY weight DESC" );

        foreach ( $field_ids as $field_id ) {
            if ( $field = PDL_FormField::get( $field_id ) )
                $res[] = $field;
        }

        return $res;
    }

    public function &find_fields( $args=array(), $one=false ) {
        global $wpdb;
        $res = array();

        $args = wp_parse_args( $args, array(
            'association' => null,
            'field_type' => null,
            'validators' => null,
            'display_flags' => null,
            'output' => 'object',
            'unique' => false
        ) );

        if ( $one == true )
            $args['unique'] = true;

        extract( $args );

        $validators = $validators ? ( !is_array( $validators ) ? array( $validators ) : $validators ) : array();
        $display_flags = $display_flags ? ( !is_array( $display_flags ) ? array( $display_flags ) : $display_flags ) : array();

        $where = '';
        if ( $args['association'] ) {
            $associations_in = array();
            $associations_not_in = array();

            $association = !is_array( $association) ? explode( ',', $association ) : $association;

            foreach ( $association as &$assoc ) {
                if ( pdl_starts_with( $assoc, '-' ) ) {
                    $associations_not_in[] = substr( $assoc, 1 );
                } else {
                    $associations_in[] = $assoc;
                }
            }

            if ( $associations_in ) {
                $where .= ' AND ( association IN ( \'' . implode( '\',\'', $associations_in)  . '\' ) ) ';
            }

            if ( $associations_not_in ) {
                $where .= ' AND ( association NOT IN ( \'' . implode( '\',\'', $associations_not_in)  . '\' ) ) ';
            }

            // $where .= $wpdb->prepare( " AND ( association = %s ) ", $args['association'] );
        }

        if ( $args['field_type'] ) {
            $field_types_in = array();
            $field_types_not_in = array();

            $field_type = ! is_array( $field_type ) ? array( $field_type ) : $field_type;

            foreach ( $field_type as $f ) {
                if ( pdl_starts_with( $f, '-' ) ) {
                    $field_types_not_in[] = substr( $f, 1 );
                } else {
                    $field_types_in[] = $f;
                }
            }

            if ( $field_types_in )
                $where .= ' AND ( field_type IN ( \'' . implode( '\',\'', $field_types_in )  . '\' ) ) ';

            if ( $field_types_not_in )
                $where .= ' AND ( field_type NOT IN ( \'' . implode( '\',\'', $field_types_not_in )  . '\' ) ) ';
        }

        foreach ( $display_flags as $f ) {
            if ( substr($f, 0, 1) == '-' )
                $where .= $wpdb->prepare( " AND ( display_flags IS NULL OR display_flags NOT LIKE '%%%s%%' )", substr( $f, 1 ) );
            else
                $where .= $wpdb->prepare( " AND ( display_flags LIKE '%%%s%%' )", $f );
        }

        foreach ( $validators as $v ) {
            if ( substr($v, 0, 1) == '-' )
                $where .= $wpdb->prepare( " AND ( validators IS NULL OR validators NOT LIKE '%%%s%%' )", substr( $v, 1 ) );
            else
                $where .= $wpdb->prepare( " AND ( validators LIKE '%%%s%%' )", $v );
        }

        if ( $where )
            $sql = "SELECT id FROM {$wpdb->prefix}pdl_form_fields WHERE 1=1 {$where} ORDER BY weight DESC";
        else
            $sql = "SELECT id FROM {$wpdb->prefix}pdl_form_fields ORDER BY weight DESC";

        $ids = $wpdb->get_col( $sql );

        if ( 'ids' == $output )
            return $ids;

        foreach ( $ids as $id ) {
            if ( $field = PDL_FormField::get( $id ) ) {
                if ( ! in_array( $field->get_association(), array_keys( $this->associations ), true ) )
                    continue;

                $res[] = $field;
            }
        }

        $res = $unique ? ( $res ? $res[0] : null ) : $res;

        return $res;
    }

    public function get_missing_required_fields() {
        global $wpdb;

        $missing = $this->get_required_field_associations();

        $sql_in = '(\'' . implode( '\',\'', $missing ) . '\')';
        $res = $wpdb->get_col( "SELECT association FROM {$wpdb->prefix}pdl_form_fields WHERE association IN {$sql_in} GROUP BY association" );

        return array_diff( $missing, $res );
    }

    /**
     * @since 3.6.9
     */
    public function get_default_fields( $id = '' ) {
        $default_fields = array(
            'title' => array( 'label' => __('Directory Name', 'PDM'), 'field_type' => 'textfield', 'association' => 'title', 'weight' => 9,
                              'validators' => array( 'required' ), 'display_flags' => array( 'excerpt', 'listing', 'search' ), 'tag' => 'title' ),
            'category' => array( 'label' => __('Directory Genre', 'PDM'), 'field_type' => 'select', 'association' => 'category', 'weight' => 8,
                                 'validators' => array( 'required' ), 'display_flags' => array( 'excerpt', 'listing', 'search' ), 'tag' => 'category' ),
            'excerpt' => array( 'label' => __('Short Directory Description', 'PDM'), 'field_type' => 'textarea', 'association' => 'excerpt', 'weight' => 7,
                                'display_flags' => array( 'excerpt', 'listing', 'search' ), 'tag' => 'excerpt' ),
            'content' => array( 'label' => __("Long Directory Description","PDM"), 'field_type' => 'textarea', 'association' => 'content', 'weight' => 6,
                                'validators' => array( 'required' ), 'display_flags' => array( 'excerpt', 'listing', 'search' ), 'tag' => 'content' ),
            'website' => array( 'label' => __("Domain Name","PDM"), 'field_type' => 'url', 'association' => 'meta', 'weight' => 5,
                              'validators' => array( 'url' ), 'display_flags' => array( 'excerpt', 'listing', 'search' ), 'tag' => 'website' ),
            'name' => array( 'label' => __("Name","PDM"), 'field_type' => 'textfield', 'association' => 'meta', 'weight' => 9,
                              'display_flags' => array( 'excerpt', 'listing', 'search' ), 'tag' => 'name' ),
            'email' => array( 'label' => __("Email","PDM"), 'field_type' => 'textfield', 'association' => 'meta', 'weight' => 2,
                             'validators' => array( 'email', 'required' ), 'display_flags' => array( 'excerpt', 'listing' ), 'tag' => 'email' ),
        	'name' => array( 'label' => __("Name","PDM"), 'field_type' => 'textfield', 'association' => 'meta', 'weight' => 9,
        				'display_flags' => array( 'excerpt', 'listing', 'search' ), 'tag' => 'name' ),
        		
        );

        if ( $id ) {
            if ( isset( $default_fields[ $id ] ) )
                return $default_fields[ $id ];
            else
                return null;
        }

        return $default_fields;
    }

    public function create_default_fields( $identifiers=array() ) {
        $default_fields = $this->get_default_fields();
        $fields_to_create = $identifiers ? array_intersect_key( $default_fields, array_flip ( $identifiers ) ) : $default_fields;

        foreach ( $fields_to_create as &$f) {
            $field = new PDL_FormField( $f );
            $field->save();
        }
    }

    /**
     * @deprecated since 4.0.
     */
    public function get_short_names( $fieldid=null ) {
        $fields = $this->get_fields();
        $shortnames = array();

        foreach ( $fields as $f )
            $shortnames[ $f->get_id()] = $f->get_shortname();

        if ( $fieldid )
            return isset( $shortnames[ $fieldid ] ) ? $shortnames[ $fieldid ] : null;

        return $shortnames;
    }

    public function _calculate_short_names() {
        $fields = $this->get_fields();
        $names = array();

        foreach ( $fields as $field ) {
            $name = PDL_Form_Field_Type::normalize_name( $field->get_label() );

            if ( $name == 'images' || $name == 'image' || $name == 'username' || $name == 'featured_level' || $name == 'expires_on' || $name == 'sequence_id' || in_array( $name, $names, true ) ) {
                $name = $name . '-' . $field->get_id();
            }

            $names[ $field->get_id() ] = $name;
        }

        update_option( 'pdl-field-short-names', $names );

        return $names;
    }

    public function set_fields_order( $fields_order = array() ) {
        if ( ! $fields_order )
            return false;

        global $wpdb;

        $total = count( $fields_order );

        foreach ( $fields_order as $i => $field_id )
            $wpdb->update( $wpdb->prefix . 'pdl_form_fields',
                           array( 'weight' => ( $total - $i ) ),
                           array( 'id' => $field_id ) );

        return true;
    }

    /**
     * @since 4.0
     */
    public function maybe_correct_tags() {
        $fields = pdl_get_form_fields();

        foreach ( $fields as $f ) {
            if ( $f->get_tag() )
                continue;

            $f->save();
        }
    }
}

/*
 * Validation.
 */

function PDL_ValidationError( $msg, $stop_validation=false ) {
    if ( $stop_validation )
        return new WP_Error( 'pdl-validation-error-stop', $msg );

    return new WP_Error( 'pdl-validation-error', $msg );
}


class PDL_FieldValidation {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Get the set of publicly available validators.
     * @return array associative array with validator name as key and display name as value
     */
    public function get_validators() {
        $validators = array(
            'email' => _x('Email Validator', 'form-fields-api', 'PDM'),
            'url' => _x('URL Validator', 'form-fields-api', 'PDM'),
            'integer_number' => _x('Whole Number Validator', 'form-fields-api', 'PDM'),
            'decimal_number' => _x('Decimal Number Validator', 'form-fields-api', 'PDM'),
            'date_' => _x('Date Validator', 'form-fields-api', 'PDM')
        );

        return $validators;
    }

    public function validate_field( $field, $value, $validator, $args=array() ) {
        $args['field-label'] = is_object( $field ) && $field ? apply_filters( 'pdl_render_field_label', $field->get_label(), $field ) : _x( 'Field', 'form-fields-api validation', 'PDM' );
        $args['field'] = $field;

        return call_user_func( array( $this, $validator ) , $value, $args );
    }

    public function validate_value( $value, $validator, $args=array() ) {
        return !is_wp_error( $this->validate_field( null, $value, $validator, $args ) );
    }

    /* Required validator */
    private function required( $value, $args=array() ) {
        $args = wp_parse_args( $args, array( 'allow_whitespace' => false, 'field' => null ) );

        if ( $args['field'] && $args['field']->get_association() == 'category' ) {
            if ( is_array( $value ) && count( $value ) == 1 && !$value[0] )
                return PDL_ValidationError( sprintf( _x( '%s is required.', 'form-fields-api validation', 'PDM' ), esc_attr( $args['field-label'] ) ) );
        }

        if ( ( $args['field'] && $args['field']->is_empty_value( $value ) ) || !$value || ( is_string( $value ) && !$args['allow_whitespace'] && !trim( $value ) ) )
            return PDL_ValidationError( sprintf( _x( '%s is required.', 'form-fields-api validation', 'PDM' ), esc_attr( $args['field-label'] ) ) );
    }

    /* URL Validator */
    private function url( $value, $args=array() ) {
        if ( is_array( $value ) ) $value = $value[0];

        if ( function_exists( 'filter_var' ) ) {
            if ( !filter_var( $value, FILTER_VALIDATE_URL ) ) {
                return PDL_ValidationError( sprintf( _x( '%s is badly formatted. Valid URL format required. Include http://', 'form-fields-api validation', 'PDM' ), esc_attr( $args['field-label'] ) )  );
            } else {
                return;
            }
        }

        if ( !preg_match( '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $value ) )
            return PDL_ValidationError( sprintf( _x( '%s is badly formatted. Valid URL format required. Include http://', 'form-fields-api validation', 'PDM' ), esc_attr( $args['field-label'] ) )  );
    }

    /* EmailValidator */
    private function email( $value, $args=array() ) {
        $valid = false;

        if ( function_exists( 'filter_var' ) ) {
            $valid = filter_var( $value, FILTER_VALIDATE_EMAIL );
        } else {
            $valid = (bool) preg_match( '/^(?!(?>\x22?(?>\x22\x40|\x5C?[\x00-\x7F])\x22?){255,})(?!(?>\x22?\x5C?[\x00-\x7F]\x22?){65,}@)(?>[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+|(?>\x22(?>[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|\x5C[\x00-\x7F])*\x22))(?>\.(?>[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+|(?>\x22(?>[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|\x5C[\x00-\x7F])*\x22)))*@(?>(?>(?!.*[^.]{64,})(?>(?>xn--)?[a-z0-9]+(?>-[a-z0-9]+)*\.){0,126}(?>xn--)?[a-z0-9]+(?>-[a-z0-9]+)*)|(?:\[(?>(?>IPv6:(?>(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){7})|(?>(?!(?:.*[a-f0-9][:\]]){8,})(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,6})?::(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,6})?)))|(?>(?>IPv6:(?>(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){5}:)|(?>(?!(?:.*[a-f0-9]:){6,})(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,4})?::(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,4}:)?)))?(?>25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])(?>\.(?>25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}))\]))$/isD', $value );
        }

        if ( !$valid )
            return PDL_ValidationError( sprintf( _x( '%s is badly formatted. Valid Email format required.', 'form-fields-api validation', 'PDM' ), esc_attr( $args['field-label'] ) ) );
    }

    /* IntegerNumberValidator */
    private function integer_number( $value, $args=array() ) {
        if ( !ctype_digit( $value ) )
            return PDL_ValidationError( sprintf( _x( '%s must be a number. Decimal values are not allowed.', 'form-fields-api validation', 'PDM' ), esc_attr ( $args['field-label'] ) ) );
    }

    /* DecimalNumberValidator */
    private function decimal_number( $value, $args=array() ) {
        if ( !is_numeric( $value ) )
            return PDL_ValidationError( sprintf( _x( '%s must be a number.', 'form-fields-api validation', 'PDM' ), esc_attr( $args['field-label'] ) ) );
    }

    /* DateValidator */
    private function date_( $value, $args=array() ) {
        $args = wp_parse_args( $args, array( 'format' => 'dd/mm/yyyy', 'messages' => array() ) );
        $format = $args['format'];

        // Normalize separators.
        $format_ = str_replace( array( '/', '.', '-' ), '', $format );
        $value_ = str_replace( array( '/', '.', '-' ), '', $value );

        if ( strlen( $format_ ) != strlen( $value_ ) )
            return PDL_ValidationError( ( ! empty ( $args['messages']['incorrect_format'] ) ) ? $args['messages']['incorrect_format'] : sprintf( _x( '%s must be in the format %s.', 'form-fields-api validation', 'PDM' ), esc_attr( $args['field-label'] ), $format  ) );

        $d = '0'; $m = '0'; $y = '0';

        switch ( $format_ ) {
            case 'ddmmyy':
                $d = substr( $value_, 0, 2 );
                $m = substr( $value_, 2, 2 );
                $y = substr( $value_, 4, 2 );
                break;
            case 'ddmmyyyy':
                $d = substr( $value_, 0, 2 );
                $m = substr( $value_, 2, 2 );
                $y = substr( $value_, 4, 4 );
                break;
            case 'mmddyy':
                $m = substr( $value_, 0, 2 );
                $d = substr( $value_, 2, 2 );
                $y = substr( $value_, 4, 2 );
                break;
            case 'mmddyyyy':
                $m = substr( $value_, 0, 2 );
                $d = substr( $value_, 2, 2 );
                $y = substr( $value_, 4, 4 );
                break;
            case 'yyyymmdd':
                $m = substr( $value_, 4, 2 );
                $d = substr( $value_, 6, 2 );
                $y = substr( $value_, 0, 4 );
                break;
            default:
                break;
        }

        if ( ! ctype_digit( $m ) || ! ctype_digit( $d ) || ! ctype_digit( $y ) || ! checkdate( $m, $d, $y ) )
            return PDL_ValidationError( ( ! empty ( $args['messages']['invalid'] ) ) ? $args['messages']['invalid'] : sprintf( _x( '%s must be a valid date.', 'form-fields-api validation', 'PDM' ), esc_attr( $args['field-label'] ) ) );
    }

    private function any_of( $value, $args=array() ) {
        $args = wp_parse_args( $args, array( 'values' => array(), 'formatter' => create_function( '$x', 'return join(",", $x);' ) ) );
        extract( $args, EXTR_SKIP );

        if ( is_string( $values ) )
            $values = explode( ',', $values );

        if ( !in_array( $value, $values ) )
            return PDL_ValidationError( sprintf( _x( '%s is invalid. Value most be one of %s.', 'form-fields-api validation', 'PDM' ), esc_attr( $args['field-label'] ), call_user_func( $formatter, $values ) ) );
    }

}


}



/**
 * @since 2.3
 * @see PDL_FormFields::find_fields()
 */
function &pdl_get_form_fields( $args=array() ) {
    global $wpdb;
    global $pdl;

    // TODO: This verification was added to prevent an SQL error triggered during
    // the first activation, when Settings_Bootstrap calls get_quicksearch_fields()
    // before the installer is executed for the first time. At that point, the
    // form_fields table does not exists.
    //
    // Please fix. This function shouldn't be called before the installer is executed.
    if ( pdl_table_exists( $wpdb->prefix . 'pdl_form_fields' ) ) {
        $fields = $pdl->formfields->find_fields( $args );
    } else {
        $fields = array();
    }

    if ( ! $fields )
        $fields = array();

    return $fields;
}

/**
 * @since 2.3
 * @see PDL_FormFields::get_field()
 */
function pdl_get_form_field( $id ) {
    global $pdl;
    return $pdl->formfields->get_field( $id );
}

/**
 * Validates a value against a given validator.
 * @param mixed $value
 * @param string $validator one of the registered validators.
 * @param array $args optional arguments to be passed to the validator.
 * @return boolean True if value validates, False otherwise.
 * @since 2.3
 * @see PDL_FieldValidation::validate_value()
 */
function pdl_validate_value( $value, $validator, $args=array() ) {
    $validation = PDL_FieldValidation::instance();
    return $validation->validate_value( $value, $validator, $args );
}

