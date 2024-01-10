<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('WC_Sub_Box_Utility')) {

    class WC_Sub_Box_Utility
    {

        /**
         * @var WC_Sub_Box_Utility
         */
        public static $instance;

        const WC_SUB_BOX_ITEMS_KEY = 'wc_sub_box_product';
        const WC_SUB_BOX_ITEMS_QTY_KEY = 'wc_sub_box_qty';


        private function __construct()
        {
        }

        /**
         * check if product is subscription box
         * @param $product
         * @return bool
         */
        public static function is_wc_sub_box_product($product)
        {
            if (empty($product))
                return false;

            if (!is_object($product) && is_integer($product))
                $wc_product_id = $product;
            else
                $wc_product_id = $product->get_id();


            if (!WC_Sub_General_Settings::is_subscription_box_enabled_in_settings())
                return false;

            if (!WC_Sub_Single_Product_Settings::is_wc_sub_box_product_enabled($wc_product_id))
                return false;

            if (empty(WC_Sub_Single_Product_Settings::wc_sub_box_get_products_ids($wc_product_id)))
                return false;

            return true;
        }

        /**
         * check if product is purchasable
         * @param $product
         * @return bool
         */
        public static function is_purchasable_product($product)
        {
            if (empty($product))
                return false;

            if (!is_object($product) && is_integer($product))
                $wc_product = wc_get_product($product);
            else
                $wc_product = $product;

            if ($wc_product->is_purchasable() && $wc_product->is_in_stock())
                return true;

            return false;
        }

        /**
         * get subscription box items data
         * @param $sub_box_product_id
         * @return array
         */
        public static function wc_sub_box_get_posted_items_data($sub_box_product_id)
        {

            $data = array();
            /*
             * Choose between $_POST or $_GET for grabbing data.
             * We will not rely on $_REQUEST because checkbox names may not exist in $_POST but they may well exist in $_GET, for instance when editing a container from the cart.
             */

            $posted_data = $_POST;

            if (empty($_POST['add-to-cart']) && !empty($_GET['add-to-cart']))
                $posted_data = $_GET;

            if (empty($posted_data[self::WC_SUB_BOX_ITEMS_KEY]))
                return $data;

            foreach ($posted_data[self::WC_SUB_BOX_ITEMS_KEY] as $key => $value) {

                if (empty($key))
                    continue;

                $quantity = intval(!empty($posted_data[self::WC_SUB_BOX_ITEMS_QTY_KEY][$key]) ? $posted_data[self::WC_SUB_BOX_ITEMS_QTY_KEY][$key] : 0);
                if ($quantity <= 0)
                    continue;

                /**
                 * 'wc_sub_box_child_item_cart_item_identifier' filter.
                 *
                 * Filters the config data array - use this to add any container-specific data that should result in unique container item ids being produced when the input data changes, such as add-ons data.
                 *
                 * @param array $quantity
                 * @param int $key
                 * @param mixed $sub_box_product_id
                 */
                $data[$key] = apply_filters('wc_sub_box_child_item_cart_item_identifier', $quantity, $key, $sub_box_product_id);
            }
            /**
             * Filter the posted configuration to support alternative templates.
             * wc_sub_box_get_posted_data
             * @param array $data
             * @param WC_Mix_and_Match_Product $product
             * @return array
             * @since  1.9.0
             */
            return (array)apply_filters('wc_sub_box_get_posted_data', $data, $sub_box_product_id);
        }

        /**
         * check if cart item is subscription box container
         * @param $cart_item
         * @return bool
         */
        public static function is_wc_sub_box_container_cart_item($cart_item)
        {

            if (empty($cart_item))
                return false;

            if (WC_Sub_Box_Product_Cart::has_wc_sub_box_items($cart_item) && WC_Sub_Box_Product_Cart::has_wc_sub_box_contents($cart_item))
                return true;

            return false;
        }

        /**
         * check if cart item is subscription box child
         * @param $cart_item
         * @return bool
         */
        public static function is_wc_sub_box_child_cart_item($cart_item)
        {
            if (empty($cart_item))
                return false;
            if (WC_Sub_Box_Product_Cart::has_wc_sub_box_container_data($cart_item))
                return true;
            return false;
        }

        /**
         * @param $container_id
         * @param $product_id
         * @param $quantity
         * @param $cart_item_data
         * @return string
         */
        public static function wc_sub_box_items_add_to_cart($container_id, $product_id, $quantity = 1, $cart_item_data = array())
        {

            /**
             * Load cart item data for child items.
             *
             * @param array $cart_item_data Child item's cart data.
             * @param int $product_id Child item's product ID.
             * @param int $variation_id Child item's variation ID.
             * @param int $quantity Child item's quantity.
             */
            $cart_item_data = (array)apply_filters('woocommerce_add_cart_item_data', $cart_item_data, $product_id, '', $quantity);

            // Generate a ID based on product ID, variation ID, variation data, and other cart item data.
            $cart_id = WC()->cart->generate_cart_id($product_id, '', '', $cart_item_data);

            // See if this product and its options is already in the cart.
            $cart_item_key = WC()->cart->find_product_in_cart($cart_id);

            // Get the product.
            $product_data = wc_get_product($product_id);

            // If cart_item_key is set, the item is already in the cart and its quantity will be handled by update_quantity_in_cart().
            if (!$cart_item_key) {

                $cart_item_key = $cart_id;

                /**
                 * Add item after merging with $cart_item_data
                 *
                 * Allow plugins and add_cart_item_filter() to modify cart item.
                 *
                 * @param array $cart_item_data Child item's cart data.
                 * @param str $cart_item_key Key in the WooCommerce cart array.
                 */
                WC()->cart->cart_contents[$cart_item_key] = apply_filters(
                    'woocommerce_add_cart_item', array_merge(
                    $cart_item_data, array(
                        'product_id' => absint($product_id),
                        'variation_id' => '',
                        'variation' => '',
                        'quantity' => $quantity,
                        'data' => $product_data
                    )
                ), $cart_item_key
                );
            }

            /**
             * Add child items to cart.
             *
             * Use this hook for compatibility instead of the 'woocommerce_add_to_cart' action hook to work around the recursion issue (solved in WP 4.7).
             * When the recursion issue is solved, we can simply replace calls to 'subscription_box_add_to_cart()' with direct calls to 'WC_Cart::add_to_cart()' and delete this function.
             *
             * @param str $cart_item_key
             * @param int $product_id
             * @param int $quantity
             * @param array $cart_item_data
             * @param int $container_id
             */
            do_action('wc_sub_box_add_to_cart', $cart_item_key, $product_id, $quantity, $cart_item_data, $container_id);

            return $cart_item_key;
        }

        /**
         * ceck if cart has subscription box
         * @return bool
         */
        public static function is_cart_has_wc_sub_box_product()
        {
            if (empty(WC()->cart->get_cart()))
                return false;

            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $product_id = $cart_item['product_id'];
                if (self::is_wc_sub_box_product($product_id))
                    return true;
            }
            return false;
        }

        /**
         * check if subscription has subscription box
         * @param $subscription
         * @return bool
         */
        public static function is_subscription_has_wc_sub_box_product($subscription)
        {

            if (!is_object($subscription) && is_numeric($subscription))
                $subscription = wcs_get_subscription($subscription);

            if ($subscription->get_items() <= 0)
                return false;

            if (!$subscription->has_status(array('active')))
                return false;

            foreach ($subscription->get_items() as $item) {
                if (($item->get_product()->is_type('subscription') || $item->get_product()->is_type('subscription_variation')) && WC_Sub_Box_Utility::is_wc_sub_box_product($item->get_product_id())) {
                    $product_id = $item->get_variation_id()??$item->get_product_id();
                    if (!empty(get_post_meta($subscription->get_id(), '_container_' . $product_id, true)))
                        return true;
                }
            }
            return false;

        }

        public static function is_order_has_wc_sub_box_product($order)
        {

            if(count($order->get_items()) <= 0)
                return false;

            foreach ($order->get_items() as $item) {
                if (($item->get_product()->is_type('subscription') || $item->get_product()->is_type('subscription_variation')) && WC_Sub_Box_Utility::is_wc_sub_box_product($item->get_product_id())) {
                    return true;
                }
            }
            return false;

        }

        /**
         * check if product is subscription box
         * @return bool
         */

        public static function wc_sub_box_is_edit_subscription_page()
        {
            global $wp;
            return is_page(wc_get_page_id('myaccount')) && isset($wp->query_vars[WC_Sub_Box_Product_Endpoint::WC_SUB_BOX_ENDPOINT]);
        }

        /**
         * @return WC_Sub_Box_Utility
         */
        public static function get_instance()
        {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }
    }


    WC_Sub_Box_Utility::get_instance();
}
