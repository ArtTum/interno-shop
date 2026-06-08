<script setup>

import CustomButton from "@components/global/CustomButton.vue";
import CustomDatePicker from "@components/global/CustomDatePicker.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import CustomInput from "@components/global/CustomInput.vue";
import Switch from "@components/global/Switch.vue";
import {ref} from "vue";
import {debounce} from "lodash";
import {useStore} from "vuex";

const props = defineProps({
    params: {
        type: Object
    },
    countries: {
        type: Array
    },
    categories: {
        type: Array
    },
});

const emits = defineEmits(['do-page-fetching', 'reset-filters']);
const store = useStore();
const yesOrNotOptions = [
    {value: -1, label: 'All'},
    {value: 1, label: 'Yes'},
    {value: 0, label: 'No'},
];

const ordersCountValues = [
    {value: -1, label: 'All'},
    {value: 1, label: '1'},
    {value: 0, label: 'More than 1'},
];

const usersOptions = ref([]);
const usersAutocomplete = debounce(async (term) => {
    usersOptions.value = await store.dispatch('user/autocomplete', {
        field: 'name',
        term,
        alreadySelectIds: [props.params.user_id],
    });
}, 200);

const usersAutocompleteRequest = async (alreadySelectedId, term = '') => {
    usersOptions.value = await store.dispatch('user/autocomplete', {
        field: 'name',
        term,
        alreadySelectIds: [alreadySelectedId],
    });
}

if (props.params.tab === 'target_customer') {
    usersAutocompleteRequest(props.params.user_id);
}
</script>

<template>
    <div>
        <div class="gap-4 mb-4 rounded-sm border border-stroke bg-white p-5 shadow-default">
            <div class="flex justify-between">
                <div>
                    <CustomButton
                        class="min-w-[120px] rounded border border-stroke bg-gray px-3 py-2 text-center font-medium text-black hover:bg-opacity-60"
                        :class="{'bg-meta-3 text-white': params.tab === 'customers'}"
                        @click="params.tab = 'customers', emits('do-page-fetching')"
                        type="button"
                    >
                        Customers
                    </CustomButton>
                </div>
                <div>
                    <CustomButton
                        class="min-w-[120px] rounded border border-stroke bg-gray px-3 py-2 text-center font-medium text-black hover:bg-opacity-60"
                        :class="{'bg-meta-3 text-white': params.tab === 'target_customer'}"
                        @click="params.tab = 'target_customer', emits('do-page-fetching')"
                        type="button"
                    >
                        Target customer
                    </CustomButton>
                </div>
                <div>
                    <CustomButton
                        class="min-w-[120px] rounded border border-stroke bg-gray px-3 py-2 text-center font-medium text-black hover:bg-opacity-60"
                        :class="{'bg-meta-3 text-white': params.tab === 'clv'}"
                        @click="params.tab = 'clv', emits('do-page-fetching')"
                        type="button"
                    >
                        CLV
                    </CustomButton>
                </div>
            </div>
            <div class="grid grid-cols-4 gap-9 mt-5" v-if="params.tab === 'customers'">
                <div class="flex flex-col p-2.5">
                    <CustomInput
                        v-model="params.first_name"
                        name="name"
                        label="Name"
                        type="text"
                        placeholder="Enter name"
                        :table-input="true"
                        :invalid-feedback-place="false"
                    />
                </div>
                <div class="flex flex-col p-2.5">
                    <CustomInput
                        v-model="params.last_name"
                        name="last_name"
                        label="Last name"
                        type="text"
                        placeholder="Enter last name"
                        :table-input="true"
                        :invalid-feedback-place="false"
                    />
                </div>
                <div class="flex flex-col p-2.5">
                    <CustomInput
                        v-model="params.email"
                        name="email"
                        label="Email"
                        type="text"
                        placeholder="Enter email"
                        :table-input="true"
                        :invalid-feedback-place="false"
                    />
                </div>
                <div class="flex flex-col p-2.5">
                    <CustomInput
                        v-model="params.exclude_email_domain"
                        name="exclude_email_domain"
                        label="Exclude email domain"
                        type="text"
                        :table-input="true"
                        placeholder="Enter email domain"
                        :invalid-feedback-place="false"
                    />
                </div>
            </div>
            <div class="grid grid-cols-4 gap-9">
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
                <div class="flex p-2.5 w-full col-span-2" v-if="params.tab === 'target_customer'">
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
                <div class="flex p-2.5 w-full col-span-1" v-if="params.tab === 'clv' || params.tab === 'customers'">
                    <div class="w-full">
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
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-4 gap-9" v-if="params.tab !== 'clv'">
                <div class="flex p-2.5 w-full col-span-2">
                    <div class="w-full">
                        <CustomInput
                            v-model="params.spent_from"
                            name="spent_from"
                            :label="params.tab === 'customers' ? 'Total spent' : 'Cost of orders'"
                            type="text"
                            placeholder="Min"
                            :tableInput="true"
                        />
                    </div>
                    <div class="flex items-center justify-center px-5">
                        <div class="text-center">
                            -
                        </div>
                    </div>
                    <div class="w-full">
                        <CustomInput
                            v-model="params.spent_to"
                            name="spent_to"
                            label="&nbsp"
                            type="text"
                            placeholder="Max"
                            :tableInput="true"
                        />
                    </div>
                </div>
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
            </div>
            <div class="grid grid-cols-5 gap-9">
                <template v-if="params.tab === 'customers'">
                    <div class="flex flex-col p-2.5">
                        <CustomSelect
                            v-model="params.company"
                            mode="single"
                            label="Company exists"
                            :options="yesOrNotOptions"
                            :searchable="false"
                            :canClear="false"
                        />
                    </div>
                    <div class="flex flex-col p-2.5">
                        <CustomSelect
                            v-model="params.vat"
                            mode="single"
                            label="VAT exists"
                            :options="yesOrNotOptions"
                            :searchable="false"
                            :canClear="false"
                        />
                    </div>
                    <div class="flex flex-col p-2.5">
                        <CustomSelect
                            v-model="params.orders_count"
                            mode="single"
                            label="Orders count"
                            :options="ordersCountValues"
                            :searchable="false"
                            :canClear="false"
                        />
                    </div>
                    <div class="flex justify-between py-2.5">
                        <Switch
                            @change="(value) => {
                           params.with_billing_info = value;
                        }"
                            :value="params.with_billing_info"
                            id="with_billing_info"
                            label="Billing info"
                        />
                        <Switch
                            @change="(value) => {
                           params.with_shipping_info = value;
                        }"
                            :value="params.with_shipping_info"
                            id="with_shipping_info"
                            label="Shipping info"
                        />
                    </div>
                </template>
                <template v-if="params.tab === 'target_customer'">
                    <div class="flex p-2.5 col-span-2">
                        <div class="w-full">
                            <CustomSelect
                                @search-change="usersAutocomplete"
                                v-model="params.user_id"
                                :searchable="true"
                                label="User"
                                placeholder="No selected user"
                                :options="usersOptions"
                                :can-clear="true"
                                :need-autocomplete="true"
                                :invalid-feedback-place="false"
                            />
                        </div>
                    </div>
                    <div class="flex p-2.5 col-span-2">

                    </div>
                </template>
                <template v-if="params.tab === 'clv'">
                    <div class="flex p-2.5 col-span-4">
                    </div>
                </template>
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
