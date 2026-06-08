<script setup>

import Switch from "@components/global/Switch.vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomFileInput from "@components/global/CustomFileInput.vue";

import {computed, ref, toRefs, watch} from "vue";

import {useStore} from "vuex";

const store = useStore()

import {validate} from "@validation/customValidation.js";

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    emitAction: {
        type: String
    }
});

const {modelValue} = toRefs(props);
const form = ref(modelValue.value);

const emits = defineEmits([
    'update:modelValue',
    'submit'
]);

watch(modelValue, (newVal) => {
    form.value = newVal;
});

watch(form.value, (newVal) => {
    emits('update:modelValue', newVal);
});

const auth = computed(() => store.getters['auth/getUser']);

</script>

<template>
    <form @submit.prevent="emits('submit')">
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 gap-6 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>
        <div class="grid grid-cols-2 gap-6  p-6 max-md:gap-4 max-md:p-4 max-sm:grid-cols-1">
            <div class="flex flex-col relative">
                <CustomInput
                    v-model="form.name"
                    name="name"
                    label="Name *"
                    type="text"
                    placeholder="Enter name"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['name']"
                />
                <div v-if="form.code" class="absolute right-[10px] top-[43px] text-0 overflow-hidden rounded-md max-md:top-[48px]">
                    <img
                        alt="icon"
                        width="50px"
                        class="max-sm:w-[40px]"
                        :src="`/flags/${form.code.toLowerCase()}.svg`"
                    >
                </div>
            </div>
            <div class="flex flex-col">
                <CustomInput
                    v-model="form.code"
                    name="code"
                    label="Code *"
                    type="text"
                    placeholder="Enter code"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['code']"
                />
            </div>
            <div class="flex flex-col">

            </div>
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">

                    <CustomButton
                        v-if="emitAction === 'update'"
                        @click="store.commit('language/SET_DELETE_MODAL_VALUE', {
                                    value: true,
                                    id: form.id
                                });"
                        class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                        type="button"
                    >
                        <font-awesome-icon :icon="['far', 'trash']"/>
                        Delete
                    </CustomButton>

                    <CustomButton
                        class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                        type="submit"
                    >
                        <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                        Save
                    </CustomButton>
                </div>

            </div>
        </div>

    </form>
</template>

<style scoped>

</style>
