<?php

class PDL__Settings {

    const PREFIX = 'pdl-';

    private $groups = array();
    private $settings = array();
    private $options = array();

    private $deps = array();


    public function __construct() {
        // Make sure our option exists.
        if ( false === ( $settings_opt = get_option( 'pdl_settings' ) ) ) {
            add_option( 'pdl_settings', array() );
        }

        // register_setting is not available on init in WordPress 4.3
        if ( ! function_exists( 'register_setting' ) && file_exists( ABSPATH . 'wp-admin/includes/plugin.php' ) ) {
		    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        register_setting( 'pdl_settings', 'pdl_settings', array( $this, 'sanitize_settings' ) );

        // Cache current values.
        $this->options = is_array( $settings_opt ) ? $settings_opt : array();
    }

    public function bootstrap() {
        // Add initial settings.
        require_once( PDL_INC . 'admin/settings/class-settings-bootstrap.php' );
        PDL__Settings__Bootstrap::register_initial_groups();
        PDL__Settings__Bootstrap::register_initial_settings();
    }

    public function sanitize_settings( $input ) {
        $on_admin = ! empty( $_POST['_wp_http_referer'] );

        $output = array_merge( $this->options, $input );

        // Validate each setting.
        foreach ( $input as $setting_id => $value ) {
            $output[ $setting_id ] = apply_filters( 'pdl_settings_sanitize', $value, $setting_id );
            $output[ $setting_id ] = apply_filters( 'pdl_settings_sanitize_' . $setting_id, $input[ $setting_id ], $setting_id );

            if ( ! empty( $this->settings[ $setting_id ] ) ) {
                $setting = $this->settings[ $setting_id ];

                // XXX: maybe this should always be executed, not only admin side?
                if ( $on_admin ) {
                    switch ( $setting['type'] ) {
                    case 'multicheck':
                        if ( is_array( $value ) ) {
                            $input[ $setting_id ] = array_filter( $value, 'strlen' );
                            $output[ $setting_id ] = array_filter( $value, 'strlen' );
                        }

                        break;
                    default:
                        break;
                    }
                }

                if ( ! empty( $setting['on_update'] ) && is_callable( $setting['on_update'] ) ) {
                    call_user_func( $setting['on_update'], $setting, $input[ $setting_id ], ! empty( $this->options[ $setting_id ] ) ? $this->options[ $setting_id ] : null );
                }
            }

            do_action( 'pdl_setting_updated', $setting_id, $output[ $setting_id ], $value );
            do_action( "pdl_setting_updated_{$setting_id}", $output[ $setting_id ], $value, $setting_id );
        }

        $this->options = $output;

        return $this->options;

        // function edd_settings_sanitize( $input = array() ) {
        // 	global $edd_options;
        // 	$doing_section = false;
        // 	if ( ! empty( $_POST['_wp_http_referer'] ) ) {
        // 		$doing_section = true;
        // 	}
        // 	$setting_types = edd_get_registered_settings_types();
        // 	$input         = $input ? $input : array();
        // 	if ( $doing_section ) {
        // 		parse_str( $_POST['_wp_http_referer'], $referrer ); // Pull out the tab and section
        // 		$tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';
        // 		$section  = isset( $referrer['section'] ) ? $referrer['section'] : 'main';
        // 		if ( ! empty( $_POST['edd_section_override'] ) ) {
        // 			$section = sanitize_text_field( $_POST['edd_section_override'] );
        // 		}
        // 		$setting_types = edd_get_registered_settings_types( $tab, $section );
        // 		// Run a general sanitization for the tab for special fields (like taxes)
        // 		$input = apply_filters( 'edd_settings_' . $tab . '_sanitize', $input );
        // 		// Run a general sanitization for the section so custom tabs with sub-sections can save special data
        // 		$input = apply_filters( 'edd_settings_' . $tab . '-' . $section . '_sanitize', $input );
        // 	}
        // 	// Merge our new settings with the existing
        // 	$output = array_merge( $edd_options, $input );
        // 	foreach ( $setting_types as $key => $type ) {
        // 		if ( empty( $type ) ) {
        // 			continue;
        // 		}
        // 		// Some setting types are not actually settings, just keep moving along here
        // 		$non_setting_types = apply_filters( 'edd_non_setting_types', array(
        // 			'header', 'descriptive_text', 'hook',
        // 		) );
        // 		if ( in_array( $type, $non_setting_types ) ) {
        // 			continue;
        // 		}
        // 		if ( array_key_exists( $key, $output ) ) {
        // 			$output[ $key ] = apply_filters( 'edd_settings_sanitize_' . $type, $output[ $key ], $key );
        // 			$output[ $key ] = apply_filters( 'edd_settings_sanitize', $output[ $key ], $key );
        // 		}
        // 		if ( $doing_section ) {
        // 			switch( $type ) {
        // 				case 'checkbox':
        // 				case 'gateways':
        // 				case 'multicheck':
        // 				case 'payment_icons':
        // 					if ( array_key_exists( $key, $input ) && $output[ $key ] === '-1' ) {
        // 						unset( $output[ $key ] );
        // 					}
        // 					break;
        // 				default:
        // 					if ( array_key_exists( $key, $input ) && empty( $input[ $key ] ) || ( array_key_exists( $key, $output ) && ! array_key_exists( $key, $input ) ) ) {
        // 						unset( $output[ $key ] );
        // 					}
        // 					break;
        // 			}
        // 		} else {
        // 			if ( empty( $input[ $key ] ) ) {
        // 				unset( $output[ $key ] );
        // 			}
        // 		}
        // 	}
        // 	if ( $doing_section ) {
        // 		add_settings_error( 'edd-notices', '', __( 'Settings updated.', 'easy-digital-downloads' ), 'updated' );
        // 	}
        // 	return $output;
        // }
    }

    /**
     * Register a setings group within the Settings API.
     * @since 5.0
     */
    public function register_group( $slug, $title = '', $parent = '', $args = array() ) {
        if ( $parent && ! isset( $this->groups[ $parent ] ) ) {
            // throw new Exception( sprintf( 'Parent settings group does not exist: %s', $parent ) );
            return false;
        }

        $parents = array();
        $parent_ = $parent;

        while( $parent_ ) {
            $parents[] = $parent_;
            $parent_ = $this->groups[ $parent_ ]['parent'];
        }

        switch ( count( $parents ) ) {
        case 0:
            $group_type = 'tab';
            break;
        case 1:
            $group_type = 'subtab';
            break;
        case 2:
            $group_type = 'section';
            break;
        default:
            // throw new Exception( sprintf( 'Invalid # of parents in the tree for settings group "%s"', $slug ) );
            return false;
            break;
        }

        if ( $parent ) {
            $this->groups[ $parent ]['count'] += 1;
        }

        $this->groups[ $slug ] = array_merge(
            $args,
            array(
                'title'  => $title,
                'desc'   => ! empty( $args['desc'] ) ? $args['desc'] : '',
                'type'   => $group_type,
                'parent' => $parent,
                'count'  => 0
            )
        );
    }

    /**
     * Register a setting within the Settings API.
     * @since 5.0
     */
    public function register_setting( $id_or_args, $name = '', $type = 'text', $group = '', $args = array() ) {
        if ( is_array( $id_or_args ) ) {
            $args = $id_or_args;
        } else {
            $args = array_merge(
                $args,
                array(
                    'id'    => $id_or_args,
                    'name'  => $name,
                    'type'  => $type,
                    'group' => $group
                )
            );
        }

        $args = wp_parse_args( $args, array(
            'id'           => '',
            'name'         => '',
            'type'         => 'text',
            'group'        => 'general/main',
            'desc'         => '',
            'validator'    => false,
            'default'      => false,
            'on_update'    => false,
            'dependencies' => array()
        ) );

        if ( isset( $this->settings[ $args['id' ] ] ) ) {
            return false;
        }

        if ( 'silent' != $args['type'] && ! isset( $this->groups[ $args['group'] ] ) ) {
            // throw new Exception( sprintf( 'Invalid settings group "%s" for setting "%s".', $args['group'], $args['id'] ) );
            return false;
        }

        if ( 'number' == $args['type'] ) {
            add_filter( 'pdl_settings_sanitize_' . $args['id'], array( $this, 'validate_number_setting' ), 10, 2 );
        }

        $this->settings[ $args['id' ] ] = $args;

        if ( 'silent' != $args['type'] ) {
            $this->groups[ $args['group'] ]['count'] += 1;
        }

        if ( ! empty( $args['validator'] ) ) {
            add_filter( 'pdl_settings_sanitize_' . $args['id'], array( $this, 'validate_setting' ), 10, 2 );
        }
    }

    public function get_registered_groups() {
        return $this->groups;
    }

    public function get_registered_settings() {
        return $this->settings;
    }

    public function get_option( $setting_id, $default = false ) {
        $default_provided = func_num_args() > 1;

        if ( array_key_exists( $setting_id, $this->options ) ) {
            $value = $this->options[ $setting_id ];
        } else {
            // Try old options.
            $old_value = get_option( 'pdl-' . $setting_id, null );

            if ( ! is_null( $old_value ) ) {
                $value = $old_value;
                $this->options[ $setting_id ] = $old_value;
                update_option( 'pdl_settings', $this->options );

                // delete_option( 'pdl-' . $setting_id );
            } else {
                if ( $default_provided ) {
                    $value = $default;
                } else {
                    if ( ! empty( $this->settings[ $setting_id ] ) ) {
                        $value = $this->settings[ $setting_id ]['default'];
                    } else {
                        $value = false;
                    }
                }
            }
        }

        $value = apply_filters( 'pdl_get_option', $value, $setting_id );
        $value = apply_filters( 'pdl_get_option_' . $setting_id, $value );
        
        // Sanitize the value (if empty) based on setting type.
        if ( empty( $value ) ) {
            if ( $setting = $this->get_setting( $setting_id ) ) {
                switch ( $setting['type'] ) {
                    case 'checkbox':
                        $value = (int) $value;
                        break;
                    case 'multicheck':
                        $value = array();
                        break;
                    default:
                        break;
                }
            }
        }

        return $value;
    }

    public function set_option( $setting_id, $value = null ) {
        $old = get_option( 'pdl_settings' );
        $old[ $setting_id ] = $value;
        update_option( 'pdl_settings', $old );
    }


    /**
     * @deprecated 5.0. Use {@link PDL__Settings::register_group()}.
     */
    public function add_group( $slug, $name, $help_text = '' ) {
        if ( ! isset( $this->groups[ $slug ] ) ) {
            $this->register_group( $slug, $name, '', array( 'desc' => $help_text ) );
        }

        return $slug;
    }

    /**
     * @deprecated 5.0. Use {@link PDL__Settings::register_group()}.
     */
    public function add_section($group_slug, $slug, $name, $help_text='') {
        $tab = $group_slug;
        $subtab = $group_slug . '/main';

        if ( ! isset( $this->groups[ $subtab ] ) ) {
            $this->register_group( $subtab, _x( 'General Settings', 'settings', 'PDM' ), $tab );
        }

        $this->register_group( "{$subtab}:{$slug}", $name, $subtab, array( 'desc' => $help_text ) );

        return "{$subtab}:{$slug}";
    }

    /**
     * @deprecated 5.0. Use {@link PDL__Settings::register_setting()}.
     */
    public function add_core_setting( $name, $default=null ) {
        return false;
    }

    /**
     * @deprecated 5.0. Use {@link PDL__Settings::register_setting()}.
     */
    public function add_setting( $section_key, $name, $label, $type = 'text', $default = null, $help_text = '', $args = array(), $validator = null, $callback = null ) {
        return;
        $args_ = func_get_args();
        pdl_debug_e( 'add setting called', $args_ );

        // list($group, $section) = explode(':', $section_key);
        // $args = !$args ? array() : $args;
        //
        // if (!$group || !$section)
        //     return false;
        //
        // if ( isset($this->groups[$group]) && isset($this->groups[$group]->sections[$section]) ) {
        //     $_default = $default;
        //     if (is_null($_default)) {
        //         switch ($type) {
        //             case 'text':
        //             case 'choice':
        //                 $_default = '';
        //                 break;
        //             case 'boolean':
        //                 $_default = false;
        //                 break;
        //             default:
        //                 $_default = null;
        //                 break;
        //         }
        //     }
        //
        //     $setting = new StdClass();
        //     $setting->name = esc_attr( $name );
        //     $setting->label = $label;
        //     $setting->help_text = $help_text;
        //     $setting->default = $_default;
        //     $setting->type = $type;
        //     $setting->args = $args;
        //     $setting->validator = $validator;
        //     $setting->callback = $callback;
        //
        //     $setup_cb = '_setting_' . $setting->type . '_setup';
        //     if ( is_callable( array( $this, $setup_cb ) ) ) {
        //         call_user_func_array( array( $this, $setup_cb ), array( &$setting ) );
        //     }
        //
        //     $this->groups[$group]->sections[$section]->settings[$name] = $setting;
        // }

        // if (!isset($this->settings[$name])) {
        //     $this->settings[$name] = $setting;
        // }

        // return $name;
    }

    /**
     * @deprecated 5.0. Specify dependencies while registering the setting using {@link PDL__Settings::register_setting()}.
     */
    public function register_dep( $setting, $dep, $arg = null ) {
        return;
        pdl_debug_e( 'register dep called' );

        if ( ! isset( $this->deps[ $setting ] ) )
            $this->deps[ $setting ] = array();

        $this->deps[ $setting ][ $dep ] = $arg;
    }

    public function get_dependencies( $args = array() ) {
        $args = wp_parse_args( $args, array(
            'setting' => null,
            'type' => null
        ) );
        extract( $args );

        if ( $setting )
            return isset( $this->deps[ $setting ] ) ? $this->deps[ $setting ] : array();

        if ( $type ) {
            $res = array();

            foreach ( $this->deps as $s => $deps ) {
                foreach ( $deps as $d => $a ) {
                    if ( $type == $d )
                        $res[ $s ] = $a;
                }
            }
        }

        return $res;
    }

    public function get_setting( $name ) {
        if ( isset( $this->settings[ $name ] ) )
            return $this->settings[ $name ];

        return false;
    }

    /* emulates get_wpdirlist_config_options() in version 2.0 until
     * all deprecated code has been ported. */
    public function pre_2_0_compat_get_config_options() {
        $legacy_options = array();

        foreach ($this->pre_2_0_options() as $old_key => $new_key) {
            $setting_value = $this->get($new_key);

            if ($new_key == 'googlecheckout' || $new_key == 'paypal' || $new_key == '2checkout')
                $setting_value = !$setting_value;

            if ($this->settings[$new_key]->type == 'boolean') {
                $setting_value = $setting_value == true ? 'yes' : 'no';
            }

            $legacy_options[$old_key] = $setting_value;
        }

        return $legacy_options;
    }

    /**
     * Resets settings to their default values. This includes ALL premium modules too, so use with care.
     */
    public function reset_defaults() {
        $options = $this->options;

        foreach ( $options as $option_id => $option_value ) {
            if ( preg_match( '/^license-key-/', $option_id ) ) {
                continue;
            }

            unset( $this->options[ $option_id ] );
        }

        update_option( 'pdl_settings', $this->options );
    }

    public function validate_setting( $value, $setting_id ) {
        $on_admin = ! empty( $_POST['_wp_http_referer'] );
        if ( ! $on_admin ) {
            return $value;
        }
        
        if ( ! empty( $this->settings[ $setting_id ] ) ) {
            $setting = $this->get_setting( $setting_id );

            if ( is_string( $setting['validator'] ) ) {
                $validators = explode( ',', $setting['validator'] );
            } else if ( is_callable( $setting['validator'] ) ) {
                $validators = array( $setting['validator'] );
            } else if ( is_array( $setting['validator'] ) ) {
                $validators = $setting['validator'];
            }
        } else {
            $setting    = null;
            $validators = array();
        }

        if ( isset( $this->options[ $setting_id ] ) ) {
            $old_value = $this->options[ $setting_id ];
        } else {
            $old_value = null;
        }

        $has_error = false;

        foreach ( $validators as $validator ) {
            switch ( $validator ) {
            case 'trim':
                $value = trim( $value );
                break;
            case 'no-spaces':
                $value = trim( preg_replace( '/\s+/', '', $value ) );
                break;
            case 'required':
                if ( is_array( $value ) ) {
                    $value = array_filter( $value, 'strlen' );
                }

                if ( empty( $value ) ) {
                    add_settings_error( 'pdl_settings', $setting_id, sprintf( _x( '"%s" can not be empty.', 'settings', 'PDM' ), $setting['name'] ), 'error' );
                    $has_error = true;
                }

                break;
            case 'taxonomy_slug':
                // Don't use sanitize_title because it replaes unicode characters
                // with octets and breaks the Rewrite Rules.
                $value = trim( $value );

                if ( empty( $value ) ) {
                    add_settings_error( 'pdl_settings', $setting_id, sprintf( _x( '"%s" can not be empty.', 'settings', 'PDM' ), $setting['name'] ), 'error' );
                    $has_error = true;
                }

                if ( ! empty( $setting ) && ! empty( $setting['taxonomy'] ) ) {
                    foreach ( get_taxonomies( null, 'objects' ) as $taxonomy ) {
                        if ( $taxonomy->rewrite && $taxonomy->rewrite['slug'] == $value && $taxonomy->name != $setting['taxonomy'] ) {
                            add_settings_error( 'pdl_settings', $setting_id, sprintf( _x( 'The slug "%s" is already in use for another taxonomy.', 'settings', 'PDM' ), $value ), 'error' );
                            $has_error = true;
                        }
                    }
                }

                break;
            default:
                // TODO: How to handle errors to set $has_error = true?
                if ( is_callable( $validator ) ) {
                    if ( is_string( $validator ) ) {
                        $value = call_user_func( $validator, $value );
                    } else {
                        $value = call_user_func( $validator, $value, $old_value, $setting );
                    }
                }

                break;
            }
        }

        return ( $has_error ? $old_value : $value );
    }

    public function validate_number_setting( $value, $setting_id ) {
        $setting = $this->get_setting( $setting_id );

        if ( ! $setting ) {
            return $value;
        }

        if ( ! empty( $setting['step'] ) && is_int( $setting['step'] ) ) {
            $value = intval( $value );
        } else {
            $value = floatval( $value );
        }

        // Min and max.
        $value = ( array_key_exists( 'min', $setting ) && $value < $setting['min'] ) ? $setting['min'] : $value;
        $value = ( array_key_exists( 'max', $setting ) && $value > $setting['max'] ) ? $setting['max'] : $value;

        return $value;
    }

    /* upgrade from old-style settings to new options */
    public function pre_2_0_options() {
        static $option_translations = array(
            'wpdirlist_settings_config_18' => 'listing-duration',
            /* 'wpdirlist_settings_config_25' => 'hide-buy-module-buttons',*/  /* removed in 2.0 */
            'wpdirlist_settings_config_26' => 'hide-tips',
            'wpdirlist_settings_config_27' => 'show-contact-form',
            'wpdirlist_settings_config_36' => 'show-comment-form',
            'wpdirlist_settings_config_34' => 'credit-author',
            'wpdirlist_settings_config_38' => 'listing-renewal',
            'wpdirlist_settings_config_39' => 'use-default-picture',
            'wpdirlist_settings_config_44' => 'show-listings-under-categories',
            'wpdirlist_settings_config_45' => 'override-email-blocking',
            'wpdirlist_settings_config_46' => 'status-on-uninstall',
            'wpdirlist_settings_config_47' => 'deleted-status',
            'wpdirlist_settings_config_3' => 'require-login',
            'wpdirlist_settings_config_4' => 'login-url',
            'wpdirlist_settings_config_5' => 'registration-url',
            'wpdirlist_settings_config_1' => 'new-post-status',
            'wpdirlist_settings_config_19' => 'edit-post-status',
            'wpdirlist_settings_config_7' => 'categories-order-by',
            'wpdirlist_settings_config_8' => 'categories-sort',
            'wpdirlist_settings_config_9' => 'show-category-post-count',
            'wpdirlist_settings_config_10' => 'hide-empty-categories',
            'wpdirlist_settings_config_48' => 'show-only-parent-categories',
            'wpdirlist_settings_config_52' => 'listings-order-by',
            'wpdirlist_settings_config_53' => 'listings-sort',
            'wpdirlist_settings_config_6' => 'allow-images',
            'wpdirlist_settings_config_2' => 'free-images',
            'wpdirlist_settings_config_11' => 'show-thumbnail',
            'wpdirlist_settings_config_13' => 'image-max-filesize',
            'wpdirlist_settings_config_14' => 'image-min-filesize',
            'wpdirlist_settings_config_15' => 'image-max-width',
            'wpdirlist_settings_config_16' => 'image-max-height',
            'wpdirlist_settings_config_17' => 'thumbnail-width',
            'wpdirlist_settings_config_20' => 'currency',
            'wpdirlist_settings_config_12' => 'currency-symbol',
            'wpdirlist_settings_config_21' => 'payments-on',
            'wpdirlist_settings_config_22' => 'payments-test-mode',
            'wpdirlist_settings_config_37' => 'payment-message',
            'wpdirlist_settings_config_23' => 'googlecheckout-merchant',
            'wpdirlist_settings_config_24' => 'googlecheckout-seller',
            'wpdirlist_settings_config_40' => 'googlecheckout',
            'wpdirlist_settings_config_35' => 'paypal-business-email',
            'wpdirlist_settings_config_41' => 'paypal',
            'wpdirlist_settings_config_42' => '2checkout-seller',
            'wpdirlist_settings_config_43' => '2checkout',
            'wpdirlist_settings_config_31' => 'featured-on',
            'wpdirlist_settings_config_32' => 'featured-price',
            'wpdirlist_settings_config_33' => 'featured-description',
            'wpdirlist_settings_config_28' => 'recaptcha-public-key',
            'wpdirlist_settings_config_29' => 'recaptcha-private-key',
            'wpdirlist_settings_config_30' => 'recaptcha-on',
            'wpdirlist_settings_config_49' => 'permalinks-directory-slug',
            'wpdirlist_settings_config_50' => 'permalinks-category-slug',
            'wpdirlist_settings_config_51' => 'permalinks-tags-slug'
        );
        return $option_translations;
    }

    public function upgrade_options() {
        if (!$this->settings)
            $this->_register_settings();

        $translations = $this->pre_2_0_options();

        if ($old_options = get_option('wpdirlist_settings_config')) {
            foreach ($old_options as $option) {
                $id = strtolower($option['id']);
                $type = strtolower($option['type']);
                $value = $option['std'];

                if ($type == 'titles' || $id == 'wpdirlist_settings_config_25' || empty($value))
                    continue;

                if ($id == 'wpdirlist_settings_config_40') {
                    $this->set('googlecheckout', $value == 'yes' ? false : true);
                } elseif ($id == 'wpdirlist_settings_config_41') {
                    $this->set('paypal', $value == 'yes' ? false : true);
                } elseif ($id == 'wpdirlist_settings_config_43') {
                    $this->set('2checkout', $value == 'yes' ? false : true);
                } else {
                    if (!isset($this->settings[$translations[$id]]))
                        continue;

                    $newsetting = $this->settings[$translations[$id]];

                    switch ($newsetting->type) {
                        case 'boolean':
                            $this->set($newsetting->name, $value == 'yes' ? true : false);
                            break;
                        case 'choice':
                        case 'text':
                        default:
                            $this->set($newsetting->name, $value);
                            break;
                    }
                }

            }

            delete_option('wpdirlist_settings_config');
        }
    }

}

// For backwards compat.
class PDL_Settings extends PDL__Settings {}

