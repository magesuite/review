define([
    'jquery',
    'underscore',
], function ($, _) {
    'use strict';

    return function (swatchRenderer) {
        $.widget('mage.SwatchRenderer', swatchRenderer, {
            /**
             * Prevent re-render product gallery on review form swatch click
             *
             * @returns {boolean}
             * @private
             */
            _loadMedia: function () {
                if (!!this.element.parents('.cs-reviews__form').length) {
                    return false;
                }

                this._super();
            }
        });

        return $.mage.SwatchRenderer;
    };
});
