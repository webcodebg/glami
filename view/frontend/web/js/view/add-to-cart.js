/*
 * @package      Webcode_Glami
 *
 * @author       Webcode, Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2021 GLAMI Inspigroup s.r.o.
 * @license      See LICENSE.txt for license details.
 */

define([
    'ko',
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (ko, Component, customerData) {
    'use strict';
    return Component.extend({
        initialize: function () {
            var self = this;
            self._super();
            customerData.get('glami-add-to-cart').subscribe(function (loadedData) {
                if (typeof loadedData.item_ids !== 'undefined') {
                    glami('track', 'addToCart', loadedData);
                    customerData.set('glami-add-to-cart', {});
                }
            });
        }
    });
});
