<div class="pdl-page-csv-export">

<a name="exporterror"></a>
<div class="error" style="display: none;"><p>
<?php _ex( 'An unknown error occurred during the export. Please make sure you have enough free disk space and memory available to PHP. Check your error logs for details.',
           'admin csv-export',
           'PDM' ); ?>
</p></div>

<div class="step-1">

<div class="pdl-note"><p>
<?php
$notice = _x( "Please note that the export process is a resource intensive task. If your export does not succeed try disabling other plugins first and/or increasing the values of the 'memory_limit' and 'max_execution_time' directives in your server's php.ini configuration file.",
              'admin csv-export',
              'PDM' );
$notice = str_replace( array( 'memory_limit', 'max_execution_time' ),
                       array( '<a href="http://www.php.net/manual/en/ini.core.php#ini.memory-limit" target="_blank" rel="noopener">memory_limit</a>',
                              '<a href="http://www.php.net/manual/en/info.configuration.php#ini.max-execution-time" target="_blank" rel="noopener">max_execution_time</a>' ),
                       $notice );
echo $notice;
?>
</p>
</div>

<!--<h3><?php _ex('Export Configuration', 'admin csv-export', 'PDM'); ?></h3>-->
<form id="pdl-csv-export-form" action="" method="POST">
    
    <h2><?php _ex( 'Export settings', 'admin csv-export', 'PDM' ); ?></h2>
    <table class="form-table">
        <tr>
            <th scope="row">
                <label> <?php _ex('Which listings to export?', 'admin csv-export', 'PDM'); ?></label>
            </th>
            <td>
                <select name="settings[listing_status]">
                    <option value="all"><?php _ex( 'All', 'admin csv-export', 'PDM' ); ?></option>
                    <option value="publish"><?php _ex( 'Active Only', 'admin csv-export', 'PDM' ); ?></option>
                    <option value="publish+draft"><?php _ex( 'Active + Pending Renewal', 'admin csv-export', 'PDM' ); ?></option>
                </select>
            </td>
        </tr>      
        <tr>
            <th scope="row">
                <label> <?php _ex('Export images?', 'admin csv-export', 'PDM'); ?></label>
            </th>
            <td>
                <label><input name="settings[export-images]"
                       type="checkbox"
                       value="1" /> <?php _ex('Export images', 'admin csv-export', 'PDM'); ?></label> <br />
                <span class="description">
                    <?php _ex( 'When checked, instead of just a CSV file a ZIP file will be generated with both a CSV file and listing images.', 'admin csv-export', 'PDM' ); ?>
                </span>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label> <?php _ex('Additional metadata to export:', 'admin csv-export', 'PDM'); ?></label>
            </th>
            <td>
                <label><input name="settings[generate-sequence-ids]"
                       type="checkbox"
                       value="1" /> <?php _ex('Include unique IDs for each listing (sequence_id column).', 'admin csv-export', 'PDM' ); ?></label><br />
                <span class="description">
                <strong><?php _ex( 'If you plan to re-import the listings into PDL and don\'t want new ones created, select this option!', 'admin csv-export', 'PDM'); ?></strong>
                </span> <br /><br />

                <label><input name="settings[include-users]"
                       type="checkbox"
                       value="1"
                       checked="checked" /> <?php _ex('Author information (username)', 'admin csv-export', 'PDM'); ?></label> <br />

                <label><input name="settings[include-expiration-date]"
                       type="checkbox"
                       value="1"
                       checked="checked" /> <?php _ex('Listing expiration date', 'admin csv-export', 'PDM'); ?></label> <br />
            </td>
        </tr>
    </table>

    <h2><?php _ex('CSV File Settings', 'admin csv-export', 'PDM'); ?></h2>
    <table class="form-table">
            <tr class="form-required">
                <th scope="row">
                    <label> <?php _ex( 'What operating system will you use to edit the CSV file?', 'admin csv-export', 'PDM' ); ?> <span class="description">(<?php _ex('required', 'admin forms', 'PDM'); ?>)</span></label>
                </th>
                <td>
                    <label><input name="settings[target-os]"
                           type="radio"
                           aria-required="true"
                           value="windows"
                           checked="checked" /><?php _ex( 'Windows', 'admin csv-export', 'PDM' ); ?></label>
                    <br />
                    <label><input name="settings[target-os]"
                           type="radio"
                           aria-required="true"
                           value="macos" /><?php _ex( 'macOS', 'admin csv-export', 'PDM' ); ?></label>
                    <br />
                    <p><?php _ex( 'Windows and macOS versions of MS Excel handle CSV files differently. To make sure all your listings information is displayed properly when you view or edit the CSV file, we need to generate different versions of the file for each operating system.', 'admin csv-export', 'PDM' ); ?></p>
                </td>
            </tr>
            <tr class="form-required">
                <th scope="row">
                    <label> <?php _ex('Image Separator', 'admin csv-export', 'PDM'); ?> <span class="description">(<?php _ex('required', 'admin forms', 'PDM'); ?>)</span></label>
                </th>
                <td>
                    <input name="settings[images-separator]"
                           type="text"
                           aria-required="true"
                           value=";" />
                </td>
            </tr>
            <tr class="form-required">
                <th scope="row">
                    <label> <?php _ex('Category Separator', 'admin csv-export', 'PDM'); ?> <span class="description">(<?php _ex('required', 'admin forms', 'PDM'); ?>)</span></label>
                </th>
                <td>
                    <input name="settings[category-separator]"
                           type="text"
                           aria-required="true"
                           value=";" />
                </td>
            </tr>
    </table>

    <p class="submit">
        <?php echo submit_button( _x( 'Export Listings', 'admin csv-export', 'PDM' ), 'primary', 'do-export', false ); ?>
    </p>
