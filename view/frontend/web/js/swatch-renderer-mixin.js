define([
    'jquery',
    'underscore',
], function ($, _) {
    'use strict';

    return function (swatchRenderer) {
        $.widget('mage.SwatchRenderer', swatchRenderer, {
            options: {
                isInReviewForm: false
            },

            /**
             * Update isInReview flag on widget init to be ready to use in another methods
             *
             * @private
             */
            _init: function () {
                if (!!this.element.parents('.cs-reviews__form').length) {
                    this.options.isInReviewForm = true;
                }

                this._super();
            },

            /**
             * Prevent default swatches actions (except self and input update) if method triggered by swatches in review-form
             *
             * @param $this
             * @param $widget
             * @private
             */
            _OnClick: function ($this, $widget) {
                var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                    $input = $parent.find('.' + $widget.options.classes.attributeInput),
                    $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                    $wrapper = $this.parents('.' + $widget.options.classes.attributeOptionsWrapper),
                    attributeId = $parent.data('attribute-id');

                if ($widget.inProductList) {
                    $input = $widget.productForm.find(
                        '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                    );
                }

                if ($this.hasClass('disabled')) {
                    return;
                }

                if ($this.hasClass('selected')) {
                    $parent.removeAttr('data-option-selected').find('.selected').removeClass('selected');
                    $input.val('');
                    $label.text('');
                    $this.attr('aria-checked', false);
                } else {
                    $parent.attr('data-option-selected', $this.data('option-id')).find('.selected').removeClass('selected');
                    $label.text($this.data('option-label'));
                    $input.val($this.data('option-id'));
                    $input.attr('data-attr-name', this._getAttributeCodeById(attributeId));
                    $this.addClass('selected');
                    $widget._toggleCheckedAttributes($this, $wrapper);
                }


                if (this.options.isInReviewForm) {
                    return; // End swatches action for ones in review-form
                } else {
                    var additionalData = this.options.jsonSwatchConfig[attributeId]['additional_data'],
                        checkAdditionalData = (additionalData !== undefined) ? JSON.parse(additionalData) : '' ,
                        $priceBox = $widget.element.parents($widget.options.selectorProduct)
                            .find(this.options.selectorProductPrice);


                    $widget._Rebuild();

                    if ($priceBox.is(':data(mage-priceBox)')) {
                        $widget._UpdatePrice();
                    }

                    $(document).trigger('updateMsrpPriceBlock',
                        [
                            this._getSelectedOptionPriceIndex(),
                            $widget.options.jsonConfig.optionPrices,
                            $priceBox
                        ]);

                    if (parseInt(checkAdditionalData['update_product_preview_image'], 10) === 1) {
                        $widget._loadMedia();
                    }

                    $input.trigger('change');
                }
            },

            /**
             * Prevent default swatches actions (except self and input update) if method triggered by swatches in review-form
             *
             * @param $this
             * @param $widget
             * @private
             */
            _OnChange: function ($this, $widget) {
                var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                    attributeId = $parent.data('attribute-id'),
                    $input = $parent.find('.' + $widget.options.classes.attributeInput);

                if ($widget.productForm.length > 0) {
                    $input = $widget.productForm.find(
                        '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                    );
                }

                if ($this.val() > 0) {
                    $parent.attr('data-option-selected', $this.val());
                    $input.val($this.val());
                } else {
                    $parent.removeAttr('data-option-selected');
                    $input.val('');
                }

                if (this.options.isInReviewForm) {
                    return; // End swatches action for ones in review-form
                } else {
                    $widget._Rebuild();
                    $widget._UpdatePrice();
                    $widget._loadMedia();
                    $input.trigger('change');
                }
            },
        });

        return $.mage.SwatchRenderer;
    };
});
