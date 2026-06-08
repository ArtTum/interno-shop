<script setup>

import CustomForm from "@components/incoming/CustomForm.vue";
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
    const res = await store.dispatch('incoming/fetchByField', {id});
    form.value = {
        id: res.id,
        year: res.year,
        month: res.month,
        call_date: res.call_date?.slice(0, 10) ?? '',
        next_call_date: res.next_call_date?.slice(0, 10) ?? '',
        disease_id: res.disease_id,
        hospital_id: res.hospital_id,
        disease: res.disease,
        medical_and_doctor: res.medical_and_doctor,
        phone: res.phone,
        age: res.age,
        patient_full_name: res.patient_full_name,
        other_phone: res.other_phone,
        find_aboutus: res.find_aboutus,
        additional_data: res.additional_data,
        incoming_color: res.incoming_color,
        day_surgery: res.day_surgery?.slice(0, 10) ?? '',
        month_copy: res.month_copy,
        konsultacia: res.konsultacia,
        user_id: res.user_id,
        price: res.price,
        sale_price: res.sale_price,
        sale: res.sale,
        a_d: res.a_d,

        yearRules: ['required'],
        monthRules: ['required'],
        phoneRules: ['required', 'minLength:2', 'maxLength:50'],
        call_dateRules: ['required'],
        patient_full_nameRules: ['required', 'minLength:2', 'maxLength:250'],
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
        await store.dispatch('incoming/update', form.value);

        await router.push({path: '/incomings'});

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
        <BreadcrumbDefault pageTitle="Խմբագրել Եկամուտ" :breadcrumb="[
            {path: '/dashboard', title: 'Վահանակ'},
            {path: '/incomings', title: 'ԵԿԱՄՈՒՏՆԵՐ'},
        ]"/>
        <DeleteModal
            @fetch="router.push({path: '/incomings'});"
            action-variable="incoming/delete"
            getter-variable="incoming/getDeleteModelValue"
            mutation-variable="incoming/SET_DELETE_MODAL_VALUE"
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