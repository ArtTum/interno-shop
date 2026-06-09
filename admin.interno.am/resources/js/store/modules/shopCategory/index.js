import baseHttp from "@store/api.js";

const state = () => ({
    deleteModalValue: null,
    deletingItemId: null,
    pageData: {data: [], pagination: {}, languages: [], parents: []},
    params: {languages: [], parents: []},
    editData: null,
});

const getters = {
    getDeleteModelValue: (state) => state.deleteModalValue,
    getPageData: (state) => state.pageData,
    getParams: (state) => state.params,
    getEditData: (state) => state.editData,
};

const actions = {
    async fetchPageData({commit}, params) {
        const res = await baseHttp.get('shop-categories/fetch', {params});
        commit('SET_PAGE_DATA', res.data);
        return res.data;
    },

    async fetchParams({commit}, params = {}) {
        const res = await baseHttp.get('shop-categories/fetch-params', {params});
        commit('SET_PARAMS', res.data);
        return res.data;
    },

    async fetchByField({commit}, params) {
        const res = await baseHttp.get('shop-categories/fetch-by-field', {params});
        commit('SET_EDIT_DATA', res.data.data);
        commit('SET_PARAMS', res.data);
        return res.data;
    },

    async create({}, params) {
        return await baseHttp.post('shop-categories/insert', params);
    },

    async update({}, params) {
        return await baseHttp.put('shop-categories/update', params);
    },

    async delete({state}) {
        return await baseHttp.delete(`shop-categories/delete/${state.deletingItemId}`);
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
    SET_PARAMS(state, data) {
        state.params = data;
    },
    SET_EDIT_DATA(state, data) {
        state.editData = data;
    },
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};
