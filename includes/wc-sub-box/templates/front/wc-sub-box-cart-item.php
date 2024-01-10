<dl class="wc-sub-box-view-subscription-item-container">
    <?php foreach ($container_childerns as $product_id => $quantity):
        $wc_product = wc_get_product($product_id);
        ?>
        <dt class="wc-sub-box-item"><?php echo $wc_product->get_formatted_name() . ' x' . $quantity .': '. wc_price($wc_product->get_price()); ?></dt>
    <?php endforeach; ?>
</dl>