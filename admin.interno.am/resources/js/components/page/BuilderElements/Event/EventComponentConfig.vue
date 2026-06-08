<script setup>

import {computed, ref, toRefs, watch} from "vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomDatePicker from "@components/global/CustomDatePicker.vue";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";

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
import {useStore} from "vuex";

const store = useStore();

const auth = computed(() => store.getters['auth/getUser']);

const {modelValue} = toRefs(props);

const items = ref(modelValue.value);

watch(modelValue, (newVal) => {
    items.value = newVal;

    if (!items.value.length) {
        items.value[0] = {
            description: '',
            tags: null,
            date: null,
            location: null,
            title: null,
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


</script>

<template>
    <div class="grid grid-cols-3 gap-9 text-left mt-5">
        <div class="flex flex-col pb-0">
            <CustomInput
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                v-model="items[0].title"
                name="title"
                label="Title *"
                type="text"
                placeholder="Title"
            />
        </div>
        <div class="flex flex-col pb-0">
            <CustomInput
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                v-model="items[0].location"
                name="location"
                label="Location *"
                type="text"
                placeholder="Location"
            />
        </div>
        <div class="flex flex-col pb-0">
            <CustomDatePicker
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                placeholder="yyyy/mm/dd"
                label="Date *"
                format="Y-m-d"
                v-model="items[0].date"
            />
        </div>
    </div>
    <div class="grid grid-cols-1 gap-9 text-left mt-5">
        <div class="flex flex-col pb-0">
            <CustomInput
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                v-model="items[0].tags"
                name="tags"
                label="Tags"
                type="text"
                placeholder="Example: Tag1, Tag2, Tag3, Tag4"
            />
        </div>
    </div>
    <div class="grid grid-cols-1 text-left gap-9">
        <div class="flex flex-col gap-9">
            <label class="block font-medium text-black">Description *</label>
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
