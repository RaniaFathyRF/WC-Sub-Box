// Aliased import
import { applyCheckoutFilter } from '@woocommerce/blocks-checkout';

const { registerCheckoutFilters } = window.wc.blocksCheckout;

const modifyShowRemoveItemLink = ( defaultValue, extensions, args ) => {
    const isCartContext = args?.context === 'cart';

    if ( ! isCartContext ) {
        return defaultValue;
    }

    // if ( args?.cartItem?.name === 'Beanie with Logo' ) {
    //     return false;
    // }
    //
    // if ( args?.cartItem?.name === 'Sunglasses' ) {
    //     return false;
    // }

    return false;
};

registerCheckoutFilters( 'example-extension', {
    showRemoveItemLink: modifyShowRemoveItemLink,
} );
// import { applyCheckoutFilter } from '@woocommerce/blocks-checkout';

// Global import
// const { applyCheckoutFilter } = wc.blocksCheckout;
//
// const options = {
//     filterName: 'showRemoveItemLink',
//     defaultValue: false,
// };
//
// const filteredValue = applyCheckoutFilter( options );
