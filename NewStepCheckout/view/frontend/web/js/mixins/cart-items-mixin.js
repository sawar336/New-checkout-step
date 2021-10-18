define([
    'Magento_Checkout/js/model/totals'
], function (totals) {
    'use strict';

    return function (Component) {
        return Component.extend({
            getItemsQty: function () {
                return +totals.totals()['items_qty'];
            },
        });
    }
});
