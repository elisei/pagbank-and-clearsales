/**
 * O2TI PagBank Source Inventory Auth.
 *
 * Copyright © 2023 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

define([
    'Magento_Ui/js/form/components/button',
    'underscore'
], function (Button, _) {
    'use strict';

    return Button.extend({
        defaults: {
            urlAuth: null
        },

        /**
         * Apply action
         *
         * @param {Object} action - action configuration
         */
        applyAction: function (action) {

            if (action.params && action.params[0] && action.params[0].url_auth) {
                action.params[0]['url_auth'] = this.urlAuth;
            } else {
                action.params = [{
                    'url_auth': this.urlAuth
                }];
            }

            window.location.href = this.urlAuth;

            this._super();
        }
    });
});
