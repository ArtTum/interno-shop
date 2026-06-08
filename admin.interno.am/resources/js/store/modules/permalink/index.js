 import baseHttp from "@store/api.js";

const state = () => ({
    updateModalValue: null,
    item: null,
    updatingActionApi: 'update',
    updatingText: null,
    pageData: [],
    editData: [],
    params: [],
});

const getters = {
    getUpdateModelValue: (state) => state.updateModalValue,
    getPageData: (state) => state.pageData,
    getEditData: (state) => state.editData,
    getParams: (state) => state.params,
};

const actions = {
    async fetchPageData({commit}, params) {
        const res = await baseHttp.get('permalinks/fetch', {
            params
        });
        commit('SET_PAGE_DATA', res.data);

        return res.data;
    },

    async fetchParams({commit}, params) {
        const res = await baseHttp.get('permalinks/fetch-params', {params});
        commit('SET_PARAMS', res.data);
    },

    async fetchByField({commit}, params) {
        const res = await baseHttp.get('permalinks/fetch-by-field', {
            params
        });
        commit('SET_EDIT_DATA', res.data.data);
        return res.data.data;
    },

    async update({}, params) {
        return await baseHttp.put(`permalinks/update`, params);
    },

    async updatePriority({}, params) {
        return await baseHttp.patch(`permalinks/update-priority`, params);
    },

    async upload({}, params) {
        return await baseHttp.post(`permalinks/upload`, params);
    },
};

const mutations = {
    SET_UPDATE_MODAL_VALUE(state, data) {
        if (data.value !== undefined) {
            state.updateModalValue = data.value;
        }
        if (data.item !== undefined) {
            state.editData = data.item;
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

const permalink = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default permalink;
