import baseHttp from "@store/api.js";

const state = () => ({
    deleteModalValue: null,
    deletingItemId: null,
    deletingActionApi: 'delete',
    deletingText: null,
    pageData: [],
    editData: [],
    params: [],
    requestParams: {},
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
        const res = await baseHttp.get('products/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);

        return res.data;
    },

    async fetchByField({commit}, params) {
        const res = await baseHttp.get('products/fetch-by-field', {
            params
        });
        commit('SET_EDIT_DATA', res.data.data);
        return res.data;
    },

    async autocomplete({commit}, params) {
        const res = await baseHttp.get('products/autocomplete', {params});

        return res.data.data;
    },

    async fetchVariations({commit}, params) {
        const res = await baseHttp.get('products/fetch-variations', {
            params
        });
        return res.data;
    },

    async fetchParams({commit}, params) {
        const res = await baseHttp.get('products/fetch-params', {params});
        commit('SET_PARAMS', res.data);
        return res;
    },

    async fetchVariantTieredPrices({commit}, params) {
        const res = await baseHttp.get('products/fetch-variant-tiered-prices', {params});

        return res;
    },

    async delete({state}) {
        return await baseHttp.delete(`products/${state.deletingActionApi}/${state.deletingItemId}`, {params: state.requestParams});
    },

    async forceDeleteDraft({state}, params) {
        return await baseHttp.delete(`products/delete-draft/${params.drafted_product_id}`);
    },

    async create({}, params) {
        return await baseHttp.post(`products/insert`, params);
    },

    async updateVariantTieredPrices({}, params) {
        return await baseHttp.post(`products/update-variant-tiered-prices`, params);
    },

    async clone({}, params) {
        return await baseHttp.post(`products/clone`, params);
    },

    async publishDraftAsActual({}, params) {
        return await baseHttp.post('products/publish-draft-as-actual', params);
    },

    async update({}, params) {
        return await baseHttp.put(`products/update`, params);
    },

    async updateVariant({}, params) {
        return await baseHttp.put(`products/update-variant`, params);
    },

    async updatePriority({}, params) {
        return await baseHttp.patch(`products/update-priority`, params);
    },

    async recover({}, params) {
        return await baseHttp.patch(`products/recover`, params);
    },

    async generateVariations({}, params) {
        return await baseHttp.post(`products/generate-variations`, params);
    },

    async translateAI({}, params) {
        return await baseHttp.post(`products/translate-ai`, params);
    },

    async approveTranslation({}, params) {
        return await baseHttp.patch(`products/approve-translation`, params);
    },

    async storeVariant({}, params) {
        return await baseHttp.post(`products/store-variant`, params);
    },

    async bulkDelete({}, params) {
        return await baseHttp.post('products/bulk-delete', params);
    },

    async upload({}, params) {
        return await baseHttp.post(`products/upload`, params);
    },

    async exportFile({}, params) {
        return await baseHttp.get(`products/export`, {
            params,
            responseType: "blob",
        });
    },

    async uploadVariations({}, params) {
        return await baseHttp.post(`products/upload-variations`, params);
    },

    async exportFileVariations({}, params) {
        return await baseHttp.get(`products/export-variations`, {
            params,
            responseType: "blob",
        });
    },

    async deleteVariations({state}, params) {
        return await baseHttp.delete(`products/delete-variants/${params.product_id}/${params.delete_invalids}`, {params: state.requestParams});
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
        if (data.requestParams !== undefined) {
            state.requestParams = data.requestParams;
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

const product = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default product;

