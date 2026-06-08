import baseHttp from "@store/api.js";

const state = () => ({
    mode: null,
    deleteModalValue: null,
    deletingItemId: null,
    pageData: [],
});

const getters = {
    getDeleteModelValue: (state) => state.deleteModalValue,
    getPageData: (state) => state.pageData,
};

const actions = {
    async fetchPageData({commit}, params) {
        const res = await baseHttp.get('file-imports/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);
    },

    async fetchLogs({commit}, params) {
        const res = await baseHttp.get('file-imports/fetch-logs', {
            params
        });
        return res.data;
    },

    async delete({state}) {
        return await baseHttp.delete(`file-imports/delete/${state.deletingItemId}`);
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
    }
};

const upload = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default upload;

