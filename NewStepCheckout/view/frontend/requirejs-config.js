var config = {
    config: {
        mixins: {
            'Magento_Catalog/js/catalog-add-to-cart': {
                'Custom_NewStepCheckout/js/mixins/catalog-add-to-cart-mixin': true
            },
            'Magento_Checkout/js/view/summary/cart-items': {
                'Custom_NewStepCheckout/js/mixins/cart-items-mixin': true
            }
        }
    }
};
