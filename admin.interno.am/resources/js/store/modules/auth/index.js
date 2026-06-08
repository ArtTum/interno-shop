import baseHttp from "@store/api.js";

const state = () => ({
    user: null,
    forbidden: false,
    authenticated: !!localStorage.getItem('epodexAuthToken')
});

const getters = {
    getUser: (state) => state.user,
    isAuthenticated: (state) => state.authenticated,
    getForbidden: (state) => state.forbidden,
};

const actions = {
    async login({commit}, credentials) {
        const res = await baseHttp.post('login', credentials);
        commit('SET_USER_INFO', res.data.user);
        commit('SET_AUTHENTICATED_STATUS', true);
        await localStorage.setItem('epodexAuthToken', res.data.token);
        await localStorage.setItem('vendor_key', res.data.vendor_key);
    },

    async fetchUser({commit}) {
        const response = await baseHttp.get('auth/fetch', {params: {dontNeedLoading: true}});
        commit('SET_USER_INFO', response.data.user);
    },

    async logout({commit}) {
        await baseHttp.post('auth/logout');
        commit('CLEAR_USER');
        commit('SET_AUTHENTICATED_STATUS', false);
        localStorage.removeItem('epodexAuthToken');
        localStorage.removeItem('vendor_key');
    },

    async switchUser({commit}, params) {
        const res = await baseHttp.post('auth/switch', params);
        commit('SET_USER_INFO', res.data.user);
        await localStorage.setItem('epodexAuthToken', res.data.token);
        await localStorage.setItem('vendor_key', res.data.vendor_key);
    },
};

const mutations = {
    SET_USER_INFO(state, user) {
        state.user = user;
    },
    SET_AUTHENTICATED_STATUS(state, status) {
        state.authenticated = status;
    },
    SET_FORBIDDEN_STATUS(state, status) {
        state.forbidden = status;
    },
    CLEAR_USER(state) {
        state.authenticated = false;
        state.user = null;
    },
};

const auth = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default auth;

