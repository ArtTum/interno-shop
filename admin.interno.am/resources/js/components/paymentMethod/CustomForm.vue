<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import Switch from "@components/global/Switch.vue";
import AccordionTwo from "@components/accordion/AccordionTwo.vue";
import {validate} from "@validation/customValidation.js";

import {computed, ref, toRefs, watch,} from "vue";
import {useStore} from "vuex";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";

const store = useStore()

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    emitAction: {
        type: String
    },
    errors: {
        type: Array,
        required: false
    },
});

const {modelValue} = toRefs(props);
const form = ref(modelValue.value);

const emits = defineEmits([
    'update:modelValue',
    'submit',
    'update-account',
    'update-accounts-by-res',
    'fetch-accounts',
])

watch(modelValue, (newVal) => {
    form.value = newVal;
});

watch(form.value, (newVal) => {
    emits('update:modelValue', newVal);
});

store.dispatch('paymentMethod/fetchParams', {id: form.value.id});
const params = computed(() => store.getters['paymentMethod/getParams']);
const auth = computed(() => store.getters['auth/getUser']);

const activeTab = ref('general');
const tabsWithErrors = ref([]);
const justSubmitted = ref(false);

watch(
    () => form.value.errors,
    (newVal) => {
        tabsWithErrors.value = [];
        if (
            Object.hasOwn(newVal, 'name')
        ) {
            if (justSubmitted.value) {
                activeTab.value = 'general';
                justSubmitted.value = false;
            }
            tabsWithErrors.value.push('general');
        }

    },
    {deep: true}
);

const tabsRoutes = [
    {key: 'general', title: 'General *', icon: ['far', 'gear']},
    {key: 'accounts', title: 'Accounts', icon: ['far', 'file-invoice-dollar']},
];

const generateAccounts = async () => {
    const res = await store.dispatch('paymentMethod/generateAccounts', {
        id: form.value.id
    })

    if (res.data.data) {
        emits('update-accounts-by-res', res.data.data, res.data.accountsPagination);
    }
    store.commit('notification/SET_NOTIFICATION', {
        visible: true,
        title: 'Success',
        message: 'Successfully generated'
    });
}

const deleteAccounts = async (deleteInvalids) => {
    const res = await store.dispatch('paymentMethod/deleteAccounts', {
        payment_method_id: form.value.id,
        delete_invalids: deleteInvalids
    });

    if (res.data.data) {
        emits('update-accounts-by-res', res.data.data, res.data.accountsPagination);
    }

    store.commit('notification/SET_NOTIFICATION', {
        visible: true,
        title: 'Success',
        message: 'Successfully updated'
    });
}
const generalParams = computed(() => store.getters['general/getParams']);

</script>

