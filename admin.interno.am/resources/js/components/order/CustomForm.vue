<script setup>

import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import GeneralTab from "@components/order/GeneralTab.vue";
import CustomerInfoTab from "@components/order/CustomerInfoTab.vue";
import OrderItemsTab from "@components/order/OrderItemsTab.vue";
import OrderNotesTab from "@components/order/OrderNotesTab.vue";
import OrderDocumentsTab from "@components/order/OrderDocumentsTab.vue";

import {computed, ref, toRefs, watch} from "vue";
import {useStore} from "vuex";
import ShippingLabelsTab from "@components/order/ShippingLabelsTab.vue";
import PopupWithSlot from "@components/global/PopupWithSlot.vue";
import CustomTableSecond from "@components/global/CustomTableSecond.vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomerFeedbackTab from "@components/order/CustomerFeedbackTab.vue";
import {debounce} from "lodash";
import CustomSelect from "@components/global/CustomSelect.vue";
import CustomerEmailTab from "@components/order/CustomerEmailTab.vue";

const store = useStore()

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    orderItemsErrors: {
        type: Object,
        default: {}
    },
});

const auth = computed(() => store.getters['auth/getUser']);
const {modelValue} = toRefs(props);
const form = ref(modelValue.value);

watch(modelValue, (newVal) => {
    form.value = newVal;
}, {immediate: true});

watch(form.value, (newVal) => {
    emits('update:modelValue', newVal);
});

store.dispatch('documentSetting/fetchPageData', {
    page: 1,
    per_page: 25,
    search: '',
    ordering_field: 'id',
    ordering_direction: 'asc',
});

store.dispatch('order/fetchParams');
const params = computed(() => store.getters['order/getParams']);

const emits = defineEmits([
    'update:modelValue',
    'submit',
    'fetch'
])

const activeTab = ref('general');
const tabsWithErrors = ref([]);
const justSubmitted = ref(false);

const tabsRoutes = [
    {key: 'general', title: 'General', icon: ['far', 'gear']},
    {key: 'customer_info', title: 'Customer info', icon: ['far', 'user']},
    {key: 'order_items', title: 'Order items', icon: ['fas', 'list']},
    {key: 'order_notes', title: 'Order notes', icon: ['far', 'note']},
    {key: 'order_documents', title: 'Order documents', icon: ['far', 'file-pdf']},
    {key: 'shipping_labels', title: 'Shipping labels', icon: ['fas', 'truck-fast']},
    {key: 'customer_feedback', title: 'Customer feedback', icon: ['far', 'comment']},
    {key: 'customer_email', title: 'Email to customer', icon: ['far', 'envelope']},
];

const emptyTabsWithErrors = (tab) => {
    const index = tabsWithErrors.value.indexOf(tab);

    if (index !== -1) {
        tabsWithErrors.value.splice(index, 1);
    }
};

const partialRefund = ref({
    popupOpen: false,
    searchingTerm: '',
    items: [],
    selectedIds: []
})

const resendEmail = async () => {
    try {
        const userConfirmed = confirm("Are you sure?");
        if (userConfirmed) {
            const res = await store.dispatch('order/resendEmail', {id: form.value.id, status: form.value.status});
            if (res.data.success) {
                store.commit('notification/SET_NOTIFICATION', {
                    visible: true,
                    title: 'Success',
                    message: 'Successfully send'
                });
            }
        }
    } catch (reqErrors) {

    }
}

const createFullReshipment = async () => {
    try {
        const userConfirmed = confirm("Are you sure?");
        if (userConfirmed) {
            await store.dispatch('order/createFullReshipment', {
                order_id: form.value.id
            });

            store.commit('notification/SET_NOTIFICATION', {
                visible: true,
                title: 'Success',
                message: 'Reshipment created successfully!'
            });

            emits('fetch')
        }
    } catch (error) {
        form.errors = error;
    }
}

const createPartialReshipment = async () => {
    try {

        partialRefund.value.popupOpen = false;

        await store.dispatch('order/createPartialReshipment', {
            order_id: form.value.id,
            items: partialRefund.value.items,
            selectedIds: partialRefund.value.selectedIds
        });

        partialRefund.value = {
            popupOpen: false,
            items: [],
            selectedIds: [],
            searchingTerm: '',
        };

        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Reshipment created successfully!'
        });

        emits('fetch');
    } catch (error) {
        form.errors = error;
    }
}

