<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";

import {computed, ref, toRefs, watch} from "vue";
import {useStore} from "vuex";

const store = useStore()

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    pages: {
        type: [Array],
    },
});

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
const vendorKey = localStorage.getItem('vendor_key');
const authenticateWithTrustpilot = () => {
    const redirectUri = form.value.redirect_uri + vendorKey;
    const authUrl = `https://authenticate.trustpilot.com/?client_id=${form.value.client_id}&redirect_uri=${redirectUri}&response_type=code`;
    window.location.href = authUrl;
};

const auth = computed(() => store.getters['auth/getUser']);
import {validate} from "@validation/customValidation.js";
import CustomTextarea from "@components/global/CustomTextarea.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

const searchQuery = ref('');
const businessUnits = ref([]);

const search = async () => {
    businessUnits.value = [];
    try {
        const response = await store.dispatch('trustpilot/searchBusinessUnitID', {
            search: searchQuery.value,
        });
        businessUnits.value = response.data.businessUnits || [];
    } catch (errors) {
        form.errors = errors
    }
};
const changeBusinessUnitId = (id) => {
    form.value.bs_id = id;
};

</script>

<template>
    <form @submit.prevent="emits('submit')">
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 gap-9 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>
        <div class="grid grid-cols-2 gap-9">
            <div class="flex flex-col p-6.5">
                <CustomInput
                    :disabled="!auth.user_group.permissions_by_name.trustpilot_settings[0].can_edit"
                    v-model="form.client_id"
                    name="client_id"
                    label="API key *"
                    type="text"
                    placeholder="Enter API key"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['client_id']"
                />
                <CustomInput
                    :disabled="!auth.user_group.permissions_by_name.trustpilot_settings[0].can_edit"
                    v-model="form.secret"
                    name="secret"
                    label="Secret *"
                    type="text"
                    placeholder="Enter secret"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['secret']"
                />
                <CustomInput
                    :disabled="true"
                    v-model="form.bs_id"
                    name="bs_id"
                    label="Business unit ID"
                    type="text"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['bs_id']"
                />
                <CustomInput
                    :disabled="!auth.user_group.permissions_by_name.trustpilot_settings[0].can_edit"
                    v-model="form.excellent_text"
                    name="excellent_text"
                    label="Excellent text"
                    type="text"
                    placeholder="Enter excellent text"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['excellent_text']"
                />
                <CustomSelect
                    label="Page"
                    v-model="form.page_id"
                    mode="single"
                    placeholder="Select page"
                    :disabled="!auth.user_group.permissions_by_name.trustpilot_settings[0].can_edit"
                    :options="props.pages"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    :searchable="true"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['page_id']"
                />
                <CustomTextarea
                    :disabled="!auth.user_group.permissions_by_name.trustpilot_settings[0].can_edit"
                    v-model="form.excluded_skus"
                    name="excluded_skus"
                    label="Excluded skus"
                    type="text"
                    placeholder="Enter excluded skus"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['excluded_skus']"
                />
            </div>
            <div class="flex flex-col px-5.5  py-5">

                <form @submit.prevent="search">
                    <div class="flex mt-0">
                        <CustomInput
                            :disabled="!auth.user_group.permissions_by_name.socials_settings[0].can_edit"
                            class="w-full mt-1 custom-tooltip"
                            v-model="searchQuery"
                            name="search_query"
                            label="Search business unit ID"
                            type="text"
                            placeholder="Enter search"
                            :tooltip="true"
                        />
                        <CustomButton
                            :disabled="!searchQuery || !form.access_token"
                            type="submit"
                            v-model="searchQuery"
                            class="flex h-fit items-center gap-2 rounded-tr rounded-br bg-primary mt-auto mb-auto py-4.5 px-4.5 font-medium text-white hover:bg-opacity-80"
                        >
                            <font-awesome-icon :icon="['far', 'search']"/>
                            Search
                        </CustomButton>

                    </div>
                    <ul v-if="businessUnits.length">
                        <li class="cursor-pointer"
                            @click="changeBusinessUnitId(unit.id)" v-for="unit in businessUnits" :key="unit.id">
                            {{ unit.displayName }}
                        </li>
                    </ul>
                </form>
            </div>
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.socials_settings[0].can_edit">
                        <CustomButton
                            @click="emits('cancel')"
                            class="block w-full rounded border border-stroke bg-gray p-3 text-center font-medium text-black hover:bg-opacity-60"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'arrow-rotate-left']"/>
                            Cancel
                        </CustomButton>
                        <CustomButton
                            v-if="form.client_id && form.secret"
                            @click="authenticateWithTrustpilot"
                            class="flex items-center gap-2 rounded border-meta-1 bg-meta-10 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            {{ form.access_token ? 'Reconnect' : 'Connect' }}
                        </CustomButton>
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
