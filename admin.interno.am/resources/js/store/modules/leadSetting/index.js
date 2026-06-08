import baseHttp from "@store/api.js";

const state = () => ({
    pageData: [],
    params: [],
});

const getters = {
    getPageData: (state) => state.pageData,
    getParams: (state) => state.params,
};

const actions = {
    async fetchPageData({commit}, params) {
        const res = await baseHttp.get('lead-settings/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);

        return res.data;
    },

    async fetchParams({commit}, params) {
        const res = await baseHttp.get('lead-settings/fetch-params', {params});
        commit('SET_PARAMS', res.data);
    },

    async update({}, params) {
        return await baseHttp.put(`lead-settings/update`, params);
    },
};

const mutations = {
    SET_PAGE_DATA(state, data) {
        state.pageData = data;
    },
    SET_PARAMS(state, params) {
        state.params = params;
    },
};

const leadSettings = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default leadSettings;
