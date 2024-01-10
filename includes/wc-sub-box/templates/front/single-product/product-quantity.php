<?php
if (!defined('ABSPATH')) {
    exit;
}

global $product;

if ($wc_sub_product->is_in_stock() && $wc_sub_product->is_in_stock()) {

    /**
     * The quantity input name.
     */
    $input_name = 'wc_sub_box_qty';
    $input_name = apply_filters('wc_sub_box_product_qty_name', $input_name, $product, $wc_sub_product);

    /**
     * The quantity input value.
     */
    $quantity = isset($_REQUEST[$input_name]) && !empty($_REQUEST[$input_name][$wc_sub_product_id]) ? intval($_REQUEST[$input_name][$wc_sub_product_id]) : apply_filters('wc_subscription_box_item_quantity_input', 1, $wc_sub_product, $product);

    if (!empty($subscription_childern))
        $quantity = $subscription_childern[$wc_sub_product_id] ?? 1;


    /**
     * Filter woocommerce_subscription_box_item_quantity_input_args.
     *
     * @param array $args
     * @param obj WC_Product
     * @param obj WC_Subscription_Box
     */
    $input_args = apply_filters(
        'wc_sub_box_item_quantity_input_args',
        array(
            'input_name' => $input_name . '[' . $wc_sub_product_id . ']',
            'input_value' => $quantity,
            'min_value' => apply_filters('woocommerce_subscription_box_quantity_input_min', 1, $wc_sub_product),
            'max_value' => apply_filters('woocommerce_subscription_box_quantity_input_max', $wc_sub_product->get_max_purchase_quantity(), $wc_sub_product),
            'placeholder' => 0,
            'step' => apply_filters('woocommerce_subscription_box_quantity_input_step', 1),
            'classes' => array('qty', 'wc-subscription-box-qty'),
        ),
        $wc_sub_product,
        $product
    );

    woocommerce_quantity_input($input_args, $wc_sub_product);

}
