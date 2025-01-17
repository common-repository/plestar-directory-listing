<?php

class PDL_FieldTypes_Select extends PDL_Form_Field_Type {

    private $multiselect = false;

    public function __construct() {
        parent::__construct( _x('Select List', 'form-fields api', 'PDM') );
    }

    public function get_id() {
        return 'select';
    }

    public function set_multiple( $val ) {
        $this->multiselect = (bool) $val;
    }

    public function is_multiple() {
        return $this->multiselect;
    }

    public function convert_input( &$field, $input ) {
        if ( is_null( $input ) || '' == $input ) {
            $input = array();
        }

        $res = is_array( $input ) ? $input : array( $input );

        if ( $field->get_association() == 'category' ) {
            $res = array_map( 'intval', $res );
        }

        return $res;
    }

    /**
     * @since 3.4.1
     */
    public function convert_csv_input( &$field, $input = '', $import_settings = array() ) {
        if ( 'tags' == $field->get_association() ) {
            $input = str_replace( ';', ',', $input );
            return explode( ',', $input );
        } elseif ( 'meta' != $field->get_association() ) {
            return $this->convert_input( $field, $input );
        }

        if ( ! $input )
            return array();

        if ( ! $this->is_multiple() )
            return array( str_replace( ',', '', $input ) );

        return explode( ',', $input );
    }

    public function render_field_inner( &$field, $value, $context, &$extra=null, $field_settings = array() ) {
        $options = $field->data( 'options' ) ? $field->data( 'options' ) : array();
        $value = is_array( $value ) ? $value : array( $value );

        $html = '';

        if ( $field->get_association() == 'tags' && !$options ) {
            $tags = get_terms( PDL_TAGS_TAX, array( 'hide_empty' => false, 'fields' => 'names' ) );
            $options = array_combine( $tags, $tags );
        }

        $size = $field->data( 'size', 4 );

        if ( $field->get_association() == 'category' ) {
            $args = array(
                'taxonomy' => $field->get_association() == 'tags' ? PDL_TAGS_TAX : PDL_CATEGORY_TAX,
                'show_option_none' => null,
                'orderby' => pdl_get_option( 'categories-order-by' ),
                'selected' => ( $this->is_multiple() ? null : ( $value ? $value[0] : null ) ),
                'order' => pdl_get_option('categories-sort' ),
                'hide_empty' => $context == 'search' && pdl_get_option( 'hide-empty-categories' ) ? 1 : 0,
                'hierarchical' => 1,
                'echo' => 0,
                'id' => 'pdl-field-' . $field->get_id(),
                'name' => 'listingfields[' . $field->get_id() . ']',
                'class' => 'pdl-js-select2'
            );

            if ( ( 'submit' == $context || 'search' == $context ) && ! $this->is_multiple() ) {
                $args['show_option_none'] = _x( '-- Choose One --', 'form-fields-api category-select', 'PDM' );

                if ( 'submit' == $context ) {
                    $args['option_none_value'] = '';
                }

                if ( 1 == wp_count_terms( PDL_CATEGORY_TAX, array( 'hide_empty' => false ) ) ) {
                    $terms = get_terms( array( 'taxonomy' => PDL_CATEGORY_TAX, 'hide_empty' => false ) );
                    $term  = reset( $terms );

                    $args['selected'] = $term->term_id;
                    $args['show_option_none'] = false;
                    $this->set_multiple( false );
                }

            } else if ( 'search' == $context && $this->is_multiple() ) {
                $args['show_option_none'] = _x( '-- Choose Terms --', 'form-fields-api category-select', 'PDM' );
            }

            $args = apply_filters( 'pdl_field_type_select_categories_args', $args, $field, $value, $context, $extra, $field_settings );

            $html .= wp_dropdown_categories( $args );

            if ( $this->is_multiple() ) {
                $html = preg_replace(
                    "/\\<select(.*)name=('|\")(.*)('|\")(.*)\\>/uiUs",
                    sprintf( "<select name=\"$3[]\" multiple=\"multiple\" $1 $5 size=\"%d\">", $size ),
                    $html
                );

                if ($value) {
                    foreach ( $value as $catid ) {
                        $html = preg_replace( "/\\<option(.*)value=('|\"){$catid}('|\")(.*)\\>/uiU",
                            "<option value=\"{$catid}\" selected=\"selected\" $1 $4>",
                            $html );
                    }
                }
            }

            if ( 'search' == $context && $this->is_multiple() ) {
                // Disable "Choose Terms".
                $html = preg_replace( "/\\<option(.*)value=('|\")-1('|\")(.*)\\>/uiU",
                    "<option value=\"-1\" disabled=\"disabled\" $1 $4>",
                    $html );
            }
        } else {
            $html .= sprintf( '<select id="%s" name="%s" %s class="%s %s" %s>',
                              'pdl-field-' . $field->get_id(),
                              'listingfields[' . $field->get_id() . ']' . ( $this->is_multiple() ? '[]' : '' ),
                              $this->is_multiple() ? 'multiple="multiple"' : '',
                              'inselect',
                              $field->is_required() ? 'required' : '',
                              $this->is_multiple() ? sprintf( 'size="%d"', $field->data( 'size', 4 ) ) : ''
            );

            if ( $field->data( 'empty_on_search' ) && $context == 'search' ) {
                $html .= sprintf( '<option value="-1">%s</option>',
                                  _x( '-- Choose One --', 'form-fields-api category-select', 'PDM' ) );
            }

            $show_empty_option = $field->data( 'show_empty_option', null );

            if ( is_null( $show_empty_option ) ) {
                $show_empty_option = ! $field->has_validator( 'required' );
            }

            if ( $show_empty_option ) {
                $default_label = _x( '— None —', 'form-fields-api select', 'PDM' );
                $empty_option_label = $field->data( 'empty_option_label', $default_label );
                $html .= '<option value="">' . $empty_option_label . '</option>';
            }

            foreach ( $options as $option => $label ) {
                $option_data = array( 'label' => $label,
                                      'value' => esc_attr( $option ),
                                      'attributes' => array() );

                if ( in_array( $option, $value ) )
                    $option_data['attributes']['selected'] = 'selected';

                $option_data = apply_filters( 'pdl_form_field_select_option', $option_data, $field );

                $html .= sprintf( '<option value="%s" class="%s" %s>%s</option>',
                                  esc_attr( $option_data['value'] ),
                                  'pdl-inner-field-option pdl-inner-field-option-' . PDL_Form_Field_Type::normalize_name( $option_data['label'] ),
                                  $this->html_attributes( $option_data['attributes'], array( 'value', 'class' ) ),
                                  esc_attr( $option_data['label'] ) );
            }

            $html .= '</select>';
        }

        return $html;
    }

