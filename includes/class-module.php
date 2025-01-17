<?php
/**
 * Lightweight wrapper for extension modules, ensuring all basic info is always available.
 * @since 5.0
 */
final class PDL__Module {

    public $file;
    public $title;
    public $id;
    public $version;
    public $required_pdl_version;

    public $text_domain;
    public $text_domain_path;

    public $is_premium_module = true;

    private $module;


    public function __construct( $obj ) {
        $this->module = $obj;

        foreach ( array( 'file', 'id' ) as $required_prop ) {
            if ( ! isset( $obj->{$required_prop} ) )
                throw new Exception( sprintf( '%s is not a valid Plestar Directory Listing module!', get_class( $obj ) ) );

            $this->{$required_prop} = $obj->{$required_prop};
        }

        $plugin_data = get_file_data( $obj->file, array( 'Plugin Name', 'Version', 'Text Domain', 'Domain Path' ) );

        $this->title = empty( $obj->title ) ? $plugin_data[0] : $obj->title;
        $this->version = empty( $obj->version ) ? $plugin_data[1] : $obj->version;
        $this->text_domain = empty( $obj->text_domain ) ? $plugin_data[2] : $obj->text_domain;
        $this->text_domain_path = empty( $obj->domain_path ) ? $plugin_data[3] : $obj->domain_path;

        if ( empty( $this->text_domain ) ) {
            $this->text_domain = 'pdl-' . $this->id;
        }

        if ( empty( $this->text_domain_path ) ) {
            foreach ( array( 'translations', 'languages' ) as $d ) {
                if ( is_dir( plugin_dir_path( $this->file ) . $d ) ) {
                    $this->text_domain_path = '/' . $d;
                    break;
                }
            }
        }

        $this->required_pdl_version = isset( $obj->required_pdl_version ) ? $obj->required_pdl_version : '';
        $this->is_premium_module = ! in_array( $this->id, array( 'categories' ), true );
    }

    public function __call( $name, $args ) {
        if ( method_exists( $this->module, $name ) )
            return call_user_func_array( array( $this->module, $name ), $args );
        elseif ( in_array( $name, array( 'init' ), true ) )
            return;

        throw new Exception( 'Invalid method called!' );
    }

}

