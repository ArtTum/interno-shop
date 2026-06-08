import baseHttp from "@store/api.js";

const state = () => ({
    deleteModalValue: null,
    deletingItemId: null,
    pageData: [],
    editData: [],
    params: []
});

const getters = {
    getDeleteModelValue: (state) => state.deleteModalValue,
    getPageData: (state) => state.pageData,
    getEditData: (state) => state.editData,
    getParams: (state) => state.params
};

const actions = {
    async fetchPageData({commit}, params) {
        const res = await baseHttp.get('feeds/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);
    },

    async fetchByField({commit}, params) {
        const res = await baseHttp.get('feeds/fetch-by-field', {
            params
        });
        commit('SET_EDIT_DATA', res.data.data);
        return res.data.data;
    },

    async delete({state}) {
        return await baseHttp.delete(`feeds/delete/${state.deletingItemId}`);
    },

    async create({}, params) {
        return await baseHttp.post(`feeds/insert`, params);
    },

    async update({}, params) {
        return await baseHttp.put(`feeds/update`, params);
    },
    async fetchParams({commit}) {
        const res = await baseHttp.get('feeds/fetch-params');
        commit('SET_PARAMS', res.data);
    },
};

const mutations = {
    SET_DELETE_MODAL_VALUE(state, data) {
        if (data.value !== undefined) {
            state.deleteModalValue = data.value;
        }
        if (data.id !== undefined) {
            state.deletingItemId = data.id;
        }
    },
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

const userGroup = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default userGroup;
