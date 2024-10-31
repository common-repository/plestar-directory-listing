<?php
/**
 * @since 4.0
 */
class PDL_Themes_Admin {

    private $api;
    private $licensing;


    function __construct( &$api, $licensing ) {
        $this->api = $api;
        $this->licensing = $licensing;

        // return;
        // require_once( PDL_PATH . 'includes/admin/upgrades/class-themes-updater.php' );
        // $this->updater = new PDL_Themes_Updater( $this->api );

        // add_filter( 'pdl_admin_menu_badge_number', array( &$this, 'admin_menu_badge_count' ) );
        add_action( 'pdl_admin_menu', array( &$this, 'admin_menu' ) );
        add_filter( 'pdl_admin_menu_reorder', array( &$this, 'admin_menu_move_themes_up' ) );

        add_action( 'pdl_admin_notices', array( &$this, 'pre_themes_templates_warning' ) );

        add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );

        add_action( 'pdl_action_set-active-theme', array( &$this, 'set_active_theme' ) );
        add_action( 'pdl_action_delete-theme', array( &$this, 'delete_theme' ) );
        add_action( 'pdl_action_upload-theme', array( &$this, 'upload_theme' ) );
        add_action( 'pdl_action_create-theme-suggested-fields', array( &$this, 'create_suggested_fields' ) );

