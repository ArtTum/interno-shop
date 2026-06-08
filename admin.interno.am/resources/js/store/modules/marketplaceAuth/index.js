import baseHttp from "@store/api.js";

const state = () => ({
    pageData: [],
    editData: [],
    params: [],
});

const getters = {
    getPageData: (state) => state.pageData,
    getEditData: (state) => state.editData,
    getParams: (state) => state.params,
};

const actions = {
    async generateToken ({}, params) {
        const res =  await baseHttp.post(`marketplaces-auth/generate-token`, params);
        return res.data;
    },
};

const mutations = {};

const marketplaceAuth = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default marketplaceAuth;
