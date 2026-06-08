<script setup>
import flatpickr from 'flatpickr'
import 'flatpickr/dist/flatpickr.css';
import {onMounted, ref, toRefs, watch} from 'vue'

const props = defineProps({
    placeholder: {
        type: String,
        default: 'mm/dd/yyyy'
    },
    label: {
        type: String,
        default: 'mm/dd/yyyy'
    },
    format: {
        type: String,
        default: 'M j, Y'
    },
    altFormat: {
        type: String,
        default: null
    },
    modelValue: [String, Number, Boolean],
    error: {
        type: [String, Array],
        required: false
    },
    tableInput: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
    mode: {
        type: String,
        default: 'single'
    },
    minDate: {
        default: false
    },
    value: {
        type: [String, Number],
        default: ''
    },
    hideLabelOnMobile: {
        type: Boolean,
        default: false
    },
    enableTime: {
        type: Boolean,
        default: false
    },
});


const value = ref(props.value || props.modelValue);
const {modelValue} = toRefs(props);
const inputRef = ref(null);

watch(modelValue, (newVal) => {
    value.value = newVal;
});

const emits = defineEmits(['update:modelValue']);

watch(value, (newVal) => {
    emits('update:modelValue', newVal);
});

onMounted(() => {
    // Init flatpickr
    flatpickr(inputRef.value, {
        mode: props.mode,
        static: true,
        monthSelectorType: 'static',
        dateFormat: props.format,
        altInput: !!props.altFormat,
        altFormat: props.altFormat || props.format,
        minDate: props.minDate,
        enableTime: props.enableTime,
        time_24hr: true,
        defaultDate: value.value || null,
        onChange: (selectedDates, dateStr) => {
            value.value = dateStr;
        },
        disable: [
            function (date) {
                if (props.minDate) {
                    return date.getDay() === 0 || date.getDay() === 6;
                }
            },
        ],
        prevArrow:
            '<svg class="fill-current" width="7" height="11" viewBox="0 0 7 11"><path d="M5.4 10.8l1.4-1.4-4-4 4-4L5.4 0 0 5.4z" /></svg>',
        nextArrow:
            '<svg class="fill-current" width="7" height="11" viewBox="0 0 7 11"><path d="M1.4 10.8L0 9.4l4-4-4-4L1.4 0l5.4 5.4z" /></svg>'
    })
})
</script>

<template>
    <div>
        <label v-if="label"
               :class="['mb-2.5', 'block', 'font-medium', 'text-black',hideLabelOnMobile? 'max-xsm:hidden':'']">{{
                label
            }}</label>
        <div class="relative">
            <input
                ref="inputRef"
                v-model="value"
                class="w-full rounded border-[1.5px] border-stroke bg-transparent px-5 font-normal outline-none transition focus:border-primary active:border-primary"
                :placeholder="placeholder"
                :disabled="disabled"
                data-class="flatpickr-right"
                :class="{
                    'is-invalid': error,
                    'py-2 bg-white': tableInput,
                    'py-4': !tableInput,
                }"
            />

            <div class="pointer-events-none absolute inset-0 right-5 left-auto flex items-center">
                <font-awesome-icon :icon="['far', 'calendar']"/>
            </div>
        </div>
        <div v-if="error" class="invalid-feedback">
            <span v-if="Array.isArray(error)">{{ error[0] }}</span>
            <span v-else>{{ error }}</span>
        </div>
    </div>
</template>
