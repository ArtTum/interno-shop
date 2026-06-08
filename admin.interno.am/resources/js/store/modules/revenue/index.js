import baseHttp from "@store/api.js";

const state = () => ({});

const getters = {};

const actions = {
    async fetchRevenueChart({commit}, params) {
        const res = await baseHttp.get('revenue/fetch-revenue-chart', {
            params: {
                ...{dontNeedLoading: true},
                ...params
            },
        });

        return res.data;
    },

    async fetchBillingCountriesChart({commit}, params) {
        const res = await baseHttp.get('revenue/fetch-billing-countries-chart', {
            params: {
                ...{dontNeedLoading: true},
                ...params
            },
        });

        return res.data;
    },

    async fetchShippingCountriesChart({commit}, params) {
        const res = await baseHttp.get('revenue/fetch-shipping-countries-chart', {
            params: {
                ...{dontNeedLoading: true},
                ...params
            },
        });

        return res.data;
    },

    async fetchLanguagesChart({commit}, params) {
        const res = await baseHttp.get('revenue/fetch-languages-chart', {
            params: {
                ...{dontNeedLoading: true},
                ...params
            },
        });

        return res.data;
    },

    async fetchTopProducts({commit}, params) {
        const res = await baseHttp.get('revenue/fetch-top-products', {
            params: {
                ...{dontNeedLoading: true},
                ...params
            },
        });

        return res.data;
    },

    async fetchTopItems({commit}, params) {
        const res = await baseHttp.get('revenue/fetch-top-items', {
            params: {
                ...{dontNeedLoading: true},
                ...params
            },
        });

        return res.data;
    },

    async fetchPaymentMethodsChart({commit}, params) {
        const res = await baseHttp.get('revenue/fetch-payment-methods-chart', {
            params: {
                ...{dontNeedLoading: true},
                ...params
            },
        });

        return res.data;
    },

    async fetchTopPaymentMethods({commit}, params) {
        const res = await baseHttp.get('revenue/fetch-top-payment-methods', {
            params: {
                ...{dontNeedLoading: true},
                ...params
            },
        });

        return res.data;
    },
};

const mutations = {};

const revenue = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default revenue;

