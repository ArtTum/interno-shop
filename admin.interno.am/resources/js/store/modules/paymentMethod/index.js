import baseHttp from "@store/api.js";

const state = () => ({
    pageData: [],
    editData: [],
    params: [],
    deletingItemId: null,
    deletingActionApi: 'delete',
    deletingText: null,
    deleteModalValue: null,
    requestParams: {},
});

const getters = {
    getPageData: (state) => state.pageData,
    getEditData: (state) => state.editData,
    getParams: (state) => state.params,
    getDeleteModelValue: (state) => state.deleteModalValue,
    getDeletingText: (state) => state.deletingText,
};

const actions = {
    async fetchPageData({commit}, params) {
        const res = await baseHttp.get('payment-methods/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);

        return res.data;
    },

    async fetchAccounts({commit}, params) {
        const res = await baseHttp.get('payment-methods/fetch-accounts', {
            params
        });

        return res;
    },

    async fetchParams({commit}, params) {
        const res = await baseHttp.get('payment-methods/fetch-params', {params});
        commit('SET_PARAMS', res.data);
    },

    async fetchByField({commit}, params) {
        const res = await baseHttp.get('payment-methods/fetch-by-field', {
            params
        });
        commit('SET_EDIT_DATA', res.data.data);
        return res.data;
    },

    async update({}, params) {
        return await baseHttp.put(`payment-methods/update`, params);
    },

    async updatePriority({}, params) {
        return await baseHttp.patch(`payment-methods/update-priority`, params);
    },

    async generateAccounts({}, params) {
        return await baseHttp.post(`payment-methods/generate-accounts`, params);
    },

    async deleteAccounts({state}, params) {
        return await baseHttp.delete(`payment-methods/delete-accounts/${params.payment_method_id}/${params.delete_invalids}`, {params: state.requestParams});
    },

    async updateAccount({}, params) {
        return await baseHttp.put(`payment-methods/update-account`, params);
    },

    async delete({state}) {
        return await baseHttp.delete(`payment-methods/${state.deletingActionApi}/${state.deletingItemId}`, {params: state.requestParams});
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

const paymentMethod = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default paymentMethod;

