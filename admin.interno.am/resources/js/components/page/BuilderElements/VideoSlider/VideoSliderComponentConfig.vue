<script setup>

import {computed, ref, toRefs, watch} from "vue";
import CustomMediaList from "@components/media/CustomMediaList.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

const emits = defineEmits([
    'update:modelValue'
]);
import {useStore} from "vuex";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";
import CustomInput from "@components/global/CustomInput.vue";
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
            desktop_height: 'a-16-9',
            mobile_height: 'a-9-16',
            button_style: 'btn-primary',
        }
    }
}, {immediate: true});

const removeSingleGallery = () => {
    items.value[0].media_id = null;
    items.value[0].images = [];
}

const removeSingleMobileGallery = () => {
    items.value[0].mobile_images = [];
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

const insertMobile = (data) => {
    data.media.forEach(media => {
        if (media.id) {
            items.value[0].mobile_images = [mediaData(media)];
        }
    });
}

const heightOption = [
    {value: 'a-16-9', label: 'Landscape (16:9)'},
    {value: 'a-9-16', label: 'Portrait (9:16)'},
    {value: 'a-1-1', label: 'Square (1:1)'},
    {value: 'a-4-5', label: 'Square (4:5)'},
];

</script>

<template>
    <div class="grid grid-cols-2 gap-9 text-left mt-5">
        <div class="flex flex-col gap-9">
            <CustomMediaList
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                @remove-images="removeSingleGallery"
                label="Desktop video"
                @insert="insert"
                :images="items[0].images ? items[0].images : []"
                :types="['videos']"
                :videoUrl="true"
                mode="single"
            />
        </div>
        <div class="flex flex-col gap-9">
            <CustomSelect
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                label="Desktop aspect ratio"
                v-model="items[0].desktop_height"
                mode="single"
                placeholder="Select *"
                :options="heightOption"
                class="py-2 rounded-lg border-stroke bg-transparent"
            />
        </div>
    </div>
    <div class="grid grid-cols-2 gap-9 text-left mt-5">
        <div class="flex flex-col gap-9">
            <CustomMediaList
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                @remove-images="removeSingleMobileGallery"
                label="Mobile video"
                @insert="insertMobile"
                :images="items[0].mobile_images ? items[0].mobile_images : []"
                :types="['videos']"
                :videoUrl="true"
                mode="single"
            />
        </div>
        <div class="flex flex-col gap-9">
            <CustomSelect
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                label="Mobile aspect ratio"
                v-model="items[0].mobile_height"
                mode="single"
                placeholder="Select *"
                :options="heightOption"
                class="py-2 rounded-lg border-stroke bg-transparent"
            />
        </div>
    </div>
    <div class="grid grid-cols-4 gap-9 text-left mt-5">
        <div class="flex flex-col gap-9 col-span-2">
            <div>
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
        <div class="flex flex-col gap-9 col-span-1">
            <CustomSelect
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                label="Button style"
                v-model="items[0].button_style"
                mode="single"
                placeholder="Select"
                :options="[
                      {value: 'btn-primary', label: 'Primary (blue)'},
                      {value: 'btn-secondary', label: 'Secondary (white)'},
                    ]"
                class="py-2 rounded-lg border-stroke bg-transparent"
            />
        </div>
    </div>
    <div class="grid grid-cols-1 gap-9 text-left mt-5">
        <div class="flex flex-col">
                <label class="block font-medium text-black mb-2.5">Description</label>
                <CKEditorComponent
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    :model="items[0].description"
                    @updateValue="(value) => {
                       items[0].description = value
                    }"
                />
        </div>
    </div>
</template>

<style scoped>

</style>
