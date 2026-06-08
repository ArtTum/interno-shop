import baseHttp from "@store/api.js";

const state = () => ({
    deleteDocumentModalValue: null,
    deletingDocumentItemId: null,

    deleteShippingLabelModalValue: null,
    deletingShippingLabelItemId: null,
    deleteAdbModalValue: null,
    deletingAdbId: null,
    deleteModalValue: null,
    deletingItemId: null,
    deletingActionApi: 'delete',
    deletingText: null,
    refund_price: 0,
    pageData: [],
    showData: [],
    params: [],
});

const getters = {
    getDeleteDocumentModelValue: (state) => state.deleteDocumentModalValue,
    getDeleteShippingLabelModelValue: (state) => state.deleteShippingLabelModalValue,
    getDeleteModelValue: (state) => state.deleteModalValue,
    getDeletingText: (state) => state.deletingText,
    getPageData: (state) => state.pageData,
    getShowData: (state) => state.showData,
    getParams: (state) => state.params,
    getRefundPrice: (state) => state.refund_price,
    getDeleteAdbModelValue: (state) => state.deleteAdbModalValue,
};

const actions = {
    async fetchPageData({commit}, params) {
        const res = await baseHttp.get('orders/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);

        return res;
    },

    async fetchIndexParams({commit}, params) {
        const res = await baseHttp.get('orders/fetch-index-params', {
            params: {
                ...{dontNeedLoading: true},
                ...params
            },
        });

        return res.data;
    },

    async bulkActions({commit}, params) {
        const res = await baseHttp.get('orders/bulk-actions', {
            params
        });

        return res.data;
    },

    async show({commit}, params) {
        const res = await baseHttp.get('orders/show', {
            params
        });
        commit('SET_EDIT_DATA', res.data.data);
        return res.data.data;
    },

    async fetchParams({commit}, params) {
        const res = await baseHttp.get('orders/fetch-params', {params});
        commit('SET_PARAMS', res.data);
    },

    async getItemPrice({commit}, params) {
        const res = await baseHttp.get('orders/get-item-price', {params});
        return res.data;
    },

    async delete({state}) {
        return await baseHttp.delete(`orders/${state.deletingActionApi}/${state.deletingItemId}`);
    },

    async update({}, params) {
        return await baseHttp.put(`orders/update`, params);
    },

    async updateJustStatus({}, params) {
        return await baseHttp.put(`orders/update-just-status`, params);
    },

    async addItem({}, params) {
        return await baseHttp.post(`orders/add-item`, params);
    },

    async applyCoupon({}, params) {
        return await baseHttp.post(`orders/apply-coupon`, params);
    },

    async resendEmail({}, params) {
        return await baseHttp.post(`orders/resend-email`, params);
    },

    async addNote({}, params) {
        return await baseHttp.post(`orders/add-note`, params);
    },

    async addFeedback({}, params) {
        return await baseHttp.post(`orders/add-feedback`, params);
    },

    async sendCustomerEmail({}, params) {
        return await baseHttp.post(`orders/customer-email`, params);
    },

    async refund({}, params) {
        return await baseHttp.post(`orders/refund`, params);
    },

    async createFullReshipment({}, params) {
        return await baseHttp.post(`orders/reshipment/create-full`, params);
    },

    async createPartialReshipment({}, params) {
        return await baseHttp.post(`orders/reshipment/create-partial`, params);
    },

    async getPartialReshipmentInfo({commit}, params) {
        const res = await baseHttp.get('orders/reshipment/partial-info', {params});

        return res.data;
    },

    async showPdf({commit}, params) {
        const res = await baseHttp.get('orders/show-pdf', {
            params
        });

        return res.data;
    },

    async showImoDocument({commit}, params) {
        const res = await baseHttp.get('orders/show-imo-document', {
            params
        });

        return res.data;
    },

    async createShippingLabel({commit}, params) {
        const res = await baseHttp.post('orders/create-shipping-label', params);

        return res.data;
    },

    async createADBDocument({commit}, params) {
        const res = await baseHttp.post('orders/create-adb-document', params);

        return res.data;
    },

    async sendADBDocument({commit}, params) {
        const res = await baseHttp.post('orders/send-adb-document', params);

        return res.data;
    },

    async approvedCustoms({commit}, params) {
        const res = await baseHttp.post('orders/approved-adb-document', params);

        return res.data;
    },

    async sendInvoicesByEmails({commit}, params) {
        return await baseHttp.post(`orders/send-invoices-by-emails`, params);
    },

    async createAdditionalLabel({commit}, params) {
        const res = await baseHttp.post('orders/create-additional-label', params);

        return res.data;
    },

    async deleteDocument({state}) {
        return await baseHttp.delete(`orders/delete-document/${state.deletingDocumentItemId}`);
    },

    async deleteShippingLabel({state}) {
        return await baseHttp.delete(`orders/delete-shipping-label/${state.deletingShippingLabelItemId}`);
    },

    async generate({}, params) {
        return await baseHttp.post(`orders/generate`, params);
    },

    async createExportAccompanyingDocument({commit}, params) {
        const res = await baseHttp.post('orders/create-ead', params);

        return res.data;
    },

    async exportProducts({commit}, params) {
        return await baseHttp.get('orders/export-products', {
            params,
            responseType: "blob",
        });
    },

    async exportProducts2({commit}, params) {
        return await baseHttp.get('orders/export-products-2', {
            params,
            responseType: "blob",
        });
    },

    async deleteAdb({state}) {
        return await baseHttp.delete(`orders/delete-adb/${state.deletingAdbId}`);
    },
};

const mutations = {
    SET_DELETE_DOCUMENT_MODAL_VALUE(state, data) {
        if (data.value !== undefined) {
            state.deleteDocumentModalValue = data.value;
        }
        if (data.id !== undefined) {
            state.deletingDocumentItemId = data.id;
        }
    },
    SET_DELETE_SHIPPING_LABEL_MODAL_VALUE(state, data) {
        if (data.value !== undefined) {
            state.deleteShippingLabelModalValue = data.value;
        }
        if (data.id !== undefined) {
            state.deletingShippingLabelItemId = data.id;
        }
    },
    SET_DELETE_ADB_MODAL_VALUE(state, data) {
        if (data.value !== undefined) {
            state.deleteAdbModalValue = data.value;
        }
        if (data.id !== undefined) {
            state.deletingAdbId = data.id;
        }
    },
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
        state.showData = data;
    },
    SET_PARAMS(state, params) {
        state.params = params;
    },
    SET_REFUND_PRICE(state, price) {
        state.refund_price = price;
    },
};

const order = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default order;

