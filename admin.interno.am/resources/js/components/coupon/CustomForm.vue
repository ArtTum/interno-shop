<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";

import {computed, ref, toRefs, watch} from "vue";
import {useStore} from "vuex";
import CustomSelect from "@components/global/CustomSelect.vue";
import Switch from "@components/global/Switch.vue";
import CustomTextarea from "@components/global/CustomTextarea.vue";
import {debounce} from "lodash";
import TooltipOne from "@components/global/Tooltips/TooltipOne.vue";
import CustomDatePicker from "@components/global/CustomDatePicker.vue";
import {validate} from "@validation/customValidation.js";

const store = useStore()
const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    emitAction: {
        type: String
    }
});

const {modelValue} = toRefs(props);
const form = ref(modelValue.value);
const productsOptions = ref([]);
const excludedProductsOptions = ref([]);

watch(modelValue, (newVal) => {
    form.value = newVal;
}, {immediate: true});

watch(form.value, (newVal) => {
    emits('update:modelValue', newVal);
});

watch(
    () => form.value.type,
    (newType) => {
        if (newType === 1) {
            form.value.amountRules = ['required', 'validDecimal', 'maxValue:100'];
        } else if (newType === 0) {
            form.value.amountRules = ['required', 'validDecimal'];
        }
    }, {immediate: true}
);

store.dispatch('coupon/fetchParams');
const params = computed(() => store.getters['coupon/getParams']);

const emits = defineEmits([
    'update:modelValue',
    'submit'
])

const productsAutocomplete = debounce(async (term) => {
    productsOptions.value = await store.dispatch('product/autocomplete', {
        field: 'name',
        term,
        alreadySelectIds: form.value.product_ids,
    });
}, 200);

const excludedProductsAutocomplete = debounce(async (term) => {
    excludedProductsOptions.value = await store.dispatch('product/autocomplete', {
        field: 'name',
        term,
        alreadySelectIds: form.value.excluded_product_ids,
    });
}, 200);

const autocompleteRequest = async (optionVariables, alreadySelectedIds, term = '') => {
    let options = await store.dispatch('product/autocomplete', {
        field: 'name',
        term,
        alreadySelectIds: alreadySelectedIds,
    });
    for (let i = 0; i < optionVariables.length; i++) {
        let variableName = optionVariables[i];
        if (variableName === 'productsOptions') {
            productsOptions.value = options;
        } else if (variableName === 'excludedProductsOptions') {
            excludedProductsOptions.value = options;
        }
    }
}
if (props.emitAction === 'update') {
    autocompleteRequest(['productsOptions'], form.value.product_ids);
    autocompleteRequest(['excludedProductsOptions'], form.value.excluded_product_ids);
} else {
    autocompleteRequest(['productsOptions', 'excludedProductsOptions'], []);
}

const generatePromoCode = (length = 8) => {
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let promoCode = '';

    for (let i = 0; i < length; i++) {
        const randomIndex = Math.floor(Math.random() * characters.length);
        promoCode += characters[randomIndex];
    }

    form.value.code = promoCode;
}

const activeTab = ref('general');
const tabsWithErrors = ref([]);
const justSubmitted = ref(false);

const tabsRoutes = [
    {key: 'general', title: 'General *', icon: ['far', 'gear']},
    {key: 'usage_restriction', title: 'Usage restriction', icon: ['far', 'ban']},
    {key: 'usage_limit', title: 'Usage limits', icon: ['far', 'hand']},
];

watch(
    () => form.value.errors,
    (newVal) => {
        tabsWithErrors.value = [];
        if (
            Object.hasOwn(newVal, 'code') || Object.hasOwn(newVal, 'type') || Object.hasOwn(newVal, 'amount') ||
            Object.hasOwn(newVal, 'expires_at') || Object.hasOwn(newVal, 'free_shipping') || Object.hasOwn(newVal, 'status') ||
            Object.hasOwn(newVal, 'description')

        ) {
            if (justSubmitted.value) {
                activeTab.value = 'general';
                justSubmitted.value = false;
            }
            tabsWithErrors.value.push('general');
        }
        if (
            Object.hasOwn(newVal, 'excluded_product_ids') || Object.hasOwn(newVal, 'category_ids') ||
            Object.hasOwn(newVal, 'product_ids') || Object.hasOwn(newVal, 'excluded_category_ids') ||
            Object.hasOwn(newVal, 'max_spend') || Object.hasOwn(newVal, 'min_spend') ||
            Object.hasOwn(newVal, 'exclude_sale_items') || Object.hasOwn(newVal, 'allowed_emails')
        ) {
            if (justSubmitted.value) {
                activeTab.value = 'usage_restriction';
                justSubmitted.value = false;
            }
            tabsWithErrors.value.push('usage_restriction');
        }
        if (
            Object.hasOwn(newVal, 'usage_limit') || Object.hasOwn(newVal, 'usage_limit_per_user')
        ) {
            if (justSubmitted.value) {
                activeTab.value = 'usage_limit';
                justSubmitted.value = false;
            }
            tabsWithErrors.value.push('usage_limit');
        }
    },
    {deep: true}
);

