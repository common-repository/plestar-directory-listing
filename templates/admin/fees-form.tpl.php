<form id="pdl-fee-form" action="" method="post">

    <table class="form-table">
        <tbody>
            <tr class="form-field form-required">
                <th scope="row">
                    <label for="pdl-fee-form-fee-label"> <?php _ex('Fee Label', 'fees admin', 'PDM'); ?> <span class="description">(required)</span></label>
                </th>
                <td>
                    <input id="pdl-fee-form-fee-label"
                           name="fee[label]"
                           type="text"
                           aria-required="true"
                           value="<?php echo esc_attr( $fee->label ); ?>" />
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row">
                    <label for="pdl-fee-form-fee-description"> <?php _ex( 'Fee Description', 'fees admin', 'PDM' ); ?></label>
                </th>
                <td>
                    <textarea id="pdl-fee-form-fee-description" name="fee[description]" rows="5" cols="50"><?php echo esc_textarea( $fee->description ); ?></textarea>
                </td>
            </tr>
            <tr class="form-required">
                <th scope="row">
                    <label for="pdl-fee-form-days"> <?php _ex('How long should the listing run?', 'fees admin', 'PDM'); ?> <span class="description">(required)</span></label>
                </th>
                <td>
                    <input type="radio" id="pdl-fee-form-days" name="_days" value="1" <?php echo absint($fee->days ) > 0 ? 'checked="checked"' : ''; ?>/> <label for="pdl-fee-form-days"><?php _ex('run listing for', 'fees admin', 'PDM'); ?></label>
                    <input id="pdl-fee-form-days-n"
                           type="text"
                           aria-required="true"
                           value="<?php echo absint( $fee->days ); ?>"
                           style="width: 80px;"
                           name="fee[days]"
                           <?php echo ( absint( $fee->days ) == 0 ) ? 'disabled="disabled"' : ''; ?>
                           />
                    <?php _ex('days', 'fees admin', 'PDM'); ?>
                    <br />
                    <input type="radio" id="pdl-fee-form-days-0" name="_days" value="0" <?php echo ( absint( $fee->days ) == 0 ) ? 'checked="checked"' : ''; ?>/> <label for="pdl-fee-form-days-0"><?php _ex('run listing forever', 'fees admin', 'PDM'); ?></label>                 
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row">
                    <label for="pdl-fee-form-fee-images"> <?php _ex('Number of images allowed', 'fees admin', 'PDM'); ?> <span class="description">(required)</span></label>
                </th>
                <td>
                    <input id="pdl-fee-form-fee-images"
                           name="fee[images]"
                           type="text"
                           aria-required="true"
                           value="<?php echo absint( $fee->images ); ?>"
                           style="width: 80px;" />
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row">
                    <label for="pdl-fee-form-fee-recurring"> <?php _ex('Is recurring?', 'fees admin', 'PDM'); ?></label>
                </th>
                <td>
                    <label>
                        <input id="pdl-fee-form-fee-recurring"
                               name="fee[recurring]"
                               type="checkbox"
                               value="1"
                               <?php echo $fee->recurring ? 'checked="checked"' : ''; ?>
                               <?php echo ( 'free' == $fee->tag ) ? 'disabled="disabled"' : ''; ?> />
                        <span class="description"><?php _ex( 'Should the listing auto-renew at the end of the listing term?', 'fees admin', 'PDM' ); ?></span>
                    </label>
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row">
                    <label for="pdl-fee-form-fee-sticky"> <?php _ex('Is featured listing/sticky?', 'fees admin', 'PDM'); ?></label>
                </th>
                <td>
                    <input id="pdl-fee-form-fee-sticky"
                           name="fee[sticky]"
                           type="checkbox"
                           value="1"
                           <?php echo $fee->sticky ? 'checked="checked"' : ''; ?>
                           <?php echo ( 'free' == $fee->tag ) ? 'disabled="disabled"' : ''; ?> />
                    <label for="pdl-fee-form-fee-sticky"><span class="description"><?php _ex( 'This floats the listing to the top of search results and browsing the directory when the user buys this plan.', 'fees admin', 'PDM' ); ?></span></label>
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row">
                    <label for="fee-bgcolor-value"><?php _ex( 'Listing background color:', 'fees admin', 'PDM' ); ?></label>
                </th>
                <td>
                    <div id="fee-bgcolor-picker">
                        <input name="fee[extra_data][bgcolor]" id="fee-bgcolor-value" value="<?php echo isset( $fee->extra_data['bgcolor'] ) ? $fee->extra_data['bgcolor'] : ''; ?>" size="4" />

                        <div class="color-selection">
                            <div class="color-selection-btns">
                                <a href="#" class="reset-btn"><span class="no-color-img"></span> <?php _ex( 'Reset Color', 'fees admin', 'PDM'); ?></a>
                                <a href="#" class="close-btn">✖</a>
                            </div>
                            <div id="fee-bgcolor-picker-iris"></div>
                        </div>
                    </div>

                    <span class="description"><?php _ex( 'Used to differentiate listings inside this plan from others.', 'fees admin', 'PDM' ); ?></span>
                </td>
            </tr>
            <tr class="form-field limit-categories">
                <th scope="row">
                    <label for="pdl-fee-form-fee-category-policy"><?php _ex( 'Plan Category Policy:', 'fees admin', 'PDM' ); ?></label>
                </th>
                <td>
                    <select id="pdl-fee-form-fee-category-policy"
                            name="limit_categories">
                        <option value="0"><?php _ex( 'Plan applies to all categories', 'fees admin', 'PDM' ); ?></option>
                        <option value="1" <?php selected( is_array( $fee->supported_categories ), true ); ?> ><?php _ex( 'Plan applies to only certain categories', 'fees admin', 'PDM' ); ?></option>
                    </select>

                    <div id="limit-categories-list" class="<?php echo is_array( $fee->supported_categories ) ? '' : 'hidden'; ?>">
                        <p><span class="description"><?php _ex( 'Limit plan to the following categories:', 'fees admin', 'PDM' ); ?></span></p>
