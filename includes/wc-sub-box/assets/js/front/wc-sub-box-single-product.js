jQuery(document).ready(function ($) {
    let wc_sub_box_single_product = {
        init: function () {
            wc_sub_box_single_product.handle_events();

        },
        handle_events: function () {
            $(document).on('change', 'input.wc-sub-box-product', function (e) {
                wc_sub_box_single_product.get_wc_sub_box_product_calculate_total();
                wc_sub_box_single_product.get_wc_sub_box_show_hide_add_to_cart_button();
            });
            $(document).on('change', 'input.wc-subscription-box-qty', function (e) {
                wc_sub_box_single_product.get_wc_sub_box_product_calculate_total();

            });
            $(document.body).on('show_variation', '', function (event, variation) {
                if (wc_sub_box_product.is_wc_sub_box) {
                    wc_sub_box_single_product.get_wc_sub_box_variation_price().hide();
               // $('.woocommerce-variation-description').hide();
               //      $('.woocommerce-variation-availability').hide();
                }
                    wc_sub_box_single_product.get_wc_sub_box_show_hide_add_to_cart_button();
            });
            $(document).on('keyup mouseup', 'input.wc-subscription-box-qty', function (e) {
                if (!$(this).val() || $(this).val() < 1)
                    $(this).val(1);

                $(this).trigger('change');

            });
        },
        get_wc_sub_box_product_calculate_total: function () {
            let total = 0;
            wc_sub_box_single_product.get_wc_sub_box_product_input_selector().each(function () {
                let parent = $(this).closest('.wc-sub-box-product-row');
                let qty = 0;
                let price = 0;
                if ($(this).is(":checked")) {
                    qty = parseInt(parent.find('.wc-subscription-box-qty').val() ?? 1);
                    price = parseFloat(parent.attr('data-product_price') ?? 0);
                    if (qty && price)
                        total += (qty * price);

                }
            });
            let formated_total = wc_sub_box_single_product.number_format(total, 2) + ' ' + wc_sub_box_product.wc_sub_box_currency;
            wc_sub_box_single_product.get_wc_sub_box_products_totals_selector().html(formated_total);

        },
        get_wc_sub_box_show_hide_add_to_cart_button: function () {
            if (wc_sub_box_single_product.is_wc_sub_box_products_selected() &&
                !wc_sub_box_single_product.get_wc_sub_box_add_to_cart_button_selector().hasClass('wc-variation-is-unavailable') &&
                !wc_sub_box_single_product.get_wc_sub_box_add_to_cart_button_selector().hasClass('wc-variation-selection-needed')
            )
                wc_sub_box_single_product.get_wc_sub_box_add_to_cart_button_selector().removeClass('disabled');
            else
                wc_sub_box_single_product.get_wc_sub_box_add_to_cart_button_selector().addClass('disabled');


        },
        is_wc_sub_box_products_selected: function () {
            let result = false;
            wc_sub_box_single_product.get_wc_sub_box_product_input_selector().each(function () {
                if ($(this).is(":checked")) {
                    result = true;
                }
            });
            return result;
        },
        get_wc_sub_box_product_input_selector: function () {
            return $('.wc-sub-box-products-data-container .wc-sub-box-product');
        },
        get_wc_sub_box_products_totals_selector: function () {
            return $('.product-totals .products-totals-value');
        },
        get_wc_sub_box_add_to_cart_button_selector: function () {
            return $('.single_add_to_cart_button');
        },
        get_wc_sub_box_variation_price: function () {
            return $('.woocommerce-variation-price');
        },
        number_format: function (number, decimals, dec_point, thousands_sep) {
            number = (number + '')
                .replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function (n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + (Math.round(n * k) / k)
                        .toFixed(prec);
                };
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
                .split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '')
                .length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1)
                    .join('0');
            }
            return s.join(dec);
        },
    }

    wc_sub_box_single_product.init();
});
