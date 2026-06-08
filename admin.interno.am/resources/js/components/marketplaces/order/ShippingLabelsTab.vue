<script setup>

import {useStore} from "vuex";
import {computed, reactive, ref} from "vue";
import CustomDatePicker from "@components/global/CustomDatePicker.vue";
import CustomButton from "@components/global/CustomButton.vue";
import CustomInput from "@components/global/CustomInput.vue";
import DeleteModal from "@components/global/DeleteModal.vue";
import Switch from "@components/global/Switch.vue";
import AdbModal from "@components/order/AdbModal.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import {validate} from "@validation/customValidation.js";

const store = useStore()
const props = defineProps({
    shipping_labels: {
        type: Array
    },
    order: {
        type: Object
    },
    vendor_name: {
        type: String
    },
    dpd_setting: {
        type: Object
    },
    dhl_setting: {
        type: Object
    },
    fedex_setting: {
        type: Object
    },
    tnt_setting: {
        type: Object
    },
    carriers: {
        type: Object
    },
});
const today = new Date();
const dayOfWeek = today.getDay();
let adjustedDate;
if (dayOfWeek === 6) {
    adjustedDate = new Date(today);
    adjustedDate.setDate(today.getDate() + 2);
} else if (dayOfWeek === 0) {
    adjustedDate = new Date(today);
    adjustedDate.setDate(today.getDate() + 1);
} else {
    adjustedDate = today;
}

const dateNow = adjustedDate.toISOString().split('T')[0];
const formShippingLabel = reactive({
    id: '',
    type: '',
    predict_function: props.dpd_setting.predict_function,
    parcel_content: props.dpd_setting.parcel_content,
    order_id: props.order.id,
    additional_count: 1,
    shipping_date: dateNow,
    errors: [],
});



const emits = defineEmits([
    'fetch',
]);
const fetchPageData = () => {
    emits('fetch');
};

const auth = computed(() => store.getters['auth/getUser']);
const generalParams = computed(() => store.getters['general/getParams']);

const filteredItem = (id) => {
    const vendor = generalParams.value?.vendor;
    if (!vendor) return null;
    return vendor.shipping_and_labels.includes(id)
};


const getShippingLabel = (type) => {
    let ShippingLabel = props.shipping_labels.filter(label => label.type === type);

    if (ShippingLabel.length) {
        let label = {...ShippingLabel[0]};
        if (label.date) {
            const dateObj = new Date(label.date);
            label.date = `${String(dateObj.getMonth() + 1).padStart(2, '0')}/${String(dateObj.getDate()).padStart(2, '0')}/${dateObj.getFullYear()}`;
        }

        if (label['created_at']) {
            const dateObj = new Date(label['created_at']);
            const formattedDate = `${String(dateObj.getMonth() + 1).padStart(2, '0')}/${String(dateObj.getDate()).padStart(2, '0')}/${dateObj.getFullYear()}`;
            const formattedTime = `${String(dateObj.getHours()).padStart(2, '0')}:${String(dateObj.getMinutes()).padStart(2, '0')}`;
            label['created_at'] = `${formattedDate} ${formattedTime}`;
        }
        return label;
    }
    return null;
};

const createShippingLabel = async (type) => {
    formShippingLabel.type = type;

    try {
        const response = await store.dispatch('order/createShippingLabel', formShippingLabel);
        emits('fetch');
        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully added'
        });
        formShippingLabel.errors = [];
    } catch (reqErrors) {
        formShippingLabel.errors = reqErrors;
    }
};
const createAdditionalLabel = async (id, type) => {
    formShippingLabel.type = type;
    formShippingLabel.id = id;

    try {
        const response = await store.dispatch('order/createAdditionalLabel', formShippingLabel);
        emits('fetch');
        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully added'
        });
        formShippingLabel.additional_count = 1;
    } catch (reqErrors) {
        formShippingLabel.errors = reqErrors;
    }
};