<?php
$all_categories = get_terms( array( 'taxonomy' => PDL_CATEGORY_TAX, 'hide_empty' => false, 'hierarchical' => true ) );
$supported_categories = is_array( $fee->supported_categories ) ? array_map( 'absint', $fee->supported_categories ) : array();

if ( count( $all_categories ) <= 30 ):
    foreach ( $all_categories as $category ):
?>
    <div class="pdl-category-item">
        <label>
            <input type="checkbox" name="fee[supported_categories][]" value="<?php echo $category->term_id; ?>" <?php checked( in_array( (int) $category->term_id, $supported_categories ) ); ?>> 
            <?php echo esc_html( $category->name ); ?>
        </label>
    </div>
<?php
    endforeach;
else:
?>
    <select name="fee[supported_categories][]" multiple="multiple" placeholder="<?php _ex( 'Click to add categories to the selection.', 'fees admin', 'PDM' ); ?>">
    <?php foreach ( $all_categories as $category ): ?>
    <option value="<?php echo $category->term_id; ?>" <?php selected( in_array( (int) $category->term_id, $supported_categories ) ); ?>><?php echo esc_html( $category->name ); ?></option>
    <?php endforeach; ?>
    </select>
<?php
endif;
?>
                        </div>
                </td>
            </tr>
        </tbody>
    </table>

    <h2><?php _ex( 'Pricing', 'fees admin', 'PDM'); ?></h2>
    <table class="form-table">
        <tbody>
            <tr class="form-field pricing-info">
                <th scope="row">
                    <label for="pdl-fee-form-pricing-model-flat"><?php _ex( 'Pricing model', 'fees admin', 'PDM' ); ?>
                </th>
                <td>
                    <div class="pricing-options">
                        <label><input id="pdl-fee-form-pricing-model-flat" type="radio" name="fee[pricing_model]" value="flat" <?php checked( $fee->pricing_model, 'flat' ); ?> /> <?php _ex( 'Flat price', 'fees admin', 'PDM' ); ?></label>
                        <label><input id="pdl-fee-form-pricing-model-variable" type="radio" name="fee[pricing_model]" value="variable" <?php checked( $fee->pricing_model, 'variable' ); ?> /> <?php _ex( 'Different price for different categories', 'fees admin', 'PDM' ); ?></label>
                        <label><input id="pdl-fee-form-pricing-model-extra" type="radio" name="fee[pricing_model]" value="extra" <?php checked( $fee->pricing_model, 'extra' ); ?> /> <?php _ex( 'Base price plus an extra amount per category', 'fees admin', 'PDM' ); ?></label>
                    </div>
                </td>
            </tr>
            <tr class="form-field fee-pricing-details pricing-details-flat pricing-details-extra <?php echo ( 'flat' == $fee->pricing_model || 'extra' == $fee->pricing_model ) ? '' : 'hidden'; ?>">
                <th scope="row">
                    <label for="pdl-fee-form-fee-price"><?php _ex( 'Fee Price', 'fees admin', 'PDM' ); ?></label>
                </th>
                <td>
                    <input id="pdl-fee-form-fee-price" type="text" name="fee[amount]" value="<?php echo $fee->amount; ?>" />
                </td>
            </tr>
            <tr class="form-field fee-pricing-details pricing-details-variable <?php echo 'variable' == $fee->pricing_model ? '' : 'hidden'; ?>">
                <th scope="row">
                    <label><?php _ex( 'Prices per category', 'fees admin', 'PDM' ); ?></label>
                </th>
                <td>
                    <table>
                        <thead>
                        <th><?php _ex( 'Category', 'fees admin', 'PDM' ); ?></th>
                        <th><?php _ex( 'Price', 'fees admin', 'PDM' ); ?></th>
                        </thead>
                        <tbody>
                            <?php
                            require_once( PDL_INC . 'admin/helpers/class-variable-pricing-configurator.php' );
                            $c = new PDL__Admin__Variable_Pricing_Configurator( array( 'fee' => $fee ) );
                            $c->display();
                            ?>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr class="form-field fee-pricing-details pricing-details-extra <?php echo 'extra' == $fee->pricing_model ? '' : 'hidden'; ?>">
                <th scope="row">
                    <label for="pdl-fee-form-fee-extra"><?php _ex( 'Extra amount (per category)', 'fees admin', 'PDM' ); ?></label>
                </th>
                <td>
                    <input id="pdl-fee-form-fee-extra" type="text" name="fee[pricing_details][extra]" value="<?php echo esc_attr( isset( $fee->pricing_details['extra'] ) ? floatval( $fee->pricing_details['extra'] ) : 0 ); ?>" />
                </td>
            </tr>
        </tbody>
    </table>

    <?php do_action( 'pdl_after_admin_fee_form', $fee ); ?>

    <?php echo submit_button( $fee->id ? _x( 'Save Changes', 'fees admin', 'PDM' ) : _x( 'Add Listing Fee', 'fees admin', 'PDM' ) ); ?>
</form>

