<?php
/**
 * @package PDL\Admin\Listings\Views
 */

/**
 * View filter for Listings admin page.
 */
class PDL__ListingsWithNoFeePlanView {

    /**
     * @var string  The ID of the View.
     */
    private $id;

    /**
     * Constructor.
     *
     * @since 5.1.6
     */
    public function __construct( $id = 'no-fee-plan' ) {
        $this->id = $id;
    }

    /**
     * TODO: Another class could use this method to manage all Listings Views and
     *       decide which ones to show.
     *
     *       That would allow Listing View classes to worry about counting that
     *       view's objects only, ignoring details about how that information is
     *       presented to the user.
     *
     * @param $post_statuses_string     A comma separated list of Post statuses
     *                                  ready to be used in a SQL query.
     *
     *                                  Example: "'draft', 'pending', 'private', 'publish'"
     *                                  Could be used as: `... IN ($post_statuses_string)`
     * @since 5.1.6
     */
    public function count_listings( $post_statuses_string ) {
        $post_statuses = explode( ',', str_replace( "'", '', $post_statuses_string ) );

        return PDL_Listing::count_listings_with_no_fee_plan( array( 'post_status' => $post_statuses ) );
    }

    /**
     * @since 5.1.6
     */
    public function filter_views( $views, $post_statuses_string ) {
        $listings_without_fee_plan = $this->count_listings( $post_statuses_string );

        if ( ! $listings_without_fee_plan ) {
            return $views;
        }

        $views['pdl-no-fee-plan'] = $this->render_view_link(
            'no-fee-plan',
            _x( 'No Fee Plan', 'listings view', 'PDM' ),
            'pdmfilter',
            $listings_without_fee_plan,
            ! empty( $_GET['pdmfilter'] ) && 'no-fee-plan' == $_GET['pdmfilter']
        );

        return $views;
    }

    /**
     * TODO: Move this method to a Listings View Helper class, so that other View
     *       classes, and even Admin Listings, can use it as well.
     *
     * @since 5.1.6
     */
    private function render_view_link( $id, $label, $parameter, $count, $active ) {
        $url = add_query_arg( $parameter, $id, remove_query_arg( array( 'post_status', 'author', 'all_posts', 'pdmfilter' ) ) );

        return sprintf(
            '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
            esc_url( $url ),
            $active ? 'current' : '',
            $label,
            $count
        );
    }

    /**
     * @since 5.1.6
     */
    public function filter_query_pieces( $pieces, $active_filter ) {
        global $wpdb;

        if ( $active_filter == $this->id ) {
            $pieces['join']  .= " LEFT JOIN {$wpdb->prefix}pdl_listings ls ON ls.listing_id = {$wpdb->posts}.ID ";
            $pieces['where'] .= ' AND ls.listing_id IS NULL';
        }

        return $pieces;
    }
}
