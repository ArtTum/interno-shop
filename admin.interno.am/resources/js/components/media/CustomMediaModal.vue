<script setup>
import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import CustomCopyButton from "@components/global/CustomCopyButton.vue";

import {useStore} from "vuex";
import {computed, reactive, ref, watch} from "vue";
import {useRoute} from "vue-router";

const route = useRoute();
import CustomSelect from "@components/global/CustomSelect.vue";


const emits = defineEmits([
    'update',
]);

const store = useStore();
const props = defineProps({
    getterVariable: {
        type: String
    },
    getterVariableForText: {
        type: String,
        required: false
    },
    mutationVariable: {
        type: String
    },
    actionVariable: {
        type: String
    },
    medias: {
        type: Object
    },
})

const modalOpen = computed(() => store.getters[props.getterVariable]);
const media = computed(() => store.getters['media/getEditData']);
const auth = computed(() => store.getters['auth/getUser']);
const mediaPermission = computed(() => auth.value?.user_group?.permissions_by_name?.medias?.[0] || {});

const getAdminBaseUrl = computed(() => store.getters['general/getAdminBaseUrl']);

const form = reactive({
    media_id: media.value.id,
    id: media.value.media_translation?.id,
    language_id: media.value.language_id,
    alt: media.value?.media_translation?.alt,
    path: media.value.path,
    original_path: media.value.original_path,
});
const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'});
};
const vendorKey = localStorage.getItem('vendor_key');

watch(media, (newMedia) => {
    form.media_id = newMedia?.id;
    form.language_id = newMedia?.language_id;
    form.id = newMedia?.media_translation?.id;
    form.alt = newMedia?.media_translation?.alt;
    form.path = getAdminBaseUrl.value + '/uploads/' + vendorKey + '/' + newMedia?.type + '/maximum' + newMedia?.path;
    form.original_path = getAdminBaseUrl.value + newMedia?.original_path;

}, {immediate: true});

const getParams = computed(() => store.getters['media/getParams']);

const changeLanguage = async (languageId) => {
    const mediaData = await store.dispatch('media/fetchByField', {
        id: form.id,
        media_id: form.media_id,
        language_id: languageId,
    });

    if (mediaData) {
        form.id = mediaData.id;
        form.alt = mediaData.alt;
    } else {
        form.id = ''
        form.alt = ''
    }
}

const submit = async () => {
    try {
        await store.dispatch('media/update', form);
        emits('update')
        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully updated'
        });
    } catch (error) {
        form.errors = error;
    }
};

const fileIndex = ref(-1);

const updateMediaModal = (index) => {
    const myMedia = props.medias[index];
    if (!myMedia) return;

    store.commit('media/SET_MEDIA_MODAL_VALUE', {
        value: true,
        media: myMedia,
        baseLanguageId: form.language_id
    });
};

const findMediaIndex = (id) => {
    return props.medias.findIndex(file => file.id === parseInt(id));
};

const prevSlide = (id) => {
    if (id && fileIndex.value === -1) {
        fileIndex.value = findMediaIndex(id);
    }

    if (fileIndex.value < 0 || fileIndex.value === undefined) return;

    if (fileIndex.value === 0) {
        const page = Number(route.query.page) || 1;
        if (page > 1) {
            emits('update', 'prev');
            fileIndex.value = props.medias.length - 1;
        } else {
            return;
        }
    } else {
        fileIndex.value--;
    }

    updateMediaModal(fileIndex.value);
};

const nextSlide = (id) => {
    if (id && fileIndex.value === -1) {
        fileIndex.value = findMediaIndex(id);
    }

    if (fileIndex.value < 0 || fileIndex.value === undefined) return;

    if (fileIndex.value >= props.medias.length - 1) {
        emits('update', 'next');
        fileIndex.value = 0;
    } else {
        fileIndex.value++;
    }

    updateMediaModal(fileIndex.value);
};

const fileInput = ref(null);
const selectedFile = ref(null);

