<script setup>
const props = defineProps({
    label: {
        type: String,
    },
    value: {
        type: Boolean,
    },
    id: {
        type: String,
    },
    colorDanger: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
})

const emits = defineEmits([
    'change'
])
</script>

<template>
    <div>
        <label :for="id" class=" cursor-pointer select-none items-center">
            <label class="mb-2.5 block font-medium text-black"> {{ label }} </label>
            <div class="relative" :class="{'disabled': disabled}">
                <input
                    :checked="value"
                    @change ="(value) => {
                        emits('change', value.target.checked)
                    }"
                    :disabled="disabled"
                    :value="value"
                    :id="id"
                    type="checkbox"
                    class="sr-only"
                    :name="id"
                />
                <div class="h-5 w-14 rounded-full bg-meta-9 shadow-inner"></div>
                <div
                    :class="{'!right-0 !translate-x-full !bg-primary': value, '!bg-primary': !colorDanger && value, '!bg-danger': colorDanger && value}"
                    class="dot absolute left-0 -top-1 h-7 w-7 rounded-full bg-white shadow-switch-1 transition"
                ></div>
            </div>
        </label>
    </div>
</template>
