import axios from 'axios';
const baseURL = '/api/erp/';
import store from "@store";

const baseHttp = axios.create({
    baseURL,
    withCredentials: true
});

baseHttp.interceptors.request.use(
    (config) => {
        if (!config.params || !config.params.dontNeedLoading) {
            store.commit('loading/SET_LOADING_STATUS', true)
        }
        config.headers.Authorization = `Bearer ${localStorage.getItem('epodexAuthToken')}`;
        config.headers.VendorKey = localStorage.getItem('vendor_key');

        return config;
    }
);

baseHttp.interceptors.response.use(
    (response) => {
        if ((!response.config.params || !response.config.params.dontNeedLoading)) {
            store.commit('loading/SET_LOADING_STATUS', false);
        }
        return response;
    },
    (error) => {
        store.commit('loading/SET_LOADING_STATUS', false);
        let prepareResponse = {};

        if(error.response.data?.logout){
            store.commit('auth/CLEAR_USER');
            store.commit('auth/SET_AUTHENTICATED_STATUS', false);
            localStorage.removeItem('epodexAuthToken');
            localStorage.removeItem('vendor_key');
        }

        if (error.response.status === 403) {
            store.commit('auth/SET_FORBIDDEN_STATUS', true);
        } else {
            store.commit('auth/SET_FORBIDDEN_STATUS', false);
        }

        if (error.response && error.response.data) {
            if (error.response.data.message) {
                prepareResponse = {general: {general: [error.response.data.message]}}
            } else if (error.response.data.errors) {
                prepareResponse = {general: error.response.data.errors}
            } else {
                prepareResponse = {general: {general: ['Bad request!']}}
            }
        } else {
            prepareResponse = {general: {general: ['Bad request!']}}
        }

        return Promise.reject(prepareResponse);
    }
);

export default baseHttp;
