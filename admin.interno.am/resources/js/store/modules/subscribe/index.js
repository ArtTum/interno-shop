import baseHttp from "@store/api.js";

const state = () => ({
    deleteModalValue: null,
    deletingItemId: null,
    pageData: [],
    editData: [],
    indexParams: {},
    count: 0,
});

const getters = {
    getDeleteModelValue: (state) => state.deleteModalValue,
    getPageData: (state) => state.pageData,
    getEditData: (state) => state.editData,
    getCount: (state) => state.count,
    getIndexParams: (state) => state.indexParams,
};

const actions = {
    async fetchIndexParams({commit}) {
        const res = await baseHttp.get('subscribes/fetch-index-params');
        commit('SET_INDEX_PARAMS', res.data);
    },

    async fetchPageData({commit}, params) {
        const res = await baseHttp.get('subscribes/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);
    },

    async fetchByField({commit}, params) {
        const res = await baseHttp.get('subscribes/fetch-by-field', {
            params
        });
        commit('SET_EDIT_DATA', res.data.data);
        return res.data.data;
    },

    async delete({state}) {
        return await baseHttp.delete(`subscribes/delete/${state.deletingItemId}`);
    },

    async create({}, params) {
        return await baseHttp.post(`subscribes/insert`, params);
    },

    async update({}, params) {
        return await baseHttp.put(`subscribes/update`, params);
    },

    async fetchCount({commit}) {
        const res = await baseHttp.get('subscribes/fetch-count', {
            params: { dontNeedLoading: true }
        });
        commit('SET_COUNT', res.data.count);
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
    SET_COUNT(state, count) {
        state.count = count;
    },
    SET_INDEX_PARAMS(state, data) {
        state.indexParams = data;
    },
};

const subscribe = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default subscribe;
