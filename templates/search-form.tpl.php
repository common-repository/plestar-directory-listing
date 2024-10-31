<div id="pdl-search-form-wrapper">

<h3><?php _ex('Find a listing', 'templates', 'PDM'); ?></h3>
<form action="<?php echo esc_url( pdl_url( 'search' ) ); ?>" id="pdl-search-form" method="get">
    <input type="hidden" name="dosrch" value="1" />
    <input type="hidden" name="q" value="" />

    <?php if ( ! pdl_rewrite_on() ): ?>
    <input type="hidden" name="page_id" value="<?php echo pdl_get_page_id(); ?>" />
    <?php endif; ?>
    <input type="hidden" name="pdl_view" value="search" />

    <?php if ( ! empty( $return_url ) ): ?>
    <input type="hidden" name="return_url" value="<?php echo esc_attr( esc_url( $return_url ) ); ?>" />
    <?php endif; ?>

    <?php if ( $validation_errors ): ?>
        <?php foreach ( $validation_errors as $err ): ?>
            <?php echo pdl_render_msg( $err, 'error' ); ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php echo $fields; ?>
    <?php do_action('pdl_after_search_fields'); ?>

    <p>
        <input type="reset" class="pdl-button reset" value="<?php _ex( 'Clear', 'search', 'PDM' ); ?> " onclick="window.location.href = '<?php echo pdl_get_page_link( 'search' ); ?>';" />
        <input type="submit" class="pdl-submit pdl-button submit" value="<?php _ex('Search', 'search', 'PDM'); ?>" />
    </p>
</form>

</div>
