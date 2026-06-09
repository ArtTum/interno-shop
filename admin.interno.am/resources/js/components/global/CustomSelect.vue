<script setup>
import {ref, watch, toRefs, computed} from 'vue';

const props = defineProps({
    modelValue: [String, Number, Boolean, Array],
    label: String,
    name: String,
    class: String,
    placeholder: String,
    error: {
        type: [String, Array],
        required: false
    },
    mode: {
        type: String,
        default: 'single'
    },
    parentDivClasses: {
        type: String,
        default: ''
    },
    searchable: {
        type: Boolean,
        default: false
    },
    invalidFeedbackPlace: {
        type: Boolean,
        default: true
    },
    canClear: {
        type: Boolean,
        default: false
    },
    closeOnSelect: {
        type: Boolean,
        default: true
    },
    disabled: {
        type: Boolean,
        default: false
    },
    options: {
        type: [Array, Object],
        default: []
    },
    checkExistsInArray: {
        type: Array,
        default: []
    },
    needAutocomplete: {
        type: Boolean,
        default: false
    },
    excludedValue: {
        type: [Number, String],
        default: null
    },
    withGeneral: {
        type: Boolean,
        default: true
    },
});

const {modelValue} = toRefs(props);
const value = ref(modelValue.value);

watch(modelValue, (newVal) => {
    value.value = newVal;
});

const emits = defineEmits(['update:modelValue', 'search-change', 'change']);

watch(value, (newVal) => {
    emits('update:modelValue', newVal);
});

const filteredOptions = computed(() => {
    if (Array.isArray(props.options)) {
        return props.options.filter(option => {
            if (!props.withGeneral && option.value == -1) {
                return false;
            }
            if (option.value == -1) return true;

            const isNotExcluded = !props.excludedValue || option.value != props.excludedValue;

            const passesCheckArray = !props.checkExistsInArray.length || props.checkExistsInArray.includes(option.value);

            return isNotExcluded && passesCheckArray;
        });
    } else {
        return props.options;
    }
});

const handleSearchChange = (query) => {
    if (props.needAutocomplete) {
        emits('search-change', query);
    }
};
</script>

<template>
    <div :class="parentDivClasses">
        <label v-if="label" class="mb-2.5 block font-medium text-black">
            {{ label }}
        </label>
        <Multiselect
            v-model="value"
            :name="name"
            :mode="mode"
            :options="filteredOptions"
            :searchable="searchable"
            :canClear="canClear"
            :placeholder="placeholder"
            :disabled="disabled"
            :close-on-select="closeOnSelect"
            :class="[props.class, error ? 'is-invalid' : '']"
            @search-change="handleSearchChange"
            @change="(value) => {
                emits('change', value);
            }"
        >
            <template #option="{ option }">
                <div class="flex items-center w-full">
                    <template
                        v-if="option.icon === 'gear'"
                    >
                        <font-awesome-icon icon="gear" class="w-5 h-5 mr-2"/>
                    </template>
                    <template v-else-if="option.icon && option.icon_code">
                        <img
                            :src="`/flags/${option.icon_code.toLowerCase()}.svg`"
                            alt="icon"
                            class="w-5 h-5 mr-2"
                        />
                    </template>
                    <template v-else-if="option.icon">
                        <img
                            :src="`/flags/${option.code ? option.code.toLowerCase() : option.label.toLowerCase()}.svg`"
                            alt="icon"
                            class="w-5 h-5 mr-2"
                        />
                    </template>
                    <template v-else-if="option.image">
                        <img
                            :src="option.image"
                            alt="icon"
                            class="w-[70px] mr-2"
                        />
                    </template>
                    <template v-else-if="option.color">
                        <span
                            class="mr-2 inline-block h-5 w-5 rounded border border-stroke"
                            :style="{backgroundColor: option.color}"
                        ></span>
                    </template>
                    <span>{{ option.label }}</span>
                    <font-awesome-icon
                        v-if="option.fulled"
                        class="ml-auto text-green-600"
                        :icon="['fass', 'check']"
                    />
                </div>
            </template>
        </Multiselect>
        <div
            v-if="invalidFeedbackPlace && error"
            class="invalid-feedback"
        >
            <span v-if="Array.isArray(error)">{{ error[0] }}</span>
            <span v-else>{{ error }}</span>
        </div>
    </div>

</template>
