<script setup>

import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import CustomForm from "@components/extendedPrice/CustomForm.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";

import {reactive} from "vue";
import {useRouter} from "vue-router";
import {useStore} from "vuex";
import {validate} from "@validation/customValidation.js";

const router = useRouter()
const store = useStore();
const form = reactive({
    disease: '',
    diseaseRules: ['required', 'minLength:2', 'maxLength:255'],
    price: '',
    sale_price: '',
    clinic: '',
    section: '',
    errors: {}
});

const submit = async () => {
    try {
        const errors = validate(form);
        if (Object.keys(errors).length > 0) {
            form.errors = errors;
            return false;
        }

        await store.dispatch('extendedPrice/create', {
            disease: form.disease,
            price: form.price,
            sale_price: form.sale_price,
            clinic: form.clinic,
            section: form.section,
        });

        await router.push({path: '/extended-prices'});

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
        <BreadcrumbDefault pageTitle="Ստեղծել Տարածված գին" :breadcrumb="[
    { path: '/dashboard', title: 'Վահանակ' },
    { path: '/extended-prices', title: 'Տարածված գներ' },
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
