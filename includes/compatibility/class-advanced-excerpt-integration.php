<?php
/**
 * Compatibility code for Advanced Excerpt plugin.
 */

/**
 * Integration with Advanced Excerpt plugin.
 */
class PDL_Advanced_Excerpt_Integration {

    /**
     * @since 5.0.2
     */
    public function __construct() {
        add_filter( 'advanced_excerpt_skip_page_types', array( $this, 'filter_skip_page_types' ) );
    }

    /**
     * @param $page_types   A list of page types that are already skipped.
     * @since 5.0.2
     */
    public function filter_skip_page_types( $page_types ) {
        return array_merge( array( PDL_CATEGORY_TAX, PDL_TAGS_TAX ), $page_types );
    }
}

