<script setup>

const props = defineProps({
    modelValue: {
        type: Object,
        default: []
    },
    isUpdate: {
        type: Boolean,
        default: false
    },
    pageTypeName: {
        type: String,
    },
});

</script>

<template>
    <div class="text-xl">Tracking form does not have settings</div>
</template>
