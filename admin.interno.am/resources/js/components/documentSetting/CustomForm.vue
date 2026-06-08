<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import Switch from "@components/global/Switch.vue";
import TooltipOne from "@components/global/Tooltips/TooltipOne.vue";
import CustomPdfLive from "@components/global/CustomPdfLive.vue";

import {computed, ref, toRefs, watch,} from "vue";
import {useStore} from "vuex";
import {validate} from "@validation/customValidation.js";

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
])

watch(modelValue, (newVal) => {
    form.value = newVal;
});

watch(form.value, (newVal) => {
    emits('update:modelValue', newVal);
});

store.dispatch('documentSetting/fetchParams', {id: form.value.id});
const params = computed(() => store.getters['documentSetting/getParams']);
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
        <div class="grid grid-cols-5 gap-9">
            <div class="flex flex-col p-6.5">
                <CustomSelect
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

            <div v-if="form.language_id == -1">
                <div class="flex flex-col pt-5 pl-6.5 pr-6.5 pb-0 col-span-1">

                    <div v-if="form.id == 1 || form.id == 2" class="flex flex-col pb-6.5">
                        <CustomSelect
                            :invalid-feedback-place="false"
                            :disabled="!auth.user_group.permissions_by_name.document_settings_individual[0].can_edit"
                            label="Create automatically for"
                            placeholder="Choose statuses"
                            v-model="form.statuses"
                            mode="tags"
                            :options="params.orderStatusesParams"
                            :searchable="false"
                            :canClear="true"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                        />
                    </div>
                    <div class="flex flex-col pb-6.5">
                        <CustomSelect
                            v-if="form.id == 2"
                            :invalid-feedback-place="false"
                            :disabled="!auth.user_group.permissions_by_name.document_settings_individual[0].can_edit"
                            label="Display invoice date"
                            placeholder="Choose invoice date"
                            v-model="form.display_invoice_date"
                            mode="single"
                            :options="params.invoiceDate"
                            :searchable="false"
                            :canClear="false"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                        />
                    </div>
                    <div class="flex flex-col pb-6.5">
                        <Switch
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.document_settings_individual[0].can_edit"
                            @change="(value) => {
                           form.display_email_address = value;
                        }"
                            :value="form.display_email_address"
                            id="display_email_address"
                            label="Display email address"
                        />
                    </div>
                    <div class="flex flex-col pb-6.5">
                        <Switch
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.document_settings_individual[0].can_edit"
                            @change="(value) => {
                           form.display_phone_number = value;
                        }"
                            :value="form.display_phone_number"
                            id="display_phone_number"
                            label="Display phone number"
                        />
                    </div>
                    <div v-if="form.id == 3"  class="flex flex-col pb-6.5">
                        <Switch
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.document_settings_individual[0].can_edit"
                            @change="(value) => {
                           form.generate_on_new_order = value;
                        }"
                            :value="form.generate_on_new_order"
                            id="generate_on_new_order"
                            label="Generate on new order"
                        />
                    </div>
                    <div v-if="form.id == 1" class="flex flex-col pb-6.5">
                        <Switch
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.document_settings_individual[0].can_edit"
                            @change="(value) => {
                           form.generate_invoice_also_in_base_language = value;
                        }"
                            :value="form.generate_invoice_also_in_base_language"
                            id="generate_invoice_also_in_base_language"
                            label="Generate invoice also in base language"
                        />
                    </div>

                    <div v-if="form.id == 4" class="flex flex-col pb-6.5">
                        <Switch
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.document_settings_individual[0].can_edit"
                            @change="(value) => {
                           form.create_automatically_after_refunding = value;
                        }"
                            :value="form.create_automatically_after_refunding"
                            id="create_automatically_after_refunding"
                            label="Create automatically after refunding"
                        />
                    </div>
                    <div v-if="form.id == 4" class="flex flex-col pb-6.5">
                        <CustomSelect
                            :invalid-feedback-place="false"
                            :disabled="!auth.user_group.permissions_by_name.document_settings_individual[0].can_edit"
                            label="Display credit note date"
                            placeholder="Choose credit note date"
                            v-model="form.display_credit_note_date"
                            mode="single"
                            :options="params.creditNoteDate"
                            :searchable="false"
                            :canClear="false"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                        />
                    </div>
                    <div v-if="form.id != 1" class="flex flex-col pb-6.5">
                        <Switch
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.document_settings_individual[0].can_edit"
                            @change="(value) => {
                               form.show_original_invoice_number = value;
                            }"
                            :value="form.show_original_invoice_number"
                            id="show_original_invoice_number"
                            label="Show original invoice number"
                        />
                    </div>
                </div>
            </div>
            <div v-else class="grid grid-cols-1 gap-9">
                <div class="flex flex-col p-6.5 pb-0">
                    <div>
                        <CustomInput
                            v-model="form.document_title"
                            name="document_title"
                            label="Document title "
                            type="text"
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.document_settings_individual[0].can_edit"
                            placeholder="Enter document title"
                            @keyup="form.errors = validate(form)"
                            :error="form.errors['document_title']"
                        />
                        <CustomInput
                            v-model="form.text_below_title"
                            name="text_below_title"
                            label="Text below title "
                            type="text"
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.document_settings_individual[0].can_edit"
                            placeholder="Enter text below title"
                            @keyup="form.errors = validate(form)"
                            :error="form.errors['text_below_title']"
                        />
                        <div class="flex items-center">
                            <CustomInput
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.document_settings_individual[0].can_edit"
                                class="w-full mt-1 custom-tooltip"
                                v-model="form.number_format_prefix"
                                name="number_format_prefix"
                                label="Number format Prefix"
                                type="text"
                                placeholder="Enter number format Prefix"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['number_format_prefix']"
                                :tooltip="true"
                            />
                            <TooltipOne
                                :button-params="{showingType: 'info'}"
                                :tooltip-text="'If set, this value will be used as number prefix. <br> You can use the invoice year and/or month with the <br> <strong>[year]</strong>, <strong>[month]</strong> or <strong>[day]</strong> placeholders respectively.'"
                                tooltipClass="flex h-fit items-center gap-2 rounded-tr rounded-br bg-primary mt-auto mb-auto py-5 px-4.5 font-medium text-white hover:bg-opacity-80"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-2">
                <CustomPdfLive :form="form" />
            </div>
        </div>

        <hr class="text-gray mt-6.5">
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template
                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.document_settings_individual[0].can_edit">
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
