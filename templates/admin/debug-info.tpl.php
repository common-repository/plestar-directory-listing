<?php echo pdl_admin_header(); ?>

<div id="pdl-admin-debug-info-page">
<p>
	<?php _ex( 'The following information can help PDL developers debug possible problems with your setup.', 'debug-info', 'PDM' ); ?>
	<strong><u><?php _ex( 'The debug information does not contain personal or sensitive information such as passwords or private keys.', 'debug-info', 'PDM' ); ?></u></strong>
</p>
<p style="text-align: right;">
    <a href="<?php echo esc_url( add_query_arg( 'download', '1' ) ); ?>" class="button button-primary"><?php _ex( 'Download Debug Information', 'debug-info', 'PDM' ); ?></a>
</p>

<h3 class="nav-tab-wrapper">
<?php foreach ( $debug_info as $section_id => &$section ): ?>
	<a class="nav-tab" href="<?php echo $section_id; ?>"><?php echo $section['_title']; ?></a>
<?php endforeach; ?>
</h3>

<?php foreach ( $debug_info as $section_id => &$section ): ?>
<table class="pdl-debug-section" data-id="<?php echo $section_id; ?>" style="display: none;">
	<tbody>
		<?php foreach ( $section as $k => $v ): ?>
		<?php if ( pdl_starts_with( $k, '_') ): continue; endif; ?>
		<tr>
			<th scope="row"><?php echo esc_attr( $k ); ?></th>
            <td>
                <?php if ( is_array( $v ) ): ?>
                    <?php echo isset( $v['html'] ) ? $v['html'] : esc_attr( $v['value'] ); ?>
                <?php else: ?>
                    <?php echo esc_attr( $v ); ?>
                <?php endif; ?>
            </td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endforeach; ?>
</div>

<script type="text/javascript">
jQuery( function( $ ) {
    $( '#pdl-admin-debug-info-page a.test-ssl-link' ).click( function( e ) {
        e.preventDefault();

        var $textarea = $( 'textarea.test-ssl-results' );

        if ( 0 == $textarea.length )
            $textarea = $( '<textarea class="test-ssl-results"></textarea>' ).insertAfter( $( this ) );

        $textarea.text( 'Loading...' );

        $.post( ajaxurl, { action: 'pdl-debugging-ssltest' }, function( res ) {
            $textarea.text( res );
        }, 'text' );
    } );
} );
</script>

<?php echo pdl_admin_footer(); ?>
