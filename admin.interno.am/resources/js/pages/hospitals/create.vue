<script setup>

import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import CustomForm from "@components/hospital/CustomForm.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";

import {reactive} from "vue";
import {useRouter} from "vue-router";
import {useStore} from "vuex";
import {validate} from "@validation/customValidation.js";

const router = useRouter()
const store = useStore();
const form = reactive({
    name: '',
    nameRules: ['required', 'minLength:2', 'maxLength:50'],
    full_name: '',
    phone: '',
    email: '',
    address: '',
    sale: '',
    sale_2: '',
    ips: [],
    errors: {}
});

const submit = async () => {
    try {
        const errors = validate(form);
        if (Object.keys(errors).length > 0) {
            form.errors = errors;
            return false;
        }

        await store.dispatch('hospital/create', {
            name: form.name,
            full_name: form.full_name,
            phone: form.phone,
            email: form.email,
            address: form.address,
            sale: form.sale,
            sale_2: form.sale_2,
            ips: form.ips,
        });

        await router.push({path: '/hospitals'});

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
        <BreadcrumbDefault pageTitle="Ստեղծել հիվանդանոց" :breadcrumb="[
    { path: '/dashboard', title: 'Վահանակ' },
    { path: '/hospitals', title: 'Հիվանդանոցներ' },
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
