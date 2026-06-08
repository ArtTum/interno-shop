import baseHttp from "@store/api.js";

const state = () => ({
    pageData: [],
});

const getters = {
    getPageData: (state) => state.pageData,
};

const actions = {
    async fetchPageData({commit}, params) {
        const res = await baseHttp.get('permissions/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);

        return res;
    },

    async update({}, params) {
        return await baseHttp.put(`permissions/update`, params);
    },
};

const mutations = {
    SET_PAGE_DATA(state, data) {
        state.pageData = data;
    }
};

const permission = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default permission;

