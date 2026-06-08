<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";
import TooltipOne from "@components/global/Tooltips/TooltipOne.vue";

import {validate} from "@validation/customValidation.js";
import {computed, ref, toRefs, watch} from "vue";
import {useStore} from "vuex";

const store = useStore()
const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    emitAction: {
        type: String
    },
});

const {modelValue} = toRefs(props);
const form = ref(modelValue.value);

const emits = defineEmits([
    'update:modelValue',
    'submit'
])

watch(modelValue, (newVal) => {
    form.value = newVal;
});

watch(form.value, (newVal) => {
    emits('update:modelValue', newVal);
});

const params = computed(() => store.getters['calculator/getParams']);
const auth = computed(() => store.getters['auth/getUser']);

const newField = ref(null);

</script>

<template>
    <form @submit.prevent="emits('submit')">
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 gap-9 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>
        <div class="grid grid-cols-5 gap-9">
            <div class="flex flex-col p-6.5">
                <CustomSelect
                    :check-exists-in-array="params.parentLanguageIds"
                    label="Languages"
                    v-model="form.language_id"
                    mode="single"
                    placeholder="Select languages"
                    :disabled="emitAction === 'create'"
                    :options="params.languages"
                    :searchable="true"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['language_id']"
                />
            </div>
        </div>
        <hr class="text-gray">
        <div class="grid grid-cols-3 gap-9">
            <div class="flex flex-col p-6.5 pb-0">
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.calculators[0].can_edit"
                    v-model="form.name"
                    name="name"
                    label="Name *"
                    type="text"
                    placeholder="Enter name"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['name']"
                />
            </div>
            <div class="flex flex-col p-6.5 pb-0">
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.calculators[0].can_edit"
                    v-model="form.coefficient"
                    name="coefficient"
                    label="Coefficient * (key for formula is <strong>coefficient</strong>)"
                    type="text"
                    placeholder="Enter coefficient"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['coefficient']"
                />
            </div>
            <div class="flex flex-col p-6.5 pb-0">
                <CustomSelect
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.calculators[0].can_edit"
                    label="Unit *"
                    v-model="form.unit"
                    mode="single"
                    placeholder="Select"
                    :options="params.units"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['unit']"
                />
            </div>
        </div>

        <div class="grid grid-cols-2 gap-9">
            <div class="flex p-6.5 w-full">
                <div class="w-full">
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.calculators[0].can_edit"
                        class="custom-tooltip mt-1"
                        :tooltip="true"
                        v-model="form.formula"
                        name="formula"
                        label="Formula"
                        type="text"
                        placeholder="Enter formula"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['formula']"
                    />
                </div>
                <div class="mt-auto mb-auto">
                    <TooltipOne
                        tooltipClass="rounded-tr rounded-br bg-primary py-5.5 px-3.5"
                        :button-params="{showingType: 'info'}"
                        :tooltip-text="'Example: field_key_1 * field_key_2 / field_key_3 + field_key_4 - field_key_5'"
                    />
                </div>
            </div>
            <div class="flex flex-col p-6.5 pb-0">
                <label class="mb-2.5 block font-medium text-black">Short description</label>
                <CKEditorComponent
                    :model="form.short_description"
                    @updateValue="(value) => {
                       form.short_description = value
                    }"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.calculators[0].can_edit"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 mt-5">
            <div class="flex p-6.5">
                <h3 class="text-black-2 font-bold text-2xl">Dimensions Fields</h3>
            </div>
        </div>

        <template
            v-for="(field, fieldKey) in form.fields"
            :key="fieldKey"
        >
            <div
                class="grid grid-cols-7 mt-5"
            >
                <div class="flex flex-col p-6.5 pb-0 col-span-2">
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.calculators[0].can_edit"
                        v-model="field.key"
                        name="key"
                        label="Key"
                        type="text"
                        placeholder="Enter key"
                        :tooltip="true"
                    />
                </div>
                <div class="flex flex-col p-6.5 pb-0 col-span-2">
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.calculators[0].can_edit"
                        v-model="field.label"
                        name="label"
                        label="Label"
                        type="text"
                        placeholder="Enter label"
                        :tooltip="true"
                    />
                </div>
                <div class="flex flex-col p-6.5 pb-0 col-span-2">
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.calculators[0].can_edit"
                        v-model="field.default_value"
                        name="default_value"
                        label="Default value"
                        type="text"
                        placeholder="Enter default value"
                        :tooltip="true"
                    />
                </div>
                <div class="flex p-6.5 pb-0 items-center justify-end col-span-1">
                    <CustomButton
                        @click="form.fields.splice(fieldKey, 1);"
                        class="h-[45px] max-w-[120px] flex items-center gap-2 ml-3 rounded bg-danger px-3.5 font-medium text-white hover:bg-opacity-80"
                        type="button"
                    >
                        <font-awesome-icon :icon="['far', 'trash']"/>
                        Delete
                    </CustomButton>
                </div>
            </div>
        </template>

        <template v-if="newField">
            <div class="grid grid-cols-7">
                <div class="flex flex-col p-6.5 pb-0 col-span-2">
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.calculators[0].can_edit"
                        v-model="newField.key"
                        name="key"
                        label="Key"
                        type="text"
                        placeholder="Enter key"
                        :tooltip="true"
                    />
                </div>
                <div class="flex flex-col p-6.5 pb-0 col-span-2">
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.calculators[0].can_edit"
                        v-model="newField.label"
                        name="label"
                        label="Label"
                        type="text"
                        placeholder="Enter label"
                        :tooltip="true"
                    />
                </div>
                <div class="flex flex-col p-6.5 pb-0 col-span-2">
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.calculators[0].can_edit"
                        v-model="newField.default_value"
                        name="default_value"
                        label="Default value"
                        type="text"
                        placeholder="Enter default value"
                        :tooltip="true"
                    />
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1">
            <template v-if="!newField">
                <div class="flex p-6.5">
                    <CustomButton
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.calculators[0].can_edit"
                        @click="newField = {key: null, label: null, default_value: null}"
                        title="Add new field"
                        type="button"
                        class="h-[45px] flex items-center gap-2 rounded bg-meta-3 px-3.5 mt-5 font-medium text-white hover:bg-opacity-80"
                    >
                        <font-awesome-icon :icon="'plus'" class="size-5"/>
                        New field
                    </CustomButton>
                </div>
            </template>
            <template v-else>
                <div class="flex p-6.5">
                    <CustomButton
                        @click="newField = null"
                        class="h-[45px] max-w-[120px] block w-full rounded border border-stroke bg-gray px-3.5 font-medium text-black hover:bg-opacity-60"
                        type="button"
                    >
                        <font-awesome-icon :icon="['far', 'arrow-rotate-left']"/>
                        Cancel
                    </CustomButton>

                    <template v-if="!newField.key || !newField.label">
                        <CustomButton
                            disabled
                            class="h-[45px] max-w-[120px] flex items-center gap-2 ml-3 rounded bg-primary px-3.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                            Save
                        </CustomButton>
                    </template>
                    <template v-else>
                        <CustomButton
                            @click="() => {
                                form.fields.push(newField);
                                newField = null;
                            }"
                            class="h-[45px] max-w-[120px] flex items-center gap-2 ml-3 rounded bg-primary px-3.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                            Save
                        </CustomButton>
                    </template>
                </div>
            </template>
        </div>
        <hr class="text-gray mt-6.5">
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.calculators[0].can_delete">
                        <CustomButton
                            v-if="emitAction === 'update' && form.translation_id"
                            @click="store.commit('category/SET_DELETE_MODAL_VALUE', {
                                    value: true,
                                    id: form.translation_id,
                                    deletingActionApi: 'delete-translation',
                                    deletingText: 'Deleting this translation will be permanent and cannot be undone. Once deleted, you will not be able to restore it.',
                                });"
                            class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'trash']"/>
                            Delete
                        </CustomButton>
                    </template>

                    <template
                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.calculators[0].can_edit">
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
