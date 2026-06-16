<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
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
const permission = computed(() => auth.value?.user_group?.permissions_by_name?.shop_craftsmen?.[0] || {});
const canEdit = computed(() => auth.value?.superadmin || permission.value.can_edit);

const fetchByField = async () => {
    const response = await store.dispatch('shopCraftsman/fetchByField', {id: route.params.id});
    form.value = {...response.data, errors: {}};
};

const submit = async () => {
    try {
        form.value.errors = {};
        await store.dispatch('shopCraftsman/update', form.value);
        await fetchByField();
        store.commit('notification/SET_NOTIFICATION', {visible: true, title: 'Success', message: 'Successfully updated'});
    } catch (error) {
        form.value.errors = error;
    }
};

fetchByField();
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Update craftsman" :breadcrumb="[{path: '/', title: 'Dashboard'}, {path: '/shop-craftsmen', title: 'Craftsmen'}]"/>
        <div v-if="form" class="rounded-sm border border-stroke bg-white shadow-default">
            <div class="border-b border-stroke px-6 py-4"><h3 class="font-medium text-black">#{{ form.id }}</h3></div>
            <form @submit.prevent="submit">
                <div v-if="form.errors && Object.keys(form.errors).length" class="p-6"><AlertError :errors="form.errors"/></div>
                <div class="grid grid-cols-3 gap-6 p-6 max-md:grid-cols-1">
                    <CustomInput :disabled="!canEdit" v-model="form.code" name="code" label="Code *" type="text" placeholder="Enter code" :error="form.errors?.code"/>
                    <CustomInput :disabled="!canEdit" v-model="form.first_name" name="first_name" label="First name *" type="text" placeholder="Enter first name" :error="form.errors?.first_name"/>
                    <CustomInput :disabled="!canEdit" v-model="form.last_name" name="last_name" label="Last name" type="text" placeholder="Enter last name" :error="form.errors?.last_name"/>
                    <CustomInput :disabled="!canEdit" v-model="form.phone" name="phone" label="Phone" type="text" placeholder="Enter phone" :error="form.errors?.phone"/>
                    <CustomInput :disabled="!canEdit" v-model="form.sort_order" name="sort_order" label="Sort order" type="number" placeholder="Enter sort order" :error="form.errors?.sort_order"/>
                    <div class="flex items-end pb-2"><Switch :disabled="!canEdit" @change="(value) => form.status = value" :value="form.status" id="craftsman_status" label="Active"/></div>
                </div>
                <hr class="text-gray mt-6.5">
                <div v-if="canEdit" class="flex p-6.5 save-button-fixed"><CustomButton class="ml-auto flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80" type="submit"><font-awesome-icon :icon="['far', 'floppy-disk']"/>Save</CustomButton></div>
            </form>
        </div>
    </DefaultLayoutComponent>
</template>
