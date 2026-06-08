import {isReactive} from "vue";

const validationRules = {
    email: {
        validate: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
        message: 'Invalid email address'
    },
    minLength: (min) => ({
        validate: (value) => value.length >= min,
        message: `Must be at least ${min} characters long`
    }),
    maxLength: (max) => ({
        validate: (value) => value.length <= max,
        message: `Must be maximum ${max} characters long`
    }),
    required: {
        validate: (value) => {
            if (Array.isArray(value) || isReactive(value)) return value.length > 0;

            return typeof value === 'string' ? value.trim() !== '' : !!value;
        },
        message: 'This field is required'
    },
    skuFormat: {
        validate: (value) => {
            if (value === null || value.trim() === '') return true;

            const skuRegex = /^\d+x\d+(,\d+x\d+)*$/;
            return skuRegex.test(value);
        },
        message: 'Invalid format. Example formats: 9x4064824550895 or 9x4064824550895,9x4064824550888,17x4064824559478,3x4064824008068,6x4064824007641'
    },
    couponAllowedEmailsFormat: {
        validate: (value) => {
            if (value === null || value.trim() === '') return true;

            const emails = value.split('|');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            return emails.every(email => emailRegex.test(email));
        },
        message: 'Invalid format. Example formats:.com or test@epodex.com|manager@epodex.com|developer@epodex.com'
    },
    validDecimal: {
        validate: (value) => {
            if (value === null || value.trim() === '') return true;

            // Updated regex to allow negative decimal values
            const decimalRegex = /^-?\d+([.]\d+)?$/;
            return decimalRegex.test(value);
        },
        message: 'The field must be a valid decimal number.'
    },

    minValue: (min) => ({
        validate: (value) => {
            if (!value && value !== 0) return true;
            return parseFloat(value) >= parseFloat(min);
        },
        message: `The value must be at least ${min}.`
    }),
    maxValue: (max) => ({
        validate: (value) => {
            if (!value && value !== 0) return true;
            return parseFloat(value) <= parseFloat(max);
        },
        message: `The value must not exceed ${max}.`
    })
};

export default validationRules;
