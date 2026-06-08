<script setup>

import CustomForm from "@components/smsBaza/CustomForm.vue";
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import DeleteModal from "@components/global/DeleteModal.vue";
import CustomCopyButton from "@components/global/CustomCopyButton.vue";

import {reactive, ref} from "vue";
import {validate} from "@validation/customValidation.js";
import {useRoute, useRouter} from "vue-router";
import {useStore} from "vuex";

const store = useStore();
const route = useRoute();
const router = useRouter()

const id = route.params.id;
const form = reactive({
    value: {}
});
const paramsReady = ref(false);

const fetchByField = async () => {
    const res = await store.dispatch('smsBaza/fetchByField', {id});
    form.value = {
        id: res.id,
        year: res.year,
        month: res.month,
        call_date: res.call_date,
        phone: res.phone,
        other_phone: res.other_phone,
        sms_bazacol: res.sms_bazacol,
        patient_full_name: res.patient_full_name,
        patient_full_nameRules: ['required', 'minLength:2', 'maxLength:255'],
        disease: res.disease,
        medical_and_doctor: res.medical_and_doctor,
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
        await store.dispatch('smsBaza/update', {
            id: form.value.id,
            year: form.value.year,
            month: form.value.month,
            call_date: form.value.call_date,
            phone: form.value.phone,
            other_phone: form.value.other_phone,
            sms_bazacol: form.value.sms_bazacol,
            patient_full_name: form.value.patient_full_name,
            disease: form.value.disease,
            medical_and_doctor: form.value.medical_and_doctor,
        });

        await router.push({path: '/sms-bazas'});

        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Հաջողություն',
            message: 'Հաջողությամբ թարմացվեց'
        });
    } catch (error) {
        form.value.errors = error;
    }
};
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Խմբագրել SMS Բազա" :breadcrumb="[
            {path: '/dashboard', title: 'Վահանակ'},
            {path: '/sms-bazas', title: 'SMS Բազա'},
        ]"/>
        <DeleteModal
            @fetch="router.push({path: '/sms-bazas'});"
            action-variable="smsBaza/delete"
            getter-variable="smsBaza/getDeleteModelValue"
            mutation-variable="smsBaza/SET_DELETE_MODAL_VALUE"
        />
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col gap-9">
                <div
                    class="rounded-sm border border-stroke bg-white shadow-default"
                >
                    <div class="border-b border-stroke py-4 px-6.5">
                        <h3 class="font-medium text-black">#{{ form.value.id }} <CustomCopyButton :text="form.value.id"/></h3>
                    </div>

                    <template v-if="paramsReady">
                        <CustomForm
                            v-model="form.value"
                            emit-action="update"
                            @submit="submit()"
                        />
                    </template>

                </div>
            </div>
        </div>
    </DefaultLayoutComponent>
</template>

<style scoped></style>
