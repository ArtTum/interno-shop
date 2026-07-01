import baseHttp from "@store/api.js";

const state = () => ({
    deleteModalValue: null,
    deletingItemId: null,
    pageData: {data: [], pagination: {}, languages: [], categories: [], kinds: [], optionTypes: [], optionColors: [], attributeValues: {}},
    params: {languages: [], categories: [], kinds: [], optionTypes: [], optionColors: [], attributeValues: {}},
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
        const res = await baseHttp.get('shop-products/fetch', {params});
        commit('SET_PAGE_DATA', res.data);
        return res.data;
    },

    async fetchParams({commit}, params = {}) {
        const res = await baseHttp.get('shop-products/fetch-params', {params});
        commit('SET_PARAMS', res.data);
        return res.data;
    },

    async fetchByField({commit}, params) {
        const res = await baseHttp.get('shop-products/fetch-by-field', {params});
        commit('SET_EDIT_DATA', res.data.data);
        commit('SET_PARAMS', res.data);
        return res.data;
    },

    async create({}, params) {
        return await baseHttp.post('shop-products/insert', params);
    },

    async update({}, params) {
        return await baseHttp.put('shop-products/update', params);
    },

    async copy({}, id) {
        return await baseHttp.post(`shop-products/copy/${id}`);
    },

    async reorder({}, params) {
        return await baseHttp.patch(`shop-products/reorder/${params.id}`, {direction: params.direction});
    },

    async delete({state}) {
        return await baseHttp.delete(`shop-products/delete/${state.deletingItemId}`);
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
