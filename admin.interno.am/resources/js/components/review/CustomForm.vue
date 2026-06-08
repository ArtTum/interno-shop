<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import CustomTextarea from "@components/global/CustomTextarea.vue";
import Switch from "@components/global/Switch.vue";

import {computed, ref, toRefs, watch} from "vue";
import {validate} from "@validation/customValidation.js";
import {useStore} from "vuex";
import {debounce} from "lodash";
import CustomDatePicker from "@components/global/CustomDatePicker.vue";

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

store.dispatch('review/fetchParams', {id: form.value.id});
const params = computed(() => store.getters['review/getParams']);

const emits = defineEmits([
    'update:modelValue',
    'submit',
    'handleFileUpload'
]);

watch(modelValue, (newVal) => {
    form.value = newVal;
});

watch(form.value, (newVal) => {
    emits('update:modelValue', newVal);
});

const productsOptions = ref([]);
const usersOptions = ref([]);

const autocompleteRequest = async (alreadySelectedId, term = '') => {
    productsOptions.value = await store.dispatch('product/autocomplete', {
        field: 'name',
        term,
        alreadySelectIds: [alreadySelectedId],
    });
}

const productsAutocomplete = debounce(async (term) => {
    productsOptions.value = await store.dispatch('product/autocomplete', {
        field: 'name',
        term,
        alreadySelectIds: [form.value.product_id],
    });
}, 200);

const usersAutocomplete = debounce(async (term) => {
    usersOptions.value = await store.dispatch('user/autocomplete', {
        field: 'name',
        term,
        alreadySelectIds: [form.value.user_id],
    });
}, 200);

const usersAutocompleteRequest = async (alreadySelectedId, term = '') => {
    usersOptions.value = await store.dispatch('user/autocomplete', {
        field: 'name',
        term,
        alreadySelectIds: [alreadySelectedId],
    });
}

const changeUser = async () => {
    let user = await store.dispatch('user/fetchByField', {id: form.value.user_id});
    form.value.name = user.name + ' ' + user.last_name;
    form.value.email = user.email;
    form.value.errors = validate(form);
};

if (props.emitAction === 'update') {
    autocompleteRequest(form.value.product_id);
    usersAutocompleteRequest(form.value.user_id);
} else {
    autocompleteRequest([]);
    usersAutocompleteRequest([]);
}

const auth = computed(() => store.getters['auth/getUser']);

const handleFileUpload = (event) => {
    const files = event.target.files;
    emits('handleFileUpload', files);  // Emit event with the files
};

const removeImages = (id, key) => {
    if (id) {
        form.value.removeImages.push(id);
        form.value.images.splice(key, 1)
    }
}
const vendorKey = localStorage.getItem('vendor_key');
</script>

