<script setup>
import {ref, watch, toRefs, onMounted} from 'vue';

const props = defineProps({
    modelValue: [String, Number, Boolean, Array],
    label: String,
    name: String,
    type: String,
    placeholder: String,
    options: {
        type: Array,
    },
    disabled: {
        type: Boolean,
        default: false
    },
    error: {
        type: [String, Array],
        required: false
    },
    invalidFeedbackPlace: {
        type: Boolean,
        default: true
    },
});

const {modelValue, options} = toRefs(props);
const value = ref(modelValue.value);

// Set the default checked value to the first option if no modelValue is provided
onMounted(() => {
    if (!modelValue.value && options.value.length > 0) {
        value.value = options.value[0].value;
    }
});

watch(modelValue, (newVal) => {
    value.value = newVal;
});

const emits = defineEmits(['update:modelValue', 'change']);

watch(value, (newVal) => {
    emits('update:modelValue', newVal);
});
</script>

<template>
    <template v-for="(option, i) in options" :key="option.value">
        <label class="flex cursor-pointer select-none items-center">
            <span class="relative" :class="{'disabled': disabled}">
                <input
                    @input="(value) => emits('change', value.target.checked)"
                    :name="name"
                    v-model="value"
                    type="radio"
                    :placeholder="placeholder"
                    :disabled="disabled"
                    :value="option.value"
                    class="sr-only"
                    :class="{
                        'is-invalid': error,
                        'disabled': disabled,
                    }"
                >
            </span>
            <span
                :class="[
                    'mr-4 flex h-6 w-6 items-center justify-center rounded-full border',
                    (Array.isArray(value) ? value.length > 0 : value) ? 'border-primary bg-gray' : ''
                ]"
            >
                <font-awesome-icon icon="circle" :class="[option.value === value ? '!opacity-100' : 'opacity-0']"/>
            </span>
            {{ option.label }}
        </label>
    </template>
</template>
