<?php

/**
 * @since 5.0
 */
function pdl_insert_log( $args = array() ) {
    $defaults = array(
        'object_id' => 0,
        'rel_object_id' => 0,
        'object_type' => '',
        'created_at' => current_time( 'mysql' ),
        'log_type' => '',
        'actor' => 'system',
        'message' => '',
        'data' => null
    );
    $args = wp_parse_args( $args, $defaults );
    extract( $args );

    if ( ! $object_type && false !== strstr( $log_type, '.' ) ) {
        $parts = explode( '.', $log_type );
        $object_type = $parts[0];
    }

    $object_id = absint( $object_id );
    $message = trim( $message );
    $data = $data ? serialize( $data ) : null;

    $row = compact( 'object_type', 'object_id', 'rel_object_id', 'created_at', 'log_type', 'actor', 'message', 'data' );

    if ( ! $data )
        unset( $row['data'] );

    global $wpdb;
    if ( ! $wpdb->insert( $wpdb->prefix . 'pdl_logs', $row ) )
        return false;

    $row['id'] = absint( $wpdb->insert_id );

    return (object) $row;
}


/**
 * @since 5.0
 */
function pdl_delete_log( $log_id ) {
    global $wpdb;
    return $wpdb->delete( $wpdb->prefix . 'pdl_logs', array( 'id' => $log_id ) );
}

/**
 * @since 5.0
 */
function pdl_get_log( $id ) {
    $results = pdl_get_logs( array( 'id' => $id ) );

    if ( ! $results )
        return false;

    return $results[0];
}

/**
 * @since 5.0
 */
function pdl_get_logs( $args = array() ) {
    $defaults = array(
        'limit' => 0,
        'orderby' => 'created_at',
        'order' => 'DESC'
    );
    $args = wp_parse_args( $args, $defaults );


    global $wpdb;

    $query  = '';
    $query .= "SELECT * FROM {$wpdb->prefix}pdl_logs WHERE 1=1";

    foreach ( $args as $arg_k => $arg_v ) {
        if ( in_array( $arg_k, array( 'id', 'object_id', 'object_type', 'created_at', 'log_type', 'actor' ) ) )
            $query .= $wpdb->prepare( " AND {$arg_k} = %s", $arg_v );
    }

    $query .= " ORDER BY {$args['orderby']} {$args['order']}, id {$args['order']}";

    return $wpdb->get_results( $query );
}
