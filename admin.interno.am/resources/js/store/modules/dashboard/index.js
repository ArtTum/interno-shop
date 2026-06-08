import baseHttp from "@store/api.js";

const state = () => ({});

const getters = {};

const actions = {
    async fetchBoxes({commit}, params) {
        const res = await baseHttp.get('dashboard/fetch-boxes', {
            params: {
                ...{dontNeedLoading: true},
                ...params
            },
        });

        return res.data;
    },

    async fetchOrdersChart({commit}, params) {
        const res = await baseHttp.get('dashboard/fetch-orders-chart', {
            params: {
                ...{dontNeedLoading: true},
                ...params
            },
        });

        return res.data;
    },

    async fetchCustomersChart({commit}, params) {
        const res = await baseHttp.get('dashboard/fetch-customers-chart', {
            params: {
                ...{dontNeedLoading: true},
                ...params
            },
        });

        return res.data;
    },

    async fetchBillingCountriesChart({commit}, params) {
        const res = await baseHttp.get('dashboard/fetch-billing-countries-chart', {
            params: {
                ...{dontNeedLoading: true},
                ...params
            },
        });

        return res.data;
    },

    async fetchShippingCountriesChart({commit}, params) {
        const res = await baseHttp.get('dashboard/fetch-shipping-countries-chart', {
            params: {
                ...{dontNeedLoading: true},
                ...params
            },
        });

        return res.data;
    },

    async fetchLanguagesChart({commit}, params) {
        const res = await baseHttp.get('dashboard/fetch-languages-chart', {
            params: {
                ...{dontNeedLoading: true},
                ...params
            },
        });

        return res.data;
    },

    async fetchTopProducts({commit}, params) {
        const res = await baseHttp.get('dashboard/fetch-top-products', {
            params: {
                ...{dontNeedLoading: true},
                ...params
            },
        });

        return res.data;
    },

    async fetchTopItems({commit}, params) {
        const res = await baseHttp.get('dashboard/fetch-top-items', {
            params: {
                ...{dontNeedLoading: true},
                ...params
            },
        });

        return res.data;
    },

    async fetchPaymentMethodsChart({commit}, params) {
        const res = await baseHttp.get('dashboard/fetch-payment-methods-chart', {
            params: {
                ...{dontNeedLoading: true},
                ...params
            },
        });

        return res.data;
    },

    async fetchTopPaymentMethods({commit}, params) {
        const res = await baseHttp.get('dashboard/fetch-top-payment-methods', {
            params: {
                ...{dontNeedLoading: true},
                ...params
            },
        });

        return res.data;
    },
};

const mutations = {};

const dashboard = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default dashboard;

