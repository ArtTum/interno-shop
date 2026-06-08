<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import Switch from "@components/global/Switch.vue";

import {computed, ref, toRefs, watch} from "vue";

import {useStore} from "vuex";

const store = useStore()

import {validate} from "@validation/customValidation.js";

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

const auth = computed(() => store.getters['auth/getUser']);

store.dispatch('shippingCountry/fetchParams', {});
const params = computed(() => store.getters['shippingCountry/getParams']);

</script>

<template>
    <form @submit.prevent="emits('submit')">
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 gap-9 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>
        <div class="grid grid-cols-3 gap-9">
            <div class="flex flex-col px-6.5">
                <div v-if="form.code">
                    <img
                        alt="icon"
                        width="50px"
                        class="mt-3"
                        :src="`/flags/${form.code.toLowerCase()}.svg`"
                    >
                </div>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-9">
            <div class="flex flex-col p-6.5">
                <CustomSelect
                    :searchable="true"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.store_countries[0].can_edit"
                    label="Base language"
                    v-model="form.language_id"
                    mode="single"
                    placeholder="Select language"
                    :options="params.languages"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    @update:modelValue="form.errors = validate(form)"
                />
            </div>
            <div class="flex flex-col p-6.5">
                <CustomInput
                    disabled
                    v-model="form.name"
                    name="name"
                    label="Name *"
                    type="text"
                    placeholder="Enter name"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['name']"
                />
            </div>
            <div class="flex flex-col p-6.5">
                <CustomInput
                    disabled
                    v-model="form.code"
                    name="code"
                    label="Code *"
                    type="text"
                    placeholder="Enter code"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['code']"
                />
            </div>
        </div>
        <div class="grid grid-cols-3 gap-9">
            <div class="flex flex-col p-6.5">
                <div class="flex items-center">
                    <div>
                        <CustomInput
                            v-model="form.delivery_days_from"
                            name="delivery_days_from"
                            label="Delivery days *"
                            type="number"
                            :min="1"
                            placeholder="From"
                            @keyup="form.errors = validate(form)"
                            :error="form.errors['delivery_days_from']"
                        />
                    </div>
                    <div class="px-4">
                        -
                    </div>
                    <div>
                        <CustomInput
                            class="pt-8.5"
                            v-model="form.delivery_days_to"
                            name="delivery_days_to"
                            label=""
                            type="number"
                            placeholder="To"
                            @keyup="form.errors = validate(form)"
                            :error="form.errors['delivery_days_to']"
                        />
                    </div>
                </div>
            </div>
            <div class="flex flex-col p-6.5">
                <Switch
                    @change="(value) => {
                       form.state_required = value;
                    }"
                    :disabled="!auth.user_group.permissions_by_name.items[0].can_edit"
                    :value="form.state_required"
                    id="state_required"
                    label="State is required *"
                />
            </div>
        </div>
        <template v-if="auth.user_group.permissions_by_name.store_countries[0].can_edit">
            <div class="grid grid-cols-1 gap-9">
                <div class="flex flex-col p-6.5 save-button-fixed">
                    <div class="flex ml-auto gap-5">

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
        </template>
    </form>
</template>

<style scoped>

</style>
