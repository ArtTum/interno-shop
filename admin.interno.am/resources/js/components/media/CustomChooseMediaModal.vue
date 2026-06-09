<script setup>
import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import FileDropZone from "@components/global/FileDropZone.vue";
import CustomForm from "@components/media/CustomForm.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import TableActions from "@components/global/TableActions.vue";

const modalOpen = computed(() => store.getters[props.getterVariable]);
const media = computed(() => store.getters['media/getEditData']);
const mode = computed(() => store.getters['media/getMediaMode']);
const auth = computed(() => store.getters['auth/getUser']);
const mediaPermission = computed(() => auth.value?.user_group?.permissions_by_name?.medias?.[0] || {});

const getAdminBaseUrl = computed(() => store.getters['general/getAdminBaseUrl']);

import {useStore} from "vuex";
import {computed, reactive, ref, watch} from "vue";
import {useRoute} from "vue-router";

const store = useStore();
const route = useRoute();

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
    mediaTypes: {
        type: Array,
        default: []
    },
    languageId: {
        type: [String, Number],
        default: null,
    },
})

const form = reactive({
    media_id: media.value.id,
    id: media.value.media_translation?.id,
    language_id: (props.languageId ? props.languageId : media.value.language_id),

    alt: media.value?.media_translation?.alt,
    path: media.value.path,
    thumbnail: media.value.thumbnail,
    original_path: media.value.original_path,
    selectedMediaIds: [],
    mediaFiles: [],
    bulkSelect: true
});

const dataParams = async (id) => {
    await store.dispatch('media/fetchParams', {id:id});
}
const vendorKey = localStorage.getItem('vendor_key');
watch(media, (newMedia) => {
    if (form.mediaFiles.length) {
        form.media_id = newMedia?.id;
        form.language_id = newMedia?.language_id;
        form.id = newMedia?.media_translation?.id;
        form.alt = newMedia?.media_translation?.alt;
        form.path = getAdminBaseUrl.value + '/uploads/' + vendorKey + '/' + newMedia.type + '/maximum' + newMedia.path;
        form.thumbnail = getAdminBaseUrl.value + '/uploads/' + vendorKey + '/' + newMedia.type + '/thumbnail' + newMedia.path;
        form.original_path = getAdminBaseUrl.value + newMedia.original_path;
    }

    dataParams(newMedia?.id)
}, {immediate: true});

const getParams = computed(() => store.getters['media/getParams']);

const copyPath = (type) => {
    const input = document.createElement('input');
    input.value = form[type];
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

const params = ref({
    page: Number(route.query.page) || 1,
    per_page: Number(route.query.per_page) || 48,
    search: route.query.search || '',
    type: route.query.type || -1,
    year: route.query.year || -1,
    month: route.query.month || -1,
    mediaTypes: props.mediaTypes || [],
    language_id: props.languageId || '',
});

const fetchPageData = async () => {
    await store.dispatch('media/fetchPageData', params.value);
};
fetchPageData();

const doPageFetching = async (isPagination = false) => {
    if (!isPagination) {
        params.value.page = 1;
    }
    await fetchPageData();
};

form.data = computed(() => store.getters['media/getPageData']);

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'});
};
const emitChanges = () => {
    form.showUpload = true;
};

const submit = async () => {
    try {
        await store.dispatch('media/update', form);
        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully updated'
        });
        fetchPageData();
    } catch (error) {
        form.errors = error;
    }
};

const mediaTypes = [
    {value: -1, label: 'All'},
    {value: 'images', label: 'Images'},
    {value: 'videos', label: 'Videos'},
    {value: 'files', label: 'Files'},
];

const months = [
    {value: -1, label: 'All'},
    {value: 1, label: 'January'},
    {value: 2, label: 'February'},
    {value: 3, label: 'March'},
    {value: 4, label: 'April'},
    {value: 5, label: 'May'},
    {value: 6, label: 'June'},
    {value: 7, label: 'July'},
    {value: 8, label: 'August'},
    {value: 9, label: 'September'},
    {value: 10, label: 'October'},
    {value: 11, label: 'November'},
    {value: 12, label: 'December'},
];
const currentYear = 2023;
const years = ref([{value: -1, label: 'All'}]);

for (let year = currentYear; year <= new Date().getFullYear(); year++) {
    years.value.push(year);
}

const emits = defineEmits([
    'insert'
])

const closeMedia = () => {
    store.commit(props.mutationVariable, {
        value: false,
        media: []
    })
    form.mediaFiles = [];
    form.selectedMediaIds = [];
};

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

const insertFiles = () => {
    emits('insert', {media: form.mediaFiles, mode: mode, language_id: form.language_id});
    form.mediaFiles = [];
    form.selectedMediaIds = [];
};

const searchNotUploaded = async (value) => {
    params.value.search = value
    await store.dispatch('media/fetchPageData', params.value);
};

</script>

