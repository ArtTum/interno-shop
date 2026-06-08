<script setup>

import CustomSelect from "@components/global/CustomSelect.vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomTableSecond from "@components/global/CustomTableSecond.vue";
import CustomButton from "@components/global/CustomButton.vue";
import PopupWithSlot from "@components/global/PopupWithSlot.vue";

import {computed, ref, toRefs, watch} from "vue";
import {debounce} from "lodash";
import {useStore} from "vuex";
import {validate} from "@validation/customValidation.js";

import {formatPrice} from "@/utils/formatPrice.js";
import Switch from "@components/global/Switch.vue";

const store = useStore()
const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    params: {
        type: Object
    },
    orderItemsErrors: {
        type: Object,
        default: {}
    },
});

const {modelValue} = toRefs(props);
const form = ref(modelValue.value);
const newItem = ref(null);
const refundInfo = ref(null);
const refundInPercent = ref('');
const refundInAmount = ref('');

watch(modelValue, (newVal) => {
    form.value = newVal;
}, {immediate: true});

watch(form.value, (newVal) => {
    emits('update:modelValue', newVal);
});

const emits = defineEmits([
    'update:modelValue',
    'empty-tabs-with-errors',
    'fetch',
    'submit',
]);

const editOrderItem = debounce((index) => {
    form.value.order_items[index].subtotal = (form.value.order_items[index].quantity * form.value.order_items[index].price);

    if (form.value.tax_rate && form.value.order_items[index].tax_status) {
        form.value.order_items[index].tax_price = (form.value.order_items[index].subtotal * form.value.tax_rate / 100);
    }
    form.value.order_items[index].total = (parseFloat(form.value.order_items[index].subtotal) + parseFloat(form.value.order_items[index].tax_price));
    form.value.order_items[index].changed = true;

    if (form.value.order_items[index].order_item_parents) {
        for (let j = 0; j < form.value.order_items[index].order_item_parents.length; j++) {
            let pivotItem = form.value.order_items[index].order_item_parents[j];
            pivotItem.quantity = pivotItem.ini_quantity / form.value.order_items[index].ini_quantity * form.value.order_items[index].quantity
        }
    }

    if (form.value.order_items[index].extra_products) {
        for (let j = 0; j < form.value.order_items[index].extra_products.length; j++) {
            let extraProduct = form.value.order_items[index].extra_products[j];

            if (extraProduct.order_item_parents) {
                for (let k = 0; k < extraProduct.order_item_parents.length; k++) {
                    let pivotItem = extraProduct.order_item_parents[k];

                    pivotItem.quantity = pivotItem.ini_quantity / form.value.order_items[index].ini_quantity * form.value.order_items[index].quantity
                }
            }
        }
    }

    calculateOrderTotal();
}, 200);

const editShipping = debounce(() => {
    if (!form.value.shipping_price) form.value.shipping_price = 0;
    if (form.value.tax_rate) {
        form.value.shipping_subtotal_tax = (form.value.shipping_price * form.value.tax_rate / 100);
    }
    form.value.total_shipping_price = (parseFloat(form.value.shipping_subtotal_tax) + parseFloat(form.value.shipping_price));

    calculateOrderTotal();
}, 200);

const calculateOrderTotal = () => {
    let itemsSubtotal = 0;
    let itemsSubtotalTaxAmount = 0;
    let itemsTotalAmount = 0;
    let totalTaxAmount = 0;
    let discountedAmount = 0;
    let totalAmount = 0;
    let isDangerous = false;
    let totalNetWeight = 0;
    let totalGrossWeight = 0;
    let productionPriceSumWarehouseItems = 0;

    for (let i = 0; i < form.value.order_items.length; i++) {
        let orderNetWeightSum = 0;
        let orderGrossWeightSum = 0;
        let orderItem = form.value.order_items[i];
        if (orderItem.deleted) continue;

        if (orderItem.order_item_parents) {
            for (let j = 0; j < orderItem.order_item_parents.length; j++) {
                let pivotItem = orderItem.order_item_parents[j];

                orderNetWeightSum = orderNetWeightSum + (parseFloat(pivotItem.net_weight) * pivotItem.quantity)
                orderGrossWeightSum = orderGrossWeightSum + (parseFloat(pivotItem.gross_weight) * pivotItem.quantity)
                productionPriceSumWarehouseItems = productionPriceSumWarehouseItems + (parseFloat(pivotItem.production_price) * pivotItem.quantity);

                if (pivotItem.un_numbers) {
                    isDangerous = true;
                }
            }
        }

        if (orderItem.extra_products) {
            for (let j = 0; j < orderItem.extra_products.length; j++) {
                let extraProduct = orderItem.extra_products[j];

                if (extraProduct.order_item_parents) {
                    for (let k = 0; k < extraProduct.order_item_parents.length; k++) {
                        let pivotItem = extraProduct.order_item_parents[k];

                        orderNetWeightSum = orderNetWeightSum + (parseFloat(pivotItem.net_weight) * pivotItem.quantity)
                        orderGrossWeightSum = orderGrossWeightSum + (parseFloat(pivotItem.gross_weight) * pivotItem.quantity)
                        productionPriceSumWarehouseItems = productionPriceSumWarehouseItems + (parseFloat(pivotItem.production_price) * pivotItem.quantity);

                        if (pivotItem.un_numbers) {
                            isDangerous = true;
                        }
                    }
                }
            }
        }
        totalNetWeight = totalNetWeight + orderNetWeightSum;
        totalGrossWeight = totalGrossWeight + orderGrossWeightSum;

        itemsSubtotal = (parseFloat(itemsSubtotal) + parseFloat(orderItem.subtotal));
        itemsSubtotalTaxAmount = (parseFloat(itemsSubtotalTaxAmount) + parseFloat(orderItem.tax_price));
        itemsTotalAmount = (parseFloat(itemsTotalAmount) + parseFloat(orderItem.total));
    }

    totalTaxAmount = (parseFloat(itemsSubtotalTaxAmount) + parseFloat(form.value.shipping_subtotal_tax) + parseFloat(form.value.zip_fee_tax));
    totalAmount = (parseFloat(itemsTotalAmount) + parseFloat(form.value.total_shipping_price) + parseFloat(form.value.zip_fee) + parseFloat(form.value.zip_fee_tax));

    if (form.value.coupon_code && form.value.coupon_amount) {
        if (form.value.coupon_type === 1) {
            discountedAmount = (parseFloat(itemsTotalAmount) * parseFloat(form.value.coupon_amount) / 100);
        } else {
            if (parseFloat(itemsTotalAmount) < form.value.coupon_amount) {
                discountedAmount = parseFloat(itemsTotalAmount);
            } else {
                discountedAmount = form.value.coupon_amount;
            }
        }
    }

    totalAmount = (parseFloat(totalAmount) - parseFloat(discountedAmount));
    if (totalAmount < 0) totalAmount = 0;

    form.value.is_dangerous = isDangerous;
    form.value.total_net_weight = totalNetWeight;
    form.value.total_weight = totalGrossWeight;
    form.value.items_subtotal_price = itemsSubtotal;
    form.value.items_subtotal_tax = itemsSubtotalTaxAmount;
    form.value.total_tax = totalTaxAmount;
    form.value.total_discount_price = discountedAmount;
    form.value.total_price = totalAmount;
};

