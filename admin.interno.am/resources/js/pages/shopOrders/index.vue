<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import {computed, ref} from "vue";
import {useStore} from "vuex";

const store = useStore();
const isLoading = ref(false);
const expandedOrderId = ref(null);
const orders = computed(() => store.getters['shopFrontend/getOrders'] || []);
const totalAmount = computed(() => orders.value.reduce((sum, order) => sum + Number(order.total || 0), 0));

const fetchOrders = async () => {
    isLoading.value = true;

    try {
        await store.dispatch('shopFrontend/fetchOrders');
    } finally {
        isLoading.value = false;
    }
};

const formatPrice = (value) => {
    const price = Number(value || 0);

    return price.toLocaleString('hy-AM');
};

const customerName = (order) => {
    const customer = order.customer || {};

    return [customer.firstName, customer.lastName].filter(Boolean).join(' ') || '-';
};

const productTitle = (item, language = 'hy') => {
    const title = item.product?.title;

    if (typeof title === 'string') {
        return title;
    }

    return title?.[language] || title?.hy || Object.values(title || {})[0] || `Product #${item.productId}`;
};

const optionEntries = (item) => {
    return Object.entries(item.product?.options || {}).filter(([, value]) => value !== null && value !== undefined && value !== '');
};

const toggleDetails = (orderId) => {
    expandedOrderId.value = expandedOrderId.value === orderId ? null : orderId;
};

fetchOrders();
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Shop Orders" :breadcrumb="[{path: '/', title: 'Dashboard'}]"/>

        <div class="space-y-6">
            <div class="rounded-sm border border-stroke bg-white p-5 shadow-default">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-black">Shop Orders</h3>
                        <p class="text-sm text-gray-500">Frontend-ից ուղարկված պատվերները և հաճախորդի տվյալները։</p>
                    </div>
                    <button :disabled="isLoading" type="button" class="rounded bg-primary px-5 py-2 font-medium text-white disabled:opacity-70" @click="fetchOrders">
                        {{ isLoading ? 'Loading...' : 'Refresh' }}
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 max-md:grid-cols-1">
                <div class="rounded-sm border border-stroke bg-white p-5 shadow-default">
                    <span class="text-sm font-medium text-gray-500">Orders</span>
                    <strong class="mt-2 block text-2xl font-semibold text-black">{{ orders.length }}</strong>
                </div>
                <div class="rounded-sm border border-stroke bg-white p-5 shadow-default">
                    <span class="text-sm font-medium text-gray-500">Total amount</span>
                    <strong class="mt-2 block text-2xl font-semibold text-black">{{ formatPrice(totalAmount) }}</strong>
                </div>
                <div class="rounded-sm border border-stroke bg-white p-5 shadow-default">
                    <span class="text-sm font-medium text-gray-500">Latest order</span>
                    <strong class="mt-2 block text-base font-semibold text-black">{{ orders[0]?.created_at || '-' }}</strong>
                </div>
            </div>

            <div class="rounded-sm border border-stroke bg-white shadow-default">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-2 text-left">
                                <th class="min-w-[90px] py-4 px-4 font-medium text-black">ID</th>
                                <th class="min-w-[150px] py-4 px-4 font-medium text-black">Date</th>
                                <th class="min-w-[180px] py-4 px-4 font-medium text-black">Customer</th>
                                <th class="min-w-[150px] py-4 px-4 font-medium text-black">Phone</th>
                                <th class="min-w-[210px] py-4 px-4 font-medium text-black">Email</th>
                                <th class="min-w-[260px] py-4 px-4 font-medium text-black">Address</th>
                                <th class="min-w-[110px] py-4 px-4 font-medium text-black">Items</th>
                                <th class="min-w-[120px] py-4 px-4 font-medium text-black">Total</th>
                                <th class="min-w-[120px] py-4 px-4 font-medium text-black">Status</th>
                                <th class="min-w-[100px] py-4 px-4 font-medium text-black">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="orders.length">
                                <template v-for="order in orders" :key="order.id">
                                    <tr class="border-b border-stroke">
                                        <td class="py-5 px-4 font-medium text-black">#{{ order.id }}</td>
                                        <td class="py-5 px-4 text-black">{{ order.created_at || '-' }}</td>
                                        <td class="py-5 px-4 text-black">{{ customerName(order) }}</td>
                                        <td class="py-5 px-4 text-black">{{ order.customer?.phone || '-' }}</td>
                                        <td class="py-5 px-4 text-black">{{ order.customer?.email || '-' }}</td>
                                        <td class="py-5 px-4 text-black">
                                            <span class="block max-w-[320px] whitespace-normal break-words">{{ order.customer?.address || '-' }}</span>
                                            <span v-if="order.customer?.masterCode" class="mt-1 block text-xs text-gray-500">Master code: {{ order.customer.masterCode }}</span>
                                        </td>
                                        <td class="py-5 px-4 text-black">{{ order.items?.length || 0 }}</td>
                                        <td class="py-5 px-4 font-medium text-black">{{ formatPrice(order.total) }}</td>
                                        <td class="py-5 px-4">
                                            <span class="inline-flex rounded-full bg-success bg-opacity-10 py-1 px-3 text-sm font-medium text-success">{{ order.status || 'new' }}</span>
                                        </td>
                                        <td class="py-5 px-4">
                                            <button type="button" class="hover:text-primary" title="Details" @click="toggleDetails(order.id)">
                                                <font-awesome-icon :icon="['far', expandedOrderId === order.id ? 'eye-slash' : 'eye']"/>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="expandedOrderId === order.id" class="border-b border-stroke bg-gray-50">
                                        <td colspan="10" class="p-4">
                                            <div class="grid grid-cols-2 gap-4 max-lg:grid-cols-1">
                                                <div v-for="item in order.items || []" :key="`${order.id}-${item.productId}`" class="rounded border border-stroke bg-white p-4">
                                                    <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
                                                        <div>
                                                            <h4 class="font-semibold text-black">{{ productTitle(item, order.language) }}</h4>
                                                            <span class="text-sm text-gray-500">Product #{{ item.productId }}</span>
                                                        </div>
                                                        <span class="rounded bg-gray-100 px-3 py-1 text-sm text-black">x{{ item.quantity }}</span>
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-2 text-sm max-sm:grid-cols-1">
                                                        <span class="text-gray-500">Price</span>
                                                        <strong class="text-black">{{ formatPrice(item.product?.price) }}</strong>
                                                        <template v-for="[key, value] in optionEntries(item)" :key="`${item.productId}-${key}`">
                                                            <span class="capitalize text-gray-500">{{ key }}</span>
                                                            <strong class="break-words text-black">{{ value }}</strong>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                            <tr v-else>
                                <td colspan="10" class="py-10 px-4 text-center text-black">
                                    {{ isLoading ? 'Loading orders...' : 'No orders yet.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </DefaultLayoutComponent>
</template>
