<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import CustomMediaList from "@components/media/CustomMediaList.vue";
import CustomTextarea from "@components/global/CustomTextarea.vue";

import {validate} from "@validation/customValidation.js";

import {computed, ref, toRefs, watch} from "vue";
import {useStore} from "vuex";
import CustomColorPicker from "@components/global/CustomColorPicker.vue";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";

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

store.dispatch('attribute/fetchParams', {id: form.value.id});
const params = computed(() => store.getters['attribute/getParams']);

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
const auth = computed(() => store.getters['auth/getUser']);

const isAiTranslateExpanded = ref(false);
const toggleAiTranslate = () => {
    isAiTranslateExpanded.value = !isAiTranslateExpanded.value;
};

const cloneLanguage = async () => {
    await store.dispatch('attribute/cloneLanguage', {
        translation_id: form.value.translation_id,
    });

    store.commit('notification/SET_NOTIFICATION', {
        visible: true,
        title: 'Success',
        message: 'Successfully cloned'
    });
}

const selectedLanguagesForTranslation = ref([]);
const generateAITranslations = async (isAll = false) => {
    await store.dispatch('attribute/translateAI', {
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
    await store.dispatch('attribute/approveTranslation', {
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
        <div class="grid grid-cols-5 gap-7 px-6.5 py-2">
            <div class="flex flex-col">
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
            <div class="flex flex-col col-span-4" v-if="form.translation_id">
                <h2 class="text-black mt-2 font-bold">You can clone this translation for missing languages or generate AI translations from this language to the missing ones.</h2>
                <div class="flex mt-2">
                    <div>
                        <CustomButton
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.attributes[0].can_edit"
                            type="button"
                            @click="cloneLanguage"
                            class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                        >
                            <font-awesome-icon :icon="['fasr', 'clone']"/>
                            Clone for all missing languages
                        </CustomButton>
                    </div>
                </div>
            </div>
        </div>
        <template v-if="form.translation_id">
            <div class="px-6.5 pb-5">
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
            <div class="px-6.5 pb-3" :class="{ 'hidden': !isAiTranslateExpanded }">
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
        </template>
        <hr class="text-gray">
        <template v-if="form.language_id == -1">
            <div class="grid grid-cols-2 gap-9">
                <div class="flex flex-col p-6.5 pb-0">
                    <CustomSelect
                        label="Attribute *"
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.attributes[0].can_edit"
                        v-model="form.attribute_type_id"
                        mode="single"
                        :canClear="true"
                        placeholder="Select"
                        :options="params.attributeTypes"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                        @update:modelValue="form.errors = validate(form)"
                        :error="form.errors['attribute_type_id']"
                    />
                </div>
                <div class="flex flex-col p-6.5 pb-0">
                    <CustomColorPicker v-model="form.color_code"/>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-9">
                <div class="flex flex-col p-6.5 pb-0">
                    <template v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.attributes[0].can_edit">
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
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.attributes[0].can_edit"
                            v-model="form.value"
                            name="value"
                            label="Value *"
                            type="text"
                            placeholder="Enter value"
                            @keyup="form.errors = validate(form)"
                            :error="form.errors['value']"
                        />
                    </div>
                </div>
                <div class="flex flex-col p-6.5 pb-0">
                    <div>
                        <CustomInput
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.attributes[0].can_edit"
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
            <div class="grid grid-cols-1 gap-9">
                <div class="flex flex-col p-6.5 pb-0">
                    <div class="flex flex-col">
                        <label class="block font-medium text-black mb-2.5">Description</label>
                        <CKEditorComponent
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.attributes[0].can_edit"
                            :model="form.description"
                            @updateValue="(value) => {
                                form.description = value
                            }"
                        />
                    </div>
                </div>
            </div>
        </template>
        <hr class="text-gray mt-6.5">
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.attributes[0].can_delete && !form.attribute_variants_pivot_count">
                        <CustomButton
                            v-if="emitAction === 'update' && (form.language_id == -1 || form.translation_id)"
                            @click="store.commit('attribute/SET_DELETE_MODAL_VALUE', {
                                  value: true,
                                    id: form.language_id == -1 ? form.id : form.translation_id,
                                    deletingActionApi: form.language_id == -1 ? 'delete' : 'delete-translation',
                                    deletingText: form.language_id == -1 ? null : 'Deleting this translation will be permanent and cannot be undone. Once deleted, you will not be able to restore it.',
                                });"
                            class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'trash']" />  Delete
                        </CustomButton>
                    </template>

                    <template v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.attributes[0].can_edit">
                        <CustomButton
                            class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="submit"
                        >
                            <font-awesome-icon :icon="['far', 'floppy-disk']" /> Save
                        </CustomButton>
                    </template>
                </div>
            </div>
        </div>
    </form>
</template>
