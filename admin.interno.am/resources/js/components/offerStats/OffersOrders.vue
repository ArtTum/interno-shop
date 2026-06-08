<script setup>

defineProps({
    order: {
        type: Object
    },
    orderStatuses: {
        type: Object
    },
    baseCurrencySymbol: {
        type: String
    }
});
</script>

<template>
    <div
        class="grid grid-cols-6 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5">
        <div class="col-span-1 flex items-center text-black">
            <a
                class="ml-2 hover-trigger hover:text-primary"
                :href="`/orders/show/${order.order_id}`"
                target="_blank"
            >
                <span class="text-base text-black-2">#{{ order.order_id }}</span>
                <font-awesome-icon
                    class="text-black-2 ml-2"
                    :icon="['fass', 'up-right-from-square']"
                />
            </a>
        </div>
        <div class="col-span-1 flex items-center">
            {{ order.currency_symbol }} {{ order.total_price }}
        </div>
        <div class="col-span-1 flex items-center">
            <template v-if="order.status === 4">
                <template v-if="order.agent_revenue">
                    {{ baseCurrencySymbol }} {{ order.agent_revenue }}
                </template>
                <template v-else>
                    {{ baseCurrencySymbol }} 0.00
                </template>
            </template>
            <template v-else>
                Waiting to order's complete
            </template>
        </div>
        <div class="col-span-1 flex items-center">
                <span
                    class="inline-flex rounded-full bg-opacity-10 py-2 px-5 text-sm font-medium"
                    :class="{'bg-warning text-warning': order.status === 0 || order.status === 1 || order.status === 5,
                                      'bg-danger text-danger': (order.status === 2 || order.status === 3 || order.status === 6),
                                      'bg-success text-success': order.status === 4
                                    }"
                >
                               {{ orderStatuses[order.status] }}
            </span>
        </div>
        <div class="col-span-1 flex items-center">
            {{ order.status === 4 ? order.status_change_date : '-' }}
        </div>
    </div>
</template>

<style scoped>

</style>
