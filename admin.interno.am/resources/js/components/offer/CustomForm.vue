<script setup>

import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";

import {computed, ref, toRefs, watch} from "vue";
import {useStore} from "vuex";
import {debounce} from "lodash";
import CustomInput from "@components/global/CustomInput.vue";
import {validate} from "@validation/customValidation.js";
import CustomSelect from "@components/global/CustomSelect.vue";


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
const offersOptions = ref([]);

watch(modelValue, (newVal) => {
    form.value = newVal;
}, {immediate: true});

watch(form.value, (newVal) => {
    emits('update:modelValue', newVal);
});

watch(
    () => form.type,
    (newType) => {
        if (newType === 1) {
            form.amountRules = ['required', 'validDecimal', 'maxValue:100'];
        } else if (newType === 0) {
            form.amountRules = ['required', 'validDecimal'];
        }
    }, {immediate: true}
);

store.dispatch('offer/fetchParams');
const params = computed(() => store.getters['offer/getParams']);

const emits = defineEmits([
    'update:modelValue',
    'submit'
])

const productsAutocomplete = debounce(async (term) => {
    productsOptions.value = await store.dispatch('product/autocomplete', {
        forOffer: true,
        field: 'name',
        term,
        language_id: form.value.language_id,
    });
}, 200);

const offersAutocomplete = debounce(async (term) => {
    offersOptions.value = await store.dispatch('offer/autocomplete', {
        field: 'title',
        term,
    });
}, 200);

const autocompleteRequest = async (optionVariables, alreadySelectedIds, term = '') => {
    let options = await store.dispatch('product/autocomplete', {
        forOffer: true,
        field: 'label',
        term,
        language_id: form.value.language_id,
        alreadySelectIds: alreadySelectedIds,
    });
    for (let i = 0; i < optionVariables.length; i++) {
        let variableName = optionVariables[i];
        if (variableName === 'productsOptions') {
            productsOptions.value = options;
        }
    }
}
if (props.emitAction === 'update') {
    let alreadySelectedIds = [];
    if (Array.isArray(form.value.cart_data)) {
        alreadySelectedIds = form.value.cart_data.map(item => item.product_variation_id);
    }
    autocompleteRequest(['productsOptions'], alreadySelectedIds);
}

const usersOptions = ref([]);
const usersAutocomplete = debounce(async (term) => {
    usersOptions.value = await store.dispatch('user/autocomplete', {
        field: 'offer_label',
        type: 'offer',
        term,
        alreadySelectIds: [form.value.offered_user_id],
    });
}, 200);

const usersAutocompleteRequest = async (alreadySelectedId, term = '') => {
    usersOptions.value = await store.dispatch('user/autocomplete', {
        field: 'label',
        type: 'offer',
        term,
        alreadySelectIds: [alreadySelectedId],
    });
}

if (props.emitAction === 'update') {
    usersAutocompleteRequest(form.value.offered_user_id);
} else {
    usersAutocompleteRequest([]);
}

const justSubmitted = ref(false);
const auth = computed(() => store.getters['auth/getUser']);

const removeProduct = (key) => {
    form.value.cart_data.splice(key, 1);
}

const dynamicValidation = (index, key, value, formKey) => {
    if (!form.value[formKey][index]) {
        form.value[formKey][index] = {};
    }

    if (!value[key] || value[key] === '') {
        form.value[formKey][index][key] = 'This field is required.';
    } else {
        delete form.value[formKey][index][key];
    }

    if (Object.keys(form.value[formKey][index]).length === 0) {
        delete form.value[formKey][index];
    }
};
const addProduct = () => {
    form.value.cart_data.push({
        product_variation_id: '',
        quantity: '',
        price: '',
    });
}

const offersId = ref('');
const offerImport = async () => {
    const res = await store.dispatch('offer/fetchByField', {id:offersId.value});
    form.value.language_id  = res.language_id;
    form.value.cart_data  = res.cart_data;
};

const changeLanguage = () => {
    form.value.cart_data = [{
        product_variation_id: '',
        quantity: '',
        price: '',
    }];
};

</script>

