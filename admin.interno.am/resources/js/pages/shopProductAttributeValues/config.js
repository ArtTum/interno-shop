export const attributeConfigs = {
    height: {
        title: 'Length',
        createTitle: 'Create Length',
        updateTitle: 'Update Length',
        route: '/shop-product-attribute-values/height',
        valueLabel: 'Value',
    },
    unit: {
        title: 'Measurement unit',
        createTitle: 'Create measurement unit',
        updateTitle: 'Update measurement unit',
        route: '/shop-product-attribute-values/unit',
        valueLabel: 'Value',
    },
    size: {
        title: 'Size',
        createTitle: 'Create size',
        updateTitle: 'Update size',
        route: '/shop-product-attribute-values/size',
        valueLabel: 'Value',
    },
    power: {
        title: 'Power',
        createTitle: 'Create power',
        updateTitle: 'Update power',
        route: '/shop-product-attribute-values/power',
        valueLabel: 'Value',
    },
};

export const attributeConfig = (type) => attributeConfigs[type] || attributeConfigs.height;
