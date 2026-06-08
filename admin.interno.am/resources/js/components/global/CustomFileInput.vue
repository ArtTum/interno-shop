<script setup>
import {ref, watch} from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    name: String,
    error: String,
    multiple: {
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
});

const value = ref(props.modelValue);
const emits = defineEmits([
    'update:modelValue'
]);

watch(value, () => {
    emits('update:modelValue', value.value)
});
</script>

<template>
    <div class="mb-4">
        <label class="mb-2.5 block font-medium text-black">
            {{ label }}
        </label>
        <div class="relative">
            <input
                name="name"
                type="file"
                :multiple="multiple"
                :disabled="disabled"
                :class="{ 'is-invalid': error }"
                class="w-full rounded-md border border-stroke p-3 outline-none transition file:mr-4 file:rounded file:border-[0.5px] file:border-stroke file:bg-[#EEEEEE] file:py-1 file:px-2.5 file:text-sm file:font-normal focus:border-primary file:focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter"
            />
            <div v-if="error" class="invalid-feedback">
                <span>{{ error }}</span>
            </div>
            <span
                class="absolute right-4 top-4"
                :class="{ 'is-invalid': error }"
            >
                <slot></slot>
            </span>
        </div>
    </div>
</template>
