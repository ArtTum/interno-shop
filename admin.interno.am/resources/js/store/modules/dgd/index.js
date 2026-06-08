import baseHttp from "@store/api.js";

const state = () => ({
    pageData: [],
    editData: [],
});

const getters = {
    getPageData: (state) => state.pageData,
    getEditData: (state) => state.editData,
};

const actions = {
    async fetchPageData({commit}, params) {
        const res = await baseHttp.get('dgd-settings/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);
        return res.data.data;
    },

    async fetchByField({commit}, params) {
        const res = await baseHttp.get('dgd-settings/fetch-by-field', {
            params
        });
        commit('SET_EDIT_DATA', res.data.data);
        return res.data.data;
    },

    async update({}, params) {
        return await baseHttp.put(`dgd-settings/update`, params);
    },
};

const mutations = {
    SET_PAGE_DATA(state, data) {
        state.pageData = data;
    },
    SET_EDIT_DATA(state, data) {
        state.editData = data;
    },
};

const social = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default social;
