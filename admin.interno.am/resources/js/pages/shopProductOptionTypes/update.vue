<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import DeleteModal from "@components/global/DeleteModal.vue";
import CustomCopyButton from "@components/global/CustomCopyButton.vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import Switch from "@components/global/Switch.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import {computed, ref} from "vue";
import {useRoute, useRouter} from "vue-router";
import {useStore} from "vuex";

const store = useStore();
const route = useRoute();
const router = useRouter();
const form = ref(null);
const auth = computed(() => store.getters['auth/getUser']);
const permission = computed(() => auth.value?.user_group?.permissions_by_name?.shop_product_option_types?.[0] || {});
const canEdit = computed(() => auth.value?.superadmin || permission.value.can_edit);
const canDelete = computed(() => auth.value?.superadmin || permission.value.can_delete);

const fetchType = async () => {
    const response = await store.dispatch('shopProductOptionType/fetchByField', {id: route.params.id});
    form.value = {...response.data, errors: {}};
};

const submit = async () => {
    try {
        form.value.errors = {};
        await store.dispatch('shopProductOptionType/update', form.value);
        await fetchType();
        store.commit('notification/SET_NOTIFICATION', {visible: true, title: 'Success', message: 'Successfully updated'});
    } catch (error) {
        form.value.errors = error;
    }
};

const afterDelete = () => router.push('/shop-product-option-types');
fetchType();
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Update product type" :breadcrumb="[{path: '/', title: 'Dashboard'}, {path: '/shop-product-option-types', title: 'Product Types'}]"/>
        <DeleteModal @fetch="afterDelete" action-variable="shopProductOptionType/delete" getter-variable="shopProductOptionType/getDeleteModelValue" mutation-variable="shopProductOptionType/SET_DELETE_MODAL_VALUE"/>
        <div v-if="form" class="rounded-sm border border-stroke bg-white shadow-default">
            <div class="border-b border-stroke px-6 py-4"><h3 class="font-medium text-black">#{{ form.id }} <CustomCopyButton :text="form.id"/></h3></div>
            <form @submit.prevent="submit">
                <div v-if="form.errors && Object.keys(form.errors).length" class="p-6"><AlertError :errors="form.errors"/></div>
                <div class="grid grid-cols-3 gap-6 p-6 max-md:grid-cols-1">
                    <CustomInput :disabled="!canEdit" v-model="form.name" name="name" label="Name *" type="text" placeholder="Enter name" :error="form.errors?.name"/>
                    <CustomInput :disabled="!canEdit" v-model="form.sort_order" name="sort_order" label="Sort order" type="number" placeholder="Enter sort order" :error="form.errors?.sort_order"/>
                    <div class="flex items-end pb-2"><Switch :disabled="!canEdit" @change="(value) => form.status = value" :value="form.status" id="type_status" label="Active"/></div>
                </div>
                <hr class="text-gray mt-6.5">
                <div class="flex gap-5 p-6.5 save-button-fixed">
                    <CustomButton v-if="canDelete" @click="store.commit('shopProductOptionType/SET_DELETE_MODAL_VALUE', {value: true, id: form.id})" class="ml-auto flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80" type="button"><font-awesome-icon :icon="['far', 'trash']"/>Delete</CustomButton>
                    <CustomButton v-if="canEdit" class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80" type="submit"><font-awesome-icon :icon="['far', 'floppy-disk']"/>Save</CustomButton>
                </div>
            </form>
        </div>
    </DefaultLayoutComponent>
</template>
