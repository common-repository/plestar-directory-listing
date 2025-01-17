<?php
if ( ! class_exists( 'PDL_CategoryFormInputWalker' ) )
    require_once ( PDL_PATH . 'includes/helpers/class-category-form-input-walker.php' );


class PDL_FieldTypes_RadioButton extends PDL_Form_Field_Type {

    public function __construct() {
        parent::__construct( _x('Radio button', 'form-fields api', 'PDM') );
    }

    public function get_id() {
        return 'radio';
    }

    public function render_field_inner( &$field, $value, $context, &$extra=null, $field_settings = array() ) {
        $options = $field->data( 'options' ) ? $field->data( 'options' ) : array();

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
                'walker' => new PDL_CategoryFormInputWalker( 'radio', $value, $field ),
                'show_option_none' => '',
                'title_li' => '',
            ) );

            return $html;
        }

        $html = '';
        $i = 1;
        foreach ( $options as $option => $label ) {
            $css_classes = array();
            $css_classes[] = 'pdl-inner-field-option';
            $css_classes[] = 'pdl-inner-field-option-' . PDL_Form_Field_Type::normalize_name( $label );

            // For backwards compat.
            $css_classes[] = 'pdl-inner-radio';
            $css_classes[] = 'pdl-inner-radio-' . $i;
            $css_classes[] = 'pdl-inner-radio-' . PDL_Form_Field_Type::normalize_name( $label );

            $html .= sprintf( '<div class="%s"><label><input type="radio" name="%s" value="%s" %s /> %s</label></div>',
                              implode( ' ', $css_classes ),
                              'listingfields[' . $field->get_id() . ']',
                              $option,
                              $value == $option ? 'checked="checked"' : '',
                              esc_attr( $label )
                            );
            $i++;
        }

        return $html;
    }

    public function get_supported_associations() {
        return array( 'category', 'tags', 'meta' );
    }

    public function render_field_settings( &$field=null, $association=null ) {
            if ( $association != 'meta' && $association != 'tags' )
            return '';

        $label = _x( 'Field Options (for select lists, radio buttons and checkboxes).', 'form-fields admin', 'PDM' ) . '<span class="description">(required)</span>';

        $content  = '<span class="description">One option per line</span><br />';
        $content .= '<textarea name="field[x_options]" cols="50" rows="2">';

        if ( $field && $field->data( 'options' ) )
            $content .= implode( "\n", $field->data( 'options' ) );
        $content .= '</textarea>';

        return self::render_admin_settings( array( array( $label, $content ) ) );
    }

    public function process_field_settings( &$field ) {
        if ( !array_key_exists( 'x_options', $_POST['field'] ) )
            return;

        $options = stripslashes( trim( $_POST['field']['x_options'] ) );

        if ( !$options && $field->get_association() != 'tags' )
            return new WP_Error( 'pdl-invalid-settings', _x( 'Field list of options is required.', 'form-fields admin', 'PDM' ) );

        $field->set_data( 'options', $options ? array_map( 'trim', explode( "\n", $options ) ) : array() );
    }

    public function get_field_value( &$field, $post_id ) {
        $value = parent::get_field_value( $field, $post_id );
        return is_array( $value ) ? $value[0] : $value;
    }

    public function get_field_plain_value( &$field, $post_id ) {
        $value = $field->value( $post_id );

        if ( $field->get_association() == 'category' || $field->get_association() == 'tags' ) {
            $term = get_term( is_array( $value ) ? $value[0] : $value,
                              $field->get_association() == 'category' ? PDL_CATEGORY_TAX : PDL_TAGS_TAX );
            return esc_attr( $term->name );
        }

        return strval( $value );
    }


}

