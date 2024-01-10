<div id='<?php echo $tab_content_id; ?>' class='panel woocommerce_options_panel'>
    <div class='options_group show_if_subscription_box show_if_subscription show_if_variable-subscription'>
        <div class="options_group">
            <p class="form-field">
                <label for="wc_sub_products_ids"><?php _e('Products', 'wc-sub-box-extra-actions'); ?></label>
                <select class="wc-product-search" multiple="" style="width: 100%;"
                        id="<?php echo $wc_sub_products_ids_key; ?>" name="<?php echo $wc_sub_products_ids_key; ?>[]"
                        data-placeholder="<?php _e('Search for a product…', 'wc-sub-box-extra-actions'); ?>"
                        data-action="woocommerce_json_search_products" data-exclude="<?php echo intval($product_id); ?>"
                        data-exclude_type="variable,subscription,external,grouped"
                        aria-hidden="true">
                    <?php
                    if (!empty($wc_sub_products_ids)) {
                        foreach ($wc_sub_products_ids as $wc_sub_products_id) {
                            $wc_sub_product = wc_get_product($wc_sub_products_id);
                            if (is_object($wc_sub_product)) {
                                echo '<option value="' . esc_attr($wc_sub_products_id) . '"' . selected(true, true, false) . '>' . esc_html(wp_strip_all_tags($wc_sub_product->get_formatted_name())) . '</option>';
                            }
                        }
                    }
                    ?>
                </select>
            </p>
        </div>
    </div>
</div>
