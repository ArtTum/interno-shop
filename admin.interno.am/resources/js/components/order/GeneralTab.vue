<script setup>

import CustomSelect from "@components/global/CustomSelect.vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomTextarea from "@components/global/CustomTextarea.vue";

import {computed, ref, toRefs, watch} from "vue";
import {useStore} from "vuex";

const store = useStore();
import {formatPrice} from "@/utils/formatPrice.js";
import CustomButton from "@components/global/CustomButton.vue";
import CustomCopyButton from "@components/global/CustomCopyButton.vue";

const emits = defineEmits([
    'update:modelValue',
    'empty-tabs-with-errors'
]);

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    params: {
        type: Object
    }
});
const localOrderStatusesParams = ref([]);
const localOrderStatusesParams2 = ref([]);
const auth = computed(() => store.getters['auth/getUser']);
const {modelValue} = toRefs(props);
const form = ref(modelValue.value);

watch(
    () => props.params.orderStatusesParams,
    (newVal) => {
        if (newVal) {
            localOrderStatusesParams.value =
                newVal.filter((status) => status.value !== -1).map((status) => {

                    if (status.value === 5 && form.value?.payment_date) {
                        return {...status, disabled: true};
                    }

                    if (status.value === 6 || status.value === 1) {
                        return {...status, disabled: true};
                    }
                    return status;
                });

            localOrderStatusesParams2.value =
                newVal.filter((status) => status.value !== -1).map((status) => {

                    if (status.value === 5 && form.value?.payment_date) {
                        return {...status, disabled: true};
                    }

                    if (status.value === 1) {
                        return {...status, disabled: true};
                    }
                    return status;
                });
        }
    },
    {immediate: true}
)


watch(modelValue, (newVal) => {
    form.value = newVal;
}, {immediate: true});

watch(form.value, (newVal) => {
    emits('update:modelValue', newVal);
});

const refundOrder = (country, zip, state, city, address1, address2) => {
    let addressParams = [
        address1,
        address2,
        city,
        state,
        zip,
        country
    ].filter(Boolean).join(', ')

    return `https://maps.google.com/maps?&q=${addressParams}`
};

const updateJustStatus = async () => {
    await store.dispatch('order/updateJustStatus', {
        id: form.value.id,
        status_old: form.value.status_old,
        status: form.value.status,
    });

    form.value.status_old = form.value.status;

    store.commit('notification/SET_NOTIFICATION', {
        visible: true,
        title: 'Success',
        message: 'Successfully updated'
    });
}
</script>