<template>
    <div
        v-show="modalOpen"
        class="fixed  top-0 left-0 z-999999 flex h-full min-h-screen w-full items-center justify-center bg-black/90 px-4 py-5"
    >
        <div
            ref="target"
            class="w-full h-full relative rounded-lg bg-white py-12 px-8 md:py-15 md:px-17.5  overflow-y-auto"
        >
            <button
                type="button"
                @click="closeMedia"
                class="absolute top-6 right-6 flex h-7 w-7 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white hover:text-primary"
            >
                <font-awesome-icon :icon="['fas', 'xmark']" class="size-6 text-black"/>
            </button>

            <div class="grid grid-cols-5 gap-9">
                <div class="col-span-4  ">
                    <TableActions
                        class="w-full"
                        :showFilter="true"
                        buttonName="Add new media file"
                        @emit-changes="emitChanges"
                        @applyFilters="doPageFetching"
                        :filter-menu-initial-value="true"
                    >
                        <div class="grid grid-cols-4 gap-9">
                            <div class="flex flex-col p-2.5">
                                <CustomSelect
                                    v-model="params.type"
                                    mode="single"
                                    label="Media type"
                                    :options="mediaTypes"
                                    :searchable="false"
                                    :canClear="false"
                                />
                            </div>
                            <div class="flex flex-col p-2.5">
                                <CustomSelect
                                    :options="years"
                                    label="Media year"
                                    v-model="params.year"
                                    :searchable="false"
                                    :canClear="false"
                                />
                            </div>
                            <div class="flex flex-col p-2.5">
                                <CustomSelect
                                    v-if="params.year > 0"
                                    :options="months"
                                    label="Media month"
                                    v-model="params.month"
                                    :searchable="false"
                                    :canClear="false"
                                />
                            </div>

                            <div class="flex flex-col p-2.5">
                                <CustomInput
                                    v-model="params.search"
                                    name="search"
                                    type="text"
                                    label="Search media"
                                    :tableInput="true"
                                    placeholder="Search ..."
                                />
                            </div>
                        </div>

                    </TableActions>
                    <div class="grid grid-cols-1 gap-9">
                        <div class="relative" v-if="form.showUpload">
                            <button
                                type="button"
                                @click="form.showUpload = false"
                                class="absolute top-10 right-12 text-gray-500 hover:text-gray-700 focus:outline-none"
                            >
                                <span class="sr-only">Close</span>
                                <font-awesome-icon :icon="['fas', 'xmark']" class="size-6"/>
                            </button>
                            <FileDropZone
                                @update="fetchPageData()"
                                @searchNotUploaded="searchNotUploaded"
                            />
                        </div>
                    </div>

                    <CustomForm
                        @do-page-fetching="doPageFetching"
                        :form="form"
                        v-model="params"
                        :mode="mode"
                        :languageId="props.languageId"
                    />
                </div>

                <div v-if="form.mediaFiles.length" class="col-span-1 text-sm">
                    <div class="" v-if="media.type === 'images'">
                        <img class="w-full" :src="media.path ? form.thumbnail : media.original_path" alt="Image">
                    </div>
                    <video v-else-if="media.type === 'videos'" controls width="100%" :key="media.original_path">
                        <source :src="media.original_path" :type="media.file_type">
                        Your browser does not support the video tag.
                    </video>

                    <font-awesome-icon v-else-if="media.type === 'files'" :icon="['far', 'file-invoice']" size="4x"/>
                    <p class="mt-3">Uploaded on: {{ formatDate(media.created_at) }}</p>
                    <p>Uploaded by: {{ media.name }} {{ media.last_name }}</p>
                    <p>File name: {{ media.file_name }}</p>
                    <p>File type: {{ media.file_type }}</p>
                    <p>File size: {{ media.file_size }} KB</p>
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
                        class="mb-0"
                    />
                    <CustomInput
                        :disabled="true"
                        v-if="media.path"
                        :datafld="true"
                        label="File Url"
                        v-model="form.path"
                    >
                        <customButton type="button" @click="copyPath('path')">
                            <font-awesome-icon :icon="['fas', 'copy']" class="size-5"/>
                        </customButton>
                    </CustomInput>
                    <CustomInput
                        :disabled="true"
                        label="Original file Url"
                        v-model="form.original_path"
                    >
                        <customButton type="button" @click="copyPath('original_path')">
                            <font-awesome-icon :icon="['fas', 'copy']" class="size-5"/>
                        </customButton>
                    </CustomInput>
                    <div class="grid-cols-1 flex gap-5">
                        <template v-if="auth?.superadmin || mediaPermission.can_delete">
                            <CustomButton
                                @click="store.commit('media/SET_DELETE_MODAL_VALUE', {
                                    value: true,
                                    id: form.media_id
                                });"
                                type="button"
                                class="ml-auto w-25 rounded border border-meta-1 bg-meta-1 py-2 px-4.5 text-center font-medium text-white hover:bg-opacity-90"
                            >
                                <font-awesome-icon :icon="['far', 'trash']"/>
                                Delete
                            </CustomButton>
                        </template>
                        <template v-if="auth?.superadmin || mediaPermission.can_edit">
                            <CustomButton
                                type="button"
                                @click.prevent="submit"
                                class="items-center w-25 gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            >
                                <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                                Save
                            </CustomButton>
                        </template>
                    </div>
                    <div class="grid-cols-1 mt-5 flex gap-5">
                        <CustomButton
                            type="button"
                            :disabled="!form.mediaFiles.length"
                            @click="insertFiles"
                            class="ml-auto items-center w-25 gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                        >
                            <font-awesome-icon :icon="['fas', 'upload']"/>
                            Insert
                        </CustomButton>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
