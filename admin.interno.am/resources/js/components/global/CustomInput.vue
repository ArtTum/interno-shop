<script setup>
import {ref, watch, toRefs} from 'vue';
import {uniq} from "lodash";

const props = defineProps({
    modelValue: [String, Number, Boolean, Array],
    label: String,
    name: String,
    type: String,
    placeholder: String,
    valueCheckbox: {
        type: [Boolean, Number, Array],
    },
    disabled: {
        type: Boolean,
        default: false
    },
    error: {
        type: [String, Array],
        required: false
    },
    icon: {
        type: Boolean,
        default: false
    },
    tableInput: {
        type: Boolean,
        default: false
    },
    tooltip: {
        type: Boolean,
        default: false
    },
    invalidFeedbackPlace: {
        type: Boolean,
        default: true
    },
    hideLabelOnMobile: {
        type: Boolean,
        default: false
    },
});

const {modelValue} = toRefs(props);
const value = ref(modelValue.value);

watch(modelValue, (newVal) => {
    value.value = newVal;
});

const emits = defineEmits(['update:modelValue', 'change']);

watch(value, (newVal) => {
    emits('update:modelValue', newVal);
});
const generateUniqueId = () => {
    return 'input-' + Math.random().toString(36).substr(2, 9) + '-' + Date.now().toString(36);
};

let idAttr = generateUniqueId();
</script>

<template>
    <div v-if="type != 'checkbox'">
        <label v-if="label" :for="idAttr"
               :class="['mb-2.5', 'block', 'font-medium', 'text-black', hideLabelOnMobile? 'max-xsm:hidden':'']"
               v-html="label"></label>
        <div class="relative">
            <input
                :id="idAttr"
                :disabled="disabled"
                :name="name"
                v-model="value"
                :type="type"
                :placeholder="placeholder"
                :class="{
                    'is-invalid': error,
                    'pl-13': icon,
                    'pl-6': !icon,
                    'py-2 bg-white': tableInput,
                    'py-4': !tableInput,
                    'disabled': disabled,
                    'rounded-tl rounded-bl py-tooltip': tooltip,
                    'rounded-lg': !tooltip,
                }"
                class="w-full border border-stroke bg-transparent pr-6 outline-none focus:border-primary focus-visible:shadow-none text-black"
            />
            <div
                v-if="invalidFeedbackPlace && error"
                class="invalid-feedback"
            >
                <span v-if="Array.isArray(error)">{{ error[0] }}</span>
                <span v-else>{{ error }}</span>
            </div>
            <span
                class="absolute top-4 bg-white"
                :class="{ 'is-invalid': error, 'right-4': !icon, 'left-4': icon}"
            >
        <slot></slot>
      </span>
        </div>
    </div>
    <div v-else>
        <label class="flex cursor-pointer select-none items-center">
            <div class="relative" :class="{'disabled': disabled}">
                <input
                    @input="(value) => {
                       emits('change', value.target.checked)
                    }"
                    :min="1"
                    :name="name"
                    v-model="value"
                    :type="type"
                    :placeholder="placeholder"
                    :disabled="disabled"
                    :value="valueCheckbox"
                    type="checkbox"
                    class="sr-only"
                    :class="{
                        'is-invalid': error,
                        'pl-13': icon,
                        'pl-6': !icon,
                        'py-2 bg-white': tableInput,
                        'py-4 ': !tableInput,
                        'disabled': disabled,
                    }"
                >
                <div
                    :class="[
                    'mr-4 flex h-5 w-5 items-center justify-center rounded border',
                    (Array.isArray(value) ? value.length > 0 : value) ? 'border-primary bg-gray' : ''
                  ]"
                >
                    <span :class="[(Array.isArray(value) ? value.length > 0 : value) ? '!opacity-100' : 'opacity-0']">
                        <font-awesome-icon icon="check"/>
                    </span>
                </div>
            </div>
            {{ label }}
        </label>
    </div>
</template>