<template>
    <form @submit.prevent="emits('submit')">
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 gap-9 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>
        <div v-if="form.is_uploaded" class="bg-amber-200 px-6.5 py-2.5"> This review is uploaded by file</div>
        <div class="grid grid-cols-4 gap-9">
            <div class="flex flex-col p-6.5">
                <div class="mb-4">
                    <CustomSelect
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.reviews[0].can_edit"
                        @search-change="usersAutocomplete"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                        v-model="form.user_id"
                        :searchable="true"
                        label="User "
                        mode="single"
                        placeholder="Select user"
                        :options="usersOptions"
                        :can-clear="true"
                        :need-autocomplete="true"
                        @update:modelValue="form.errors = validate(form); changeUser()"
                        :error="form.errors['user_id']"
                    />
                </div>
            </div>
            <div class="flex flex-col p-6.5 ">
                <CustomInput
                    :disabled="!!form.user_id || (emitAction === 'update' && !auth.user_group.permissions_by_name.reviews[0].can_edit)"
                    v-model="form.name"
                    name="name"
                    label="Name *"
                    type="text"
                    placeholder="Enter name"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['name']"
                />
            </div>
            <div class="flex flex-col p-6.5 ">
                <CustomInput
                    :disabled="!!form.user_id || (emitAction === 'update' && !auth.user_group.permissions_by_name.reviews[0].can_edit)"
                    v-model="form.email"
                    name="email"
                    label="E-mail *"
                    type="email"
                    placeholder="Enter email"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['email']"
                />
            </div>
            <div class="flex flex-col p-6.5 ">
                <CustomDatePicker
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.reviews[0].can_edit"
                    placeholder="yyyy/mm/dd"
                    label="Created date"
                    format="Y-m-d"
                    v-model="form.created_at"
                />
            </div>
        </div>
        <div class="grid grid-cols-7 gap-9">
            <div class="flex flex-col p-6.5 col-span-2">
                <div class="mb-4">
                    <CustomSelect
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.reviews[0].can_edit"
                        @search-change="productsAutocomplete"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                        v-model="form.product_id"
                        :searchable="true"
                        label="Product *"
                        placeholder="No selected products"
                        :options="productsOptions"
                        :can-clear="true"
                        :need-autocomplete="true"
                        @update:modelValue="form.errors = validate(form)"
                        :error="form.errors['product_id']"
                    />
                </div>
            </div>
            <div class="flex flex-col p-6.5 col-span-2 ">
                <div class="mb-4">
                    <CustomSelect
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.reviews[0].can_edit"
                        label="Rating *"
                        v-model="form.rating"
                        mode="single"
                        placeholder="Select rating"
                        :options="['1.0', '2.0', '3.0', '4.0', '5.0']"
                        :searchable="false"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                        @update:modelValue="form.errors = validate(form)"
                        :error="form.errors['rating']"
                    />
                </div>
            </div>
            <div class="flex flex-col p-6.5 col-span-2 ">
                <div class="mb-4">
                    <CustomSelect
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.reviews[0].can_edit"
                        v-model="form.country_code"
                        label="Origin country *"
                        :options="params.countries"
                        mode="single"
                        :searchable="true"
                        :canClear="false"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                        :close-on-select="true"
                        placeholder="Select country"
                        @update:modelValue="form.errors = validate(form)"
                        :error="form.errors['country_code']"
                    />
                </div>
            </div>
            <div class="flex flex-col p-6.5 ">
                <Switch
                    @change="(value) => {
                       form.status = value;
                    }"
                    class="w-fit"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.reviews[0].can_edit"
                    :value="form.status"
                    id="status"
                    label="Status"
                />
            </div>
        </div>
        <template v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.reviews[0].can_edit">
            <div class="flex flex-col pl-6.5 pr-6.5 mb-6.5">
                <div class="pl-6.5 border border-stroke rounded">
                    <div class="mb-6 mt-6">
                        <input
                            @change="handleFileUpload"
                            id="fileUpload"
                            type="file"
                            accept="image/*,video/*"
                            multiple
                            class="cursor-pointer rounded border-[1.5px] border-stroke bg-transparent font-medium outline-none transition file:mr-5 file:border-collapse file:cursor-pointer file:border-0 file:border-r file:border-solid file:border-stroke file:bg-whiter file:py-3 file:px-5 file:hover:bg-primary file:hover:bg-opacity-10 focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter"
                        />

                        <div v-if="form.images.length" class="mt-4">
                            <div class="flex flex-wrap gap-4 menu-container">
                                <div v-for="(file, index) in form.images" class="flex gap-5.5 menu">
                                    <div
                                        class="menu-item relative w-40 border border-stroke rounded p-2 flex justify-center items-center mb-6.5">
                                        <button
                                            type="button"
                                            @click="removeImages(file.id, index)"
                                            class="absolute z-1  top-0.5 right-0.5 text-red hover:text-gray-700 focus:outline-none"
                                        >
                                            <span class="sr-only">Close</span>
                                            <font-awesome-icon :icon="['fas', 'xmark']" class="size-6"/>
                                        </button>
                                        <div v-if="file.type === 0">
                                            <img :src="'/uploads/' + vendorKey + '/images/thumbnail/' +  file.path " class="w-full"
                                                 alt="Image">
                                        </div>
                                        <div v-if="file.type === 1">
                                            <video :src="file.path" controls
                                                   class="w-full h-24 object-cover rounded-md"></video>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5">
                <CustomTextarea
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.reviews[0].can_edit"
                    label="Text *"
                    name="text"
                    v-model="form.text"
                    placeholder="Enter text"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['text']"
                />
            </div>
        </div>
        <!-- Submit and Delete Buttons -->
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.reviews[0].can_delete">
                        <CustomButton
                            v-if="emitAction === 'update'"
                            @click="store.commit('review/SET_DELETE_MODAL_VALUE', {
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

                    <template v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.reviews[0].can_edit">
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
