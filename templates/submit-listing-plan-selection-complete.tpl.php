<?php
$plan = $listing->get_fee_plan()->fee;
$categories = wp_get_post_terms( $listing->get_id(), PDL_CATEGORY_TAX, array( 'fields' => 'ids' ) );
?>

<?php if ( $categories ): ?>
    <ul class="category-list">
    <?php foreach ( $categories as $cat_id ): ?>
        <?php $category = get_term( $cat_id, PDL_CATEGORY_TAX ); ?>
        <li><?php echo $category->name; ?></li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>

<div class="pdl-plan-selection-wrapper" data-breakpoints='{"tiny": [0,410], "small": [410,560], "medium": [560,710], "large": [710,999999]}' data-breakpoints-class-prefix="pdl-size">
    <div class="pdl-plan-selection">
        <div class="pdl-plan-selection-list">
            <?php echo pdl_render( 'plan-selection-plan', array( 'plan' => $plan, 'categories' => $categories, 'display_only' => true, 'extra' ) ); ?>
        </div>
    </div>
</div>

<div id="change-plan-link" class="pdl-clearfix">
    <span class="dashicons dashicons-update"></span>
    <a href="#"><?php _ex( 'Change category/plan', 'listing submit', 'PDM'); ?></a>
</div>

<script type="text/javascript">
jQuery(function($) {
    var amount = <?php echo $plan->calculate_amount( $categories ); ?>;

    if ( pdlSubmitListingL10n.isAdmin || amount == 0.0 ) {
        $( '#pdl-submit-listing-submit-btn' ).val( pdlSubmitListingL10n.completeListingTxt );
    } else {
        $( '#pdl-submit-listing-submit-btn' ).val( pdlSubmitListingL10n.continueToPaymentTxt );
    }
});
</script>