const handleFileUpload = (event) => {
    const file = event.target.files[0];

    if (media.value.file_type !== file.type) {
        alert('File formats do not match. The new image format must be the same as the old one.');
        fileInput.value.value = '';
        selectedFile.value = null;
    }

    if (file && (media.value.file_type === file.type)) {
        selectedFile.value = file;
    }
};

const upload = async () => {
    if (!selectedFile.value) {
        alert('Please select a file first.');
        return;
    }

    const formData = new FormData();
    formData.append('file', selectedFile.value);
    formData.append('media_id', form.media_id);

    try {
        const uniqueId = Math.random().toString(36).substr(2, 9);
        await store.dispatch('media/replaceImage', formData);
        media.value.original_path = media.value.original_path + '?v=' + uniqueId
        media.value.path = media.value.path + '?v=' + uniqueId
        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'File uploaded successfully!'
        });

        fileInput.value.value = '';
        selectedFile.value = null;
    } catch (error) {
        console.error('Upload failed:', error);
    }
};

const isAiTranslateExpanded = ref(false);
const toggleAiTranslate = () => {
    isAiTranslateExpanded.value = !isAiTranslateExpanded.value;
};
const selectedLanguagesForTranslation = ref([]);

const generateAITranslations = async (isAll = false) => {
    await store.dispatch('media/translateAI', {
        translation_id: form.id,
        language_ids: isAll ? [] : selectedLanguagesForTranslation.value,
    })

    if (!isAll) selectedLanguagesForTranslation.value = [];

    store.commit('notification/SET_NOTIFICATION', {
        visible: true,
        title: 'Success',
        message: `Successfully submitted (Will work in background)`
    });
}
</script>

