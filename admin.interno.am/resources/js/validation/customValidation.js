import validationRules from '@validation/customValidationRules.js';
import {isReactive} from 'vue';

export function validate(form) {
    const newErrors = {};
    Object.keys(form).forEach(key => {
        const param = form[key];
        const rulesParam = form[`${key}Rules`];

        if (key.endsWith("Rules") || !rulesParam || typeof param === 'boolean') return;

        for (let i = 0; i < rulesParam.length; i++) {
            let ruleString = rulesParam[i];
            const [rule, ruleParam] = ruleString.split(':');

            if (rule === 'required' && (param == null || param === '')) {
                newErrors[key] = 'This field is required';
                break;
            } else if (rule && validationRules[rule]) {
                const {validate, message} = ruleParam !== undefined
                    ? validationRules[rule](ruleParam)
                    : validationRules[rule];

                let value = '';
                if (typeof param === 'number') {
                    value = param.toString();
                } else if (typeof param === 'string') {
                    value = param;
                } else if (param == null || param === '') {
                    value = param;
                } else if (isReactive(param)) {
                    value = param;
                } else if (typeof param === 'object') {
                    value = param.name;
                }

                if (!validate(value)) {
                    newErrors[key] = message;
                    break;
                }
            }
        }
    });

    return newErrors;
}
