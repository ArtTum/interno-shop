<script setup>
import CustomForm from "@components/language/CustomForm.vue";
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import DeleteModal from "@components/global/DeleteModal.vue";
import CustomCopyButton from "@components/global/CustomCopyButton.vue";

import {reactive, ref} from "vue";
import {useStore} from "vuex";
import {useRoute, useRouter} from "vue-router";
import {validate} from "@validation/customValidation.js";

const store = useStore();
const route = useRoute();
const router = useRouter()
const code = route.params.code;

const form = reactive({
    value: {}
});
const paramsReady = ref(false);

const fetchByField = async () => {
    const res = await store.dispatch('language/fetchByField', {code});

    form.value = {
        id: res.data.id,
        name: res.data.name,
        currency_id: res.data.currency_id,
        currencies: res.currencies,
        nameRules: ['required', 'minLength:2', 'maxLength:15'],
        hreflang: res.data.hreflang,
        hreflangRules: ['required', 'minLength:2', 'maxLength:10'],
        local_for_trustpilot: res.data.local_for_trustpilot,
        local_for_trustpilotRules: ['required', 'minLength:2', 'maxLength:10'],
        default_hreflang: res.data.default_hreflang,
        is_rtl: res.data.is_rtl,
        old_code: res.data.code,
        code: res.data.code,
        codeRules: ['required', 'minLength:1', 'maxLength:3'],
        base: res.data.base,
        status: res.data.status,
        draft: res.data.draft,
        email: res.data.email,
        errors: {}
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

        await store.dispatch('language/update', form.value);
        await router.push({path: '/settings/languages'});

        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully updated'
        });
    } catch (error) {
        form.value.errors = error;
    }
};
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Edit language" :breadcrumb="[
            {path: '/', title: 'Dashboard'},
            {path: '/settings/languages', title: 'Languages'},
        ]"/>
        <DeleteModal
            @fetch="router.push({path: '/settings/languages'});"
            action-variable="language/delete"
            getter-variable="language/getDeleteModelValue"
            mutation-variable="language/SET_DELETE_MODAL_VALUE"
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
