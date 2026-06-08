import baseHttp from "@store/api.js";

const state = () => ({
    pageData: [],
    modalData: {},
});

const getters = {
    getPageData: (state) => state.pageData,
    getUpdateModalData: (state) => state.modalData,
};

const actions = {
    async fetchPageData({commit}, params) {
        const res = await baseHttp.get('leads/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);

        return res.data;
    },

    async download({}, params) {
        return await baseHttp.post(`leads/download-offer`, params,
            {
                responseType: 'blob' // Blob type required for binary files
            }
        );
    },

    async update({}, params) {
        return await baseHttp.put(`leads/update`, params);
    },

    async regeneratePDF({}, params) {
        return await baseHttp.post(`leads/regenerate-pdf`, params);
    },

    async exportLeads({}, params) {
        return await baseHttp.get(`leads/export`, {
            params,
            responseType: "blob",
        });
    },
};

const mutations = {
    SET_PAGE_DATA(state, data) {
        state.pageData = data;
    },
    SET_UPDATE_MODAL_DATA(state, data) {
        if (data) {
            state.modalData = data;
        }
    },
};

const lead = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default lead;

