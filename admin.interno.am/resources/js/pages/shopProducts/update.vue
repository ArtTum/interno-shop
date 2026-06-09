<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import DeleteModal from "@components/global/DeleteModal.vue";
import CustomCopyButton from "@components/global/CustomCopyButton.vue";
import CustomForm from "@components/shopProduct/CustomForm.vue";
import {computed, ref} from "vue";
import {useRoute, useRouter} from "vue-router";
import {useStore} from "vuex";

const store = useStore();
const route = useRoute();
const router = useRouter();
const form = ref(null);
const params = computed(() => store.getters['shopProduct/getParams']);

const fetchProduct = async (languageId = Number(route.params.languageId) || 0) => {
    const response = await store.dispatch('shopProduct/fetchByField', {
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
    await router.push(`/shop-products/update/${form.value.id}/${languageId}`);
    await fetchProduct(languageId);
};

const submit = async () => {
    try {
        form.value.errors = {};
        await store.dispatch('shopProduct/update', form.value);
        await fetchProduct(form.value.language_id);
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
    router.push('/shop-products');
};

fetchProduct();
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Update shop product" :breadcrumb="[
            {path: '/', title: 'Dashboard'},
            {path: '/shop-products', title: 'Shop Products'},
        ]"/>

        <DeleteModal
            @fetch="afterDelete"
            action-variable="shopProduct/delete"
            getter-variable="shopProduct/getDeleteModelValue"
            mutation-variable="shopProduct/SET_DELETE_MODAL_VALUE"
        />

        <div v-if="form" class="rounded-sm border border-stroke bg-white shadow-default">
            <div class="border-b border-stroke px-6 py-4">
                <h3 class="font-medium text-black">#{{ form.id }} <CustomCopyButton :text="form.id"/></h3>
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