const auth = computed(() => store.getters['auth/getUser']);

</script>

<template>
    <form @submit.prevent="emits('submit'), justSubmitted = true">
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 gap-9 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>
        <div class="flex gap-9" v-if="emitAction === 'update'">
            <div class="flex pt-6.5 px-6.5">
                <span
                    class="inline-flex rounded-full bg-opacity-10 py-2 px-5 text-sm font-medium"
                    :class="{'bg-danger text-danger': !form.orders_count,
                             'bg-success text-success': form.orders_count}"
                >
                Used count: {{ form.orders_count }}
            </span>
            </div>
            <div class="flex pt-6.5 px-6.5" v-if="form.gift_card_details">
                <span
                    class="inline-flex rounded-full bg-opacity-10 py-2 px-5 font-bold text-black"
                >
                   Initial amount: {{ form.gift_card_details.amount }}
                </span>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-9">
            <div class="w-full p-7.5">
                <div class="mb-6 flex flex-wrap gap-5 border-b border-stroke">
                    <template
                        :key="key"
                        v-for="(tabRoute, key) in tabsRoutes"
                    >
                        <router-link
                            to=""
                            @click="activeTab = tabRoute.key"
                            :class="{
                                    'text-danger border-danger': tabsWithErrors.includes(tabRoute.key),
                                    'text-primary border-primary': activeTab === tabRoute.key && !tabsWithErrors.includes(tabRoute.key),
                                    'border-transparent': activeTab !== tabRoute.key
                                }"
                            class="border-b-2 py-4 text-sm font-medium hover:text-primary md:text-base px-2"
                        >
                            <font-awesome-icon :icon="tabRoute.icon"/>
                            {{ tabRoute.title }}
                        </router-link>
                    </template>
                </div>
                <div v-if="activeTab === 'general'">
                    <div class="grid grid-cols-3 gap-9">
                        <div class="p-6.5 col-span-2">
                            <div class="pr-8 flex">
                                <CustomInput
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.coupons[0].can_edit"
                                    class="w-full mt-1 custom-tooltip"
                                    v-model="form.code"
                                    name="code"
                                    label="Code *"
                                    type="text"
                                    placeholder="Enter code"
                                    @keyup="form.errors = validate(form)"
                                    :error="form.errors['code']"
                                    :tooltip="true"
                                />
                                <template
                                    v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.coupons[0].can_edit">
                                    <CustomButton
                                        type="button"
                                        @click="generatePromoCode(8)"
                                        class="flex h-fit items-center gap-2 rounded-tr rounded-br bg-primary mt-auto mb-auto py-4.5 px-4.5 font-medium text-white hover:bg-opacity-80"
                                    >
                                        <font-awesome-icon :icon="['far', 'wind-turbine']"/>
                                        Generate
                                    </CustomButton>
                                </template>
                            </div>
                        </div>
                        <div class="flex p-6.5 flex-col col-span-1">
                            <CustomDatePicker
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.coupons[0].can_edit"
                                placeholder="yyyy/mm/dd"
                                label="Expires date"
                                format="Y-m-d"
                                v-model="form.expires_at"
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-9 gap-9">
                        <div class="flex flex-col p-6.5 col-span-3">
                            <CustomSelect
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.coupons[0].can_edit"
                                label="Discount type *"
                                placeholder="Choose discount type"
                                v-model="form.type"
                                mode="single"
                                :options="params.couponTypes"
                                :searchable="false"
                                :canClear="false"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                                @update:modelValue="form.errors = validate(form)"
                                :error="form.errors['type']"
                            />
                        </div>
                        <div class="flex flex-col p-6.5 col-span-3">
                            <CustomInput
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.coupons[0].can_edit"
                                v-model="form.amount"
                                name="amount"
                                label="Amount *"
                                type="text"
                                placeholder="Enter amount"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['amount']"
                            />
                        </div>
                        <div class="flex p-6.5 col-span-3">
                            <Switch
                                @change="(value) => {
                                    form.free_shipping = value;
                                }"
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.coupons[0].can_edit"
                                :value="form.free_shipping"
                                id="free_shipping"
                                label="Free shipping"
                            />
                            <Switch
                                @change="(value) => {
                                    form.status = value;
                                }"
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.coupons[0].can_edit"
                                class="ml-15"
                                :value="form.status"
                                id="status"
                                label="Active"
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-9">
                        <div class="flex flex-col p-6.5">
                            <CustomTextarea
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.coupons[0].can_edit"
                                label="Description"
                                name="description"
                                :rows="5"
                                v-model="form.description"
                                placeholder="Description"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['description']"
                            />
                        </div>
                    </div>
                </div>
                <div v-else-if="activeTab === 'usage_restriction'">
                    <div class="grid grid-cols-9 gap-9">
                        <div class="flex flex-col p-6.5 col-span-3">
                            <CustomInput
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.coupons[0].can_edit"
                                v-model="form.min_spend"
                                name="min_spend"
                                label="Min spend"
                                type="text"
                                placeholder="No minimum"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['min_spend']"
                            />
                        </div>
                        <div class="flex flex-col p-6.5 col-span-3">
                            <CustomInput
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.coupons[0].can_edit"
                                v-model="form.max_spend"
                                name="max_spend"
                                label="Max spend"
                                type="text"
                                placeholder="No maximum"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['max_spend']"
                            />
                        </div>
                        <div class="flex p-6.5 col-span-3">
                            <Switch
                                @change="(value) => {
                                    form.exclude_sale_items = value;
                                }"
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.coupons[0].can_edit"
                                :value="form.exclude_sale_items"
                                id="exclude_sale_items"
                                label="Exclude sale items"
                            />
                        </div>
                    </div>
                    `<hr class="text-gray my-4">`
                    <div class="grid grid-cols-2 gap-9">
                        <div class="flex flex-col p-6.5">
                            <CustomSelect
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.coupons[0].can_edit"
                                @search-change="productsAutocomplete"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                                v-model="form.product_ids"
                                :searchable="true"
                                mode="tags"
                                label="Products"
                                placeholder="No selected products"
                                :options="productsOptions"
                                :show-labels="true"
                                :close-on-select="false"
                                :canClear="false"
                                :need-autocomplete="true"
                                @update:modelValue="form.errors = validate(form)"
                                :error="form.errors['product_ids']"
                            />
                        </div>
                        <div class="flex flex-col p-6.5">
                            <CustomSelect
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.coupons[0].can_edit"
                                @search-change="excludedProductsAutocomplete"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                                v-model="form.excluded_product_ids"
                                :searchable="true"
                                mode="tags"
                                label=" Excluded products"
                                placeholder="No excluded products"
                                :options="excludedProductsOptions"
                                :show-labels="true"
                                :close-on-select="false"
                                :canClear="false"
                                :need-autocomplete="true"
                                @update:modelValue="form.errors = validate(form)"
                                :error="form.errors['excluded_product_ids']"
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-9">
                        <div class="flex flex-col p-6.5">
                            <CustomSelect
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.coupons[0].can_edit"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                                v-model="form.category_ids"
                                :searchable="true"
                                mode="tags"
                                label="Categories"
                                placeholder="No selected categories"
                                :options="params.structuredCategories"
                                :show-labels="true"
                                :close-on-select="false"
                                :canClear="false"
                                @update:modelValue="form.errors = validate(form)"
                                :error="form.errors['category_ids']"
                            />
                        </div>
                        <div class="flex flex-col p-6.5">
                            <CustomSelect
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.coupons[0].can_edit"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                                v-model="form.excluded_category_ids"
                                :searchable="true"
                                mode="tags"
                                label="Excluded categories"
                                placeholder="No excluded categories"
                                :options="params.structuredCategories"
                                :show-labels="true"
                                :close-on-select="false"
                                :canClear="false"
                                @update:modelValue="form.errors = validate(form)"
                                :error="form.errors['excluded_category_ids']"
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-9">
                        <div class="flex p-6.5 w-full">
                            <div class="w-full">
                                <CustomInput
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.coupons[0].can_edit"
                                    class="custom-tooltip mt-1"
                                    :tooltip="true"
                                    v-model="form.allowed_emails"
                                    name="allowed_emails"
                                    label="Allowed emails"
                                    type="text"
                                    placeholder="Example: test@epodex.com|manager@epodex.com|developer@epodex.com"
                                    @keyup="form.errors = validate(form)"
                                    :error="form.errors['allowed_emails']"
                                />
                            </div>
                            <div class="mt-auto mb-auto">
                                <TooltipOne
                                    tooltipClass="rounded-tr rounded-br bg-primary py-5.5 px-3.5"
                                    :button-params="{showingType: 'info'}"
                                    :tooltip-text="'You must separate email by |<br>For Example: test@epodex.com|manager@epodex.com|developer@epodex.com'"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else-if="activeTab === 'usage_limit'">
                    <div class="grid grid-cols-2 gap-9">
                        <div class="flex flex-col p-6.5">
                            <CustomInput
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.coupons[0].can_edit"
                                v-model="form.usage_limit"
                                name="usage_limit"
                                label="Usage limit per coupon"
                                type="number"
                                placeholder="Unlimited usage"
                            />
                        </div>
                        <div class="flex flex-col p-6.5">
                            <CustomInput
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.coupons[0].can_edit"
                                v-model="form.usage_limit_per_user"
                                name="usage_limit_per_user"
                                label="Usage limit per user"
                                type="number"
                                placeholder="Unlimited usage"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.coupons[0].can_delete">
                        <CustomButton
                            v-if="emitAction === 'update'"
                            @click="store.commit('coupon/SET_DELETE_MODAL_VALUE', {
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

                    <template v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.coupons[0].can_edit">
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

    </form>
</template>

<style scoped>

</style>
