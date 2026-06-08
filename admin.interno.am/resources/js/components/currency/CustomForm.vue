<script setup>

import Switch from "@components/global/Switch.vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";

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

const auth = computed(() => store.getters['auth/getUser']);

</script>

<template>
    <form @submit.prevent="emits('submit')">
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 gap-9 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>
        <div class="grid grid-cols-2 gap-9">
            <div class="flex flex-col p-6.5">
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.currencies[0].can_edit"
                    v-model="form.name"
                    name="name"
                    label="Name *"
                    type="text"
                    placeholder="Enter name"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['name']"
                />
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.currencies[0].can_edit"
                    v-model="form.code"
                    name="code"
                    label="Code *"
                    type="text"
                    placeholder="Enter symbol"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['code']"
                />
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.currencies[0].can_edit"
                    v-if="!form.base"
                    v-model="form.rate"
                    name="rate"
                    label="Rate *"
                    type="text"
                    placeholder="Enter rate"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['rate']"
                />

            </div>
            <div class="flex flex-col p-6.5">
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.currencies[0].can_edit"
                    v-model="form.symbol"
                    name="symbol"
                    label="Symbol *"
                    type="text"
                    placeholder="Enter symbol"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['symbol']"
                />
                <CustomInput
                    :disabled="true"
                    v-model="form.gbp_rate"
                    name="gbp_rate"
                    label="GBP rate"
                    type="text"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['gbp_rate']"
                />
                <div class="flex gap-8">
                    <Switch
                        @change="(value) => {
                            form.base = value;
                        }"
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.currencies[0].can_edit"
                        :value="form.base"
                        id="base"
                        label="Base"
                    />
                </div>

            </div>
            <div class="flex flex-col p-6.5">

            </div>
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.tax[0].can_delete">
                        <CustomButton
                            v-if="emitAction === 'update'"
                            @click="store.commit('currency/SET_DELETE_MODAL_VALUE', {
                                    value: true,
                                    id: form.id
                                });"
                            class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'trash']" />  Delete
                        </CustomButton>
                    </template>

                    <template v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.currencies[0].can_edit">
                        <CustomButton
                            class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="submit"
                        >
                            <font-awesome-icon :icon="['far', 'floppy-disk']" /> Save
                        </CustomButton>
                    </template>
                </div>

            </div>
        </div>

    </form>
</template>

<style scoped>

</style>
