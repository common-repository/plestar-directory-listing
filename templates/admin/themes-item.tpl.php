    <div class="pdl-theme <?php echo $theme->id; ?> <?php echo ( $theme->active ? 'active' : '' ); ?> <?php do_action( 'pdl-admin-themes-item-css', $theme ); ?> ">
        <h3 class="pdl-theme-name">
            <?php if ( $theme->active ): ?><span><?php _ex( 'Active:', 'themes', 'PDM' ); ?></span> <?php endif; ?>
            <?php echo $theme->name; ?>
        </h3>

        <div class="pdl-theme-actions">
            <?php if ( ! $theme->active && ! in_array( $theme->id, array( 'default', 'no_theme' ), true ) ): ?>
            <a href="<?php echo esc_url( add_query_arg( array( 'action' => 'delete-theme', 'theme_id' => $theme->id ) ) ); ?>" class="button delete-theme-link">Delete</a>
            <?php endif; ?>

            <?php if ( $theme->can_be_activated ): ?>
            <form action="" method="post">
                <input type="hidden" name="pdl-action" value="set-active-theme" />
                <input type="hidden" name="theme_id" value="<?php echo $theme->id; ?>" />
                <?php wp_nonce_field( 'activate theme ' . $theme->id ); ?>
                <input type="submit" class="button choose-theme button-primary" value="<?php _ex( 'Activate', 'themes', 'PDM' ); ?>" />
            </form>
            <?php endif; ?>
        </div>

        <div class="pdl-theme-details-wrapper">
            <?php if ( $theme->thumbnail ): ?>
                <a href="<?php echo $theme->thumbnail; ?>" title="<?php esc_attr_e( $theme->name ); ?>" class="thickbox" rel="pdl-theme-<?php echo $theme->id; ?>-gallery"><img src="<?php echo $theme->thumbnail; ?>" class="pdl-theme-thumbnail" /></a>
                <!-- Other images -->
                <?php foreach ( $theme->thumbnails as $imgpath => $title ): ?>
                    <a href="<?php echo $theme->url; ?><?php echo $imgpath; ?>" class="thickbox" title="<?php esc_attr_e( $title ); ?>" class="thickbox" rel="pdl-theme-<?php echo $theme->id; ?>-gallery" style="display: none;"></a>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="pdl-theme-thumbnail"></div>
            <?php endif; ?>

            <div class="pdl-theme-details">
                <dl>
                    <dt class="version"><?php _ex( 'Version:', 'themes', 'PDM' ); ?></dt>
                    <dd class="version"><?php echo $theme->version; ?></dd>

                    <dt class="author"><?php _ex( 'Author:', 'themes', 'PDM' ); ?></dt>
                    <dd class="author"><?php echo $theme->author; ?></dd>
                </dl>

                <p class="desc"><?php echo $theme->description; ?></p>
            </div>

        </div>

        <?php do_action( 'pdl-admin-themes-extra', $theme ); ?>
    </div>
