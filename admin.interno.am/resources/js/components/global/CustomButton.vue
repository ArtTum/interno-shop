<script setup>

const props = defineProps({
    class: String,
    type: String,
    disabled: {
        type: Boolean,
        default: false
    }
})
</script>

<template>
    <button
        :disabled="disabled"
        :type="props.type"
        class="disabled:cursor-not-allowed"
        :class="[props.class, disabled ? 'opacity-80' : '']"
    >
        <slot></slot>
    </button>
</template>
