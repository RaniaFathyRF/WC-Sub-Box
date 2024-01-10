<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('WC_Sub_Box_Product_Edit_Subscription')) {

    class WC_Sub_Box_Product_Edit_Subscription
    {
        /**
         * @var WC_Sub_Box_Product_Edit_Subscription
         */
        public static $instance;


        public function __construct()
        {
            // enqueue scripts
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
            // edit subscription page content
            add_action('woocommerce_account_' . WC_Sub_Box_Product_Endpoint::WC_SUB_BOX_ENDPOINT . '_endpoint', array($this, 'wc_sub_box_add_custom_edit_subscription_page'), 10, 1);
            // update subscription items
            add_action('template_redirect', array($this, 'wc_sub_box_update_subscription'), 99);
        }

        /**
         * enqueue scripts
         * @return void
         */
        public function enqueue_scripts()
        {

            if (empty(get_query_var(WC_Sub_Box_Product_Endpoint::WC_SUB_BOX_ENDPOINT)))
                return;
            $subscription = wcs_get_subscription(get_query_var(WC_Sub_Box_Product_Endpoint::WC_SUB_BOX_ENDPOINT));
            if (!WC_Sub_Box_Utility::is_subscription_has_wc_sub_box_product($subscription))
                return;

            $is_wc_sub_box = true;
            wp_enqueue_style('wc_sub_box_edit_subscription_style', WC_SUB_BOX_URL . 'assets/css/front/wc-sub-box-edit-subscription.css', array(), WC_SUB_BOX_ASSETS_VERSION);
            wp_enqueue_script('wc_sub_box_edit_subscription', WC_SUB_BOX_URL . 'assets/js/front/wc-sub-box-edit-subscription.js', array('jquery'), WC_SUB_BOX_ASSETS_VERSION);
            wp_localize_script('wc_sub_box_edit_subscription', 'wc_sub_box_subscription', [
                'wc_sub_box_currency' => get_woocommerce_currency_symbol(),
                'is_wc_sub_box' => $is_wc_sub_box,
            ]);
        }

        /**
         * edit subscription page content
         * @return false|void
         */
        public function wc_sub_box_add_custom_edit_subscription_page()
        {

            if (empty(get_query_var(WC_Sub_Box_Product_Endpoint::WC_SUB_BOX_ENDPOINT)))
                return;

            $subscription = wcs_get_subscription(get_query_var(WC_Sub_Box_Product_Endpoint::WC_SUB_BOX_ENDPOINT));
            if (empty($subscription->get_items()))
                return;

            if (!$subscription->has_status(array('active')))
                return false;
            $product_id = 0;
            foreach ($subscription->get_items() as $item) {
                if (($item->get_product()->is_type('subscription') || $item->get_product()->is_type('subscription_variation')) && WC_Sub_Box_Utility::is_wc_sub_box_product($item->get_product_id())) {
                    $product_id = $item->get_variation_id() ?? $item->get_product_id();
                    $parent_product_id = $item->get_product_id() ?? 0;
                    $subscription_childern = get_post_meta($subscription->get_id(), '_container_' . $product_id, true);
                    break;
                }
            }

            if (empty($product_id) || empty($parent_product_id) || empty($subscription_childern))
                return;

            $wc_sub_products_ids = WC_Sub_Single_Product_Settings::wc_sub_box_get_products_ids($parent_product_id);

            ob_start();
            include_once WC_SUB_BOX_PATH . 'templates/front/wc-sub-box-products-edit-subscription-content.php';
            $html = ob_get_clean();
            echo $html;
        }

        /**
         * update subscription items
         * @return void
         * @throws Exception
         */
        public function wc_sub_box_update_subscription()
        {

            //verify none
            if (!wp_verify_nonce($_POST['wc-sub-box-edit-subscription-nonce'], 'wc-sub-box-edit-subscription')) {
//                wc_add_notice(__('You do not have permission to edit this subscription.', 'wc-sub-box-extra-actions'), 'error');
                return;
            }

            if (empty($_POST['wc_sub_box_edit_subscription_id'])) {
//                wc_add_notice(__('Subscription not found', 'wc-sub-box-extra-actions'), 'error');
                return;
            }

            $subscription_id = esc_attr($_POST['wc_sub_box_edit_subscription_id']);
            $subscription = wcs_get_subscription($subscription_id);

            if (!WC_Sub_Box_Utility::is_subscription_has_wc_sub_box_product($subscription)) {
                wc_add_notice(__('Subscription doesn\'t contain WC Subscription Box', 'wc-sub-box-extra-actions'), 'error');
                return;
            }

            $configuration = WC_Sub_Box_Utility::wc_sub_box_get_posted_items_data($subscription_id);
            if (empty($configuration)) {
                wc_add_notice(__('Configuration is empty', 'wc-sub-box-extra-actions'), 'error');
                return;
            }
            $old_childerens = [];
            foreach ($subscription->get_items() as $item) {
                if (($item->get_product()->is_type('subscription') || $item->get_product()->is_type('subscription_variation')) && WC_Sub_Box_Utility::is_wc_sub_box_product($item->get_product_id())) {
                    $container_id = $item->get_variation_id() ?? $item->get_product_id();
                    $old_childerens = get_post_meta($subscription->get_id(), '_container_' . $container_id, true);
                    // delete old items from subscriptions
                    $this->wc_sub_box_delete_childern_items($container_id, $subscription);
                    // add new items to subscriptions
                    $this->wc_sub_box_add_children_items($container_id, $configuration, $subscription);
                }
            }
            // update subscription
            update_post_meta($subscription->get_id(), '_container_' . $container_id, $configuration);
            // add order note
            $this->wc_sub_box_get_order_note_message($old_childerens, $configuration, $subscription);
            // prepare redirect url
            wp_safe_redirect(esc_url(wc_get_account_endpoint_url('view-subscription')) . $subscription->get_id());
            exit;
        }

        /**
         * delete old items from subscriptions
         * @param $container_id
         * @param $subscription
         * @return void
         */
        public function wc_sub_box_delete_childern_items($container_id, $subscription)
        {
            if (empty($container_id) || empty($subscription))
                return;

            global $wpdb;
            $query = $wpdb->prepare("
                SELECT order_item_id
                FROM {$wpdb->prefix}woocommerce_order_itemmeta
                WHERE meta_key = %s
                AND meta_value = %s
            ", '_container_id', $container_id);
            $results = $wpdb->get_results($query);
            if (empty($results))
                return;
            foreach ($results as $result) {
                $order_item_id = $result->order_item_id;
                // delete subscription item
                $subscription->remove_item($order_item_id);
            }
        }

        /**
         * add new items to subscriptions
         * @param $container_id
         * @param $configuration
         * @param $subscription
         * @return void
         * @throws Exception
         */
        public function wc_sub_box_add_children_items($container_id, $configuration, $subscription)
        {
            if (empty($container_id) || empty($configuration) || empty($subscription))
                return;

            foreach ($configuration as $product_id => $quantity) {
                $child_item_id = $subscription->add_product(wc_get_product($product_id), $quantity);

                wc_update_order_item_meta($child_item_id, '_container_id', $container_id);

                $subscription->calculate_totals();
                $subscription->save();
            }
        }

        public function wc_sub_box_get_order_note_message($old_childerens, $configuration, $subscription)
        {

            if (empty($old_childerens) || empty($configuration) || empty($subscription))
                return;

            $old_childes_text_format = $this->wc_sub_box_get_childes_text_format($old_childerens);
            $new_childes_text_format = $this->wc_sub_box_get_childes_text_format($configuration);
            $subscription->add_order_note(sprintf(__('Subscription changed from "%s" to "%s"', 'wc-sub-box-extra-actions'), $old_childes_text_format, $new_childes_text_format));
        }

        public function wc_sub_box_get_childes_text_format($childes)
        {
            if (empty($childes))
                return '';
            $text = '';
            $index = 1;
            foreach ($childes as $key => $value) {
                $product = wc_get_product($key);
                if (count($childes) != $index)
                    $text .= $product->get_formatted_name() . ' x' . $value . ',';
                else
                    $text .= $product->get_formatted_name() . ' x' . $value;
                $index++;
            }
            return $text;
        }

        /**
         * @return WC_Sub_Box_Product_Edit_Subscription
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

WC_Sub_Box_Product_Edit_Subscription::get_instance();

