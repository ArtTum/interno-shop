import baseHttp from "@store/api.js";

const state = () => ({
    deleteModalValue: null,
    deletingItemId: null,
    mediaModalValue: null,
    mediaVideoModalValue: null,
    mediaModal: null,
    mode: null,
    index: null,
    media: [],
    pageData: [],
    editData: [],
    params: [],
});

const getters = {
    getMediaModel: (state) => state.mediaModal,
    getMediaMode: (state) => state.mode,
    getMediaIndex: (state) => state.index,
    getDeleteModelValue: (state) => state.deleteModalValue,
    getMediaModelValue: (state) => state.mediaModalValue,
    getVideoMediaModelValue: (state) => state.mediaVideoModalValue,
    getPageData: (state) => state.pageData,
    getEditData: (state) => state.editData,
    getMediaData: (state) => state.media,
    getParams: (state) => state.params,
};

const actions = {
    async fetchPageData({commit}, params) {
        const res = await baseHttp.get('media/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);
        return res.data;
    },
    async fetchParams({commit}, params) {
        const res = await baseHttp.get('media/fetch-params', {params});
        commit('SET_PARAMS', res.data);
    },

    async fetchByField({commit}, params) {
        const res = await baseHttp.get('media/fetch-by-field', {
            params
        });
        // commit('SET_EDIT_DATA', res.data.data);
        return res.data.data;
    },

    async update({}, params) {
        return await baseHttp.put(`media/update`, params);
    },

    async delete({state}) {
        return await baseHttp.delete(`media/delete/${state.deletingItemId}`);
    },

    async upload({}, params) {
        return await baseHttp.post(`media/upload`, params);
    },

    async uploadFile({}, params) {
        return await baseHttp.post(`media/upload-file`, params);
    },

    async exportFile({}, params) {
        return await baseHttp.get(`media/export`, {
            params,
            responseType: "blob",
        });
    },
    async replaceImage({}, params) {
        return await baseHttp.post(`media/replace-image`, params);
    },

    async translateAI({}, params) {
        return await baseHttp.post(`media/translate-ai`, params);
    },
};

const mutations = {
    SET_PAGE_DATA(state, data) {
        state.pageData = data;
    },
    SET_EDIT_DATA(state, data) {
        state.editData = data;
    },
    SET_MEDIA_MODAL_VALUE(state, data) {
        data.media['language_id'] = data.baseLanguageId;
        state.mediaModalValue = data.value;
        state.editData = data.media;
    },
    SET_VIDEO_MODAL_VALUE(state, data) {
        state.mediaVideoModalValue = data.value;
        state.media = data.media;
    },
    SET_DELETE_MODAL_VALUE(state, data) {
        if (data.value !== undefined) {
            state.deleteModalValue = data.value;
        }
        if (data.id !== undefined) {
            state.deletingItemId = data.id;
        }
    },
    SET_MEDIA_MODAL(state, data) {
        if (data.value !== undefined) {
            state.mediaModal = data.value;
        }
        if (data.mode !== undefined) {
            state.mode = data.mode;
        }

        if (data.index !== undefined) {
            state.index = data.index;
        }
    },
    SET_PARAMS(state, params) {
        state.params = params;
    },
};

const media = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default media;
