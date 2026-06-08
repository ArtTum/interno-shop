import baseHttp from "@store/api.js";

const state = () => ({
    notification: {},
    pushNotifications: [],
});

const getters = {
    getNotification: (state) => state.notification,
    getPushNotifications: (state) => state.pushNotifications,
};

const actions = {

};

const mutations = {
    SET_NOTIFICATION(state, data) {
        state.notification = data;
        setTimeout(() => {
            state.notification = {};
        }, 3000);
    },
    SET_NEW_PUSH_NOTIFICATION(state, data) {
        state.pushNotifications.push(data);
    },
    SET_PUSH_NOTIFICATION_SHOW_VALUE(state, params) {
        state.pushNotifications[params.index].show = params.value;
    },
};

const notification = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default notification;

