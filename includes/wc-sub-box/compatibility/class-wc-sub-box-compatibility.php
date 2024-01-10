<?php
/**
 * WC_Sub_Box_Compatibility class
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_Sub_Box_Compatibility {

    /**
     * Array of min required plugin versions.
     *
     * @var array
     */
    private $required = array();

    /**
     * Modules to load.
     *
     * @var array
     */
    private $modules = array();

    /**
     * The single instance of the class.
     *
     * @var WC_Sub_Box_Compatibility
     *
     * */
    protected static $_instance = null;



    /**
     * Constructor.
     */
    public function __construct() {
        $this->required = array(
            'blocks' => '7.2.0',
        );

        // Initialize.
        $this->load_modules();
    }

    /**
     * Initialize.
     *
     * @since  3.10.2
     *
     * @return void
     */
    protected function load_modules() {
           // Initialize.
        add_action( 'plugins_loaded', array( $this, 'module_includes' ), 100 );

    }

    /**
     * Init compatibility classes.
     */
    public function module_includes() {

        $module_paths = array();

        // WooCommerce Cart/Checkout Blocks support.
        if ( class_exists( 'Automattic\WooCommerce\Blocks\Package' ) && version_compare( \Automattic\WooCommerce\Blocks\Package::get_version(), $this->required[ 'blocks' ] ) >= 0 ) {
            $module_paths[ 'blocks' ] = WC_SUB_BOX_PATH . 'compatibility/modules/class-wc-sub-box-blocks-compatibility.php';
        }
        /**
         * 'wc_sub_box_compatibility_modules' filter.
         *
         * Use this to filter the required compatibility modules.
         *
         * @since  3.13.6
         * @param  array $module_paths
         */
        $this->modules = apply_filters( 'wc_sub_box_compatibility_modules', $module_paths );

        foreach ( $this->modules as $name => $path ) {
            require_once( $path );
        }
    }
    /**
     * Main WC_Sub_Box_Compatibility instance.
     *
     *
     * @static
     * @return WC_Sub_Box_Compatibility
     */
    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}

WC_Sub_Box_Compatibility::get_instance();
