<script setup>
defineProps({
    title: {
        type: String,
    },
})

</script>

<template>
    <div class="pricing-table-wrapper border border-stroke">
        <table class="pricing-table">
            <slot name="content"/>
        </table>
    </div>
</template>

<style lang="scss">

.pricing-table-wrapper {
    margin-top: 20px;
    border-radius: 6px;
    overflow: hidden;
}

.pricing-table {
    width: 100%;
    background-color: #fff;
}

.pricing-table thead {
    background-color: red;
}

.pricing-table th,
.pricing-table td {
    padding: 12px 16px;
    text-align: left;

    &:first-child {
        border-left: none;
    }

    &:last-child {
        border-right: none;
    }
}

.pricing-table th {
    font-weight: bold;
    border-top: none;
}

.pricing-table tr:last-child td {
    border-bottom: none;
}

</style>
