<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import CustomMediaList from "@components/media/CustomMediaList.vue";
import CustomPdfLive from "@components/global/CustomPdfLive.vue";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";

import {computed, ref, toRefs, watch,} from "vue";
import {useStore} from "vuex";
import {validate} from "@validation/customValidation.js";
import Switch from "@components/global/Switch.vue";
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
])

watch(modelValue, (newVal) => {
    form.value = newVal;
});

watch(form.value, (newVal) => {
    emits('update:modelValue', newVal);
});

store.dispatch('documentSettingsGeneral/fetchParams', {id: form.value.id});
const params = computed(() => store.getters['documentSettingsGeneral/getParams']);

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

const insert = (data) => {
    data.media.forEach(media => {
        if (media.id) {
            form.value.media_id = media.id
            form.value.media = [mediaData(media)]
            media.value = [mediaData(media)];
        }
    });
}

const addNewSection = () => {
    form.value.rules.push({});
}

const removeRule = (key) => {
    form.value.rules.splice(key, 1);
}

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
            class="grid grid-cols-1 gap-9 sm:grid-cols-1 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>
        <div class="grid grid-cols-1 gap-9 sm:grid-cols-5">
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
            <div
                v-if="form.language_id == -1"
            >

                <div class="flex flex-col p-6.5 pt-0">
                    <CustomMediaList
                        @remove-images="removeSingleGallery"
                        label="Shop header/logo"
                        @insert="insert"
                        :images="form.media"
                        :types="['images']"
                        mode="single"
                    />
                </div>
            </div>
            <div v-else >
                <div class="grid grid-cols-1 mt-5 sm:grid-cols-1">
                    <div class="flex flex-col pl-6.5 pr-6.5 pb-0">
                        <CustomInput
                            v-model="form.name"
                            name="name"
                            label="Shop Name *"
                            type="text"
                            placeholder="Enter name"
                            @keyup="form.errors = validate(form)"
                            :error="form.errors['name']"
                        />
                        <CustomInput
                            v-model="form.phone"
                            name="phone"
                            label="Phone"
                            type="text"
                            placeholder="Phone"
                            :error="form.errors['phone']"
                        />
                        <CustomInput
                            v-model="form.email"
                            name="email"
                            label="Email"
                            type="text"
                            placeholder="Email"
                            :error="form.errors['email']"
                        />
                        <div class="mb-5">
                            <label class="mb-2.5 block font-medium text-black">Shop Address</label>
                            <CKEditorComponent
                                :model="form.address"
                                @updateValue="(value) => {
                                            form.address = value
                                        }"
                            />
                        </div>
                        <div class="mb-5">
                            <label class="mb-2.5 mt-2.5 block font-medium text-black">Footer text</label>
                            <CKEditorComponent
                                :model="form.footer_text"
                                @updateValue="(value) => {
                                            form.footer_text = value
                                        }"
                            />
                        </div>
                        <div class="mb-5">
                            <label class="mb-2.5 mt-2.5 block font-medium text-black">Seller info</label>
                            <CKEditorComponent
                                :model="form.seller_info"
                                @updateValue="(value) => {
                                            form.seller_info = value
                                        }"
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

        <div class="grid grid-cols-1 gap-9" v-if="form.language_id > 0">
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
                                label="Country"
                                placeholder="Select country"
                                :options="filteredCountries"
                                :show-labels="true"
                                :close-on-select="true"
                                :canClear="false"
                                :searchable="true"
                                :error="form.errors['sender_country'] ?? null"
                            />
                        </div>
                        <div></div>
                        <div>
                            <div class="mb-5">
                                <label class="mb-2.5 block font-medium text-black">Footer text</label>
                                <CKEditorComponent
                                    :model="rule.footer_text"
                                    @updateValue="(value) => {
                                            rule.footer_text = value
                                        }"
                                />
                            </div>
                        </div>
                        <div>
                            <div class="mb-5">
                                <label class="mb-2.5 block font-medium text-black">Footer tex for base language</label>
                                <CKEditorComponent
                                    :model="rule.footer_text_base"
                                    @updateValue="(value) => {
                                            rule.footer_text_base = value
                                        }"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <CustomButton
                    title="Add new section"
                    @click="addNewSection"
                    type="button"
                    class="flex items-center gap-2 rounded bg-meta-3 py-2 px-3.5 font-medium text-white hover:bg-opacity-80 mr-auto"
                >
                    <font-awesome-icon :icon="'plus'" class="size-5"/>
                    Add rule
                </CustomButton>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-9 sm:grid-cols-1">
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
    </form>
</template>
