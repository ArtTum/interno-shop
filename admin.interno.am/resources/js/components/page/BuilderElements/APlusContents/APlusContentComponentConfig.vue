<script setup>

import {computed, ref, toRefs, watch} from "vue";
import CustomButton from "@components/global/CustomButton.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

const newItem = ref(null);
const emits = defineEmits([
    'update:modelValue',
    'save'
]);
import {useStore} from "vuex";
import APlusContentComponentItemDragAndDrop from "@components/page/BuilderElements/APlusContents/APlusContentComponentItemDragAndDrop.vue";

const store = useStore();

const auth = computed(() => store.getters['auth/getUser']);

const props = defineProps({
    modelValue: {
        type: Object,
        default: []
    },
    contents: {
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
    selfId: {
        type: [Number, String],
        default: null
    },
});

const {modelValue} = toRefs(props);
const component = ref(modelValue.value);

watch(modelValue, (newVal) => {
    component.value = newVal;
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
    <div class="flex justify-center">
        <div>
            <template v-if="!newItem">
                <CustomButton
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    @click="newItem = {page_translation_id: null}"
                    title="Add new item"
                    type="button"
                    class="h-[45px] flex items-center gap-2 rounded bg-meta-3 px-3.5 mt-5 font-medium text-white hover:bg-opacity-80 ml-auto"
                >
                    <font-awesome-icon :icon="'plus'" class="size-5"/>
                    New content
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

                    <template v-if="!newItem.page_translation_id">
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
        <div class="grid grid-cols-1 gap-9 text-left mt-5 px-6.5">
            <div class="flex flex-col gap-9">
                <template v-if="pageTypeName === 'a_plus_content'">
                    <CustomSelect
                        label="Related content"
                        v-model="newItem.page_translation_id"
                        mode="single"
                        :canClear="true"
                        :excluded-value="selfId"
                        placeholder="Select"
                        :searchable="true"
                        :options="contents"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                    />
                </template>
                <template v-else>
                    <CustomSelect
                        label="Related content"
                        v-model="newItem.page_translation_id"
                        mode="single"
                        :canClear="true"
                        placeholder="Select"
                        :searchable="true"
                        :options="contents"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                    />
                </template>
            </div>
        </div>
    </template>
    <template v-if="component.items">
        <APlusContentComponentItemDragAndDrop
            class="col-8"
            v-model="component.items"
            :contents="contents"
            :self-id="selfId"
            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
        />
    </template>
</template>

<style scoped>

</style>
