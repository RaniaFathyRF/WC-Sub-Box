<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('WC_Sub_Box_Single_Product')) {

    class WC_Sub_Box_Single_Product
    {
        /**
         * @var WC_Sub_Box_Single_Product
         */
        public static $instance;

        private function __construct()
        {
            // enqueue scripts
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
            // add subscription box product widget
            add_action('woocommerce_before_add_to_cart_button', array($this, 'add_subscription_box_products_widget'));
            // hide quqntity input in single product
            add_filter('woocommerce_is_sold_individually', array($this, 'remove_wc_sub_box_product_quantity'), 10, 2);
            // hide wc subscription box product price
            add_filter('woocommerce_get_price_html', array($this, 'remove_wc_sub_box_product_price'), 10, 2);
        }


        /**
         * enqueue scripts
         * @return void
         */
        public function enqueue_scripts()
        {
            global $post;
            if (empty($post))
                return;
            $product_id = $post->ID;

            if (empty($product_id))
                return;

            if (!WC_Sub_Box_Utility::is_wc_sub_box_product($product_id))
                return;
            $is_wc_sub_box = true;
            wp_enqueue_style('wc_sub_box_single_product_style', WC_SUB_BOX_URL . 'assets/css/front/wc-sub-box-single-product.css', array(), WC_SUB_BOX_ASSETS_VERSION);
            wp_enqueue_script('wc_sub_box_single_product', WC_SUB_BOX_URL . 'assets/js/front/wc-sub-box-single-product.js', array('jquery'), WC_SUB_BOX_ASSETS_VERSION);
            wp_localize_script('wc_sub_box_single_product', 'wc_sub_box_product', [
                'wc_sub_box_currency' => get_woocommerce_currency_symbol(),
                'is_wc_sub_box' => $is_wc_sub_box,
            ]);
        }
        /*
         * add subscription box product widget
         */
        public function add_subscription_box_products_widget()
        {

            global $product;
            $product_id = $product->get_id();

            if (empty($product_id))
                return;

            if (!WC_Sub_Box_Utility::is_wc_sub_box_product($product_id))
                return;

            $wc_sub_products_ids = WC_Sub_Single_Product_Settings::wc_sub_box_get_products_ids($product_id);

            ob_start();
            include WC_SUB_BOX_PATH . 'templates/front/wc-sub-box-products-widget-content.php';
            $html = ob_get_clean();
            echo $html;
        }
        /**
         * hide quqntity input in single product
         * @param $result
         * @param $product
         * @return mixed|true
         */
        public function remove_wc_sub_box_product_quantity($result, $product)
        {
            if (!WC_Sub_Box_Utility::is_wc_sub_box_product($product))
                return $result;

            return true;

        }

        /**
         * hide wc subscription box product price
         * @param $price
         * @param $product
         * @return mixed|string
         */
        public function remove_wc_sub_box_product_price($price, $product)
        {
            if (!is_product())
                return $price;

            if (!WC_Sub_Box_Utility::is_wc_sub_box_product($product))
                return $price;

            if (!WC_Sub_Box_Utility::is_purchasable_product($product))
                return $price;

            return '';
        }

        /**
         * @return WC_Sub_Box_Single_Product
         */
        public static function get_instance()
        {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }

}
WC_Sub_Box_Single_Product::get_instance();

