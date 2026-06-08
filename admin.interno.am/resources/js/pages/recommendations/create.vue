<script setup>

import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import CustomForm from "@components/recommendation/CustomForm.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";

import {reactive} from "vue";
import {useRouter} from "vue-router";
import {useStore} from "vuex";
import {validate} from "@validation/customValidation.js";

const router = useRouter()
const store = useStore();
const now = new Date()
const today = now.toISOString().slice(0, 10);

const form = reactive({
    year: now.getFullYear(),
    yearRules: ['required'],
    month: now.getMonth() + 1,
    monthRules: ['required'],
    call_date: today,
    call_dateRules: ['required'],
    next_call_date: '',
    disease_id: '',
    hospital_id: '',
    disease: '',
    medical_and_doctor: '',
    phone: '',
    phoneRules: ['required', 'minLength:2', 'maxLength:50'],
    age: '',
    patient_full_name: '',
    patient_full_nameRules: ['required', 'minLength:2', 'maxLength:250'],
    other_phone: '',
    find_aboutus: '',
    find_aboutusRules: ['required'],
    info: [],
    additional_data: '',
    color: '#000000',
    day_surgery: '',
    departure_datetime: '',
    preliminary_price: '',
    month_copy: '',
    konsultacia: 1,
    konsultaciaRules: ['required'],
    user_id: '',
    user_idRules: ['required'],
    errors: {}
});

const submit = async () => {
    try {
        const errors = validate(form);
        if (Object.keys(errors).length > 0) {
            form.errors = errors;
            return false;
        }

        await store.dispatch('recommendation/create', form);
        await router.push({path: '/recommendations'});

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
        <BreadcrumbDefault pageTitle="Ստեղծել Հերթագրում" :breadcrumb="[
    { path: '/dashboard', title: 'Վահանակ' },
    { path: '/recommendation', title: 'Հերթագրումներ' },
]" />
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col gap-9">
                <div
                    class="rounded-sm border border-stroke bg-white shadow-default"
                >
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

<style scoped>

</style>
