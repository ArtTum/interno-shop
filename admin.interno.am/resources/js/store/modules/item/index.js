import baseHttp from "@store/api.js";

const state = () => ({
    deleteModalValue: null,
    deletingItemId: null,
    deletingActionApi: 'delete',
    deletingText: null,
    pageData: [],
});

const getters = {
    getDeleteModelValue: (state) => state.deleteModalValue,
    getDeletingText: (state) => state.deletingText,
    getPageData: (state) => state.pageData,
};

const actions = {
    async fetchPageData({commit}, params) {
        const res = await baseHttp.get('items/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);

        return res.data;
    },

    async findForReshipment({commit}, params) {
        const res = await baseHttp.get('items/find-for-reshipment', {
            params
        });

        return res.data;
    },

    async delete({state}) {
        return await baseHttp.delete(`items/${state.deletingActionApi}/${state.deletingItemId}`);
    },

    async upload({}, params) {
        return await baseHttp.post(`items/upload`, params);
    },

    async exportFile({}, params) {
        return await baseHttp.get(`items/export`, {
            params,
            responseType: "blob",
        });
    },

    async autocomplete({commit}, params) {
        const res = await baseHttp.get('items/autocomplete', {params});

        return res.data.data;
    },

    async updateStatus({}, params) {
        return await baseHttp.patch(`items/update-status`, params);
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
    }
};

const item = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default item;

