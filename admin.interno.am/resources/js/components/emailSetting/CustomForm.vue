<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import CustomTextarea from "@components/global/CustomTextarea.vue";

import {validate} from "@validation/customValidation.js";

import {computed, ref, toRefs, watch} from "vue";
import {useStore} from "vuex";
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

store.dispatch('emailSetting/fetchParams', {id: form.value.id});
const params = computed(() => store.getters['emailSetting/getParams']);

const auth = computed(() => store.getters['auth/getUser']);
const attachLabel = {
    1: 'Attach on hold document',
    2: 'Attach invoice document',
    3: 'Attach packing list document',
    4: 'Attach credit note document'
};

const isAiTranslateExpanded = ref(false);
const toggleAiTranslate = () => {
    isAiTranslateExpanded.value = !isAiTranslateExpanded.value;
};

const selectedLanguagesForTranslation = ref([]);
const generateAITranslations = async (isAll = false) => {
    await store.dispatch('emailSetting/translateAI', {
        translation_id: form.value.translation_id,
        language_ids: isAll ? [] : selectedLanguagesForTranslation.value,
    })

    if (!isAll) selectedLanguagesForTranslation.value = [];

    store.commit('notification/SET_NOTIFICATION', {
        visible: true,
        title: 'Success',
        message: `Successfully submitted (Will work in background)`
    });
}

const approveTranslation = async () => {
    await store.dispatch('emailSetting/approveTranslation', {
        translation_id: form.value.translation_id,
    })
    form.value.approved = true;

    store.commit('notification/SET_NOTIFICATION', {
        visible: true,
        title: 'Success',
        message: `Successfully approved`
    });
}
</script>

<template>
    <form @submit.prevent="emits('submit')">
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 gap-9 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>
        <div class="grid grid-cols-5 gap-9 px-6.5 py-4">
            <div class="">
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
        <div class="-mt-6 px-6.5 py-4"
             v-if="form.translation_id && emitAction === 'update' && auth.user_group.permissions_by_name.email_settings[0].can_edit">
            <div>
                <CustomButton
                    :disabled="!auth.user_group.permissions_by_name.attributes[0].can_edit"
                    @click="toggleAiTranslate"
                    type="button"
                    class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                >
                    <font-awesome-icon :icon="['fasr', 'robot']"/>
                    Translate with AI
                </CustomButton>
            </div>
            <div class="mt-4" :class="{ 'hidden': !isAiTranslateExpanded }">
                <template v-if="form.approved">
                    <h2 class="text-black mt-2 font-bold">You can generate AI translations from this language to the
                        missing ones.</h2>
                    <div class="flex mt-2">
                        <div>
                            <CustomButton
                                type="button"
                                @click="generateAITranslations(true)"
                                class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            >
                                <font-awesome-icon :icon="['fas', 'robot']"/>
                                Generate AI translations (all missing)
                            </CustomButton>
                        </div>
                        <div class="ml-2">
                            <CustomButton
                                :title="!selectedLanguagesForTranslation.length ? 'First select languages' : ''"
                                :disabled="!selectedLanguagesForTranslation.length"
                                type="button"
                                @click="generateAITranslations()"
                                class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            >
                                <font-awesome-icon :icon="['fas', 'robot']"/>
                                Generate AI translations for selected languages
                            </CustomButton>
                        </div>
                        <div class="ml-2 min-w-[300px]">
                            <CustomSelect
                                v-model="selectedLanguagesForTranslation"
                                mode="tags"
                                placeholder="Select languages"
                                :options="params.languages"
                                :invalid-feedback-place="false"
                                :close-on-select="false"
                                :searchable="true"
                                :with-general="false"
                                class="py-2 rounded-lg border-stroke bg-transparent w-full"
                            />
                        </div>
                    </div>
                </template>
                <template v-else>
                    <h2 class="text-black mt-2 font-bold">This translation was generated by AI. Please approve it if
                        everything looks correct.</h2>
                    <div class="flex mt-2">
                        <div>
                            <CustomButton
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                type="button"
                                @click="approveTranslation()"
                                class="flex items-center gap-2 rounded bg-warning py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            >
                                <font-awesome-icon :icon="['fas', 'robot']"/>
                                Approve translation
                            </CustomButton>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <hr class="text-gray">
        <div>
            <div class="grid grid-cols-5 gap-9">
                <div class="flex flex-col p-6.5 pb-0 col-span-4 justify-center">
                    <div>
                        <CustomInput
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.email_settings[0].can_edit"
                            v-model="form.title"
                            name="title"
                            label="Title"
                            type="text"
                            placeholder="Enter title"
                            @keyup="form.errors = validate(form)"
                            :error="form.errors['title']"
                        />
                        <CustomInput
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.email_settings[0].can_edit"
                            v-model="form.subject"
                            name="subject"
                            label="Subject"
                            type="text"
                            placeholder="Enter subject"
                            @keyup="form.errors = validate(form)"
                            :error="form.errors['subject']"
                        />
                        <div v-if="form.id === 4">
                            <CustomTextarea
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.email_settings[0].can_edit"
                                v-model="form.admin_receiver_email_address"
                                name="admin_receiver_email_address"
                                label="Admin receiver email address"
                                type="text"
                                placeholder="Enter admin receiver email address"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['admin_receiver_email_address']"
                            />
                        </div>
                        <div v-if="form.id === 1 || form.id === 3 || form.id === 2 || form.id === 5">

                            <Switch
                                @change="(value) => {
                                        form.attach_document = value;
                                    }"
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.email_settings[0].can_edit"
                                class="w-fit"
                                :value="form.attach_document"
                                id="attach_document"
                                :label="attachLabel[form.id] || 'Attach credit note document'"
                            />

                        </div>
                    </div>
                </div>
                <div class="flex flex-col p-6.5 pb-0 col-span-1 w-fit">
                    <ul v-if="form.id == 11 || form.id == 12">
                        <li class="border-b border-stroke font-semibold text-black">Content variables</li>
                        <li>{offer_link}</li>
                        <li>{user_first_name}</li>
                        <li>{user_last_name}</li>
                        <li>{site_title}</li>
                        <li>{site_url}</li>
                    </ul>
                    <ul v-else>
                        <li>{products_table}</li>
                        <li>{addresses_table}</li>
                        <li>{bank_account_details}</li>
                        <li>{tracking_number}</li>
                        <li>{billing_first_name}</li>
                        <li>{billing_last_name}</li>
                        <li>{order_number}</li>
                        <li>{order_date}</li>
                        <li>{order_total}</li>
                        <li>{payment_method}</li>
                        <li>{shipping_method}</li>
                        <li>{password_reset_link}</li>
                        <li>{user_first_name}</li>
                        <li>{user_last_name}</li>
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
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.email_settings[0].can_edit"
                        />
                    </div>
                    <div class="mb-5" v-if="form.id != 14">
                        <label class="mb-2.5 mt-2.5 block font-medium text-black">Bottom text</label>
                        <CKEditorComponent
                            :model="form.bottom_text"
                            @updateValue="(value) => {
                                        form.bottom_text = value
                                    }"
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.email_settings[0].can_edit"
                        />
                    </div>
                    <div class="mb-5">
                        <label class="mb-2.5 mt-2.5 block font-medium text-black">Footer text</label>
                        <CKEditorComponent
                            :model="form.footer_text"
                            @updateValue="(value) => {
                                        form.footer_text = value
                                    }"
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.email_settings[0].can_edit"
                        />
                    </div>
                </div>
            </div>
        </div>
        <hr class="text-gray mt-6.5">
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template
                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.email_settings[0].can_edit">
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