        // add_action( 'pdl-admin-themes-extra', array( &$this, 'enter_license_key_row' ) );
    }

    function admin_menu( $slug ) {
        // $count = $this->updater->get_updates_count();
        $count = 0;

        if ( $count )
            $count_html = '<span class="update-plugins"><span class="plugin-count">' . number_format_i18n( $count ) . '</span></span>';
        else
            $count_html = '';

        add_submenu_page( $slug,
                          _x( 'Directory Themes', 'themes', 'PDM' ),
                          sprintf( _x( 'Directory Themes %s', 'themes', 'PDM' ), $count_html ),
                          'administrator',
                          'pdl-themes',
                          array( &$this, 'dispatch' ) );
    }

    function admin_menu_badge_count( $cnt = 0 ) {
        return ( (int) $cnt ) + $this->updater->get_updates_count();
    }

    function admin_menu_move_themes_up( $menu ) {
        $themes_key = false;

        foreach ( $menu as $k => $i ) {
            if ( 'pdl-themes' === $i[2] ) {
                $themes_key = $k;
                break;
            }
        }

        if ( false === $themes_key )
            return $menu;

        $themes = $menu[ $themes_key ];
        unset( $menu[ $themes_key ] );
        $menu = array_merge( array( $menu[0], $themes ), array_slice( $menu, 1 ) );

        return $menu;
    }

    function pre_themes_templates_warning() {
        $pre_themes_templates = array( 'plestardirectory-excerpt',
                                       'plestardirectory-listing',
                                       'plestardirectory-listings',
                                       'plestardirectory-main-page' );
        $overridden = array();

        foreach ( $pre_themes_templates as $t ) {
            if ( $f = pdl_locate_template( $t, true, false ) )
                $overridden[ $t ] = str_replace( WP_CONTENT_DIR, '', $f );
        }

        if ( ! $overridden )
            return;

        $msg  =  '';
        $msg .= '<strong>' . _x( 'Plestar Directory Listing - Your template overrides need to be reviewed!', 'admin themes', 'PDM' ) . '</strong>';
        $msg .= '<br />';
        $msg .= _x( 'Starting with version 4.0, Plestar Directory Listing is using a new theming system that is not compatible with the templates used in previous versions.', 'admin themes', 'PDM' );
        $msg .= '<br />';
        $msg .= _x( 'Because of this, your template overrides below have been disabled. You should <a>review our documentation on customization</a> in order adjust your templates.', 'admin themes', 'WBPDM' );
        $msg .= '<br /><br />';

        foreach ( $overridden as $t => $relpath ) {
            $msg .= '&#149; <tt>' . $relpath . '</tt><br />';
        }

        pdl_admin_message( $msg, 'error' );
    }

    function enqueue_scripts( $hook ) {
        global $pdl;
        global $pagenow;

        if ( 'admin.php' != $pagenow || ! isset( $_GET['page'] ) || 'pdl-themes' != $_GET['page'] )
            return;

        wp_enqueue_style(
            'pdl-admin-themes',
            PDL_URL . 'assets/css/admin-themes.min.css',
            array(),
            PDL_VERSION
        );

        wp_enqueue_script(
            'pdl-admin-themes',
            PDL_URL . 'assets/js/admin-themes.min.js',
            array(),
            PDL_VERSION
        );
    }

    function set_active_theme() {
        $theme_id = isset( $_POST['theme_id'] ) ? intval($_POST['theme_id']) : '';

        if ( ! current_user_can( 'administrator' ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'activate theme ' . $theme_id ) )
            wp_die();

        if ( ! $this->api->set_active_theme( $theme_id ) )
            wp_die( sprintf( _x( 'Could not change the active theme to "%s".', 'themes', 'PDM' ), $theme_id ) );

//        $this->api->try_active_theme();
//        pdl_debug_e( $theme_id );

        wp_redirect( admin_url( 'admin.php?page=pdl-themes&message=1' ) );
        exit;
    }

    function create_suggested_fields() {
        if ( ! current_user_can( 'administrator' ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'create_suggested_fields' ) )
            wp_die();

        $missing = $this->api->missing_suggested_fields();

        global $pdl;
        $pdl->formfields->create_default_fields( $missing );

        wp_safe_redirect( admin_url( 'admin.php?page=pdl_admin_formfields&action=updatetags' ) );
        exit;
    }

    function dispatch() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : ( isset( $_GET['v'] ) ? $_GET['v'] : '' );

        switch ( $action ) {
            case 'theme-install':
                return $this->theme_install();
                break;
            case 'delete-theme':
                return $this->theme_delete_confirm();
                break;
            case 'theme-selection':
            default:
                return $this->theme_selection();
                break;
        }
    }

    function theme_selection() {
        $msg = isset( $_GET['message'] ) ? $_GET['message'] : '';

        switch ( $msg ) {
            case 1:
                pdl_admin_message( sprintf( _x( 'Active theme changed to "%s".', 'themes', 'PDM' ), $this->api->get_active_theme() ) );

                if ( $missing_fields = $this->api->missing_suggested_fields( 'label' ) ) {
                    $msg  = sprintf( _x( '%s requires that you tag your existing fields to match some places we want to put your data on the theme. Below are fields we think are missing.', 'themes', 'PDM' ), $this->api->get_active_theme() );
                    $msg .= '<br />';

                    foreach ( $missing_fields as $mf )
                        $msg .= '<span class="tag">' . $mf . '</span>';

                    $msg .= '<br /><br />';
                    $msg .= sprintf( '<a href="%s" class="button button-primary">%s</a>',
                                     admin_url( 'admin.php?page=pdl_admin_formfields&action=updatetags' ),
                                     _x( 'Map My Fields', 'themes', 'PDM' ) );

                    pdl_admin_message( $msg, 'error' );
                }

                break;
            case 2:
                pdl_admin_message( _x( 'Suggested fields created successfully.', 'themes', 'PDM' ) );
                break;
            case 3:
                pdl_admin_message( _x( 'Theme installed successfully.', 'themes', 'PDM' ) );
                break;
            case 4:
                pdl_admin_message( _x( 'Theme was deleted successfully.', 'themes', 'PDM' ) );
                break;
            case 5:
                pdl_admin_message( _x( 'Could not delete theme directory. Check permissions.', 'themes', 'PDM' ), 'error' );
                break;
            default:
                break;
        }

        $themes = $this->get_installed_themes();
        $active_theme = $this->api->get_active_theme();

        // Make sure the current theme is always first.
        $current = $themes[ $active_theme ];
        unset( $themes[ $active_theme ] );
        array_unshift( $themes, $current );

        echo pdl_render_page( PDL_PATH . 'templates/admin/themes.tpl.php',
                                array( 'themes' => $themes,
                                       'active_theme' => $active_theme ) );
    }

    private function get_installed_themes() {
        $themes = $this->api->get_installed_themes();

        foreach( $themes as &$theme ) {
            if ( $theme->is_core_theme ) {
                $license_status = 'valid';
            } else {
                $license_status = $this->licensing->get_license_status( null, $theme->id, 'theme' );
            }

            if ( 'valid' === $license_status ) {
                $theme->can_be_activated = true;
            } else {
                $theme->can_be_activated = false;
            }
        }

        return $themes;
    }

    function upload_theme() {
        if ( ! current_user_can( 'administrator' ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'upload theme zip' ) )
            wp_die();

        $theme_file = isset( $_FILES['themezip'] ) ? $_FILES['themezip'] : false;

        if ( ! $theme_file || ! is_uploaded_file( $theme_file['tmp_name'] ) || UPLOAD_ERR_OK != $_FILES['themezip']['error'] ) {
            pdl_admin_message( _x( 'Please upload a valid theme file.', 'themes', 'PDM' ), 'error' );
            return;
        }

        $dest = wp_normalize_path( untrailingslashit( get_temp_dir() ) . DIRECTORY_SEPARATOR . $theme_file['name'] );

        if ( ! move_uploaded_file( $theme_file['tmp_name'], $dest ) ) {
            pdl_admin_message( sprintf( _x( 'Could not move "%s" to a temporary directory.', 'themes', 'PDM' ),
                                          $theme_file['name'] ),
                                 'error' );
            return;
        }

        $res = $this->api->install_theme( $dest );

        if ( is_wp_error( $res ) ) {
            pdl_admin_message( $res->get_error_message(), 'error' );
            return;
        }

        wp_redirect( admin_url( 'admin.php?page=pdl-themes&message=3' ) );
        exit;
    }

    function theme_install() {
        echo pdl_render_page( PDL_PATH . 'templates/admin/themes-install.tpl.php',
                                array() );
    }

    function theme_delete_confirm() {
        $theme_id = $_REQUEST['theme_id'];
        $theme = $this->api->get_theme( $theme_id );

        echo pdl_render_page( PDL_PATH . 'templates/admin/themes-delete-confirm.tpl.php',
                                array( 'theme' => $theme ) );
    }

    function delete_theme() {
        if ( ! isset( $_POST['dodelete'] ) || 1 != $_POST['dodelete'] )
            return;

        // Cancel. Return to themes page.
        if ( empty( $_POST['delete-theme'] ) ) {
            wp_redirect( admin_url( 'admin.php?page=pdl-themes' ) );
            exit;
        }

        $theme_id = isset( $_POST['theme_id'] ) ? intval(($_POST['theme_id'])) : '';
        $nonce = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';

        if ( ! current_user_can( 'administrator' ) || ! wp_verify_nonce( $nonce, 'delete theme ' . $theme_id ) )
            wp_die();

        $active_theme = $this->api->get_active_theme();
        $theme = $this->api->get_theme( $theme_id );

        if ( in_array( $theme_id, array( 'default', 'no_theme', $active_theme ), true ) || ! $theme )
            wp_die();

        $theme = $this->api->get_theme( $theme_id );
        $path = rtrim( $theme->path, '/\\' );
        $removed = false;

        if ( is_link( $path ) ) {
            $removed = unlink( $path );
        } elseif ( is_dir( $path ) ) {
            $removed = PDL_FS::rmdir( $path );
        }

        if ( $removed )
            wp_redirect( admin_url( 'admin.php?page=pdl-themes&message=4&deleted=' . $theme_id ) );
        else
            wp_redirect( admin_url( 'admin.php?page=pdl-themes&message=5&deleted=' . $theme_id ) );

        exit;
    }

    function enter_license_key_row( $theme ) {
        if ( $theme->can_be_activated )
            return;

        echo '<div class="pdl-theme-license-required-row">';
        echo str_replace( '<a>', '<a href="' . esc_url( admin_url( 'admin.php?page=pdl-themes&v=licenses' ) ) .  '">', _x( 'Activate your <a>license key</a> to use this theme.', 'themes', 'PDM' ) );
        echo '</div>';
    }

}
