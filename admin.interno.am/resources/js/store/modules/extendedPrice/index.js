import baseHttp from "@store/api.js";

const state = () => ({
    deleteModalValue: null,
    deletingItemId: null,
    pageData: [],
    editData: [],
});

const getters = {
    getDeleteModelValue: (state) => state.deleteModalValue,
    getPageData: (state) => state.pageData,
    getEditData: (state) => state.editData,
};

const actions = {
    async fetchPageData({commit}, params) {
        const res = await baseHttp.get('extended-prices/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);
    },

    async fetchByField({commit}, params) {
        const res = await baseHttp.get('extended-prices/fetch-by-field', {
            params
        });
        commit('SET_EDIT_DATA', res.data.data);
        return res.data.data;
    },

    async delete({state}) {
        return await baseHttp.delete(`extended-prices/delete/${state.deletingItemId}`);
    },

    async create({}, params) {
        return await baseHttp.post(`extended-prices/insert`, params);
    },

    async update({}, params) {
        return await baseHttp.put(`extended-prices/update`, params);
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
};

const extendedPrice = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default extendedPrice;
