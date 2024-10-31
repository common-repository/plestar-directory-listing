<?php
require_once ( PDL_PATH . 'includes/helpers/class-wp-taxonomy-term-list.php'  );

/**
 * @since 5.0
 */
class PDL__Admin__Variable_Pricing_Configurator extends PDL__WP_Taxonomy_Term_List {

    private $categories = array();
    private $prices = array();


    function __construct( $args ) {
        parent::__construct( $args );

        if ( ! isset( $args['fee'] ) )
            return;

        $fee = $args['fee'];

        $this->pricing_model = $fee->pricing_model;
        $this->categories = $fee->supported_categories;

        if ( 'variable' != $this->pricing_model )
            return;

        $this->prices = $fee->pricing_details;
    }

    protected function element_before( $term, $depth ) {
        return '<tr class="pdl-variable-pricing-configurator-row ' . ( 'all' != $this->categories && ! in_array( $term->term_id, $this->categories ) ? 'hidden' : '' ) . '" data-term-id="' . $term->term_id . '"><td class="category-name-col">';
    }

    protected function element( $term, $depth ) {
        $res = parent::element( $term, $depth );
        return str_replace( array( '<br>', '<br />' ), '', $res );
    }

    protected function element_after( $term, $depth  ) {
        $res  = '';
        $res .= sprintf( '</td><td class="category-price-col"><input id="pdl-fee-form-fee-category" type="text" name="fee[pricing_details][%d]" class="category-price" value="%s" /></td>',
                         $term->term_id,
                         isset( $this->prices[ $term->term_id ] ) ? $this->prices[ $term->term_id ] : '0.0' );
        $res .= '</tr>';

        return $res;
    }

}
