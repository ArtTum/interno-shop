<script setup>

import {computed, ref, toRefs, watch} from "vue";
import CustomButton from "@components/global/CustomButton.vue";
import CustomMediaList from "@components/media/CustomMediaList.vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomTextarea from "@components/global/CustomTextarea.vue";
import SliderComponentItemDragAndDrop from "@components/page/SliderComponentItemDragAndDrop.vue";

const newItem = ref(null);
import {useStore} from "vuex";
const store = useStore();

const auth = computed(() => store.getters['auth/getUser']);

const removeSingleGallery = () => {
    newItem.value.media_id = null;
    newItem.value.images = [];
}

const emits = defineEmits([
    'update:modelValue',
    'save'
]);

const props = defineProps({
    modelValue: {
        type: Array,
        default: []
    },
    isUpdate: {
        type: Boolean,
        default: false
    },
    pageTypeName: {
        type: String,
    },
});

const {modelValue} = toRefs(props);
const items = ref(modelValue.value);

watch(modelValue, (newVal) => {
    items.value = newVal;
});

watch(
    () => items.value,
    (newVal) => {
        emits('update:modelValue', newVal);
    },
    {deep: true}
);

const mediaData = (media) => {
    return {
        id: '',
        media_id: media.id,
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
            newItem.value.media_id = media.id
            newItem.value.images = [mediaData(media)];
        }
    });
}

</script>

<template>
    <div class="flex justify-center">
        <div>
            <template v-if="!newItem">
                <CustomButton
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    @click="newItem = {title: null, description: null, url: null, button_text: null, media_id: null, images: []}"
                    title="Add new item"
                    type="button"
                    class="h-[45px] flex items-center gap-2 rounded bg-meta-3 px-3.5 mt-5 font-medium text-white hover:bg-opacity-80 ml-auto"
                >
                    <font-awesome-icon :icon="'plus'" class="size-5"/> New item
                </CustomButton>
            </template>
            <template v-else>
                <div class="flex">
                    <CustomButton
                        @click="newItem = null"
                        class="block w-full rounded border border-stroke bg-gray px-4.5 py-2.5 text-center font-medium text-black hover:bg-opacity-60"
                        type="button"
                    >
                        <font-awesome-icon :icon="['far', 'arrow-rotate-left']"/>
                        Cancel
                    </CustomButton>

                    <template v-if="!newItem.media_id">
                        <CustomButton
                            disabled
                            class="flex items-center gap-2 ml-3 rounded bg-primary px-4.5 py-2.5  font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                            Save
                        </CustomButton>
                    </template>
                    <template v-else>
                        <CustomButton
                            @click="emits('save', newItem), newItem = null"
                            class="flex items-center gap-2 ml-3 rounded bg-primary px-4.5 py-2.5  font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                            Save
                        </CustomButton>
                    </template>
                </div>
            </template>
        </div>
    </div>
    <template v-if="newItem">
        <div class="grid grid-cols-5 gap-9 text-left mt-5">
            <div class="flex flex-col col-span-2 gap-9">
                <CustomMediaList
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    @remove-images="removeSingleGallery"
                    label="Base image"
                    @insert="insert"
                    :images="newItem.images ? newItem.images : []"
                    :types="['images']"
                    mode="single"
                />
            </div>
            <div class="flex flex-col gap-9 col-span-2">
                <CustomInput
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    v-model="newItem.title"
                    name="title"
                    label="Title *"
                    type="text"
                    placeholder="Title"
                />
                <div>
                    <CustomInput
                        :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                        v-model="newItem.url"
                        name="url"
                        label="URL *"
                        type="text"
                        placeholder="URL"
                    />
                </div>
            </div>
            <div class="flex flex-col gap-9 col-span-1">
                <CustomInput
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    v-model="newItem.button_text"
                    name="button_text"
                    label="Button text"
                    type="text"
                    placeholder="Button text"
                />
            </div>
        </div>
        <div class="grid grid-cols-1 text-left gap-9">
            <div class="flex flex-col gap-9">
                <CustomTextarea
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    label="Description"
                    name="description"
                    :rows="4"
                    v-model="newItem.description"
                    placeholder="Description"
                />
            </div>
        </div>
    </template>
    <template v-if="items">
        <SliderComponentItemDragAndDrop
            class="col-8"
            v-model="items"
            :disabled="isUpdate && !auth.user_group.permissions_by_name.pages[0].can_edit"
        />
    </template>
</template>

<style scoped>

</style>
