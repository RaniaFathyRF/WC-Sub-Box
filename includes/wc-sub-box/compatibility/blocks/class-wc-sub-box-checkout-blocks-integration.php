<?php
/**
 * WC_Sub_Box_Checkout_Blocks_Integration class
 */

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

/**
 * Class for integrating with WooCommerce Blocks scripts.
 *
 * @version 8.8.0
 */
class WC_Sub_Box_Checkout_Blocks_Integration implements IntegrationInterface {

	/**
	 * Whether the integration has been initialized.
	 *
	 * @var boolean
	 */
	protected $is_initialized;

	/**
	 * The single instance of the class.
	 *
	 * @var WC_Sub_Box_Checkout_Blocks_Integration
	 */
	public static $_instance = null;



	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Foul!', 'woocommerce-composite-products' ), '8.4.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Foul!', 'woocommerce-composite-products' ), '8.4.0' );
	}

	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'wc-sub-box';
	}

	/**
	 * When called invokes any initialization/setup for the integration.
	 */
	public function initialize() {
		if ( $this->is_initialized ) {
			return;
		}

		if ( is_null( WC()->cart ) ) {
			return;
		}

        $script_asset      =  ['dependencies' => ['wc-blocks-checkout'], 'version' => '3dba1044da59df977f3c8f7cbdf0774e'];

		wp_register_script('wc-sub-box-checkout-blocks', WC_SUB_BOX_URL. 'assets/js/front/wc-sub-box-checkout-blocks.js', $script_asset[ 'dependencies' ], $script_asset[ 'version' ], true);
        wp_localize_script('wc-sub-box-checkout-blocks', 'wc_sub_box_checkout_blocks', [
        'cart_items_data' => json_encode(self::get_wc_sub_box_cart_items_data()),
        ]);
		add_action('wp_enqueue_scripts',function() {
            if (wcs_is_view_subscription_page())
                return;
            wp_enqueue_style('wc-sub-box-checkout-blocks', WC_SUB_BOX_URL . 'assets/css/wc-sub-box-checkout-blocks.css', '', WC_SUB_BOX_ASSETS_VERSION, 'all');
            wp_style_add_data('wc-sub-box-checkout-blocks', 'rtl', 'replace');
        }
		);

		$this->is_initialized = true;
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return array( 'wc-sub-box-checkout-blocks' );
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return array();
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data() {
		return array(
			'wc-sub-box-checkout-blocks' => 'active',
		);
	}

    public static function get_wc_sub_box_cart_items_data(){
        $cart_item_data = array();

        foreach ( WC()->cart->get_cart() as $cart_item_key =>$cart_item ) {
            if ( WC_Sub_Box_Utility::is_wc_sub_box_container_cart_item($cart_item ) ) {
                $cart_item_data[$cart_item['data']->get_id()] = ['is_parent' => true,'is_child' => false];

            } elseif ( WC_Sub_Box_Utility::is_wc_sub_box_child_cart_item( $cart_item ) ) {
                $cart_item_data[$cart_item['data']->get_id()] = ['is_parent' => false,'is_child' => true];
            }
        }
        return $cart_item_data;


    }
    /**
     * Main WC_Sub_Box_Checkout_Blocks_Integration instance. Ensures only one instance of WC_CP_Checkout_Blocks_Integration is loaded or can be loaded.
     *
     * @static
     * @return WC_Sub_Box_Checkout_Blocks_Integration
     */
    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}
