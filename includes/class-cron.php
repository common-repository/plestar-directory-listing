<?php
/**
 * @since 5.0
 */
class PDL__Cron {

    public function __construct() {
        $this->schedule_events();
    }

    private function schedule_events() {
        if ( ! wp_next_scheduled( 'pdl_hourly_events' ) )
            wp_schedule_event( current_time( 'timestamp' ), 'hourly', 'pdl_hourly_events' );

        if ( ! wp_next_scheduled( 'pdl_daily_events' ) )
            wp_schedule_event( current_time( 'timestamp' ), 'daily', 'pdl_daily_events' );
    }

}
