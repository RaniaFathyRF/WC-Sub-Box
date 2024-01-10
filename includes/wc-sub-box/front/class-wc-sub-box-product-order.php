<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('WC_Sub_Box_Product_Order')) {

    class WC_Sub_Box_Product_Order
    {
        /**
         * @var WC_Sub_Box_Product_Order
         */
        public static $instance;


        private function __construct()
        {
            // add subscription items when place order
            add_action('woocommerce_checkout_create_subscription', array($this, 'wc_sub_box_add_subscription_items'), 10, 4);
//            add_action('woocommerce_checkout_subscription_created', array($this, 'wc_sub_box_add_subscription_items'), 10, 3);
            add_filter('woocommerce_order_item_class', array($this, 'wc_sub_box_add_order_classes'), 10, 3);

        }

        /**
         * add subscription items when place order
         * @param $subscription
         * @param $posted_data
         * @param $order
         * @param $cart
         * @return void
         */
        public function wc_sub_box_add_subscription_items($subscription, $posted_data, $order, $cart)
        {

            if (!WC_Sub_Box_Utility::is_order_has_wc_sub_box_product($subscription))
                return;

            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                if (!WC_Sub_Box_Utility::is_wc_sub_box_container_cart_item($cart_item))
                    continue;
                $container_id = $cart_item['data']->get_id();
                $container_childern = $cart_item[WC_Sub_Box_Product_Cart::WC_SUB_BOX_ITEMS_KEY];
            }
            if (empty($container_id) || empty($container_childern))
                return;

            update_post_meta($subscription->get_id(), '_container_' . $container_id, $container_childern);
//            update_post_meta($order->get_id(), '_container_' . $container_id, $container_childern);

            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                if (!WC_Sub_Box_Utility::is_wc_sub_box_child_cart_item($cart_item))
                    continue;
                if (!in_array($cart_item['data']->get_id(), array_keys($container_childern)))
                    continue;
                $child_item_id = $subscription->add_product($cart_item['data'], $cart_item['quantity']);
                wc_update_order_item_meta($child_item_id, '_container_id', $container_id);

                $subscription->calculate_totals();
                $subscription->save();

            }

        }

        public function wc_sub_box_add_order_classes($class,$item, $order)
        {
            if (WC_Sub_Box_Utility::is_wc_sub_box_container_cart_item($cart_item))
                return $class .= ' wc-sub-box-item-container';
            if (WC_Sub_Box_Utility::is_wc_sub_box_child_cart_item($cart_item))
                return $class .= ' wc-sub-box-item-child';
            return $class;
        }

        /**
         * @return WC_Sub_Box_Product_Order
         */
        public
        static function get_instance()
        {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }

}

WC_Sub_Box_Product_Order::get_instance();