<template>
    <div>
        <div class="">
            <template v-if="form.full_reshipment !== null">
                <div class="text-danger">
                    <font-awesome-icon :icon="['fas', 'repeat']" class="mr-2"/>
                    <span>This order is reshipment for order
                <a
                    class="hover-trigger hover:text-primary underline font-bold"
                    :href="'/orders/show/' + form.parent_id"
                    target="_blank"
                >#{{ form.parent_id }}
                    <font-awesome-icon
                        class="hover:text-primary"
                        :icon="['fass', 'up-right-from-square']"
                    />
                </a>
                (
                <template v-if="form.full_reshipment === 1">
                    Full reshipment
                </template>
                <template v-else-if="form.full_reshipment === 0">
                    Partial reshipment
                </template>
                )</span>
                </div>
                <hr class="text-gray my-6">
            </template>
            <template v-if="form.reshipments?.length">
                <div class="text-danger">
                    <font-awesome-icon :icon="['fas', 'repeat']" class="mr-2"/>
                    <span>This order has reshipment order<template
                        v-if="form.reshipments.length > 1">s</template>:
                <template v-for="(reshipment, key) in form.reshipments" :key="key">
                     <a
                         class="hover-trigger hover:text-primary underline font-bold mr-2"
                         :href="'/orders/show/' + reshipment.id"
                         target="_blank"
                     >#{{ reshipment.id }}
                    <font-awesome-icon
                        class="hover:text-primary"
                        :icon="['fass', 'up-right-from-square']"
                    /><template v-if="(form.reshipments.length - 1) !== key">,</template>
                </a>
                </template>
            </span>
                </div>
                <hr class="text-gray my-6">
            </template>
            <div class="mb-3">
                <font-awesome-icon class="text-danger" :icon="['fasr', 'circle-exclamation']"/>
                <span class="text-danger font-bold ml-1">Just status, without any action (Temporary opportunity, only when there is issue in order):</span>
            </div>
            <div class="ml-3 flex flex-wrap gap-3 max-xsm:ml-0">
                <div class="min-w-[200px]">
                    <CustomSelect
                        :invalid-feedback-place="false"
                        :disabled="!auth.user_group.permissions_by_name.orders[0].can_edit"
                        label=""
                        placeholder="Choose carrier"
                        v-model="form.status"
                        mode="single"
                        :options="localOrderStatusesParams2"
                        :searchable="false"
                        :canClear="false"
                        class="py-0 rounded-lg border-stroke bg-transparent"
                    />
                </div>
                <div>
                    <CustomButton
                        :disabled="form.status === form.status_old"
                        type="button"
                        @click="updateJustStatus"
                        class="ml-auto  gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                    >
                        <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                        Update Just status, without any action
                    </CustomButton>
                </div>
            </div>

        </div>
        <hr class="text-gray my-6">
        <div class="grid grid-cols-2 gap-8 max-md:grid-cols-1">
            <div class=" relative">
                <div class="p-4 shadow-2 border border-stroke rounded-md sticky top-[95px] left-0">
                    <div class="flex flex-col gap-4">
                        <div class="font-bold text-title-xsm">
                            <font-awesome-icon :icon="['fass', 'hashtag']"/>
                            <span class="text-base ml-1">Order number:</span>
                            <span class="text-base text-black-2 ml-2">{{ form.id }}</span>
                        </div>
                        <div class="font-bold mb-4 text-title-xsm">
                            <font-awesome-icon :icon="['fas', 'business-time']"/>
                            <span class="text-base ml-1">Order date:</span>
                            <span class="text-base text-black-2 ml-2">{{ form.created_at }}</span>
                        </div>
                        <div class="flex items-center">
                            <div>
                                <font-awesome-icon :icon="['fasr', 'language']"/>
                                <span class="text-base ml-1">Language:</span>
                            </div>
                            <div class="w-1/3 ml-3">
                                <div class="flex">
                                    <img
                                        class="ml-1"
                                        width="25px"
                                        :src="`/flags/${form.language_code.toLowerCase()}.svg`"
                                        :alt="form.language_code"
                                    >
                                    <span class="text-base text-black-2 ml-2">{{ form.language_name }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center flex-wrap">
                            <div>
                                <font-awesome-icon :icon="['fasr', 'circle-exclamation']"/>
                                <span class="text-base ml-1">Order status:</span>
                            </div>
                            <div class="w-[100%] max-w-[160px] ml-3">
                                <CustomSelect
                                    :invalid-feedback-place="false"
                                    :disabled="!auth.user_group.permissions_by_name.orders[0].can_edit"
                                    label=""
                                    placeholder="Choose carrier"
                                    v-model="form.status"
                                    mode="single"
                                    :options="localOrderStatusesParams"
                                    :searchable="false"
                                    :canClear="false"
                                    class="py-0 rounded-lg border-stroke bg-transparent"
                                />
                            </div>
                        </div>
                        <div class="flex items-center flex-wrap">
                            <div>
                                <font-awesome-icon :icon="['fasr', 'circle-exclamation']"/>
                                <span class="text-base ml-1">Warehouse status:</span>
                            </div>
                            <div class="w-[100%] max-w-[160px] ml-3">
                                <p
                                    class="inline-flex rounded-full py-1 px-3 text-sm font-medium"
                                    :class="{
                         'bg-primary text-white bg-opacity-80': form.warehouse_status === null,
                         'bg-warning text-warning bg-opacity-10': form.warehouse_status == 0 || form.warehouse_status == 1 || form.warehouse_status == 5,
                         'bg-danger text-danger bg-opacity-10': form.warehouse_status == 2 || form.warehouse_status == 3 || form.warehouse_status == 6,
                         'bg-success text-success bg-opacity-10': form.warehouse_status === 4
                    }"
                                >
                                    <template v-if="form.warehouse_status === null">
                                        Not in warehouse
                                    </template>
                                    <template v-else>
                                        {{ params.warehouseStatuses?.[form.warehouse_status] }}
                                    </template>
                                </p>

                            </div>
                        </div>
                        <div class="flex items-center">
                            <font-awesome-icon :icon="['fasr', 'circle-exclamation']"/>
                            <span class="text-base ml-1">Same day delivery:</span>
                            <span class="text-base text-black-2 ml-2">{{
                                    form.same_day_delivery ? 'Yes!!!' : 'No'
                                }}</span>
                        </div>
                        <div class="flex items-center">
                            <font-awesome-icon :icon="['fasr', 'file']"/>
                            <span class="text-base ml-1">Processed by tax exporter:</span>
                            <span class="text-base text-black-2 ml-2">{{
                                    form.exported_taxfile ? 'Yes!!!' : 'No'
                                }}</span>
                        </div>
                        <div class="flex items-center">
                            <font-awesome-icon :icon="['fasr', 'circle-exclamation']"/>
                            <span class="text-base ml-1">Stopped:</span>
                            <CustomInput
                                class="ml-1"
                                :disabled="!auth.user_group.permissions_by_name.orders[0].can_edit || form.warehouse_status === 4"
                                v-model="form.stopped"
                                name="shipping"
                                type="checkbox"
                                :tableInput="true"
                            />
                        </div>
                        <div class="flex items-center">
                            <font-awesome-icon :icon="['fasr', 'circle-exclamation']"/>
                            <span class="text-base ml-1">Express:</span>
                            <CustomInput
                                class="ml-1"
                                :disabled="!auth.user_group.permissions_by_name.orders[0].can_edit || form.warehouse_status === 4"
                                v-model="form.express"
                                name="shipping"
                                type="checkbox"
                                :tableInput="true"
                            />
                        </div>

                        <div class="flex items-center flex-wrap gap-2">
                            <font-awesome-icon :icon="['far', 'credit-card']"/>
                            <span class="text-base ml-1">Payment:</span>
                            <template v-if="form.status_old === 3">
                                <CustomSelect
                                    :invalid-feedback-place="false"
                                    :disabled="!auth.user_group.permissions_by_name.orders[0].can_edit"
                                    label=""
                                    placeholder="Payment methods"
                                    v-model="form.payment_method_parent"
                                    mode="single"
                                    :options="form.on_hold_payment_methods"
                                    :searchable="false"
                                    :canClear="false"
                                    class="py-0 rounded-lg border-stroke bg-transparent w-[220px]"
                                />
                            </template>
                            <span class="text-base text-black-2">
                <template v-if="form.status_old !== 3">
                  {{
                        form.payment_method_parent + ', (' + form.payment_method_child_base + ')'
                    }}
                </template>
                <template v-if="form.payment_date"> | Paid on: {{ form.payment_date }}</template></span>
                            <CustomSelect
                                :invalid-feedback-place="false"
                                :disabled="!auth.user_group.permissions_by_name.orders[0].can_edit || form.warehouse_status === 4"
                                label=""
                                placeholder="Paid status"
                                v-model="form.paid_status"
                                mode="single"
                                :options="[
                    {value: 'paid', label: 'Paid'},
                    {value: 'not_paid', label: 'Not paid'},
                ]"
                                :searchable="false"
                                :canClear="false"
                                class="py-0 rounded-lg border-stroke bg-transparent w-[150px]"
                            />
                        </div>
                        <template v-if="form.full_reshipment === null && form.transaction_id">
                            <div class="flex">
                                <div>
                                    <font-awesome-icon :icon="['far', 'receipt']"/>
                                </div>
                                <div>
                                    <span class="text-base ml-1">Transaction ID:</span>
                                </div>

                                <span class="text-base text-black-2 ml-2">{{ form.transaction_id }}</span>
                                <template v-if="form.payment_method_parent === 'Stripe'">
                                    <a
                                        class="hover-trigger hover:text-primary"
                                        :href="`https://dashboard.stripe.com/payments/${form.transaction_id}`"
                                        target="_blank"
                                    >
                                        <font-awesome-icon
                                            class="text-black-2 ml-2"
                                            :icon="['fass', 'up-right-from-square']"
                                        />
                                    </a>
                                </template>
                                <template v-else-if="form.payment_method_parent === 'Paypal'">
                                    <a
                                        class="hover-trigger hover:text-primary"
                                        :href="`https://www.paypal.com/cgi-bin/webscr?cmd=_view-a-trans&id=${form.transaction_id}`"
                                        target="_blank"
                                    >
                                        <font-awesome-icon
                                            class="text-black-2 ml-2"
                                            :icon="['fass', 'up-right-from-square']"
                                        />
                                    </a>
                                </template>
                            </div>
                        </template>
                        <template v-if="form.coupon_code">
                            <div>
                                <font-awesome-icon :icon="['far', 'money-bill']"/>
                                <span class="text-base ml-1">Used coupon:</span>
                                <span class="text-base text-black-2 ml-2">{{ form.coupon_code }}</span>
                                <template
                                    v-if="form.coupon_id && auth.user_group.permissions_by_name.coupons[0].can_view">
                                    <a
                                        class="ml-2"
                                        :href="'/marketing/coupons/update/' + form.coupon_id"
                                        target="_blank"
                                    >
                                        <font-awesome-icon
                                            class="text-black-2 hover:text-primary"
                                            :icon="['fass', 'up-right-from-square']"
                                        />
                                    </a>
                                </template>
                            </div>
                        </template>
                        <div class="flex items-center flex-wrap">
                            <div>
                                <font-awesome-icon :icon="['far', 'truck-fast']"/>
                                <span class="text-base ml-1">Alternative shipping:</span>
                            </div>
                            <div class="w-[100%] ml-3 max-w-[300px]">
                                <CustomSelect
                                    :invalid-feedback-place="false"
                                    :disabled="!auth.user_group.permissions_by_name.orders[0].can_edit || form.warehouse_status === 4"
                                    label=""
                                    placeholder="Alternative shipping"
                                    v-model="form.alternative_shipping"
                                    mode="single"
                                    :options="params.alternativeShippingMethods"
                                    :searchable="false"
                                    :canClear="false"
                                    class="py-0 rounded-lg border-stroke bg-transparent"
                                />
                            </div>
                        </div>

                        <template v-if="form.dispute_state">
                            <div
                                class="inline-flex rounded-full bg-opacity-10 py-2 px-5 text-sm font-medium items-center"
                                :class="{'bg-warning text-warning': form.dispute_state === 1,
                  'bg-danger text-danger': form.dispute_state === 2 || form.dispute_state === 4,
                  'bg-success text-success': form.dispute_state === 3
              }"
                            >
                                <template v-if="form.dispute_state === 1">
                                    <font-awesome-icon :icon="['far', 'clock']" class="mr-2"/>
                                    <span>Dispute: In process</span>
                                </template>
                                <template v-else-if="form.dispute_state === 3">
                                    <font-awesome-icon :icon="['far', 'shield-check']" class="mr-2"/>
                                    <span>Dispute: Won</span>
                                </template>
                                <template v-else>
                                    <font-awesome-icon :icon="['far', 'shield-exclamation']" class="mr-2"/>
                                    <span>Dispute: Lost</span>
                                </template>
                            </div>
                        </template>


                        <template v-if="form.cart_path">
                            <div>
                                <font-awesome-icon :icon="['far', 'cart-shopping']"/>
                                <span class="text-base ml-1">Restore cart from order items:</span>
                                <a
                                    class="ml-2 hover-trigger hover:text-primary"
                                    :href="form.cart_path"
                                    target="_blank"
                                >
                                    <span class="text-base text-black-2">{{ form.cart_path }}</span>
                                    <font-awesome-icon
                                        class="text-black-2 ml-2"
                                        :icon="['fass', 'up-right-from-square']"
                                    />
                                </a>
                            </div>
                        </template>

                        <template v-if="form.campaign_email_id">
                            <div>
                                <font-awesome-icon :icon="['fab', 'telegram']"/>
                                <span class="text-base ml-1">From campaign email:</span>
                                <span class="text-base text-black-2 ml-2">#{{ form.campaign_email_id }}</span>
                                <a
                                    class="ml-2"
                                    :href="'/newsletter/email-ads/update/' + form.campaign_email_id"
                                    target="_blank"
                                >
                                    <font-awesome-icon
                                        class="text-black-2 hover:text-primary"
                                        :icon="['fass', 'up-right-from-square']"
                                    />
                                </a>
                            </div>
                        </template>
                        <template v-if="form.shared_cart_user_id">
                            <div>
                                <font-awesome-icon :icon="['far', 'cart-shopping']"/>
                                <span class="text-base ml-1">By shared cart (ID and created by): </span>
                                <span class="text-base text-black-2">#{{ form.shared_cart_id }} | {{
                                        form.shared_cart_user_name
                                    }}</span>
                            </div>
                        </template>
                        <template v-if="form.offer_user_id">
                            <div>
                                <font-awesome-icon :icon="['far', 'cart-shopping']"/>
                                <span class="text-base ml-1">By offer (ID and created by): </span>
                                <span class="text-base text-black-2">#{{ form.offer_id }} | {{
                                        form.offer_user_name
                                    }}</span>
                            </div>
                        </template>

                    </div>
                </div>

            </div>
            <div class="">
                <div class="p-4 shadow-2 border border-stroke  rounded-md mb-6">
                    <h3 class="text-title-sm2 font-bold text-black-2">Order Summary</h3>
                    <hr class="text-gray my-4">
                    <div class="grid grid-cols-2 gap-4 max-xl:grid-cols-1">

                        <div class="flex flex-col gap-2">
                            <template v-if="parseFloat(form.shipping_cost)">

                                <div>
                                    <font-awesome-icon :icon="['far', 'truck-clock']"/>
                                    <span class="text-base">Shipping cost:</span>
                                    <span class="text-base text-black-2 ml-2">
                    {{ formatPrice(form.shipping_cost, form.baseCurrencyCode, true) }}
              </span>
                                </div>

                            </template>
                            <div>
                                <span class="text-base ">Items subtotal:</span>
                                <span class="text-xs text-black-2 ml-2 font-bold">
              {{ formatPrice(form.items_subtotal_price, form.order_currency, true) }}
          </span>
                            </div>
                            <template v-if="parseFloat(form.items_subtotal_tax)">
                                <div>
                                    <span class="text-base ">Items subtotal tax amount:</span>
                                    <span class="text-xs text-black-2 ml-2 font-bold">
                  {{ formatPrice(form.items_subtotal_tax, form.order_currency, true) }}</span>
                                </div>
                            </template>
                            <template v-if="parseFloat(form.zip_fee)">
                                <div>
                                    <span class="text-base ">{{ form.zip_fee_label }}:</span>
                                    <span class="text-xs text-black-2 ml-2 font-bold">
                  {{ formatPrice(form.zip_fee, form.order_currency, true) }}</span>
                                </div>
                            </template>
                            <template v-if="parseFloat(form.zip_fee_tax)">
                                <div>
                                    <span class="text-base ">{{ form.zip_fee_label }} tax:</span>
                                    <span class="text-xs text-black-2 ml-2 font-bold">
                  {{ formatPrice(form.zip_fee_tax, form.order_currency, true) }}</span>
                                </div>
                            </template>
                            <template v-if="parseFloat(form.shipping_price)">
                                <div>
                                    <span class="text-base ">Shipping price:</span>
                                    <span class="text-xs text-black-2 ml-2 font-bold">
                  {{ formatPrice(form.shipping_price, form.order_currency, true) }}</span>
                                </div>
                            </template>
                            <template v-if="parseFloat(form.shipping_subtotal_tax)">
                                <div>
                                    <span class="text-base ">Shipping subtotal tax amount:</span>
                                    <span class="text-xs text-black-2 ml-2 font-bold">
                  {{ formatPrice(form.shipping_subtotal_tax, form.order_currency, true) }}</span>
                                </div>
                            </template>
                            <template v-if="parseFloat(form.total_tax)">
                                <div>
                                    <span class="text-base ">Total tax amount:</span>
                                    <span class="text-xs text-black-2 ml-2 font-bold">
                  {{ formatPrice(form.total_tax, form.order_currency, true) }}</span>
                                </div>
                            </template>
                            <template v-if="parseFloat(form.total_discount_price)">
                                <div>
                                    <span class="text-base">Discounted amount:</span>
                                    <span class="text-xs text-black-2 ml-2 font-bold">
                  {{ formatPrice(form.total_discount_price, form.order_currency, true) }}</span>
                                </div>
                            </template>
                            <div class="font-bold mt-4 text-title-xsm">
                                <span class="">Total amount:</span>
                                <span class=" text-black-2 ml-2">{{
                                        formatPrice(form.total_price, form.order_currency, true)
                                    }}</span>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <div>
                                <font-awesome-icon :icon="['fas', 'weight-scale']"/>
                                <span class="text-base ml-1">Net weight:</span>
                                <span class="text-base text-black-2 ml-2"> {{
                                        formatPrice(form.total_net_weight)
                                    }}</span>
                            </div>
                            <div>
                                <font-awesome-icon :icon="['fas', 'weight-scale']"/>
                                <span class="text-base ml-1">Gross weight:</span>
                                <span class="text-base text-black-2 ml-2">
                {{ formatPrice(form.total_weight) }}
            </span>
                            </div>
                            <template v-if="form.full_reshipment === null">
                                <div>
                                    <font-awesome-icon :icon="['far', 'chart-pie-simple-circle-dollar']"/>
                                    <span class="text-base ml-1">Margin:</span>
                                    <span class="text-base text-black-2 ml-2">
                                {{ formatPrice(form.margin, form.baseCurrencyCode, true) }}
              </span>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>
                <div class="p-4 shadow-2 border border-stroke rounded-md customer-details-holder">
                    <h3 class="text-title-sm2 font-bold text-black-2">Customer Details</h3>
                    <hr class="text-gray my-4">
                    <div class="grid grid-cols-2 gap-4 max-xl:grid-cols-1 max-xl:gap-4">
                        <div>
                            <p class="font-bold text-black-2 mb-3">
                                Billing address
                                <template v-if="!form.order_billing_address.editing">
                                    <template v-if="auth.user_group.permissions_by_name.orders[0].can_edit">
                                        <font-awesome-icon
                                            class="cursor-pointer hover:text-primary"
                                            @click="form.editing_order_billing_address.changed = true, form.order_billing_address.editing = true"
                                            :icon="['far', 'pen-to-square']"
                                        />
                                    </template>
                                </template>
                                <template v-else>
                                    <font-awesome-icon
                                        class="cursor-pointer hover:text-primary"
                                        @click="form.order_billing_address.editing = false, form.editing_order_billing_address = form.order_billing_address"
                                        :icon="['far', 'arrow-rotate-left']"
                                    />
                                </template>
                                <a
                                    class="ml-2 mr-2 hover:text-primary"
                                    :href="refundOrder(form.order_billing_address.country,form.order_billing_address.zip, form.order_billing_address.state, form.order_billing_address.city, form.order_billing_address.address, form.order_billing_address.address_2)"
                                    target="_blank"
                                    title="On map"
                                >
                                    <font-awesome-icon :icon="['fas', 'location-dot']"/>
                                </a>
                                <CustomCopyButton
                                    :text="[
                            form.order_billing_address.address,
                            form.order_billing_address.address_2,
                            form.order_billing_address.city,
                            form.order_billing_address.state,
                            form.order_billing_address.zip,
                            form.order_billing_address.country
                          ].filter(Boolean).join(', ')"
                                />
                            </p>
                            <div class="flex flex-col gap-1">
                                <template v-if="!form.order_billing_address.editing">
                                    <div>
                                        <span class="text-base ml-1"> Full name:</span>
                                        <span class="text-base text-black-2 ml-2">
                                    {{ form.order_billing_address.name }}
                                    {{ form.order_billing_address.last_name }}
                                </span>
                                    </div>
                                    <div>
                                        <span class="text-base ml-1">Email:</span>
                                        <span class="text-base text-black-2 ml-2">
                                    {{ form.order_billing_address.email }}
                                </span>
                                    </div>
                                    <div>
                                        <span class="text-base ml-1">Phone:</span>
                                        <span class="text-base text-black-2 ml-2">
                                    {{ form.order_billing_address.phone }}
                                </span>
                                    </div>
                                    <div>
                                        <span class="text-base ml-1">Company:</span>
                                        <span class="text-base text-black-2 ml-2">
                                    {{ form.order_billing_address.company }}
                                </span>
                                    </div>
                                    <div>
                                        <span class="text-base ml-1">Address line 1:</span>
                                        <span class="text-base text-black-2 ml-2">
                                    {{ form.order_billing_address.address }}
                                </span>
                                    </div>
                                    <div>
                                        <span class="text-base ml-1">Address line 2:</span>
                                        <span class="text-base text-black-2 ml-2">
                                    {{ form.order_billing_address.address_2 }}
                                </span>
                                    </div>
                                    <div>
                                        <span class="text-base ml-1">City:</span>
                                        <span class="text-base text-black-2 ml-2">
                                    {{ form.order_billing_address.city }}
                                </span>
                                    </div>
                                    <div>
                                        <span class="text-base ml-1">State:</span>
                                        <span class="text-base text-black-2 ml-2">
                                    {{ form.order_billing_address.state }}
                                </span>
                                    </div>
                                    <div>
                                        <span class="text-base ml-1">ZIP:</span>
                                        <span class="text-base text-black-2 ml-2">
                                    {{ form.order_billing_address.zip }}
                                </span>
                                    </div>
                                    <div>
                                        <span class="text-base ml-1">Country:</span>
                                        <span class="text-base text-black-2 ml-2">
                                    {{ form.order_billing_address.country }}
                                </span>
                                    </div>
                                    <div>
                                        <span class="text-base ml-1">VAT_ID:</span>
                                        <span class="text-base text-black-2 ml-2">
                                    {{ form.order_billing_address.vat_number }}
                                </span>
                                    </div>
                                    <template
                                        v-if="form.order_billing_address.country_code === 'PL' || form.order_billing_address.country_code === 'ES'">
                                        <div>
                                            <span class="text-base ml-1">Extra VAT_ID:</span>
                                            <span class="text-base text-black-2 ml-2">
                             {{ form.order_billing_address.extra_vat_number }}
                         </span>
                                        </div>
                                    </template>
                                    <div>

                                        <span class="text-base ml-1">Note:</span>
                                        <span class="text-base text-black-2 ml-2">
                          {{ form.note }}
                      </span>
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="grid grid-cols-1 gap-9">
                                        <div class="flex flex-col gap-2">

                                            <CustomInput
                                                @input="delete form.errors.general, emits('empty-tabs-with-errors', 'general')"
                                                :table-input="true"
                                                v-model="form.editing_order_billing_address.name"
                                                name="name"
                                                label="First name *"
                                                type="text"
                                                placeholder="Enter first name"
                                                :error="form.errors.general !== undefined && form.errors.general['editing_order_billing_address.name'] ? form.errors.general['editing_order_billing_address.name'] : null"
                                            />

                                            <CustomInput
                                                @input="delete form.errors.general, emits('empty-tabs-with-errors', 'general')"
                                                :table-input="true"
                                                v-model="form.editing_order_billing_address.last_name"
                                                name="last_name"
                                                label="Last name *"
                                                type="text"
                                                placeholder="Enter last name"
                                                :error="form.errors.general !== undefined && form.errors.general['editing_order_billing_address.last_name'] ? form.errors.general['editing_order_billing_address.last_name'] : null"
                                            />

                                            <CustomInput
                                                @input="delete form.errors.general, emits('empty-tabs-with-errors', 'general')"
                                                :table-input="true"
                                                v-model="form.editing_order_billing_address.email"
                                                name="email"
                                                label="E-mail *"
                                                type="text"
                                                placeholder="Enter email"
                                                :error="form.errors.general !== undefined && form.errors.general['editing_order_billing_address.email'] ? form.errors.general['editing_order_billing_address.email'] : null"
                                            />
                                            <CustomInput
                                                @input="delete form.errors.general, emits('empty-tabs-with-errors', 'general')"
                                                :error="form.errors.general !== undefined && form.errors.general['editing_order_billing_address.phone'] ? form.errors.general['editing_order_billing_address.phone'] : null"
                                                :table-input="true"
                                                v-model="form.editing_order_billing_address.phone"
                                                name="phone"
                                                label="Phone *"
                                                type="text"
                                                placeholder="Enter phone"
                                            />
                                            <CustomInput
                                                @input="delete form.errors.general, emits('empty-tabs-with-errors', 'general')"
                                                :error="form.errors.general !== undefined && form.errors.general['editing_order_billing_address.company'] ? form.errors.general['editing_order_billing_address.company'] : null"
                                                :table-input="true"
                                                v-model="form.editing_order_billing_address.company"
                                                name="company"
                                                label="Company name"
                                                type="text"
                                                placeholder="Enter company name"
                                            />
                                            <CustomSelect
                                                v-model="form.editing_order_billing_address.country_code"
                                                mode="single"
                                                label="Country"
                                                :options="params.countries"
                                                :searchable="false"
                                                :canClear="false"
                                                @change="(val) => {
                                  form.editing_order_billing_address.country = params.countries.find(item => item.code === val).label;
                              }"
                                            />

                                            <CustomSelect
                                                v-if="typeof params.states[form.editing_order_billing_address.country_code] === 'object' && !Array.isArray(params.states[form.editing_order_billing_address.country_code])"
                                                v-model="form.editing_order_billing_address.state"
                                                mode="single"
                                                label="State *"
                                                :options="params.states[form.editing_order_billing_address.country_code]"
                                                :searchable="false"
                                                :canClear="false"
                                                @change="delete form.errors.general, emits('empty-tabs-with-errors', 'general')"
                                                :error="form.errors.general !== undefined && form.errors.general['editing_order_billing_address.state'] ? form.errors.general['editing_order_billing_address.state'] : null"
                                            />
                                            <CustomInput
                                                v-else
                                                :table-input="true"
                                                @input="delete form.errors.general, emits('empty-tabs-with-errors', 'general')"
                                                :error="form.errors.general !== undefined && form.errors.general['editing_order_billing_address.state'] ? form.errors.general['editing_order_billing_address.state'] : null"
                                                v-model="form.editing_order_billing_address.state"
                                                name="state"
                                                label="State *"
                                                type="text"
                                                placeholder="Enter state"
                                            />

                                            <CustomInput
                                                :table-input="true"
                                                @input="delete form.errors.general, emits('empty-tabs-with-errors', 'general')"
                                                :error="form.errors.general !== undefined && form.errors.general['editing_order_billing_address.city'] ? form.errors.general['editing_order_billing_address.city'] : null"
                                                v-model="form.editing_order_billing_address.city"
                                                name="city"
                                                label="City *"
                                                type="text"
                                                placeholder="Enter city"
                                            />

                                            <CustomInput
                                                :table-input="true"
                                                v-model="form.editing_order_billing_address.address"
                                                :error="form.errors.general !== undefined && form.errors.general['editing_order_billing_address.address'] ? form.errors.general['editing_order_billing_address.address'] : null"
                                                name="address"
                                                label="Address line 1 *"
                                                type="text"
                                                placeholder="Enter address line 1"
                                            />

                                            <CustomInput
                                                :table-input="true"
                                                @input="delete form.errors.general, emits('empty-tabs-with-errors', 'general')"
                                                :error="form.errors.general !== undefined && form.errors.general['editing_order_billing_address.zip'] ? form.errors.general['editing_order_billing_address.zip'] : null"
                                                v-model="form.editing_order_billing_address.zip"
                                                name="zip"
                                                label="Zip *"
                                                type="text"
                                                placeholder="Enter ZIP"
                                            />

                                            <CustomInput
                                                :table-input="true"
                                                @input="delete form.errors.general, emits('empty-tabs-with-errors', 'general')"
                                                :error="form.errors.general !== undefined && form.errors.general['editing_order_billing_address.address_2'] ? form.errors.general['editing_order_billing_address.address_2'] : null"
                                                v-model="form.editing_order_billing_address.address_2"
                                                name="address_2"
                                                label="Address line 2"
                                                type="text"
                                                placeholder="Enter address line 2"
                                            />

                                            <CustomTextarea
                                                label="Note"
                                                name="note"
                                                :rows="6"
                                                v-model="form.note"
                                                placeholder="Type note ..."
                                                @input="delete form.errors.general, emits('empty-tabs-with-errors', 'general')"
                                                :error="form.errors.general !== undefined && form.errors.general['editing_order_billing_address.note'] ? form.errors.general['editing_order_billing_address.note'] : null"
                                            />
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <p class="font-bold text-black-2 mb-3">
                                Shipping address
                                <template v-if="!form.order_shipping_address.editing">
                                    <template v-if="auth.user_group.permissions_by_name.orders[0].can_edit">
                                        <font-awesome-icon
                                            class="cursor-pointer hover:text-primary"
                                            @click="form.editing_order_shipping_address.changed = true, form.order_shipping_address.editing = true"
                                            :icon="['far', 'pen-to-square']"
                                        />
                                    </template>
                                </template>
                                <template v-else>
                                    <font-awesome-icon
                                        class="cursor-pointer hover:text-primary"
                                        @click="form.order_shipping_address.editing = false, form.editing_order_shipping_address = form.order_shipping_address"
                                        :icon="['far', 'arrow-rotate-left']"
                                    />
                                </template>
                                <a
                                    class="ml-2 mr-2 hover:text-primary"
                                    :href="refundOrder(form.order_shipping_address.country, form.order_shipping_address.zip, form.order_shipping_address.state, form.order_shipping_address.city, form.order_shipping_address.address, form.order_shipping_address.address_2)"
                                    target="_blank"
                                    title="On map"
                                >
                                    <font-awesome-icon :icon="['fas', 'location-dot']"/>
                                </a>
                                <CustomCopyButton
                                    :text="[
                            form.order_shipping_address.address,
                            form.order_shipping_address.address_2,
                            form.order_shipping_address.city,
                            form.order_shipping_address.state,
                            form.order_shipping_address.zip,
                            form.order_shipping_address.country
                          ].filter(Boolean).join(', ')"
                                />
                            </p>
                            <div class="flex flex-col gap-1">
                                <template v-if="!form.order_shipping_address.editing">
                                    <div>
                                        <span class="text-base ml-1">Full name:</span>
                                        <span class="text-base text-black-2 ml-2">
                                    {{ form.order_shipping_address.name }}
                                    {{ form.order_shipping_address.last_name }}
                                </span>
                                    </div>
                                    <div>
                                        <span class="text-base ml-1">Company:</span>
                                        <span class="text-base text-black-2 ml-2">
                        {{ form.order_shipping_address.company }}
                    </span>
                                    </div>
                                    <div>
                                        <span class="text-base ml-1">Address line 1:</span>
                                        <span class="text-base text-black-2 ml-2">
                                    {{ form.order_shipping_address.address }}
                                </span>
                                    </div>
                                    <div>
                                        <span class="text-base ml-1">Address line 2:</span>
                                        <span class="text-base text-black-2 ml-2">
                                    {{ form.order_shipping_address.address_2 }}
                                </span>
                                    </div>
                                    <div>
                                        <span class="text-base ml-1">City:</span>
                                        <span class="text-base text-black-2 ml-2">
                                    {{ form.order_shipping_address.city }}
                                </span>
                                    </div>
                                    <div>
                                        <span class="text-base ml-1">State:</span>
                                        <span class="text-base text-black-2 ml-2">
                                    {{ form.order_shipping_address.state }}
                                </span>
                                    </div>
                                    <div>
                                        <span class="text-base ml-1">ZIP:</span>
                                        <span class="text-base text-black-2 ml-2">
                                    {{ form.order_shipping_address.zip }}
                                </span>
                                    </div>
                                    <div>
                                        <span class="text-base ml-1">Country:</span>
                                        <span class="text-base text-black-2 ml-2">
                                    {{ form.order_shipping_address.country }}
                                </span>
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="grid grid-cols-1 gap-9">
                                        <div class="flex flex-col gap-2">
                                            <CustomInput
                                                :table-input="true"
                                                @input="delete form.errors.general, emits('empty-tabs-with-errors', 'general')"
                                                :error="form.errors.general !== undefined && form.errors.general['editing_order_shipping_address.name'] ? form.errors.general['editing_order_shipping_address.name'] : null"
                                                v-model="form.editing_order_shipping_address.name"
                                                name="name"
                                                label="First name *"
                                                type="text"
                                                placeholder="Enter first name"
                                            />
                                            <CustomInput
                                                :table-input="true"
                                                @input="delete form.errors.general, emits('empty-tabs-with-errors', 'general')"
                                                :error="form.errors.general !== undefined && form.errors.general['editing_order_shipping_address.last_name'] ? form.errors.general['editing_order_shipping_address.last_name'] : null"
                                                v-model="form.editing_order_shipping_address.last_name"
                                                name="last_name"
                                                label="Last name *"
                                                type="text"
                                                placeholder="Enter last name"
                                            />


                                            <CustomInput
                                                :table-input="true"
                                                v-model="form.editing_order_shipping_address.company"
                                                :error="form.errors.general !== undefined && form.errors.general['editing_order_shipping_address.company'] ? form.errors.general['editing_order_shipping_address.company'] : null"
                                                name="company"
                                                label="Company name"
                                                type="text"
                                                placeholder="Enter company name"
                                            />
                                            <CustomSelect
                                                :disabled="true"
                                                v-model="form.editing_order_shipping_address.country_code"
                                                mode="single"
                                                label="Country"
                                                :options="params.countries"
                                                :searchable="false"
                                                :canClear="false"
                                                @change="(val) => {
                                  form.editing_order_shipping_address.country = params.countries.find(item => item.code === val).label;
                              }"
                                            />
                                            <CustomSelect
                                                v-if="typeof params.states[form.editing_order_shipping_address.country_code] === 'object' && !Array.isArray(params.states[form.editing_order_shipping_address.country_code])"
                                                v-model="form.editing_order_shipping_address.state"
                                                mode="single"
                                                label="State *"
                                                :options="params.states[form.editing_order_billing_address.country_code]"
                                                :searchable="false"
                                                :canClear="false"
                                                @change="delete form.errors.general, emits('empty-tabs-with-errors', 'general')"
                                                :error="form.errors.general !== undefined && form.errors.general['editing_order_shipping_address.state'] ? form.errors.general['editing_order_shipping_address.state'] : null"
                                            />
                                            <CustomInput
                                                v-else
                                                :table-input="true"
                                                @input="delete form.errors.general, emits('empty-tabs-with-errors', 'general')"
                                                :error="form.errors.general !== undefined && form.errors.general['editing_order_shipping_address.state'] ? form.errors.general['editing_order_shipping_address.state'] : null"
                                                v-model="form.editing_order_shipping_address.state"
                                                name="state"
                                                label="State *"
                                                type="text"
                                                placeholder="Enter state"
                                            />

                                            <CustomInput
                                                :table-input="true"
                                                @input="delete form.errors.general, emits('empty-tabs-with-errors', 'general')"
                                                :error="form.errors.general !== undefined && form.errors.general['editing_order_shipping_address.city'] ? form.errors.general['editing_order_shipping_address.city'] : null"
                                                v-model="form.editing_order_shipping_address.city"
                                                name="city"
                                                label="City *"
                                                type="text"
                                                placeholder="Enter city"
                                            />

                                            <CustomInput
                                                :table-input="true"
                                                v-model="form.editing_order_shipping_address.address"
                                                :error="form.errors.general !== undefined && form.errors.general['editing_order_shipping_address.address'] ? form.errors.general['editing_order_shipping_address.address'] : null"
                                                name="address"
                                                label="Address line 1 *"
                                                type="text"
                                                placeholder="Enter address line 1"
                                            />

                                            <CustomInput
                                                :table-input="true"
                                                @input="delete form.errors.general, emits('empty-tabs-with-errors', 'general')"
                                                :error="form.errors.general !== undefined && form.errors.general['editing_order_shipping_address.zip'] ? form.errors.general['editing_order_shipping_address.zip'] : null"
                                                v-model="form.editing_order_shipping_address.zip"
                                                name="zip"
                                                label="Zip *"
                                                type="text"
                                                placeholder="Enter ZIP"
                                            />

                                            <CustomInput
                                                :table-input="true"
                                                @input="delete form.errors.general, emits('empty-tabs-with-errors', 'general')"
                                                :error="form.errors.general !== undefined && form.errors.general['editing_order_shipping_address.address_2'] ? form.errors.general['editing_order_shipping_address.address_2'] : null"
                                                v-model="form.editing_order_shipping_address.address_2"
                                                name="address_2"
                                                label="Address line 2"
                                                type="text"
                                                placeholder="Enter address line 2"
                                            />

                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</template>

<style lang="scss">

    .customer-details-holder label {
        margin-bottom: 2px;
    }
</style>
