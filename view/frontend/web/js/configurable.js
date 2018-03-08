define([
    'jquery',
    'jquery/ui',
    'mage/template',
    'Magento_ConfigurableProduct/js/configurable'
], function($, $jui, mageTemplate){

    $.widget('powerbody_bridge.configurable', $.mage.configurable, {
        /**
         * Initialize tax configuration, initial settings, and options values.
         * @private
         */
        _initializeOptions: function () {
            var options = this.options,
                gallery = $(options.mediaGallerySelector),
                // added priceBox() intitialization before priceBox('option')...
                priceBoxOptions = $(this.options.priceHolderSelector).priceBox().priceBox('option').priceConfig || null;

            if (priceBoxOptions && priceBoxOptions.optionTemplate) {
                options.optionTemplate = priceBoxOptions.optionTemplate;
            }

            if (priceBoxOptions && priceBoxOptions.priceFormat) {
                options.priceFormat = priceBoxOptions.priceFormat;
            }
            options.optionTemplate = mageTemplate(options.optionTemplate);

            options.settings = options.spConfig.containerId ?
                $(options.spConfig.containerId).find(options.superSelector) :
                $(options.superSelector);

            options.values = options.spConfig.defaultValues || {};
            options.parentImage = $('[data-role=base-image-container] img').attr('src');

            this.inputSimpleProduct = this.element.find(options.selectSimpleProduct);

            gallery.data('gallery') ?
                this._onGalleryLoaded(gallery) :
                gallery.on('gallery:loaded', this._onGalleryLoaded.bind(this, gallery));

        },
        _calculatePrice: function (config) {
            return this.options.spConfig.optionPrices[_.first(config.allowedProducts)];
        }
    });

    return $.powerbody_bridge.configurable;
});
