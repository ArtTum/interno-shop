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
        const res = await baseHttp.get('posts/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);

        return res.data;
    },

    async recover({}, params) {
        return await baseHttp.patch(`posts/recover`, params);
    },

    async fetchByField({commit}, params) {
        const res = await baseHttp.get('posts/fetch-by-field', {
            params
        });
        commit('SET_EDIT_DATA', res.data.data);
        return res.data.data;
    },

    async fetchParams({commit}, params) {
        const res = await baseHttp.get('posts/fetch-params', {params});
        commit('SET_PARAMS', res.data);

        return res.data;
    },

    async generatePath({commit}, params) {
        const res = await baseHttp.get('posts/generate-path', {params});

        return res.data;
    },

    async delete({state}) {
        return await baseHttp.delete(`posts/${state.deletingActionApi}/${state.deletingItemId}`);
    },

    async create({}, params) {
        return await baseHttp.post(`posts/insert`, params);
    },

    async update({}, params) {
        return await baseHttp.put(`posts/update`, params);
    },

    async clone({}, params) {
        return await baseHttp.post(`posts/clone`, params);
    },

    async upload({}, params) {
        return await baseHttp.post(`posts/upload`, params);
    },

    async exportFile({}, params) {
        return await baseHttp.get(`posts/export`, {
            params: {...{...params, pageType: 1}},
            responseType: "blob",
        });
    },

    async translateAI({}, params) {
        return await baseHttp.post(`posts/translate-ai`, params);
    },

    async approveTranslation({}, params) {
        return await baseHttp.patch(`posts/approve-translation`, params);
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

const post = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default post;

