<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import CustomForm from "@components/language/CustomForm.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";

import {reactive} from "vue";
import {useRouter} from "vue-router";
import {useStore} from "vuex";
import {validate} from "@validation/customValidation.js";

const router = useRouter()
const store = useStore();

const form = reactive({
    name: '',
    nameRules: ['required', 'minLength:2', 'maxLength:15'],
    code: '',
    codeRules: ['required', 'minLength:1', 'maxLength:3'],
    hreflang: '',
    hreflangRules: ['required', 'minLength:2', 'maxLength:10'],
    local_for_trustpilot: '',
    local_for_trustpilotRules: ['required', 'minLength:2', 'maxLength:10'],
    default_hreflang: false,
    is_rtl: false,
    status: true,
    draft: true,
    base: false,
    errors: {}
});

const submit = async () => {
    try {
        const errors = validate(form);
        if (Object.keys(errors).length > 0) {
            form.errors = errors;
            return false;
        }

        await store.dispatch('language/create', form);
        await router.push({path: '/settings/languages'});

        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully created'
        })
    } catch (error) {
        form.errors = error;
    }
};
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Create language" :breadcrumb="[
            {path: '/', title: 'Dashboard'},
            {path: '/settings/languages', title: 'Languages'},
        ]"/>
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col gap-9">
                <div class="rounded-sm border border-stroke bg-white shadow-default">
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
