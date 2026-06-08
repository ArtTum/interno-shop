<style scoped>
.item-container {
    margin: 0;
}
</style>

<template>
    <draggable
        :disabled="disabled"
        v-bind="dragOptions"
        tag="div"
        class="item-container"
        :list="realValue"
        @input="emitter"
        item-key="index"
    >
        <template #item="{ element, index }">
            <div
                v-if="!element.deleted"
                class="mt-3"
            >
                <div class="flex justify-end">
                    <div class="flex">
                        <router-link
                            title="Drag"
                            :to="''"
                            class="mr-1 flex items-center cursor-pointer w-[27px] h-[27px] bg-gray border border-stroke border-b-0 text-white text-center font-medium"
                        >
                            <font-awesome-icon :icon="['far', 'arrows-from-dotted-line']"
                                               class="w-full text-black mr-auto"/>
                        </router-link>
                        <router-link
                            @click="element.open = !element.open"
                            title="Open"
                            :to="''"
                            class="mr-1 flex items-center cursor-pointer w-[27px] h-[27px] bg-gray border border-stroke border-b-0 text-white text-center font-medium"
                        >
                            <font-awesome-icon
                                class="w-full text-black mr-auto"
                                :class="{ 'rotate-180': element.open }"
                                icon="chevron-down"
                            />
                        </router-link>
                        <router-link
                            @click="element.deleted = true"
                            title="Delete"
                            :to="''"
                            class="flex items-center cursor-pointer w-[27px] h-[27px] hover-border-white-trigger bg-gray border border-stroke border-r-0 border-b-0 text-white text-center font-medium hover:bg-danger hover:border-danger hover:text-white"
                        >
                            <font-awesome-icon :icon="['fas', 'xmark']" class="w-full text-black mr-auto"/>
                        </router-link>
                    </div>
                </div>
                <div class="border border-stroke rounded text-left">
                    <div class="bg-gray p-3">
                        Item #{{ index + 1 }}
                    </div>
                    <template v-if="element.open">
                        <div class="grid grid-cols-5 gap-9 text-left mt-5 px-5">
                            <div class="flex flex-col col-span-2 gap-9">
                                <CustomMediaList
                                    @remove-images="element.media_id = null, element.images = []"
                                    label="Base image"
                                    @insert="(data) => {
                                     data.media.forEach(media => {
                                            if (media.id) {
                                                element.media_id = media.id
                                                element.images = [this.mediaData(media)];
                                            }
                                        });
                                    }"
                                    :images="element.images ? element.images : []"
                                    :types="['images']"
                                    mode="single"
                                />
                            </div>
                            <div class="flex flex-col gap-9 col-span-2">
                                <CustomInput
                                    v-model="element.title"
                                    name="title"
                                    label="Title *"
                                    type="text"
                                    placeholder="Title"
                                />
                                <div>
                                    <CustomInput
                                        v-model="element.url"
                                        name="url"
                                        label="URL *"
                                        type="text"
                                        placeholder="URL"
                                    />
                                </div>
                            </div>
                            <div class="flex flex-col gap-9 col-span-1">
                                <CustomInput
                                    v-model="element.button_text"
                                    name="button_text"
                                    label="Button text"
                                    type="text"
                                    placeholder="Button text"
                                />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 text-left gap-9 px-5">
                            <div class="flex flex-col gap-9">
                                <CustomTextarea
                                    label="Description"
                                    name="description"
                                    :rows="4"
                                    v-model="element.description"
                                    placeholder="Description"
                                />
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </draggable>
</template>

<script>
import draggable from 'vuedraggable';
import RowSection from "@components/page/RowSection.vue";
import CustomTextarea from "@components/global/CustomTextarea.vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomMediaList from "@components/media/CustomMediaList.vue";

export default {
    name: "slider-component-item-drag-and-drop",
    methods: {
        emitter(value) {
            this.$emit("input", value);
        },
        mediaData(media) {
            return {
                id: '',
                media_id: media.id,
                product_translation_id: '',
                path: media.original_path,
                type: media.type,
                file_type: media.file_type,
                video_type: '',
                video_url: '',
            };
        },

    },
    components: {
        CustomMediaList,
        CustomInput, CustomTextarea,
        RowSection,
        draggable,
    },
    computed: {
        dragOptions() {
            return {
                animation: 0,
                group: "page_sliders",
                ghostClass: "ghost"
            };
        },
        realValue() {
            return this.value ? this.value : this.list;
        }
    },
    props: {
        value: {
            required: false,
            type: Array,
            default: null
        },
        list: {
            required: false,
            type: Array,
            default: null
        },
        parent: {
            type: Boolean,
            required: false,
            default: false
        },
        disabled: {
            type: Boolean,
            required: false
        },
    }
};
</script>
