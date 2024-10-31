<?php
final class PDL__Settings__Bootstrap {

    public static function register_initial_groups() {
        pdl_register_settings_group( 'general', _x( 'General', 'settings', 'PDM' ) );

        pdl_register_settings_group( 'listings', _x( 'Listings', 'settings', 'PDM' ) );
        pdl_register_settings_group( 'listings/main', _x( 'General Settings', 'settings', 'PDM' ), 'listings' );

        pdl_register_settings_group( 'email', _x( 'E-Mail', 'settings', 'PDM' ) );
        pdl_register_settings_group( 'email/main', _x( 'General Settings', 'settings', 'PDM' ), 'email' );

        pdl_register_settings_group( 'payment', _x( 'Payment', 'settings', 'PDM' ) );
        pdl_register_settings_group( 'payment/main', _x( 'General Settings', 'settings', 'PDM' ), 'payment' );

        pdl_register_settings_group( 'appearance', _x( 'Appearance', 'settings', 'PDM' ) );
        pdl_register_settings_group( 'appearance/main', _x( 'General Settings', 'settings', 'PDM' ), 'appearance' );

        // pdl_register_settings_group( 'licenses', _x( 'Licenses', 'settings', 'PDM' ) );

        pdl_register_settings_group( 'modules', _x( 'Premium Modules', 'settings', 'PDM' ) );
    }

    public static function register_initial_settings() {
        self::settings_general();
        self::settings_listings();
        self::settings_email();
        self::settings_payment();
        self::settings_appearance();
    }

    private static function settings_general() {
        pdl_register_settings_group( 'general/main', _x( 'General Settings', 'settings', 'PDM' ), 'general' );

        // Permalinks.
        pdl_register_settings_group( 'permalink_settings', _x( 'Permalink Settings', 'settings', 'PDM' ), 'general/main' );
        pdl_register_setting( array(
            'id'      => 'permalinks-directory-slug',
            'type'    => 'text',
            'name'    => _x( 'Directory Listings Slug', 'settings', 'PDM' ),
            'default' => 'pdl_listing',
            'group'   => 'permalink_settings',
            'validator' => 'no-spaces,trim,required'
        ) );
        pdl_register_setting( array(
            'id'        => 'permalinks-category-slug',
            'type'      => 'text',
            'name'      => _x( 'Categories Slug', 'settings', 'PDM' ),
            'desc'      => _x( 'The slug can\'t be in use by another term. Avoid "category", for instance.', 'settings', 'PDM' ),
            'default'   => 'pdl_category',
            'group'     => 'permalink_settings',
            'taxonomy'  => PDL_CATEGORY_TAX,
            'validator' => 'taxonomy_slug'
        ) );
        pdl_register_setting( array(
            'id'      => 'permalinks-tags-slug',
            'type'    => 'text',
            'name'    => _x( 'Tags Slug', 'settings', 'PDM' ),
            'desc'    => _x( 'The slug can\'t be in use by another term. Avoid "tag", for instance.', 'settings', 'PDM' ),
            'default' => 'pdl_tag',
            'group' => 'permalink_settings',
            'taxonomy'  => PDL_TAGS_TAX,
            'validator' => 'taxonomy_slug'
        ) );
        pdl_register_setting( array(
            'id'      => 'permalinks-no-id',
            'type'    => 'checkbox',
            'name'    => _x( 'Remove listing ID from directory URLs?', 'settings', 'PDM' ),
            'desc'    => _x( 'Check this setting to remove the ID for better SEO.', 'settings', 'PDM' ),
            'tooltip' => _x( 'Prior to 3.5.1, we included the ID in the listing URL, like "/directory-listing/1809/listing-title".', 'settings', 'PDM' ) . _x(  '<strong>IMPORTANT:</strong> subpages of the main directory page cannot be accesed while this settings is checked.', 'admin settings', 'PDM' ),
            'group' => 'permalink_settings'
        ) );

        // reCAPTCHA.
        pdl_register_settings_group(
            'recaptcha',
            _x( 'reCAPTCHA', 'settings', 'PDM' ),
            'general',
            array(
                'desc' => str_replace( '<a>', '<a href="http://www.google.com/recaptcha" target="_blank" rel="noopener">', _x( 'Need API keys for reCAPTCHA? Get them <a>here</a>.', 'settings', 'PDM' ) )
            )
        );
        pdl_register_setting( array(
            'id'      => 'recaptcha-on',
            'type'    => 'checkbox',
            'name'    => _x( 'Use reCAPTCHA for contact forms', 'settings', 'PDM' ),
            'group'   => 'recaptcha'
        ) );
        pdl_register_setting( array(
            'id'      => 'hide-recaptcha-loggedin',
            'type'    => 'checkbox',
            'name'    => _x( 'Turn off reCAPTCHA for logged in users?', 'settings', 'PDM' ),
            'group' => 'recaptcha'
        ) );
        pdl_register_setting( array(
            'id'      => 'recaptcha-for-submits',
            'type'    => 'checkbox',
            'name'    => _x( 'Use reCAPTCHA for listing submits', 'settings', 'PDM' ),
            'group'   => 'recaptcha'
        ) );
        pdl_register_setting( array(
            'id'      => 'recaptcha-for-flagging',
            'type'    => 'checkbox',
            'name'    => _x( 'Use reCAPTCHA for report listings?', 'settings', 'PDM' ),
            'group' => 'recaptcha'
        ) );
        pdl_register_setting( array(
            'id'      => 'recaptcha-for-comments',
            'type'    => 'checkbox',
            'name'    => _x( 'Use reCAPTCHA for listing comments?', 'settings', 'PDM' ),
            'group' => 'recaptcha'
        ) );
        pdl_register_setting( array(
            'id'      => 'recaptcha-public-key',
            'type'    => 'text',
            'name'    => _x( 'reCAPTCHA Public Key', 'settings', 'PDM' ),
            'default' => '',
            'group' => 'recaptcha'
        ) );
        pdl_register_setting( array(
            'id'      => 'recaptcha-private-key',
            'type'    => 'text',
            'name'    => _x( 'reCAPTCHA Private Key', 'settings', 'PDM' ),
            'default' => '',
            'group' => 'recaptcha'
        ) );

        pdl_register_settings_group( 'registration', _x( 'Registration', 'settings', 'PDM' ), 'general', array( 'desc' => _x( "We expect that a membership plugin supports the 'redirect_to' parameter for the URLs below to work. If the plugin does not support them, these settings will not function as expected. Please contact the membership plugin and ask them to support the WP standard 'redirect_to' query parameter.", 'settings', 'PDM' ) ) );
        pdl_register_setting( array(
            'id'      => 'require-login',
            'type'    => 'checkbox',
            'name'    => _x( 'Require login to post listings?', 'settings', 'PDM' ),
            'default' => 1,
            'group'   => 'registration'
        ) );
        pdl_register_setting( array(
            'id'      => 'enable-key-access',
            'type'    => 'checkbox',
            'name'    => _x( 'Allow anonymous users to edit/manage listings with an access key?', 'settings', 'PDM' ),
            'group'   => 'registration'
        ) );
        pdl_register_setting( array(
            'id'          => 'login-url',
            'type'        => 'text',
            'name'        => _x( 'Login URL', 'settings', 'PDM' ),
            'desc'        => _x( 'Only enter this if using a membership plugin or custom login page.', 'settings', 'PDM' ),
            'placeholder' => _x( 'URL of your membership plugin\'s login page.', 'settings', 'PDM' ),
            'default'     => '',
            'group'       => 'registration'
        ) );
        pdl_register_setting( array(
            'id'          => 'registration-url',
            'type'        => 'text',
            'name'        => _x( 'Registration URL', 'settings', 'PDM' ),
            'desc'        => _x( 'Only enter this if using a membership plugin or custom registration page.', 'settings', 'PDM' ),
            'placeholder' => _x( 'URL of your membership plugin\'s registration page.', 'settings', 'PDM' ),
            'default'     => '',
            'group'       => 'registration'
        ) );
        pdl_register_setting( array(
            'id' => 'create-account-during-submit-mode',
            'type' => 'radio',
            'name'  => _x( 'Allow users to create accounts during listing submit?', 'settings', 'PDM' ),
            'default' => 'disabled',
            'options' => array(
                'disabled' => _x( 'No', 'settings', 'PDM' ),
                'optional' => _x( 'Yes, and make it optional', 'settings', 'PDM' ),
                'required' => _x( 'Yes, and make it required', 'settings', 'PDM' ),
            ),
            'group' => 'registration'
        ) );


        // Terms & Conditions.
        pdl_register_settings_group( 'tos_settings', _x( 'Terms and Conditions', 'settings', 'PDM' ), 'general/main' );
        pdl_register_setting( array(
            'id'      => 'display-terms-and-conditions',
            'type'    => 'checkbox',
            'name'    => _x( 'Display and require user agreement to Terms and Conditions', 'settings', 'PDM' ),
            'group' => 'tos_settings'
        ) );
        pdl_register_setting( array(
            'id'      => 'terms-and-conditions',
            'type'    => 'textarea',
            'name'    => _x( 'Terms and Conditions', 'settings', 'PDM' ),
            'desc'    => _x( 'Enter text or a URL starting with http. If you use a URL, the Terms and Conditions text will be replaced by a link to the appropiate page.', 'settings', 'PDM' ),
            'default' => '',
            'placeholder' => _x( 'Terms and Conditions text goes here.', 'settings', 'PDM' ),
            'group' => 'tos_settings'
        ) );
        // Search.
        pdl_register_settings_group( 'search_settings', _x( 'Directory Search', 'settings', 'PDM' ), 'general/main' );
        pdl_register_setting( array(
            'id'      => 'search-form-in-results',
            'type'    => 'radio',
            'name'    => _x( 'Search form display', 'settings', 'PDM' ),
            'default' => 'above',
            'options' => array(
                'above' => _x( 'Above results', 'admin settings', 'PDM' ),
                'below' => _x( 'Below results', 'admin settings', 'PDM' ),
                'none'  => _x( 'Don\'t show with results', 'admin settings', 'PDM' )
            ),
            'group' => 'search_settings'
        ) );

        $too_many_fields  = '<span class="text-fields-warning pdl-note" style="display: none;">';
        $too_many_fields .= _x( 'You have selected a textarea field to be included in quick searches. Searches involving those fields are very expensive and could result in timeouts and/or general slowness.', 'admin settings', 'PDM' );
        $too_many_fields .= '</span>';

        list( $fields, $text_fields ) = self::get_quicksearch_fields();
        pdl_register_setting( array(
            'id'       => 'quick-search-fields',
            'type'     => 'multicheck',
            'name'     => _x( 'Quick search fields', 'settings', 'PDM' ),
            'desc'     => _x( 'Choosing too many fields for inclusion into Quick Search can result in very slow search performance.', 'settings', 'PDM' ) . $too_many_fields,
            'default'  => array(),
            'multiple' => true,
            'options'  => $fields,
            'group'    => 'search_settings',
            'attrs'    => array(
                'data-text-fields' => json_encode( $text_fields )
            )
        ) );
        pdl_register_setting( array(
            'id'      => 'quick-search-enable-performance-tricks',
            'type'    => 'checkbox',
            'name'    => _x( 'Enable high performance searches?', 'settings', 'PDM' ),
            'desc'    => _x( 'Enabling this makes PDL sacrifice result quality to improve speed. This is helpful if you\'re on shared hosting plans, where database performance is an issue.', 'settings', 'PDM' ),
            'group' => 'search_settings'
        ) );

        // Advanced settings.
        pdl_register_settings_group( 'general/advanced', _x( 'Advanced', 'settings', 'PDM' ), 'general' );

        pdl_register_setting( array(
            'id'      => 'disable-cpt',
            'type'    => 'checkbox',
            'name'    => _x( 'Disable advanced CPT integration?', 'settings', 'PDM' ),
            'group' => 'general/advanced'
        ) );
        pdl_register_setting( array(
            'id'      => 'ajax-compat-mode',
            'type'    => 'checkbox',
            'name'    => _x( 'Enable AJAX compatibility mode?', 'settings', 'PDM' ),
            'desc'    => _x( 'Check this if you are having trouble with PDL, particularly when importing or exporting CSV files.', 'admin settings', 'PDM' )
                         . ' ' . str_replace( '<a>', '<a href="http://plestar.net/support-forum/faq/how-to-check-for-plugin-and-theme-conflicts-with-pdl/" target="_blank" rel="noopener">', _x( 'If this compatibility mode doesn\'t solve your issue, you may be experiencing a more serious conflict. <a>Here is an article</a> about how to test for theme and plugin conflicts with Plestar Directory Listing.', 'settings', 'PDM' ) ),
            'group' => 'general/advanced',
            'on_update' => array( __CLASS__, 'setup_ajax_compat_mode' )
        ) );
        pdl_register_setting( array(
            'id'      => 'disable-submit-listing',
            'type'    => 'checkbox',
            'name'    => _x( 'Disable Frontend Listing Submission?', 'settings', 'PDM' ),
            'group' => 'general/advanced'
        ) );
    }

