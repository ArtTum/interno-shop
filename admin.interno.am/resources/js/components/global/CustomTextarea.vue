<script setup>
import {ref, watch, toRefs} from 'vue';

const props = defineProps({
    modelValue: [String, Number, Boolean],
    label: String,
    name: String,
    placeholder: String,
    error: {
        type: [String, Array],
        required: false
    },
    rows: {
        type: Number,
        required: false,
        default: 6
    },
    disabled: {
        type: Boolean,
        default: false
    }
});

const {modelValue} = toRefs(props);
const value = ref(modelValue.value);

watch(modelValue, (newVal) => {
    value.value = newVal;
});

const emits = defineEmits(['update:modelValue']);

watch(value, (newVal) => {
    emits('update:modelValue', newVal);
});
</script>

<template>
    <div :class="{'mb-4': true}">
        <label v-if="label" class="mb-2.5 block font-medium text-black" v-html="label"></label>
        <div class="relative">
           <textarea
               :name="name"
               v-model="value"
               :rows="rows"
               :disabled="disabled"
               :placeholder="placeholder"
               :class="{
                    'is-invalid': error,
                }"
               class="w-full rounded border-[1.5px] text-black border-stroke bg-transparent py-3 px-5 font-normal outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter"
           ></textarea>
            <div v-if="error" class="invalid-feedback">
                <span v-if="Array.isArray(error)">{{ error[0] }}</span>
                <span v-else>{{ error }}</span>
            </div>
        </div>
    </div>
</template>
