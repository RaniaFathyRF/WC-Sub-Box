<?php
/**
 * WC_Sub_Box_Blocks_Compatibility class
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WC_Sub_Box_Blocks_Compatibility {

	/**
	 * Initialize.
	 */
	public static function init() {

		if ( ! did_action( 'woocommerce_blocks_loaded' ) ) {
			return;
		}
        include_once WC_SUB_BOX_PATH.'compatibility/blocks/class-wc-sub-box-checkout-blocks-integration.php';

		add_action(
			'woocommerce_blocks_cart_block_registration',
			function( $registry ) {
				$registry->register( WC_Sub_Box_Checkout_Blocks_Integration::get_instance() );
			}
		);

		add_action(
			'woocommerce_blocks_mini-cart_block_registration',
			function( $registry ) {
				$registry->register( WC_Sub_Box_Checkout_Blocks_Integration::get_instance() );
			}
		);

		add_action(
			'woocommerce_blocks_checkout_block_registration',
			function( $registry ) {
				$registry->register( WC_Sub_Box_Checkout_Blocks_Integration::get_instance() );
			}
		);
        add_action(
            'woocommerce_blocks_order-confirmation_block_registration',
            function( $registry ) {
                $registry->register( WC_Sub_Box_Checkout_Blocks_Integration::get_instance() );
            }
        );

	}
}

WC_Sub_Box_Blocks_Compatibility::init();