const checkShippingLabel = (types, alternativeShipping = null) => {
    if (alternativeShipping) {
        return true;
    }

    for (const type of types) {
        const shippingLabel = props.shipping_labels.filter(label => label.type === type);

        if (shippingLabel.length) {
            return true;
        }
    }
    return false;
};

const changeDate = (event) => {
    formShippingLabel.shipping_date = event.target.value;
};

const isModalOpen = ref(false);

const openModal = () => {
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const products = ref({items: [{
    customs_tariff_number: '',
    description_in_german: '',
    price: '',
    quantity: '',
    weight: '',
}], type: 'endgültig (Verkauf)', shipping_label_id: getShippingLabel(4)?.id});

const addNewSection = () => {
    products.value.items.push({
        customs_tariff_number: '',
        description_in_german: '',
        price: '',
        quantity: '',
        weight: '',
    });
}
const removeProduct = (key) => {
    products.value.items.splice(key, 1);
}

const multiselectErrors = ref([]);
const dynamicValidation = (index, key, form, validationArrayKey) => {
    if (!multiselectErrors.value[index]) {
        multiselectErrors.value[index] = {};
    }
    multiselectErrors.value[index][key] = validate(form)[key];
};

const product_total_price = ref(0);
const product_total_weight = ref(0);

const handleConfirm = async () => {

    if (product_total_price.value > props.order.items_subtotal_price) {
        alert('Error: Amount of your entered items is greater tha order products amount');
        return false;
    }

    if (product_total_weight.value > props.order.product_total_weight) {
        alert('Error: Weights of your entered items is greater tha order weight');
        return false;
    }

    multiselectErrors.value = {};

    products.value.items.forEach((item, itemKey) => {
        Object.keys(item).forEach(key => {
            if (!item[key]) {
                dynamicValidation(
                    itemKey,
                    key,
                    {
                        [key]: item[key],
                        [`${key}Rules`]: ['required']
                    },
                    'multiselectErrors'
                );
            }
        });
    });

    if (Object.keys(multiselectErrors.value).length > 0) {
        return false;
    }

    try {
        const response = await store.dispatch('order/createADBDocument', products.value);
        if (response) {
            product_total_price.value = 0;
            product_total_weight.value = 0;
            products.value.items = [{
                customs_tariff_number: '',
                description_in_german: '',
                price: '',
                quantity: '',
                weight: '',
            }];
            store.commit('notification/SET_NOTIFICATION', {
                visible: true,
                title: 'Success',
                message: 'Successfully'
            });
            closeModal();
            emits('fetch');
        }
    } catch (error) {
        console.error("Error creating document:", error);
    }
};
const sendEmail = async () => {
    try {
        const response = await store.dispatch('order/sendADBDocument', {id: getShippingLabel(4).id});
        if (response) {
            store.commit('notification/SET_NOTIFICATION', {
                visible: true,
                title: 'Success',
                message: 'Successfully send'
            });
            emits('fetch');
        }
    } catch (error) {
        console.error("Error creating document:", error);
    }
};
const approvedCustoms = async () => {
    try {
        const response = await store.dispatch('order/approvedCustoms', {id: getShippingLabel(4).id});
        if (response) {
            store.commit('notification/SET_NOTIFICATION', {
                visible: true,
                title: 'Success',
                message: 'Successfully send'
            });
            emits('fetch');
        }
    } catch (error) {
        console.error("Error creating document:", error);
    }
};

const changeInfo = () => {
    product_total_price.value = 0;
    product_total_weight.value = 0;

    products.value.items.forEach((item, itemKey) => {
        if (item.price && item.quantity) {
            product_total_price.value += item.price;
        }

        if (item.weight && item.quantity) {
            product_total_weight.value += item.weight;
        }
    });
}

</script>

<template>
    <div class="flex">
        <h1 class="text-xl font-bold text-black mb-2.5">Shipping labels</h1> <br>
        <div class="ml-auto">
            <a
                target="_blank"
                :href="getShippingLabel(4).nf4_file + '?v=' + Date.now()"
                v-if="getShippingLabel(4) && getShippingLabel(4).nf4_file"
                class="text-center   gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-fit"
                type="button"
            >
                <font-awesome-icon :icon="['far', 'download']"/>
                Download NF4 document
            </a>
            <a
                target="_blank"
                :href="'/orders/show-imo-document/' + checkShippingLabel([4]) + '/' + order.id + '?v=' + Date.now()"
                v-if="order.is_dangerous && generalParams?.vendor?.dgd"
                class="text-center ml-5   gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-fit"
                type="button"
            >
                <font-awesome-icon :icon="['far', 'download']"/>
                Download IMO document
            </a>
        </div>
    </div>
    <div class="mb-5">
        <h4 class="text-md font-bold  text-black mb-2.5">Selected Carrier:
            <span class="text-meta-1">{{ carriers[order.shipping_carrier] }}</span>
        </h4>
    </div>
    <div v-if="order.alternative_shipping" class="bg-amber-200 px-6.5 py-2.5 mb-5">
        It's not possible to create shipping label if Alternative shipping method is selected
    </div>
    <div v-if="order.number" class="bg-amber-200 px-6.5 py-2.5 mb-5">
        Orders from OMS are not editable!
    </div>
    <div
        v-if="Object.keys(formShippingLabel.errors).length > 0 && formShippingLabel.errors"
        class="grid grid-cols-1 gap-9 p-6.5"
    >
        <AlertError :errors="formShippingLabel.errors"/>
    </div>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-4 2xl:gap-7.5">
        <div v-if="filteredItem(1)" class="rounded-sm border border-stroke bg-white shadow-default ">
            <div class="border-b border-stroke p-4">
                <h3 class="font-medium text-black">DPD shipping labels</h3>
            </div>
            <div class="p-4" v-if="getShippingLabel(1)">
                <p>Created date: {{ getShippingLabel(1).created_at }}</p>
                <p>Shipping date: {{ getShippingLabel(1).date }}</p>
                <p v-if="getShippingLabel(1).user">Created by: {{ getShippingLabel(1).user.name }}
                    {{ getShippingLabel(1).user.last_name }}</p>
                <div class="border-b-2 my-2.5"></div>
                <ul class="tntsl-parcel-numbers list-none">
                    <li v-for="parcelNumber  in getShippingLabel(1).parcel_number "
                        class="before:content-['•'] before:mr-2 before:text-gray-700">
                        <a :href="'https://tracking.dpd.de/status/de_DE/parcel/' + parcelNumber"
                           target="_blank" class="hover:text-blue-800 underline hover:no-underline">
                            {{ parcelNumber }}
                        </a>
                    </li>
                </ul>
                <div class="flex gap-2 mt-5">
                    <a
                        target="_blank"
                        :href="'/pdf/' + vendor_name + '/dpd/' +getShippingLabel(1).file_name + '?v=' + Date.now()"
                        class="text-center  gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-full"
                        type="button"
                    >
                        <font-awesome-icon :icon="['far', 'download']"/>
                        Download
                    </a>
                    <CustomButton
                        @click="store.commit('order/SET_DELETE_SHIPPING_LABEL_MODAL_VALUE', {
                                value: true,
                                id:  getShippingLabel(1).id,
                                deletingActionApi: 'delete',
                                deletingText: null,
                            });"
                        :disabled="order.warehouse_status === 4"
                        class=" text-center  gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-full"
                        type="button"
                    >
                        <font-awesome-icon :icon="['far', 'trash']"/>
                        Delete
                    </CustomButton>
                </div>
                <div class="border-b-2 my-2.5"></div>
                <div>
                    <h5>Additional Label</h5>
                    <CustomInput
                        v-model="formShippingLabel.additional_count"
                        name="name"
                        :label="'Labels count to create (each parcel weight will be ' + dpd_setting.parcel_max_weight + 'KG)'"
                        type="number"
                        placeholder="Enter  number"
                        :invalidFeedbackPlace="false"
                    />
                    <custom-button
                        @click="createAdditionalLabel(getShippingLabel(1).id, 1)"
                        type="button"
                        class="gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-full">
                        <font-awesome-icon :icon="['fas', 'plus']"/>
                        Create additional label
                    </custom-button>
                </div>
            </div>
            <div class="p-4" v-else>
                <p>Shipping Label hasn't been created yet. Please create one.</p>
                <div class="border-b-2 my-2.5"></div>
                <Switch
                    @change="(value) => {
                       formShippingLabel.predict_function = value;
                    }"
                    class="w-fit mb-2.5 shipping-label-switch"
                    :value="formShippingLabel.predict_function"
                    id="predict_function"
                    label="DPD Predict"
                />

                <CustomInput
                    v-model="formShippingLabel.parcel_content"
                    name="parcel_content"
                    :label="'Parcel content description'"
                    type="text"
                    placeholder="Enter parcel content description"
                    :invalidFeedbackPlace="false"
                />
                <CustomDatePicker
                    @change="changeDate"
                    :value="dateNow"
                    placeholder="yyyy/mm/dd"
                    label="Shipping date"
                    format="Y-m-d"
                    minDate="today"
                />
                <custom-button
                    :disabled="order.number || checkShippingLabel([2,3,4], order.alternative_shipping)"
                    type="button"
                    @click="createShippingLabel(1)"
                    class="gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-full">
                    <font-awesome-icon :icon="['fas', 'plus']"/>
                    Create shipping label
                </custom-button>
            </div>
        </div>
        <div v-if="filteredItem(2)" class="rounded-sm border border-stroke bg-white shadow-default ">
            <div class="border-b border-stroke p-4">
                <h3 class="font-medium text-black">DHL shipping labels</h3>
            </div>
            <div class="p-4" v-if="getShippingLabel(2)">
                <p>Created date: {{ getShippingLabel(2).created_at }}</p>
                <p>Shipping date: {{ getShippingLabel(2).date }}</p>
                <p v-if="getShippingLabel(2).user">Created by: {{ getShippingLabel(2).user.name }}
                    {{ getShippingLabel(2).user.last_name }}</p>
                <div class="border-b-2 my-2.5"></div>
                <ul class="tntsl-parcel-numbers list-none">
                    <li v-for="parcelNumber  in getShippingLabel(2).parcel_number "
                        class="before:content-['•'] before:mr-2 before:text-gray-700">
                        <a :href="'https://www.dhl.de/en/privatkunden/pakete-empfangen/verfolgen.html?piececode=' + parcelNumber"
                           target="_blank" class="hover:text-blue-800 underline hover:no-underline">
                            {{ parcelNumber }}
                        </a>
                    </li>
                </ul>
                <div class="flex gap-2 mt-5">
                    <a
                        target="_blank"
                        :href="'/pdf/' + vendor_name + '/dhl/' +getShippingLabel(2).file_name + '?v=' + Date.now()"
                        class="text-center  gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-full"
                        type="button"
                    >
                        <font-awesome-icon :icon="['far', 'download']"/>
                        Download
                    </a>
                    <CustomButton
                        @click="store.commit('order/SET_DELETE_SHIPPING_LABEL_MODAL_VALUE', {
                                value: true,
                                id:  getShippingLabel(2).id,
                                deletingActionApi: 'delete',
                                deletingText: null,
                            });"
                        class=" text-center  gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-full"
                        type="button"
                    >
                        <font-awesome-icon :icon="['far', 'trash']"/>
                        Delete
                    </CustomButton>
                </div>
                <div class="border-b-2 my-2.5"></div>
                <div>
                    <h5>Additional Label</h5>
                    <CustomInput
                        v-model="formShippingLabel.additional_count"
                        name="name"
                        :label="'Labels count to create (each parcel weight will be ' + dhl_setting.parcel_max_weight + 'KG)'"
                        type="number"
                        placeholder="Enter  number"
                        :invalidFeedbackPlace="false"
                    />
                    <custom-button
                        @click="createAdditionalLabel(getShippingLabel(2).id, 2)"
                        type="button"
                        class="gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-full">
                        <font-awesome-icon :icon="['fas', 'plus']"/>
                        Create additional label
                    </custom-button>
                </div>
            </div>
            <div class="p-4" v-else>
                <p>Shipping Label hasn't been created yet. Please create one.</p>
                <div class="border-b-2 my-2.5"></div>
                <CustomDatePicker
                    @change="changeDate"
                    :value="dateNow"
                    placeholder="yyyy/mm/dd"
                    label="Shipping date"
                    format="Y-m-d"
                    minDate="today"
                />
                <custom-button
                    :disabled="order.number || checkShippingLabel([1,3,4], order.alternative_shipping)"
                    type="button"
                    @click="createShippingLabel(2)"
                    class="gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-full">
                    <font-awesome-icon :icon="['fas', 'plus']"/>
                    Create shipping label
                </custom-button>
            </div>
        </div>

        <div v-if="filteredItem(3)" class="rounded-sm border border-stroke bg-white shadow-default ">
            <div class="border-b border-stroke p-4">
                <h3 class="font-medium text-black">Fedex shipping labels</h3>
            </div>
            <div class="p-4" v-if="getShippingLabel(3)">
                <p>Created date: {{ getShippingLabel(3).created_at }}</p>
                <p>Shipping date: {{ getShippingLabel(3).date }}</p>
                <p v-if="getShippingLabel(3).user">Created by: {{ getShippingLabel(3).user.name }}
                    {{ getShippingLabel(3).user.last_name }}</p>
                <div class="border-b-2 my-2.5"></div>
                <ul class="tntsl-parcel-numbers list-none">
                    <li v-for="parcelNumber  in getShippingLabel(3).parcel_number "
                        class="before:content-['•'] before:mr-2 before:text-gray-700">
                        <a :href="'https://www.dhl.de/en/privatkunden/pakete-empfangen/verfolgen.html?piececode=' + parcelNumber"
                           target="_blank" class="hover:text-blue-800 underline hover:no-underline">
                            {{ parcelNumber }}
                        </a>
                    </li>
                </ul>
                <div class="flex gap-2 mt-5">
                    <a
                        target="_blank"
                        :href="'/pdf/' + vendor_name + '/fedex/' +getShippingLabel(3).file_name + '?v=' + Date.now()"
                        class="text-center  gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-full"
                        type="button"
                    >
                        <font-awesome-icon :icon="['far', 'download']"/>
                        Download
                    </a>
                    <CustomButton
                        @click="store.commit('order/SET_DELETE_SHIPPING_LABEL_MODAL_VALUE', {
                                value: true,
                                id:  getShippingLabel(3).id,
                                deletingActionApi: 'delete',
                                deletingText: null,
                            });"
                        class=" text-center  gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-full"
                        type="button"
                    >
                        <font-awesome-icon :icon="['far', 'trash']"/>
                        Delete
                    </CustomButton>
                </div>
                <div class="border-b-2 my-2.5"></div>
                <div>
                    <h5>Additional Label</h5>
                    <CustomInput
                        v-model="formShippingLabel.additional_count"
                        name="name"
                        :label="'Labels count to create (each parcel weight will be ' + fedex_setting.parcel_max_weight + ' OZ)'"
                        type="number"
                        placeholder="Enter  number"
                        :invalidFeedbackPlace="false"
                    />
                    <custom-button
                        @click="createAdditionalLabel(getShippingLabel(3).id, 3)"
                        type="button"
                        class="gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-full">
                        <font-awesome-icon :icon="['fas', 'plus']"/>
                        Create additional label
                    </custom-button>
                </div>
            </div>
            <div class="p-4" v-else>
                <p>Shipping Label hasn't been created yet. Please create one.</p>
                <div class="border-b-2 my-2.5"></div>
                <CustomDatePicker
                    @change="changeDate"
                    :value="dateNow"
                    placeholder="yyyy/mm/dd"
                    label="Shipping date"
                    format="Y-m-d"
                    minDate="today"
                />
                <custom-button
                    :disabled="order.number || checkShippingLabel([1,2,4], order.alternative_shipping)"
                    type="button"
                    @click="createShippingLabel(3)"
                    class="gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-full">
                    <font-awesome-icon :icon="['fas', 'plus']"/>
                    Create shipping label
                </custom-button>
            </div>
        </div>
        <div v-if="filteredItem(4)" class="rounded-sm border border-stroke bg-white shadow-default ">
            <div class="border-b border-stroke p-4">
                <h3 class="font-medium text-black">TNT shipping labels</h3>
            </div>
            <div class="p-4" v-if="getShippingLabel(4)">
                <p>Created date: {{ getShippingLabel(4).created_at }}</p>
                <p>Shipping date: {{ getShippingLabel(4).date }}</p>
                <p v-if="getShippingLabel(4).user">Created by: {{ getShippingLabel(4).user.name }}
                    {{ getShippingLabel(4).user.last_name }}</p>
                <div class="border-b-2 my-2.5"></div>
                <ul class="tntsl-parcel-numbers list-none">
                    <li v-for="parcelNumber  in getShippingLabel(4).parcel_number "
                        class="before:content-['•'] before:mr-2 before:text-gray-700">
                        <a :href="'https://www.tnt.com/express/en_gc/site/shipping-tools/track.html?searchType=con&cons=' + parcelNumber"
                           target="_blank" class="hover:text-blue-800 underline hover:no-underline">
                            {{ parcelNumber }}
                        </a>
                    </li>
                </ul>
                <div class="flex gap-2 mt-5">
                    <a
                        target="_blank"
                        :href="'/pdf/' + vendor_name + '/tnt/' +getShippingLabel(4).file_name + '?v=' + Date.now()"
                        class="text-center  gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-full"
                        type="button"
                    >
                        <font-awesome-icon :icon="['far', 'download']"/>
                        Download
                    </a>
                    <CustomButton
                        @click="store.commit('order/SET_DELETE_SHIPPING_LABEL_MODAL_VALUE', {
                                value: true,
                                id:  getShippingLabel(4).id,
                                deletingActionApi: 'delete',
                                deletingText: null,
                            });"
                        class=" text-center  gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-full"
                        type="button"
                    >
                        <font-awesome-icon :icon="['far', 'trash']"/>
                        Delete
                    </CustomButton>
                </div>
                <div class="border-b-2 my-2.5"></div>
                <div>
                    <h5>Additional Label</h5>
                    <CustomInput
                        v-model="formShippingLabel.additional_count"
                        name="name"
                        :label="'Labels count to create (each parcel weight will be ' + tnt_setting.parcel_max_weight + ' KG)'"
                        type="number"
                        placeholder="Enter  number"
                        :invalidFeedbackPlace="false"
                    />
                    <custom-button
                        @click="createAdditionalLabel(getShippingLabel(4).id, 4)"
                        type="button"
                        class="gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-full">
                        <font-awesome-icon :icon="['fas', 'plus']"/>
                        Create additional label
                    </custom-button>

                    <custom-button
                        v-if="order.adb_status && order.status === 4 && !getShippingLabel(4).adb_file_path"
                        @click="openModal"
                        type="button"
                        class="gap-2 mt-2.5 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-full">
                        <font-awesome-icon :icon="['fas', 'plus']"/>
                        Generate ADB
                    </custom-button>
                    <template v-if="getShippingLabel(4).adb_file_path">
                        <div class="flex flex-col gap-2 mt-5">
                            <a
                                target="_blank"
                                :href="getShippingLabel(4).adb_file_path + '?v=' + Date.now()"
                                class="block w-full text-center rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                                type="button"
                            >
                                <font-awesome-icon :icon="['far', 'download']"/>
                                Download ADB document
                            </a>
                            <CustomButton
                                v-if="getShippingLabel(4).adb_sent != 2"
                                @click="store.commit('order/SET_DELETE_ADB_MODAL_VALUE', {
                                    value: true,
                                    id:  order.id,
                                });"
                                class="text-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-full"
                                type="button"
                            >
                                <font-awesome-icon :icon="['far', 'trash']"/>
                                Delete ADB
                            </CustomButton>
                        </div>
                    </template>
                    <custom-button
                        v-if="getShippingLabel(4).adb_file_path && (getShippingLabel(4).adb_sent == 0)"
                        @click="sendEmail"
                        type="button"
                        class="gap-2 mt-2.5 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-full">
                        <font-awesome-icon :icon="['far', 'envelope']" />
                        Send ADB document
                    </custom-button>
                    <custom-button
                        v-if="(getShippingLabel(4).adb_sent == 1)"
                        @click="approvedCustoms"
                        type="button"
                        class="gap-2 mt-2.5 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-full">
                        <font-awesome-icon :icon="['far', 'envelope']" />
                        Mark as "Approved by Customs"
                    </custom-button>
                    <custom-button
                        v-if="(getShippingLabel(4).adb_sent == 2)"
                        :disabled="true"
                        type="button"
                        class="gap-2 mt-2.5 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-full">
                        <font-awesome-icon :icon="['far', 'envelope']" />
                        Marked as "Approved by Customs"
                    </custom-button>
                </div>
            </div>
            <div class="p-4" v-else>
                <p>Shipping Label hasn't been created yet. Please create one.</p>
                <div class="border-b-2 my-2.5"></div>
                <CustomDatePicker
                    @change="changeDate"
                    :value="dateNow"
                    placeholder="yyyy/mm/dd"
                    label="Shipping date"
                    format="Y-m-d"
                    minDate="today"
                />
                <custom-button
                    :disabled="order.number || checkShippingLabel([1,2,3], order.alternative_shipping)"
                    type="button"
                    @click="createShippingLabel(4)"
                    class="gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-full">
                    <font-awesome-icon :icon="['fas', 'plus']"/>
                    Create shipping label
                </custom-button>
            </div>
        </div>
    </div>
    <AdbModal
        :isOpen="isModalOpen"
        title="Order products"
        @close="closeModal"
        @confirm="handleConfirm"
    >
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-9 px-4">
                <div v-for="(adb, key) in products.items" :key="key"
                     class="flex flex-col relative my-6.5 min-h-[100px] border-[1.5px] shadow-default relative top-0 col-span-1 sm:col-span-1 md:col-span-1 ">
                    <button
                        v-if="key > 0"
                        type="button"
                        @click="removeProduct(key)"
                        class="hover:text-primary absolute right-2 top-1"
                        title="Delete"
                    >
                        <font-awesome-icon :icon="['fas', 'trash-can']"/>
                    </button>
                    <div class="grid grid-cols-8 gap-3 px-4 py-6">
                        <CustomInput
                            class="adb-input col-span-2"
                            v-model="adb.customs_tariff_number"
                            label="Zolltarifnummer"
                            type="text"
                            placeholder="Enter value"
                            @keyup="dynamicValidation(key, 'customs_tariff_number', {customs_tariff_number: adb.customs_tariff_number, customs_tariff_numberRules: ['required']}, 'multiselectErrors')"
                            :error="multiselectErrors[key]?.['customs_tariff_number'] ?? null"
                        />
                        <CustomInput
                            class="adb-input col-span-2"
                            v-model="adb.description_in_german"
                            label="Description in german"
                            type="text"
                            placeholder="Enter value"
                            @keyup="dynamicValidation(key, 'description_in_german', {description_in_german: adb.description_in_german, description_in_germanRules: ['required']}, 'multiselectErrors')"
                            :error="multiselectErrors[key]?.['description_in_german'] ?? null"
                        />
                        <CustomInput
                            @input="changeInfo"
                            class="adb-input col-span-1"
                            v-model="adb.price"
                            label="Price"
                            type="number"
                            placeholder="Enter value"
                            @keyup="dynamicValidation(key, 'price', {price: adb.price, priceRules: ['required']}, 'multiselectErrors')"
                            :error="multiselectErrors[key]?.['price'] ?? null"
                        />
                        <CustomInput
                            class="adb-input col-span-1"
                            :disabled="true"
                            label="Currency"
                            type="number"
                            :placeholder="order.order_currency"
                        />
                        <CustomInput
                            @input="changeInfo"
                            class="adb-input col-span-1"
                            v-model="adb.quantity"
                            label="Quantity"
                            type="number"
                            placeholder="Enter value"
                            @keyup="dynamicValidation(key, 'quantity', {quantity: adb.quantity, quantityRules: ['required']}, 'multiselectErrors')"
                            :error="multiselectErrors[key]?.['quantity'] ?? null"
                        />
                        <CustomInput
                            @input="changeInfo"
                            class="adb-input col-span-1"
                            v-model="adb.weight"
                            label="Weight"
                            type="number"
                            placeholder="Enter value"
                            @keyup="dynamicValidation(key, 'weight', {weight: adb.weight, quantityRules: ['required']}, 'multiselectErrors')"
                            :error="multiselectErrors[key]?.['weight'] ?? null"
                        />
                    </div>

                </div>
                <CustomButton
                    title="Add new section"
                    @click="addNewSection"
                    type="button"
                    class="flex items-center gap-2 rounded bg-meta-3 py-2 px-3.5 font-medium text-white hover:bg-opacity-80 ml-auto"
                >
                    <font-awesome-icon :icon="'plus'" class="size-5"/>
                    Add product
                </CustomButton>
            </div>
            <div class="col-span-3  px-4">
                <h3 class="text-lg mr-5 font-semibold ">Order info</h3>
                <h4 class="text-sm ml-auto mr-5 font-semibold">Products Total: {{ order.items_subtotal_price }} {{ order.order_currency }}</h4>
                <h4 class="text-sm ml-auto mr-5 font-semibold">Order Weight: {{ order.total_weight }}kg</h4>
                <div class="p-2 border-b"></div>
                <h3 class="text-lg mr-5 font-semibold mt-3">Lines Info</h3>
                <h4 class="text-sm ml-auto mr-5 font-semibold">Products Total: {{ product_total_price }} {{ order.order_currency }}</h4>
                <h4 class="text-sm ml-auto mr-5 font-semibold">Order Weight: {{ product_total_weight }}kg</h4>
                <div class="my-3  border-b"></div>
                <div class="flex flex-col ">
                    <label class="mb-1 block font-medium text-sm">Type of export</label>
                    <CustomSelect
                        v-model="products.type"
                        mode="single"
                        placeholder="Select type of export"
                        :options="['endgültig (Verkauf)', 'vorrübergehend (z.B. passive Veredelung)']"
                        class="py-0.5 rounded-lg border-stroke bg-transparent"
                    />
                </div>
            </div>
        </div>
    </AdbModal>
    <DeleteModal
        @fetch="fetchPageData()"
        action-variable="order/deleteShippingLabel"
        getter-variable="order/getDeleteShippingLabelModelValue"
        mutation-variable="order/SET_DELETE_SHIPPING_LABEL_MODAL_VALUE"
    />
    <DeleteModal
        @fetch="fetchPageData()"
        action-variable="order/deleteAdb"
        getter-variable="order/getDeleteAdbModelValue"
        mutation-variable="order/SET_DELETE_ADB_MODAL_VALUE"
    />
</template>
