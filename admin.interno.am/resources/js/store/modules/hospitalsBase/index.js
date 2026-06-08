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
        const res = await baseHttp.get('hospitals-bases/fetch', {params});
        commit('SET_PAGE_DATA', res.data);
    },

    async fetchByField({commit}, params) {
        const res = await baseHttp.get('hospitals-bases/fetch-by-field', {params});
        commit('SET_EDIT_DATA', res.data.data);
        return res.data.data;
    },

    async delete({state}) {
        return await baseHttp.delete(`hospitals-bases/delete/${state.deletingItemId}`);
    },

    async create({}, params) {
        return await baseHttp.post(`hospitals-bases/insert`, params);
    },

    async update({}, params) {
        return await baseHttp.put(`hospitals-bases/update`, params);
    },

    async fetchIndexParams({commit}, params) {
        const res = await baseHttp.get('hospitals-bases/fetch-index-params', {
            params: {...{dontNeedLoading: true}, ...params},
        });
        return res.data;
    },

    async fetchParams({commit}, params) {
        const res = await baseHttp.get('hospitals-bases/fetch-params', {
            params: {...{dontNeedLoading: true}, ...params},
        });
        return res.data;
    },
};

const mutations = {
    SET_DELETE_MODAL_VALUE(state, data) {
        if (data.value !== undefined) state.deleteModalValue = data.value;
        if (data.id !== undefined) state.deletingItemId = data.id;
    },
    SET_PAGE_DATA(state, data) {
        state.pageData = data;
    },
    SET_EDIT_DATA(state, data) {
        state.editData = data;
    },
};

const hospitalsBase = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default hospitalsBase;