<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('WC_Sub_Box_Product_Endpoint')) {

    class WC_Sub_Box_Product_Endpoint
    {
        /**
         * @var WC_Sub_Box_Product_Endpoint
         */
        public static $instance;

        const WC_SUB_BOX_ENDPOINT = 'edit-subscription';


        public function __construct()
        {
            // add edit subscription endpoint
            add_action('init', array($this, 'wc_sub_box_add_endpoint'));
            // set edit subscription endpoint in query vars
            add_filter('query_vars', array($this, 'wc_sub_box_custom_query_vars'), 20);
            add_filter('woocommerce_get_query_vars', array($this, 'wc_sub_box_custom_query_vars'), 20);
            // flush rewrite rules
            add_action('wp_loaded', array($this, 'wc_sub_box_custom_flush_rewrite_rules'));
        }

        /**
         * add edit subscription endpoint
         * @return void
         */
        public function wc_sub_box_add_endpoint()
        {
            add_rewrite_endpoint(self::WC_SUB_BOX_ENDPOINT, EP_ROOT | EP_PAGES);
        }

        /**
         * set edit subscription endpoint in query vars
         * @param $vars
         * @return mixed
         */
        function wc_sub_box_custom_query_vars($vars)
        {
            $vars[self::WC_SUB_BOX_ENDPOINT] = self::WC_SUB_BOX_ENDPOINT;
            return $vars;
        }

        /**
         * flush rewrite rules
         * @return void
         */
        function wc_sub_box_custom_flush_rewrite_rules()
        {
            flush_rewrite_rules();
        }



        /**
         * @return WC_Sub_Box_Product_Endpoint
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

WC_Sub_Box_Product_Endpoint::get_instance();

