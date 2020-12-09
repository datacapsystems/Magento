/*jshint browser:true jquery:true*/
/*global alert*/

define(
    [
        'jquery',
        'uiComponent',
        'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator'
    ],
    function ($, Component, creditCardNumberValidator) {
       'use strict';
        $.each({
            'custom-validate-card-type': [
                function (num, item, allowedTypes) {
                    var number = num;
                    var cardInfo, i, l;

                    if (!creditCardNumberValidator(number).isValid) {
                        return false;
                    } else {
                        cardInfo = creditCardNumberValidator(number).card;

                        for (i = 0, l = allowedTypes[0].type.length; i < l; i++) {

                            if (cardInfo.type == allowedTypes[0].type[i]) {
                                return true;
                            }
                        }
                        $.mage.__('Credit card type not allowed ');
                        return false;
                    }
                },

                $.mage.__('Credit card type not allowed ')
            ]
        }, function (i, rule) {
            rule.unshift(i);
            $.validator.addMethod.apply($.validator, rule);
        });

        return Component.extend({});
    }

);
