
const state = () => ({
    loadingStatus: null
});

const getters = {
    getLoadingStatus: (state) => state.loadingStatus,
};

const actions = {

};

const mutations = {
    SET_LOADING_STATUS(state, status) {
        state.loadingStatus = status;
    },
};

const loading = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default loading;

