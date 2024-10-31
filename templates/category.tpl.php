<h2 class="category-name">
    <?php echo esc_html( $category->name ); ?>
</h2>

<?php do_action( 'pdl_before_category_page', $category ); ?>
<?php echo pdl_x_render( 'listings', array( 'query' => $query ) ); ?>
<?php do_action( 'pdl_after_category_page', $category ); ?>
