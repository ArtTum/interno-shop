<script setup>

import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import CustomForm from "@components/smsBaza/CustomForm.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";

import {reactive} from "vue";
import {useRouter} from "vue-router";
import {useStore} from "vuex";
import {validate} from "@validation/customValidation.js";

const router = useRouter()
const store = useStore();
const form = reactive({
    year: null,
    month: null,
    call_date: '',
    phone: '',
    other_phone: '',
    sms_bazacol: '',
    patient_full_name: '',
    patient_full_nameRules: ['required', 'minLength:2', 'maxLength:255'],
    disease: '',
    medical_and_doctor: '',
    errors: {}
});

const submit = async () => {
    try {
        const errors = validate(form);
        if (Object.keys(errors).length > 0) {
            form.errors = errors;
            return false;
        }

        await store.dispatch('smsBaza/create', {
            year: form.year,
            month: form.month,
            call_date: form.call_date,
            phone: form.phone,
            other_phone: form.other_phone,
            sms_bazacol: form.sms_bazacol,
            patient_full_name: form.patient_full_name,
            disease: form.disease,
            medical_and_doctor: form.medical_and_doctor,
        });

        await router.push({path: '/sms-bazas'});

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
        <BreadcrumbDefault pageTitle="Ստեղծել SMS Բազա" :breadcrumb="[
    { path: '/dashboard', title: 'Վահանակ' },
    { path: '/sms-bazas', title: 'SMS Բազա' },
]" />
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
                    />

                </div>

            </div>
        </div>
    </DefaultLayoutComponent>
</template>

<style scoped>

</style>
