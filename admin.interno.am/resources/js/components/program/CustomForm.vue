<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import Switch from "@components/global/Switch.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

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

const saleTypes = ref([
    {label: 'Percentage (%)', value: 1},
    {label: 'Fixed amount', value: 0},
])
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
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.programs[0].can_edit"
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
                <Switch
                    @change="(value) => {
                           form.status = value;
                    }"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.programs[0].can_edit"
                    :value="form.status"
                    id="status"
                    label="Status"
                    :colorDanger="true"
                />
            </div>
        </div>
        <div class="grid grid-cols-2 gap-9">
            <div class="flex flex-col p-6.5 m-6.5 border border-stroke rounded">
                <div class="flex text-xl font-bold text-black border-b border-stroke pb-4 justify-center">
                    <div>
                        Sale settings
                    </div>
                    <div class="ml-5">
                        <Switch
                            @change="(value) => {
                              form.sale_status = value;
                             }"
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.programs[0].can_edit"
                            :value="form.sale_status"
                            id="sale_status"
                            label=""
                            :colorDanger="true"
                        />
                    </div>
                </div>
                <div class="mt-6.5">
                    <CustomSelect
                        :disabled="(emitAction === 'update' && !auth.user_group.permissions_by_name.programs[0].can_edit) || !form.sale_status"
                        label="Type *"
                        placeholder="Choose type"
                        v-model="form.sale_type"
                        mode="single"
                        :options="saleTypes"
                        :searchable="false"
                        :canClear="false"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                    />
                </div>
                <div class="mt-3">
                    <CustomInput
                        v-model="form.sale_amount"
                        name="sale_amount"
                        label="Amount *"
                        type="text"
                        :disabled="(emitAction === 'update' && !auth.user_group.permissions_by_name.programs[0].can_edit) || !form.sale_status"
                        placeholder="Enter amount"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['sale_amount']"
                    />
                </div>
            </div>
            <div class="flex flex-col p-6.5 m-6.5 border border-stroke rounded">
                <div class="flex text-xl font-bold text-black border-b border-stroke pb-4 justify-center">
                    <div>
                        Click settings
                    </div>
                    <div class="ml-5">
                        <Switch
                            @change="(value) => {
                              form.click_status = value;
                             }"
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.programs[0].can_edit"
                            :value="form.click_status"
                            id="click_status"
                            label=""
                            :colorDanger="true"
                        />
                    </div>
                </div>
                <div class="mt-6.5">
                    <CustomInput
                        v-model="form.click_amount"
                        name="click_amount"
                        label="Amount *"
                        type="text"
                        :disabled="(emitAction === 'update' && !auth.user_group.permissions_by_name.programs[0].can_edit) || !form.click_status"
                        placeholder="Enter amount"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['click_amount']"
                    />
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.programs[0].can_delete">
                        <CustomButton
                            @click="store.commit('program/SET_DELETE_MODAL_VALUE', {
                                    value: true,
                                    id: form.id
                                });"
                            class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'trash']"/>
                            Delete
                        </CustomButton>
                    </template>

                    <template
                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.programs[0].can_edit">
                        <CustomButton
                            class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="submit"
                        >
                            <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                            Save
                        </CustomButton>
                    </template>
                </div>

            </div>
        </div>
    </form>
</template>

<style scoped>

</style>