const openPartialRefundPopup = async () => {
    partialRefund.value.items = [];
    try {
        const res = await store.dispatch('order/getPartialReshipmentInfo', {
            order_id: form.value.id
        });
        partialRefund.value.items = res.items;
        partialRefund.value.popupOpen = true;
    } catch (error) {
        form.errors = error;
    }
}

watch(
    () => form.value.errors.general,
    (newVal) => {
        if (newVal === undefined || !newVal) return;

        Object.keys(newVal).forEach(key => {
            let errorArray = key.split(".");
            let field_name = errorArray[0];
            if (
                field_name === 'editing_order_billing_address' || field_name === 'editing_order_shipping_address' ||
                field_name === 'status' || field_name === 'transaction_id'
            ) {
                if (justSubmitted.value) {
                    activeTab.value = 'general';
                    justSubmitted.value = false;
                }
                tabsWithErrors.value.push('general');
            }
            if (
                field_name === 'shipping_carrier' || field_name === 'shipping_price' ||
                field_name === 'shipping_method_name_base'
            ) {
                if (justSubmitted.value) {
                    activeTab.value = 'order_items';
                    justSubmitted.value = false;
                }
                tabsWithErrors.value.push('order_items');
            }
        });
    },
    {deep: true}
);
watch(
    () => props.orderItemsErrors,
    (newVal) => {
        if (newVal === undefined || !newVal) return;

        if (justSubmitted.value) {
            activeTab.value = 'order_items';
            justSubmitted.value = false;
        }
        tabsWithErrors.value.push('order_items');
    },
    {deep: true}
);

const itemsOptions = ref([]);
const itemsAutocomplete = debounce(async (term) => {
    itemsOptions.value = await store.dispatch('item/autocomplete', {
        field: 'name,sku',
        term,
        alreadySelectIds: [],
        oldItems: partialRefund.value.items,
    });
}, 200);

const addAnotherItemToReshipmentItems = async (item_id) => {
    const res = await store.dispatch('item/findForReshipment', {
        item_id,
    });
    partialRefund.value.searchingTerm = '';
    partialRefund.value.items = {
        ...{
            [res.item.id]: {
                id: res.item.id,
                name: res.item.name,
                production_price: res.item.production_price,
                regular_price: res.item.regular_price,
                sku: res.item.sku,
                isAnother: true,
                quantity: 1,
            }
        }, ...partialRefund.value.items
    }
}

const changeTab = (key) => {
    activeTab.value = key;
    store.commit('order/SET_REFUND_PRICE', 0);
}

const getRefundPrice = computed(() => store.getters['order/getRefundPrice']);

</script>

