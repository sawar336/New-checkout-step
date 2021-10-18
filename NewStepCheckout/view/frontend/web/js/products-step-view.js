define([
    'ko',
    'uiComponent',
    'underscore',
    'Magento_Checkout/js/model/step-navigator',
    'mage/translate',
    'jquery',
    'Magento_Catalog/js/validate-product',
    'priceBox'
], function (ko, Component, _, stepNavigator, $t, $) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Custom_NewStepCheckout/products-step'
        },

        isVisible: ko.observable(true),

        /**
         * @return {*}
         */
        initialize: function () {
            this._super();

            stepNavigator.registerStep(
                'products_step',
                null,
                $t('Checkout Products'),
                this.isVisible,

                _.bind(this.navigate, this),
                9
            );

            return this;
        },

        /**
         * @returns void
         */
        navigate: function () {
            this.isVisible(true);
        },

        initPriceBox: function (productInfo) {
            if (productInfo.prices) {
                $("[data-role=priceBox][data-price-box=product-id-" + productInfo.id + "]").priceBox({
                    priceConfig: {
                        priceFormat: productInfo.prices.priceFormat,
                        prices: productInfo.prices.prices
                    }
                });
            }
        },

        initProductValidation: function () {
            $('[data-role=tocart-form]').productValidate({});
        },

        /**
         * @returns void
         */
        navigateToNextStep: function () {
            stepNavigator.next();
        }
    });
});