    public function get_supported_associations() {
        return array( 'category', 'tags', 'meta', 'region' );
    }

    public function render_field_settings( &$field=null, $association=null ) {
        return self::render_admin_settings( $this->get_field_settings( $field, $association ) );
    }

    protected function get_field_settings( $field=null, $association=null ) {
        if ( $association != 'meta' && $association != 'tags' ) {
            return array();
        }

        $settings = array();

        $settings['options'][] = _x( 'Field Options (for select lists, radio buttons and checkboxes).', 'form-fields admin', 'PDM' ) . '<span class="description">(required)</span>';

        $content  = '<span class="description">One option per line</span><br />';
        $content .= '<textarea name="field[x_options]" cols="50" rows="2">';

        if ( $field && $field->data( 'options' ) )
            $content .= implode( "\n", $field->data( 'options' ) );
        $content .= '</textarea>';

        $settings['options'][] = $content;

        $settings['empty_on_search'][] = _x('Allow empty selection on search?', 'form-fields admin', 'PDM');

        $content  = '<span class="description">Empty search selection means users can make this field optional in searching. Turn it off if the field must always be searched on.</span><br />';
        $content .= '<input type="checkbox" value="1" name="field[x_empty_on_search]" ' . ( !$field ? ' checked="checked"' : ($field->data( 'empty_on_search' ) ? ' checked="checked"' : '') ) . ' />';

        $settings['empty_on_search'][] = $content;

        return $settings;
    }

    public function process_field_settings( &$field ) {
        if ( !array_key_exists( 'x_options', $_POST['field'] ) )
            return;

        $options = stripslashes( trim( $_POST['field']['x_options'] ) );

        if ( !$options && $field->get_association() != 'tags' )
            return new WP_Error( 'pdl-invalid-settings', _x( 'Field list of options is required.', 'form-fields admin', 'PDM' ) );

        $field->set_data( 'options', $options ? array_map( 'trim', explode( "\n", $options ) ) : array() );

        if ( array_key_exists( 'x_empty_on_search', $_POST['field'] ) ) {
            $empty_on_search = (bool) intval( $_POST['field']['x_empty_on_search'] );
            $field->set_data( 'empty_on_search', $empty_on_search );
        }
    }

    public function store_field_value( &$field, $post_id, $value ) {
        if ( $this->is_multiple() && $field->get_association() == 'meta' ) {
            if ( $value )
                $value =  implode( "\t", is_array( $value ) ? $value : array( $value ) );
        } elseif ( 'meta' == $field->get_association() ) {
            $value = is_array( $value ) ? ( ! empty( $value ) ? $value[0] : '' ) : $value;
        }

        parent::store_field_value( $field, $post_id, $value );
    }

    public function get_field_value( &$field, $post_id ) {
        $value = parent::get_field_value( $field, $post_id );

        if ( $this->is_multiple() && $field->get_association() == 'meta' ) {
            if ( !empty( $value ) )
                return explode( "\t", $value );
        }

        if ( !$value )
            return array();

        $value = is_array( $value ) ? $value : array( $value );
        return $value;
    }

    public function get_field_html_value( &$field, $post_id ) {
        if ( $field->get_association() == 'meta' ) {
            $value = $field->value( $post_id );

            return esc_attr( implode( ', ', $value ) );
        }

        return parent::get_field_html_value( $field, $post_id );
    }

    public function get_field_plain_value( &$field, $post_id ) {
        $value = $field->value( $post_id );

        if ( ! $value )
            return '';

        if ( $field->get_association() == 'category' ) {
            $args = array( 'include' => $value, 'hide_empty' => 0, 'fields' => 'names' );
            $term_names = get_terms( $field->get_association() == 'category' ? PDL_CATEGORY_TAX : PDL_TAGS_TAX,
                                     $args );
            return join( ', ', $term_names );
        } elseif ( $field->get_association() == 'tags' ) {
            return join( ', ', $value );
        } elseif ( $field->get_association() == 'meta' ) {
            return esc_attr( implode( ', ', $value ) );
        }

        return $value;
    }

    /**
     * @since 3.4.1
     */
    public function get_field_csv_value( &$field, $post_id ) {
        if ( 'meta' != $field->get_association() )
            return $field->plain_value( $post_id );

        $value = $field->value( $post_id );
        return esc_attr( implode( ',', $value ) );
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
