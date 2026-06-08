import baseHttp from "@store/api.js";

const state = () => ({
    deleteModalValue: null,
    deletingItemId: null,
    pageSmsHistoryData: [],
});

const getters = {
    getPageSmsHistoryData: (state) => state.pageSmsHistoryData,
};

const actions = {
    async fetchPageData({commit}, params) {
        const res = await baseHttp.get('sms-histories/fetch', {
            params
        });
        commit('SET_SNS_HISTORY_DATA', res.data);
    },

    async fetchIndexParams({commit}, params) {
        const res = await baseHttp.get('sms-histories/fetch-index-params', {
            params: {
                ...{dontNeedLoading: true},
                ...params
            },
        });
        return res.data;
    },

    async sendSms({}, params) {
        return await baseHttp.post('sms-histories/send', params);
    },
};

const mutations = {
    SET_SNS_HISTORY_DATA(state, data) {
        state.pageSmsHistoryData = data;
    },
};

const smsHistory = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default smsHistory;