const pageBottom = ref(null);
const prepareRefundInfo = (vendorName, refundPrice) => {
    if (pageBottom.value) {
        pageBottom.value.scrollIntoView({behavior: 'smooth', top: 5000,});
    }
    refundInfo.value = {};
    refundInfo.value.order_items = [];
    for (let i = 0; i < form.value.order_items.length; i++) {
        let orderItem = form.value.order_items[i];

        refundInfo.value.order_items[i] = {
            order_item_id: orderItem.id,
            subtotal: 0,
            item_price: orderItem.price,
            item_total: orderItem.total,
            quantity: 0,
            item_quantity: orderItem.quantity,
            tax_price: 0,
            total: 0,
            refunded: false,
        }

        if (orderItem.order_item_refund) {
            refundInfo.value.order_items[i].order_item_refund_id = orderItem.order_item_refund.id;
            refundInfo.value.order_items[i].already_refunded_quantity = orderItem.order_item_refund.quantity;
            refundInfo.value.order_items[i].already_refunded_total = orderItem.order_item_refund.total;
            refundInfo.value.order_items[i].already_tax_price = orderItem.order_item_refund.tax_price;
            refundInfo.value.order_items[i].already_subtotal = orderItem.order_item_refund.subtotal;
        }
    }

    if (form.value.order_refund) {
        refundInfo.value.order_refund_id = form.value.order_refund.id;
        refundInfo.value.already_refunded_amount = form.value.order_refund.refund_amount;
        refundInfo.value.already_tax_amount = form.value.order_refund.tax_amount;
        refundInfo.value.already_refunded_shipping_amount = form.value.order_refund.shipping_refund_amount;
        refundInfo.value.already_refunded_total_shipping_amount = form.value.order_refund.total_shipping_refund_amount;
        refundInfo.value.type = form.value.order_refund.type;
    } else {
        refundInfo.value.already_refunded_amount = 0;
        refundInfo.value.already_tax_amount = 0;
        refundInfo.value.sales_tax = false;
        refundInfo.value.type = false;
    }

    refundInfo.value.order_id = form.value.id;
    refundInfo.value.payment_id = form.value.payment_id;
    refundInfo.value.payment_method_parent = form.value.payment_method_parent;
    refundInfo.value.total_price = 0;
    ;
    refundInfo.value.refund_reason = 'Returns';
    refundInfo.value.total_price_old = form.value.total_price;
    refundInfo.value.shipping_price_old = form.value.shipping_price;
    refundInfo.value.shipping_price = 0;
    refundInfo.value.shipping_tax = 0;
    refundInfo.value.shipping_total_price = 0;
    refundInfo.value.manual_refund = false;
    refundInfo.value.errors = {};
    if (refundPrice) {
        refundInfo.value.total_price = refundPrice;
    }

    refundInfo.value.vendor_key = vendorName;
};

const changeOrderItemRefund = debounce((index) => {
    refundInfo.value.order_items[index].subtotal = (refundInfo.value.order_items[index].quantity * refundInfo.value.order_items[index].item_price);
    if (form.value.tax_rate) {
        refundInfo.value.order_items[index].tax_price = (refundInfo.value.order_items[index].subtotal * form.value.tax_rate / 100);
    }

    if (form.value.order_refund?.fulled_tax) {
        refundInfo.value.order_items[index].total = parseFloat(refundInfo.value.order_items[index].subtotal);
    } else {
        refundInfo.value.order_items[index].total = (parseFloat(refundInfo.value.order_items[index].subtotal) + parseFloat(refundInfo.value.order_items[index].tax_price));
    }

    refundInfo.value.order_items[index].refunded = true;
    calculateOrderRefundTotal();
}, 200);

const changeOrderShippingPrice = debounce(() => {
    if (!refundInfo.value.shipping_price || refundInfo.value.shipping_price === '') {
        refundInfo.value.shipping_price = 0;
    }

    let shippingTaxPrice = 0;
    if (form.value.tax_rate) {
        shippingTaxPrice = (refundInfo.value.shipping_price * form.value.tax_rate / 100);
    }

    if (form.value.order_refund?.fulled_tax) {
        refundInfo.value.shipping_total_price = parseFloat(refundInfo.value.shipping_price)
    } else {
        refundInfo.value.shipping_total_price = (parseFloat(refundInfo.value.shipping_price) + parseFloat(shippingTaxPrice));
    }

    calculateOrderRefundTotal();
}, 200);

const fullRefund = (vendorName, refundPrice) => {

    prepareRefundInfo(vendorName, refundPrice)
    for (let i = 0; i < form.value.order_items.length; i++) {
        let orderItem = form.value.order_items[i];
        refundInfo.value.order_items[i].quantity = orderItem.quantity;

        if (!orderItem.order_item_refund || !orderItem.order_item_refund.fulled) {
            setRefundMaxValue(i, 'quantity');
            refundInfo.value.order_items[i].subtotal = (refundInfo.value.order_items[i].quantity * refundInfo.value.order_items[i].item_price);
            if (form.value.tax_rate) {
                refundInfo.value.order_items[i].tax_price = (refundInfo.value.order_items[i].subtotal * form.value.tax_rate / 100);
            }
            refundInfo.value.order_items[i].total = (parseFloat(refundInfo.value.order_items[i].subtotal) + parseFloat(refundInfo.value.order_items[i].tax_price));
            refundInfo.value.order_items[i].refunded = true;
            calculateOrderRefundTotal();
        }
    }
    if (form.value.shipping_price > 0) {
        refundInfo.value.shipping_price = form.value.shipping_price
        setRefundMaxValue(null, 'shipping_price')
        changeOrderShippingPrice()
    }
    refundInfo.value.total_price = form.value.order_refund?.refund_amount ? (form.value.total_price - form.value.order_refund.refund_amount) : form.value.total_price;
    refundInfo.value.shipping_tax = form.value.shipping_subtotal_tax;
    refundInfo.value.fulled = true;
};

