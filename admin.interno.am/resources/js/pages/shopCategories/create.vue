<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import CustomForm from "@components/shopCategory/CustomForm.vue";
import {computed, reactive} from "vue";
import {useRouter} from "vue-router";
import {useStore} from "vuex";

const store = useStore();
const router = useRouter();
const params = computed(() => store.getters['shopCategory/getParams']);

const form = reactive({
    language_id: null,
    parent_id: null,
    global_slug: '',
    name: '',
    slug: '',
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
    status: true,
    sort_order: 0,
    errors: {},
});

const fetchParams = async () => {
    const data = await store.dispatch('shopCategory/fetchParams', {language_id: form.language_id || 0});
    form.language_id = form.language_id || data.base_language_id || data.languages?.[0]?.value || null;
};

const submit = async () => {
    try {
        form.errors = {};
        const response = await store.dispatch('shopCategory/create', form);
        await router.push(`/shop-categories/update/${response.data.data.id}/${form.language_id}`);
        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully created',
        });
    } catch (error) {
        form.errors = error;
    }
};

fetchParams();
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Create shop category" :breadcrumb="[
            {path: '/', title: 'Dashboard'},
            {path: '/shop-categories', title: 'Shop Categories'},
        ]" />

        <div class="rounded-sm border border-stroke bg-white shadow-default">
            <div class="border-b border-stroke px-6 py-4">
                <h3 class="font-medium text-black">Fields</h3>
            </div>
            <CustomForm
                v-model="form"
                :params="params"
                emit-action="create"
                @language-change="fetchParams"
                @submit="submit"
            />
        </div>
    </DefaultLayoutComponent>
</template>
