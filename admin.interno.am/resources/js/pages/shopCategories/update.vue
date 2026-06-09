<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import DeleteModal from "@components/global/DeleteModal.vue";
import CustomForm from "@components/shopCategory/CustomForm.vue";
import {computed, ref} from "vue";
import {useRoute, useRouter} from "vue-router";
import {useStore} from "vuex";

const store = useStore();
const route = useRoute();
const router = useRouter();
const form = ref(null);
const params = computed(() => store.getters['shopCategory/getParams']);

const fetchCategory = async (languageId = Number(route.params.languageId) || 0) => {
    const response = await store.dispatch('shopCategory/fetchByField', {
        id: route.params.id,
        language_id: languageId,
    });
    form.value = {
        ...response.data,
        errors: {},
    };
};

const changeLanguage = async (languageId) => {
    if (!form.value?.id || !languageId) return;
    await router.push(`/shop-categories/update/${form.value.id}/${languageId}`);
    await fetchCategory(languageId);
};

const submit = async () => {
    try {
        form.value.errors = {};
        await store.dispatch('shopCategory/update', form.value);
        await fetchCategory(form.value.language_id);
        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully updated',
        });
    } catch (error) {
        form.value.errors = error;
    }
};

const afterDelete = () => {
    router.push('/shop-categories');
};

fetchCategory();
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Update shop category" :breadcrumb="[
            {path: '/', title: 'Dashboard'},
            {path: '/shop-categories', title: 'Shop Categories'},
        ]" />

        <DeleteModal
            @fetch="afterDelete"
            action-variable="shopCategory/delete"
            getter-variable="shopCategory/getDeleteModelValue"
            mutation-variable="shopCategory/SET_DELETE_MODAL_VALUE"
        />

        <div v-if="form" class="rounded-sm border border-stroke bg-white shadow-default">
            <div class="border-b border-stroke px-6 py-4">
                <h3 class="font-medium text-black">#{{ form.id }}</h3>
            </div>
            <CustomForm
                v-model="form"
                :params="params"
                emit-action="update"
                @language-change="changeLanguage"
                @submit="submit"
            />
        </div>
    </DefaultLayoutComponent>
</template>
