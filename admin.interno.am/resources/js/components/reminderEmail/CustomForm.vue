<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

import {computed, ref, toRefs, watch,} from "vue";
import {useStore} from "vuex";
import {validate} from "@validation/customValidation.js";
import CustomTextarea from "@components/global/CustomTextarea.vue";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";
import Switch from "@components/global/Switch.vue";

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

store.dispatch('reminderEmail/fetchParams', {id: form.value.id});
const params = computed(() => store.getters['reminderEmail/getParams']);
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
            <div class="flex flex-col p-6.5 col-span-3">
                <CustomInput
                    v-if="form.language_id == -1"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.reminder_emails[0].can_edit"
                    v-model="form.name"
                    label="Name *"
                    name="name"
                    type="text"
                    placeholder="Enter name"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['name']"
                />
            </div>
            <div class="flex flex-col p-6.5 col-span-1">
                <Switch
                    @change="(value) => {
                                    form.newsletter_related = value;
                                }"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.reminder_emails[0].can_edit"
                    :value="form.newsletter_related"
                    id="newsletter_related"
                    label="Newsletter"
                />
            </div>
        </div>
        <hr class="text-gray">
        <template v-if="form.language_id == -1">
            <div class="grid grid-cols-7 gap-9">
                <div class="flex flex-col px-6.5 col-span-12">
                    <label class="my-2.5 block font-medium text-black">When to send</label>
                    <div class="grid grid-cols-7 gap-9">
                        <div class="flex flex-col pb-0 col-span-1">
                            <CustomInput
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.reminder_emails[0].can_edit"
                                v-model="form.count"
                                name="count"
                                type="number"
                                placeholder="Enter count"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['count']"
                            />
                        </div>
                        <div class="flex flex-col pb-0 col-span-1">
                            <CustomSelect
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.reminder_emails[0].can_edit"
                                v-model="form.time_unit"
                                mode="single"
                                :canClear="false"
                                placeholder="Select"
                                :options="params.timeUnits"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                                @update:modelValue="form.errors = validate(form)"
                                :error="form.errors['time_unit']"
                            />
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-7 gap-9 border-t border-stroke">
                <div class="flex flex-col px-6.5 col-span-12 mt-5">
                    <label class="mb-2.5 block font-medium text-black">Order requirements</label>
                    <div class="grid grid-cols-7 gap-9">
                        <div class="flex flex-col pb-0 col-span-2">
                            <CustomSelect
                                class="py-2 rounded-lg  border-stroke bg-transparent"
                                v-model="form.payment_method"
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.reminder_emails[0].can_edit"
                                @update:modelValue="form.errors = validate(form)"
                                mode="tags"
                                label="Payment Method"
                                placeholder="All"
                                :options="params.paymentMethods"
                                :show-labels="true"
                                :close-on-select="false"
                                :canClear="false"
                                :error="form.errors['payment_method'] ?? null"
                            />
                        </div>
                        <div class="flex flex-col pb-0 col-span-2">
                            <CustomSelect
                                class="py-2 rounded-lg  border-stroke bg-transparent"
                                v-model="form.order_status"
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.reminder_emails[0].can_edit"
                                @update:modelValue="form.errors = validate(form)"
                                mode="tags"
                                label="Order status"
                                placeholder="All"
                                :options="params.statuses"
                                :show-labels="true"
                                :close-on-select="false"
                                :canClear="false"
                                :error="form.errors['order_status'] ?? null"
                            />
                        </div>
                        <div class="flex flex-col pb-0 col-span-2">
                            <CustomSelect
                                class="py-2 rounded-lg  border-stroke bg-transparent"
                                v-model="form.shipping_method"
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.reminder_emails[0].can_edit"
                                @update:modelValue="form.errors = validate(form)"
                                mode="tags"
                                label="Shipping method"
                                placeholder="All"
                                :options="params.carriers"
                                :show-labels="true"
                                :close-on-select="false"
                                :canClear="false"
                                :error="form.errors['shipping_method'] ?? null"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <template v-else>
            <div>
                <div class="grid grid-cols-5 gap-9">
                    <div class="flex flex-col p-6.5 pb-0 col-span-4 justify-center">
                        <div>
                            <CustomInput
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.reminder_emails[0].can_edit"
                                v-model="form.title"
                                name="title"
                                label="Title"
                                type="text"
                                placeholder="Enter title"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['title']"
                            />
                            <CustomInput
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.reminder_emails[0].can_edit"
                                v-model="form.subject"
                                name="subject"
                                label="Subject"
                                type="text"
                                placeholder="Enter subject"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['subject']"
                            />
                        </div>
                    </div>
                    <div class="flex flex-col p-6.5 pb-0 col-span-1 w-fit">
                        <ul>
                            <li class="border-b border-stroke font-semibold text-black">Content variables</li>
                            <li>{trustpilot_url}</li>
                            <li>{products_table}</li>
                            <li>{bank_account_details}</li>
                            <li>{billing_first_name}</li>
                            <li>{billing_last_name}</li>
                            <li>{order_number}</li>
                            <li>{order_date}</li>
                            <li>{order_total}</li>
                            <li>{payment_method}</li>
                            <li>{shipping_method}</li>
                            <li>{site_title}</li>
                            <li>{site_url}</li>
                        </ul>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-9">
                    <div class="flex flex-col p-6.5 pb-0">
                        <div class="mb-5">
                            <label class="mb-2.5 mt-2.5 block font-medium text-black">Top text</label>
                            <CKEditorComponent
                                :model="form.top_text"
                                @updateValue="(value) => {
                                        form.top_text = value
                                    }"
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.reminder_emails[0].can_edit"
                            />
                        </div>
                        <div class="mb-5">
                            <label class="mb-2.5 mt-2.5 block font-medium text-black">Bottom text</label>
                            <CKEditorComponent
                                :model="form.bottom_text"
                                @updateValue="(value) => {
                                        form.bottom_text = value
                                    }"
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.reminder_emails[0].can_edit"
                            />
                        </div>
                        <div class="mb-5">
                            <label class="mb-2.5 mt-2.5 block font-medium text-black">Footer text</label>
                            <CKEditorComponent
                                :model="form.footer_text"
                                @updateValue="(value) => {
                                        form.footer_text = value
                                    }"
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.reminder_emails[0].can_edit"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <hr class="text-gray mt-6.5">
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.attribute_types[0].can_delete">
                        <CustomButton
                            v-if="emitAction === 'update' && (form.language_id == -1 || form.translation_id)"
                            @click="store.commit('attributeType/SET_DELETE_MODAL_VALUE', {
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
                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.attribute_types[0].can_edit">
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