    private static function get_quicksearch_fields() {
        $fields = array();
        $text_fields = array();

        foreach ( pdl_get_form_fields( 'association=-custom' ) as $field ) {
            if ( in_array( $field->get_association(), array( 'excerpt', 'content' ) ) || 'textarea' == $field->get_field_type_id() ) {
                $text_fields[] = $field->get_id();
            }

            $fields[ $field->get_id() ] = $field->get_label();
        }

        return array( $fields, $text_fields );
    }

    private static function settings_listings() {
        pdl_register_setting( array(
            'id'      => 'listings-per-page',
            'type'    => 'number',
            'name'    => _x( 'Listings per page', 'settings', 'PDM' ),
            'desc'    => _x( 'Number of listings to show per page. Use a value of "0" to show all listings.', 'settings', 'PDM' ),
            'default' => '10',
            'min'     => 0, 'step' => 1,
            'group' => 'listings/main'
        ) );
        pdl_register_setting( array(
            'id'      => 'listing-duration',
            'type'    => 'number',
            'name'    => _x( 'Listing duration for no-fee sites (in days)', 'settings', 'PDM' ),
            'desc'    => _x( 'Use a value of "0" to keep a listing alive indefinitely or enter a number less than 10 years (3650 days).', 'settings', 'PDM' ),
            'default' => '365',
            'min'     => 0, 'step' => 1, 'max' => 3650,
            'group'   => 'listings/main'
        ) );
        pdl_register_setting( array(
            'id'      => 'listing-renewal',
            'type'    => 'checkbox',
            'name'    => _x( 'Turn on listing renewal option?', 'settings', 'PDM' ),
            'default' => true,
            'group' => 'listings/main'
        ) );
        pdl_register_setting( array(
            'id'      => 'listing-link-in-new-tab',
            'type'    => 'checkbox',
            'name'    => _x( 'Open detailed view of listing in new tab?', 'settings', 'PDM' ),
            'default' => false,
            'group' => 'listings/main'
        ) );

        pdl_register_settings_group( 'listings/report', _x( 'Report Listings', 'settings', 'PDM' ), 'listings/main' );
        pdl_register_setting( array(
            'id'      => 'enable-listing-flagging',
            'type'    => 'checkbox',
            'name'    => _x( 'Include button to report listings?', 'settings', 'PDM' ),
            'default' => false,
            'group' => 'listings/report'
        ) );
        pdl_register_setting( array(
            'id'      => 'listing-flagging-register-users',
            'type'    => 'checkbox',
            'name'    => _x( 'Enable report listing for registered users only', 'settings', 'PDM' ),
            'default' => true,
            'group'   => 'listings/report',
            'requirements' => array( 'enable-listing-flagging' )
        ) );
        pdl_register_setting( array(
            'id'      => 'listing-flagging-options',
            'type'    => 'textarea',
            'name'    => _x( 'Report listing option list', 'settings', 'PDM' ),
            'desc'    => _x( 'Form option list to report a listing as inappropriate. One option per line.', 'settings', 'PDM' ),
            'default' => false,
            'group'   => 'listings/report',
            'requirements' => array( 'enable-listing-flagging' )
        ) );

        pdl_register_settings_group( 'listings/contact', _x( 'Contact Form', 'settings', 'PDM' ), 'listings/main' );
        pdl_register_setting( array(
            'id'      => 'show-contact-form',
            'type'    => 'checkbox',
            'name'    => _x( 'Include listing contact form on listing pages?', 'settings', 'PDM' ),
            'desc'    => _x( 'Allows visitors to contact listing authors privately. Authors will receive the messages via email.', 'settings', 'PDM' ),
            'default' => true,
            'group' => 'listings/contact'
        ) );
        pdl_register_setting( array(
            'id'      => 'contact-form-require-login',
            'type'    => 'checkbox',
            'name'    => _x( 'Require login for using the contact form?', 'settings', 'PDM' ),
            'default' => false,
            'group' => 'listings/contact',
            'requirements' => array( 'show-contact-form' )
        ) );
        pdl_register_setting( array(
            'id'      => 'contact-form-daily-limit',
            'type'    => 'number',
            'name'    => _x( 'Maximum number of contact form submits per day', 'settings', 'PDM' ),
            'desc'    => _x( 'Use this to prevent spamming of listing owners. 0 means unlimited submits per day.', 'settings', 'PDM' ),
            'default' => '0',
            'group' => 'listings/contact',
            'requirements' => array( 'show-contact-form' )
        ) );
        pdl_register_setting( array(
            'id'      => 'allow-comments-in-listings',
            'type'    => 'radio',
            'name'    => _x( 'Include comment form on listing pages?', 'settings', 'PDM' ),
            'desc'    => _x( 'PDL uses the standard comment inclusion from WordPress, but most themes only allow for comments on posts, not pages. Some themes handle both. PDL is displayed on a page, so we need a theme that can handle both to show comments. Use the 2nd option if you want to allow comments on listings first, and if that doesn\'t work, try the 3rd option instead.', 'settings', 'PDM' ),
            'default' => get_option( 'pdl-show-comment-form', false) ? 'allow-comments-and-insert-template' : 'do-not-allow-comments',
            'options' => array(
                'do-not-allow-comments'              => _x( 'Do not include comments in listings', 'admin settings', 'PDM' ),
                'allow-comments'                     => _x( 'Include comment form, theme invoked (standard option)', 'admin settings', 'PDM' ),
                'allow-comments-and-insert-template' => _x( "Include comment form, PDL invoked (use only if 2nd option doesn't work)", 'admin settings', 'PDM' )
            ),
            'group' => 'listings/main'
        ) );
        pdl_register_setting( array(
            'id'      => 'show-listings-under-categories',
            'type'    => 'checkbox',
            'name'    => _x( 'Show listings under categories on main page?', 'settings', 'PDM' ),
            'default' => false,
            'group' => 'listings/main'
        ) );
        pdl_register_setting( array(
            'id'      => 'prevent-sticky-on-directory-view',
            'type'    => 'multicheck',
            'name'    => _x( 'Prevent featured (sticky) status on PDL pages?', 'settings', 'PDM' ),
            'desc'    => _x( 'Prevents featured listings from floating to the top of the selected page.', 'settings', 'PDM' ),
            'default' => array(),
            'options' => array(
                'main'          => _x( 'Directory view.', 'admin settings', 'PDM' ),
                'all_listings'  => _x( 'All Listings view.', 'admin settings', 'PDM' ),
                'show_category' => _x( 'Category view.', 'admin settings', 'PDM' ),
                'search'        => _x( 'Search view.', 'admin settings', 'PDM' ),
            ),
            'group' => 'listings/main'
        ) );
        pdl_register_setting( array(
            'id'      => 'status-on-uninstall',
            'type'    => 'radio',
            'name'    => _x( 'Status of listings upon uninstalling plugin', 'settings', 'PDM' ),
            'default' => 'trash',
            'options' => array(
                'draft' => _x( 'Draft', 'post status' ),
                'trash' => _x( 'Trash', 'post status' )
            ),
            'group' => 'listings/main'
        ) );
        pdl_register_setting( array(
            'id'      => 'deleted-status',
            'type'    => 'radio',
            'name'    => _x( 'Status of deleted listings', 'settings', 'PDM' ),
            'default' => 'trash',
            'options' => array(
                'draft' => _x( 'Draft', 'post status' ),
                'trash' => _x( 'Trash', 'post status' )
            ),
            'group' => 'listings/main'
        ) );
        pdl_register_setting( array(
            'id'      => 'submit-instructions',
            'type'    => 'textarea',
            'name'    => _x( 'Submit Listing instructions message', 'settings', 'PDM' ),
            'desc'    => _x( 'This text is displayed at the first page of the Submit Listing process for Plestar Directory Listing. You can use it for instructions about filling out the form or anything you want to tell users before they get started.', 'settings', 'PDM' ),
            'default' => '',
            'group' => 'listings/main'
        ) );

        pdl_register_settings_group( 'listings/post_category', _x( 'Post/Category Settings', 'settings', 'PDM' ), 'listings/main' );
        pdl_register_setting( array(
            'id'      => 'new-post-status',
            'type'    => 'radio',
            'name'    => _x( 'Default new post status', 'settings', 'PDM' ),
            'default' => 'pending',
            'options' => array(
                'publish' => _x( 'Published', 'post status' ),
                'pending' => _x( 'Pending', 'post status' )
            ),
            'group' => 'listings/post_category'
        ) );
        pdl_register_setting( array(
            'id'      => 'edit-post-status',
            'type'    => 'radio',
            'name'    => _x( 'Edit post status', 'settings', 'PDM' ),
            'default' => 'publish',
            'options' => array(
                'publish' => _x( 'Published', 'post status' ),
                'pending' => _x( 'Pending', 'post status' )
            ),
            'group' => 'listings/post_category'
        ) );
        pdl_register_setting( array(
            'id'      => 'categories-order-by',
            'type'    => 'radio',
            'name'    => _x( 'Order categories list by', 'settings', 'PDM' ),
            'default' => 'name',
            'options' => array(
                'name'  => _x( 'Name', 'admin settings', 'PDM' ),
                'slug'  => _x( 'Slug', 'admin settings', 'PDM' ),
                'count' => _x( 'Listing Count', 'admin settings', 'PDM' )
            ),
            'group' => 'listings/post_category'
        ) );
        pdl_register_setting( array(
            'id'      => 'categories-sort',
            'type'    => 'radio',
            'name'    => _x( 'Sort order for categories', 'settings', 'PDM' ),
            'default' => 'ASC',
            'options' => array(
                'ASC'  => _x('Ascending', 'admin settings', 'PDM'),
                'DESC' => _x('Descending', 'admin settings', 'PDM')
            ),
            'group' => 'listings/post_category'
        ) );
        pdl_register_setting( array(
            'id'      => 'show-category-post-count',
            'type'    => 'checkbox',
            'name'    => _x( 'Show category post count?', 'settings', 'PDM' ),
            'default' => true,
            'group' => 'listings/post_category'
        ) );
        pdl_register_setting( array(
            'id'      => 'hide-empty-categories',
            'type'    => 'checkbox',
            'name'    => _x( 'Hide empty categories?', 'settings', 'PDM' ),
            'default' => false,
            'group' => 'listings/post_category'
        ) );
        pdl_register_setting( array(
            'id'      => 'show-only-parent-categories',
            'type'    => 'checkbox',
            'name'    => _x( 'Show only parent categories in category list?', 'settings', 'PDM' ),
            'default' => false,
            'group' => 'listings/post_category'
        ) );

        pdl_register_settings_group( 'listings/sorting', _x( 'Listings Sorting', 'settings', 'PDM' ), 'listings/main' );

        $msg = _x( 'Fee Plan Custom Order can be changed under <a>Manage Fees</a>', 'admin settings', 'PDM' );
        $msg = str_replace( '<a>', '<a href="' . esc_url( admin_url( 'admin.php?page=pdl-admin-fees' ) ) . '">', $msg );
        pdl_register_setting( array(
            'id'      => 'listings-order-by',
            'type'    => 'select',
            'name'    => _x( 'Order directory listings by', 'settings', 'PDM' ),
            'desc'    => $msg,
            'default' => 'title',
            'options' => array(
                'title'      => _x( 'Title', 'admin settings', 'PDM' ),
                'author'     => _x( 'Author', 'admin settings', 'PDM' ),
                'date'       => _x( 'Date posted', 'admin settings', 'PDM' ),
                'modified'   => _x( 'Date last modified', 'admin settings', 'PDM' ),
                'rand'       => _x( 'Random', 'admin settings', 'PDM' ),
                'paid'       => _x( 'Paid first then free. Inside each group by date.', 'admin settings', 'PDM' ),
                'paid-title' => _x( 'Paid first then free. Inside each group by title.', 'admin settings', 'PDM' ),
                'plan-order-date' => _x( 'Fee Plan Custom Order, then Date', 'admin settings', 'PDM' ),
                'plan-order-title' => _x( 'Fee Plan Custom Order, then Title', 'admin settings', 'PDM' )
            ),
            'group'  => 'listings/sorting'
        ) );
        pdl_register_setting( array(
            'id'      => 'listings-sort',
            'type'    => 'radio',
            'name'    => _x( 'Sort directory listings by', 'settings', 'PDM' ),
            'desc'    => _x( 'Ascending for ascending order A-Z, Descending for descending order Z-A', 'settings', 'PDM' ),
            'default' => 'ASC',
            'options' => array(
                'ASC'  => _x('Ascending', 'admin settings', 'PDM'),
                'DESC' => _x('Descending', 'admin settings', 'PDM')
            ),
            'group'  => 'listings/sorting'
        ) );
        pdl_register_setting( array(
            'id'      => 'listings-sortbar-enabled',
            'type'    => 'checkbox',
            'name'    => _x( 'Enable sort bar?', 'settings', 'PDM' ),
            'default' => false,
            'group'  => 'listings/sorting'
        ) );
        pdl_register_setting( array(
            'id'      => 'listings-sortbar-fields',
            'type'    => 'multicheck',
            'name'    => _x( 'Sortbar Fields', 'settings', 'PDM' ),
            'default' => array(),
            'options' => pdl_sortbar_get_field_options(),
            'group'  => 'listings/sorting',
            'requirements' => array( 'listings-sortbar-enabled' )
        ) );
    }

