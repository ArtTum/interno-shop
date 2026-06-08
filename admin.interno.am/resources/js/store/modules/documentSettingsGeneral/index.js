import baseHttp from "@store/api.js";

const state = () => ({
    deleteModalValue: null,
    deletingItemId: null,
    deletingActionApi: 'delete',
    deletingText: null,
    pageData: [],
    editData: [],
    params: [],
});

const getters = {
    getDeleteModelValue: (state) => state.deleteModalValue,
    getDeletingText: (state) => state.deletingText,
    getPageData: (state) => state.pageData,
    getEditData: (state) => state.editData,
    getParams: (state) => state.params,
};

const actions = {
    async fetchPageData({commit}, params) {
        const res = await baseHttp.get('document-settings-general/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);
    },

    async fetchParams({commit}, params) {
        const res = await baseHttp.get('document-settings-general/fetch-params', {params});
        commit('SET_PARAMS', res.data);
    },

    async fetchByField({commit}, params) {
        const res = await baseHttp.get('document-settings-general/fetch-by-field', {
            params
        });
        commit('SET_EDIT_DATA', res.data.data);
        return res.data.data;
    },

    async delete({state}) {
        return await baseHttp.delete(`document-settings-general/${state.deletingActionApi}/${state.deletingItemId}`);
    },

    async create({}, params) {
        return await baseHttp.post(`document-settings-general/insert`, params);
    },

    async update({}, params) {
        return await baseHttp.put(`document-settings-general/update`, params);
    },

    async updatePriority({}, params) {
        return await baseHttp.patch(`document-settings-general/update-priority`, params);
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
        if (data.deletingActionApi !== undefined) {
            state.deletingActionApi = data.deletingActionApi;
            state.deletingText = data.deletingText;
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

const documentSettingsGeneral = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default documentSettingsGeneral;

