<script setup>

import CustomButton from "@components/global/CustomButton.vue";
import CustomDatePicker from "@components/global/CustomDatePicker.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import {computed, reactive, ref, watch} from "vue";
import {debounce} from "lodash";

const props = defineProps({
    params: {
        type: Object
    },
    countries: {
        type: Array
    },
    productAttributes: {
        type: Array
    },
    categories: {
        type: Array
    },
    coupons: {
        type: Array
    },
});

import {useStore} from "vuex";

const store = useStore();
const emits = defineEmits(['do-page-fetching', 'reset-filters']);
const productsOptions = ref([]);
const productsAutocomplete = debounce(async (term) => {
    productsOptions.value = await store.dispatch('product/autocomplete', {
        field: 'name',
        term,
        alreadySelectIds: [props.params.product_id],
    });
}, 200);

const autocompleteRequest = async (alreadySelectedId, term = '') => {
    productsOptions.value = await store.dispatch('product/autocomplete', {
        field: 'name',
        term,
        alreadySelectIds: [alreadySelectedId],
    });
}


const itemsOptions = ref([]);
const itemsAutocomplete = debounce(async (term) => {
    itemsOptions.value = await store.dispatch('item/autocomplete', {
        field: 'name',
        term,
        alreadySelectIds: [props.params.item_id],
    });
}, 200);

const autocompleteRequestForItems = async (alreadySelectedId, term = '') => {
    itemsOptions.value = await store.dispatch('item/autocomplete', {
        field: 'name',
        term,
        alreadySelectIds: [alreadySelectedId],
    });
}

if (props.params.tab === 'products') {
    autocompleteRequest(props.params.product_id);
}

if (props.params.tab === 'items' || props.params.tab === 'products' || props.params.tab === 'categories') {
    autocompleteRequestForItems(props.params.item_id);
}

const selectedAttributes = reactive({});

const firstLoad = ref(true);

watch(() => props.productAttributes, (val) => {
    if (firstLoad.value && props.productAttributes) {
        let checkedAndSelectedAttributes = 0;
        props.productAttributes.forEach(attribute => {
            let selectedAttributeId = null;
            if (props.params.product_attribute_ids && props.params.product_attribute_ids.length) {
                for (let i = 0; i < attribute.attributes.length; i++) {
                    if (props.params.product_attribute_ids.includes(attribute.attributes[i].value.toString())) {
                        selectedAttributeId = attribute.attributes[i].value;
                        checkedAndSelectedAttributes++;
                        break;
                    }
                }
            }
            if (selectedAttributeId) {
                selectedAttributes[attribute.id] = [selectedAttributeId];
            } else {
                selectedAttributes[attribute.id] = [];
            }
        });
        firstLoad.value = false;

    }
}, {immediate: true});

const selectedIds = computed(() => {
    return Object.values(selectedAttributes).flat();
});

watch(() => selectedIds.value, (val) => {
    props.params.product_attribute_ids = val
});

</script>