    private static function settings_appearance() {
        // Display Options.
        pdl_register_settings_group( 'display_options', _x( 'Directory Display Options', 'settings', 'PDM' ), 'appearance/main' );
        pdl_register_setting( array(
            'id'      => 'show-submit-listing',
            'type'    => 'checkbox',
            'name'    => _x( 'Show the "Submit listing" button.', 'settings', 'PDM' ),
            'default' => true,
            'group' => 'display_options',
            'requirements' => array( '!disable-submit-listing' )
        ) );
        pdl_register_setting( array(
            'id'      => 'show-search-listings',
            'type'    => 'checkbox',
            'name'    => _x( 'Show "Search listings".', 'settings', 'PDM' ),
            'default' => true,
            'group' => 'display_options'
        ) );
        pdl_register_setting( array(
            'id'      => 'show-view-listings',
            'type'    => 'checkbox',
            'name'    => _x( 'Show the "View Listings" button.', 'settings', 'PDM' ),
            'default' => true,
            'group' => 'display_options'
        ) );
        pdl_register_setting( array(
            'id'      => 'show-directory-button',
            'type'    => 'checkbox',
            'name'    => _x( 'Show the "Directory" button.', 'settings', 'PDM' ),
            'default' => true,
            'group' => 'display_options'
        ) );

        // Themes.
        pdl_register_settings_group( 'themes', _x( 'Theme Settings', 'settings', 'PDM' ), 'appearance', array( 'desc' => str_replace( '<a>', '<a href="' . admin_url( 'admin.php?page=pdl-themes' ) . '">', _x( 'You can manage your themes on <a>Directory Themes</a>.', 'admin settings', 'PDM' ) ) ) );

        pdl_register_setting( array(
            'id'      => 'themes-button-style',
            'type'    => 'radio',
            'name'    => _x( 'Theme button style', 'settings', 'PDM' ),
            'default' => 'theme',
            'options' => array(
                'theme' => _x( 'Use the PDL theme style for PDL buttons', 'admin settings', 'PDM' ),
                'none'  =>_x( 'Use the WP theme style for PDL buttons', 'admin settings', 'PDM' )
            ),
            'group' => 'themes'
        ) );
        pdl_register_setting( array(
            'id'      => 'include-button-styles',
            'type'    => 'checkbox',
            'name'    => _x( 'Include CSS rules to give their own style to View, Edit and Delete buttons?', 'settings', 'PDM' ),
            'default' => 1,
            'group' => 'themes'
        ) );

        // Image.
        pdl_register_settings_group( 'appearance/image', _x( 'Image', 'settings', 'PDM' ), 'appearance' );
        pdl_register_settings_group( 'images/general', _x( 'Image Settings', 'settings', 'PDM' ), 'appearance/image', array( 'desc' => 'Any changes to these settings will affect new listings only.  Existing listings will not be affected.  If you wish to change existing listings, you will need to re-upload the image(s) on that listing after changing things here.' ) );
        pdl_register_setting( array(
            'id'      => 'allow-images',
            'type'    => 'checkbox',
            'name'    => _x( 'Allow images?', 'settings', 'PDM' ),
            'default' => true,
            'group'   => 'images/general'
        ) );
        pdl_register_setting( array(
            'id'      => 'image-min-filesize',
            'type'    => 'number',
            'min'     => 0, 'step' => 1,
            'name'    => _x( 'Min Image File Size (KB)', 'settings', 'PDM' ),
            'default' => '0',
            'group'   => 'images/general'
        ) );
        pdl_register_setting( array(
            'id'      => 'image-max-filesize',
            'type'    => 'number',
            'min'     => 0, 'step' => 1,
            'name'    => _x( 'Max Image File Size (KB)', 'settings', 'PDM' ),
            'default' => '10000',
            'group'   => 'images/general'
        ) );
        pdl_register_setting( array(
            'id'      => 'image-min-width',
            'type'    => 'number',
            'min'     => 0, 'step' => 1,
            'name'    => _x( 'Min image width (px)', 'settings', 'PDM' ),
            'default' => '0',
            'group'   => 'images/general'
        ) );
        pdl_register_setting( array(
            'id'      => 'image-min-height',
            'type'    => 'number',
            'name'    => _x( 'Min image height (px)', 'settings', 'PDM' ),
            'default' => '0',
            'min'     => 0, 'step' => 1,
            'group'   => 'images/general'
        ) );
        pdl_register_setting( array(
            'id'      => 'image-max-width',
            'type'    => 'number',
            'min'     => 0, 'step' => 1,
            'name'    => _x( 'Max image width (px)', 'settings', 'PDM' ),
            'default' => '500',
            'group'   => 'images/general'
        ) );
        pdl_register_setting( array(
            'id'      => 'image-max-height',
            'type'    => 'number',
            'min'     => 0, 'step' => 1,
            'name'    => _x( 'Max image height (px)', 'settings', 'PDM' ),
            'default' => '500',
            'group'   => 'images/general'
        ) );
        pdl_register_setting( array(
            'id'      => 'use-thickbox',
            'type'    => 'checkbox',
            'min'     => 0, 'step' => 1,
            'name'    => _x( 'Turn on thickbox/lightbox?', 'settings', 'PDM' ),
            'desc'    => _x( 'Uncheck if it conflicts with other elements or plugins installed on your site', 'settings', 'PDM' ),
            'default' => false,
            'group'   => 'images/general'
        ) );

        pdl_register_settings_group( 'image/thumbnails', _x( 'Thumbnails', 'settings', 'PDM' ), 'appearance/image' );
        pdl_register_setting( array(
            'id'      => 'thumbnail-width',
            'type'    => 'number',
            'min'     => 0, 'step' => 1,
            'name'    => _x( 'Thumbnail width (px)', 'settings', 'PDM' ),
            'default' => '150',
            'group'   => 'image/thumbnails'
        ) );
        pdl_register_setting( array(
            'id'      => 'thumbnail-height',
            'type'    => 'number',
            'min'     => 0, 'step' => 1,
            'name'    => _x( 'Thumbnail height (px)', 'settings', 'PDM' ),
            'default' => '150',
            'group'   => 'image/thumbnails'
        ) );
        pdl_register_setting( array(
            'id'      => 'thumbnail-crop',
            'type'    => 'checkbox',
            'name'    => _x( 'Crop thumbnails to exact dimensions?', 'settings', 'PDM' ),
            'desc'    => _x( 'When enabled images will match exactly the dimensions above but part of the image may be cropped out. If disabled, image thumbnails will be resized to match the specified width and their height will be adjusted proportionally. Depending on the uploaded images, thumbnails may have different heights.', 'settings', 'PDM' ),
            'default' => false,
            'group'   => 'image/thumbnails'
        ) );

        pdl_register_settings_group( 'image/listings', _x( 'Listings', 'settings', 'PDM' ), 'appearance/image' );
        pdl_register_setting( array(
            'id'      => 'enforce-image-upload',
            'type'    => 'checkbox',
            'name'    => _x( 'Enforce image upload on submit/edit?', 'settings', 'PDM' ),
            'default' => false,
            'group'   => 'image/listings'
        ) );
        pdl_register_setting( array(
            'id'      => 'free-images',
            'type'    => 'number',
            'name'    => _x( 'Number of free images', 'settings', 'PDM' ),
            'default' => '2',
            'min'     => 0, 'step' => 1,
            'desc'    => str_replace( '<a>', '<a href="' . admin_url( 'admin.php?page=pdl-admin-fees' ) . '">', _x( 'For paid listing images, configure that by adding or editing a <a>Fee Plan</a> instead of this setting, which is ignored for paid listings.', 'admin settings', 'PDM' ) ),
            'group'   => 'image/listings'
        ) );
        pdl_register_setting( array(
            'id'      => 'use-default-picture',
            'type'    => 'checkbox',
            'name'    => _x( 'Use default picture for listings with no picture?', 'settings', 'PDM' ),
            'default' => true,
            'group'   => 'image/listings'
        ) );
        pdl_register_setting( array(
            'id'      => 'show-thumbnail',
            'type'    => 'checkbox',
            'name'    => _x( 'Show Thumbnail on main listings page?', 'settings', 'PDM' ),
            'default' => true,
            'group'   => 'image/listings'
        ) );
    }

