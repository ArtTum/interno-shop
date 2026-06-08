<script setup>

import {computed, ref, toRefs, watch} from "vue";

import {useStore} from "vuex";
import CustomInput from "@components/global/CustomInput.vue";
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
        <div class="text-left">
            <div>
                <CustomInput
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    v-model="component.config.email_subject"
                    name="email_subject"
                    label="Email Subject"
                    type="text"
                    placeholder="Email Subject"
                />
            </div>
            <div class="mb-8">
                <label class="block font-medium text-black mb-2.5">Email Body text</label>
                <CKEditorComponent
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    :model="component.config.email_body_text"
                    @updateValue="(value) => {
                                component.config.email_body_text = value
                            }"
                />
            </div>
            <div>
                <CustomInput
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    v-model="component.config.success_text"
                    name="success_text"
                    label="Success Text"
                    type="text"
                    placeholder="Success Text"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
