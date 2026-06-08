import baseHttp from "@store/api.js";
const state = () => ({
    userInfo: 88888888
});

const getters = {
    getUserInfo: (state) => state.userInfo
};

const actions = {
    fetchUserInfo({ commit }) {
        // Mock API call
        setTimeout(() => {
            const userInfo = { name: 'John Doe', email: 'john@example.com' };
            commit('SET_USER_INFO', userInfo);
        }, 1000);
    }
};

const mutations = {
    SET_USER_INFO(state, userInfo) {
        state.userInfo = userInfo;
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
