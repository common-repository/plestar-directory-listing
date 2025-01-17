<?php
if ( ! class_exists( 'PDL_CategoryFormInputWalker' ) )
    require_once ( PDL_PATH . 'includes/helpers/class-category-form-input-walker.php' );


class PDL_FieldTypes_Checkbox extends PDL_Form_Field_Type {

    public function __construct() {
        parent::__construct( _x('Checkbox', 'form-fields api', 'PDM') );
    }

    public function get_id() {
        return 'checkbox';
    }

    public function render_field_inner( &$field, $value, $context, &$extra=null, $field_settings = array() ) {
        $options = $field->data( 'options' ) ? $field->data( 'options') : array();

        if ( $field->get_association() == 'tags' && !$options ) {
            $tags = get_terms( PDL_TAGS_TAX, array( 'hide_empty' => false, 'fields' => 'names' ) );
            $options = array_combine( $tags, $tags );
        } elseif ( $field->get_association() == 'category' ) {
            $html = wp_list_categories( array(
                'taxonomy' => PDL_CATEGORY_TAX,
                'orderby' => pdl_get_option( 'categories-order-by' ),
                'order' => pdl_get_option( 'categories-sort' ),
                'hide_empty' => 0,
                'echo' => 0,
                'depth' => 0,
                'walker' => new PDL_CategoryFormInputWalker( 'checkbox', $value, $field ),
                'show_option_none' => '',
                'show_option_all' => '1' == $field->data( 'allow_select_all' ) ? _x( 'Select all', 'checkbox form field', 'PDM' ) : '',
                'title_li' => '',
            ) );

            return $html;
        }

        $field_name = 'listingfields[' . $field->get_id() . '][]';

        $html = sprintf( '<input type="hidden" name="%s" value="" />', $field_name );

        $i = 1;
        foreach ( $options as $option_key => $label ) {
            $css_classes = array();

            $css_classes[] = 'pdl-inner-field-option';
            $css_classes[] = 'pdl-inner-field-option-' . PDL_Form_Field_Type::normalize_name( $label );

            // For backwards compat.
            $css_classes[] = 'pdl-inner-checkbox';
            $css_classes[] = 'pdl-inner-checkbox-' . $i;
            $css_classes[] = 'pdl-inner-checkbox-' . PDL_Form_Field_Type::normalize_name( $label );

            $html .= sprintf( '<div class="%s"><label><input type="checkbox" name="%s" value="%s" %s/> %s</label></div>',
                              implode( ' ', $css_classes ),
                              $field_name,
                              $option_key,
                              in_array( $option_key, is_array( $value ) ? $value : array( $value ) ) ? 'checked="checked"' : '',
                              esc_attr( $label ) );

            $i++;
        }

        if ( '1' == $field->data( 'allow_select_all' ) ) {
            $html .= sprintf( '<div class="pdl-inner-field-option pdl-inner-field-option-select_all"><label><input type="checkbox" name="%s" value="%s"/> %s</label></div>',
                'checkbox_select_all['. $field->get_id() .']',
                'select_all-' . $field->get_id(),
                _x( 'Select All', 'form-fields admin', 'PDM' ) );
        }

        return $html;
    }

    public function get_supported_associations() {
        return array( 'category', 'tags', 'meta' );
    }

    public function render_field_settings( &$field=null, $association=null ) {
        if ( $association != 'meta' && $association != 'tags' )
            return '';

        $settings = array();

        $settings['options'][] = _x( 'Field Options (for select lists, radio buttons and checkboxes).', 'form-fields admin', 'PDM' ) . '<span class="description">(required)</span>';

        $content  = '<span class="description">One option per line</span><br />';
        $content .= '<textarea name="field[x_options]" cols="50" rows="2">';

        if ( $field && $field->data( 'options' ) )
            $content .= implode( "\n", $field->data( 'options' ) );
        $content .= '</textarea>';

        $settings['options'][] = $content;

        $settings['select_all'][] = _x( 'Include "Select all"?', 'form-fields admin', 'PDM' );

        $content = '<label>';
        $content .= '<input name="field[allow_select_all]" value="1" type="checkbox" ' .  ( ( $field && '1' == $field->data( 'allow_select_all' ) ) ? 'checked="checked"': '' ) . '/>';
        $content .= _x( 'Display "Select all" option among options above.', 'form-fields admin', 'PDM' );
        $content .= '</label>';

        $settings['select_all'][] = $content;

        return self::render_admin_settings( $settings );
    }

