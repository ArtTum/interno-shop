<script setup>

import CustomButton from "@components/global/CustomButton.vue";
import CustomDatePicker from "@components/global/CustomDatePicker.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import CustomInput from "@components/global/CustomInput.vue";

const props = defineProps({
    params: {
        type: Object
    },
    countries: {
        type: Array
    },
    carrierMethods: {
        type: Object
    },
    languages: {
        type: Array
    }
});

const emits = defineEmits(['do-page-fetching', 'reset-filters']);

const discountExistsValues = [
    {value: -1, label: 'All'},
    {value: 1, label: 'Has'},
    {value: 0, label: 'Has not'},
];

const reshipmentOptions = [
    {value: -1, label: 'All'},
    {value: 3, label: 'Not reshipment'},
    {value: 2, label: 'All reshipments'},
    {value: 1, label: 'Full reshipment'},
    {value: 0, label: 'Partial reshipment'},
];

</script>

<template>
    <div>
        <div class="gap-4 mb-4 rounded-sm border border-stroke bg-white p-5 shadow-default">
            <div class="flex">
                <div>
                    <CustomButton
                        class="min-w-[120px] rounded border border-stroke bg-gray px-3 py-2 text-center font-medium text-black hover:bg-opacity-60"
                        :class="{'bg-meta-3 text-white': params.tab === 'order'}"
                        @click="params.tab = 'order', emits('do-page-fetching')"
                        type="button"
                    >
                        Order controlling
                    </CustomButton>
                </div>
                <div class="ml-3">
                    <CustomButton
                        class="min-w-[120px] rounded border border-stroke bg-gray px-3 py-2 text-center font-medium text-black hover:bg-opacity-60"
                        :class="{'bg-meta-3 text-white': params.tab === 'shop'}"
                        @click="params.tab = 'shop', emits('do-page-fetching')"
                        type="button"
                    >
                        Shop controlling
                    </CustomButton>
                </div>
            </div>
            <div class="grid grid-cols-4 gap-9 mt-5">
                <div class="flex flex-col p-2.5 w-full">
                    <CustomInput
                        v-model="params.order_id"
                        name="order_id"
                        label="Order ID"
                        type="text"
                        placeholder="Enter ID"
                        :table-input="true"
                        :invalid-feedback-place="false"
                    />
                </div>
                <div class="flex flex-col p-2.5 w-full">
                    <CustomSelect
                        v-model="params.language_id"
                        label="Language"
                        :options="languages"
                        :searchable="true"
                        :canClear="false"
                        :close-on-select="false"
                        placeholder="All"
                    />
                </div>
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
            </div>
            <div class="grid grid-cols-5 gap-9" v-if="params.tab !== 'clv'">
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
                <div class="flex flex-col p-2.5 w-full">
                    <CustomSelect
                        v-model="params.discount_exists"
                        label="Discount exists"
                        :options="discountExistsValues"
                        :canClear="false"
                        :close-on-select="false"
                        placeholder="All"
                    />
                </div>
                <div class="flex flex-col p-2.5 w-full">
                    <CustomSelect
                        v-model="params.reshipment"
                        label="Reshipment"
                        :options="reshipmentOptions"
                        :canClear="false"
                        :close-on-select="false"
                        placeholder="All"
                    />
                </div>
                <div class="flex flex-col p-2.5 w-full">
                    <CustomSelect
                        v-model="params.carrier"
                        label="Carrier"
                        :options="carrierMethods"
                        :canClear="false"
                        :close-on-select="false"
                        placeholder="All"
                    />
                </div>
            </div>
            <div class="grid grid-cols-5 gap-9">
                <div class="flex p-2.5">
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
        </div>
    </div>
</template>

<style scoped>

</style>
