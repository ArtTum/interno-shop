import baseHttp from "@store/api.js";

const state = () => ({
    pageData: [],
    params: [],
});

const getters = {
    getPageData: (state) => state.pageData,
    getParams: (state) => state.params,
};

const actions = {
    async fetchPageData({commit}, params) {
        const res = await baseHttp.get('reports/customers/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);

        return res.data;
    },
    async exportFullCustomersList({}, params) {
        return await baseHttp.get(`reports/customers/export-customers-full-list`, {
            params,
            responseType: "blob",
        });
    },
};

const mutations = {
    SET_PAGE_DATA(state, data) {
        state.pageData = data;
    },
    SET_PARAMS(state, params) {
        state.params = params;
    },
};

const customerReports = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default customerReports;

