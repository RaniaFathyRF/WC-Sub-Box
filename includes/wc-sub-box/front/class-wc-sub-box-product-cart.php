<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('WC_Sub_Box_Product_Cart')) {

    class WC_Sub_Box_Product_Cart
    {
        /**
         * @var WC_Sub_Box_Product_Cart
         */
        public static $instance;

        const WC_SUB_BOX_ITEMS_KEY = 'wc_sub_box_items';
        const WC_SUB_BOX_CONTENTS_KEY = 'wc_sub_box_contents';

        const WC_SUB_BOX_CONTAINERS_KEY = 'wc_sub_box_container_key';
        const WC_SUB_BOX_CONTAINERS_ID = 'wc_sub_box_container_id';

        private function __construct()
        {
            // enqueue scripts
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
            // Add Subscription Box items configuration data.
            add_filter('woocommerce_add_cart_item_data', array($this, 'wc_sub_box_add_cart_item_data'), 99, 2);
            // add subscription items data in cart
            add_action('woocommerce_add_to_cart', array($this, 'wc_sub_box_add_to_cart'), 20, 6);
            // validate subscription items data in cart
            add_filter('woocommerce_add_to_cart_validation', array($this, 'wc_sub_box_add_to_cart_validation'), 10, 6);
            // Hide quantity field in cart
            add_filter('woocommerce_store_api_product_quantity_editable', array($this, 'wc_sub_box_disable_cart_item_quantity'), 99, 3);
            // remove cart container childern when container removed
            add_action('woocommerce_cart_item_removed', array($this, 'wc_sub_box_cart_item_removed'), 10, 2);
            // add custom class on items if woocommerce block is active
            add_filter('woocommerce_cart_item_class', array($this, 'wc_sub_box_add_classes'), 10, 3);
            // add custom cart item name
            add_filter('woocommerce_cart_item_name', array($this, 'wc_sub_box_add_item_name'), 10, 3);
            // display childs for parent sub boc in cart and checkout
            add_filter('woocommerce_get_item_data', array($this, 'wc_sub_box_display_childern_data'), 10, 2);
            // set parent product prive with zero
            add_action('woocommerce_before_calculate_totals', array($this, 'wc_sub_box_recalculate_cart_totals'), 99, 1);
            add_action('woocommerce_after_calculate_totals', array($this, 'wc_sub_box_recalculate_cart_totals'), 99, 1);
            add_action('woocommerce_cart_item_subtotal', array($this, 'wc_sub_box_change_cart_item_sub_total'), 99, 3);
            // remove quantity in parent product
            add_filter('woocommerce_cart_item_quantity', array($this, 'wc_sub_box_remove_quantity'), 10, 3); // PHPCS: XSS ok.
            // check if cart has subscription product
            add_filter('wc_sub_box_is_cart_has_subscription_box', array($this, 'wc_sub_box_is_cart_has_subscription_product'), 10);
        }

        public function wc_sub_box_remove_quantity($product_quantity, $cart_item_key, $cart_item)
        {
            if (WC_Sub_Box_Utility::is_wc_sub_box_container_cart_item($cart_item))
                return '';
            return $product_quantity;
        }

        public function enqueue_scripts()
        {
            if (!is_cart() && !is_checkout())
                return;
            wp_enqueue_style('wc_sub_box_cart', WC_SUB_BOX_URL . 'assets/css/front/wc-sub-box-cart.css', array(), WC_SUB_BOX_ASSETS_VERSION);
        }

        /**
         * Add Subscription Box items configuration data.
         * @param $cart_item_data
         * @param $product_id
         * @return mixed
         */
        public function wc_sub_box_add_cart_item_data($cart_item_data, $product_id)
        {
            if (!WC_Sub_Box_Utility::is_wc_sub_box_product($product_id))
                return $cart_item_data;

            if (!WC_Sub_Box_Extra_Actions_Utility::is_subscription_product($product_id))
                return $cart_item_data;

            // Create a unique array with the sb configuration.
            if (isset($cart_item_data[self::WC_SUB_BOX_ITEMS_KEY]))
                return $cart_item_data;


            $configuration = WC_Sub_Box_Utility::wc_sub_box_get_posted_items_data($product_id);
            if (empty($configuration))
                return $cart_item_data;

            // Add the array to the container item's data.
            $cart_item_data[self::WC_SUB_BOX_ITEMS_KEY] = $configuration;

            // Add an empty contents array to the item's data.
            if (!isset($cart_item_data[self::WC_SUB_BOX_CONTENTS_KEY]))
                $cart_item_data[self::WC_SUB_BOX_CONTENTS_KEY] = array();


            return $cart_item_data;
        }

        /**
         * check if subscription has items key
         * @param $cart_item
         * @return bool
         *
         */
        public static function has_wc_sub_box_items($cart_item)
        {
            if (empty($cart_item))
                return false;

            if (!empty($cart_item[self::WC_SUB_BOX_ITEMS_KEY]))
                return true;

            return false;
        }

        /**
         * check if subscription has content key
         * @param $cart_item
         * @return bool
         */
        public static function has_wc_sub_box_contents($cart_item)
        {
            if (empty($cart_item))
                return false;

            if (isset($cart_item[self::WC_SUB_BOX_CONTENTS_KEY]))
                return true;

            return false;
        }

        /**
         * check if subscription has container data
         * @param $cart_item
         * @return bool
         */
        public static function has_wc_sub_box_container_data($cart_item)
        {
            if (empty($cart_item))
                return false;

            if (!empty($cart_item[self::WC_SUB_BOX_CONTAINERS_ID]))
                return true;

            return false;
        }

        /**
         * add subscription items data in cart
         * @param $item_cart_key
         * @param $product_id
         * @param $quantity
         * @param $variation_id
         * @param $variation
         * @param $cart_item_data
         * @return void
         */
        public
        function wc_sub_box_add_to_cart($item_cart_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
        {
            //check if it is parent item
            if (!WC_Sub_Box_Utility::is_wc_sub_box_container_cart_item($cart_item_data))
                return;

            $wc_sub_box_cart_item_data = array(
                self::WC_SUB_BOX_CONTAINERS_KEY => $item_cart_key,
                self::WC_SUB_BOX_CONTAINERS_ID => $variation_id > 0 ? $variation_id : $product_id
            );

            // Now add all items - yay!
            if (empty($cart_item_data[self::WC_SUB_BOX_ITEMS_KEY]))
                return;

            foreach ($cart_item_data[self::WC_SUB_BOX_ITEMS_KEY] as $id => $qty) {

                /**
                 * Before child item is added to cart.
                 *
                 * @param int $id The child item product ID.
                 * @param int $qty The quantity of the child item in the container.
                 * @param array $wc_sub_box_cart_item_data Child item product data.
                 */
                do_action('wc_sub_box_before_item_add_to_cart', $id, $qty, $wc_sub_box_cart_item_data);

                // Add to cart.
                $subscription_box_item_cart_key = WC_Sub_Box_Utility::wc_sub_box_items_add_to_cart($product_id, $id, $qty, $wc_sub_box_cart_item_data);

                if ($subscription_box_item_cart_key) {

                    if (!isset(WC()->cart->cart_contents[$subscription_box_item_cart_key][self::WC_SUB_BOX_CONTENTS_KEY])) {

                        WC()->cart->cart_contents[$item_cart_key][self::WC_SUB_BOX_CONTENTS_KEY] = array();
                    } elseif (!in_array($subscription_box_item_cart_key, WC()->cart->cart_contents[$item_cart_key][self::WC_SUB_BOX_CONTENTS_KEY])) {

                        WC()->cart->cart_contents[$item_cart_key][self::WC_SUB_BOX_CONTENTS_KEY][] = $subscription_box_item_cart_key;
                    }
                }

                /**
                 * After child item is added to cart.
                 *
                 * @param int $subscription_box_product_id The child item product ID.
                 * @param int $subscription_box_quantity The quantity of the child item in the container.
                 * @param int $subscription_box_variation_id The child item variation ID.
                 * @param array $subscription_box_variations Attributes of specific variation being added to cart.
                 * @param array $subscription_box_cart_item_data Child item product data.
                 */
                do_action('woocommerce_subscription_box_after_subscription_box_add_to_cart', $subscription_box_product_id, $subscription_box_quantity, $subscription_box_variation_id, $subscription_box_variations, $subscription_box_cart_item_data);
            }
        }

        /**
         * validate subscription items data in cart
         * @param $passed_validation
         * @param $product_id
         * @param $quantity
         * @param $variation_id
         * @param $variations
         * @param $cart_item_data
         * @return false|mixed
         */
        public function wc_sub_box_add_to_cart_validation($passed_validation, $product_id, $quantity, $variation_id = '', $variations = array(), $cart_item_data = array())
        {
            // check if product is subscription box
            if (!WC_Sub_Box_Utility::is_wc_sub_box_product($product_id))
                return $passed_validation;
            // check if product is simple/variable subscription
            if (!WC_Sub_Box_Extra_Actions_Utility::is_subscription_product($product_id))
                return $passed_validation;
            if (!apply_filters('wc_sub_box_cart_validation', true, $product_id, $quantity, $variation_id, $variations, $cart_item_data)) {
                wc_add_notice(WC_Sub_Box_Extra_Actions_Utility::wc_sub_box_get_one_only_subscription_message(), 'error');
                return false;
            }

            //check if cart already has subscription box
            if (WC_Sub_Box_Utility::is_cart_has_wc_sub_box_product()) {
                wc_add_notice(WC_Sub_Box_Extra_Actions_Utility::wc_sub_box_get_one_only_subscription_message(), 'error');
                return false;
            }


            return $passed_validation;
        }

        /**
         * Hide quantity field in cart
         * @param $value
         * @param $cart_item_key
         * @param $cart_item
         * @return false|mixed
         */
        public function wc_sub_box_disable_cart_item_quantity($value, $cart_item_key, $cart_item)
        {
            if (WC_Sub_Box_Utility::is_wc_sub_box_container_cart_item($cart_item) || WC_Sub_Box_Utility::is_wc_sub_box_child_cart_item($cart_item))
                return false;
            return $value;
        }

        /**
         * remove cart container childern when container removed
         * @param $cart_item_key
         * @param $cart
         * @return void
         */
        public function wc_sub_box_cart_item_removed($cart_item_key, $cart)
        {
            if (WC_Sub_Box_Utility::is_wc_sub_box_container_cart_item($cart->removed_cart_contents[$cart_item_key])) {
                $wc_sub_box_key = $cart_item_key;
                foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
                    if ($cart_item[self::WC_SUB_BOX_CONTAINERS_KEY] == $wc_sub_box_key) {
                        /** WC core action. */
                        do_action('woocommerce_remove_cart_item', $cart_item_key, $cart);

                        unset($cart->cart_contents[$cart_item_key]);

                        /** Triggered when a composited item is removed from the cart.
                         *
                         * @param string $cart_item_key
                         * @param WC_Cart $cart
                         * @since  8.3.4
                         *
                         * @hint   Bypass WC_Cart::remove_cart_item to avoid issues with performance and loops.
                         *
                         */
                        do_action('wc_sub_box_cart_item_removed', $cart_item_key, $cart);
                    }
                }
            }
        }

        /**
         * add custom class on items if woocommerce block is active
         * @param $class
         * @param $cart_item
         * @param $cart_item_key
         * @return mixed|string
         */
        public function wc_sub_box_add_classes($class, $cart_item, $cart_item_key)
        {
            if (WC_Sub_Box_Utility::is_wc_sub_box_container_cart_item($cart_item))
                return $class .= ' wc-sub-box-item-container';
            if (WC_Sub_Box_Utility::is_wc_sub_box_child_cart_item($cart_item))
                return $class .= ' wc-sub-box-item-child';
            return $class;
        }

        /**
         * add custom cart item name
         * @param $name
         * @param $cart_item
         * @param $cart_item_key
         * @return string
         */
        function wc_sub_box_add_item_name($name, $cart_item, $cart_item_key)
        {
            if (WC_Sub_Box_Utility::is_wc_sub_box_child_cart_item($cart_item))
                return $name . ' x' . $cart_item['quantity'];
            return $name;
        }

        function wc_sub_box_display_cart_childern_data($cart_item, $cart_item_key)
        {
            $container_childerns = [];
            if (!WC_Sub_Box_Utility::is_wc_sub_box_container_cart_item($cart_item))
                return;

            if (!isset($cart_item[self::WC_SUB_BOX_ITEMS_KEY]) || empty($cart_item[self::WC_SUB_BOX_ITEMS_KEY]) || !is_array($cart_item[self::WC_SUB_BOX_ITEMS_KEY]))
                return;
            $container_childerns = $cart_item[self::WC_SUB_BOX_ITEMS_KEY];
            if (empty($container_childerns))
                return;

            ob_start();
            include_once WC_SUB_BOX_PATH . 'templates/front/wc-sub-box-cart-item.php';
            echo ob_get_clean();
        }

        public function wc_sub_box_display_childern_data($item_data, $cart_item)
        {
            if (!WC_Sub_Box_Utility::is_wc_sub_box_container_cart_item($cart_item))
                return $item_data;

            if (!isset($cart_item[self::WC_SUB_BOX_ITEMS_KEY]) || empty($cart_item[self::WC_SUB_BOX_ITEMS_KEY]) || !is_array($cart_item[self::WC_SUB_BOX_ITEMS_KEY]))
                return $item_data;

            foreach ($cart_item[self::WC_SUB_BOX_ITEMS_KEY] as $product_id => $quantity) {
                $product = wc_get_product($product_id);
                $key = $product->get_formatted_name() . ' x' . $quantity;
                $label = wc_get_price_to_display($product, array('qty' => $quantity));

                $item_data[] = array(
                    'key' => $key,
                    'value' => wc_price($label),
                );

            }

            return $item_data;
        }

        public function wc_sub_box_recalculate_cart_totals($cart)
        {

            foreach ($cart->get_cart() as $cart_item_key => $cart_item) {

                if (!WC_Sub_Box_Utility::is_wc_sub_box_container_cart_item($cart_item))
                    continue;

                $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                // will not effect product price only product vat
                $_product->set_price(0);
            }

        }

        public function wc_sub_box_change_cart_item_sub_total($subtotal, $cart_item, $cart_item_key)
        {
            if (WC_Sub_Box_Utility::is_wc_sub_box_container_cart_item($cart_item))
                return 0;
            return $subtotal;
        }

        public function wc_sub_box_is_cart_has_subscription_product()
        {
            if (WC_Sub_Box_Utility::is_cart_has_wc_sub_box_product())
                return true;
            return false;
        }

        /**
         * @return WC_Sub_Box_Product_Cart
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

WC_Sub_Box_Product_Cart::get_instance();

