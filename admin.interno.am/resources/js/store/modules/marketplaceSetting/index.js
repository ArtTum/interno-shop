import baseHttp from "@store/api.js";

const state = () => ({
    pageData: [],
    editData: [],
    params: [],
});

const getters = {
    getPageData: (state) => state.pageData,
    getEditData: (state) => state.editData,
    getParams: (state) => state.params,
};

const actions = {
    async fetchPageData({commit}, params) {
        const res = await baseHttp.get('marketplace-settings/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);
    },

    async fetchParams({commit}, params) {
        const res = await baseHttp.get('marketplace-settings/fetch-params', {params});
        commit('SET_PARAMS', res.data);
    },

    async fetchByField({commit}, params) {
        const res = await baseHttp.get('marketplace-settings/fetch-by-field', {
            params
        });
        commit('SET_EDIT_DATA', res.data.data);
        return res.data.data;
    },

    async update({}, params) {
        return await baseHttp.put(`marketplace-settings/update`, params);
    },
};

const mutations = {
    SET_PAGE_DATA(state, data) {
        state.pageData = data;
    },
    SET_EDIT_DATA(state, data) {
        state.editData = data;
    },
    SET_PARAMS(state, params) {
        state.params = params;
    },
};

const marketplaceSettings = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default marketplaceSettings;

