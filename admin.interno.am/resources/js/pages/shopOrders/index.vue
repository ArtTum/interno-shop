<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import {computed, ref} from "vue";
import {useStore} from "vuex";

const store = useStore();
const isLoading = ref(false);
const expandedOrderId = ref(null);
const updatingStatusId = ref(null);

const STATUSES = ['new', 'processing', 'completed', 'cancelled'];
const orders = computed(() => store.getters['shopFrontend/getOrders'] || []);
const totalAmount = computed(() => orders.value.reduce((sum, order) => sum + Number(order.total || 0), 0));
const newOrdersCount = computed(() => orders.value.filter(o => (o.status || 'new') === 'new').length);

const fetchOrders = async () => {
    isLoading.value = true;
    try {
        await store.dispatch('shopFrontend/fetchOrders');
    } finally {
        isLoading.value = false;
    }
};

const formatPrice = (value) => Number(value || 0).toLocaleString('hy-AM');

const formatDate = (value) => {
    if (!value) return '-';
    const d = new Date(value);
    if (isNaN(d)) return value;
    return d.toLocaleDateString('hy-AM', {day: '2-digit', month: 'short', year: 'numeric'})
        + ', ' + d.toLocaleTimeString('hy-AM', {hour: '2-digit', minute: '2-digit'});
};

const customerName = (order) => {
    const c = order.customer || {};
    return [c.firstName, c.lastName].filter(Boolean).join(' ') || '-';
};

const craftsmanInfo = (order) => {
    const cr = order.craftsman || {};
    const cu = order.customer || {};
    const code = cr.code || cu.masterCode || '';
    const name = cr.name || '';
    return {code, name, label: [code, name].filter(Boolean).join(' · ') || null};
};

const productTitle = (item, language = 'hy') => {
    const title = item.product?.title;
    if (typeof title === 'string') return title;
    return title?.[language] || title?.hy || Object.values(title || {})[0] || `Product #${item.productId}`;
};

const OPTION_LABELS = {
    code: 'Կոդ', size: 'Չափ', height: 'Բարձրություն', unit: 'Չափիչ',
    piece: 'Հատ', type: 'Տեսակ', material: 'Նյութ', color: 'Գույն', quantity: 'Քանակ',
};

const optionEntries = (item) =>
    Object.entries(item.product?.options || {}).filter(([, v]) => v !== null && v !== undefined && v !== '');

const isColorValue = (v) => typeof v === 'string' && /^#[0-9a-fA-F]{3,8}$/.test(v.trim());

const toggleDetails = (orderId) => {
    expandedOrderId.value = expandedOrderId.value === orderId ? null : orderId;
};

const changeStatus = async (order, status) => {
    if (order.status === status || updatingStatusId.value === order.id) return;
    updatingStatusId.value = order.id;
    try {
        await store.dispatch('shopFrontend/updateOrderStatus', {id: order.id, status});
    } finally {
        updatingStatusId.value = null;
    }
};

const statusClass = (status) => {
    const map = {
        new: 'bg-blue-50 text-blue-700 border border-blue-200',
        processing: 'bg-yellow-50 text-yellow-700 border border-yellow-200',
        completed: 'bg-green-50 text-green-700 border border-green-200',
        cancelled: 'bg-red-50 text-red-700 border border-red-200',
    };
    return map[status] || 'bg-gray-100 text-gray-600 border border-gray-200';
};

