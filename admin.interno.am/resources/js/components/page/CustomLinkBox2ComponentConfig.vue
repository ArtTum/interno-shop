<script setup>

import {computed, ref, toRefs, watch} from "vue";
import CustomMediaList from "@components/media/CustomMediaList.vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomLinkBox2ComponentDragAndDrop from "@components/page/CustomLinkBox2ComponentDragAndDrop.vue";
import CustomButton from "@components/global/CustomButton.vue";

import {useStore} from "vuex";
const store = useStore();

const auth = computed(() => store.getters['auth/getUser']);

const emits = defineEmits([
    'update:modelValue',
    'save'
]);

const props = defineProps({
    modelValue: {
        type: Object,
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

const newItem = ref(null);
const {modelValue} = toRefs(props);
const items = ref(modelValue.value);

watch(modelValue, (newVal) => {
    items.value = newVal;
    if (!items.value.length) {
        items.value[0] = {
            url: null,
            button_text: null,
            media_id: null,
            images: [],
            links: []
        }
    }
}, {immediate: true});

const removeSingleGallery = () => {
    items.value[0].media_id = null;
    items.value[0].images = [];
}

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
        product_translation_id: '',
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
            items.value[0].media_id = media.id
            items.value[0].images = [mediaData(media)];
        }
    });
}

</script>

<template>
    <div class="grid grid-cols-5 gap-9 text-left mt-5">
        <div class="flex flex-col col-span-2 gap-9">
            <CustomMediaList
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                @remove-images="removeSingleGallery"
                label="Base image"
                @insert="insert"
                :images="items[0].images ? items[0].images : []"
                :types="['images']"
                mode="single"
            />
        </div>
        <div class="flex flex-col gap-9 col-span-2">
            <div>
                <CustomInput
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    v-model="items[0].url"
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
                v-model="items[0].button_text"
                name="button_text"
                label="Button text"
                type="text"
                placeholder="Button text"
            />
        </div>
    </div>
    <div class="flex justify-center">
        <div>
            <template v-if="!newItem">
                <CustomButton
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    @click="newItem = {title: null, url: null}"
                    title="Add new item"
                    type="button"
                    class="h-[45px] flex items-center gap-2 rounded bg-meta-3 px-3.5 mt-5 font-medium text-white hover:bg-opacity-80 ml-auto"
                >
                    <font-awesome-icon :icon="'plus'" class="size-5"/> New line
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

                    <template v-if="!newItem.title || !newItem.url">
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
                            @click="items[0].links.push(newItem), newItem = null"
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
        <div class="grid grid-cols-2 gap-9 text-left mt-5 px-6.5">
            <div class="flex flex-col gap-9">
                <CustomInput
                    v-model="newItem.title"
                    name="title"
                    label="Title *"
                    type="text"
                    placeholder="Title"
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                />
            </div>
            <div class="flex flex-col gap-9">
                <CustomInput
                    v-model="newItem.url"
                    name="url"
                    label="URL *"
                    type="text"
                    placeholder="URL"
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                />
            </div>
        </div>
    </template>
    <template v-if="items[0].links.length">
        <CustomLinkBox2ComponentDragAndDrop
            class="col-8"
            v-model="items[0].links"
            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
        />
    </template>
</template>

<style scoped>

</style>