</form>
</div>

<div class="step-2">
    <h2><?php _ex( 'Export in Progress...', 'admin csv-export', 'PDM' ); ?></h2>
    <p><?php _ex( 'Your export file is being prepared. Please <u>do not leave</u> this page until the export finishes.', 'admin csv-export', 'PDM' ); ?></p>
    
    <dl>
        <dt><?php _ex( 'No. of listings:', 'admin csv-export', 'PDM' ); ?></dt>
        <dd class="listings">?</dd>
        <dt><?php _ex( 'Approximate export file size:', 'admin csv-export', 'PDM' ); ?></dt>
        <dd class="size">?</dd> 
    </dl>
    
    <div class="export-progress"></div>
    
    <p class="submit">
        <a href="#" class="cancel-import button"><?php _ex( 'Cancel Export', 'admin csv-export', 'PDM' ); ?></a>
    </p>
</div>

<div class="step-3">
    <h2><?php _ex( 'Export Complete', 'admin csv-export' )?></h2>
    <p><?php _ex( 'Your export file has been successfully created and it is now ready for download.', 'admin csv-export', 'PDM' ); ?></p>
    <div class="download-link">
        <a href="" class="button button-primary">
            <?php echo sprintf( _x( 'Download %s (%s)', 'admin csv-export', 'PDM' ),
                                '<span class="filename"></span>',
                                '<span class="filesize"></span>' ); ?>
        </a>
    </div>
    <div class="cleanup-link pdl-note">
        <p><?php _ex( 'Click "Cleanup" once the file has been downloaded in order to remove all temporary data created by Plestar Directory Listing during the export process.', 'admin csv-export', 'PDM' ); ?><br />
        <a href="" class="button"><?php _ex( 'Cleanup', 'admin csv-export', 'PDM' ); ?></a></p>
    </div>    
</div>

<div class="canceled-export">
    <h2><?php _ex( 'Export Canceled', 'admin csv-export' )?></h2>
    <p><?php _ex( 'The export has been canceled.', 'admin csv-export', 'PDM' ); ?></p>
    <p><a href="" class="button"><?php _ex( 'â†� Return to CSV Export', 'admin csv-export', 'PDM' ); ?></a></p>
</div>

</div>
