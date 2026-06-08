<script setup>

import {computed, ref, toRefs, watch} from "vue";
import CustomMediaList from "@components/media/CustomMediaList.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

const images = ref([])

const emits = defineEmits([
    'update:modelValue',
    'save'
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

const desktopPerRaw = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
const tabletOrMobilePerRaw = [1, 2, 3, 4, 5, 6];

const {modelValue} = toRefs(props);
const component = ref(modelValue.value);

watch(modelValue, (newVal) => {
    component.value = newVal;
    if (Object.keys(component.value.config).length === 0) {
        component.value.config = {}
    }

    if (component.value.config.desktop_per_raw === undefined) component.value.config.desktop_per_raw = 6;
    if (component.value.config.tablet_per_raw === undefined) component.value.config.tablet_per_raw = 3;
    if (component.value.config.mobile_per_raw === undefined) component.value.config.mobile_per_raw = 2;
}, {immediate: true});

const removeSingleGallery = (id) => {
    if (id) {
        component.value.items = component.value.items.filter((u) => u.id !== id);
    }
}

watch(
    () => component.value,
    (newVal) => {
        emits('update:modelValue', newVal);
    },
    {deep: true}
);

const insert = (data) => {
    data.media.forEach(media => {
        if (media.id) {
            component.value.items.push({
                id: '',
                media_id: media.id,
                path: media.original_path,
                type: media.type,
                file_type: media.file_type,
                video_type: '',
                video_url: '',
            });
        }
    });
}

</script>

<template>
    <div class="grid grid-cols-3 gap-9 text-left mt-5">
        <div class="flex flex-col gap-9">
            <CustomSelect
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                label="Images per raw: Desktop"
                v-model="component.config.desktop_per_raw"
                mode="single"
                placeholder="Select"
                :options="desktopPerRaw"
                class="py-2 rounded-lg border-stroke bg-transparent"
            />
        </div>
        <div class="flex flex-col gap-9">
            <CustomSelect
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                label="Images per raw: Tablet"
                v-model="component.config.tablet_per_raw"
                mode="single"
                placeholder="Select"
                :options="tabletOrMobilePerRaw"
                class="py-2 rounded-lg border-stroke bg-transparent"
            />
        </div>
        <div class="flex flex-col gap-9">
            <CustomSelect
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                label="Images per raw: Mobile"
                v-model="component.config.mobile_per_raw"
                mode="single"
                placeholder="Select"
                :options="tabletOrMobilePerRaw"
                class="py-2 rounded-lg border-stroke bg-transparent"
            />
        </div>
    </div>
    <div class="grid grid-cols-1 gap-9 text-left mt-5">
        <div class="flex flex-col gap-9">
            <CustomMediaList
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                @remove-images="removeSingleGallery"
                label="Images *"
                @insert="insert"
                :images="component.items ? component.items : []"
                :types="['images']"
            />
        </div>
    </div>
</template>

<style scoped>

</style>
