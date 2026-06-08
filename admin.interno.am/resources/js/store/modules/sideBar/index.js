const state = () => ({
    isHovered: false,
    isSidebarOpen: false,
    isSidebarMenuOpen: false,
    selected: localStorage.getItem('selected') || 'Dashboard',
    page: localStorage.getItem('page') || 'Dashboard',
    navTimestamp: 0,
});

const getters = {
    isHovered: (state) => state.isHovered,
    isSidebarOpen: (state) => state.isSidebarOpen,
    isSidebarMenuOpen: (state) => state.isSidebarMenuOpen,
    selected: (state) => state.selected,
    page: (state) => state.page,
    navTimestamp: (state) => state.navTimestamp,
};

const actions = {
    toggleSidebar({ commit }) {
        commit('TOGGLE_SIDEBAR')
    },
    toggleSidebarMenu({ commit }) {
        commit('TOGGLE_SIDEBAR_MENU')
    },
    setSelected({ commit }, selected) {
        commit('SET_SELECTED', selected)
    },
    setPage({ commit }, page) {
        commit('SET_PAGE', page)
    },
    setSidebarState({ commit }, value) {
        commit("SET_SIDEBAR_STATE", value);
    },
};

const mutations = {
    TOGGLE_SIDEBAR_MENU(state) {
        state.isSidebarMenuOpen = !state.isSidebarMenuOpen
        if (state.isSidebarMenuOpen) {
            state.isHovered = true;
        } else {
            state.isHovered = false;
        }
    },
    SET_SIDEBAR_STATE(state, value) {
        state.isHovered = value;
    },
    TOGGLE_SIDEBAR(state) {
        state.isSidebarOpen = !state.isSidebarOpen
    },
    SET_SELECTED(state, selected) {
        state.selected = selected
        localStorage.setItem('selected', selected)
    },
    SET_PAGE(state, page) {
        state.page = page
        localStorage.setItem('page', page)
    },
    UPDATE_NAV_TIMESTAMP(state) {
        state.navTimestamp = Date.now()
    }
};

const sideBar = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default sideBar;
