<script setup>
import {ref, onMounted} from 'vue';
import CustomButton from "@components/global/CustomButton.vue";
import CustomMediaVideoModal from "@components/media/CustomMediaVideoModal.vue";

import store from "@store";
import CustomChooseMediaModal from "@components/media/CustomChooseMediaModal.vue";
const modalOpen = ref(false);

const props = defineProps({
    images: {
        type: Array,
        required: true,
    },
    button: {
        type: Boolean,
        default: false,
    },
    label: String,
    types: {
        type: Array,
        default: [],
    },
    mode: {
        type: String,
        default: 'multiple',
    },
    videoUrl: {
        type: Boolean,
        default: false,
    },
    languageId: {
        default: null,
    },
    index: {
        default: null,
    },
});

let draggedItem = ref(null);

const onDragStart = (event, item) => {
    draggedItem.value = item;
    event.dataTransfer.effectAllowed = 'move';
};

const onDragOver = (event) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
};

const onDrop = (event, targetItem) => {
    event.preventDefault();
    if (draggedItem.value) {
        reorderItems(draggedItem.value, targetItem);
        draggedItem.value = null;
    }
};

const onDragEnd = () => {
    // emits('update:modelValue', params.value)
};

const reorderItems = (dragged, target) => {
    const indexDragged = props.images.indexOf(dragged);
    const indexTarget = props.images.indexOf(target);
    if (indexDragged > -1 && indexTarget > -1) {
        props.images.splice(indexDragged, 1);
        props.images.splice(indexTarget, 0, dragged);
    }
};

const removeImage = (id, key) => {
    emits('remove-images', id);
    props.images.splice(key, 1)
};

const videoModalOpen = ref(false);
const currentVideoMedia = ref(null);

const openVideoModal = (media) => {
    currentVideoMedia.value = media;
    videoModalOpen.value = true;
};

const videoData = (data) => {
    const idToUpdate = data.id;
    const item = props.images.find(item => item.id === idToUpdate);
    if (item) {
        item.video_url = data.video_url;
        item.video_type = data.video_type;
    }

    videoModalOpen.value = false;
    currentVideoMedia.value = null;
};

const emits = defineEmits([
    'remove-images',
    'insert',
])

const insert = (data) => {
    emits('insert', data);
    store.commit('media/SET_MEDIA_MODAL', {
        value: false,
        media: []
    })
}

onMounted(() => {
    if (!props.button) {
        const menu = document.querySelector('.menu');
        menu.addEventListener('dragover', (e) => e.preventDefault());
    }
});
</script>
<template>
    <div>
        <label v-if="label && !button" class="mb-2.5 block font-medium text-black">
            {{ label }}
        </label>
        <CustomButton
            v-if="button"
            @click="store.commit('media/SET_MEDIA_MODAL', {value: true, mode:mode}); modalOpen=true"
            type="button"
            class="flex w-fit  items-center gap-2 rounded border-black bg-meta-2 py-2 my-0 px-4.5 font-medium text-black hover:bg-opacity-80"
        >
            {{ label }}
        </CustomButton>
        <div v-else class="pl-6.5 border border-stroke rounded">
            <CustomButton
                @click="store.commit('media/SET_MEDIA_MODAL', {value: true, mode:mode, index: index}); modalOpen=true"
                type="button"
                class="flex w-fit  items-center gap-2 rounded border-black bg-meta-2 py-2 my-4 px-4.5 font-medium text-black hover:bg-opacity-80"
            >
                Choose file
            </CustomButton>
            <div class=" flex flex-wrap gap-4  menu-container">
                <div class="flex gap-5.5 menu" ref="menu">
                    <div
                        v-for="(media, key) in images"
                        :key="media.media_id"
                        class="menu-item relative w-40 border border-stroke rounded  p-2 flex justify-center items-center mb-6.5"
                        :draggable="true"
                        @dragstart="onDragStart($event, media)"
                        @dragover="onDragOver($event)"
                        @drop="onDrop($event, media)"
                        @dragend="onDragEnd"
                    >
                        <button
                            type="button"
                            @click="removeImage(media.id, key, mode)"
                            class="absolute z-1  top-0.5 right-0.5 text-red hover:text-gray-700 focus:outline-none"
                        >
                            <span class="sr-only">Close</span>
                            <font-awesome-icon :icon="['fas', 'xmark']" class="size-6"/>
                        </button>
                        <button
                            title="Add video url"
                            v-if="media.type === 'images' && videoUrl"
                            type="button"
                            @click="openVideoModal(media)"
                            class="absolute relative z-10 top-0.5 left-1.5 text-black hover:text-gray-700 focus:outline-none"
                        >
                            <span class="sr-only">Close</span>
                            <font-awesome-icon :icon="['fas', 'video']" class="size-6"/>
                        </button>
                        <div v-if="media.type === 'images'">
                            <img class="w-full" :src="media.path" alt="Image">
                        </div>
                        <video class="relative " v-else-if="media.type === 'videos'" controls width="100%">
                            <source :src="media.path" :type="media.file_type">
                            Your browser does not support the video tag.
                        </video>
                        <font-awesome-icon v-else-if="media.type === 'files'"
                                           :icon="['far', 'file-invoice']" size="4x"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <CustomMediaVideoModal
        v-model="videoModalOpen"
        :media="currentVideoMedia"
        @video-data="videoData"
        @close="videoModalOpen = false"
    />
    <CustomChooseMediaModal
        v-if="modalOpen"
        @insert="insert"
        getter-variable="media/getMediaModel"
        mutation-variable="media/SET_MEDIA_MODAL"
        :media-types="types"
        :languageId="props.languageId"
    />
</template>
<style scoped>
.menu-container {
    overflow-x: auto;
    white-space: nowrap;
}

.menu-item {
    background-color: #f9f9f9;
    cursor: move;
}

.menu-item.dragging {
    opacity: 0.5;
}
</style>
