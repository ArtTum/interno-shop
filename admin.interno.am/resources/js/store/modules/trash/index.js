import baseHttp from "@store/api.js";

const state = () => ({
    pageData: [],
});

const getters = {
    getPageData: (state) => state.pageData,
};

const actions = {
    async fetchPageData({commit}, params) {
        const res = await baseHttp.get('trash/fetch', {params});
        commit('SET_PAGE_DATA', res.data);
    },

    async restore({}, id) {
        return await baseHttp.post(`trash/restore/${id}`);
    },

    async forceDelete({}, id) {
        return await baseHttp.delete(`trash/force-delete/${id}`);
    },
};

const mutations = {
    SET_PAGE_DATA(state, data) {
        state.pageData = data;
    },
};

const trash = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default trash;