const fullRefundTaxes = (vendorName, refundPrice) => {
    prepareRefundInfo(vendorName, refundPrice)

    for (let i = 0; i < form.value.order_items.length; i++) {
        let orderItem = form.value.order_items[i];
        let quantity = orderItem.order_item_refund?.quantity ? orderItem.quantity - orderItem.order_item_refund?.quantity : orderItem.quantity;
        if (form.value.tax_rate) {
            let subtotal = (quantity * refundInfo.value.order_items[i].item_price);
            refundInfo.value.order_items[i].tax_price = (subtotal * form.value.tax_rate / 100);
            refundInfo.value.order_items[i].total = (subtotal * form.value.tax_rate / 100);
            refundInfo.value.order_items[i].refunded = true;
        }
    }

    refundInfo.value.fulled_tax = true;

    if (form.value.order_refund) {
        refundInfo.value.total_price = form.value.total_tax - form.value.order_refund.tax_amount;
    } else {
        refundInfo.value.total_price = form.value.total_tax;
    }
    refundInfo.value.shipping_tax = form.value.shipping_subtotal_tax;
};


watch(
    () => refundInfo.value?.total_price,
    (newVal) => {
        if (parseFloat(newVal)) {
            if (
                (parseFloat(refundInfo.value.already_refunded_amount) + parseFloat(refundInfo.value.total_price)) > parseFloat(refundInfo.value.total_price_old)
            ) {
                refundInfo.value.total_price = parseFloat(form.value.total_price) - parseFloat(refundInfo.value.already_refunded_amount);
            } else {
                refundInfo.value.total_price = parseFloat(refundInfo.value.total_price);
            }
            store.commit('order/SET_REFUND_PRICE', refundInfo.value.total_price);
        }
    },
    {deep: true}
);

const calculateOrderRefundTotal = () => {
    refundInfo.value.errors = {};
    let itemsSubtotal = 0;
    let itemsSubtotalTaxAmount = 0;
    let itemsTotalAmount = 0;
    let discountedAmount = 0;
    let totalAmount;

    for (let i = 0; i < form.value.order_items.length; i++) {
        let refundItem = refundInfo.value.order_items[i];
        itemsSubtotal = (parseFloat(itemsSubtotal) + parseFloat(refundItem.subtotal));
        itemsSubtotalTaxAmount = (parseFloat(itemsSubtotalTaxAmount) + parseFloat(refundItem.tax_price));
        itemsTotalAmount = (parseFloat(itemsTotalAmount) + parseFloat(refundItem.total));
    }

    totalAmount = (parseFloat(itemsTotalAmount) + parseFloat(refundInfo.value.shipping_total_price));
    if (form.value.zip_fee > 0 && refundInfo.value.fulled) {
        totalAmount += (parseFloat(form.value.zip_fee) + parseFloat(form.value.zip_fee_tax));
    }

    // todo temporary comment old code for refund issue
    // if (form.value.coupon_code && form.value.coupon_amount) {
    //     if (form.value.coupon_type === 1) {
    //         discountedAmount = (parseFloat(totalAmount) * parseFloat(form.value.coupon_amount) / 100);
    //     } else {
    //         discountedAmount = form.value.coupon_amount;
    //     }
    // }

    // totalAmount = (parseFloat(totalAmount) - parseFloat(discountedAmount));
    // if (totalAmount < 0) totalAmount = 0;

    refundInfo.value.total_price = totalAmount;
};

const setRefundMaxValue = (index, field) => {
    if (index !== null) {
        let checkingValue = refundInfo.value.order_items[index][field];
        if (isNaN(checkingValue)) {
            refundInfo.value.order_items[index][field] = 0;
        }

        if (checkingValue < 0) refundInfo.value.order_items[index][field] = '';

        let alreadyRefundedQuantity = 0;

        if (form.value.order_items[index].order_item_refund) {
            alreadyRefundedQuantity = form.value.order_items[index].order_item_refund.quantity;
        }
        if ((parseFloat(checkingValue) + parseFloat(alreadyRefundedQuantity)) > parseFloat(refundInfo.value.order_items[index]['item_' + field])) {
            refundInfo.value.order_items[index][field] = refundInfo.value.order_items[index]['item_' + field] - alreadyRefundedQuantity;
        }
    } else {
        let checkingValue = parseFloat(refundInfo.value[field]);

        if (checkingValue < 0) refundInfo.value[field] = 0;

        let alreadyRefundedQuantity = 0;

        if (form.value.order_refund && parseFloat(form.value.order_refund.shipping_refund_amount)) {
            alreadyRefundedQuantity = parseFloat(form.value.order_refund.shipping_refund_amount);
        }
        let sumOfNewAndAlreadyRefunded = (checkingValue + alreadyRefundedQuantity);

        let oldValue = parseFloat(refundInfo.value[field + '_old']);

        if (sumOfNewAndAlreadyRefunded > oldValue) {
            refundInfo.value[field] = (oldValue - alreadyRefundedQuantity);
        }
    }
};

const productsOptions = ref([]);

const productsAutocomplete = debounce(async (term) => {
    productsOptions.value = await store.dispatch('product/autocomplete', {
        field: 'name',
        term,
        forOrder: true,
        order_id: form.value.id
    });
}, 200);

const saveNewItem = async () => {
    const errors = validate(newItem.value);
    if (Object.keys(errors).length > 0) {
        newItem.value.errors = errors;
        return false;
    }

    try {
        await store.dispatch('order/addItem', newItem.value);
        newItem.value = null;
        emits('fetch');
        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully added'
        });
    } catch (reqErrors) {

    }
};

const refundOrder = async () => {
    if (refundInfo.value.already_refunded_amount) {
        if (
            (parseFloat(refundInfo.value.already_refunded_amount) + parseFloat(refundInfo.value.total_price)) > parseFloat(refundInfo.value.total_price_old)
        ) {
            refundInfo.value.errors.total_refund_amount = "Total refund amount can't be more, than order's total price";
            return false;
        }
    }

    if (!refundInfo.value.refund_reason) {
        refundInfo.value.errors.refund_reason = "This field is required";
        return false;
    }

    try {
        await store.dispatch('order/refund', refundInfo.value);
        refundInfo.value = null;

        emits('fetch');
        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully refunded'
        });
    } catch (reqErrors) {

    }
};

const auth = computed(() => store.getters['auth/getUser']);
const generalParams = computed(() => store.getters['general/getParams']);

