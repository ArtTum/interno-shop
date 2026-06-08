<script setup>

import {computed, ref, toRefs, watch} from "vue";
import CustomMediaList from "@components/media/CustomMediaList.vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

const emits = defineEmits([
    'update:modelValue'
]);
import {useStore} from "vuex";
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
            hide_buttons: false,
            height: 'a-16-9',
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
    {value: 'a-16-9', label: 'Landscape (16:9)'},
    {value: 'a-9-16', label: 'Portrait (9:16)'},
];

</script>

<template>
    <div class="grid grid-cols-5 gap-9 text-left mt-5">
        <div class="flex flex-col col-span-2 gap-9">
            <CustomMediaList
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                @remove-images="removeSingleGallery"
                label="Media *"
                @insert="insert"
                :images="items[0].images ? items[0].images : []"
                :types="['videos', 'images']"
                :videoUrl="true"
                mode="single"
            />
        </div>
        <div class="flex flex-col col-span-2 gap-9">
            <CustomSelect
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                label="Aspect ratio *"
                v-model="items[0].height"
                mode="single"
                placeholder="Select *"
                :options="heightOption"
                class="py-2 rounded-lg border-stroke bg-transparent"
            />
        </div>
        <div class="flex flex-col justify-center col-span-1 gap-9">
            <CustomInput
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                v-model="items[0].hide_buttons"
                name="hide_buttons"
                label="Hide buttons"
                type="checkbox"
            />
        </div>
    </div>
</template>

<style scoped>

</style>
