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
        const res = await baseHttp.get('pages/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);

        return res.data;
    },

    async recover({}, params) {
        return await baseHttp.patch(`pages/recover`, params);
    },

    async fetchByField({commit}, params) {
        const res = await baseHttp.get('pages/fetch-by-field', {
            params
        });
        commit('SET_EDIT_DATA', res.data.data);
        return res.data.data;
    },

    async fetchParams({commit}, params) {
        const res = await baseHttp.get('pages/fetch-params', {params});
        commit('SET_PARAMS', res.data);

        return res.data;
    },

    async generatePath({commit}, params) {
        const res = await baseHttp.get('pages/generate-path', {params});

        return res.data;
    },

    async delete({state}) {
        return await baseHttp.delete(`pages/${state.deletingActionApi}/${state.deletingItemId}`);
    },

    async create({}, params) {
        return await baseHttp.post(`pages/insert`, params);
    },

    async update({}, params) {
        return await baseHttp.put(`pages/update`, params);
    },

    async clone({}, params) {
        return await baseHttp.post(`pages/clone`, params);
    },

    async upload({}, params) {
        return await baseHttp.post(`pages/upload`, params);
    },

    async exportFile({}, params) {
        return await baseHttp.get(`pages/export`, {
            params: {...{...params, pageType: 0}},
            responseType: "blob",
        });
    },

    async translateAI({}, params) {
        return await baseHttp.post(`pages/translate-ai`, params);
    },

    async approveTranslation({}, params) {
        return await baseHttp.patch(`pages/approve-translation`, params);
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

const page = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default page;

