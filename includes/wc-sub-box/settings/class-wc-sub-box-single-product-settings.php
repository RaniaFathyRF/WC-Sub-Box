<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('WC_Sub_Single_Product_Settings')) {

    class WC_Sub_Single_Product_Settings
    {
        /**
         * @var WC_Sub_Single_Product_Settings
         */
        public static $instance;
        const PRODUCT_SCREEN = 'product';
        const SUBSCRIBTION_BOX_OPTION_ID = '_subscription_box';
        const SUBSCRIBTION_BOX_OPTION_KEY = 'subscription_box';
        const SUBSCRIBTION_BOX_PRODUCTS_IDS = 'wc_sub_products_ids';
        const SUBSCRIPTION_BOX_TAB_KEY = 'subscription_box_tab';
        const SUBSCRIPTION_BOX_TAB_TARGET_KEY = 'subscription_box_tab_id';


        private function __construct()
        {
            // admin enqueue scripts
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
            // add custom product type option
            add_filter('product_type_options', array($this, 'add_custom_sub_box_option'));
            // save custom product type option
            add_action('woocommerce_process_product_meta_subscription', array($this, 'save_custom_sub_box_option'), 10, 1);
            add_action('woocommerce_process_product_meta_variable-subscription', array($this, 'save_custom_sub_box_option'), 10, 1);
            // Creates the admin Subscription Box panel tabs.
            add_action('woocommerce_product_data_tabs', array($this, 'subscription_box_product_data_tabs'));
            // Subscription Box product tab content
            add_action('woocommerce_product_data_panels', array($this, 'subscription_box_product_tab_content'));

        }

        /**
         * admin enqueue scripts
         * @return void
         */
        public function admin_enqueue_scripts()
        {
            $screen = get_current_screen();

            if ($screen && strpos($screen->id, self::PRODUCT_SCREEN) === false)
                return;

            wp_enqueue_script('wc_sub_box_product', WC_SUB_BOX_URL . 'assets/js/admin/wc-sub-box-product.js', array('jquery'), WC_SUB_BOX_ASSETS_VERSION);

        }

        /**
         * add custom product type option
         * @param $options
         * @return mixed
         */
        public function add_custom_sub_box_option($options)
        {
            $options[self::SUBSCRIBTION_BOX_OPTION_KEY] = [
                'id' => self::SUBSCRIBTION_BOX_OPTION_ID,
                'wrapper_class' => 'show_if_subscription show_if_variable-subscription',
                'label' => __('Subscription Box', 'wc-sub-box-extra-actions'),
                'description' => __('Enable Subscription Box Feature', 'wc-sub-box-extra-actions'),
                'default' => '',
            ];

            return $options;
        }

        /**
         * save custom product type option
         * @param $product_id
         * @return void
         */
        public function save_custom_sub_box_option($product_id)
        {
            // save product type option
            $is_subscription_box = ($_POST[self::SUBSCRIBTION_BOX_OPTION_ID]) ? 'yes' : '';
            update_post_meta($product_id, self::SUBSCRIBTION_BOX_OPTION_ID, $is_subscription_box);
            // save subscription box products ids
            $wc_sub_products_ids = isset($_POST[self::SUBSCRIBTION_BOX_PRODUCTS_IDS]) ? $_POST[self::SUBSCRIBTION_BOX_PRODUCTS_IDS] : '';
            update_post_meta($product_id, self::SUBSCRIBTION_BOX_PRODUCTS_IDS, $wc_sub_products_ids);
        }

        /**
         * Creates the admin Subscription Box panel tabs.
         * @param $tabs
         * @return mixed
         */
        public function subscription_box_product_data_tabs($tabs)
        {
            $tabs[self::SUBSCRIPTION_BOX_TAB_KEY] = array(
                'label' => __('Subscription Box', 'wc-sub-box-extra-actions'),
                'target' => self::SUBSCRIPTION_BOX_TAB_TARGET_KEY,
                'class' => ['show_if_subscription_box', 'show_if_subscription', 'show_if_variable-subscription'],
            );
            return $tabs;
        }

        /**
         * Subscription Box product tab content
         * @return void
         */
        public function subscription_box_product_tab_content()
        {
            global $post;
            $product_id = $post->ID;
            $tab_content_id = self::SUBSCRIPTION_BOX_TAB_TARGET_KEY;
            $wc_sub_products_ids_key = self::SUBSCRIBTION_BOX_PRODUCTS_IDS;
            $wc_sub_products_ids = self::wc_sub_box_get_products_ids($product_id);

            include WC_SUB_BOX_PATH . 'includes/wc-sub-box/templates/admin/wc-sub-box-product-tab-content.php';
        }

        public static function is_wc_sub_box_product_enabled($product_id)
        {
            if (empty($product_id))
                return false;

            return get_post_meta($product_id, self::SUBSCRIBTION_BOX_OPTION_ID, true) ? true : false;
        }

        public static function wc_sub_box_get_products_ids($product_id)
        {
            if (empty($product_id))
                return [];
            return get_post_meta($product_id, self::SUBSCRIBTION_BOX_PRODUCTS_IDS, true) ?? [];
        }

        /**
         * @return WC_Sub_Single_Product_Settings
         */
        public static function get_instance()
        {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }

}
WC_Sub_Single_Product_Settings::get_instance();

