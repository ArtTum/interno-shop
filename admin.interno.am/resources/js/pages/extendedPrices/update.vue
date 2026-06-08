<script setup>

import CustomForm from "@components/extendedPrice/CustomForm.vue";
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
    const res = await store.dispatch('extendedPrice/fetchByField', {id});
    form.value = {
        id: res.id,
        disease: res.disease,
        diseaseRules: ['required', 'minLength:2', 'maxLength:255'],
        price: res.price,
        sale_price: res.sale_price,
        clinic: res.clinic,
        section: res.section,
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
        await store.dispatch('extendedPrice/update', {
            id: form.value.id,
            disease: form.value.disease,
            price: form.value.price,
            sale_price: form.value.sale_price,
            clinic: form.value.clinic,
            section: form.value.section,
        });

        await router.push({path: '/extended-prices'});

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
        <BreadcrumbDefault pageTitle="Խմբագրել Տարածված գին" :breadcrumb="[
            {path: '/dashboard', title: 'Վահանակ'},
            {path: '/extended-prices', title: 'Տարածված գներ'},
        ]"/>
        <DeleteModal
            @fetch="router.push({path: '/extended-prices'});"
            action-variable="extendedPrice/delete"
            getter-variable="extendedPrice/getDeleteModelValue"
            mutation-variable="extendedPrice/SET_DELETE_MODAL_VALUE"
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
