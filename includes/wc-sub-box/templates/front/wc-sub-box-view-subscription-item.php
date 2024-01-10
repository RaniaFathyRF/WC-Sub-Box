    <div class="wc-sub-box-view-subscription-item-container">
        <?php foreach ($container_childerns as $product_id => $quantity):
            $wc_product = wc_get_product($product_id);
            ?>
            <p class="wc-sub-box-item"><?php echo $wc_product->get_formatted_name() . ' x'. $quantity?></p>
        <?php endforeach; ?>
    </div>