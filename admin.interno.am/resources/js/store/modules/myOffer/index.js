import baseHttp from "@store/api.js";

const state = () => ({
    pageData: [],
});

const getters = {
    getPageData: (state) => state.pageData,
};

const actions = {
    async fetchPageData({commit}, params) {
        const res = await baseHttp.get('my-offers/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);

        return res.data;
    },
};

const mutations = {
    SET_PAGE_DATA(state, data) {
        state.pageData = data;
    }
};

const myOffer = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default myOffer;