fetchOrders();
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Shop Orders" :breadcrumb="[{path: '/', title: 'Dashboard'}]"/>

        <div class="space-y-5">

            <!-- Header -->
            <div class="rounded-xl border border-stroke bg-white px-6 py-4 shadow-default flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-black">Shop Orders</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Orders sent from the frontend store.</p>
                </div>
                <button
                    :disabled="isLoading"
                    type="button"
                    class="flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm disabled:opacity-60 hover:opacity-90 transition"
                    @click="fetchOrders"
                >
                    <font-awesome-icon :icon="['far', 'rotate-right']" :class="{'animate-spin': isLoading}" />
                    {{ isLoading ? 'Loading…' : 'Refresh' }}
                </button>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-4 max-md:grid-cols-1">
                <div class="rounded-xl border border-stroke bg-white px-6 py-5 shadow-default flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600 text-xl shrink-0">
                        <font-awesome-icon :icon="['far', 'receipt']" />
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total Orders</p>
                        <strong class="mt-0.5 block text-2xl font-bold text-black">{{ orders.length }}</strong>
                    </div>
                </div>
                <div class="rounded-xl border border-stroke bg-white px-6 py-5 shadow-default flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-50 text-green-600 text-xl shrink-0">
                        <font-awesome-icon :icon="['far', 'circle-dollar']" />
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total Amount</p>
                        <strong class="mt-0.5 block text-2xl font-bold text-black">{{ formatPrice(totalAmount) }} ֏</strong>
                    </div>
                </div>
                <div class="rounded-xl border border-stroke bg-white px-6 py-5 shadow-default flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-50 text-orange-500 text-xl shrink-0">
                        <font-awesome-icon :icon="['far', 'bell']" />
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">New Orders</p>
                        <strong class="mt-0.5 block text-2xl font-bold text-black">{{ newOrdersCount }}</strong>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-xl border border-stroke bg-white shadow-default overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-stroke bg-gray-2">
                                <th class="py-3.5 px-4 text-left font-semibold text-xs uppercase tracking-wide text-gray-500 w-16">#</th>
                                <th class="py-3.5 px-4 text-left font-semibold text-xs uppercase tracking-wide text-gray-500">Date</th>
                                <th class="py-3.5 px-4 text-left font-semibold text-xs uppercase tracking-wide text-gray-500">Customer</th>
                                <th class="py-3.5 px-4 text-left font-semibold text-xs uppercase tracking-wide text-gray-500">Craftsman</th>
                                <th class="py-3.5 px-4 text-left font-semibold text-xs uppercase tracking-wide text-gray-500">Items</th>
                                <th class="py-3.5 px-4 text-left font-semibold text-xs uppercase tracking-wide text-gray-500">Total</th>
                                <th class="py-3.5 px-4 text-left font-semibold text-xs uppercase tracking-wide text-gray-500">Status</th>
                                <th class="py-3.5 px-4 w-14"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="orders.length">
                                <template v-for="order in orders" :key="order.id">
                                    <!-- Main Row -->
                                    <tr
                                        class="border-b border-stroke transition-colors"
                                        :class="expandedOrderId === order.id ? 'bg-blue-50/40' : 'hover:bg-gray-50/60'"
                                    >
                                        <td class="py-4 px-4 font-bold text-black">#{{ order.id }}</td>
                                        <td class="py-4 px-4 text-gray-600 whitespace-nowrap">{{ formatDate(order.created_at) }}</td>
                                        <td class="py-4 px-4">
                                            <p class="font-semibold text-black leading-snug">{{ customerName(order) }}</p>
                                            <p class="text-xs text-gray-500 mt-0.5">{{ order.customer?.phone || '' }}</p>
                                            <p class="text-xs text-gray-400">{{ order.customer?.email || '' }}</p>
                                        </td>
                                        <td class="py-4 px-4">
                                            <template v-if="craftsmanInfo(order).label">
                                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                                                    <font-awesome-icon :icon="['far', 'id-card']" class="text-indigo-400" />
                                                    {{ craftsmanInfo(order).label }}
                                                </span>
                                            </template>
                                            <span v-else class="text-gray-400">—</span>
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-700">
                                                {{ order.items?.length || 0 }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 font-bold text-black whitespace-nowrap">{{ formatPrice(order.total) }} ֏</td>
                                        <td class="py-4 px-4">
                                            <div class="relative inline-flex items-center">
                                                <select
                                                    :value="order.status || 'new'"
                                                    :disabled="updatingStatusId === order.id"
                                                    class="appearance-none rounded-full border py-0.5 pl-2.5 pr-6 text-xs font-semibold capitalize cursor-pointer focus:outline-none disabled:opacity-60"
                                                    :class="statusClass(order.status || 'new')"
                                                    @change="changeStatus(order, $event.target.value)"
                                                >
                                                    <option v-for="s in STATUSES" :key="s" :value="s">{{ s }}</option>
                                                </select>
                                                <span class="pointer-events-none absolute right-1.5 top-1/2 -translate-y-1/2 text-[9px]">▾</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 text-right">
                                            <button
                                                type="button"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-stroke text-gray-500 hover:border-primary hover:text-primary transition"
                                                :title="expandedOrderId === order.id ? 'Hide details' : 'Show details'"
                                                @click="toggleDetails(order.id)"
                                            >
                                                <font-awesome-icon :icon="['far', expandedOrderId === order.id ? 'chevron-up' : 'chevron-down']" class="text-xs" />
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Detail Row -->
                                    <tr v-if="expandedOrderId === order.id" class="border-b border-stroke bg-blue-50/20">
                                        <td colspan="8" class="px-4 py-5">
                                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 xl:grid-cols-3">

                                                <!-- Customer card -->
                                                <div class="rounded-xl border border-blue-100 bg-white p-4 col-span-full lg:col-span-1">
                                                    <p class="mb-3 text-xs font-bold uppercase tracking-wide text-gray-400">Customer Info</p>
                                                    <div class="space-y-2 text-sm">
                                                        <div class="flex justify-between gap-2">
                                                            <span class="text-gray-500">Name</span>
                                                            <span class="font-semibold text-black">{{ customerName(order) }}</span>
                                                        </div>
                                                        <div class="flex justify-between gap-2">
                                                            <span class="text-gray-500">Phone</span>
                                                            <a :href="`tel:${order.customer?.phone}`" class="font-semibold text-blue-600 hover:underline">{{ order.customer?.phone || '-' }}</a>
                                                        </div>
                                                        <div class="flex justify-between gap-2">
                                                            <span class="text-gray-500">Email</span>
                                                            <a :href="`mailto:${order.customer?.email}`" class="font-semibold text-blue-600 hover:underline">{{ order.customer?.email || '-' }}</a>
                                                        </div>
                                                        <div class="flex justify-between gap-2">
                                                            <span class="text-gray-500">Address</span>
                                                            <span class="font-semibold text-black text-right max-w-[200px]">{{ order.customer?.address || '-' }}</span>
                                                        </div>
                                                        <div v-if="craftsmanInfo(order).label" class="flex justify-between gap-2">
                                                            <span class="text-gray-500">Craftsman</span>
                                                            <span class="font-semibold text-indigo-700">{{ craftsmanInfo(order).label }}</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Product cards -->
                                                <div
                                                    v-for="item in order.items || []"
                                                    :key="`${order.id}-${item.productId}`"
                                                    class="rounded-xl border border-stroke bg-white overflow-hidden"
                                                >
                                                    <!-- Card header -->
                                                    <div class="flex items-start justify-between gap-3 border-b border-stroke bg-gray-50/60 px-4 py-3">
                                                        <div class="min-w-0">
                                                            <p class="truncate font-semibold text-black leading-snug">{{ productTitle(item, order.language) }}</p>
                                                            <p class="text-xs text-gray-400 mt-0.5">ID #{{ item.productId }}</p>
                                                        </div>
                                                        <span class="shrink-0 rounded-lg bg-black px-2.5 py-1 text-sm font-bold text-white">×{{ item.quantity }}</span>
                                                    </div>

                                                    <!-- Params grid -->
                                                    <div class="p-4">
                                                        <!-- Price row -->
                                                        <div class="mb-3 flex items-center justify-between rounded-lg bg-green-50 px-3 py-2">
                                                            <div>
                                                                <span class="text-xs font-bold uppercase tracking-wide text-green-700">Գին × {{ item.quantity }}</span>
                                                                <span v-if="item.selectedOptionLabel" class="ml-2 rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">{{ item.selectedOptionLabel }}</span>
                                                            </div>
                                                            <strong class="text-base font-bold text-green-800">
                                                                {{ formatPrice((item.effectivePrice != null ? Number(item.effectivePrice) : Number(item.product?.price || 0)) * item.quantity) }} ֏
                                                            </strong>
                                                        </div>

                                                        <!-- Options table -->
                                                        <div v-if="optionEntries(item).length" class="divide-y divide-stroke rounded-lg border border-stroke overflow-hidden">
                                                            <div
                                                                v-for="[key, value] in optionEntries(item)"
                                                                :key="`${item.productId}-${key}`"
                                                                class="flex items-center justify-between gap-4 px-3 py-2 text-sm even:bg-gray-50/50"
                                                            >
                                                                <span class="shrink-0 text-xs font-semibold uppercase tracking-wide text-gray-400">
                                                                    {{ OPTION_LABELS[key] || key }}
                                                                </span>
                                                                <!-- Color swatch -->
                                                                <div v-if="isColorValue(value)" class="flex items-center gap-2">
                                                                    <span
                                                                        class="h-5 w-5 rounded-full border border-black/10 shadow-sm shrink-0"
                                                                        :style="{background: value}"
                                                                    />
                                                                    <span class="font-mono text-xs text-gray-600">{{ value }}</span>
                                                                </div>
                                                                <strong v-else class="break-words text-right font-semibold text-black max-w-[200px]">{{ value }}</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </template>

                            <tr v-else>
                                <td colspan="8" class="py-16 text-center text-gray-400">
                                    <font-awesome-icon :icon="['far', 'inbox']" class="text-4xl mb-3 block mx-auto opacity-30" />
                                    {{ isLoading ? 'Loading orders…' : 'No orders yet.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </DefaultLayoutComponent>
</template>
