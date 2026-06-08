<script setup>

import CustomMediaModal from "@components/media/CustomMediaModal.vue";
import CustomButton from "@components/global/CustomButton.vue";
import DeleteModal from "@components/global/DeleteModal.vue";
import {reactive, ref} from "vue";
import {VueAwesomePaginate} from "vue-awesome-paginate";
import {useStore} from "vuex";

const store = useStore()
const props = defineProps({
    form: {
        type: Object
    },
    modelValue: {
        type: Object,
        required: true
    },
    mode: {
        type: String,
        default: 'multiple',
    },
    languageId: {
        default: null,
    },
});

const form = reactive(props.form)
const params = ref(props.modelValue)
const vendorKey = localStorage.getItem('vendor_key');
const emits = defineEmits([
    'update:modelValue',
    'do-page-fetching',
])

const mediaDelete = () => {
    store.commit('media/SET_MEDIA_MODAL_VALUE', {
        value: false,
        media: []
    });
    emits('do-page-fetching');
};
const toggleMediaId = (media) => {
    let id = media.id;
    if (form.selectedMediaIds.includes(id)) {
        form.mediaFiles = form.mediaFiles.filter(item => item.id  !== id);
        form.selectedMediaIds = form.selectedMediaIds.filter(mediaId => mediaId !== id);
    } else {
        if (props.mode === 'single') {
            form.selectedMediaIds = [id];
            form.mediaFiles = [media];
        } else {
            form.selectedMediaIds.push(id);
            form.mediaFiles.push(media);
        }
    }

    const filteredArray = form.mediaFiles[form.mediaFiles.length - 1];
    store.commit('media/SET_MEDIA_MODAL_VALUE', {value: false, media: filteredArray ? filteredArray : [], baseLanguageId: (props.languageId ? props.languageId : form.data.base_language_id) })
};

const openModal = async (media) => {
    if (!form.bulkSelect) {
        store.commit('media/SET_MEDIA_MODAL_VALUE', {
            value: true,
            media: media,
            baseLanguageId: props.languageId ? props.languageId : form.data.base_language_id,
        });
        await store.dispatch('media/fetchParams', { id: media.id });
    } else {
        toggleMediaId(media);
    }
};
const updatePage = (page = null) => {
    emits('do-page-fetching', page);
};


</script>

<template>
    <div  class="grid grid-cols-8 gap-9 p-6.5">
        <div :class="[form.selectedMediaIds.includes(media.id) ? 'border-4 border-meta-3 p-2' : 'border-1 border-grey p-2']"
            class="relative flex flex-col border border-cyan-90 justify-center items-center" v-for="media in form.data.data">
            <font-awesome-icon  v-if="form.selectedMediaIds.includes(media.id)" class=" text-green-600 size-5 absolute top-1 right-1"  :icon="['fass', 'check']" />
            <CustomButton
                :title="media.file_name"
                class="w-full h-full"
                type="button"
                @click="openModal(media)"
            >
                <div v-if="media.type === 'images' && media.path">
                    <img loading="lazy" :src="'/uploads/' + vendorKey+ '/' + media.type+'/thumbnail/' + media.path" width="100%" alt="Image">
                </div>
                <div  v-else-if="media.type === 'images' && !media.path" >
                    <img loading="lazy" :src="media.original_path" width="100%" alt="Image">
                </div>
                <div v-else-if="media.type === 'videos'">
                    <font-awesome-icon  :icon="['far', 'video']" size="4x" />
                    <div class="line-clamp-2 leading-none text-xs">
                        {{ media.file_name }}
                    </div>
                </div>
                <div v-else-if="media.type === 'files'">
                    <font-awesome-icon  :icon="['far', 'file-invoice']" size="4x" />
                    <div class="line-clamp-2 leading-none text-xs">
                        {{ media.file_name }}
                    </div>
                </div>

            </CustomButton>
        </div>
    </div>
    <div class="grid grid-cols-1 gap-9">
        <div class="flex flex-col  p-6.5">
            <div
                v-if="form.data.pagination"
                class="datatable-bottom"
            >
                <vue-awesome-paginate
                    v-if="params.per_page < form.data.pagination.total_items"
                    :total-items="form.data.pagination.total_items"
                    :items-per-page="params.per_page"
                    :max-pages-shown="3"
                    v-model="params.page"
                    @click="emits('do-page-fetching', true)"
                >
                    <template #prev-button>
                        <a class="flex h-9 w-9 items-center justify-center rounded-l-md border border-stroke hover:border-primary hover:bg-gray hover:text-primary">
                            <font-awesome-icon :icon="['fal', 'angle-left']" class="size-5"/>
                        </a>
                    </template>
                    <template #next-button>
                        <a class="flex h-9 w-9 items-center justify-center rounded-r-md border border-stroke border-l-transparent hover:border-primary hover:bg-gray hover:text-primary">
                            <font-awesome-icon :icon="['fal', 'angle-right']" class="size-5"/>
                        </a>
                    </template>
                </vue-awesome-paginate>
                <div class="datatable-info">
                    Showing {{ form.data.pagination.showing.from }} to {{ form.data.pagination.showing.to }} of
                    {{ form.data.pagination.total_items }} entries
                </div>

            </div>
        </div>
    </div>
    <CustomMediaModal
        getter-variable="media/getMediaModelValue"
        mutation-variable="media/SET_MEDIA_MODAL_VALUE"
        :change-route="true"
        :medias="form.data.data"
        @update="updatePage"
    />
    <DeleteModal
        @mediaDelete="mediaDelete"
        action-variable="media/delete"
        getter-variable="media/getDeleteModelValue"
        mutation-variable="media/SET_DELETE_MODAL_VALUE"
        parent-name="media_index"
    />
</template>

<style scoped>

</style>
