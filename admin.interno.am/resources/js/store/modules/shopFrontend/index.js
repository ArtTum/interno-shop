import baseHttp from "@store/api.js";

const state = () => ({
    config: null,
    orders: [],
});

const getters = {
    getConfig: (state) => state.config,
    getOrders: (state) => state.orders,
};

const actions = {
    async fetchConfig({commit}, params = {}) {
        const res = await baseHttp.get('shop-frontend/fetch', {params});
        commit('SET_CONFIG', res.data.data);
        return res.data.data;
    },

    async updateConfig({commit}, payload) {
        const res = await baseHttp.put('shop-frontend/update', payload);
        commit('SET_CONFIG', res.data.data);
        return res.data.data;
    },

    async fetchOrders({commit}, params = {}) {
        const res = await baseHttp.get('shop-frontend/orders', {params});
        commit('SET_ORDERS', res.data.data);
        return res.data.data;
    },

    async fetchTranslations(context, params = {}) {
        const res = await baseHttp.get('shop-frontend/translations', {params});
        return res.data.data;
    },

    async updateTranslations(context, payload) {
        const res = await baseHttp.put('shop-frontend/translations', payload);
        return res.data.data;
    },
};

const mutations = {
    SET_CONFIG(state, data) {
        state.config = data;
    },
    SET_ORDERS(state, data) {
        state.orders = data;
    },
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};
