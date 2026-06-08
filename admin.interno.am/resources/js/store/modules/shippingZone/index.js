import baseHttp from "@store/api.js";

const state = () => ({
    deleteModalValue: null,
    deletingItemId: null,
    pageData: [],
    editData: [],
    params: [],
});

const getters = {
    getDeleteModelValue: (state) => state.deleteModalValue,
    getPageData: (state) => state.pageData,
    getEditData: (state) => state.editData,
    getParams: (state) => state.params,
};

const actions = {
    async fetchPageData({commit}, params) {
        const res = await baseHttp.get('shipping-zones/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);

        return res.data;
    },

    async fetchParams({commit}, params) {
        const res = await baseHttp.get('shipping-zones/fetch-params', {params});
        commit('SET_PARAMS', res.data);
    },

    async fetchByField({commit}, params) {
        const res = await baseHttp.get('shipping-zones/fetch-by-field', {
            params
        });
        commit('SET_EDIT_DATA', res.data);
        return res.data;
    },

    async delete({state}) {
        return await baseHttp.delete(`shipping-zones/delete/${state.deletingItemId}`);
    },

    async create({}, params) {
        return await baseHttp.post(`shipping-zones/create`, params);
    },

    async update({}, params) {
        return await baseHttp.put(`shipping-zones/update`, params);
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

const shippingZone = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default shippingZone;

