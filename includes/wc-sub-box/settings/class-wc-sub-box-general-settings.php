<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('WC_Sub_General_Settings')) {

    class WC_Sub_General_Settings {
        /**
         * @var WC_Sub_General_Settings
         */
        public static $instance;

        CONST SUBSCRIPTION_BOX_ID = 'wc_sub_box_enable_subscription_box';
        CONST SUBSCRIPTION_BOX_ENABLE_KEY = 'enable_subscription_box';

        private function __construct() {
            // add custom setting for wc sub box
            add_filter('wc_sub_box_extra_actions_add_settings', array($this, 'wc_sub_box_add_settings_options'), 40);
        }

        /**
         * add custom setting for wc sub box
         * @param $settings
         * @return mixed
         */
        public function wc_sub_box_add_settings_options($settings){
            $settings[self::SUBSCRIPTION_BOX_ENABLE_KEY] = array(
                'name'     => __('Enable Subscription Box Feature','wc-sub-box-extra-actions'),
                'id'       => self::SUBSCRIPTION_BOX_ID,
                'type'     => 'checkbox',
                'default'  => 'no'
            );

            return $settings;
        }

        /**
         * check if subscription box is enabled
         * @return bool
         */
        public static function is_subscription_box_enabled_in_settings(){
            return WC_Sub_Box_Extra_Actions_Utility::wc_sub_box_get_settings_options(self::SUBSCRIPTION_BOX_ID) !='no' ? true:false;
        }

        /**
         * @return WC_Sub_General_Settings
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }

}
WC_Sub_General_Settings::get_instance();

