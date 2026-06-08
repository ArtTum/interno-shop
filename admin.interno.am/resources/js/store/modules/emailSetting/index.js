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
        const res = await baseHttp.get('email-settings/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);

        return res.data;
    },

    async fetchByField({commit}, params) {
        const res = await baseHttp.get('email-settings/fetch-by-field', {
            params
        });
        commit('SET_EDIT_DATA', res.data.data);
        return res.data.data;
    },

    async fetchParams({commit}, params) {
        const res = await baseHttp.get('email-settings/fetch-params', {params});
        commit('SET_PARAMS', res.data);
    },

    async update({}, params) {
        return await baseHttp.put(`email-settings/update`, params);
    },

    async translateAI({}, params) {
        return await baseHttp.post(`email-settings/translate-ai`, params);
    },
    async approveTranslation({}, params) {
        return await baseHttp.patch(`email-settings/approve-translation`, params);
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

const attribute = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default attribute;

