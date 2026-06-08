<script setup>

import {computed, ref, toRefs, watch} from "vue";
import CustomInput from "@components/global/CustomInput.vue";

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
import CustomTextarea from "@components/global/CustomTextarea.vue";
import Switch from "@components/global/Switch.vue";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";

const store = useStore();

const auth = computed(() => store.getters['auth/getUser']);

const {modelValue} = toRefs(props);

const items = ref(modelValue.value);

watch(modelValue, (newVal) => {
    items.value = newVal;

    if (!items.value.length) {
        items.value[0] = {
            title: null,
            info: null,
            popup_title: null,
            popup_text: null,
            primary: false
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
    <template v-if="items.length">
        <div class="grid grid-cols-7 gap-9 text-left mt-5">
            <div class="flex flex-col pb-0 col-span-2">
                <CustomInput
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    v-model="items[0].title"
                    name="title"
                    label="Title *"
                    type="text"
                    placeholder="Title"
                />
            </div>
            <div class="flex flex-col pb-0 col-span-2">
                <CustomInput
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    v-model="items[0].info"
                    name="info"
                    label="Amount *"
                    type="text"
                    placeholder="Amount"
                />
            </div>
            <div class="flex flex-col pb-0 col-span-2">
                <CustomInput
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    v-model="items[0].amount_symbol"
                    name="amount_symbol"
                    label="Amount symbol *"
                    type="text"
                    placeholder="Amount symbol"
                />
            </div>
            <div class="flex flex-col pb-0 col-span-1">
                <Switch
                    @change="(value) => {
                       items[0].primary = value;
                    }"
                    :value="items[0].primary"
                    id="is_primary"
                    label="Is primary"
                />
            </div>
        </div>
        <div class="grid grid-cols-3 gap-9 text-left mt-5">
            <div class="flex flex-col pb-0 col-span-1">
                <CustomInput
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    v-model="items[0].popup_title"
                    name="popup_title"
                    label="Popup title"
                    type="text"
                    placeholder="Popup title"
                />
            </div>
            <div class="flex flex-col pb-0 col-span-2">
                <label class="block font-medium text-black mb-2.5">Popup text</label>
                <CKEditorComponent
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    :model="items[0].popup_text"
                    @updateValue="(value) => {
                       items[0].popup_text = value
                    }"
                />
            </div>
        </div>
    </template>
</template>

<style scoped>

</style>
