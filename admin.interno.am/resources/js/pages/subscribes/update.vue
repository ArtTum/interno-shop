<script setup>

import CustomForm from "@components/subscribe/CustomForm.vue";
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
const router = useRouter();

const id = route.params.id;
const form = reactive({value: {}});
const paramsReady = ref(false);

const fetchByField = async () => {
    const res = await store.dispatch('subscribe/fetchByField', {id});
    if (!res) {
        await router.push({path: '/subscribes'});
        return;
    }
    form.value = {
        id: res.id,
        date: res.date?.slice(0, 10) ?? '',
        year: res.year,
        month: res.month,
        full_name: res.full_name,
        phone: res.phone,
        description: res.description,
        doctor: res.doctor,
        hospital: res.hospital,
        status: parseInt(res.status) ?? 0,
        updated: res.updated,
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
        await store.dispatch('subscribe/update', form.value);
        await router.push({path: '/subscribes'});
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
        <BreadcrumbDefault pageTitle="Խմբագրել" :breadcrumb="[
            {path: '/dashboard', title: 'Վahanak'},
            {path: '/subscribes', title: 'Subscribes'},
        ]"/>
        <DeleteModal
            @fetch="router.push({path: '/subscribes'});"
            action-variable="subscribe/delete"
            getter-variable="subscribe/getDeleteModelValue"
            mutation-variable="subscribe/SET_DELETE_MODAL_VALUE"
        />
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col gap-9">
                <div class="rounded-sm border border-stroke bg-white shadow-default">
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
