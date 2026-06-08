<script setup>

import {computed, ref, toRefs, watch} from "vue";
import CustomButton from "@components/global/CustomButton.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import CustomInput from "@components/global/CustomInput.vue";
import Switch from "@components/global/Switch.vue";

const newItem = ref(null);
const emits = defineEmits([
    'update:modelValue',
    'save'
]);
import {useStore} from "vuex";
import CategoryBlogComponentItemDragAndDrop
    from "@components/page/BuilderElements/CategoryBlog/CategoryBlogComponentItemDragAndDrop.vue";

const store = useStore();

const auth = computed(() => store.getters['auth/getUser']);
const productLimit = [1, 2, 3, 4, 5, 6, 8, 12, 16, 20, 24];
const desktopPerRaw = [1, 2, 3, 4, 5, 6, 8, 12];
const tabletOrMobilePerRaw = [1, 2, 3, 4, 6];

const props = defineProps({
    modelValue: {
        type: Object,
        default: []
    },
    categories: {
        type: Array,
        default: []
    },
    sectionShowingTypes: {
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
const component = ref(modelValue.value);

watch(modelValue, (newVal) => {
    component.value = newVal;

    if (Object.keys(component.value.config).length === 0) {
        component.value.config = {
            without_all: false,
            desktop_per_raw: 4,
            tablet_per_raw: 2,
            mobile_per_raw: 1,
            products_limit: 16,
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

</script>

<template>
    <div class="grid grid-cols-3 gap-9 text-left mt-5">
        <div class="flex flex-col gap-9">
            <CustomSelect
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                label="Post's limit *"
                v-model="component.config.products_limit"
                :searchable="true"
                mode="single"
                placeholder="Select"
                :options="productLimit"
                class="py-2 rounded-lg border-stroke bg-transparent"
            />
        </div>
        <div class="flex flex-col gap-9">
            <Switch
                @change="(value) => {
                    component.config.without_all = value;
                }"
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                :value="component.config.without_all"
                id="without_all"
                label="Without all"
            />
        </div>
    </div>
    <div class="grid grid-cols-4 gap-9 text-left mt-5">
        <div class="flex flex-col col-span-1 gap-9">
            <CustomSelect
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                label="Per raw: Medium desktop"
                v-model="component.config.desktop_per_raw"
                mode="single"
                placeholder="Select"
                :options="desktopPerRaw"
                class="py-2 rounded-lg border-stroke bg-transparent"
            />
        </div>
        <div class="flex flex-col col-span-1 gap-9">
            <CustomSelect
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                label="Per raw: Tablet"
                v-model="component.config.tablet_per_raw"
                mode="single"
                placeholder="Select"
                :options="tabletOrMobilePerRaw"
                class="py-2 rounded-lg border-stroke bg-transparent"
            />
        </div>
        <div class="flex flex-col col-span-1 gap-9">
            <CustomSelect
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                label="Per raw: Mobile"
                v-model="component.config.mobile_per_raw"
                mode="single"
                placeholder="Select"
                :options="tabletOrMobilePerRaw"
                class="py-2 rounded-lg border-stroke bg-transparent"
            />
        </div>
    </div>
    <div class="flex justify-center">
        <div>
            <template v-if="!newItem">
                <CustomButton
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    @click="newItem = {post_category_translation_id: null, title: null}"
                    title="Add new item"
                    type="button"
                    class="h-[45px] flex items-center gap-2 rounded bg-meta-3 px-3.5 mt-5 font-medium text-white hover:bg-opacity-80 ml-auto"
                >
                    <font-awesome-icon :icon="'plus'" class="size-5"/>
                    New category
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

                    <template v-if="!newItem.post_category_translation_id || !newItem.title">
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
        <div class="grid grid-cols-2 gap-9 text-left mt-5 px-6.5">
            <div class="flex flex-col gap-9">
                <CustomSelect
                    label="Related category"
                    v-model="newItem.post_category_translation_id"
                    mode="single"
                    :canClear="true"
                    :searchable="true"
                    placeholder="Select"
                    :options="categories"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                />
            </div>
            <div class="flex flex-col gap-9">
                <CustomInput
                    v-model="newItem.title"
                    name="title"
                    label="Title"
                    type="text"
                    placeholder="Title"
                />
            </div>
        </div>
    </template>
    <template v-if="component.items">
        <CategoryBlogComponentItemDragAndDrop
            class="col-8"
            v-model="component.items"
            :categories="categories"
            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
        />
    </template>
</template>

<style scoped>

</style>
