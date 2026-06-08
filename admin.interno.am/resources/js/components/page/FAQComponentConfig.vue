<script setup>

import {computed, ref, toRefs, watch} from "vue";
import CustomButton from "@components/global/CustomButton.vue";
import CustomInput from "@components/global/CustomInput.vue";
import FAQComponentItemDragAndDrop from "@components/page/FAQComponentItemDragAndDrop.vue";

import {useStore} from "vuex";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";
const store = useStore();

const auth = computed(() => store.getters['auth/getUser']);

const newItem = ref(null);
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

</script>

<template>
    <div class="flex justify-center">
        <div>
            <template v-if="!newItem">
                <CustomButton
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    @click="newItem = {question: null, answer: null, set_as_opened: false}"
                    title="Add new item"
                    type="button"
                    class="h-[45px] flex items-center gap-2 rounded bg-meta-3 px-3.5 mt-5 font-medium text-white hover:bg-opacity-80 ml-auto"
                >
                    <font-awesome-icon :icon="'plus'" class="size-5"/>
                    New item
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

                    <template v-if="!newItem.question || !newItem.answer">
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
        <div class="grid grid-cols-5 gap-9 text-left mt-5 px-6.5">
            <div class="flex flex-col col-span-2 gap-9">
                <label class="block font-medium text-black">Question *</label>
                <CKEditorComponent
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    :model="newItem.question"
                    @updateValue="(value) => {
                        newItem.question = value
                    }"
                />
            </div>
            <div class="flex flex-col col-span-2 gap-9">
                <label class="block font-medium text-black">Answer *</label>
                <CKEditorComponent
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    :model="newItem.answer"
                    @updateValue="(value) => {
                        newItem.answer = value
                    }"
                />
            </div>
            <div class="flex flex-col justify-center col-span-1 gap-9">
                <CustomInput
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    v-model="newItem.set_as_opened"
                    name="set_as_opened"
                    label="Set as opened"
                    type="checkbox"
                />
            </div>
        </div>
    </template>
    <template v-if="items">
        <FAQComponentItemDragAndDrop
            class="col-8"
            v-model="items"
        />
    </template>
</template>

<style scoped>

</style>
