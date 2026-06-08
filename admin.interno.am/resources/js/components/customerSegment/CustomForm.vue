<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

import {computed, ref, toRefs, watch} from "vue";
import {useStore} from "vuex";
import {validate} from "@validation/customValidation.js";
import TooltipOne from "@components/global/Tooltips/TooltipOne.vue";
import ClipLoader from "vue-spinner/src/ClipLoader.vue";

const store = useStore()
const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    emitAction: {
        type: String
    },
});

store.dispatch(`customerSegment/fetchParams`);
const params = computed(() => store.getters[`customerSegment/getParams`]);
const auth = computed(() => store.getters['auth/getUser']);

const {modelValue} = toRefs(props);
const form = ref(modelValue.value);

const emits = defineEmits([
    'update:modelValue',
    'submit'
]);

watch(modelValue, (newVal) => {
    form.value = newVal;
});

watch(form.value, (newVal) => {
    emits('update:modelValue', newVal);
});

const handleUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;
    form.value.file = file;
};

const activeTab = ref('demographic')
const tabsRoutes = [
    {key: 'demographic', title: 'Demographic', icon: ['fas', 'earth-americas']},
    {key: 'purchase_behavior', title: 'Purchase behavior', icon: ['fas', 'cart-shopping']},
    {key: 'interests', title: 'Interests', icon: ['fas', 'basket-shopping']},
    {key: 'customer_type', title: 'Customer type', icon: ['fas', 'user']},
    {key: 'import', title: 'Import by file', icon: ['fass', 'upload']},
    {key: 'segment-source', title: 'Segment source', icon: ['fas', 'database']},
];

const yesOrNotOptions = [
    {value: -1, label: 'All'},
    {value: 1, label: 'Yes'},
    {value: 0, label: 'No'},
];

const statusOptions = [
    {value: -1, label: 'All'},
    {value: 1, label: 'Active'},
    {value: 0, label: 'Inactive'},
];

const exportImportedCustomers = async () => {
    let filename = 'imported-customers.xlsx';

    const response = await store.dispatch('customerSegment/exportFile', {
        segment_id: form.value.id
    })

    const blob = new Blob([response.data], {type: response.headers['content-type']});
    const link = document.createElement('a');
    link.setAttribute("target", "_blank");
    link.href = window.URL.createObjectURL(blob);
    link.download = filename;
    link.click();
}
</script>

