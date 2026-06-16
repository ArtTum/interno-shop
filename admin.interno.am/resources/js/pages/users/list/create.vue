<script setup>

import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import CustomForm from "@components/user/CustomForm.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";

import {reactive} from "vue";
import {useRouter} from "vue-router";
import {useStore} from "vuex";
import {validate} from "@validation/customValidation.js";

const router = useRouter()
const store = useStore();
const form = reactive({
    type: 'employees',
    user_group_id: '',
    user_group_idRules: ['required'],
    customer_group_id: '',
    newsletter_subscribed: false,
    check_client_certificate: false,
    blocked: false,
    name: '',
    nameRules: ['required', 'minLength:2', 'maxLength:50'],
    last_name: '',
    last_nameRules: ['required', 'minLength:2', 'maxLength:50'],
    email: '',
    emailRules: ['required', 'email', 'minLength:2', 'maxLength:50'],
    gtin: '',
    password: '',
    ip: '',
    ipRules: ['maxLength:250'],
    ip_expires_at: '',
    time: '00:00',
    passwordRules: ['required', 'minLength:2'],
    password_confirmation: '',
    password_confirmationRules: ['required', 'minLength:2'],
    billingAddresses: {},
    shippingAddresses: {},
    errors: {}
});

const submit = async () => {
    try {
        const errors = validate(form);
        if (Object.keys(errors).length > 0) {
            form.errors = errors;
            return false;
        }

        await store.dispatch('user/create', {
            user_group_id: form.user_group_id,
            customer_group_id: form.customer_group_id,
            name: form.name,
            last_name: form.last_name,
            type: form.type,
            email: form.email,
            newsletter_subscribed: form.newsletter_subscribed,
            gtin: form.gtin,
            blocked: form.blocked,
            password: form.password,
            ip: form.ip,
            ip_expires_at: form.ip_expires_at,
            time: form.time,
            password_confirmation: form.password_confirmation,
            billing_addresses: form.billingAddresses,
            shipping_addresses: form.shippingAddresses,
            check_client_certificate: form.check_client_certificate,
        });

        await router.push({path: '/users/list'});

        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully created'
        })
    } catch (error) {
        form.errors = error;
    }
};
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Create user" :breadcrumb="[
            {path: '/dashboard', title: 'Dashboard'},
            {path: '/users/list', title: 'Users'},
        ]"/>
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col gap-9">
                <div
                    class="rounded-sm border border-stroke bg-white shadow-default"
                >
                    <div class="border-b border-stroke py-4 px-6.5">
                        <h3 class="font-medium text-black">Fields</h3>
                    </div>
                    <CustomForm
                        v-model="form"
                        emit-action="create"
                        @submit="submit()"
                        user-type="user"
                    />
                </div>
            </div>
        </div>
    </DefaultLayoutComponent>
</template>

<style scoped>

</style>
