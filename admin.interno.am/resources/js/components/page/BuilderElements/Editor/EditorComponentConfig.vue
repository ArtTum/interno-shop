<script setup>

import {computed, ref, toRefs, watch} from "vue";
import CustomMediaList from "@components/media/CustomMediaList.vue";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";

const emits = defineEmits([
    'update:modelValue',
    'save'
]);
import {useStore} from "vuex";
import CustomSelect from "@components/global/CustomSelect.vue";
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

const component = ref(modelValue.value);

watch(modelValue, (newVal) => {
    component.value = newVal;

    if (Object.keys(component.value.config).length === 0) {
        component.value.config = {
            bg_color: '',
        }
    }

    if (!component.value.items.length) {
        component.value.items[0] = {
            text: ''
        }
    }
}, {immediate: true});

const colorOptions = [
    {value: '', label: 'None'},
    {value: 'light-bg', label: 'Light gray'},
    {value: 'bg-white', label: 'White'},
];

watch(
    () => component.value,
    (newVal) => {
        emits('update:modelValue', newVal);
    },
    {deep: true}
);

const getAdminBaseUrl = computed(() => store.getters['general/getAdminBaseUrl']);
const vendorKey = localStorage.getItem('vendor_key');
const insertShortDescription = (data) => {
    data.media.forEach(file => {
        store.dispatch('media/fetchByField', {
            media_id: file.id,
            language_id: data.language_id,
        }).then((mediaData) => {
            let alt = '';
            if (mediaData && mediaData.alt) {
                alt = mediaData.alt;
            } else {
                alt = file.file_name;
            }

            if (file.path) {
                component.value.items[0].text += `<img src="${getAdminBaseUrl.value}/uploads/${vendorKey}/images/large${file.path}" loading="lazy" alt="${alt}" title="${alt}" width="${file.width}" height="${file.height}" sizes="(max-width: 640px) 300px, (max-width: 1024) 640px, 1024px" srcset="${getAdminBaseUrl.value}/uploads/${vendorKey}/images/thumbnail${file.path} 300w, ${getAdminBaseUrl.value}/uploads/${vendorKey}/images/medium${file.path} 640w, ${getAdminBaseUrl.value}/uploads/${vendorKey}/images/large${file.path} 1024w"/><br>`;
            } else {
                component.value.items[0].text += `<img src="${getAdminBaseUrl.value}${file.original_path}" loading="lazy" alt="${alt}" title="${alt}" width="${file.width}" height="${file.height}"/><br>`;
            }
        });
    });
}

</script>

<template>
    <div>
        <div class="grid grid-cols-3 gap-9 text-left mt-5">
            <div class="flex flex-col gap-9">
                <CustomSelect
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    label="Background color"
                    v-model="component.config.bg_color"
                    mode="single"
                    placeholder="Select *"
                    :options="colorOptions"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                />
            </div>
        </div>
        <template v-if="component.items.length">
            <div class="grid grid-cols-1 gap-9 text-left mt-5">
                <div class="flex flex-col pb-0">
                    <label class="mb-2.5 block font-medium text-black">Compose</label>
                    <CustomMediaList
                        :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                        label="Insert media"
                        @insert="insertShortDescription"
                        :images="[]"
                        :types="['images']"
                        :button="true"
                    />
                    <CKEditorComponent
                        :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                        :model="component.items[0].text"
                        @updateValue="(value) => {
                        component.items[0].text = value
                    }"
                    />
                </div>
            </div>
        </template>
    </div>
</template>

<style scoped>

</style>
