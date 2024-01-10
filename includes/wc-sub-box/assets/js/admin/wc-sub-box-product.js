jQuery(document).ready(function ($) {

    // on change product type
    $(document.body).on('woocommerce-product-type-change', function () {
       setTimeout(function(){
           show_hide_sub_box();
       },1000);
    });
    // on change subscription box option
    $('input#_subscription_box').on('change', function () {
        show_hide_sub_box();
    });
    // on load
    $(window).on('load', function () {
        show_hide_sub_box();
    });
});

function show_hide_sub_box() {
    var is_subscription_box = jQuery('input#_subscription_box:checked').length;
    var selector = jQuery('.show_if_subscription_box');
    (is_subscription_box > 0) ? selector.show() : selector.hide();
}