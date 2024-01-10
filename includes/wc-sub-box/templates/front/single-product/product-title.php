<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<?php
if ($wc_sub_product->is_visible()) {
    ?>
    <a href="<?php echo $wc_sub_product->get_permalink(); ?>" target="_blank">
           <span class="product-title-wrap">
            <?php echo $wc_sub_product->get_title(); ?>
           </span>
    </a>
    <?php
} else {
    ?>
    <span class="product-title-wrap">
        <?php
        echo $wc_sub_product->get_title();
        ?>
    </span>
    <?php
}