    private static function settings_payment() {
        pdl_register_setting( array(
            'id'      => 'fee-order',
            'type'    => 'silent',
            'name'    => _x( 'Fee Order', 'settings', 'PDM' ),
            'default' => array( 'method' => 'label', 'order' => 'asc' ),
            'group'   => 'payment/main'
        ) );

        pdl_register_setting( array(
            'id'      => 'payments-on',
            'type'    => 'checkbox',
            'name'    => _x( 'Turn On payments?', 'settings', 'PDM' ),
            'default' => false,
            'group'   => 'payment/main'
        ) );
        pdl_register_setting( array(
            'id'      => 'payments-test-mode',
            'type'    => 'checkbox',
            'name'    => _x( 'Put payment gateways in test mode?', 'settings', 'PDM' ),
            'default' => true,
            'group'   => 'payment/main',
            'requirements' => array( 'payments-on' )
        ) );
        pdl_register_setting( array(
            'id'      => 'payments-use-https',
            'type'    => 'checkbox',
            'name'    => _x( 'Perform checkouts on the secure (HTTPS) version of your site?', 'settings', 'PDM' ),
            'desc'    => _x( 'Recommended for added security. For this to work you need to enable HTTPS on your server and obtain an SSL certificate.', 'settings', 'PDM' ),
            'default' => false,
            'group'   => 'payment/main',
            'requirements' => array( 'payments-on' )
        ) );
        pdl_register_setting( array(
            'id'      => 'currency',
            'type'    => 'select',
            'name'    => _x( 'Currency Code', 'settings', 'PDM' ),
            'default' => 'USD',
            'options' => array(
                'AUD' => _x('Australian Dollar (AUD)', 'admin settings', 'PDM'),
                'BRL' => _x('Brazilian Real (BRL)', 'admin settings', 'PDM'),
                'CAD' => _x('Canadian Dollar (CAD)', 'admin settings', 'PDM'),
                'CZK' => _x('Czech Koruna (CZK)', 'admin settings', 'PDM'),
                'DKK' => _x('Danish Krone (DKK)', 'admin settings', 'PDM'),
                'EUR' => _x('Euro (EUR)', 'admin settings', 'PDM'),
                'HKD' => _x('Hong Kong Dollar (HKD)', 'admin settings', 'PDM'),
                'HUF' => _x('Hungarian Forint (HUF)', 'admin settings', 'PDM'),
                'ILS' => _x('Israeli New Shequel (ILS)', 'admin settings', 'PDM'),
                'JPY' => _x('Japanese Yen (JPY)', 'admin settings', 'PDM'),
                'MAD' => _x('Moroccan Dirham (MAD)', 'admin settings', 'PDM'),
                'MYR' => _x('Malasian Ringgit (MYR)', 'admin settings', 'PDM'),
                'MXN' => _x('Mexican Peso (MXN)', 'admin settings', 'PDM'),
                'NOK' => _x('Norwegian Krone (NOK)', 'admin settings', 'PDM'),
                'NZD' => _x('New Zealand Dollar (NZD)', 'admin settings', 'PDM'),
                'PHP' => _x('Philippine Peso (PHP)', 'admin settings', 'PDM'),
                'PLN' => _x('Polish Zloty (PLN)', 'admin settings', 'PDM'),
                'GBP' => _x('Pound Sterling (GBP)', 'admin settings', 'PDM'),
                'SGD' => _x('Singapore Dollar (SGD)', 'admin settings', 'PDM'),
                'SEK' => _x('Swedish Krona (SEK)', 'admin settings', 'PDM'),
                'CHF' => _x('Swiss Franc (CHF)', 'admin settings', 'PDM'),
                'TWD' => _x('Taiwan Dollar (TWD)', 'admin settings', 'PDM'),
                'THB' => _x('Thai Baht (THB)', 'admin settings', 'PDM'),
                'TRY' => _x('Turkish Lira (TRY)', 'admin settings', 'PDM'),
                'USD' => _x('U.S. Dollar (USD)', 'admin settings', 'PDM')
            ),
            'group'   => 'payment/main',
            'requirements' => array( 'payments-on' )
        ) );
        pdl_register_setting( array(
            'id'      => 'currency-symbol',
            'type'    => 'text',
            'name'    => _x( 'Currency Symbol', 'settings', 'PDM' ),
            'default' => '$',
            'group'   => 'payment/main',
            'requirements' => array( 'payments-on' )
        ) );
        pdl_register_setting( array(
            'id'      => 'currency-symbol-position',
            'type'    => 'radio',
            'name'    => _x( 'Currency symbol display', 'settings', 'PDM' ),
            'default' => 'left',
            'options' => array(
                'left'  => _x( 'Show currency symbol on the left', 'admin settings', 'PDM' ),
                'right' =>_x( 'Show currency symbol on the right', 'admin settings', 'PDM'),
                'none'  => _x( 'Do not show currency symbol', 'admin settings', 'PDM' )
            ),
            'group'   => 'payment/main',
            'requirements' => array( 'payments-on' )
        ) );
        pdl_register_setting( array(
            'id'      => 'payment-message',
            'type'    => 'textarea',
            'name'    => _x( 'Thank you for payment message', 'settings', 'PDM' ),
            'default' => _x( 'Thank you for your payment. Your payment is being verified and your listing reviewed. The verification and review process could take up to 48 hours.', 'admin settings', 'PDM' ),
            'group'   => 'payment/main',
            'requirements' => array( 'payments-on' )
        ) );
        pdl_register_setting( array(
            'id'      => 'payment-abandonment',
            'type'    => 'checkbox',
            'name'    => _x( 'Ask users to come back for abandoned payments?', 'settings', 'PDM' ),
            'desc'    => _x( 'An abandoned payment is when a user attempts to place a listing and gets to the end, but fails to complete their payment for the listing. This results in listings that look like they failed, when the user simply didn\'t complete the transaction.  PDL can remind them to come back and continue.', 'settings', 'PDM' ),
            'default' => false,
            'group'   => 'payment/main',
            'requirements' => array( 'payments-on' )
        ) );
        pdl_register_setting( array(
            'id'      => 'payment-abandonment-threshold',
            'type'    => 'number',
            'name'    => _x( 'Listing abandonment threshold (hours)', 'settings', 'PDM' ),
            'desc'    => str_replace( '<a>', '<a href="' . admin_url( 'admin.php?page=pdl_settings&tab=email' ) . '#email-templates-payment-abandoned">', _x( 'Listings with pending payments are marked as abandoned after this time. You can also <a>customize the e-mail</a> users receive.', 'admin settings', 'PDM' ) ),
            'default' => '24',
            'min'     => 0, 'step' => 1,
            'group'   => 'payment/main',
            'requirements' => array( 'payment-abandonment' )
        ) );
    }

