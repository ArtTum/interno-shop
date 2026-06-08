<script setup>
defineProps({
    keyField: {
        type: Number
    },
    log: {
        type: Object
    }
});
</script>

<template>
    <div>
        <p>
            <span class="text-danger">#{{ keyField + 1 }}. </span>
            <span v-html="log.log"></span>
        </p>
        <hr class="text-gray my-3">
    </div>
</template>

<style scoped>

</style>
