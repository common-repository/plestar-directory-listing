<?php
    $buttons = array(
        array( _x('Add New Form Field', 'form-fields admin', 'PDM'), esc_url(add_query_arg('action', 'addfield')) ),
        array( _x('Preview Form', 'form-fields admin', 'PDM'), esc_url(add_query_arg('action', 'previewform')) ) );

    $buttons[] = array( _x( 'Manage Theme Tags', 'form-fields admin', 'PDM' ), esc_url( add_query_arg( 'action', 'updatetags' ) ) );

    echo pdl_admin_header( null, null, $buttons );
?>
    <?php pdl_admin_notices(); ?>

    <?php _ex( 'Here, you can create new fields for your listings, edit or delete existing ones, change the order and visibility of the fields as well as configure special options for them.',
               'form-fields admin',
               'PDM' ); ?><br />
    <?php
    echo str_replace( '<a>',
                      '<a href="https://plestar.net/knowledge-base/manage-form-fields/" target="_blank" rel="noopener">',
                      _x( 'Please see the <a>Form Fields documentation</a> for more details.',
                          'form-fields admin',
                          'PDM' ) ); ?>

    <?php $table->views(); ?>
    <?php $table->display(); ?>

<?php echo pdl_admin_footer(); ?>
