<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import {validate} from "@validation/customValidation.js";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";
import {computed, ref, toRefs, watch} from "vue";
import {useStore} from "vuex";
import CustomMediaList from "@components/media/CustomMediaList.vue";
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

const auth = computed(() => store.getters['auth/getUser']);

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
const formFeed = ref({
    language_code: '',
    category_id: form.value.translation_id,
    category_slug: form.value.slug,
    feed_type_id: '',
    currency: '',
    file_type: '',
})

const params = computed(() => store.getters['category/getParams'] ?? { feedTypes: [] });
const copyFeedUrl = () => {
    const domain = window.location.origin;
    const url = `${domain}${fileUrl()}`;

    const input = document.createElement('input');
    input.value = url;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);

    store.commit('notification/SET_NOTIFICATION', {
        visible: true,
        title: 'Success',
        message: 'Copied to clipboard!'
    });
};


const feedTypes = computed(() => {
    return params.value.feedTypes?.filter(feed => feed.language_id == form.value.language_id) ?? [];
});

const languageCode = ref('');

const isAiTranslateExpanded = ref(false);
const toggleAiTranslate = () => {
    isAiTranslateExpanded.value = !isAiTranslateExpanded.value;
};

const selectedLanguagesForTranslation = ref([]);
const generateAITranslations = async (isAll = false) => {
    await store.dispatch('category/translateAI', {
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
    await store.dispatch('category/approveTranslation', {
        translation_id: form.value.translation_id,
    })
    form.value.approved = true;

    store.commit('notification/SET_NOTIFICATION', {
        visible: true,
        title: 'Success',
        message: `Successfully approved`
    });
}



watch(params, (newParams) => {
    if (newParams?.feedLanguages?.length) {
        languageCode.value = newParams.feedLanguages.find(lang => lang.id == form.value.language_id)?.value || '';
    }
}, { immediate: true });


watch(languageCode, (newCode) => {
    if (newCode) {
        formFeed.value.language_code = newCode;
        formFeed.value.feed_type = feedTypes.value?.[0]?.value || null;
        formFeed.value.currency = params.value.currencies?.[0]?.value || null;
        formFeed.value.file_type = '.csv';
    }
}, { immediate: true });

watch(formFeed.value, (newVal) => {
    formFeed.value = newVal
});

const fileUrl = () => {
    let vendorKey = localStorage.getItem('vendor_key');
    return `/${vendorKey}/${formFeed.value.language_code}/shop/${formFeed.value.currency}/${formFeed.value.category_slug}/${formFeed.value.category_id}/${formFeed.value.feed_type}${formFeed.value.file_type}`

};

</script>

<template>
    <form @submit.prevent="emits('submit')">
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>
        <div v-if="emitAction === 'update' && form.language_id > -1" class="pb-0 border-b border-stroke py-3 px-6.5">
            <label class="mb-2.5 block font-medium text-black">Download Category Feed </label>
            <div class="grid grid-cols-8 gap-4 ">
                <CustomSelect
                    v-model="formFeed.feed_type"
                    mode="single"
                    :options="feedTypes"
                    class="rounded-lg border-stroke bg-transparent"
                />
                <CustomSelect
                    v-model="formFeed.currency"
                    mode="single"
                    :options="params.currencies"
                    class="rounded-lg border-stroke bg-transparent"
                />

                <CustomSelect
                    v-model="formFeed.file_type"
                    mode="single"
                    :options="params.fileTypes"
                    class="rounded-lg border-stroke bg-transparent"
                />
                <div>
                    <a target="_blank" :href="fileUrl()"
                       class="flex items-center pointer-events-auto w-full gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                       type="button"
                    >
                        <font-awesome-icon :icon="['far', 'download']"/>
                        Download
                    </a>

                </div>
                <div>
                    <CustomButton
                        @click="copyFeedUrl()"
                        class="flex items-center gap-2  rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                        type="button"
                    >
                        <font-awesome-icon :icon="['far', 'copy']"/>
                        Copy
                    </CustomButton>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-5 gap-7 px-6.5 py-2">
            <div class="flex flex-col">
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

        <div class="flex flex-col px-6.5 col-span-4" v-if="form.translation_id">
            <div class="pb-5">
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
            <div class="pb-3" :class="{ 'hidden': !isAiTranslateExpanded }">
            <template v-if="form.approved">
                <h2 class="text-black mt-2 font-bold">You can generate AI translations from this language to the
                    missing ones.</h2>
                <div class="flex mt-2">
                    <div>
                        <CustomButton
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
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
                            :disabled="(emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit) || !selectedLanguagesForTranslation.length"
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
                            :disabled="emitAction === 'create'"
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

        <div
            v-if="emitAction === 'update' && form.slug"
            class="grid grid-cols-1 gap-9"
        >
            <div class="flex flex-col px-6.5 pb-2">
                <a
                    target="_blank"
                    class="hover-trigger hover:text-primary"
                    :href="form.front_url_view"
                >
                    <span class="font-semibold text-black">{{ form.front_url_view }}</span>
                </a>
            </div>
        </div>
        <hr class="text-gray">
        <template v-if="form.language_id == -1">
            <div class="grid grid-cols-4">
                <div class="flex flex-col p-6.5 pb-0">
                    <CustomSelect
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.categories[0].can_edit"
                        label="Parent"
                        v-model="form.parent_id"
                        mode="single"
                        :canClear="true"
                        placeholder="Select"
                        :excluded-value="form.id"
                        :options="params.structuredCategories"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                    />
                </div>
                <div class="flex flex-col p-6.5 pb-0">
                    <CustomSelect
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.categories[0].can_edit"
                        label="Showing type"
                        v-model="form.products_showing_type"
                        mode="single"
                        placeholder="Select"
                        :options="params.showingTypes"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                        @update:modelValue="form.errors = validate(form)"
                        :error="form.errors['products_showing_type']"
                    />
                </div>
                <div class="flex flex-col p-6.5 pb-0">
                    <CustomSelect
                        label="Related calculator"
                        v-model="form.calculator_id"
                        mode="single"
                        :canClear="true"
                        placeholder="Select"
                        :options="params.calculators"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                    />
                </div>
                <div class="flex flex-col p-6.5 pb-0">
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.categories[0].can_edit"
                        v-model="form.price_adjustment"
                        name="price_adjustment"
                        label="Price adjustment by %"
                        type="text"
                        placeholder="Enter percent"
                        @update:modelValue="form.errors = validate(form)"
                        :error="form.errors['price_adjustment']"
                    />
                </div>
            </div>
            <div class="grid grid-cols-4 ">
                <div class="flex flex-col p-6.5 pb-0">
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.categories[0].can_edit"
                        v-model="form.desktop"
                        name="desktop"
                        label="Products per row on desktop"
                        type="number"
                        placeholder="Enter count"
                        @update:modelValue="form.errors = validate(form)"
                        :error="form.errors['desktop']"
                    />
                </div>
                <div class="flex flex-col p-6.5 pb-0">
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.categories[0].can_edit"
                        v-model="form.tablet"
                        name="tablet"
                        label="Products per row on tablet"
                        type="number"
                        placeholder="Enter count"
                        @update:modelValue="form.errors = validate(form)"
                        :error="form.errors['tablet']"
                    />
                </div>
                <div class="flex flex-col p-6.5 pb-0">
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.categories[0].can_edit"
                        v-model="form.mobile"
                        name="mobile"
                        label="Products per row on mobile"
                        type="number"
                        placeholder="Enter count"
                        @update:modelValue="form.errors = validate(form)"
                        :error="form.errors['mobile']"
                    />
                </div>
                <div class="flex flex-col p-6.5 pb-0">
                    <Switch
                        class=""
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.categories[0].can_edit"
                        @change="(value) => {
                                     form.hide_for_front = value;
                                }"
                        :value="form.hide_for_front"
                        id="hide_for_front"
                        label="Hide category for front"
                    />
                </div>
            </div>
            <div class="grid grid-cols-1 ">
                <div class="flex flex-col p-6.5 pb-0">
                    <template
                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.categories[0].can_edit">
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
            </div>
        </template>
        <template v-else>
            <div class="grid grid-cols-2 gap-9">
                <div class="flex flex-col p-6.5 pb-0">
                    <div>
                        <CustomInput
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.categories[0].can_edit"
                            v-model="form.name"
                            name="name"
                            label="Name *"
                            type="text"
                            placeholder="Enter name"
                            @keyup="form.errors = validate(form)"
                            :error="form.errors['name']"
                        />
                    </div>
                </div>
                <div class="flex flex-col p-6.5 pb-0">
                    <div>
                        <CustomInput
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.categories[0].can_edit"
                            v-model="form.slug"
                            name="slug"
                            label="Slug"
                            type="text"
                            placeholder="Enter or will generate automatically"
                            :error="form.errors['slug']"
                        />
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-9">
                <div class="flex flex-col p-6.5 pb-0">
                    <CustomSelect
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.categories[0].can_edit"
                        label="Related category"
                        v-model="form.related_category_translation_id"
                        mode="single"
                        :searchable="true"
                        placeholder="Select"
                        :excluded-value="form.id"
                        :canClear="true"
                        :options="params.structuredCategoriesWithTranslation"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                    />
                </div>
                <div class="flex flex-col p-6.5 pb-0">
                    <CustomSelect
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.categories[0].can_edit"
                        label="A + content"
                        v-model="form.a_plus_content_id"
                        mode="single"
                        placeholder="Select"
                        :canClear="true"
                        :options="params.aPlusContents"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                    />
                </div>
                <div class="flex flex-col p-6.5 pb-0">
                    <CustomSelect
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.categories[0].can_edit"
                        label="Snippet"
                        v-model="form.snippet_id"
                        mode="single"
                        placeholder="Select"
                        :canClear="true"
                        :options="params.snippets"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                    />
                </div>
            </div>
            <div class="grid grid-cols-1 gap-9">
                <div class="flex flex-col p-6.5 pb-0">
                    <div>
                        <label class="mb-2.5 block font-medium text-black">Description</label>
                        <CKEditorComponent
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.categories[0].can_edit"
                            :model="form.description"
                            @updateValue="(value) => {
                                form.description = value
                            }"
                        />
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-9">
                <div class="flex flex-col p-6.5 pb-0">
                    <div>
                        <CustomInput
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.categories[0].can_edit"
                            v-model="form.meta_title"
                            name="meta_title"
                            label="Meta title"
                            type="text"
                            placeholder="Enter meta title"
                            @keyup="form.errors = validate(form)"
                            :error="form.errors['meta_title']"
                        />
                    </div>
                </div>
                <div class="flex flex-col p-6.5 pb-0">
                    <div>
                        <CustomInput
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.categories[0].can_edit"
                            v-model="form.meta_keywords"
                            name="meta_keywords"
                            label="Meta keywords"
                            type="text"
                            placeholder="Enter meta keywords"
                            :error="form.errors['meta_keywords']"
                        />
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-9">
                <div class="flex flex-col p-6.5 pb-0">
                    <div>
                        <label class="mb-2.5 block font-medium text-black">Meta description</label>
                        <textarea
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.categories[0].can_edit"
                            v-model="form.meta_description"
                            rows="6"
                            placeholder="Enter meta description"
                            class="w-full rounded border-[1.5px] text-black border-stroke bg-transparent py-3 px-5 font-normal outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter"
                        ></textarea>
                    </div>
                </div>
            </div>
        </template>
        <hr class="text-gray mt-6.5">
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.categories[0].can_delete">
                        <CustomButton
                            v-if="emitAction === 'update' && (form.language_id == -1 || form.translation_id)"
                            @click="store.commit('category/SET_DELETE_MODAL_VALUE', {
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
                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.categories[0].can_edit">
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