    private static function settings_email() {
        pdl_register_settings_group( 'email/main/general', _x( 'General Settings', 'settings', 'PDM' ), 'email/main' );
        pdl_register_setting( array(
            'id'      => 'override-email-blocking',
            'type'    => 'checkbox',
            'name'    => _x( 'Display email address fields publicly?', 'settings', 'PDM' ),
            'desc'    => _x( 'Shows the email address of the listing owner to all web users. NOT RECOMMENDED as this increases spam to the address and allows spam bots to harvest it for future use.', 'settings', 'PDM' ),
            'default' => false,
            'group'   => 'email/main/general'
        ) );
        pdl_register_setting( array(
            'id'      => 'listing-email-mode',
            'type'    => 'radio',
            'name'    => _x( 'How to determine the listing\'s email address?', 'settings', 'PDM' ),
            'desc'    => _x( 'This affects emails sent to listing owners via contact forms or when their listings expire.', 'settings', 'PDM' ),
            'default' => 'field',
            'options' => array(
                'field' => _x( 'Try listing\'s email field first, then author\'s email.', 'admin settings', 'PDM' ),
                'user'  => _x( 'Try author\'s email first and then listing\'s email field.', 'admin settings', 'PDM' )
            ),
            'group'   => 'email/main/general'
        ) );
        pdl_register_setting( array(
            'id'      => 'listing-email-content-type',
            'type'    => 'radio',
            'name'    => _x( 'Email Content-Type header', 'settings', 'PDM' ),
            'desc'    => _x( 'Use this setting to control the format of the emails explicitly. Some plugins for email do not correctly support Content Type unless explicitly set, you can do that here. If you\'re unsure, try "HTML", "Plain" and then "Both".', 'settings', 'PDM' ),
            'default' => 'html',
            'options' => array(
                'plain' => _x( 'Plain (text/plain)', 'admin settings', 'PDM' ),
                'html'  => _x( 'HTML (text/html)', 'admin settings', 'PDM' ),
                'both'  => _x( 'Both (multipart/alternative)', 'admin settings', 'PDM' )
            ),
            'group'   => 'email/main/general'
        ) );

        pdl_register_settings_group( 'email_notifications', _x( 'E-Mail Notifications', 'settings', 'PDM' ), 'email/main' );
        pdl_register_setting( array(
            'id'      => 'admin-notifications',
            'type'    => 'multicheck',
            'name'    => _x( 'Notify admin via e-mail when...', 'settings', 'PDM' ),
            'default' => array(),
            'options' => array(
                'new-listing'     => _x( 'A new listing is submitted.', 'admin settings', 'PDM' ),
                'listing-edit'    => _x( 'A listing is edited.', 'admin settings', 'PDM' ),
                'renewal'         => _x( 'A listing expires.', 'admin settings', 'PDM' ),
                'after_renewal'   => _x( 'A listing is renewed.', 'admin settings', 'PDM' ),
                'flagging_listing'=> _x( 'A listing has been reported as inappropriate.', 'admin settings', 'PDM' ),
                'listing-contact' => _x( 'A contact message is sent to a listing\'s owner.', 'admin settings', 'PDM' )
            ),
            'group' => 'email_notifications'
        ) );
        pdl_register_setting( array(
            'id'      => 'admin-notifications-cc',
            'type'    => 'text',
            'name'    => _x( 'CC this e-mail address too', 'settings', 'PDM' ),
            'group' => 'email_notifications'
        ) );

        $settings_url = admin_url( 'admin.php?page=pdl_settings&tab=email&subtab=email_templates' );
        $description = _x( 'You can modify the text template used for most of these e-mails in the <templates-link>Templates</templates-link> tab.', 'settings', 'PDM' );
        $description = str_replace( '<templates-link>', '<a href="' . $settings_url . '">', $description );
        $description = str_replace( '</templates-link>', '</a>', $description );

        pdl_register_setting( array(
            'id'      => 'user-notifications',
            'type'    => 'multicheck',
            'name'    => _x( 'Notify users via e-mail when...', 'settings', 'PDM' ),
            'desc'    => $description,
            'default' => array( 'new-listing', 'listing-published' ),
            'options' => array(
                'new-listing'       => _x( 'Their listing is submitted.', 'admin settings', 'PDM' ),
                'listing-published' => _x( 'Their listing is approved/published.', 'admin settings', 'PDM' ),
            ),
            'group' => 'email_notifications'
        ) );

        pdl_register_settings_group( 'email_templates', _x( 'Templates', 'settings', 'PDM' ), 'email' );
        pdl_register_setting( array(
            'id'           => 'email-confirmation-message',
            'type'         => 'email_template',
            'name'         => _x( 'Email confirmation message', 'settings', 'PDM' ),
            'desc'         => _x( 'Sent after a listing has been submitted.', 'settings', 'PDM' ),
            'default'      => array(
                'subject' => '[[site-title]] Listing "[listing]" received',
                'body'    => 'Your submission \'[listing]\' has been received and it\'s pending review. This review process could take up to 48 hours.'
            ),
            'placeholders' => array(
                'listing' => array( _x( 'Listing\'s title', 'admin settings', 'PDM' ) )
            ),
            'group' => 'email_templates'
        ) );
        pdl_register_setting( array(
            'id'      => 'email-templates-listing-published',
            'type'    => 'email_template',
            'name'    => _x( 'Listing published message', 'settings', 'PDM' ),
            'desc'    => _x( 'Sent when the listing has been published or approved by an admin.', 'settings', 'PDM' ),
            'default' => array(
                'subject' => '[[site-title]] Listing "[listing]" published',
                'body'    => _x( 'Your listing "[listing]" is now available at [listing-url] and can be viewed by the public.', 'admin settings', 'PDM' )
            ),
            'placeholders' => array(
                'listing'     => _x( 'Listing\'s title', 'admin settings', 'PDM' ),
                'listing-url' => _x( 'Listing\'s URL', 'admin settings', 'PDM' )
            ),
            'group' => 'email_templates'
        ) );
        pdl_register_setting( array(
            'id'      => 'email-templates-contact',
            'type'    => 'email_template',
            'name'    => _x( 'Listing Contact Message', 'settings', 'PDM' ),
            'desc'    => _x( 'Sent to listing owners when someone uses the contact form on their listing pages.', 'settings', 'PDM' ),
            'default' => array(
                'subject' => '[[site-title]] Contact via "[listing]"',
                'body'    => '' .
                             sprintf( _x( 'You have received a reply from your listing at %s.', 'contact email', 'PDM' ), '[listing-url]' ) . "\n\n" .
                             sprintf( _x( 'Name: %s', 'contact email', 'PDM' ), '[name]' ) . "\n" .
                             sprintf( _x( 'E-Mail: %s', 'contact email', 'PDM' ), '[email]' ) . "\n" .
                             _x( 'Message:', 'contact email', 'PDM' ) . "\n" .
                             '[message]' . "\n\n" .
                             sprintf( _x( 'Time: %s', 'contact email', 'PDM' ), '[date]' )
            ),
            'placeholders' => array(
                'listing-url' => 'Listing\'s URL',
                'listing' => 'Listing\'s title',
                'name' => 'Sender\'s name',
                'email' => 'Sender\'s e-mail address',
                'message' => 'Contact message',
                'date' => 'Date and time the message was sent'
            ),
            'group' => 'email_templates'
        ) );

        pdl_register_setting( array(
            'id'      => 'email-templates-payment-abandoned',
            'type'    => 'email_template',
            'name'    => _x( 'Payment abandoned reminder message', 'settings', 'PDM' ),
            'desc'    => _x( 'Sent some time after a pending payment is abandoned by users.', 'settings', 'PDM' ),
            'default' => array(
                'subject' => '[[site-title]] Pending payment for "[listing]"',
                'body'    => '
        Hi there,

        We noticed that you tried submitting a listing on [site-link] but didn\'t finish
        the process.  If you want to complete the payment and get your listing
        included, just click here to continue:

        [link]

        If you have any issues, please contact us directly by hitting reply to this
        email!

        Thanks,
        - The Administrator of [site-title]'
            ),
            'placeholders' => array(
                'listing' => _x( 'Listing\'s title', 'admin settings', 'PDM' ),
                'link' => _x( 'Checkout URL link', 'admin settings', 'PDM' )
            ),
            'group' => 'email_templates'
        ) );

        // pdl_register_setting( array(
        //     'id'   => 'email-renewal-reminders_settings',
        //     'type' => 'section',
        //     'name' => _x( 'Expiration/Renewal Notices', 'settings', 'PDM' ),
        //     'desc' =>  _x( 'You can configure here the text for the expiration/renewal emails and also how long before/after expiration/renewal they are sent.', 'settings', 'PDM' ),
        //     'tab' => 'email'
        // ) );
        pdl_register_setting( array(
            'id'      => 'expiration-notices',
            'type'    => 'expiration_notices',
            'name'    => _x( 'E-Mail Notices', 'settings', 'PDM' ),
            'default' => self::get_default_expiration_notices(),
            'group' => 'email_templates',
            'validator' => array( __class__, 'validate_expiration_notices' )
        ) );
    }

