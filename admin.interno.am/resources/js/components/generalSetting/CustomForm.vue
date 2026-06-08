<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import CustomMediaList from "@components/media/CustomMediaList.vue";

import {computed, ref, toRefs, watch,} from "vue";
import {useStore} from "vuex";
import {validate} from "@validation/customValidation.js";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";


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

store.dispatch('generalSetting/fetchParams', {id: form.value.id});
const params = computed(() => store.getters['generalSetting/getParams']);
const auth = computed(() => store.getters['auth/getUser']);

const removeSingleGallery = () => {
    form.value.lang_value = null;
}

const mediaData = (media) => {
    form.value.lang_value = media.original_path;
    return {
        path: media.original_path,
        type: 'images',
    }
}

const media = ref(form.value.lang_value ? [{
    path: form.value.lang_value,
    type: 'images',
}] : []);

const insert = (data) => {
    data.media.forEach(mediaItem => {
        if (mediaItem.id) {
            const updatedMedia = mediaData(mediaItem);
            media.value = [updatedMedia];
        }
    });
};

const removeSingleGalleryGeneral = () => {
    form.value.value = null;
}

const mediaDataGeneral = (mediaGeneral) => {
    form.value.value = mediaGeneral.original_path;
    return {
        path: mediaGeneral.original_path,
        type: 'images',
    }
}

const mediaGeneral = ref(form.value.value ? [{
    path: form.value.value,
    type: 'images',
}] : []);

const insertGeneral = (data) => {

    data.media.forEach(mediaItem => {
        if (mediaItem.id) {
            form.value.value = mediaItem.original_path;
            const updatedMediaGeneral = mediaDataGeneral(mediaItem);
            mediaGeneral.value = [updatedMediaGeneral];
        }
    });
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

        <div class="grid grid-cols-2 gap-9">

            <div v-if="form.language_id == -1">
                <div class="flex flex-col pt-5 pb-0 col-span-1">
                    <div class="flex flex-col p-6.5 pb-0"
                         v-if="form.key === 'logo' || form.key === 'admin_panel_logo' || form.key === 'social_img_default' || form.key === 'placeholder_image' || form.key === 'logo_email'">
                        <template
                            v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.general_settings[0].can_edit">
                            <CustomMediaList
                                @remove-images="removeSingleGalleryGeneral"
                                label="Image"
                                @insert="insertGeneral"
                                :images="mediaGeneral"
                                :types="['images']"
                                mode="single"
                            />
                        </template>
                    </div>
                    <div v-else-if="form.key === 'prices_include_taxes'" class="flex flex-col pb-6.5 pl-6.5 pr-6.5 ">
                        <CustomSelect
                            label="Prices include taxes"
                            v-model="form.value"
                            mode="single"
                            placeholder="Select prices include taxes"
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.general_settings[0].can_edit"
                            :options="[
                                {value: 'yes', label: 'Yes, I will enter prices inclusive of tax'},
                                {value: 'no', label: 'No, I will enter prices exclusive of tax'}]"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                        />
                    </div>
                    <div v-else-if="form.key === 'paypal_mode' || form.key === 'stripe_mode'" class="flex flex-col pb-6.5 pl-6.5 pr-6.5 ">
                        <CustomSelect
                            label="Mode"
                            v-model="form.value"
                            mode="single"
                            placeholder="Select mode"
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.general_settings[0].can_edit"
                            :options="[
                                {value: 'live', label: 'Live'},
                                {value: 'sandbox', label: 'Sandbox'}]"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                        />
                    </div>
                    <div v-else-if="form.key === 'use_ip_checker'" class="flex flex-col pb-6.5 pl-6.5 pr-6.5 ">
                        <CustomSelect
                            label="Use IP checker"
                            v-model="form.value"
                            mode="single"
                            placeholder="Select"
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.general_settings[0].can_edit"
                            :options="[
                                {value: 'yes', label: 'Yes'},
                                {value: 'no', label: 'No'}
                                ]"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                        />
                    </div>
                    <div v-else-if="form.key === 'company_address'" class="flex flex-col pb-6.5 pl-6.5 pr-6.5 ">
                        <CKEditorComponent
                            :model="form.value"
                            @updateValue="(value) => {
                                        form.value = value
                                    }"
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.general_settings[0].can_edit"
                        />
                    </div>
                    <div v-else class="flex flex-col pb-6.5 pl-6.5 pr-6.5 ">
                        <CustomInput
                            v-model="form.value"
                            name="value"
                            label="Value"
                            :type="form.key === 'delivery_working_days' ? 'number' : 'text'"
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.general_settings[0].can_edit"
                            placeholder="Enter value"
                            @keyup="form.errors = validate(form)"
                            :error="form.errors['value']"
                        />
                    </div>

                </div>
            </div>
            <div v-else class="grid grid-cols-1 gap-9">
                <div class="flex flex-col p-6.5 pb-0"
                     v-if="form.key === 'logo' || form.key === 'admin_panel_logo' || form.key === 'social_img_default' || form.key === 'placeholder_image' || form.key === 'logo_email'">
                    <template
                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.general_settings[0].can_edit">
                        <CustomMediaList
                            @remove-images="removeSingleGallery"
                            label="Image"
                            @insert="insert"
                            :images="media"
                            :types="['images']"
                            mode="single"
                        />
                    </template>
                </div>
                <div v-else-if="form.key === 'prices_include_taxes'" class="flex flex-col pb-6.5 pl-6.5 pr-6.5 ">
                    <CustomSelect
                        label="Prices include taxes"
                        v-model="form.lang_value"
                        mode="single"
                        placeholder="Select prices include taxes"
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.general_settings[0].can_edit"
                        :options="[
                                {value: 'yes', label: 'Yes, I will enter prices inclusive of tax'},
                                {value: 'no', label: 'No, I will enter prices exclusive of tax'}]"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                    />
                </div>
                <div v-else-if="form.key === 'use_ip_checker'" class="flex flex-col pb-6.5 pl-6.5 pr-6.5 ">
                    <CustomSelect
                        label="Use IP checker"
                        v-model="form.lang_value"
                        mode="single"
                        placeholder="Select"
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.general_settings[0].can_edit"
                        :options="[
                                {value: 'yes', label: 'Yes'},
                                {value: 'no', label: 'No'}
                        ]"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                    />
                </div>
                <div v-else-if="form.key === 'company_address'" class="flex flex-col pb-6.5 pl-6.5 pr-6.5 ">
                    <CKEditorComponent
                        :model="form.lang_value"
                        @updateValue="(value) => {
                                        form.lang_value = value
                                    }"
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.general_settings[0].can_edit"
                    />
                </div>
                <div v-else class="flex flex-col p-6.5 pb-0">
                    <div>
                        <CustomInput
                            v-model="form.lang_value"
                            name="lang_value"
                            label="Value"
                            :type="form.key === 'delivery_working_days' ? 'number' : 'text'"
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.general_settings[0].can_edit"
                            placeholder="Enter value"
                            @keyup="form.errors = validate(form)"
                            :error="form.errors['lang_value']"
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
                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.general_settings[0].can_edit">
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
