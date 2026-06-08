<script setup>

import {computed, ref, toRefs, watch} from "vue";

import {useStore} from "vuex";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";

const store = useStore();

const auth = computed(() => store.getters['auth/getUser']);

const emits = defineEmits([
    'update:modelValue',
    'save'
]);

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
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

    if (!component.value?.config || Object.keys(component.value?.config).length === 0) {
        component.value.config = {
            by_text_info: '',
            by_csv_info: '',
            download_catalog_info: '',
        }
    }
}, {immediate: true});
watch(
    () => component.value,
    (newVal) => {
        emits('update:modelValue', newVal);
    },
    {deep: true}
);

const activeTab = ref('by_text_info');

const tabsRoutes = [
    {key: 'by_text_info', title: 'Info of by text', icon: ['far', 'file-lines']},
    {key: 'by_csv_info', title: 'Info of by csv', icon: ['far', 'file-csv']},
    {key: 'download_catalog_info', title: 'Info of download catalog', icon: ['far', 'file-export']},
];

</script>

<template>
    <div class="grid grid-cols-1">
        <div class="w-full">
            <div class="mb-6 flex flex-wrap gap-5 border-b border-stroke">
                <template
                    :key="key"
                    v-for="(tabRoute, key) in tabsRoutes"
                >
                    <router-link
                        to=""
                        @click="activeTab = tabRoute.key"
                        :class="{
                                    'text-primary border-primary': activeTab === tabRoute.key,
                                    'border-transparent': activeTab !== tabRoute.key
                                }"
                        class="border-b-2 py-4 text-sm font-medium hover:text-primary md:text-base px-2"
                    >
                        <font-awesome-icon :icon="tabRoute.icon"/>
                        {{ tabRoute.title }}
                    </router-link>
                </template>
            </div>
            <div v-if="activeTab === 'by_text_info'">
                <div class="grid grid-cols-1 gap-9 text-left mt-5">
                    <div class="flex flex-col gap-9">
                        <CKEditorComponent
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            :model="component.config.by_text_info"
                            @updateValue="(value) => {
                                console.log(98877);
                                component.config.by_text_info = value
                                console.log(component.config.by_text_info);
                            }"
                        />
                    </div>
                </div>
            </div>
            <div v-else-if="activeTab === 'by_csv_info'">
                <div class="grid grid-cols-1 gap-9 text-left mt-5">
                    <div class="flex flex-col gap-9">
                        <CKEditorComponent
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            :model="component.config.by_csv_info"
                            @updateValue="(value) => {
                                component.config.by_csv_info = value
                            }"
                        />
                    </div>
                </div>
            </div>
            <div v-else-if="activeTab === 'download_catalog_info'">
                <div class="grid grid-cols-1 gap-9 text-left mt-5">
                    <div class="flex flex-col gap-9">
                        <CKEditorComponent
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            :model="component.config.download_catalog_info"
                            @updateValue="(value) => {
                                component.config.download_catalog_info = value
                            }"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
