<script setup>

import {computed, ref, toRefs, watch} from "vue";
import CustomMediaList from "@components/media/CustomMediaList.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

const emits = defineEmits([
    'update:modelValue'
]);
import {useStore} from "vuex";
import CustomInput from "@components/global/CustomInput.vue";
import Switch from "@components/global/Switch.vue";

const store = useStore();

const auth = computed(() => store.getters['auth/getUser']);

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

const {modelValue} = toRefs(props);
const items = ref(modelValue.value);

watch(modelValue, (newVal) => {
    items.value = newVal;

    if (!items.value.length) {
        items.value[0] = {
            size: 'large',
            title: '',
            portrait_orientation: '',
            url: '',
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

const sizeStyles = [
    {value: 'maximum', label: 'Maximum'},
    {value: 'large', label: 'Large'},
    {value: 'medium', label: 'Medium'},
    {value: 'thumbnail', label: 'Thumbnail'}
];

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
            items.value[0].media_id = media.id
            items.value[0].images = [mediaData(media)];
        }
    });
}

const heightOption = [
    {value: '', label: 'None'},
    {value: 'a-16-9', label: 'Landscape (16:9)'},
    {value: 'a-9-16', label: 'Portrait (9:16)'},
];

</script>

<template>
    <template v-if="items.length">
        <div class="grid grid-cols-5 gap-9 text-left mt-5">
            <div class="flex flex-col col-span-2 gap-9">
                <CustomMediaList
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    @remove-images="removeSingleGallery"
                    label="Image *"
                    @insert="insert"
                    :images="items[0].images ? items[0].images : []"
                    :types="['images']"
                    mode="single"
                />
            </div>
            <div class="flex flex-col gap-9 col-span-2">
                <CustomSelect
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    label="Size *"
                    v-model="items[0].size"
                    mode="single"
                    placeholder="Select size"
                    :options="sizeStyles"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                />
            </div>
            <div class="flex flex-col gap-9 col-span-1">
                <CustomSelect
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    label="Aspect ratio *"
                    v-model="items[0].portrait_orientation"
                    mode="single"
                    placeholder="Select *"
                    :options="heightOption"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                />
            </div>
        </div>
        <div class="grid grid-cols-5 gap-9 text-left mt-5">
            <div class="flex flex-col col-span-2 gap-9">
                <CustomInput
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    v-model="items[0].title"
                    name="title"
                    label="Title"
                    type="text"
                    placeholder="Title"
                />
            </div>
            <div class="flex flex-col col-span-3 gap-9">
                <CustomInput
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    v-model="items[0].url"
                    name="url"
                    label="URL"
                    type="text"
                    placeholder="URL"
                />
            </div>
        </div>
    </template>
</template>

<style scoped>

</style>