<template>
    <form @submit.prevent="emits('submit'), justSubmitted = true">
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 gap-9 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>
        <div class="grid grid-cols-2 gap-9">
            <div class="grid grid-cols-2">
                <div class="flex flex-col p-6.5">
                    <CustomSelect
                        @change="changeLanguage"
                        label="Languages *"
                        v-model="form.language_id"
                        mode="single"
                        placeholder="Select languages"
                        :options="params.languages"
                        :searchable="true"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                        @update:modelValue="form.errors = validate(form)"
                        :disabled="emitAction === 'update'"
                        :error="form.errors['language_id']"
                    />
                </div>
            </div>
            <div class="p-6.5 col-span-1" v-if="emitAction === 'create'">
                <div class="pr-8 flex">
                    <div class="mb-4 w-full mt-1 custom-tooltip">
                        <CustomSelect
                            @search-change="offersAutocomplete"
                            class="py-2 rounded-tl rounded-bl border-stroke bg-transparent"
                            v-model="offersId"
                            :searchable="true"
                            :can-clear="true"
                            label="Import products from another offer"
                            placeholder="No selected"
                            :options="offersOptions"
                            :need-autocomplete="true"
                            parent-div-classes="w-full"
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.offers[0].can_edit"
                        />
                    </div>
                    <CustomButton
                        :disabled="!offersId"
                        @click="offerImport"
                        type="button"
                        class="flex h-fit items-center gap-2 rounded-tr rounded-br bg-primary mt-auto mb-auto py-4.5 px-4.5 font-medium text-white hover:bg-opacity-80"
                    >
                        <font-awesome-icon :icon="['fas', 'file-import']" />
                        Import
                    </CustomButton>
                </div>
            </div>

        </div>
        <hr class="text-gray">
        <div class="grid grid-cols-1 gap-9">
            <div class="w-full p-7.5">
                <div class="grid grid-cols-3">
                    <div class="p-6.5 col-span-2">
                        <div class=" ">
                            <div class="col-span-9 ">
                                <div v-if="form.language_id" v-for="(cart, key) in form.cart_data" :key="key"
                                     class="flex flex-col mb-6.5 min-h-[100px] rounded-lg  border-[1.5px] shadow-default relative top-0 col-span-1 sm:col-span-1 md:col-span-1 ">
                                    <button
                                        v-if="form.cart_data.length > 1"
                                        type="button"
                                        @click="removeProduct(key)"
                                        class="hover:text-primary absolute right-2 top-1"
                                        title="Delete"
                                    >
                                        <font-awesome-icon :icon="['fas', 'trash-can']"/>
                                    </button>
                                    <div class="grid grid-cols-4 gap-3 px-4 pt-3">
                                        <div class="col-span-2">
                                            <CustomSelect
                                                @search-change="productsAutocomplete"
                                                class="py-2 rounded-lg border-stroke bg-transparent adb-input"
                                                v-model="cart.product_variation_id"
                                                :searchable="true"
                                                label="Product *"
                                                placeholder="No selected product"
                                                :options="productsOptions"
                                                :need-autocomplete="true"
                                                parent-div-classes="w-full"
                                                @update:modelValue="dynamicValidation(key, 'product_variation_id', {product_variation_id: cart.product_variation_id, product_variation_idRules: ['required']}, 'multiselectErrors')"
                                                :error="form.multiselectErrors[key]?.['product_variation_id'] ?? null"
                                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.offers[0].can_edit"
                                            />
                                        </div>
                                        <CustomInput
                                            class="adb-input col-span-1"
                                            v-model="cart.quantity"
                                            label="Quantity *"
                                            type="number"
                                            placeholder="Enter quantity"
                                            @keyup="dynamicValidation(key, 'quantity', {quantity: cart.quantity, quantityRules: ['required']}, 'multiselectErrors')"
                                            :error="form.multiselectErrors[key]?.['quantity'] ?? null"
                                        />
                                        <CustomInput
                                            class="adb-input col-span-1"
                                            v-model="cart.price"
                                            label="Price *"
                                            type="text"
                                            placeholder="Enter value"
                                            @keyup="dynamicValidation(key, 'price', {price: cart.price, priceRules: ['required']}, 'multiselectErrors')"
                                            :error="form.multiselectErrors[key]?.['price'] ?? null"
                                        />
                                    </div>

                                </div>
                                <CustomButton
                                    v-if="form.language_id"
                                    title="Add new section"
                                    @click="addProduct"
                                    type="button"
                                    class="flex items-center gap-2 rounded bg-meta-3 py-2 px-3.5 font-medium text-white hover:bg-opacity-80 ml-auto"
                                >
                                    <font-awesome-icon :icon="'plus'" class="size-5"/>
                                    Add product
                                </CustomButton>
                            </div>
                        </div>
                    </div>
                    <div class="flex p-6.5 flex-col col-span-1">
                        <div>
                            <CustomInput
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.offers[0].can_edit"
                                v-model="form.title"
                                name="title"
                                label="Offer Title *"
                                type="text"
                                placeholder="Enter title"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['title']"
                            />
                        </div>
                        <div>
                            <CustomSelect
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.offers[0].can_edit"
                                @search-change="usersAutocomplete"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                                v-model="form.offered_user_id"
                                :searchable="true"
                                label="User *"
                                mode="single"
                                placeholder="Select user"
                                :options="usersOptions"
                                :can-clear="true"
                                :need-autocomplete="true"
                                @update:modelValue="form.errors = validate(form)"
                                :error="form.errors['offered_user_id']"
                            />
                        </div>
                        <div>
                            <CustomSelect
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.offers[0].can_edit"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                                v-model="form.currency_id"
                                label="Currencies *"
                                placeholder="Select currencies"
                                :searchable="true"
                                :options="params.currencies"
                                :canClear="false"
                                @update:modelValue="form.errors = validate(form)"
                                :error="form.errors['currency_id']"
                            />
                        </div>
                        <div>
                            <CustomInput
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.offers[0].can_edit"
                                v-model="form.shipping_cost"
                                name="shipping_cost"
                                label="Shipping cost"
                                type="text"
                                placeholder="Enter shipping cost"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['shipping_cost']"
                            />
                        </div>
                        <div v-if="form.shipping_cost">
                            <CustomSelect
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.offers[0].can_edit"
                                label="Carrier *"
                                placeholder="Choose carrier *"
                                v-model="form.carrier"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['carrier']"
                                mode="single"
                                :options="params.carriers"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                            />
                        </div>
                        <div>
                            <CustomInput
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.offers[0].can_edit"
                                v-model="form.expire_days"
                                name="expire_days"
                                label="Will expire after *"
                                type="number"
                                placeholder="Enter will expire after"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['expire_days']"
                            />
                        </div>

                    </div>
                </div>

            </div>
        </div>
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.offers[0].can_delete">
                        <CustomButton
                            v-if="emitAction === 'update'"
                            @click="store.commit('offer/SET_DELETE_MODAL_VALUE', {
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

                    <template v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.offers[0].can_edit">
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
