<script setup>

import Switch from "@components/global/Switch.vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

import {computed, ref, toRefs, watch} from "vue";
import {validate} from "@validation/customValidation.js";
import {useStore} from "vuex";
import CustomMediaList from "@components/media/CustomMediaList.vue";
import CustomTextarea from "@components/global/CustomTextarea.vue";

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

store.dispatch('shippingLabelSetting/fetchParams', {id: form.value.id});
const params = computed(() => store.getters['shippingLabelSetting/getParams']);
const auth = computed(() => store.getters['auth/getUser']);
const addNewSection = () => {
    form.value.rules.push({});
}

const removeRule = (key) => {
    form.value.rules.splice(key, 1);
}

const removeSingleGallery = () => {
    form.value.media_id = null;
    form.value.media = [];
}

const mediaData = (media) => {
    return {
        id: '',
        media_id: media.id,
        product_id: form.value.id,
        path: media.original_path,
        type: media.type,
        file_type: media.file_type,
        video_type: '',
        video_url: '',
    };
}

const media = ref(form.value.media);

const insert = (data) => {
    data.media.forEach(mediaItem => {
        if (mediaItem.id) {
            form.value.media_id = mediaItem.id;
            const updatedMedia = mediaData(mediaItem);
            media.value = [updatedMedia];
        }
    });
};

const sendInvoicesByEmail = async () => {
    try {
        const userConfirmed = confirm("Are you sure?");
        if (userConfirmed) {
            const response = await store.dispatch('order/sendInvoicesByEmails');
            if (response.success) {
                store.dispatch('shippingLabelSetting/fetchParams', {id: form.value.id});
                store.commit('notification/SET_NOTIFICATION', {
                    visible: true,
                    title: 'Success',
                    message: 'Successfully send'
                });
            }
        }
    } catch (error) {
        form.value.errors = error;
    }
};


const filteredCountries = computed(() => {
    const selectedCountries = form.value.rules.map(rule => rule.country).filter(Boolean);
    return params.value.customCountries.map(country => ({
        ...country,
        disabled: selectedCountries.includes(country.value),
    }));
});