const warehouseItemsPopup = ref(false)
const warehouseItemIndexShowing = ref(null)

const applayingCoupon = ref({
    coupon_code: '',
    order_id: form.value.id,
    errors: null
});

const applyCoupon = async () => {
    const res = await store.dispatch('order/applyCoupon', applayingCoupon.value);

    if (res.data.couponInfo) {
        form.value.coupon_amount = res.data.couponInfo.coupon_amount;
        form.value.coupon_type = res.data.couponInfo.coupon_type;
        form.value.coupon_code = res.data.couponInfo.coupon_code;
        form.value.coupon_id = res.data.couponInfo.coupon_id;
        calculateOrderTotal();
        emits('submit');
    } else {
        applayingCoupon.value.errors = ['Invalid coupon']
    }
}
const selectedRefund = ref('Returns');
const handleChange = (newValue) => {
    if (newValue === 'Other') {
        refundInfo.value.refund_reason = '';
    } else {
        refundInfo.value.refund_reason = newValue;
    }
    return refundInfo.value.refund_reason;
};

const getItemPrice = async () => {
    const res = await store.dispatch('order/getItemPrice', {
        product_variant_id: newItem.value.product_variant_id,
        rate: form.value.order_currency_rate,
        tax_rate: form.value.tax_rate,
        language_id: form.value.language_id,
    })

    if (res.info) {
        newItem.value.price = res.info.price;
    }
}

const setRefundPercentAndAmount = (type, value) => {
    const totalPrice = parseFloat(form.value.total_price) - parseFloat(form.value.order_refund?.refund_amount || 0);
    refundInfo.value.total_price = 0;

    if (!form.value?.order_refund?.type) {
        refundInfo.value.type = false;
    }

    if (type === 'percent') {
        const totalRefundPrice = totalPrice * (value / 100);
        refundInAmount.value = totalRefundPrice.toFixed(2);

        if (refundInAmount.value >= totalPrice) {
            refundInAmount.value = totalPrice;
            refundInPercent.value = 100;
        }
    } else {
        if (refundInAmount.value >= totalPrice) {
            refundInAmount.value = totalPrice;
        }
    }

    if (refundInAmount.value < 0) {
        refundInAmount.value = 0;
    }

    refundInfo.value.total_price = refundInAmount.value;

    if (refundInAmount.value > 0) {
        refundInfo.value.type = true;
    }
}

const handleExportProducts = async (action) => {
    try {
        const response = await store.dispatch(action, {order_id: form.value.id});
        const blob = new Blob([response.data], {type: response.headers['content-type']});
        const link = document.createElement('a');
        link.setAttribute("target", "_blank");
        link.href = window.URL.createObjectURL(blob);
        link.download = 'products.xlsx';
        link.click();
    } catch (error) {
        console.error("Error export document:", error);
    }
};
</script>

