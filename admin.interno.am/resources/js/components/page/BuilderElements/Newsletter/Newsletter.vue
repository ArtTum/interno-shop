<script setup>

import {computed, ref, toRefs, watch} from "vue";

import {useStore} from "vuex";
import CustomInput from "@components/global/CustomInput.vue";

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
            button_text: '',
            popup_title: '',
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
    <div class="grid grid-cols-1">
        <div class="w-full">
            <div class="grid grid-cols-2 gap-9 text-left mt-5">
                <div class="flex flex-col gap-9">
                    <CustomInput
                        :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                        v-model="component.config.button_text"
                        name="button_text"
                        label="Button text"
                        type="text"
                        placeholder="Button text"
                    />
                </div>
                <div class="flex flex-col gap-9">
                    <CustomInput
                        :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                        v-model="component.config.popup_title"
                        name="popup_title"
                        label="Popup title"
                        type="text"
                        placeholder="Popup title"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