<template>
    <form @submit.prevent="emits('submit')">
        <div class="grid grid-cols-1 gap-9" v-if="emitAction === 'update'">
            <div class="flex pt-6.5 px-6.5 items-center">
                <a
                    target="_blank"
                    class="hover-trigger hover:text-primary font-medium text-black"
                    :href="'/crm/customers?only_actives=-1&segment_id=' + form.id"
                >
                       <span
                           class="inline-flex rounded-full bg-opacity-10 py-2 px-5 text-sm font-medium items-center"
                           :class="{
                              'bg-warning text-warning': form.in_progress,
                           'bg-danger text-danger': !form.customer_segment_users_count && !form.in_progress,
                             'bg-success text-success': form.customer_segment_users_count && !form.in_progress
                       }"
                       >
                           <template v-if="form.in_progress">
                                 In progress
                                <ClipLoader class="text-left ml-2" color="#3C50E0" size="20px"/>
                           </template>
                          <template v-else>
                                    Customers: {{ form.customer_segment_users_count }}
                                    <font-awesome-icon
                                        class="ml-2"
                                        :icon="['fass', 'up-right-from-square']"
                                    />
                          </template>
                        </span>
                </a>
                <span class="text-black font-bold ml-2" v-if="!form.in_progress">Will update list at: {{ form.expire_date }}</span>
            </div>
        </div>
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 gap-9 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>
        <div class="grid grid-cols-3 gap-9 mt-5">
            <div class="flex flex-col px-6.5 py-0">
                <CustomSelect
                    class="py-2 rounded-lg  border-stroke bg-transparent"
                    v-model="form.criteria.language_id"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.customers_segments[0].can_edit"
                    label="Languages"
                    placeholder="Select language"
                    :options="params.languages"
                    :close-on-select="true"
                    :canClear="true"
                />
            </div>
            <div class="flex flex-col px-6.5 py-0">
                <CustomInput
                    v-model="form.name"
                    name="name"
                    label="Name *"
                    type="text"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.customers_segments[0].can_edit"
                    placeholder="Enter first name"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['name']"
                />
            </div>
            <div class="flex flex-col px-6.5 py-0">
                <CustomInput
                    v-model="form.cache_hours"
                    name="cache_hours"
                    label="Age of cache by hours *"
                    type="number"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.customers_segments[0].can_edit"
                    placeholder="Enter age"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['cache_hours']"
                />
            </div>
        </div>
        <div class="grid grid-cols-1 gap-9">
            <div class="mb-14 w-full p-7.5">
                <div class="mb-6 flex flex-wrap gap-10 border-b border-stroke">
                    <template
                        :key="key"
                        v-for="(tabRoute, key) in tabsRoutes"
                    >
                        <router-link
                            to=""
                            @click="activeTab = tabRoute.key"
                            :class="{
                                    'text-primary border-primary': activeTab === tabRoute.key,
                                    'border-transparent': activeTab !== tabRoute.key
                                }"
                            class="border-b-2 py-4 text-sm font-medium hover:text-primary md:text-base px-2"
                        >
                            <font-awesome-icon :icon="tabRoute.icon"/>
                            {{ tabRoute.title }}
                        </router-link>
                    </template>
                </div>

                <div v-if="activeTab === 'demographic'">
                    <div class="grid grid-cols-3 gap-9 mt-5">
                        <div class="flex flex-col px-6.5 py-0">
                            <CustomSelect
                                class="py-2 rounded-lg  border-stroke bg-transparent"
                                v-model="form.criteria.country_ids"
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.customers_segments[0].can_edit"
                                @update:modelValue="form.errors = validate(form)"
                                mode="tags"
                                label="Countries"
                                placeholder="Select countries"
                                :options="params.countries"
                                :show-labels="true"
                                :close-on-select="false"
                                :canClear="false"
                            />
                        </div>
                        <div class="flex px-6.5 w-full">
                            <div class="w-full">
                                <CustomInput
                                    v-model="form.criteria.cities"
                                    name="cities"
                                    class="custom-tooltip"
                                    label="Cities"
                                    :tooltip="true"
                                    type="text"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.customers_segments[0].can_edit"
                                    placeholder="Example: City 1, city 2, city 3"
                                />
                            </div>
                            <div class="mt-auto mb-auto">
                                <TooltipOne
                                    tooltipClass="rounded-tr rounded-br bg-primary py-5.5 px-3.5"
                                    :button-params="{showingType: 'info'}"
                                    tooltip-text="Example: Single: City 1 OR Multiple: City 1, city 2, city 3"
                                />
                            </div>
                        </div>
                        <div class="flex px-6.5 w-full">
                            <div class="w-full">
                                <CustomInput
                                    v-model="form.criteria.zip"
                                    name="zip"
                                    class="custom-tooltip"
                                    label="ZIP"
                                    :tooltip="true"
                                    type="text"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.customers_segments[0].can_edit"
                                    placeholder="Example: Single։ 11101 OR Range: 11101-11115 OR Multiple: 11101;11156;55547"
                                />
                            </div>
                            <div class="mt-auto mb-auto">
                                <TooltipOne
                                    tooltipClass="rounded-tr rounded-br bg-primary py-5.5 px-3.5"
                                    :button-params="{showingType: 'info'}"
                                    tooltip-text="Example: Single։ 11101 <br> Range: 11101-11115 <br> Multiple: 11101;11156;55547"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else-if="activeTab === 'purchase_behavior'">
                    <div class="grid grid-cols-2 gap-9 mt-5">
                        <div class="flex px-6.5 w-full">
                            <div class="w-full">
                                <CustomInput
                                    v-model="form.criteria.purchase_frequency_days_from"
                                    name="purchase_frequency_days_from"
                                    label="Purchase frequency (Days range)"
                                    type="number"
                                    placeholder="Min"
                                />
                            </div>
                            <div class="flex items-center justify-center px-5">
                                <div class="text-center">
                                    -
                                </div>
                            </div>
                            <div class="w-full">
                                <CustomInput
                                    v-model="form.criteria.purchase_frequency_days_to"
                                    name="purchase_frequency_days_to"
                                    label="&nbsp"
                                    type="number"
                                    placeholder="Max"
                                />
                            </div>
                        </div>
                        <div class="flex px-6.5 w-full">
                            <div class="w-full">
                                <CustomInput
                                    v-model="form.criteria.avg_revenue_from"
                                    name="avg_revenue_from"
                                    label="Avg revenue"
                                    type="text"
                                    placeholder="Min"
                                />
                            </div>
                            <div class="flex items-center justify-center px-5">
                                <div class="text-center">
                                    -
                                </div>
                            </div>
                            <div class="w-full">
                                <CustomInput
                                    v-model="form.criteria.avg_revenue_to"
                                    name="avg_revenue_to"
                                    label="&nbsp"
                                    type="text"
                                    placeholder="Max"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-9">
                        <div class="flex px-6.5 w-full">
                            <div class="w-full">
                                <CustomInput
                                    v-model="form.criteria.order_count_range_from"
                                    name="order_count_range_from"
                                    label="Orders count (Range)"
                                    type="number"
                                    placeholder="Min"
                                />
                            </div>
                            <div class="flex items-center justify-center px-5">
                                <div class="text-center">
                                    -
                                </div>
                            </div>
                            <div class="w-full">
                                <CustomInput
                                    v-model="form.criteria.order_count_range_to"
                                    name="purchase_frequency_days_to"
                                    label="&nbsp"
                                    type="number"
                                    placeholder="Max"
                                />
                            </div>
                        </div>
                        <div class="flex px-6.5 w-full">
                            <div class="w-full">
                                <CustomInput
                                    v-model="form.criteria.last_purchase_days_from"
                                    name="last_purchase_days_from"
                                    label="Last purchase (Range of days)"
                                    type="number"
                                    placeholder="Min"
                                />
                            </div>
                            <div class="flex items-center justify-center px-5">
                                <div class="text-center">
                                    -
                                </div>
                            </div>
                            <div class="w-full">
                                <CustomInput
                                    v-model="form.criteria.last_purchase_days_to"
                                    name="last_purchase_days_to"
                                    label="&nbsp"
                                    type="number"
                                    placeholder="Max"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else-if="activeTab === 'interests'">
                    <div class="grid grid-cols-6 gap-9 mt-5">
                        <div class="flex flex-col px-6.5 py-0 col-span-1">
                            <CustomSelect
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.customers_segments[0].can_edit"
                                v-model="form.criteria.products_matching_rule"
                                mode="single"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                                label="Matching rule"
                                :options="[{value: 'all', label: 'All selected products'}, {value: 'one', label: 'At least one selected product'}]"
                                :searchable="false"
                                :canClear="false"
                            />
                        </div>
                        <div class="flex flex-col px-6.5 py-0 col-span-2">
                            <CustomSelect
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.customers_segments[0].can_edit"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                                v-model="form.criteria.product_ids"
                                mode="tags"
                                :searchable="true"
                                label="Products"
                                :excluded-value="form.id"
                                placeholder="Select products"
                                :options="params.products"
                                :show-labels="true"
                                :close-on-select="false"
                                :canClear="false"
                            />
                        </div>
                        <div class="flex flex-col px-6.5 py-0 col-span-1">
                            <CustomSelect
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.customers_segments[0].can_edit"
                                v-model="form.criteria.categories_matching_rule"
                                mode="single"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                                label="Matching rule"
                                :options="[{value: 'all', label: 'All selected categories'}, {value: 'one', label: 'At least one selected category'}]"
                                :searchable="false"
                                :canClear="false"
                            />
                        </div>
                        <div class="flex flex-col px-6.5 py-0 col-span-2">
                            <CustomSelect
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.customers_segments[0].can_edit"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                                v-model="form.criteria.category_ids"
                                mode="tags"
                                :searchable="true"
                                label="Categories"
                                :excluded-value="form.id"
                                placeholder="Select categories"
                                :options="params.categories"
                                :show-labels="true"
                                :close-on-select="false"
                                :canClear="false"
                            />
                        </div>
                    </div>
                </div>
                <div v-else-if="activeTab === 'customer_type'">
                    <div class="grid grid-cols-4 gap-9 mt-5">
                        <div class="flex flex-col px-6.5 py-0">
                            <CustomSelect
                                v-model="form.criteria.vat_exists"
                                mode="single"
                                label="VAT exists"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                                :options="yesOrNotOptions"
                                :searchable="false"
                                :canClear="false"
                            />
                        </div>
                        <div class="flex flex-col px-6.5 py-0">
                            <CustomSelect
                                v-model="form.criteria.company_exists"
                                mode="single"
                                label="Company exists"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                                :options="yesOrNotOptions"
                                :searchable="false"
                                :canClear="false"
                            />
                        </div>
                        <div class="flex flex-col px-6.5 py-0">
                            <CustomSelect
                                v-model="form.criteria.only_actives"
                                mode="single"
                                label="Status"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                                :options="statusOptions"
                                :searchable="false"
                                :canClear="false"
                            />
                        </div>
                    </div>
                </div>
                <div v-else-if="activeTab === 'import'">
                    <div class="grid grid-cols-4 gap-9 mt-5">
                        <div class="flex flex-col px-6.5 py-0">
                            <div class="relative w-[40%]">
                                <div>
                                    <input
                                        @change="(event) => {
                                            handleUpload(event);
                                        }"
                                        accept=".xlsx,.xls,.csv"
                                        ref="uploadFileInput"
                                        type="file"
                                        class="cursor-pointer rounded border-[1.5px] border-stroke bg-transparent font-medium outline-none transition file:mr-5 file:border-collapse file:cursor-pointer file:border-0 file:border-r file:border-solid file:border-stroke file:bg-whiter file:py-3 file:px-5 file:hover:bg-primary file:hover:bg-opacity-10 focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col px-6.5 py-0">
                            <CustomButton
                                type="button"
                                @click="exportImportedCustomers"
                                class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 h-[50px]"
                            >
                                <font-awesome-icon :icon="['far', 'file-export']"/>
                                Export imported customers ({{ form.customer_segment_imported_users_count }})
                            </CustomButton>
                        </div>
                    </div>
                </div>
                <div v-else-if="activeTab === 'segment-source'">
                    <div class="grid grid-cols-4 gap-9 mt-5">
                        <div class="flex flex-col px-6.5 py-0">
                            <CustomInput
                                v-model="form.criteria.use_abandoned_emails"
                                name="use_abandoned_emails"
                                label="Use abandoned emails"
                                type="checkbox"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.customers_segments[0].can_delete">
                        <CustomButton
                            v-if="emitAction === 'update'"
                            @click="store.commit('user/SET_DELETE_MODAL_VALUE', {
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

                    <template
                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.customers_segments[0].can_edit">
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
