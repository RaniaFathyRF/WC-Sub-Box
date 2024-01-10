<?php if (!empty($wc_sub_products_ids)): ?>
    <div class="wc-sub-box-products-widget-container">
        <form method="post" enctype="multipart/form-data" class="subscription-box-weekly-items cart cart-group">
         <?php wp_nonce_field('wc-sub-box-edit-subscription', 'wc-sub-box-edit-subscription-nonce'); ?>
            <div class="wc-sub-box-products-data-container">
                <?php foreach ($wc_sub_products_ids as $wc_sub_product_id): ?>
                    <?php
                    $wc_sub_product = wc_get_product($wc_sub_product_id);
                    if (WC_Sub_Box_Utility::is_purchasable_product($wc_sub_product)):
                        ?>
                        <div class="wc-sub-box-group <?php echo $wc_sub_product_id; ?>"
                             data-product-parent-price="<?php echo $wc_sub_product->get_price(); ?>">
                            <div class="wc-sub-box-product-row"
                                 data-product_price="<?php echo $wc_sub_product->get_price(); ?>">
                                <div class="product" data-price="<?php echo $wc_sub_product->get_price(); ?>">
                                    <div class="product-info">
                                        <div class="product-name">
                                            <input class='wc-sub-box-product' type="checkbox"
                                                   name="wc_sub_box_product[<?php echo $wc_sub_product_id; ?>][]"
                                                   id="wc_sub_box_product_<?php echo $wc_sub_product_id; ?>" <?php echo (!empty($subscription_childern) && in_array($wc_sub_product_id,array_keys($subscription_childern)))?  'checked' :'';?> value="1">
                                            <?php
                                            // get product title
                                            include WC_SUB_BOX_PATH . 'templates/front/single-product/product-title.php';
                                            ?>
                                        </div>
                                        <div class="product-price">
                                            <?php
                                            // get product price
                                            include WC_SUB_BOX_PATH . 'templates/front/single-product/product-price.php';
                                            ?>
                                        </div>
                                    </div>
                                    <div class="product-qty" data-product-id="<?php echo $wc_sub_product_id; ?>">
                                        <?php
                                        // get product qty
                                        include WC_SUB_BOX_PATH . 'templates/front/single-product/product-quantity.php';
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php endforeach; ?>
            </div>
            <div class="wc-sub-box-products-calculate-products-container">
                <div class="product-totals">
                    <?php
                    // get products totals
                    include WC_SUB_BOX_PATH . 'templates/front/single-product/products-totals.php';
                    ?>
                </div>

            </div>
            <div class="wc-sub-box-products-update">
                    <button type="submit" class="wc-sub-box-products-update-button button alt wp-element-button "><?php _e('Update', 'wc-sub-box-extra-actions'); ?></button>
            </div>
            <input type="hidden" name="wc_sub_box_edit_subscription_id" value="<?php echo get_query_var(WC_Sub_Box_Product_Endpoint::WC_SUB_BOX_ENDPOINT);?>">
    </div>
    </div>
    </div>

<?php endif; ?>