const handleCountryChange = () => {
    form.value.errors = validate(form.value);
};

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
                <div v-if="form.id === 4">
                    <div class="flex border-b border-stroke pb-4 mb-4 items-center">
                        <CustomButton
                            :disabled="(params.shippingLabelMailCount === 0) || (emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit)"
                            title="Add new section"
                            @click="sendInvoicesByEmail"
                            type="button"
                            class="flex items-center gap-2 rounded bg-meta-3 py-2 px-3.5 font-medium text-white hover:bg-opacity-80"
                        >
                            <font-awesome-icon :icon="'envelope'" class="size-5"/>
                            Send invoices emails
                        </CustomButton>
                        <p class=" ml-auto">{{ params.shippingLabelMailCount }} Emails will be sent</p>
                    </div>

                    <h1 class="font-bold mb-2.5"> Invoices Emails Settings</h1>
                    <CustomSelect
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                        v-model="form.from_email_language_id"
                        @update:modelValue="form.errors = validate(form)"
                        mode="single"
                        label="From email"
                        placeholder="Select from email"
                        :options='params.emails'
                        :show-labels="true"
                        :close-on-select="true"
                        :canClear="true"
                        :searchable="true"
                        :error="form.errors['from_email_language_id'] ?? null"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.to_email"
                        name="to_email"
                        label="To email"
                        placeholder="Enter to email"
                        type="text"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['to_email']"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.to_admin_email"
                        name="to_admin_email"
                        label="To email(Admin)"
                        placeholder="Enter to email(Admin)"
                        type="text"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['to_admin_email']"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.email_subject"
                        name="email_subject"
                        label="Email subject"
                        placeholder="Enter email subject"
                        type="text"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['email_subject']"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.prefix_before_invoice_num"
                        name="prefix_before_invoice_num"
                        label="Proforma invoice file name prefix"
                        placeholder="Enter proforma invoice file name prefix"
                        type="text"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['prefix_before_invoice_num']"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.deleted_labels_text"
                        name="deleted_labels_text"
                        label="Text before deleted labels"
                        placeholder="Enter text before deleted labels"
                        type="text"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['deleted_labels_text']"
                    />
                    <div class="flex gap-8 mb-6">
                        <Switch
                            @change="(value) => {
                            form.combine_emails_pdfs = value;
                        }"
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                            :value="form.combine_emails_pdfs"
                            id="combine_emails_pdfs"
                            label='Combine proforma invoice & IMO PDFs'
                        />
                    </div>

                </div>
                <h1 class="font-bold mb-2.5"> API credentials</h1>
                <CustomInput
                    v-if="form.id === 2 || form.id === 3"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.api_key"
                    name="api_key"
                    label="API Key"
                    type="text"
                    placeholder="Enter delis id"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['api_key']"
                />
                <CustomInput
                    v-if="form.id === 1 || form.id === 2 || form.id === 4"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.username"
                    name="username"
                    :label="form.id === 1 ? 'Delis ID' : 'Username'"
                    :placeholder="form.id === 1 ? 'Enter delis ID' : 'Enter username'"
                    type="text"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['username']"
                />

                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.password"
                    name="password"
                    label="Password *"
                    type="text"
                    placeholder="Enter password"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['password']"
                />

                <CustomInput
                    v-if="form.id === 2 || form.id === 3|| form.id === 4"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.billing_number"
                    name="billing_number"
                    :label="form.id === 2 ? 'Billing number (for DE shipping)' : 'Account number'"
                    :placeholder="form.id === 2 ? 'Enter billing number' : 'Enter account number'"
                    type="text"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['billing_number']"
                />
                <CustomInput
                    v-if="form.id === 2 || form.id === 3"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.billing_number_international"
                    :label="form.id === 2 ? 'Billing Number (For International Shipping)' : 'Meter number'"
                    :placeholder="form.id === 2 ? 'Enter billing number' : 'Enter meter number'"
                    name="billing_number_international"
                    type="text"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['billing_number_international']"
                />
                <h1 class="font-bold mb-2.5">Parcel settings</h1>
                <CustomSelect
                    v-if="form.id !== 4"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    label="PDF page size"
                    v-model="form.pdf_page_size"
                    mode="single"
                    placeholder="Select page size"
                    :options="params.shippingLabels"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                />
                <div v-if="form.id === 1" class="mt-4 mb-5">
                    <CustomSelect
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        label="Label start position"
                        v-model="form.label_start_position"
                        mode="single"
                        placeholder="Select label start position"
                        :options="params.labelStartPositions"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                    />
                </div>

                <CustomInput
                    v-if="form.id === 1 || form.id === 3"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.parcel_content"
                    name="parcel_content"
                    label="Parcel content description"
                    type="text"
                    placeholder="Enter parcel content description"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['parcel_content']"
                />
                <CustomInput
                    v-if="form.id === 1 || form.id === 2"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.reference_1"
                    name="reference_1"
                    :label="form.id === 1 ? 'Reference 1' : 'Reference number prefix'"
                    :placeholder="form.id === 1 ? 'Enter reference 1' : 'Enter number prefix'"
                    type="text"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['reference_1']"
                />
                <CustomInput
                    v-if="form.id === 1 || form.id === 2"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.reference_2"
                    name="reference_2"
                    :label="form.id === 1 ? 'Reference 2' : 'Country of origin'"
                    :placeholder="form.id === 1 ? 'Enter reference 2' : 'Enter country of origin'"
                    type="text"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['reference_2']"
                />
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.parcel_max_weight"
                    name="parcel_max_weight"
                    label="Max. weight for each parcel in KGs"
                    type="number"
                    placeholder="Enter max. weight for each parcel in KGs"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['parcel_max_weight']"
                />
                <CustomInput
                    v-if="form.id === 3"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.envelope_max_weight"
                    name="envelope_max_weight"
                    label='Max weight (in OZ) for "fedex envelop" shipping method'
                    type="number"
                    placeholder='Enter max. weight (in OZ) for "fedex envelop" shipping method'
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['envelope_max_weight']"
                />
                <CustomInput
                    v-if="form.id === 4"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.api_key"
                    name="api_key"
                    label='Instructions that must be passed to the TNT driver when delivering the consignment'
                    type="text"
                    placeholder='Instructions that must be passed to the TNT driver when delivering the consignment'
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['api_key']"
                />
                <div v-if="form.id === 1" class="flex gap-8 mb-6">
                    <Switch
                        @change="(value) => {
                            form.predict_function = value;
                        }"
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        :value="form.predict_function"
                        id="predict_function"
                        label='Default value for Predict Function'
                    />
                </div>

                <div class="flex gap-8 mb-6">
                    <Switch
                        @change="(value) => {
                            form.generate_label_on_status_process = value;
                        }"
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        :value="form.generate_label_on_status_process"
                        id="generate_label_on_status_process"
                        label='Automatically generate label when order status set to "Process"'
                    />
                </div>
                <h1 v-if="form.id === 1 || form.id === 2" class="font-bold mb-2.5">Customs settings</h1>
                <CustomSelect
                    v-if="form.id === 1 || form.id === 2"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    v-model="form.customs_countries"
                    @update:modelValue="form.errors = validate(form)"
                    mode="tags"
                    label="Countries to apply customs information to"
                    placeholder="Select  countries"
                    :options="params.customCountries"
                    :show-labels="true"
                    :close-on-select="false"
                    :canClear="false"
                    :error="form.errors['customs_countries'] ?? null"
                />
                <div v-if="form.id === 1" class="mt-4 mb-4">
                    <CustomSelect
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        label="Terms of delivery"
                        v-model="form.delivery_terms"
                        mode="single"
                        placeholder="Select delivery termsn"
                        :options="params.termsOfDeliveries"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                    />
                </div>
                <CustomInput
                    v-if="form.id === 1"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.gtnr"
                    name="gtnr"
                    label="GTNR"
                    type="text"
                    placeholder="Enter max. weight for each parcel in KGs "
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['gtnr']"
                />
                <CustomInput
                    v-if="form.id === 1"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.goods_content"
                    name="goods_content"
                    label="Goods Content"
                    type="text"
                    placeholder="Enter goods content "
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['goods_content']"
                />

            </div>
            <div class="flex flex-col p-6.5">
                <div v-if="form.id === 1 || form.id === 4">
                    <h1 class="font-bold mb-1.5"> Pickup Settings</h1>
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.time_from"
                        name="time_from"
                        label="Pickup time from"
                        type="time"
                        placeholder="Enter time from"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['time_from']"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.time_to"
                        name="time_to"
                        label="Pickup time to"
                        type="time"
                        placeholder="Enter pickup time to "
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['time_to']"
                    />
                </div>
                <h1 class="font-bold mb-2.5">Sender details</h1>
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.sender_company"
                    name="sender_company"
                    label="Company name"
                    type="text"
                    placeholder="Enter company name"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['sender_company']"
                />
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.sender_street"
                    name="sender_street"
                    :label="form.id === 4 ? 'Address 1' : 'Sender street'"
                    :placeholder="form.id === 4 ? 'Enter address 1' : 'Enter sender street'"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['sender_street']"
                />
                <CustomInput
                    v-if="form.id === 2 || form.id === 4"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.sender_state"
                    name="sender_state"
                    :label="form.id === 4 ? 'Address 2' : 'Sender street number'"
                    :placeholder="form.id === 4 ? 'Enter address 2' : 'Enter sender street number'"
                    type="text"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['sender_state']"
                />
                <div class="mt-1 mb-4">
                    <CustomSelect
                        class="py-2 rounded-lg border-stroke bg-transparent"
                        v-model="form.sender_country"
                        @update:modelValue="form.errors = validate(form)"
                        mode="single"
                        label="Sender country"
                        placeholder="Select sender country"
                        :options="params.countries"
                        :show-labels="true"
                        :close-on-select="true"
                        :canClear="false"
                        :searchable="true"
                        :error="form.errors['sender_country'] ?? null"
                    />
                </div>
                <CustomInput
                    v-if="form.id === 3"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.sender_state"
                    name="sender_state"
                    :label="'Sender state code'"
                    placeholder="Enter sender state code"
                    type="text"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['sender_state']"
                />
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.sender_zip"
                    name="sender_zip"
                    label="Sender zip code"
                    type="text"
                    placeholder="Enter sender zip code"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['sender_zip']"
                />
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.sender_city"
                    name="sender_city"
                    label="Sender city"
                    type="text"
                    placeholder="Enter sender city"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['sender_city']"
                />
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.sender_phone"
                    name="sender_phone"
                    label="Sender phone number"
                    type="text"
                    placeholder="Enter sender phone number"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['sender_phone']"
                />

                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.sender_email"
                    name="sender_email"
                    label="Sender email"
                    type="email"
                    placeholder="Enter sender email"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['sender_email']"
                />
                <CustomInput
                    v-if="form.id === 2 || form.id === 3 || form.id === 4"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.sender_contact_person"
                    name="sender_contact_person"
                    label="Sender contact person"
                    type="text"
                    placeholder="Enter sender contact person"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['sender_contact_person']"
                />
                <CustomInput
                    v-if="form.id === 1 || form.id === 2 || form.id === 4"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.sender_customer_no"
                    name="sender_customer_no"
                    :label="form.id === 4 ? 'VAT number' : 'Sender customer number'"
                    :placeholder="form.id === 4 ? 'Enter VAT number' : 'Enter vat customer number'"
                    type="text"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['sender_customer_no']"
                />
            </div>
        </div>
        <div v-if="form.id === 4" class="grid grid-cols-2 gap-9" style="margin-top: -50px">
            <div class="flex flex-col p-6.5">
                <div class="mt-6">
                    <h1 class="font-bold mb-2.5">Customs Settings</h1>
                    <div class="flex gap-8 mb-6">
                        <Switch
                            @change="(value) => {
                            form.use_imo = value;
                        }"
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                            :value="form.use_imo"
                            id="use_imo"
                            label='Use IMO document by default'
                        />
                    </div>
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.eori_number"
                        name="eori_number"
                        label="EORI number"
                        placeholder="Enter EORI number"
                        type="text"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['eori_number']"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.adb_enable_price"
                        name="adb_enable_price"
                        label="Use ADB document for orders greater than (in EUR)"
                        placeholder="Enter use ADB document for orders greater than (in EUR)"
                        type="number"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['adb_enable_price']"
                    />
                    <CustomSelect
                        class="py-2 rounded-lg border-stroke bg-transparent"
                        v-model="form.customs_countries"
                        @update:modelValue="form.errors = validate(form)"
                        mode="tags"
                        label="Create ADB document for following countries"
                        placeholder="Select  countries"
                        :options="params.customCountries"
                        :show-labels="true"
                        :close-on-select="false"
                        :canClear="false"
                        :error="form.errors['customs_countries'] ?? null"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.packaging_type"
                        name="packaging_type"
                        label="Packaging type"
                        placeholder="Enter packaging type"
                        type="text"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['packaging_type']"
                    />
                    <CustomSelect
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        label="Type of export"
                        v-model="form.type_of_export"
                        mode="single"
                        :canClear="true"
                        placeholder="Select type of export"
                        :options="params.typeOfExports"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                    />
                    <template
                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit">
                        <div class="mb-5.5">
                            <CustomMediaList
                                @remove-images="removeSingleGallery"
                                label="PDF to combine with ADB document"
                                @insert="insert"
                                :images="media"
                                :types="['files']"
                                mode="single"
                            />
                        </div>
                    </template>

                    <CustomSelect
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                        v-model="form.adb_from_email_language_id"
                        @update:modelValue="form.errors = validate(form)"
                        mode="single"
                        label="ADB document from email"
                        placeholder="Select ADB document from email"
                        :options='params.emails'
                        :show-labels="true"
                        :close-on-select="true"
                        :canClear="true"
                        :searchable="true"
                        :error="form.errors['adb_from_email_language_id']"
                    />

                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.adb_to_email"
                        name="adb_to_email"
                        label="ADB document to email"
                        placeholder="Enter ADB document to email"
                        type="text"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['adb_to_email']"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.adb_to_admin_email"
                        name="adb_to_admin_email"
                        label="ADB document to email (Admin)"
                        placeholder="Enter ADB document to email (Admin)"
                        type="text"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['adb_to_admin_email']"
                    />

                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.adb_email_subject"
                        name="adb_email_subject"
                        label="ADB document email subject"
                        placeholder="Enter ADB document email subject"
                        type="text"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['adb_email_subject']"
                    />
                    <CustomSelect
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        label="Default incoterm value"
                        v-model="form.default_incoterm"
                        mode="single"
                        :canClear="true"
                        placeholder="Select default incoterm value"
                        :options="params.defaultIncoterms"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.dap_label_text"
                        name="dap_label_text"
                        label="Text on label for DAP"
                        placeholder="Enter text on label for DAP"
                        type="text"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['dap_label_text']"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.ddp_label_text"
                        name="ddp_label_text"
                        label="Text on label for DDP"
                        placeholder="Enter text on label for DDP"
                        type="text"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['ddp_label_text']"
                    />

                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.dap_label_text_uk"
                        name="dap_label_text_uk"
                        label="Text on label for DAP (UK)"
                        placeholder="Enter text on label for DAP (UK)"
                        type="text"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['dap_label_text_uk']"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.ddp_label_text_uk"
                        name="ddp_label_text_uk"
                        label="Text on label for DDP (UK)"
                        placeholder="Enter text on label for DDP (UK)"
                        type="text"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['ddp_label_text_uk']"
                    />
                </div>
            </div>

            <div class="flex flex-col p-6.5">
                <div>
                    <h1 class="font-bold mb-2.5 mt-5.5">Collection Details </h1>
                    <div class="flex gap-8 mb-6">
                        <Switch
                            @change="(value) => {
                            form.use_collection_data = value;
                        }"
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                            :value="form.use_collection_data"
                            id="use_collection_data"
                            label='Use bellow info for collection'
                        />
                    </div>
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.collection_company"
                        name="collection_company"
                        label="Name of company"
                        type="text"
                        placeholder="Enter name of company"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['collection_company']"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.collection_address_1"
                        name="collection_address_1"
                        label="Address 1"
                        type="text"
                        placeholder="Enter address 1"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['collection_address_1']"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.collection_address_2"
                        name="collection_address_2"
                        label="Address 2"
                        type="text"
                        placeholder="Enter address 2"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['collection_address_2']"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.collection_city"
                        name="collection_city"
                        label="City"
                        type="text"
                        placeholder="Enter city"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['collection_city']"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.collection_zip"
                        name="collection_zip"
                        label="Zip Code"
                        type="text"
                        placeholder="Enter zip code"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['collection_zip']"
                    />

                    <CustomSelect
                        class="py-2 rounded-lg border-stroke bg-transparent"
                        v-model="form.collection_country"
                        @update:modelValue="form.errors = validate(form)"
                        mode="single"
                        label="Country"
                        placeholder="Select country"
                        :options="params.countries"
                        :show-labels="true"
                        :close-on-select="true"
                        :canClear="false"
                        :searchable="true"
                        :error="form.errors['collection_country'] ?? null"
                    />

                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.collection_phone"
                        name="collection_phone"
                        label="Fixed line phone number"
                        type="text"
                        placeholder="Enter fixed line phone number"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['collection_phone']"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.collection_email"
                        name="collection_email"
                        label="Email"
                        type="text"
                        placeholder="Enter email"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['collection_email']"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.collection_contact_person"
                        name="collection_contact_person"
                        label="Contact person full name"
                        type="text"
                        placeholder="Enter contact person full name"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['collection_contact_person']"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.collection_vat"
                        name="collection_vat"
                        label="VAT number"
                        type="text"
                        placeholder="Enter VAT number"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['collection_vat']"
                    />
                </div>
                <div>
                    <h1 class="font-bold mb-2.5 ">NF4 Document Settings</h1>
                    <div class="flex gap-8 mb-6">
                        <Switch
                            @change="(value) => {
                            form.enable_nf4_files = value;
                        }"
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                            :value="form.enable_nf4_files"
                            id="enable_nf4_files"
                            label='Enable NF4 files'
                        />
                    </div>
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.tnt_consignment_number"
                        name="tnt_consignment_number"
                        label="TNT consignment number"
                        type="text"
                        placeholder="Enter TNT consignment number"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['tnt_consignment_number']"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.source_code"
                        name="source_code"
                        label="Source code"
                        type="text"
                        placeholder="Enter source code"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['source_code']"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.sftp_host"
                        name="sftp_host"
                        label="SFTP host"
                        type="text"
                        placeholder="Enter SFTP host"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['sftp_host']"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.sftp_username"
                        name="sftp_username"
                        label="SFTP username"
                        type="text"
                        placeholder="Enter SFTP host"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['sftp_username']"
                    />
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                        v-model="form.sftp_password"
                        name="sftp_password"
                        label="SFTP password"
                        type="text"
                        placeholder="Enter SFTP password"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['sftp_password']"
                    />


                </div>
            </div>

        </div>
        <div v-if="form.id === 4 || form.id === 1" class="grid grid-cols-1 gap-9" style="margin-top: -50px">
            <div class="flex flex-col p-6.5">
                <div v-for="(rule, key) in form.rules" :key="key"
                     class="flex flex-col relative my-6.5 min-h-[100px] border-[1.5px] shadow-default relative top-0 col-span-1 sm:col-span-1 md:col-span-1 ">
                    <button
                        type="button"
                        @click="removeRule(key)"
                        class="hover:text-primary absolute right-2 top-1"
                        title="Delete"
                    >
                        <font-awesome-icon :icon="['fas', 'trash-can']"/>
                    </button>
                    <div class="grid grid-cols-2 gap-9 p-6.5">
                        <div>
                            <CustomSelect
                                class="py-2 rounded-lg border-stroke bg-transparent"
                                v-model="rule.country"
                                @update:modelValue="handleCountryChange"
                                mode="single"
                                label="Country / Region"
                                placeholder="Select country"
                                :options="filteredCountries"
                                :show-labels="true"
                                :close-on-select="true"
                                :canClear="false"
                                :searchable="true"
                                :error="form.errors['sender_country'] ?? null"
                            />
                        </div>
                        <div v-if="form.id === 4">
                            <CustomSelect
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                                label="Incoterm"
                                v-model="rule.incoterm"
                                mode="single"
                                :canClear="true"
                                placeholder="Select incoterm "
                                :options="params.defaultIncoterms"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                            />
                            <CustomSelect
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                                label="Incoterm for reshipment"
                                v-model="rule.incoterm_reshipment"
                                mode="single"
                                :canClear="true"
                                placeholder="Select incoterm for reshipment"
                                :options="params.defaultIncoterms"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                            />
                            <div class="flex gap-8 mb-6">
                                <Switch
                                    @change="(value) => {
                                        rule.use_imo = value;
                                    }"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                                    :value="rule.use_imo"
                                    :id="'rule_use_imo_' + key"
                                    label='Use IMO document'
                                />
                            </div>
                            <div class="flex gap-8 mb-6">
                                <Switch
                                    @change="(value) => {
                                        rule.not_use_invoice = value;
                                    }"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                                    :value="rule.not_use_invoice"
                                    :id="'not_use_invoice_' + key"
                                    label="Don't use Invoice"
                                />
                            </div>
                            <CustomTextarea
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                                v-model="rule.bypass_zip_codes"
                                name="bypass_zip_codes"
                                label="Bypass automatic label creation for following ZIP codes <br>
                                        One ZIP code per line"
                                placeholder="Enter text on label for DDP (UK)"
                                type="text"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['bypass_zip_codes']"
                            />
                        </div>
                        <div v-else-if="form.id === 1">
                            <CustomInput
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                                v-model="rule.business_unit"
                                name="business_unit"
                                label="Business unit"
                                placeholder="Enter business unit"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['business_unit']"
                            />
                        </div>
                    </div>
                </div>
                <CustomButton
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    title="Add new section"
                    @click="addNewSection"
                    type="button"
                    class="flex items-center gap-2 rounded bg-meta-3 py-2 px-3.5 font-medium text-white hover:bg-opacity-80 ml-auto"
                >
                    <font-awesome-icon :icon="'plus'" class="size-5"/>
                    Add rule
                </CustomButton>
            </div>
        </div>
        <div class="flex flex-col p-6.5 save-button-fixed">
            <div class="flex ml-auto gap-5">
                <template v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.currencies[0].can_edit">
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
    </form>
</template>

<style scoped>

</style>