<template>
    <div
        v-show="modalOpen"
        class="fixed  top-0 left-0 z-999999 flex h-full min-h-screen w-full items-center justify-center bg-black/90 px-4 py-5"
    >
        <div
            v-if="media.id"
            ref="target"
            class="w-full h-full relative max-w-screen-2xl rounded-lg bg-white py-12 px-8 md:py-15 md:px-17.5 overflow-y-auto"
        >
            <button
                type="button"
                @click="store.commit(props.mutationVariable, {
                                    value: false,
                                    media: []
                                })"
                class="absolute top-6 right-6 flex h-7 w-7 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white hover:text-primary"
            >
                <font-awesome-icon :icon="['fas', 'xmark']" class="size-6 text-black"/>
            </button>

            <div class="grid grid-cols-3 gap-9">
                <div class="col-span-2 flex justify-center">
                    <div>
                        <div class="" v-if="media?.type === 'images'">
                            <img class="w-full" :src="media.original_path" alt="Image">
                        </div>
                        <video v-else-if="media?.type === 'videos'" controls width="100%" :key="media?.original_path">
                            <source :src="media?.original_path" :type="media?.file_type">
                            Your browser does not support the video tag.
                        </video>
                        <font-awesome-icon v-else-if="media?.type === 'files'" :icon="['far', 'file-invoice']"
                                           size="4x"/>
                        <div class="mt-5">
                            <label class="mb-2.5 block font-medium text-black">Replace image</label>
                            <input
                                ref="fileInput"
                                @change="handleFileUpload"
                                id="fileUpload"
                                type="file"
                                accept="image/*,video/*"
                                class="cursor-pointer border-[1.5px] border-stroke bg-transparent font-medium outline-none transition
                                 file:mr-5 file:border-collapse file:cursor-pointer file:border-0 file:border-r file:border-solid file:border-stroke
                                 file:bg-whiter file:py-3 file:px-5 file:hover:bg-primary file:hover:bg-opacity-10
                                 focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter"
                            />
                            <CustomButton
                                :disabled="!selectedFile"
                                @click.prevent="upload"
                                class="items-center gap-2 rounded-tr rounded-br bg-primary py-3.5 px-4.5 font-medium text-white hover:bg-opacity-80"
                            >
                                <font-awesome-icon :icon="['fass', 'upload']"/>
                                Upload
                            </CustomButton>
                        </div>
                    </div>
                </div>
                <div class="col-span-1 text-sm">
                    <div class="flex items-center justify-end">
                        <button
                            class="flex items-center justify-center w-10 h-10 bg-gray-200 rounded-full hover:bg-gray-300"
                            @click="prevSlide(media?.id)"
                        >
                            <font-awesome-icon :icon="['fas', 'chevron-left']" size="2x" class="text-gray-800"/>
                        </button>
                        <button
                            class="flex items-center justify-center w-10 h-10 bg-gray-200 rounded-full hover:bg-gray-300"
                            @click="nextSlide(media?.id)"
                        >
                            <font-awesome-icon :icon="['fas', 'chevron-right']" size="2x" class="text-gray-800"/>
                        </button>
                    </div>
                    <p class="text-black; font-bold">ID: #{{ media?.id }}
                        <CustomCopyButton :text="media?.id"/>
                    </p>
                    <p>Uploaded on: {{ formatDate(media?.created_at) }}</p>
                    <p>Uploaded by: {{ media?.name }} {{ media?.last_name }}</p>
                    <p>File name: {{ media?.file_name }}</p>
                    <p>File type: {{ media?.file_type }}</p>
                    <p>File size: {{ media?.file_size }} KB</p>
                    <p v-if="media.width && media.height">Dimensions: {{ media.width }} by {{ media.height }} pixels</p>
                    <hr class="mt-4 mb-4">
                    <CustomSelect
                        label="Languages"
                        v-model="form.language_id"
                        mode="single"
                        placeholder="Select languages"
                        :options="getParams.languages"
                        :searchable="true"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                        @change="changeLanguage"
                    />
                    <CustomInput
                        label="Alternative Text"
                        v-model="form.alt"
                        class="mb-0">
                        <CustomButton
                            type="button"
                            @click="toggleAiTranslate()"
                            class="flex items-center gap-2 rounded bg-primary p-2 font-medium text-white hover:bg-opacity-80"
                            v-if="(auth?.superadmin || mediaPermission.can_edit) && form.id && form.alt"
                        >
                            <font-awesome-icon :icon="['fas', 'robot']"/>
                        </CustomButton>
                    </CustomInput>
                    <div v-if="(auth?.superadmin || mediaPermission.can_edit) && form.id && form.alt && isAiTranslateExpanded">
                        <h2 class="text-black mt-2 font-bold">You can generate AI translations from this language to the
                            missing ones.</h2>
                        <div class="mt-2">
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
                            <div class="mt-2">
                                <CustomSelect
                                    v-model="selectedLanguagesForTranslation"
                                    mode="tags"
                                    placeholder="Select languages"

                                    :options="getParams.languages"
                                    :invalid-feedback-place="false"
                                    :close-on-select="false"
                                    :searchable="true"
                                    :with-general="false"
                                    class="py-2 rounded-lg border-stroke bg-transparent w-full"
                                />
                            </div>
                            <div class="mt-2">
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

                        </div>
                        <hr class="my-2">
                    </div>
                    <CustomInput
                        :disabled="true"
                        v-if="media.path"
                        :datafld="true"
                        label="File Url"
                        v-model="form.path"
                    >
                        <CustomCopyButton :text="form['path']"/>
                    </CustomInput>
                    <CustomInput
                        :disabled="true"
                        label="Original file Url"
                        v-model="form.original_path"
                    >
                        <CustomCopyButton :text="form['original_path']"/>
                    </CustomInput>
                </div>
            </div>

            <div class="grid-cols-1 flex gap-5">
                <template v-if="auth?.superadmin || mediaPermission.can_delete">
                    <CustomButton
                        @click="store.commit('media/SET_DELETE_MODAL_VALUE', {
                                    value: true,
                                    id: form.media_id
                                });"
                        type="button"
                        class="ml-auto w-fit rounded border border-meta-1 bg-meta-1 py-2 px-4.5 text-center font-medium text-white hover:bg-opacity-90"
                    >
                        <font-awesome-icon :icon="['far', 'trash']"/>
                        Delete
                    </CustomButton>
                </template>
                <template v-if="auth?.superadmin || mediaPermission.can_edit">
                    <CustomButton
                        @click.prevent="submit"
                        class="items-center gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                    >
                        <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                        Save
                    </CustomButton>
                </template>
            </div>

        </div>
    </div>
</template>