    public static function get_default_expiration_notices() {
        $notices = array();

        /* renewal-pending-message, non-recurring only */
        $notices[] = array(
            'event' => 'expiration',
            'relative_time' => '+5 days', /* renewal-email-threshold, def: 5 days */
            'listings' => 'non-recurring',
            'subject' => '[[site-title]] [listing] - Your listing is about to expire',
            'body' => 'Your listing "[listing]" is about to expire at [site]. You can renew it here: [link].'
        );
        //         array( 'placeholders' => array( 'listing' => _x( 'Listing\'s name (with link)', 'settings', 'PDM' ),
        //                                         'author' => _x( 'Author\'s name', 'settings', 'PDM' ),
        //                                         'expiration' => _x( 'Expiration date', 'settings', 'PDM' ),
        //                                         'category' => _x( 'Category that is going to expire', 'settings', 'PDM' ),
        //                                         'link' => _x( 'Link to renewal page', 'settings', 'PDM' ),
        //                                         'site' => _x( 'Link to your site', 'settings', 'PDM' )  ) )

        /* listing-renewal-message, non-recurring only */
        $notices[] = array(
            'event' => 'expiration',
            'relative_time' => '0 days', /* at time of expiration */
            'listings' => 'non-recurring',
            'subject' => 'Your listing on [site-title] expired',
            'body' => "Your listing \"[listing]\" in category [category] expired on [expiration]. To renew your listing click the link below.\n[link]"
        );
        //                     array( 'placeholders' => array( 'listing' => _x( 'Listing\'s name (with link)', 'settings', 'PDM' ),
        //                                                     'author' => _x( 'Author\'s name', 'settings', 'PDM' ),
        //                                                     'expiration' => _x( 'Expiration date', 'settings', 'PDM' ),
        //                                                     'category' => _x( 'Category that expired', 'settings', 'PDM' ),
        //                                                     'link' => _x( 'Link to renewal page', 'settings', 'PDM' ),
        //                                                     'site' => _x( 'Link to your site', 'settings', 'PDM' )  ) )

        /* renewal-reminder-message, both recurring and non-recurring */
        $notices[] = array(
            'event' => 'expiration',
            'relative_time' => '-5 days', /* renewal-reminder-threshold */
            'listings' => 'both',
            'subject' => '[[site-title]] [listing] - Expiration reminder',
            'body' => "Dear Customer\nWe've noticed that you haven't renewed your listing \"[listing]\" for category [category] at [site] and just wanted to remind you that it expired on [expiration]. Please remember you can still renew it here: [link]."
        );
        //                     array( 'placeholders' => array( 'listing' => _x( 'Listing\'s name (with link)', 'settings', 'PDM' ),
        //                                                     'author' => _x( 'Author\'s name', 'settings', 'PDM' ),
        //                                                     'expiration' => _x( 'Expiration date', 'settings', 'PDM' ),
        //                                                     'category' => _x( 'Category that expired', 'settings', 'PDM' ),
        //                                                     'link' => _x( 'Link to renewal page', 'settings', 'PDM' ),
        //                                                     'site' => _x( 'Link to your site', 'settings', 'PDM' )  ) )

        /* listing-autorenewal-notice, recurring only, controlled by the send-autorenewal-expiration-notice setting */
        $notices[] = array(
            'event' => 'expiration',
            'relative_time' => '+5 days' /*  renewal-email-threshold, def: 5 days */,
            'listings' => 'recurring',
            'subject' => '[[site-title]] [listing] - Renewal reminder',
            'body' => "Hey [author],\n\nThis is just to remind you that your listing [listing] is going to be renewed on [expiration] for another period.\nIf you want to review or cancel your subscriptions please visit [link].\n\nIf you have any questions, contact us at [site]."
        );
        //                     array( 'placeholders' => array( 'listing' => _x( 'Listing\'s name (with link)', 'settings', 'PDM' ),
        //                                                     'author' => _x( 'Author\'s name', 'settings', 'PDM' ),
        //                                                     'date' => _x( 'Renewal date', 'settings', 'PDM' ),
        //                                                     'category' => _x( 'Category that is going to be renewed', 'settings', 'PDM' ),
        //                                                     'site' => _x( 'Link to your site', 'settings', 'PDM' ),
        //                                                     'link' => _x( 'Link to manage subscriptions', 'settings', 'PDM' ) ) )

        /* listing-autorenewal-message, after IPN notification of renewal of recurring */
        $notices[] = array(
            'event' => 'renewal',
            'relative_time' => '0 days',
            'listings' => 'recurring',
            'subject' => '[[site-title]] [listing] renewed',
            'body' => "Hey [author],\n\nThanks for your payment. We just renewed your listing [listing] on [payment_date] for another period.\n\nIf you have any questions, contact us at [site]."
        );
        // $replacements['listing'] = sprintf( '<a href="%s">%s</a>',
        //                                     get_permalink( $payment->get_listing_id() ),
        //                                     get_the_title( $payment->get_listing_id() ) );
        // $replacements['author'] = get_the_author_meta( 'display_name', get_post( $payment->get_listing_id() )->post_author );
        // $replacements['category'] = pdl_get_term_name( $recurring_item->rel_id_1 );
        // $replacements['date'] = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
        //                                    strtotime( $payment->get_processed_on() ) );
        // $replacements['site'] = sprintf( '<a href="%s">%s</a>',
        //                                  get_bloginfo( 'url' ),
        //                                  get_bloginfo( 'name' ) );
        //


        return $notices;
    }

