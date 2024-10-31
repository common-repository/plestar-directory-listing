<?php
/**
 * Search widget.
 * @since 2.1.6
 */
class PDL_SearchWidget extends WP_Widget {

    public function __construct() {
        parent::__construct(false,
                            _x('Plestar Directory Listing - Search', 'widgets', 'PDM'),
                            array('description' => _x('Displays a search form to look for Plestar Directory Listing listings.', 'widgets', 'PDM')));
    }

    public function form($instance) {
        if (isset($instance['title']))
            $title = $instance['title'];
        else
            $title = _x('Search the Plestar Directory Listing', 'widgets', 'PDM');

        echo sprintf('<p><label for="%s">%s</label> <input class="widefat" id="%s" name="%s" type="text" value="%s" /></p>',
                     $this->get_field_id('title'),
                     _x('Title:', 'widgets', 'PDM'),
                     $this->get_field_id('title'),
                     $this->get_field_name('title'),
                     esc_attr($title)
                    );
        echo '<p>';

        echo _x('Form Style:', 'widgets', 'PDM');
        echo '<br/>';
        echo sprintf('<input id="%s" name="%s" type="radio" value="%s" %s/> <label for="%s">%s</label>',
                     $this->get_field_id('use_basic_form'),
                     $this->get_field_name('form_mode'),
                     'basic', 
                     pdl_getv($instance, 'form_mode', 'basic') == 'basic' ? 'checked="checked"' : '',
                     $this->get_field_id('use_basic_form'),                     
                    _x('Basic', 'widgets', 'PDM') );
        echo '&nbsp;&nbsp;';
        echo sprintf('<input id="%s" name="%s" type="radio" value="%s" %s/> <label for="%s">%s</label>',
                     $this->get_field_id('use_advanced_form'),
                     $this->get_field_name('form_mode'),
                     'advanced',
                     pdl_getv($instance, 'form_mode', 'basic') == 'advanced' ? 'checked="checked"' : '',
                     $this->get_field_id('use_advanced_form'),
                    _x('Advanced', 'widgets', 'PDM') );
        echo '</p>';

        echo '<p class="pdl-search-widget-advanced-settings">';
        echo _x('Search Fields (advanced mode):', 'widgets', 'PDM') . '<br/>';
        echo ' <span class="description">' . _x('Display the following fields in the form.', 'widgets', 'PDM') . '</span>';

        $instance_fields = pdl_getv( $instance, 'search_fields', array() );

        $api = pdl_formfields_api();

        echo sprintf('<select name="%s[]" multiple="multiple">', $this->get_field_name('search_fields'));

        foreach ( $api->get_fields() as $field ) {
            if ( $field->display_in( 'search' ) ) {
                echo sprintf( '<option value="%s" %s>%s</option>',
                              $field->get_id(),
                              ( !$instance_fields || in_array( $field->get_id(), $instance_fields) ) ? 'selected="selected"' : '',
                             esc_attr( $field->get_label() ) );
            }
        }

        echo '</select>';
        echo '</p>';
    }

    public function update($new_instance, $old_instance) {
        $new_instance['title'] = strip_tags($new_instance['title']);
        $new_instance['form_mode'] = pdl_getv($new_instance, 'form_mode', 'basic');
        $new_instance['search_fields'] = pdl_getv($new_instance, 'search_fields', array());
        return $new_instance;
    }

    public function widget($args, $instance) {
        extract($args);
        $title = apply_filters( 'widget_title', $instance['title'] );

        echo $before_widget;
        if ( ! empty( $title ) ) echo $before_title . $title . $after_title;

        echo sprintf('<form action="%s" method="get">', pdl_url( '/' ) );

        if ( ! pdl_rewrite_on() )
            echo sprintf('<input type="hidden" name="page_id" value="%s" />', pdl_get_page_id('main'));

        echo '<input type="hidden" name="pdl_view" value="search" />';
        echo '<input type="hidden" name="dosrch" value="1" />';

        if (pdl_getv($instance, 'form_mode', 'basic') == 'advanced') {
            $fields_api = pdl_formfields_api();

            foreach  ( $fields_api->get_fields() as $field ) {
                if ( $field->display_in( 'search' ) && in_array( $field->get_id(), $instance['search_fields'] ) ) {
                    echo $field->render( null, 'search' );
                }
            }

            echo '<input type="hidden" name="kw" value="" />';
        } else {
            echo '<input type="text" name="kw" value="" />';
        }

        echo sprintf('<p><input type="submit" value="%s" class="submit pdl-search-widget-submit" /></p>', _x('Search', 'widgets', 'PDM'));
        echo '</form>';

        echo $after_widget;
    }    

}
