/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'datacap_gateway',
                component: 'Vtn_Datacap/js/view/payment/method-renderer/datacap_gateway'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
