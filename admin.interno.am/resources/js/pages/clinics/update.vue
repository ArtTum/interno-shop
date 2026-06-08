<script setup>

import CustomForm from "@components/clinic/CustomForm.vue";
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
    const res = await store.dispatch('clinic/fetchByField', {id});
    form.value = {
        id: res.id,
        clinic: res.clinic,
        clinicRules: ['required', 'minLength:2', 'maxLength:255'],
        name: res.name,
        phone: res.phone,
        email: res.email,
        sale: res.sale,
        other_sale: res.other_sale,
        address: res.address,
        color: res.color,
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
        await store.dispatch('clinic/update', {
            id: form.value.id,
            clinic: form.value.clinic,
            name: form.value.name,
            phone: form.value.phone,
            email: form.value.email,
            sale: form.value.sale,
            other_sale: form.value.other_sale,
            address: form.value.address,
            color: form.value.color,
        });

        await router.push({path: '/clinics'});

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
        <BreadcrumbDefault pageTitle="Խմբագրել Կլինիկա" :breadcrumb="[
            {path: '/dashboard', title: 'Վահանակ'},
            {path: '/clinics', title: 'Կլինիկաներ'},
        ]"/>
        <DeleteModal
            @fetch="router.push({path: '/clinics'});"
            action-variable="clinic/delete"
            getter-variable="clinic/getDeleteModelValue"
            mutation-variable="clinic/SET_DELETE_MODAL_VALUE"
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
