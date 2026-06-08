<script setup>

import CustomForm from "@components/hospital/CustomForm.vue";
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
    const res = await store.dispatch('hospital/fetchByField', {id});
    form.value = {
        id: res.id,
        name: res.name,
        nameRules: ['required', 'minLength:2', 'maxLength:50'],
        full_name: res.full_name || '',
        phone: res.phone || '',
        email: res.email || '',
        address: res.address || '',
        sale: res.sale || '',
        sale_2: res.sale_2 || '',
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
        await store.dispatch('hospital/update', {
            id: form.value.id,
            name: form.value.name,
            full_name: form.value.full_name,
            phone: form.value.phone,
            email: form.value.email,
            address: form.value.address,
            sale: form.value.sale,
            sale_2: form.value.sale_2,
            ips: form.value.ips,
        });

        await router.push({path: '/hospitals'});

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
        <BreadcrumbDefault pageTitle="Խմբագրել հիվանդանոց" :breadcrumb="[
            {path: '/dashboard', title: 'Վահանակ'},
            {path: '/hospitals', title: 'Հիվանդանոցներ'},
        ]"/>
        <DeleteModal
            @fetch="router.push({path: '/hospitals'});"
            action-variable="hospital/delete"
            getter-variable="hospital/getDeleteModelValue"
            mutation-variable="hospital/SET_DELETE_MODAL_VALUE"
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
