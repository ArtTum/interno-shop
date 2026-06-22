<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import Switch from "@components/global/Switch.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import {reactive} from "vue";
import {useRouter} from "vue-router";
import {useStore} from "vuex";

const store = useStore();
const router = useRouter();
const form = reactive({name: '', value: '#000000', status: true, sort_order: 0, errors: {}});
const submit = async () => {
    try {
        form.errors = {};
        const response = await store.dispatch('shopProductColor/create', form);
        await router.push(`/shop-product-colors/update/${response.data.data.id}`);
        store.commit('notification/SET_NOTIFICATION', {visible: true, title: 'Success', message: 'Successfully created'});
    } catch (error) {
        form.errors = error;
    }
};
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Create product color" :breadcrumb="[{path: '/', title: 'Dashboard'}, {path: '/shop-product-colors', title: 'Product Colors'}]"/>
        <div class="rounded-sm border border-stroke bg-white shadow-default">
            <div class="border-b border-stroke px-6 py-4"><h3 class="font-medium text-black">Fields</h3></div>
            <form @submit.prevent="submit">
                <div v-if="form.errors && Object.keys(form.errors).length" class="p-6"><AlertError :errors="form.errors"/></div>
                <div class="grid grid-cols-4 gap-6 p-6 max-md:grid-cols-1">
                    <CustomInput v-model="form.name" name="name" label="Name *" type="text" placeholder="Enter name" :error="form.errors?.name"/>
                    <div>
                        <label class="mb-2.5 block font-medium text-black" for="product_color_value">Value</label>
                        <input
                            id="product_color_value"
                            v-model="form.value"
                            name="value"
                            type="color"
                            placeholder="#000000"
                            class="product-color-value"
                            :class="{'is-invalid': form.errors?.value}"
                        >
                        <div v-if="form.errors?.value" class="invalid-feedback">
                            <span v-if="Array.isArray(form.errors.value)">{{ form.errors.value[0] }}</span>
                            <span v-else>{{ form.errors.value }}</span>
                        </div>
                    </div>
                    <CustomInput v-model="form.sort_order" name="sort_order" label="Sort order" type="number" placeholder="Enter sort order" :error="form.errors?.sort_order"/>
                    <div class="flex items-end pb-2"><Switch @change="(value) => form.status = value" :value="form.status" id="color_status" label="Active"/></div>
                </div>
                <hr class="text-gray mt-6.5">
                <div class="flex p-6.5 save-button-fixed"><CustomButton class="ml-auto flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80" type="submit"><font-awesome-icon :icon="['far', 'floppy-disk']"/>Save</CustomButton></div>
            </form>
        </div>
    </DefaultLayoutComponent>
</template>

<style scoped>
.product-color-value {
    width: 100%;
}
</style>
