<div id="pdl-main-box" class="pdl-main-box" data-breakpoints='{"tiny": [0,360], "small": [360,560], "medium": [560,710], "large": [710,999999]}' data-breakpoints-class-prefix="pdl-main-box">

<?php if ( pdl_get_option( 'show-search-listings' ) ): ?>
<div class="main-fields box-row cols-2 cf">
    <form action="<?php echo $search_url; ?>" method="get">
        <input type="hidden" name="pdl_view" value="search" />
        <?php if ( ! pdl_rewrite_on() ): ?>
        <input type="hidden" name="page_id" value="<?php echo pdl_get_page_id(); ?>" />
        <?php endif; ?>
        <div class="box-col search-fields">
            <div class="box-row cols-<?php echo $no_cols; ?>">
                <div class="box-col main-input">
                    <input type="text" id="pdl-main-box-keyword-field" class="keywords-field" name="kw" placeholder="<?php echo esc_attr( _x( 'Find listings for <keywords>', 'main box', 'PDM' ) ); ?>" />
                </div>
                <?php echo $extra_fields; ?>
            </div>
        </div>
        <div class="box-col submit-btn">
            <input type="submit" value="<?php _ex( 'Find Listings', 'main box', 'PDM' ); ?>" /><br />
            <a class="advanced-search-link" href="<?php echo $search_url; ?>"><?php _ex( 'Advanced Search', 'main box', 'PDM' ); ?></a>
        </div>
    </form>
</div>

<div class="box-row separator"></div>
<?php endif; ?>

<?php if ( $main_links = pdl_the_main_links( $buttons ) ): ?>
<div class="box-row"><?php echo $main_links; ?></div>
<?php endif; ?>

</div>