    public function process_field_settings( &$field ) {
        if ( !array_key_exists( 'x_options', $_POST['field'] ) )
            return;

        $options = stripslashes( trim( $_POST['field']['x_options'] ) );

        if ( !$options && $field->get_association() != 'tags' )
            return new WP_Error( 'pdl-invalid-settings', _x( 'Field list of options is required.', 'form-fields admin', 'PDM' ) );

        $field->set_data( 'options', $options ? array_map( 'trim', explode( "\n", $options ) ) : array() );
        $field->set_data( 'allow_select_all', array_key_exists( 'allow_select_all', $_POST['field'] ) ? $_POST['field']['allow_select_all'] : '' );
    }

    public function store_field_value( &$field, $post_id, $value ) {
        if ( $field->get_association() == 'meta' ) {
            if ( ! is_array( $value ) ) {
                $value = array( $value );
            }

            $value =  implode( "\t", array_filter( $value, 'strlen' ) );
        }

        parent::store_field_value( $field, $post_id, $value );
    }

    public function get_field_value( &$field, $post_id ) {
        $value = parent::get_field_value( $field, $post_id );
        $value = empty( $value ) ? array() : $value;

        if ( is_string( $value ) )
            return explode( "\t", $value );

        return $value;
    }

    public function get_field_html_value( &$field, $post_id ) {
        if ( $field->get_association() == 'meta' ) {
            return esc_attr( implode( ', ', $field->value( $post_id ) ) );
        }

        return parent::get_field_html_value( $field, $post_id );
    }

    public function get_field_plain_value( &$field, $post_id ) {
        $value = $field->value( $post_id );

        if ( $field->get_association() == 'category' || $field->get_association() == 'tags' ) {
            $term_names = get_terms( $field->get_association() == 'category' ? PDL_CATEGORY_TAX : PDL_TAGS_TAX,
                                     array( 'include' => $value, 'hide_empty' => 0, 'fields' => 'names' ) );

            return join( ', ', $term_names );
        } elseif ( $field->get_association() == 'meta' ) {
            return esc_attr( implode( ', ', $value ) );
        }

        return strval( $value );
    }

    /**
     * @since 3.4.1
     */
    public function get_field_csv_value( &$field, $post_id ) {
        if ( 'meta' != $field->get_association() )
            return $this->get_field_plain_value( $field, $post_id );

        $value = $field->value( $post_id );
        return esc_attr( implode( ',', $value ) );
    }

    /**
     * @since 3.4.1
     */
    public function convert_csv_input( &$field, $input = '', $import_settings = array() ) {
        if ( 'meta' != $field->get_association() )
            return $this->convert_input( $field, $input );

        if ( ! $input )
            return array();

        return explode( ',', $input );
    }

    /**
     * @since 5.0
     */
    public function configure_search( &$field, $query, &$search ) {
        global $wpdb;

        if ( 'meta' != $field->get_association() )
            return false;

        $query = array_map( 'preg_quote', array_diff( is_array( $query ) ? $query : array( $query ), array( -1, '' ) ) );

        if ( ! $query )
            return array();

        $search_res = array();
        list( $alias, $reused ) = $search->join_alias( $wpdb->postmeta, false );

        $search_res['join'] = $wpdb->prepare(
            " LEFT JOIN {$wpdb->postmeta} AS {$alias} ON ( {$wpdb->posts}.ID = {$alias}.post_id AND {$alias}.meta_key = %s )",
            "_pdl[fields][" . $field->get_id() . "]"
        );

        $pattern = '(' . implode('|', $query) . '){1}([tab]{0,1})';
        $search_res['where'] = $wpdb->prepare( "{$alias}.meta_value REGEXP %s", $pattern );

        return $search_res;
    }

}