<template>
    <div>
        <div class="gap-4 mb-4 rounded-sm border border-stroke bg-white p-5 shadow-default">
            <div class="flex justify-between">
                <div>
                    <CustomButton
                        class="min-w-[120px] rounded border border-stroke bg-gray px-3 py-2 text-center font-medium text-black hover:bg-opacity-60"
                        :class="{'bg-meta-3 text-white': params.tab === 'overview'}"
                        @click="params.tab = 'overview', emits('do-page-fetching')"
                        type="button"
                    >
                        Overview
                    </CustomButton>
                </div>
                <div>
                    <CustomButton
                        class="min-w-[120px] rounded border border-stroke bg-gray px-3 py-2 text-center font-medium text-black hover:bg-opacity-60"
                        :class="{'bg-meta-3 text-white': params.tab === 'revenue'}"
                        @click="params.tab = 'revenue', emits('do-page-fetching')"
                        type="button"
                    >
                        Revenue
                    </CustomButton>
                </div>
                <div>
                    <CustomButton
                        class="min-w-[120px] rounded border border-stroke bg-gray px-3 py-2 text-center font-medium text-black hover:bg-opacity-60"
                        :class="{'bg-meta-3 text-white': params.tab === 'items'}"
                        @click="params.tab = 'items', emits('do-page-fetching')"
                        type="button"
                    >
                        Items
                    </CustomButton>
                </div>
                <div>
                    <CustomButton
                        class="min-w-[120px] rounded border border-stroke bg-gray px-3 py-2 text-center font-medium text-black hover:bg-opacity-60"
                        :class="{'bg-meta-3 text-white': params.tab === 'products'}"
                        @click="params.tab = 'products', emits('do-page-fetching')"
                        type="button"
                    >
                        Products
                    </CustomButton>
                </div>
                <div>
                    <CustomButton
                        class="min-w-[120px] rounded border border-stroke bg-gray px-3 py-2 text-center font-medium text-black hover:bg-opacity-60"
                        :class="{'bg-meta-3 text-white': params.tab === 'categories'}"
                        @click="params.tab = 'categories', emits('do-page-fetching')"
                        type="button"
                    >
                        Categories
                    </CustomButton>
                </div>
                <div>
                    <CustomButton
                        class="min-w-[120px] rounded border border-stroke bg-gray px-3 py-2 text-center font-medium text-black hover:bg-opacity-60"
                        :class="{'bg-meta-3 text-white': params.tab === 'coupons'}"
                        @click="params.tab = 'coupons', emits('do-page-fetching')"
                        type="button"
                    >
                        Coupons
                    </CustomButton>
                </div>
                <div>
                    <CustomButton
                        class="min-w-[120px] rounded border border-stroke bg-gray px-3 py-2 text-center font-medium text-black hover:bg-opacity-60"
                        :class="{'bg-meta-3 text-white': params.tab === 'map'}"
                        @click="params.tab = 'map', emits('do-page-fetching')"
                        type="button"
                    >
                        Map
                    </CustomButton>
                </div>
            </div>
            <div class="grid grid-cols-4 gap-9 mt-5">
                <div class="flex p-2.5 w-full col-span-2">
                    <div class="w-full">
                        <CustomDatePicker
                            placeholder="yyyy/mm/dd"
                            :tableInput="true"
                            label="Date"
                            format="Y-m-d"
                            v-model="params.order_date_from"
                        />
                    </div>
                    <div class="flex items-center justify-center px-5">
                        <div class="text-center">
                            -
                        </div>
                    </div>
                    <div class="w-full">
                        <CustomDatePicker
                            placeholder="yyyy/mm/dd"
                            label="&nbsp"
                            format="Y-m-d"
                            v-model="params.order_date_to"
                            :tableInput="true"
                        />
                    </div>
                </div>
                <div class="flex p-2.5 w-full col-span-2">
                    <div class="w-full">
                        <CustomDatePicker
                            placeholder="yyyy/mm/dd"
                            :tableInput="true"
                            label="Compare date"
                            format="Y-m-d"
                            v-model="params.compared_order_date_from"
                        />
                    </div>
                    <div class="flex items-center justify-center px-5">
                        <div class="text-center">
                            -
                        </div>
                    </div>
                    <div class="w-full">
                        <CustomDatePicker
                            placeholder="yyyy/mm/dd"
                            label="&nbsp"
                            format="Y-m-d"
                            v-model="params.compared_order_date_to"
                            :tableInput="true"
                        />
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-4 gap-9">
                <div class="flex flex-col p-2.5">
                    <CustomSelect
                        v-model="params.billing_country"
                        mode="tags"
                        label="Billing countries"
                        :options="countries"
                        :searchable="true"
                        :canClear="false"
                        :close-on-select="false"
                        placeholder="All countries"
                    />
                </div>
                <div class="flex flex-col p-2.5">
                    <CustomSelect
                        v-model="params.shipping_country"
                        mode="tags"
                        label="Shipping countries"
                        :options="countries"
                        :searchable="true"
                        :canClear="false"
                        :close-on-select="false"
                        placeholder="All countries"
                    />
                </div>
                <div class="flex flex-col p-2.5">
                    <template v-if="params.tab === 'products'">
                        <CustomSelect
                            @search-change="productsAutocomplete"
                            v-model="params.product_id"
                            :searchable="true"
                            label="Product"
                            placeholder="No selected products"
                            :options="productsOptions"
                            :can-clear="true"
                            :need-autocomplete="true"
                            :invalid-feedback-place="false"
                        />
                    </template>
                    <template v-else-if="params.tab === 'items'">
                        <CustomSelect
                            @search-change="itemsAutocomplete"
                            v-model="params.item_id"
                            :searchable="true"
                            label="Item"
                            placeholder="No selected item"
                            :options="itemsOptions"
                            :can-clear="true"
                            :need-autocomplete="true"
                            :invalid-feedback-place="false"
                        />
                    </template>
                    <template v-else-if="params.tab === 'categories'">
                        <CustomSelect
                            v-model="params.category_id"
                            :searchable="true"
                            label="Category"
                            placeholder="No selected category"
                            :options="categories"
                            :can-clear="true"
                            :need-autocomplete="true"
                            :invalid-feedback-place="false"
                        />
                    </template>
                    <template v-else-if="params.tab === 'coupons'">
                        <CustomSelect
                            v-model="params.coupon_code"
                            :searchable="true"
                            label="Coupon"
                            placeholder="No selected coupon"
                            :options="coupons"
                            :can-clear="true"
                            :need-autocomplete="true"
                            :invalid-feedback-place="false"
                        />
                    </template>
                </div>
                <div class="flex flex-col p-2.5">
                    <template v-if="params.tab === 'products'">
                        <CustomSelect
                            v-model="params.category_id"
                            :searchable="true"
                            label="Category"
                            placeholder="No selected category"
                            :options="categories"
                            :can-clear="true"
                            :need-autocomplete="true"
                            :invalid-feedback-place="false"
                        />
                    </template>
                </div>
            </div>

            <div class="grid grid-cols-4 gap-9">
                <div class="flex flex-col p-2.5">
                    <template v-if="params.tab === 'products' || params.tab === 'categories'">
                            <CustomSelect
                                @search-change="itemsAutocomplete"
                                v-model="params.item_id"
                                :searchable="true"
                                label="Item"
                                placeholder="No selected item"
                                :options="itemsOptions"
                                :can-clear="true"
                                :need-autocomplete="true"
                                :invalid-feedback-place="false"
                            />
                    </template>
                </div>
                <div class="flex p-2.5 col-start-4">
                    <div>
                        <CustomButton
                            @click="emits('reset-filters')"
                            class="flex items-center ml-auto gap-2 rounded border border-stroke bg-gray py-2 px-4.5 mt-8 font-medium  text-black hover:bg-opacity-60 max-w-[240px]"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'close']"/>
                            Reset
                        </CustomButton>
                    </div>
                    <div class="ml-3">
                        <CustomButton
                            @click="emits('do-page-fetching')"
                            class="flex items-center ml-auto gap-2 rounded bg-primary py-2 px-4.5 mt-8 font-medium text-white hover:bg-opacity-80 max-w-[240px]"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'magnifying-glass']"/>
                            Apply
                        </CustomButton>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-9">
                <template v-for="(attribute, index) in productAttributes">
                    <div class="flex flex-col p-2.5">
                        <CustomSelect
                            v-model="selectedAttributes[attribute.id]"
                            :label="attribute.attribute_type_translation.name"
                            :options="attribute.attributes"
                            :searchable="true"
                            :canClear="true"
                            :close-on-select="true"
                            :placeholder="attribute.attribute_type_translation.name"
                        />
                    </div>
                </template>
                <!--                <div class="flex flex-col p-2.5">-->
                <!--                    <CustomSelect-->
                <!--                        v-model="params.shipping_country"-->
                <!--                        mode="tags"-->
                <!--                        label="Shipping countries"-->
                <!--                        :options="countries"-->
                <!--                        :searchable="true"-->
                <!--                        :canClear="false"-->
                <!--                        :close-on-select="false"-->
                <!--                        placeholder="All countries"-->
                <!--                    />-->
                <!--                </div>-->
                <!--                <div class="flex flex-col p-2.5">-->
                <!--                    <template v-if="params.tab === 'products'">-->
                <!--                        <CustomSelect-->
                <!--                            @search-change="productsAutocomplete"-->
                <!--                            v-model="params.product_id"-->
                <!--                            :searchable="true"-->
                <!--                            label="Product"-->
                <!--                            placeholder="No selected products"-->
                <!--                            :options="productsOptions"-->
                <!--                            :can-clear="true"-->
                <!--                            :need-autocomplete="true"-->
                <!--                            :invalid-feedback-place="false"-->
                <!--                        />-->
                <!--                    </template>-->
                <!--                </div>-->
            </div>
            <!--            <div class="grid grid-cols-4 gap-9">-->
            <!--                <div class="flex flex-col p-2.5">-->
            <!--                  -->
            <!--                </div>-->
            <!--            </div>-->
        </div>
    </div>
</template>

<style scoped>

</style>