    public static function validate_expiration_notices( $value ) {
        // We remove notices with no subject and no content.
        foreach ( array_keys( $value ) as $notice_id ) {
            $value[ $notice_id ] = array_map( 'trim', $value[ $notice_id ] );

            if ( empty( $value[ $notice_id ]['subject'] ) && empty( $value[ $notice_id ]['content'] ) ) {
                unset( $value[ $notice_id ] );
            }
        }

        // We make sure that there's always one notice applying to the expiration time of non-recurring listings.
        $found = false;
        foreach ( $value as $notice_id => $notice ) {
            if ( 'expiration' == $notice['event'] && ( 'non-recurring' == $notice['listings'] || 'both' == $notice['listings'] ) && '0 days' == $notice['relative_time'] ) {
                $found = true;
                break;
            }
        }

        if ( ! $found ) {
            $default_notices = self::get_default_expiration_notices();
            $value[] = $default_notices[1];
        }

        return $value;
    }

    public static function setup_ajax_compat_mode( $setting, $value ) {
        $mu_dir = ( defined( 'WPMU_PLUGIN_DIR' ) && defined( 'WPMU_PLUGIN_URL' ) ) ? WPMU_PLUGIN_DIR : trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins';
        $source = PDL_INC . '/compatibility/pdl-ajax-compat-mu.php';
        $dest   = trailingslashit( $mu_dir ) . basename( $source );

        if ( 0 == $value && file_exists( $dest ) ) {
            if ( ! unlink( $dest ) ) {
                $message = array(
                    sprintf( _x( 'Could not remove the "Plestar Directory Listing - AJAX Compatibility Module". Please remove the file "%s" manually or deactivate the plugin.',
                                 'admin settings',
                                 'PDM' ),
                             $dest ),
                    'error'
                );
                update_option( 'pdl-ajax-compat-mode-notice', $message );
            }
        } elseif ( 1 == $value && ! file_exists( $dest ) ) {
            // Install plugin.
            $success = true;

            if ( ! wp_mkdir_p( $mu_dir ) ) {
                $message = array( sprintf( _x( 'Could not activate AJAX Compatibility mode: the directory "%s" could not be created.', 'admin settings', 'PDM' ), $mu_dir ), 'error' );
                $success = false;
            }

            if ( $success && ! copy( $source, $dest ) ) {
                $message = array( sprintf( _x( 'Could not copy the AJAX compatibility plugin "%s". Compatibility mode was not activated.', 'admin settings', 'PDM' ), $dest ), 'error' );
                $success = false;
            }

            if ( ! $success ) {
                update_option( 'pdl-ajax-compat-mode-notice', $message );
                pdl_set_option( $setting['id'], 0 );
            }
        }
    }

}
