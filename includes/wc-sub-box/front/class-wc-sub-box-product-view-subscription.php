<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('WC_Sub_Box_Product_View_Subscription')) {

    class WC_Sub_Box_Product_View_Subscription
    {
        /**
         * @var WC_Sub_Box_Product_View_Subscription
         */
        public static $instance;

        const WC_SUB_BOX_ACTION_KEY = 'wc_sub_box_key';


        public function __construct()
        {
            // enqueue scripts
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
            // add custom class on items
            add_filter('woocommerce_order_item_class', array($this, 'wc_sub_box_add_item_class'), 10, 3);
            // add order item meta custom section
            add_action('woocommerce_order_item_meta_end', array($this, 'wc_sub_box_add_order_item_meta_end'), 10, 4);
            // hide remove icon from view subscription
            add_filter('wcs_can_items_be_removed', array($this, 'wc_sub_box_can_items_be_removed'), 10, 2);
            // add wc subscription box button
            add_filter('wcs_view_subscription_actions', array($this, 'wc_sub_box_add_button'), 40, 2);

        }

        /**
         * enqueue scripts
         * @return void
         */
        public function enqueue_scripts()
        {
            if (!wcs_is_view_subscription_page())
                return;
            $subscription_id = get_query_var('view-subscription');
            $subscription = wcs_get_subscription($subscription_id);
            if (!WC_Sub_Box_Utility::is_subscription_has_wc_sub_box_product($subscription))
                return;
            wp_enqueue_style('wc_sub_box_view_subscription_style', WC_SUB_BOX_URL . 'assets/css/front/wc-sub-box-view-subscription.css', array(), WC_SUB_BOX_ASSETS_VERSION);
            wp_enqueue_style('wc_sub_box_view_subscription_style', WC_SUB_BOX_URL . 'assets/css/front/wc-sub-box-edit-subscription.css', array(), WC_SUB_BOX_ASSETS_VERSION);
        }

        /**
         * add custom class on items
         * @param $class
         * @param $item
         * @param $subscription
         * @return mixed|string
         */
        public function wc_sub_box_add_item_class($class, $item, $subscription)
        {
            if (!empty($item->get_meta('_container_id')))
                $class .= ' wc-sub-box-item-child';

            return $class;
        }

        /**
         * add order item meta custom section
         * @param $item_id
         * @param $item
         * @param $subscription
         * @param $order
         * @return void
         */
        public function wc_sub_box_add_order_item_meta_end($item_id, $item, $subscription, $order)
        {
            $container_childerns = [];
            foreach ($subscription->get_items() as $item_data) {
                if ($item_id == $item_data->get_id() && ($item_data->get_product()->is_type('subscription') || $item_data->get_product()->is_type('subscription_variation')) && WC_Sub_Box_Utility::is_wc_sub_box_product($item_data->get_product_id())) {
                    $product_id = $item_data->get_variation_id() ?? $item_data->get_product_id();
                    $container_childerns = get_post_meta($subscription->get_id(), '_container_' . $product_id, true) ?? [];
                    break;
                }
            }
            if (empty($container_childerns))
                return;

            ob_start();
            include_once WC_SUB_BOX_PATH . 'templates/front/wc-sub-box-view-subscription-item.php';
            echo ob_get_clean();

        }

        /**
         * hide remove icon from view subscription
         * @param $allow_remove
         * @param $subscription
         * @return false|mixed
         */
        function wc_sub_box_can_items_be_removed($allow_remove, $subscription)
        {
            if (WC_Sub_Box_Utility::is_subscription_has_wc_sub_box_product($subscription))
                return false;

            return $allow_remove;
        }

        /**
         * add wc subscription box button
         * @param $actions
         * @param $subscription
         * @return mixed
         */
        public function wc_sub_box_add_button($actions, $subscription)
        {
            if (!WC_Sub_Box_Utility::is_subscription_has_wc_sub_box_product($subscription))
                return $actions;

            $url = esc_url(wc_get_account_endpoint_url(WC_Sub_Box_Product_Endpoint::WC_SUB_BOX_ENDPOINT)) . $subscription->get_id();
            $actions[self::WC_SUB_BOX_ACTION_KEY] = array(
                'url' => $url,
                'name' => __('WC Subscription Box', 'wc-sub-box-extra-actions'),
            );


            return $actions;
        }

        /**
         * @return WC_Sub_Box_Product_View_Subscription
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

WC_Sub_Box_Product_View_Subscription::get_instance();

