<div id="pdl-listing-metabox-timeline">
    <?php foreach ( $timeline as $item ): ?>
    <div class="timeline-item timeline-item-<?php echo str_replace( '.', '_', $item->log_type ); ?>" id="pdl-timeline-item-<?php echo $item->id; ?>">
        <div class="timeline-item-header">
            <span class="timeline-item-icon"></span>
            <span class="timeline-item-description"><?php echo $item->html; ?></span>
            <span class="timeline-item-datetime">
                <?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $item->timestamp ); ?>
            </span>
        </div>
        <div class="timeline-item-extra">
            <?php echo $item->extra; ?>
        </div>
        <?php if ( $item->actions ): ?>
        <div class="timeline-item-actions">
            <?php foreach ( $item->actions as $action_key => $action_html ): ?>
                <?php echo $action_html; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
