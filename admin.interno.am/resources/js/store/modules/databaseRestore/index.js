import baseHttp from "@store/api.js";

const state = () => ({
    files: [],
    selectedFile: null,
    loading: false,
    message: '',
    success: false,
});

const getters = {
    getFiles: (state) => state.files,
    getSelectedFile: (state) => state.selectedFile,
    getLoading: (state) => state.loading,
    getMessage: (state) => state.message,
    getSuccess: (state) => state.success,
};

const actions = {
    async fetchListBackups({ commit }) {
        commit('SET_LOADING', true);
        try {
            const res = await baseHttp.get('/restore-db');
            commit('SET_FILES', res.data.files || []);
        } catch (e) {
            commit('SET_MESSAGE', { message: 'Failed to load files.', success: false });
        } finally {
            commit('SET_LOADING', false);
        }
    },

    async import({ commit, state }) {
        if (!state.selectedFile) return;

        commit('SET_LOADING', true);
        commit('SET_MESSAGE', { message: '', success: false });

        try {
            await baseHttp.post('/restore-db/import', {
                filename: state.selectedFile
            });
            commit('SET_MESSAGE', { message: 'Import started successfully.', success: true });
        } catch (e) {
            commit('SET_MESSAGE', { message: 'Import failed.', success: false });
        } finally {
            commit('SET_LOADING', false);
        }
    }
};

const mutations = {
    SET_FILES(state, files) {
        state.files = files;
    },
    SET_SELECTED_FILE(state, file) {
        state.selectedFile = file;
    },
    SET_LOADING(state, status) {
        state.loading = status;
    },
    SET_MESSAGE(state, { message, success }) {
        state.message = message;
        state.success = success;
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};
