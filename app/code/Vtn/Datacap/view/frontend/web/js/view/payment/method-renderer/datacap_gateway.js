/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/

define([
    'Magento_Payment/js/view/payment/cc-form',
    'jquery',
    'mage/translate',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/action/redirect-on-success',
    'Magento_Payment/js/model/credit-card-validation/validator',

], function (Component, $, $t, additionalValidators, redirectOnSuccessAction) {

    'use strict';
    return Component.extend({

        defaults: {
            template: 'Vtn_Datacap/payment/form',
            dataEvent:"",
            dataFunction:"",
        },

        /**
         * @returns {exports.initialize}
         */
        initialize: function () {
            var environment = this.getEnvironment();
            if (environment == "production") {
                $('head').append('<script type="text/javascript" charset="utf-8" async="" data-requirecontext="_" data-requiremodule="devCDN" src="https://token.dcap.com/v1/client/"></script>');
            } else {
                $('head').append('<script type="text/javascript" charset="utf-8" async="" data-requirecontext="_" data-requiremodule="devCDN" src="https://token-cert.dcap.com/v1/client/"></script>');
            }
            this._super();
        },

        /**
         * Get data
         * @returns {Object}
         */
        getData: function () {

            return {
                'method': this.item.method,
                'additional_data': {
                    "datacap_payment_token": $("#datacap_payment_token").val()
                },

            };
        },

        /**
         * Returns state of place order button
         *
         * @returns {Boolean}
         */
        isButtonActive: function () {
            return this.isActive() && this.isPlaceOrderActionAllowed();
        },


        getCode: function () {
            return 'datacap_gateway';
        },

        isActive: function () {
            return window.checkoutConfig.payment.datacap_gateway.isActive;
        },

        validate: function () {
            var $form = $('#' + this.getCode() + '-form');

            return $form.validation() && $form.validation('isValid');
        },

        /**
         * Get list of available credit card types
         * @returns {Object}
         */
        getCcAvailableTypes: function () {
            return window.checkoutConfig.payment.datacap_gateway.availableCardTypes;
        },


        /**
         * Get list of available credit card types values
         * @returns {Object}
         */
        getCcAvailableTypesValues: function () {
            return _.map(this.getCcAvailableTypes(), function (value, key) {
                return {
                    'value': key,
                    'type': value
                };
            });
        },

        /**
         * Get list of available month values
         * @returns {Object}
         */
        getCcMonthsValues: function () {
            return _.map(this.getCcMonths(), function (value, key) {
                return {
                    'value': key,
                    'month': value
                };
            });
        },

        getEnvironment: function () {
            return window.checkoutConfig.payment.datacap_gateway.environment;
        },

        /**
         * Get list of months
         * @returns {Object}
         */
        getCcMonths: function () {
            return window.checkoutConfig.payment.datacap_gateway.months[0];
        },

        /**
         * Check if current payment has verification
         * @returns {Boolean}
         */
        hasVerification: function () {
            return true;
        },

        /**
         * Get list of available year values
         * @returns {Object}
         */
        getCcYearsValues: function () {
            return _.map(this.getCcYears(), function (value, key) {
                return {
                    'value': key,
                    'year': value
                };
            });
        },

        /**
         * Get list of years
         * @returns {Object}
         */
        getCcYears: function () {
            return window.checkoutConfig.payment.datacap_gateway.years[0];
        },

        /**
         * Get image url for CVV
         * @returns {String}
         */
        getCvvImageUrl: function () {
            return window.checkoutConfig.payment.datacap_gateway.cvvImageUrl;
        },

        /**
         * Get image for CVV
         * @returns {String}
         */
        getCvvImageHtml: function () {
            return '<img src="' + this.getCvvImageUrl() +
                '" alt="' + $t('Card Verification Number Visual Reference') +
                '" title="' + $t('Card Verification Number Visual Reference') +
                '" />';
        },

        placeOrderEvent: function (data, event) {
            this.dataFunction = data;
            this.dataEvent = event;
            if (this.validate()) {
                this.getCardData();
            }else{
                return false;
            }
        },

        /**
         * Place order.
         */
        placeOrder: function (data, event) {
            var self = this;
            if (event) {
                event.preventDefault();
            }

            if (this.validate() &&
                additionalValidators.validate() &&
                this.isPlaceOrderActionAllowed() === true
            ) {

                this.isPlaceOrderActionAllowed(false);

                this.getPlaceOrderDeferredObject()
                    .done(
                        function () {
                            self.afterPlaceOrder();
                            if (self.redirectAfterPlaceOrder) {
                                redirectOnSuccessAction.execute();
                            }
                        }
                    ).always(
                        function () {
                            self.isPlaceOrderActionAllowed(true);
                        }
                    );

                return true;
            }

            return false;
        },
        getCardData: function () {

            var cardNumber = $("input[name='payment[cc_number]']").val();
            var exYear = $("#datacap_gateway_expiration_yr").val();
            var exMonth = $("#datacap_gateway_expiration").val();
            var cvv = $("#datacap_gateway_cc_cid").val();

            $("input[data-token='card_number']").val(cardNumber);
            $("input[data-token='exp_month']").val(exMonth);
            $("input[data-token='exp_year']").val(exYear);
            $("input[data-token='cvv']").val(cvv);
            if (cardNumber && exMonth && exYear && cvv) {
                var datacapToken = this.getDatacapToken();
                $('body').trigger('processStart');
                DatacapWebToken.requestToken(datacapToken, "datacap_gateway-form", this.readResponse.bind(this));
            }

        },

        readResponse: function (response) {
           var self = this;
            if (response.Error) {
                var error = '<div class="mage-error" generated="true">Failed to create token </div>';
                $("#datacap_gateway_cc_number").after(error);
                $('body').trigger('processStop');
                throw "Failed to create token";
            } else {
                var token = response.Token;
                $("#datacap_payment_token").val(token);
                $('body').trigger('processStop');
                self.placeOrder(self.dataFunction,self.dataEvent);
               
            }
            
        },

        getDatacapToken: function () {
            return window.checkoutConfig.payment.datacap_gateway.datacapToken;
        }


    });
});