<template>
    <form @submit.prevent="emits('submit')">
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 gap-9 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>
        <div class="grid grid-cols-5 gap-9">
            <div class="flex flex-col p-6.5">
                <CustomSelect
                    label="Languages"
                    v-model="form.language_id"
                    mode="single"
                    placeholder="Select languages"
                    :disabled="emitAction === 'create'"
                    :options="params.languages"
                    :searchable="true"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['language_id']"
                />
            </div>
        </div>
        <hr class="text-gray">

        <div class="grid grid-cols-1 gap-9">
            <div class="w-full p-7.5">
                <div class="mb-6 flex flex-wrap gap-5 border-b border-stroke">
                    <template
                        :key="key"
                        v-for="(tabRoute, key) in tabsRoutes"
                    >
                        <template v-if="tabRoute.key !== 'seo' || pageType === 1 || pageType === 0">
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
                    </template>
                </div>
                <div v-if="activeTab === 'general'">
                    <div class="grid grid-cols-5 gap-9">
                        <div class="flex flex-col p-6.5 pb-0 col-span-1">
                            <div class="mt-8">
                                <img
                                    width="90px"
                                    :src="`/icon/payment/${form.payment_key}.svg`"
                                >
                            </div>
                        </div>
                        <div class="flex flex-col p-6.5 pb-0 col-span-2">
                            <div v-if="params.payment_types">
                                <CustomInput
                                    v-model="params.payment_types[form.payment_type]"
                                    name="type"
                                    label="Type"
                                    type="text"
                                    :disabled="true"
                                />
                            </div>
                        </div>
                        <div class="flex flex-col p-6.5 pb-0 col-span-2">
                            <div>
                                <CustomInput
                                    v-model="form.payment_key"
                                    name="key"
                                    label="Key"
                                    type="text"
                                    :disabled="true"
                                    placeholder="Key"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-9 gap-9">
                        <div class="flex flex-col p-6.5 pb-0 col-span-2">
                            <div>
                                <CustomInput
                                    v-model="form.name"
                                    name="name"
                                    label="Name *"
                                    type="text"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.payment_methods[0].can_edit"
                                    placeholder="Enter name"
                                    @keyup="form.errors = validate(form)"
                                    :error="form.errors['name']"
                                />
                            </div>
                        </div>
                        <div class="flex flex-col p-6.5 pb-0 col-span-2">
                            <div>
                                <CustomSelect
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.payment_methods[0].can_edit"
                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                    v-model="form.payment_method_country_ids"
                                    @update:modelValue="form.errors = validate(form)"
                                    mode="tags"
                                    label="Billing countries"
                                    placeholder="Select countries"
                                    :searchable="true"
                                    :options="params.countries"
                                    :show-labels="true"
                                    :close-on-select="false"
                                    :canClear="false"
                                    :error="form.errors['payment_method_country_ids'] ?? null"
                                />
                            </div>
                        </div>
                        <div class="flex flex-col p-6.5 pb-0 col-span-2">
                            <div>
                                <CustomSelect
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.payment_methods[0].can_edit"
                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                    v-model="form.payment_method_currency_ids"
                                    @update:modelValue="form.errors = validate(form)"
                                    mode="tags"
                                    label="Currencies"
                                    placeholder="Select currencies"
                                    :searchable="true"
                                    :options="params.currencies"
                                    :show-labels="true"
                                    :close-on-select="false"
                                    :canClear="false"
                                    :error="form.errors['payment_method_currency_ids'] ?? null"
                                />
                            </div>
                        </div>
                        <div class="flex flex-col p-6.5 pb-0 col-span-2">
                            <div class="flex flex-col col-span-2" v-if="generalParams?.vendor?.b2b">
                                <CustomSelect
                                    class="py-2 rounded-lg  border-stroke bg-transparent"
                                    v-model="form.customer_group_ids"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.payment_methods[0].can_edit"
                                    mode="tags"
                                    label="B2B Customer groups"
                                    placeholder="Select groups"
                                    :options="params.customerGroups"
                                    :show-labels="true"
                                    :close-on-select="false"
                                />
                            </div>
                        </div>
                        <div class="flex flex-col p-6.5 pb-0">
                            <div>
                                <Switch
                                    @change="(value) => {
                               form.status = value;
                            }"
                                    class="w-fit"
                                    :disabled="!auth.user_group.permissions_by_name.payment_methods[0].can_edit"
                                    :value="form.status"
                                    id="status"
                                    label="Status *"
                                />
                            </div>
                        </div>

                    </div>
                    <div class="grid grid-cols-1 gap-9">
                        <div class="flex flex-col p-6.5 pb-0">
                            <div>
                                <label class="mb-2.5 block font-medium text-black">Description</label>
                                <CKEditorComponent
                                    :disabled="!auth.user_group.permissions_by_name.categories[0].can_edit"
                                    :model="form.description"
                                    @updateValue="(value) => {
                                form.description = value
                            }"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else-if="activeTab === 'accounts'">
                    <div class="grid grid-cols-1 mt-6">
                        <div class="px-6.5 flex ml-auto">
                            <div class="mr-3.5">
                                <template v-if="auth.user_group.permissions_by_name.payment_methods[0].can_delete">
                                    <CustomButton
                                        type="button"
                                        @click="deleteAccounts(true)"
                                        class="flex items-center gap-2 rounded bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                                    >
                                        <font-awesome-icon :icon="['far', 'trash']"/>
                                        Delete invalids
                                    </CustomButton>
                                </template>
                            </div>
                            <div class="mr-3.5">
                                <template v-if="auth.user_group.permissions_by_name.payment_methods[0].can_delete">
                                    <CustomButton
                                        type="button"
                                        @click="deleteAccounts(false)"
                                        class="flex items-center gap-2 rounded bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                                    >
                                        <font-awesome-icon :icon="['far', 'trash']"/>
                                        Delete all
                                    </CustomButton>
                                </template>
                            </div>
                            <div class="mr-3.5">
                                <template v-if="auth.user_group.permissions_by_name.payment_methods[0].can_add">
                                    <CustomButton
                                        @click="generateAccounts"
                                        type="button"
                                        class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                                    >
                                        <font-awesome-icon :icon="['far', 'wind-turbine']"/>
                                        Generate accounts
                                    </CustomButton>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="p-6.5 pb-0 text-2xl text-black font-bold">
                        Filters and search
                    </div>
                    <div class="grid grid-cols-3 mt-6">
                        <div class="px-6.5 flex flex-col">
                            <CustomSelect
                                class="py-2 rounded-lg border-stroke bg-transparent"
                                :searchable="true"
                                v-model="form.accounts_query.country_id"
                                label="Countries"
                                placeholder="Select countries"
                                :options="params.countries"
                                :show-labels="true"
                                :canClear="true"
                            />
                        </div>
                        <div class="px-6.5 flex flex-col">
                            <CustomSelect
                                class="py-2 rounded-lg border-stroke bg-transparent"
                                :searchable="true"
                                v-model="form.accounts_query.currency_id"
                                label="Currencies"
                                placeholder="Select currencies"
                                :options="params.currencies"
                                :show-labels="true"
                                :canClear="true"
                            />
                        </div>
                    </div>
                    <div class="px-6.5 flex col-span-1 justify-between">
                        <div class="flex">
                            <div>
                                <CustomButton
                                    @click="emits('fetch-accounts')"
                                    class="flex items-center gap-2 ml-3 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                                    type="button"
                                >
                                    <font-awesome-icon :icon="['far', 'magnifying-glass']"/>
                                    Կիրառել որոնումը և ֆիլտրերը
                                </CustomButton>
                            </div>
                        </div>
                        <div class="datatable-info mt-0">
                                 <span class="mr-4">
                                     Showing {{ form.accounts_query.pagination.showing.from }} to
                                 {{ form.accounts_query.pagination.showing.to }} of
                                 {{ form.accounts_query.pagination.total_items }} entries
                                 </span>
                            <vue-awesome-paginate
                                v-if="20 < form.accounts_query.pagination.total_items"
                                :total-items="form.accounts_query.pagination.total_items"
                                :items-per-page="20"
                                :max-pages-shown="3"
                                v-model="form.accounts_query.page"
                                @click="emits('fetch-accounts')"
                            >
                                <template #prev-button>
                                    <a class="flex h-9 w-9 items-center justify-center rounded-l-md border border-stroke hover:border-primary hover:bg-gray hover:text-primary">
                                        <font-awesome-icon :icon="['fal', 'angle-left']" class="size-5"/>
                                    </a>
                                </template>
                                <template #next-button>
                                    <a class="flex h-9 w-9 items-center justify-center rounded-r-md border border-stroke border-l-transparent hover:border-primary hover:bg-gray hover:text-primary">
                                        <font-awesome-icon :icon="['fal', 'angle-right']" class="size-5"/>
                                    </a>
                                </template>
                            </vue-awesome-paginate>
                        </div>
                    </div>
                    <template
                        v-for="(account, index) in form.accounts"
                        :key="index"
                    >
                        <AccordionTwo
                            :parent="account"
                            :invalid="!account.info || !Object.keys(account.info).length"
                        >
                            <template #header>
                                <div class="flex w-full">
                                    <div>
                                        <span>#{{ account.id }}</span>
                                    </div>

                                    <div
                                        class="grid w-full"
                                        :class="`grid-cols-2`"
                                    >
                                        <div class="flex flex-col p-6.5 pb-0">
                                            <CustomSelect
                                                v-model="account.country_id"
                                                class="py-2 rounded-lg border-stroke bg-transparent"
                                                label="Country"
                                                :options="params.countries"
                                                :disabled="true"
                                                :show-labels="true"
                                                :close-on-select="false"
                                                :canClear="false"
                                            />
                                        </div>
                                        <div class="flex flex-col p-6.5 pb-0">
                                            <CustomSelect
                                                v-model="account.currency_id"
                                                class="py-2 rounded-lg border-stroke bg-transparent"
                                                label="Currency"
                                                :options="params.currencies"
                                                :disabled="true"
                                                :show-labels="true"
                                                :close-on-select="false"
                                                :canClear="false"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template #content>
                                <div class="grid grid-cols-7 w-full">
                                    <div class="flex flex-col p-6.5 pb-0 col-span-1">
                                        <Switch
                                            @change="(value) => {
                                               account.is_base = value;
                                            }"
                                            :value="account.is_base"
                                            :id="'is_base_' + index"
                                            label="Is base"
                                        />
                                    </div>
                                    <div class="flex flex-col p-6.5 pb-0 col-span-2">
                                        <CustomInput
                                            :disabled="!auth.user_group.permissions_by_name.payment_methods[0].can_edit"
                                            v-model="account.info.account_name"
                                            name="account_name"
                                            label="Account name *"
                                            type="text"
                                            placeholder="Enter account name"
                                            @keyup="delete errors[index]?.info?.account_name"
                                            :error="errors[index]?.info?.account_name ?? null"
                                        />
                                    </div>
                                    <div class="flex flex-col p-6.5 pb-0 col-span-2">
                                        <CustomInput
                                            :disabled="!auth.user_group.permissions_by_name.payment_methods[0].can_edit"
                                            v-model="account.info.account_number"
                                            name="account_number"
                                            label="Account number"
                                            type="text"
                                            placeholder="Enter account number"
                                            @keyup="delete errors[index]?.info?.account_number"
                                            :error="errors[index]?.info?.account_number ?? null"
                                        />
                                    </div>
                                    <div class="flex flex-col p-6.5 pb-0 col-span-2">
                                        <CustomInput
                                            :disabled="!auth.user_group.permissions_by_name.payment_methods[0].can_edit"
                                            v-model="account.info.bank_name"
                                            name="bank_name"
                                            label="Bank name *"
                                            type="text"
                                            placeholder="Enter bank name"
                                            @keyup="delete errors[index]?.info?.bank_name"
                                            :error="errors[index]?.info?.bank_name ?? null"
                                        />
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 w-full">
                                    <div class="flex flex-col p-6.5 pb-0">
                                        <CustomInput
                                            :disabled="!auth.user_group.permissions_by_name.payment_methods[0].can_edit"
                                            v-model="account.info.sort_code"
                                            name="sort_code"
                                            label="Sort code"
                                            type="text"
                                            placeholder="Enter sort code"
                                            @keyup="delete errors[index]?.info?.sort_code"
                                            :error="errors[index]?.info?.sort_code ?? null"
                                        />
                                    </div>
                                    <div class="flex flex-col p-6.5 pb-0">
                                        <CustomInput
                                            :disabled="!auth.user_group.permissions_by_name.payment_methods[0].can_edit"
                                            v-model="account.info.iban"
                                            name="iban"
                                            label="Iban *"
                                            type="text"
                                            placeholder="Enter IBAN"
                                            @keyup="delete errors[index]?.info?.iban"
                                            :error="errors[index]?.info?.iban ?? null"
                                        />
                                    </div>
                                    <div class="flex flex-col p-6.5 pb-0">
                                        <CustomInput
                                            :disabled="!auth.user_group.permissions_by_name.payment_methods[0].can_edit"
                                            v-model="account.info.bic_swift"
                                            name="bic_swift"
                                            label="BIC / Swift *"
                                            type="text"
                                            placeholder="Enter BIC / Swift"
                                            @keyup="delete errors[index]?.info?.bic_swift"
                                            :error="errors[index]?.info?.bic_swift ?? null"
                                        />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1">
                                    <div class="flex flex-col p-6.5">
                                        <div class="flex ml-auto gap-5">
                                            <template
                                                v-if="auth.user_group.permissions_by_name.payment_methods[0].can_delete">
                                                <CustomButton
                                                    @click="store.commit('paymentMethod/SET_DELETE_MODAL_VALUE', {
                                                            value: true,
                                                            id: account.id,
                                                            deletingActionApi: 'delete-account',
                                                            deletingText: 'Deleting this account will be permanent and cannot be undone. Once deleted, you will not be able to restore it.'
                                                        })"
                                                    class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                                                    type="button"
                                                >
                                                    <font-awesome-icon :icon="['far', 'trash']"/>
                                                    Delete
                                                </CustomButton>
                                            </template>

                                            <template
                                                v-if="auth.user_group.permissions_by_name.payment_methods[0].can_edit">
                                                <CustomButton
                                                    @click="emits('update-account', account, index)"
                                                    class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                                                    type="button"
                                                >
                                                    <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                                                    Save
                                                </CustomButton>
                                            </template>
                                        </div>

                                    </div>
                                </div>
                            </template>
                        </AccordionTwo>
                    </template>
                </div>
            </div>
        </div>

        <hr class="text-gray mt-6.5">
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template
                        v-if="auth.user_group.permissions_by_name.payment_methods[0].can_edit">
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
