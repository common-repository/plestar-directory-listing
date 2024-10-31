<?php echo pdl_admin_header( $title ); ?>
<?php pdl_admin_notices(); ?>

<?php if ( $explanation ): ?>
    <p><?php echo $explanation; ?></p>
<?php endif; ?>

<form action="" method="post">
    <?php wp_nonce_field( 'confirm ' . md5( $title ) ); ?>
    <a href="<?php echo $cancel_url; ?>" class="button button-secondary"><?php echo $cancel_text; ?></a>
        <input type="submit" value="<?php echo $submit_text; ?>" class="button button-primary" />
</form>

<?php echo pdl_admin_footer(); ?>
