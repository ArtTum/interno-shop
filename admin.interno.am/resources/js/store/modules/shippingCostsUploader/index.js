import baseHttp from "@store/api.js";

const state = () => ({

});

const getters = {

};

const actions = {
    async process({}, params) {
        return await baseHttp.post(`shipping-costs/process`, params);
    },
};

const mutations = {

};

const shippingCostsUploader = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

export default shippingCostsUploader;

