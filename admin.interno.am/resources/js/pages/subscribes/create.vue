<script setup>

import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import CustomForm from "@components/subscribe/CustomForm.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";

import {reactive} from "vue";
import {useRouter} from "vue-router";
import {useStore} from "vuex";
import {validate} from "@validation/customValidation.js";

const router = useRouter()
const store = useStore();
const now = new Date()

const form = reactive({
    year: now.getFullYear(),
    yearRules: ['required'],
    month: now.getMonth() + 1,
    monthRules: ['required'],
    full_name: '',
    full_nameRules: ['required', 'minLength:2', 'maxLength:255'],
    phone: '',
    phoneRules: ['required', 'minLength:2', 'maxLength:50'],
    description: '',
    doctor: '',
    hospital: '',
    status: 0,
    color: '',
    errors: {}
});

const submit = async () => {
    try {
        const errors = validate(form);
        if (Object.keys(errors).length > 0) {
            form.errors = errors;
            return false;
        }

        await store.dispatch('subscribe/create', form);
        await router.push({path: '/subscribes'});

        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Հաջողություն',
            message: 'Հաջողությամբ ստեղծվել է'
        })
    } catch (error) {
        form.errors = error;
    }
};
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Ստեղծել Հերթագրված" :breadcrumb="[
    { path: '/dashboard', title: 'Վահանակ' },
    { path: '/subscribes', title: 'ՀԵՐԹԱԳՐՎԱԾՆԵՐ doctor911.am' },
]" />
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col gap-9">
                <div class="rounded-sm border border-stroke bg-white shadow-default">
                    <div class="border-b border-stroke py-4 px-6.5">
                        <h3 class="font-medium text-black">Դաշտեր</h3>
                    </div>
                    <CustomForm
                        v-model="form"
                        emit-action="create"
                        @submit="submit()"
                    />
                </div>
            </div>
        </div>
    </DefaultLayoutComponent>
</template>

<style scoped></style>