<template>
    <template v-if="warehouseItemsPopup">
        <PopupWithSlot
            classes="max-w-[1500px]"
            @close="warehouseItemsPopup = false, warehouseItemIndexShowing = null"
        >
            <CustomTableSecond
                title="Warehouse items"
            >
                <template #content>
                    <div class="overflow-x-auto">
                        <div class="min-w-[650px]">
                            <div
                                class="grid grid-cols-7 gap-2 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5"
                            >
                                <div class="col-span-2 items-center flex">
                                    <p class="font-medium">SKU</p>
                                </div>
                                <div class="col-span-4 flex items-center">
                                    <p class="font-medium">Name</p>
                                </div>
                                <div class="col-span-1 flex items-center">
                                    <p class="font-medium">Quantity</p>
                                </div>
                            </div>
                            <template
                                v-for="(warehouseItem, itemIndex) in form.order_items[warehouseItemIndexShowing].order_item_parents"
                                :key="itemIndex"
                            >
                                <div
                                    class="grid grid-cols-7 gap-2 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5">
                                    <div class="col-span-2 items-center flex">
                                        {{ warehouseItem.sku }}
                                    </div>
                                    <div class="col-span-4 items-center flex">
                                        {{ warehouseItem.name }}
                                    </div>
                                    <div class="col-span-1  items-center flex">
                                        {{ warehouseItem.quantity }}
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </CustomTableSecond>
            <template v-if="form.order_items[warehouseItemIndexShowing].extra_products.length">
                <CustomTableSecond
                    title="Extra product's items"
                >
                    <template #header>

                    </template>

                    <template #content>
                        <div class="overflow-x-auto">
                            <div class="min-w-[650px]">
                                <div
                                    class="grid grid-cols-7 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5"
                                >
                                    <div class="col-span-2 items-center sm:flex">
                                        <p class="font-medium">SKU</p>
                                    </div>
                                    <div class="col-span-4 flex items-center">
                                        <p class="font-medium">Name</p>
                                    </div>
                                    <div class="col-span-1 flex items-center">
                                        <p class="font-medium">Quantity</p>
                                    </div>
                                </div>

                                <template
                                    v-for="(extraProduct, extraProductIndex) in form.order_items[warehouseItemIndexShowing].extra_products"
                                    :key="extraProductIndex"
                                >
                                    <template
                                        v-for="(extraProductItem, extraProductItemIndex) in extraProduct.order_item_parents"
                                        :key="extraProductItemIndex"
                                    >
                                        <div
                                            class="grid grid-cols-7 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5">
                                            <div class="col-span-2 items-center flex">
                                                {{ extraProductItem.sku }}
                                            </div>
                                            <div class="col-span-4  items-center flex">
                                                {{ extraProductItem.name }}
                                            </div>
                                            <div class="col-span-1  items-center flex">
                                                {{ extraProductItem.quantity }}
                                            </div>
                                        </div>
                                    </template>
                                </template>
                            </div>
                        </div>

                    </template>
                </CustomTableSecond>
            </template>
        </PopupWithSlot>
    </template>
    <template
        v-if="!form.number && (form.status_old == 2 || form.status_old == 4) && (!form.order_refund || !form.order_refund.fulled) && auth.user_group.permissions_by_name.orders[0].can_edit && form.full_reshipment === null">
        <div class="grid grid-cols-1 gap-9 mb-5">
            <div class="flex flex-col">
                <template v-if="!refundInfo">
                    <div class="flex gap-3 flex-wrap">
                        <CustomButton
                            @click="prepareRefundInfo(params.vendorName, 0)"
                            type="button"
                            class="block h-fit max-w-[120px] items-center gap-2 rounded bg-gray py-2 px-4.5 font-medium text-black hover:bg-opacity-60"
                        >
                            <font-awesome-icon :icon="['fas', 'arrow-rotate-left']"/>
                            Refund
                        </CustomButton>
                        <CustomButton
                            @click="fullRefund(params.vendorName)"
                            type="button"
                            class="block h-fit max-w-[140px] items-center gap-2 rounded bg-gray py-2 px-4.5 font-medium text-black hover:bg-opacity-60"
                        >
                            <font-awesome-icon :icon="['fas', 'arrow-rotate-left']"/>
                            Full refund
                        </CustomButton>
                        <CustomButton
                            v-if="!form.order_refund?.fulled_tax"
                            @click="fullRefundTaxes(params.vendorName)"
                            type="button"
                            class="block h-fit max-w-[240px] items-center gap-2 rounded bg-gray py-2 px-4.5 font-medium text-black hover:bg-opacity-60"
                        >
                            <font-awesome-icon :icon="['fas', 'arrow-rotate-left']"/>
                            Full refund taxes
                        </CustomButton>
                    </div>
                </template>
                <template v-else>
                    <div class="flex flex-wrap gap-6 justify-between">
                        <CustomButton
                            @click="refundInfo = null"
                            type="button"
                            class="block h-fit max-w-[120px] items-center gap-2 rounded bg-gray py-2 px-4.5 font-medium text-black hover:bg-opacity-60"
                        >
                            <font-awesome-icon :icon="['fas', 'arrow-rotate-left']"/>
                            Cancel
                        </CustomButton>
                        <div class="flex flex-wrap gap-4" v-if="!refundInfo.fulled_tax && !refundInfo.fulled">
                            <CustomInput
                                @input="setRefundPercentAndAmount('amount', refundInAmount)"
                                :disabled="!!refundInPercent"
                                v-model="refundInAmount"
                                class="w-fit gap-3"
                                :table-input="true"
                                name="refundInAmount"
                                label="Refund in amount"
                                type="number"
                                placeholder="Enter amount"
                            />
                            <CustomInput
                                @input="setRefundPercentAndAmount('percent', refundInPercent)"
                                :disabled="!!refundInAmount && !!refundInPercen"
                                v-model="refundInPercent"
                                class="w-fit "
                                :table-input="true"
                                name="refundInPercent"
                                label="Refund in percent %"
                                type="number"
                                placeholder="Enter percent"
                            />
                        </div>

                    </div>
                </template>
            </div>
        </div>
    </template>
    <template v-if="generalParams?.vendor?.b2b">
        <div class="flex flex-wrap justify-end gap-4 mb-6">
            <button
                @click="handleExportProducts('order/exportProducts')"
                type="button"
                class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
            >
                <font-awesome-icon :icon="['far', 'file-export']"/>
                Export to Excel
            </button>
            <button
                @click="handleExportProducts('order/exportProducts2')"
                type="button"
                class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
            >
                <font-awesome-icon :icon="['far', 'file-export']"/>
                Export to Excel 2
            </button>
        </div>
    </template>

    <CustomTableSecond
        @addItem="newItem = {
            order_id: form.id,
            product_variant_id: null,
            tax_rate: form.tax_rate,
            language_id: form.language_id,
            product_variant_idRules: ['required'],
            quantity: 1,
            quantityRules: ['required'],
            price: 0,
            priceRules: ['required', 'validDecimal'],
            errors: {}
        }"
        :button-info="form.status_old == 3 && form.full_reshipment === null && auth.user_group.permissions_by_name.orders[0].can_add ? {
            title: 'Add item',
            emitName: 'add-item',
            icon: 'plus',
            classes: 'bg-meta-3',
            disabled: !!newItem
        } : null"
        title="Order items"
    >
        <template #content>
            <template v-if="newItem">
                <div class="flex flex-wrap items-end gap-3 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5">
                    <div class="w-[30%] max-md:w-[100%]">
                        <CustomSelect
                            @search-change="productsAutocomplete"
                            class="py-0 rounded-lg border-stroke bg-transparent"
                            v-model="newItem.product_variant_id"
                            @update:modelValue="newItem.errors = validate(newItem), getItemPrice()"
                            :searchable="true"
                            label="Product *"
                            placeholder="No selected product"
                            :options="productsOptions"
                            :need-autocomplete="true"
                            parent-div-classes="w-full"
                            :error="newItem.errors['product_variant_id']"
                        />
                    </div>
                    <div class="max-sm:w-[100%]">
                        <CustomInput
                            :table-input="true"
                            v-model="newItem.price"
                            name="price"
                            label="Price *"
                            type="text"
                            placeholder="Enter price"
                            @keyup="newItem.errors = validate(newItem)"
                            :error="newItem.errors['price']"
                        />
                    </div>
                    <div class="max-sm:w-[100%]">
                        <CustomInput
                            :table-input="true"
                            v-model="newItem.quantity"
                            name="quantity"
                            label="Quantity *"
                            type="number"
                            placeholder="Enter quantity"
                            @keyup="newItem.errors = validate(newItem)"
                            :error="newItem.errors['quantity']"
                        />
                    </div>
                    <div class="max-sm:w-[100%] flex justify-end">
                        <CustomButton
                            @click="saveNewItem"
                            class="flex items-center gap-2 mt-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                            Save
                        </CustomButton>
                    </div>
                </div>
            </template>
            <div class="overflow-x-auto">
                <div class="min-w-[1250px]">

                    <!-- Table Header -->
                    <div
                        class="grid grid-cols-9 gap-x-3 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5"
                    >
                        <div class="col-span-1 flex items-center">
                            <p class="font-medium">Image</p>
                        </div>
                        <div class="col-span-2 items-center sm:flex">
                            <p class="font-medium">Name / SKU / Attributes *</p>
                        </div>
                        <div class="col-span-1 flex items-center">
                            <p class="font-medium">Quantity</p>
                        </div>
                        <div class="col-span-1 flex items-center">
                            <p class="font-medium">Price</p>
                        </div>
                        <div class="col-span-1 flex items-center">
                            <p class="font-medium">Tax amount</p>
                        </div>
                        <div class="col-span-1 flex items-center">
                            <p class="font-medium">Subtotal</p>
                        </div>
                        <div class="col-span-1 flex items-center">
                            <p class="font-medium">Total</p>
                        </div>
                        <div class="col-span-1 flex items-center">
                            <p class="font-medium">Actions</p>
                        </div>
                    </div>

                    <!-- Table Rows -->
                    <template
                        v-for="(item, index) in form.order_items"
                        :key="index"
                    >
                        <div
                            v-if="!item.deleted"
                            class="grid grid-cols-9 gap-x-3 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5"
                            :class="{'bg-meta-1 bg-opacity-25': !item.order_item_parents || !item.order_item_parents.length}"
                        >
                            <div class="col-span-1 flex items-center">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                    <div class="h-auto w-[95%] rounded-md">
                                        <img
                                            :src="item.media ? item.media.path : 'https://www.diamondawl.co.uk/wp-content/uploads/2022/04/Image-Placeholder-Dark.png'"
                                            alt=""
                                        >
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-2  flex-col">
                                <p class="text-sm font-medium text-black">{{ item.name }}</p>
                                <p class="text-sm font-medium">{{ item.sku }}</p>
                                <p v-if="item.attributes" class="text-sm font-medium"
                                   v-html="item.attributes.replace(/~/g, ', ')"></p>
                            </div>
                            <div class="col-span-1 flex items-center">
                                <template v-if="item.editing">
                                    <CustomInput
                                        @input="editOrderItem(index), delete orderItemsErrors[index], emits('empty-tabs-with-errors', 'order_items')"
                                        :table-input="true"
                                        v-model="item.quantity"
                                        name="quantity"
                                        label=""
                                        type="number"
                                        placeholder="Enter quantity"
                                        :invalid-feedback-place="false"
                                        :error="orderItemsErrors[index] !== undefined && orderItemsErrors[index]['quantity'] ? orderItemsErrors[index]['quantity'] : null"
                                    />
                                </template>
                                <template v-else>
                                    <div>
                                        <div>
                                            <p class="text-sm font-medium text-black">
                                                {{ item.quantity }}
                                                <template v-if="item.order_item_refund">
                                        <span v-if="item.order_item_refund.quantity > 0" class="text-danger">
                                            -
                                            {{ item.order_item_refund.quantity }}
                                            <font-awesome-icon :icon="['fas', 'arrow-rotate-left']"/>
                                        </span>
                                                </template>
                                            </p>
                                        </div>
                                        <template
                                            v-if="refundInfo && (!item.order_item_refund || !item.order_item_refund.fulled)">
                                            <CustomInput
                                                :disabled="refundInfo.fulled_tax || refundInfo.fulled || refundInfo.type"
                                                :table-input="true"
                                                v-model="refundInfo.order_items[index].quantity"
                                                @input="setRefundMaxValue(index, 'quantity'), changeOrderItemRefund(index)"
                                                name="quantity"
                                                label=""
                                                placeholder="Refund quantity"
                                                :invalid-feedback-place="false"
                                            />
                                        </template>
                                    </div>
                                </template>
                            </div>
                            <div class="col-span-1 flex items-center">
                                <template v-if="item.editing">
                                    <CustomInput
                                        :table-input="true"
                                        @input="editOrderItem(index), delete orderItemsErrors[index], emits('empty-tabs-with-errors', 'order_items')"
                                        v-model="item.price"
                                        name="price"
                                        label=""
                                        type="text"
                                        placeholder="Enter price"
                                        :invalid-feedback-place="false"
                                        :error="orderItemsErrors[index] !== undefined && orderItemsErrors[index]['price'] ? orderItemsErrors[index]['price'] : null"
                                    />
                                </template>
                                <template v-else>
                                    <p class="text-sm font-medium text-black">
                                        {{ formatPrice(item.price, form.order_currency, true) }}
                                    </p>
                                </template>
                            </div>
                            <div class="col-span-1 flex items-center">

                                <p class="text-sm font-medium mr-1 text-black">
                                    {{ formatPrice(item.tax_price, form.order_currency, true) }}
                                    <template v-if="item.order_item_refund">
                                        <p class="text-danger">
                                            -
                                            {{
                                                formatPrice(item.order_item_refund.tax_price, form.order_currency, true)
                                            }}
                                            <font-awesome-icon :icon="['fas', 'arrow-rotate-left']"/>
                                        </p>
                                    </template>
                                </p>
                            </div>
                            <div class="col-span-1 flex items-center">
                                <p class="text-sm font-medium w-full text-black">
                                    {{ formatPrice(item.subtotal, form.order_currency, true) }}
                                    <template v-if="item.order_item_refund">
                                        <p v-if="item.order_item_refund.subtotal > 0" class="text-danger w-full">
                                            -
                                            {{
                                                formatPrice(item.order_item_refund.subtotal, form.order_currency, true)
                                            }}
                                            <font-awesome-icon :icon="['fas', 'arrow-rotate-left']"/>
                                        </p>
                                    </template>
                                </p>
                            </div>
                            <div class="col-span-1 flex items-center mr-3">

                                <p class="text-sm font-medium text-black">
                                    {{ formatPrice(item.total, form.order_currency, true) }}
                                    <template v-if="item.order_item_refund">
                                        <p class="text-danger">
                                            -
                                            {{ formatPrice(item.order_item_refund.total, form.order_currency, true) }}
                                            <font-awesome-icon :icon="['fas', 'arrow-rotate-left']"/>
                                        </p>
                                    </template>
                                </p>
                            </div>
                            <div class="col-span-1 flex items-center">
                                <template
                                    v-if="form.status_old == 3 && form.full_reshipment === null && auth.user_group.permissions_by_name.orders[0].can_edit">
                                    <template v-if="!item.editing">
                                        <button
                                            @click="item.editing = true"
                                            type="button"
                                            class="text-black hover:text-primary"
                                            title="Edit"
                                        >
                                            <font-awesome-icon :icon="['far', 'pen-to-square']"/>
                                        </button>
                                    </template>
                                    <template v-else>
                                        <button
                                            @click="item.editing = false"
                                            type="button"
                                            class="text-black hover:text-primary"
                                            title="Edit"
                                        >
                                            <font-awesome-icon :icon="['far', 'check']"/>
                                        </button>
                                    </template>
                                </template>
                                <template
                                    v-if="item.product_id && auth.user_group.permissions_by_name.products[0].can_view">
                                    <a
                                        class="ml-2"
                                        :href="'/catalog/products/update/'+ item.product_id +'/-1'"
                                        target="_blank"
                                    >
                                        <font-awesome-icon
                                            class="text-black hover:text-primary"
                                            :icon="['fasr', 'eye']"
                                        />
                                    </a>
                                </template>
                                <button
                                    type="button"
                                    class="text-black hover:text-danger ml-2"
                                    title="items"
                                >
                                    <font-awesome-icon
                                        @click="warehouseItemIndexShowing = index, warehouseItemsPopup = true"
                                        class="text-black hover:text-primary"
                                        :icon="['far', 'circle-info']"
                                    />
                                </button>
                                <template
                                    v-if="form.status_old == 3 && form.full_reshipment === null && auth.user_group.permissions_by_name.orders[0].can_delete">
                                    <button
                                        @click="item.deleted = true, calculateOrderTotal()"
                                        type="button"
                                        class="text-black hover:text-danger ml-2"
                                        title="Edit"
                                    >
                                        <font-awesome-icon :icon="['far', 'trash']"/>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div
                class="flex justify-end border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5">
                <div class="flex flex-col">
                    <div>
                        <span class="text-base">Items subtotal:</span>
                        <span class="text-xs text-black-2 ml-2 font-bold">
                            {{ formatPrice(form.items_subtotal_price, form.order_currency, true) }}
                         </span>
                    </div>
                    <template v-if="parseFloat(form.items_subtotal_tax)">
                        <div>
                            <span class="text-base">Items subtotal tax amount:</span>
                            <span class="text-xs text-black-2 ml-2 font-bold">
                                  {{ formatPrice(form.items_subtotal_tax, form.order_currency, true) }}
                            </span>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </CustomTableSecond>


    <CustomTableSecond
        title="Shipping"
    >
        <template #content>
            <div class="overflow-x-auto">
                <div class="min-w-[1250px]">
                    <div
                        class="grid gap-x-2 grid-cols-7 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5"
                    >
                        <div class="col-span-2 items-center">
                            <p class="font-medium">Name</p>
                        </div>
                        <div class="col-span-1 flex items-center">
                            <p class="font-medium">Carrier</p>
                        </div>
                        <div class="col-span-1 flex items-center">
                            <p class="font-medium">Price</p>
                        </div>
                        <div class="col-span-1 flex items-center">
                            <p class="font-medium">Tax amount</p>
                        </div>
                        <div class="col-span-1 flex items-center">
                            <p class="font-medium">Total</p>
                        </div>
                        <div class="col-span-1 flex items-center">
                            <p class="font-medium">Actions</p>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-7 gap-x-2 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5">
                        <div class="col-span-2  items-center">
                            <template v-if="form.shipping_editing">
                                <CustomInput
                                    @input="delete form.errors.general, emits('empty-tabs-with-errors', 'order_items')"
                                    :error="form.errors.general !== undefined && form.errors.general['shipping_method_name_base'] ? form.errors.general['shipping_method_name'] : null"
                                    :table-input="true"
                                    v-model="form.shipping_method_name_base"
                                    name="shipping_method_name_base"
                                    label=""
                                    type="text"
                                    placeholder="Enter shipping method name"
                                    :invalid-feedback-place="false"
                                />
                            </template>
                            <template v-else>
                                <p class="text-sm font-medium text-black">
                                    {{ form.shipping_method_name_base }}</p>
                            </template>
                        </div>
                        <div class="col-span-1 items-center mr-3">
                            <template v-if="form.shipping_editing">
                                <CustomSelect
                                    parent-div-classes="w-full"
                                    label=""
                                    placeholder="Select carrier"
                                    v-model="form.shipping_carrier"
                                    mode="single"
                                    :options="params.carriersForParams"
                                    :searchable="false"
                                    :canClear="false"
                                    :invalid-feedback-place="false"
                                    class="py-0 rounded-lg border-stroke bg-transparent"
                                />
                            </template>
                            <template v-else>
                                <p class="text-sm font-medium text-black">
                                    {{ params.carriers[form.shipping_carrier] }}</p>
                            </template>
                        </div>
                        <div class="col-span-1 items-center mr-3">
                            <template v-if="form.shipping_editing">
                                <CustomInput
                                    @input="editShipping(), delete form.errors.general, emits('empty-tabs-with-errors', 'order_items')"
                                    :error="form.errors.general !== undefined && form.errors.general['shipping_price'] ? form.errors.general['shipping_price'] : null"
                                    :table-input="true"
                                    v-model="form.shipping_price"
                                    name="shipping_price"
                                    label=""
                                    type="text"
                                    placeholder="Enter price"
                                    :invalid-feedback-place="false"
                                />
                            </template>
                            <template v-else>
                                <div>
                                    <div>
                                        <p class="text-sm font-medium text-black">
                                            {{ formatPrice(form.shipping_price, form.order_currency, true) }}
                                            <template
                                                v-if="form.order_refund && parseFloat(form.order_refund.shipping_refund_amount)">
                                    <span class="text-danger">
                                        -
                                        {{
                                            formatPrice(form.order_refund.shipping_refund_amount, form.order_currency, true)
                                        }}
                                        <font-awesome-icon :icon="['fas', 'arrow-rotate-left']"/>
                                    </span>
                                            </template>
                                        </p>
                                    </div>
                                    <template
                                        v-if="refundInfo && (!form.order_refund || parseFloat(form.order_refund.shipping_refund_amount) < parseFloat(form.shipping_price))">
                                        <CustomInput
                                            :disabled="refundInfo.fulled_tax || refundInfo.fulled || refundInfo.type"
                                            :table-input="true"
                                            v-model="refundInfo.shipping_price"
                                            @input="setRefundMaxValue(null, 'shipping_price'), changeOrderShippingPrice()"
                                            name="refunded_shipping_price"
                                            label=""
                                            type="text"
                                            placeholder="Refund amount"
                                            :invalid-feedback-place="false"
                                        />
                                    </template>
                                </div>
                            </template>
                        </div>
                        <div class="col-span-1 items-center">
                            <p class="text-sm font-medium text-black">
                                {{ formatPrice(form.shipping_subtotal_tax, form.order_currency, true) }}
                                <template v-if="form.order_refund && form.order_refund.shipping_tax_amount">
                                    <br>
                                    <span v-if="form.order_refund.shipping_tax_amount > 0" class="text-danger">
                                -
                                {{ formatPrice(form.order_refund.shipping_tax_amount, form.order_currency, true) }}
                                <font-awesome-icon :icon="['fas', 'arrow-rotate-left']"/>
                            </span>
                                </template>
                            </p>
                        </div>
                        <div class="col-span-1 flex items-center">
                            <p class="text-sm font-medium text-black">
                                {{ formatPrice(form.total_shipping_price, form.order_currency, true) }}
                                <template
                                    v-if="form.order_refund && parseFloat(form.order_refund.total_shipping_refund_amount)">
                        <span class="text-danger">
                            -
                            {{ formatPrice(form.order_refund.total_shipping_refund_amount, form.order_currency, true) }}
                            <font-awesome-icon :icon="['fas', 'arrow-rotate-left']"/>
                        </span>
                                </template>
                            </p>
                        </div>
                        <div class="col-span-1 flex items-center">
                            <template
                                v-if="form.status_old == 3 && form.full_reshipment === null && auth.user_group.permissions_by_name.orders[0].can_edit">
                                <template v-if="!form.shipping_editing">
                                    <button
                                        @click="form.shipping_editing = true"
                                        type="button"
                                        class="text-black hover:text-primary"
                                        title="Edit"
                                    >
                                        <font-awesome-icon :icon="['far', 'pen-to-square']"/>
                                    </button>
                                </template>
                                <template v-else>
                                    <button
                                        @click="form.shipping_editing = false"
                                        type="button"
                                        class="text-black hover:text-primary"
                                        title="Edit"
                                    >
                                        <font-awesome-icon :icon="['far', 'check']"/>
                                    </button>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="flex justify-end border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5">
                <div class=" flex flex-col">
                    <template v-if="parseFloat(form.zip_fee)">
                        <div>
                            <span class="text-base ">{{ form.zip_fee_label }}:</span>
                            <span class="text-xs text-black-2 ml-2 font-bold">
                                {{ formatPrice(form.zip_fee, form.order_currency, true) }}
                            </span>
                        </div>
                    </template>
                    <template v-if="parseFloat(form.zip_fee_tax)">
                        <div>
                            <span class="text-base ">{{ form.zip_fee_label }} tax:</span>
                            <span class="text-xs text-black-2 ml-2 font-bold">
                                {{ formatPrice(form.zip_fee_tax, form.order_currency, true) }}
                            </span>
                        </div>
                    </template>
                    <template v-if="parseFloat(form.total_discount_price)">
                        <div>
                            <span class="text-base ">Discounted amount:</span>
                            <span class="text-xs text-black-2 ml-2 font-bold">
                                 {{ formatPrice(form.total_discount_price, form.order_currency, true) }}
                            </span>
                        </div>
                    </template>
                    <div class="font-bold">
                        <span class="text-base">Total amount:</span>
                        <span class="text-base text-black-2 ml-2">
                        {{ formatPrice(form.total_price, form.order_currency, true) }}
                          <template v-if="form.order_refund">
                              <span class="text-danger">
                                      -
                                   {{ formatPrice(form.order_refund.refund_amount, form.order_currency, true) }}
                                  <font-awesome-icon :icon="['fas', 'arrow-rotate-left']"/>
                             </span>
                          </template>
                    </span>
                    </div>

                    <template v-if="form.order_refund">
                        <div class="font-bold">
                            <span class="text-base">Net Payment:</span>
                            <span class="text-base text-black-2 ml-2">
                                {{
                                    formatPrice(parseFloat(form.total_price) - parseFloat(form.order_refund.refund_amount), form.order_currency, true)
                                }}
                            </span>
                        </div>
                    </template>
                    <template v-if="refundInfo">
                        <hr class="text-gray my-3">
                        <div class="font-bold">
                            <span class="text-base">Refund amount:</span>
                            <span class="text-base text-black-2 ml-2">
                                {{ formatPrice(refundInfo.total_price, form.order_currency, true) }}
                            </span>
                        </div>
                    </template>

                    <template v-if="refundInfo">
                        <hr class="text-gray my-3">
                        <div class="font-bold">
                            <Switch
                                @change="(value) => {
                                    refundInfo.manual_refund = value;
                                }"
                                :value="refundInfo.manual_refund"
                                id="manual_refund"
                                label="Manual Refund (No money will be refunded)"
                            />
                        </div>
                    </template>
                    <template v-if="(!form.order_refund || !form.order_refund.fulled) && refundInfo">
                        <div class="">
                            <div class="flex flex-col gap-2 py-6.5 pb-3 max-w-[300px] w-[100%]">
                                <CustomSelect
                                    class="py-0 rounded-lg border-stroke bg-transparent"
                                    :searchable="true"
                                    v-model="selectedRefund"
                                    label="Refund reason type *"
                                    placeholder="No selected refund reason"
                                    :options="params.refundReasons"
                                    :need-autocomplete="true"
                                    parent-div-classes="w-full"
                                    @update:modelValue="handleChange"
                                />
                                <CustomInput
                                    v-if="selectedRefund === 'Other'"
                                    class="w-full"
                                    :table-input="true"
                                    v-model="refundInfo.refund_reason"
                                    name="refund_reason"
                                    label="Refund reason *"
                                    type="text"
                                    placeholder="Refund reason"
                                    :tooltip="true"
                                    @keyup="refundInfo.errors = validate(refundInfo)"
                                    :error="refundInfo.errors['refund_reason']"
                                />
                                <template v-if="parseFloat(refundInfo.total_price)">
                                    <div class="flex w-full  pb-0 col-span-1">
                                        <CustomButton
                                            @click="refundOrder"
                                            type="button"
                                            class="flex w-full justify-center gap-2 rounded bg-primary mt-auto mb-auto py-3 px-4.5 font-medium text-white hover:bg-opacity-80"
                                        >
                                            <div>
                                                <font-awesome-icon :icon="['fas', 'check']"/>
                                            </div>
                                            Confirm
                                        </CustomButton>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                    <template v-if="!form.payment_date && form.status_old === 3 && !form.coupon_code">
                        <div class="">
                            <div class="flex items-end py-6.5 pb-3 max-w-[300px] w-[100%]">
                                <CustomInput
                                    class="w-full"
                                    @input="applayingCoupon.errors = null"
                                    :table-input="true"
                                    v-model="applayingCoupon.coupon_code"
                                    name="coupon_code"
                                    label="Apply coupon:"
                                    type="text"
                                    placeholder="Coupon code"
                                    :error="applayingCoupon.errors"
                                    :tooltip="true"
                                />
                                <CustomButton
                                    :disabled="!applayingCoupon.coupon_code"
                                    @click="applyCoupon"
                                    type="button"
                                    class="flex h-fit items-center gap-2 rounded-tr rounded-br bg-primary py-3 px-4.5 font-medium text-white hover:bg-opacity-80"
                                >
                                    <div>
                                        <font-awesome-icon :icon="['fas', 'check']"/>
                                    </div>
                                </CustomButton>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </CustomTableSecond>


    <div ref="pageBottom"></div>
</template>

<style lang="scss">
.table-default-holder {
    > div:first-child {
        flex-wrap: wrap;
        gap: 7px;

        > div {
            min-width: unset;
            @media (max-width: 576px) {
                width: auto;
            }

        }
    }
}
</style>
