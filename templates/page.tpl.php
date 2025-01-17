<?php
$show_bar = ( isset( $_child->_bar ) ? $_child->_bar : ( isset( $_bar ) ? $_bar : true ) );
?>
<div id="pdl-page-<?php echo $_child->_id; ?>" class="pdl-page pdl-page-<?php echo $_child->_id; ?> <?php echo $_class; ?>">
    <?php if ( $show_bar ): ?>
        <?php $bar_args = isset( $_bar_args ) ? $_bar_args : ( isset( $_child->_bar_args ) ? $_child->_bar_args : array() ); ?>
        <?php echo pdl_main_box( $bar_args ); ?>
    <?php endif; ?>

    <?php
    // TODO: Try to use blocks for this too, instead of actions.
    ?>

    <?php do_action( 'pdl_page_before', $_child->_id ); ?>
    <?php do_action( 'pdl_page_' . $_child->_id . '_before' ); ?>
    <?php echo $content; ?>
    <?php do_action( 'pdl_page_after', $_child->_id ); ?>
    <?php do_action( 'pdl_page_' . $_child->_id . '_after' ); ?>
</div>
