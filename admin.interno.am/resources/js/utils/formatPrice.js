export function formatPrice(value, currency = 'EUR', isPrice = false) {
    if (isNaN(value)) return '';

    if (isPrice) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(value);
    } else {
        return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 1,
            maximumFractionDigits: 1
        }).format(value);
    }
}