<template>
    <div>
        <template v-if="partialRefund.popupOpen">
            <PopupWithSlot
                classes="max-w-[1500px]"
                @close="partialRefund.popupOpen = false, partialRefund.selectedIds = [], partialRefund.searchingTerm = ''"
            >
                <CustomTableSecond
                    title="Warehouse items"
                    @createReshipment="createPartialReshipment()"
                    :button-info="{
                        title: 'Create reshipment',
                        emitName: 'create-reshipment',
                        icon: '',
                        classes: 'bg-meta-3',
                        disabled: !partialRefund.selectedIds.length
                    }"
                >
                    <template #header>

                    </template>
                    <template #headerFeature>
                        <div>
                            <CustomSelect
                                @search-change="itemsAutocomplete"
                                v-model="partialRefund.searchingTerm"
                                :searchable="true"
                                label="Another item"
                                placeholder="Name"
                                :options="itemsOptions"
                                :can-clear="true"
                                :need-autocomplete="true"
                                :invalid-feedback-place="false"
                                @change="(newId) => {
                                    addAnotherItemToReshipmentItems(newId)
                                }"
                            />
                        </div>
                    </template>
                    <template #content>
                        <div class="overflow-x-auto">
                            <div class="min-w-[850px]">
                                <div
                                    class="grid grid-cols-10 gap-2 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5"
                                >
                                    <div class="col-span-4 flex items-center">
                                        <p class="font-medium">Name</p>
                                    </div>
                                    <div class="col-span-2 flex items-center">
                                        <p class="font-medium">SKU</p>
                                    </div>
                                    <div class="col-span-2 flex items-center">
                                        <p class="font-medium">Price</p>
                                    </div>
                                    <div class="col-span-1 flex items-center">
                                        <p class="font-medium">Quantity</p>
                                    </div>
                                </div>

                                <template
                                    v-for="(warehouseItem, itemId) in partialRefund.items"
                                    :key="itemId"
                                >
                                    <div
                                        class="grid grid-cols-10 gap-2 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5"
                                        :class="{'bg-meta-3/20': warehouseItem.isAnother}"
                                    >
                                        <div class="col-span-4 items-start text-left">
                                            <div>
                                                {{ warehouseItem.name }}
                                            </div>
                                            <div>
                                                Production price: <span
                                                class="font-bold text-black">{{ warehouseItem.production_price }}</span>
                                            </div>
                                        </div>
                                        <div class="col-span-2 flex items-center">
                                            {{ warehouseItem.sku }}
                                        </div>
                                        <div class="col-span-2 flex items-center">
                                            <CustomInput
                                                :disabled="!partialRefund.selectedIds.includes(warehouseItem.id)"
                                                v-model="warehouseItem.regular_price"
                                                name="price"
                                                type="text"
                                                placeholder="Price"
                                                :tableInput="true"
                                            />
                                        </div>
                                        <div class="col-span-1 items-center flex">
                                            <CustomInput
                                                :disabled="!partialRefund.selectedIds.includes(warehouseItem.id)"
                                                v-model="warehouseItem.quantity"
                                                name="quantity"
                                                type="number"
                                                placeholder="Quantity"
                                                :tableInput="true"
                                            />
                                        </div>
                                        <div class="col-span-1 items-center flex justify-center">
                                            <CustomInput
                                                name="select"
                                                type="checkbox"
                                                @change="(value) => {
                                            if (value) {
                                              partialRefund.selectedIds.push(warehouseItem.id)
                                            } else {
                                              partialRefund.selectedIds = partialRefund.selectedIds.filter(ids => id == warehouseItem.id);
                                            }
                                        }"
                                            />
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </CustomTableSecond>
            </PopupWithSlot>
        </template>
        <form @submit.prevent="emits('submit'), justSubmitted = true">
            <div
                v-if="Object.keys(form.errors).length > 0 && form.errors.general"
                class="grid grid-cols-1 gap-9 p-6.5"
            >
                <AlertError :errors="form.errors.general"/>
            </div>
            <div class="grid grid-cols-1 gap-9">
                <div class="w-full p-7.5 max-lg:p-6 max-sm:p-2">
                    <div class="overflow-x-auto mb-6">
                        <div class=" flex gap-9 border-b border-stroke ">
                            <template
                                :key="key"
                                v-for="(tabRoute, key) in tabsRoutes"
                            >
                                <router-link
                                    to=""
                                    @click="changeTab(tabRoute.key)"
                                    :class="{
                                    'text-danger border-danger': tabsWithErrors.includes(tabRoute.key),
                                    'text-primary border-primary': activeTab === tabRoute.key && !tabsWithErrors.includes(tabRoute.key),
                                    'border-transparent': activeTab !== tabRoute.key
                                }"
                                    class="border-b-2 py-4 font-medium hover:text-primary text-base px-2 shrink-0"
                                >
                                    <font-awesome-icon :icon="tabRoute.icon"/>
                                    {{ tabRoute.title }}
                                </router-link>
                            </template>
                        </div>
                    </div>

                    <div v-if="activeTab === 'general'">
                        <GeneralTab
                            v-model="form"
                            :params="params"
                            @emptyTabsWithErrors="emptyTabsWithErrors"
                        />
                    </div>
                    <div v-else-if="activeTab === 'customer_info'">
                        <CustomerInfoTab :form="form"/>
                    </div>
                    <div v-else-if="activeTab === 'order_items'">
                        <OrderItemsTab
                            v-model="form"
                            :params="params"
                            :order-items-errors="orderItemsErrors"
                            @emptyTabsWithErrors="emptyTabsWithErrors"
                            @fetch="emits('fetch')"
                            @submit="emits('submit')"
                        />
                    </div>
                    <div v-else-if="activeTab === 'order_notes'">
                        <OrderNotesTab
                            :order_notes="form.order_notes"
                            :order_id="form.id"
                            @fetch="emits('fetch')"
                        />
                    </div>
                    <div v-else-if="activeTab === 'order_documents'">
                        <OrderDocumentsTab
                            :order_documents="form.order_documents"
                            :order_id="form.id"
                            :ead-url="form.ead_url"
                            :full_reshipment="form.full_reshipment"
                            :status="form.status"
                            @fetch="emits('fetch')"
                        />
                    </div>
                    <div v-else-if="activeTab === 'shipping_labels'">
                        <ShippingLabelsTab
                            :shipping_labels="form.shipping_labels"
                            :order="form"
                            :vendor_name="params.vendorName"
                            :dpd_setting="params.dpdSetting"
                            :dhl_setting="params.dhlSetting"
                            :fedex_setting="params.fedexSetting"
                            :tnt_setting="params.tntSetting"
                            :carriers="params.carriers"
                            @fetch="emits('fetch')"
                        />
                    </div>
                    <div v-else-if="activeTab === 'customer_feedback'">
                        <CustomerFeedbackTab
                            :feedback-types="params.feedbackTypes"
                            :order-id="form.id"
                            :employees="form.employees"
                            :order-feedbacks="form.order_feedbacks"
                            @fetch="emits('fetch')"
                            :feedback-types-for-showing="params.feedbackTypeForShowing"
                        />
                    </div>
                    <div v-else-if="activeTab === 'customer_email'">
                        <CustomerEmailTab
                            :order-id="form.id"
                            :order-emails="form.order_customer_emails"
                            @fetch="emits('fetch')"
                        />
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-9" v-if="!form.number">
                <div class="flex flex-col p-6.5 save-button-fixed">
                    <div class="flex ml-auto gap-5" v-if="!getRefundPrice">
                        <template
                            v-if="auth.user_group.permissions_by_name.orders[0].can_add && form.status_old === 4 && form.full_reshipment === null">
                            <CustomButton
                                @click="createFullReshipment"
                                class="flex items-center gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 whitespace-nowrap"
                                type="button"
                            >
                                <font-awesome-icon :icon="['fas', 'share']" class=""/>
                                Create full reshipment
                            </CustomButton>
                            <CustomButton
                                @click="openPartialRefundPopup"
                                class="flex items-center gap-2  rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 whitespace-nowrap"
                                type="button"
                            >
                                <font-awesome-icon :icon="['fas', 'repeat']" class=""/>
                                Create partial reshipment
                            </CustomButton>
                        </template>
                        <template v-if="auth.user_group.permissions_by_name.orders[0].can_edit">
                            <CustomButton
                                @click="resendEmail"
                                class="flex items-center gap-2 rounded bg-meta-3 py-2 whitespace-nowrap px-4.5 font-medium text-white hover:bg-opacity-80"
                                type="button"
                            >
                                <font-awesome-icon :icon="['far', 'envelope']"/>
                                Resend email
                            </CustomButton>
                        </template>

                        <template v-if="auth.user_group.permissions_by_name.orders[0].can_delete">
                            <CustomButton
                                @click="store.commit('order/SET_DELETE_MODAL_VALUE', {
                                    value: true,
                                    id: form.id
                                });"
                                class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                                type="button"
                            >
                                <font-awesome-icon :icon="['far', 'trash']"/>
                                Delete
                            </CustomButton>
                        </template>

                        <template v-if="auth.user_group.permissions_by_name.orders[0].can_edit">
                            <CustomButton
                                class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                                type="submit"
                            >
                                <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                                Save
                            </CustomButton>
                        </template>
                    </div>

                </div>
            </div>
            <div class="grid grid-cols-1 gap-9" v-else>
                <div class="flex flex-col save-button-fixed">
                    <div
                        class="bg-amber-200 px-6.5 py-2.5"
                    >
                        Orders from OMS are not editable!
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>
    .table-default-holder {
        min-width: unset;
    }
</style>
