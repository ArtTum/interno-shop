<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import Switch from "@components/global/Switch.vue";

import {computed, ref, toRefs, watch} from "vue";
import {validate} from "@validation/customValidation.js";
import {useStore} from "vuex";

const store = useStore()
const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    emitAction: {
        type: String
    }
});

const {modelValue} = toRefs(props);
const form = ref(modelValue.value);

const emits = defineEmits([
    'update:modelValue',
    'submit'
]);

watch(modelValue, (newVal) => {
    form.value = newVal;
});

watch(form.value, (newVal) => {
    emits('update:modelValue', newVal);
});

store.dispatch('vendor/fetchParams', {id: form.value.id});
const params = computed(() => store.getters['vendor/getParams']);

const b2bOptions = [
    {value: 0, label: 'B2C'},
    {value: 1, label: 'B2B'},
    {value: 2, label: 'B2C & B2B'},
];

</script>

<template>
    <form @submit.prevent="emits('submit')" class="mb-5">
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 gap-9 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>
        <div
            class="grid grid-cols-3 gap-6  p-6 max-md:grid-cols-2 max-md:gap-4 max-md:p-4 max-sm:grid-cols-1">
            <div class="flex flex-col  pb-0">
                <CustomInput
                    :disabled="emitAction === 'update'"
                    v-model="form.name"
                    name="name"
                    label="Name *"
                    type="text"
                    placeholder="Enter name"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['name']"
                />
            </div>
            <div class="flex flex-col  pb-0">
                <CustomInput
                    v-model="form.db_server_ip"
                    name="db_server_ip"
                    label="DataBase IP *"
                    type="text"
                    placeholder="Enter IP"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['db_server_ip']"
                />
            </div>
            <div class="flex flex-col  pb-0">
                <CustomInput
                    v-model="form.domain"
                    name="domain"
                    label="Domain *"
                    type="text"
                    placeholder="Domain"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['domain']"
                />
            </div>
            <div class="flex flex-col  pt-0" v-if="emitAction === 'update'">
                <CustomSelect
                    label="Status"
                    v-model="form.status"
                    mode="single"
                    placeholder="Select status"
                    :options="params.statusArray"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                />
            </div>
            <div class="flex flex-col col-span-2  pt-0 max-sm:col-span-1">
                <CustomSelect
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    v-model="form.checkout_country_ids"
                    @update:modelValue="form.errors = validate(form)"
                    mode="tags"
                    label="Shipping countries *"
                    placeholder="Select countries"
                    :searchable="true"
                    :options="params.checkout_countries"
                    :show-labels="true"
                    :close-on-select="false"
                    :canClear="false"
                    :error="form.errors['checkout_country_ids'] ?? null"
                />
            </div>
        </div>
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <CustomButton
                        v-if="emitAction === 'update'"
                        @click="store.commit('vendor/SET_DELETE_MODAL_VALUE', {
                                    value: true,
                                    id: form.id
                                });"
                        class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                        type="button"
                    >
                        <font-awesome-icon :icon="['far', 'trash']"/>
                        Delete
                    </CustomButton>

                    <CustomButton
                        class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                        type="submit"
                    >
                        <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                        Save
                    </CustomButton>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1">
            <div class="flex flex-col p-6">
                <span class="text-title-xl2 text-black font-bold">Options</span>
            </div>
        </div>
        <div
            class="grid grid-cols-5 gap-6 p-6 max-xl:grid-cols-4 max-md:grid-cols-2 max-md:gap-5 max-md:p-4 max-sm:grid-cols-1">
            <div class="flex flex-col pt-0">
                <CustomSelect
                    v-model="form.b2b"
                    mode="single"
                    label="Activity"
                    :options="b2bOptions"
                    :searchable="false"
                    :canClear="false"
                />
            </div>
            <div class="flex flex-col pt-0">
                <CustomSelect
                    v-model="form.shipping_and_labels"
                    mode="tags"
                    label="All shipping methods including ..."
                    placeholder="Select shipping"
                    :searchable="true"
                    :options="params.shippingLabels"
                    :show-labels="true"
                    :close-on-select="false"
                    :canClear="false"
                />
            </div>
            <div class="flex flex-col pt-0">
                <CustomSelect
                    v-model="form.marketplaces"
                    mode="tags"
                    label="Marketplaces"
                    placeholder="Select shipping"
                    :searchable="true"
                    :options="params.marketplaces"
                    :show-labels="true"
                    :close-on-select="false"
                    :canClear="false"
                />
            </div>
            <div class="flex flex-col pt-0">
                <Switch
                    @change="(value) => {
                            form.dgd = value;
                        }"
                    :value="form.dgd"
                    id="dgd"
                    label="DGD (hazardous goods documentation)"
                />
            </div>
            <div class="flex flex-col pt-0">
                <Switch
                    @change="(value) => {
                            form.leads = value;
                        }"
                    :value="form.leads"
                    id="leads"
                    label="Leads including all related functionalities"
                />
            </div>
            <div class="flex flex-col pt-0">
                <Switch
                    @change="(value) => {
                            form.loyalty_programs = value;
                        }"
                    :value="form.loyalty_programs"
                    id="loyalty_programs"
                    label="Loyalty programs"
                />
            </div>
            <div class="flex flex-col pt-0">
                <Switch
                    @change="(value) => {
                            form.accounting_features = value;
                        }"
                    :value="form.accounting_features"
                    id="accounting_features"
                    label="All accounting features"
                />
            </div>

            <div class="flex flex-col pt-0">
                <Switch
                    @change="(value) => {
                            form.abandoned_cart_emails = value;
                        }"
                    :value="form.abandoned_cart_emails"
                    id="abandoned_cart_emails"
                    label="Abandoned cart emails"
                />
            </div>
            <div class="flex flex-col pt-0">
                <Switch
                    @change="(value) => {
                            form.newsletter_system = value;
                        }"
                    :value="form.newsletter_system"
                    id="newsletter_system"
                    label="Newsletter system"
                />
            </div>
            <div class="flex flex-col pt-0">
                <Switch
                    @change="(value) => {
                            form.cookie_management = value;
                        }"
                    :value="form.cookie_management"
                    id="cookie_management"
                    label="Cookie management"
                />
            </div>

        </div>
    </form>
</template>

<style scoped>

</style>

