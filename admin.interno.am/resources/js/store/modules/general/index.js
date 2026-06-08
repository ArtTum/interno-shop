import baseHttp from "@store/api.js";

const state = () => ({
    params: [],
    vendorsForSwitch: null,
    adminBaseUrl: null,
});

const getters = {
    getParams: (state) => state.params,
    getAdminBaseUrl: (state) => state.adminBaseUrl,
    getVendorsForSwitch: (state) => state.vendorsForSwitch,
};

const actions = {
    async fetchParams({commit}, params) {
        // const res = await baseHttp.get('general-info/fetch', {params});
        // commit('SET_PARAMS', res.data);
        // commit('SET_ADMIN_BASE_URL', res.data.base_url);
    },

    async fetchVendorsForSwitch({commit}, params) {
        // const res = await baseHttp.get('general-info/fetch-vendors-for-switch', {params});
        // commit('SET_VENDORS_FOR_SWITCH', res.data.vendors);
    },
};

const mutations = {
    SET_PARAMS(state, params) {
        state.params = params;
    },
    SET_ADMIN_BASE_URL(state, params) {
        state.adminBaseUrl = params;
    },
    SET_VENDORS_FOR_SWITCH(state, data) {
        state.vendorsForSwitch = data;
    },
};

const general = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default general;

