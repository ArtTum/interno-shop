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
        const res = await baseHttp.get('reports/analytics/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);

        return res.data;
    },

    async regionsByCountry({commit}, params) {
        const res = await baseHttp.get('reports/analytics/regions-by-country', {
            params
        });

        return res.data;
    },

    async fetchParams({commit}, params) {
        const res = await baseHttp.get('reports/analytics/fetch-params', {params});
        commit('SET_PARAMS', res.data);
    },

    async exportFile({}, params) {
        return await baseHttp.get(`reports/analytics/export`, {
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

const analytic = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default analytic;

