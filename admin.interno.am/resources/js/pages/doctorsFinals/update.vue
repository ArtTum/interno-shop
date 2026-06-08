<script setup>

import CustomForm from "@components/doctorsFinal/CustomForm.vue";
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
    const res = await store.dispatch('doctorsFinal/fetchByField', {id});
    form.value = {
        id: res.id,
        full_name: res.full_name,
        full_nameRules: ['required', 'minLength:2', 'maxLength:255'],
        profession: res.profession,
        degree: res.degree,
        workplace: res.workplace,
        other_info: res.other_info,
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
        await store.dispatch('doctorsFinal/update', {
            id: form.value.id,
            full_name: form.value.full_name,
            profession: form.value.profession,
            degree: form.value.degree,
            workplace: form.value.workplace,
            other_info: form.value.other_info,
        });

        await router.push({path: '/doctors-finals'});

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
        <BreadcrumbDefault pageTitle="Խմբագրել Բժիշկ" :breadcrumb="[
            {path: '/dashboard', title: 'Վահանակ'},
            {path: '/doctors-finals', title: 'Բժիշկներ'},
        ]"/>
        <DeleteModal
            @fetch="router.push({path: '/doctors-finals'});"
            action-variable="doctorsFinal/delete"
            getter-variable="doctorsFinal/getDeleteModelValue"
            mutation-variable="doctorsFinal/SET_DELETE_MODAL_VALUE"
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
