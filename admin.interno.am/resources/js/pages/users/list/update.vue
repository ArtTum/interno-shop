<script setup>

import CustomForm from "@components/user/CustomForm.vue";
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import DeleteModal from "@components/global/DeleteModal.vue";
import CustomCopyButton from "@components/global/CustomCopyButton.vue";

import {computed, reactive, ref} from "vue";

import {useStore} from "vuex";
import {useRoute, useRouter} from "vue-router";

const store = useStore();
const route = useRoute();
const router = useRouter()

import {validate} from "@validation/customValidation.js";

const id = route.params.id;
const form = reactive({
    value: {}
});
const paramsReady = ref(false);

const fetchByField = async () => {
    const res = await store.dispatch('user/fetchByField', {
        id,
        type: 'employees',
    });
    form.value = {
        type: 'employees',
        id: res.id,
        balance: res.balance,
        currency_code: res.currency_code,
        superadmin: res.superadmin,
        user_group_id: res.user_group_id,
        customer_group_id: res.customer_group_id,
        newsletter_subscribed: res.newsletter_subscribed,
        blocked: res.blocked,
        reviews_count: res.reviews_count,
        orders_count: res.orders_count,
        name: res.name,
        nameRules: ['required', 'minLength:2', 'maxLength:50'],
        last_name: res.last_name,
        last_nameRules: ['required', 'minLength:2', 'maxLength:50'],
        gtin: res.gtin ,
        email: res.email ,
        ip: res.ip ? res.ip : '',
        ipRules: ['maxLength:250'],
        ip_expires_at: res.ip_expires_at,
        time: res.time || '00:00',
        emailRules: ['required', 'email', 'minLength:2', 'maxLength:50'],
        password: '',
        password_confirmation: '',
        billingAddresses: res.user_billing_address ? res.user_billing_address : {},
        shippingAddresses: res.user_shipping_address ? res.user_shipping_address : {},
        check_client_certificate: res.check_client_certificate,
        errors: {},
        valid: true
    };
    paramsReady.value = true;
};

fetchByField();

const submit = async () => {
    try {
        const errors = validate(form.value);
        if (Object.keys(errors).length > 0) {
            form.value.errors = errors;
            return false;
        }

        await store.dispatch('user/update', {
            id: form.value.id,
            name: form.value.name,
            user_group_id: form.value.user_group_id,
            customer_group_id: form.value.customer_group_id,
            ip: form.value.ip,
            type: form.value.type,
            ip_expires_at: form.value.ip_expires_at,
            time: form.value.time,
            newsletter_subscribed: form.value.newsletter_subscribed,
            last_name: form.value.last_name,
            email: form.value.email,
            gtin: form.value.gtin,
            blocked: form.value.blocked,
            password: form.value.password,
            password_confirmation: form.value.password_confirmation,
            billing_addresses: form.value.billingAddresses,
            shipping_addresses: form.value.shippingAddresses,
            check_client_certificate: form.value.check_client_certificate,
        });

        // await router.push({path: '/users/list'});

        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully updated'
        });
    } catch (error) {
        form.value.errors = error;
    }
};
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Edit user" :breadcrumb="[
            {path: '/dashboard', title: 'Dashboard'},
            {path: '/users/list', title: 'Users'},
        ]"/>
        <DeleteModal
            @fetch="router.push({path: '/users/list'});"
            action-variable="user/delete"
            getter-variable="user/getDeleteModelValue"
            mutation-variable="user/SET_DELETE_MODAL_VALUE"
        />
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col gap-9">
                <div class="rounded-sm border border-stroke bg-white shadow-default">
                    <div class="border-b border-stroke py-4 px-6.5 flex ">
                        <h3 class="font-medium text-black">#{{ form.value.id }} <CustomCopyButton :text="form.value.id"/></h3>

                    </div>
                    <template v-if="paramsReady">
                        <CustomForm
                            v-model="form.value"
                            emit-action="update"
                            @submit="submit()"
                            user-type="user"
                        />
                    </template>
                </div>
            </div>
        </div>
    </DefaultLayoutComponent>
</template>

<style scoped></style>
