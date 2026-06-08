<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import {validate} from "@validation/customValidation.js";
import CustomTextarea from "@components/global/CustomTextarea.vue";

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

const params = computed(() => store.getters['zipRule/getParams']);
const auth = computed(() => store.getters['auth/getUser']);

</script>

<template>
    <div
        class="bg-amber-200 px-6.5 py-2.5 mt-1"
    >
        Leave empty to disable shipping for entered ZIP codes
    </div>
    <form @submit.prevent="emits('submit')">
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>
        <div class="grid grid-cols-5 ">
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
        <template v-if="form.language_id == -1">
            <div class="grid grid-cols-3">
                <div class="flex flex-col p-6.5 pb-0">
                    <CustomSelect
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.zip_rules[0].can_edit"
                        label="Country *"
                        v-model="form.country_id"
                        mode="single"
                        placeholder="Select country"
                        :options="params.countries"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                        @update:modelValue="form.errors = validate(form)"
                        :error="form.errors['country_id']"
                    />
                </div>
                <div class="flex flex-col p-6.5 pb-0">
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.zip_rules[0].can_edit"
                        v-model="form.fee"
                        name="fee"
                        label="Fee"
                        type="text"
                        placeholder="Enter fee"
                        @update:modelValue="form.errors = validate(form)"
                        :error="form.errors['fee']"
                    />
                </div>
                <div class="flex flex-col p-6.5 pb-0">
                    <CustomTextarea
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.zip_rules[0].can_edit"
                        label="ZIPs *"
                        name="zips"
                        :rows="4"
                        v-model="form.zips"
                        placeholder="ZIPs"
                        @update:modelValue="form.errors = validate(form)"
                        :error="form.errors['zips']"
                    />
                </div>
            </div>
        </template>
        <template v-else>
            <div class="grid grid-cols-2 gap-9">
                <div class="flex flex-col p-6.5 pb-0">
                    <div>
                        <CustomInput
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.zip_rules[0].can_edit"
                            v-model="form.fee_label"
                            name="fee_label"
                            label="Fee label *"
                            type="text"
                            placeholder="Enter fee label"
                            @keyup="form.errors = validate(form)"
                            :error="form.errors['fee_label']"
                        />
                    </div>
                </div>
            </div>
        </template>
        <hr class="text-gray mt-6.5">
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.zip_rules[0].can_delete">
                        <CustomButton
                            v-if="emitAction === 'update' && (form.language_id == -1 || form.translation_id)"
                            @click="store.commit('zipRule/SET_DELETE_MODAL_VALUE', {
                                    value: true,
                                    id: form.language_id == -1 ? form.id : form.translation_id,
                                    deletingActionApi: form.language_id == -1 ? 'delete' : 'delete-translation',
                                    deletingText: form.language_id == -1 ? null : 'Deleting this translation will be permanent and cannot be undone. Once deleted, you will not be able to restore it.',
                                });"
                            class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'trash']"/>
                            Delete
                        </CustomButton>
                    </template>

                    <template
                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.zip_rules[0].can_edit">
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
