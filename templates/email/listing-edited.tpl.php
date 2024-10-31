<?php
    _ex( 'A listing in the directory has been edited recently. Listing details can be found below.', 'emails', 'PDM' );
?>

----

<?php _ex('ID', 'notify email', 'PDM'); ?>: <?php echo $listing->get_id(); ?>


<?php _ex('Title', 'notify email', 'PDM'); ?>: <?php echo $listing->get_title(); ?>


<?php _ex('URL', 'notify email', 'PDM'); ?>: <?php echo $listing->is_published() ? $listing->get_permalink() : _x( '(not published yet)', 'notify email', 'PDM' ); ?>

<?php _ex( 'Admin URL', 'notify email', 'PDM' ); ?>: <?php echo get_edit_post_link( $listing->get_id() ); ?>

<?php _ex('Categories', 'notify email', 'PDM'); ?>: <?php foreach ( $listing->get_categories() as $category ): ?><?php echo $category->name; ?> / <?php endforeach; ?>


<?php _ex('Posted By', 'notify email', 'PDM'); ?>: <?php echo $listing->get_author_meta( 'user_login' ); ?> (<?php echo $listing->get_author_meta( 'user_email' ); ?>)
