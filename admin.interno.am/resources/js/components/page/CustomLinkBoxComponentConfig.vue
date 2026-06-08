<script setup>

import {computed, ref, toRefs, watch} from "vue";
import {useStore} from "vuex";
import CustomMediaList from "@components/media/CustomMediaList.vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomTextarea from "@components/global/CustomTextarea.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import Switch from "@components/global/Switch.vue";

const store = useStore();

const auth = computed(() => store.getters['auth/getUser']);

const removeSingleGallery = () => {
    items.value[0].media_id = null;
    items.value[0].images = [];
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
    if (!items.value.length) {
        items.value[0] = {
            title: null,
            description: null,
            design_option: 'option_1',
            url: null,
            button_text: null,
            media_id: null,
            is_full_height: false,
            images: [],
            desktop_aspect_ratio: 'default',
            mobile_aspect_ratio: 'default'
        }
    }
}, {immediate: true});

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

const designOptions = [
    {value: 'option_1', label: 'Option 1'},
    {value: 'option_2', label: 'Option 2'},
    {value: 'option_3', label: 'Option 3'},
    {value: 'option_4', label: 'Option 4'},
];

const AspectRatioOptions = [
    {value: 'default', label: 'Default'},
    {value: 'a-16-9', label: 'Landscape (16:9)'},
    {value: 'a-9-16', label: 'Portrait (9:16)'},
    {value: 'a-1-1', label: 'Square (1:1)'},
    {value: 'a-14-3', label: 'Banner (14:3)'},
];
</script>

<template>
    <template v-if="items.length">
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
                <CustomInput
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    v-model="items[0].title"
                    name="title"
                    label="Title *"
                    type="text"
                    placeholder="Title"
                />
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
        <div class="grid grid-cols-5 gap-9 text-left mt-5">
            <div class="flex flex-col gap-9 col-span-2">
                <CustomSelect
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    label="Design options"
                    v-model="items[0].design_option"
                    mode="single"
                    placeholder="Select"
                    :options="designOptions"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                />
            </div>
            <div class="flex flex-col gap-9 col-span-2">
                <CustomInput
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    v-model="items[0].url"
                    name="url"
                    label="URL *"
                    type="text"
                    placeholder="URL"
                />
            </div>
            <div>
                <Switch
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    @change="value => items[0].is_full_height = value"
                    :value="items[0].is_full_height"
                    :id="`is_full_height`"
                    label="Full height"
                />
            </div>
        </div>
        <div class="grid grid-cols-5 gap-9 text-left">
            <div class="flex flex-col gap-9 col-span-2">
                <CustomSelect
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    label="Desktop aspect ratio"
                    v-model="items[0].desktop_aspect_ratio"
                    mode="single"
                    placeholder="Select"
                    :options="AspectRatioOptions"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                />
            </div>
            <div class="flex flex-col gap-9 col-span-2">
                <CustomSelect
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    label="Mobile aspect ratio"
                    v-model="items[0].mobile_aspect_ratio"
                    mode="single"
                    placeholder="Select"
                    :options="AspectRatioOptions"
                    class="py-2 rounded-lg border-stroke bg-transparent"
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
                    v-model="items[0].description"
                    placeholder="Description"
                />
            </div>
        </div>
    </template>
</template>

<style scoped>

</style>
