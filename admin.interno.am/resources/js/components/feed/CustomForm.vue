<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import draggable from 'vuedraggable';
import {computed, ref, toRefs, watch} from "vue";
import {validate} from "@validation/customValidation.js";
import {useStore} from "vuex";
import Switch from "@components/global/Switch.vue";
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

const addNewSection = () => {

    form.value.feeds.push({
        id: '',
        feed_type_id: form.value.id,
        column_name: '',
        field_type: '',
        sku_prefix: '',
        custom_key: '',
        custom_value: '',
        in_stock_value: '',
        out_of_stock_value: '',
        chars_limit: '',
        include_sub_name: false,
        square_image_ratio: false,
    });
}

const removeFeed = (key) => {
    form.value.feeds.splice(key, 1);
}

const auth = computed(() => store.getters['auth/getUser']);

const dragOptions = ref({
    animation: 200,
    group: 'items',
    disabled: false,
    ghostClass: 'ghost',
});

const emitter = (event) => {
    console.log('Updated');
};

store.dispatch('feed/fetchParams');
const params = computed(() => store.getters['feed/getParams']);

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
            <div class="flex flex-col p-6.5 pb-0">
                <CustomSelect
                    label="Languages"
                    v-model="form.language_id"
                    mode="single"
                    placeholder="Select languages"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.feeds[0].can_edit"
                    :options="params.languages"
                    :searchable="true"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['language_id']"
                />
            </div>
            <div class="flex flex-col p-6.5 pb-0">
                <Switch
                    @change="(value) => {
                                            form.variants_export_into_feed = value;
                                        }"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.feeds[0].can_edit"
                    :value="form.variants_export_into_feed"
                    id="Variants_export_into_feed"
                    label="Variants export into feed"
                />
            </div>
        </div>
        <div class="grid grid-cols-2 gap-9">
            <div class="flex flex-col px-6.5">
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.feeds[0].can_edit"
                    v-model="form.name"
                    name="name"
                    label="Name *"
                    type="text"
                    placeholder="Enter name"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['name']"
                />
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.feeds[0].can_edit"
                    v-model="form.separator"
                    name="separator"
                    label="CSV Separator (Default is ; (Without spaces))"
                    type="text"
                    placeholder="Enter separator"
                    @keyup="form.errors = validate(form)"
                />
            </div>
            <div class="flex flex-col px-6.5">
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.feeds[0].can_edit"
                    v-model="form.slug"
                    name="slug"
                    label="Slug for URL *"
                    type="text"
                    placeholder="Enter slug"
                    :error="form.errors['slug']"
                    @keyup="form.errors = validate(form)"
                />
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.feeds[0].can_edit"
                    v-model="form.price_separator"
                    name="price_separator"
                    label="Price Separator Default is . (Without spaces)"
                    type="text"
                    placeholder="Enter price separator"
                />
            </div>
        </div>
        <div class="flex flex-col px-6.5 pb-0">
            <draggable
                v-bind="dragOptions"
                tag="div"
                class="item-container"
                :list="form.feeds"
                @end="emitter"
                item-key="id"
            >
                <template #item="{ element, index }">
                    <div
                        :key="element.id"
                        class="flex flex-col relative my-6.5 min-h-[100px] border-[1.5px] shadow-default relative top-0 col-span-1 sm:col-span-1 md:col-span-1"
                    >
                        <div class="cursor-grab absolute left-2 top-1 text-gray-500 hover:text-gray-700"
                             style="cursor: grab;">
                            <font-awesome-icon :icon="['fas', 'grip-lines']"/>
                        </div>
                        <button
                            type="button"
                            @click="removeFeed(index)"
                            class="hover:text-primary absolute right-2 top-1"
                            title="Delete"
                        >
                            <font-awesome-icon :icon="['fas', 'trash-can']"/>
                        </button>

                        <div class="grid grid-cols-2 gap-9 p-6.5">
                            <div>
                                <CustomInput
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.feeds[0].can_edit"
                                    v-model="element.column_name"
                                    name="column_name"
                                    label="Column Name"
                                    placeholder="Enter column name"
                                    type="text"
                                />
                                <CustomInput
                                    v-if="element.field_type === 'sku'"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.feeds[0].can_edit"
                                    v-model="element.sku_prefix"
                                    name="sku_prefix"
                                    label="SKU prefix"
                                    placeholder="Enter sku prefix"
                                    type="text"
                                />
                                <CustomInput
                                    v-if="element.field_type === 'availability'"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.feeds[0].can_edit"
                                    v-model="element.in_stock_value"
                                    name="in_stock_value"
                                    label='In stock value - Default is "instock"'
                                    placeholder="Enter in-stock value"
                                    type="text"
                                />
                                <CustomInput
                                    v-if="element.field_type === 'custom_field'"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.feeds[0].can_edit"
                                    v-model="element.custom_key"
                                    name="custom_key"
                                    label='Custom field key'
                                    placeholder="Enter custom field key"
                                    type="text"
                                />
                                <CustomInput
                                    v-if="element.field_type === 'custom_value'"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.feeds[0].can_edit"
                                    v-model="element.custom_value"
                                    name="custom_value"
                                    label='Custom value'
                                    placeholder="Enter custom value"
                                    type="text"
                                />
                                <div class="flex gap-8 mb-6"
                                     v-if="element.field_type === 'title'"
                                >
                                    <Switch
                                        @change="(value) => {
                                element.include_sub_name = value;
                            }"
                                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.feeds[0].can_edit"
                                        :value="element.include_sub_name"
                                        :id="'include_sub_name-' + index"
                                        label='Include subname'
                                    />
                                </div>
                                <div
                                    v-if="element.field_type === 'image'"
                                    class="flex gap-8 mb-6">
                                    <Switch
                                        @change="(value) => {
                                element.square_image_ratio = value;
                            }"
                                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.feeds[0].can_edit"
                                        :value="element.square_image_ratio"
                                        :id="'square_image_ratio-' + index"
                                        label='Square image ratio'
                                    />
                                </div>
                            </div>
                            <div>
                                <CustomSelect
                                    class="py-2 mb-4 rounded-lg border-stroke bg-transparent"
                                    v-model="element.field_type"
                                    mode="single"
                                    label="Type"
                                    placeholder="Select type"
                                    :options='[
                            { value: "sku", label: "SKU",},
                            { value: "title", label: "Title"},
                            { value: "short_description", label: "Short description"},
                            { value: "url", label: "URL"},
                            { value: "category", label: "Category"},
                            { value: "image", label: "Image"},
                            { value: "gallery", label: "Gallery images"},
                            { value: "availability", label: "Availability"},
                            { value: "discounted", label: "Sale price"},
                            { value: "price", label: "Price"},
                            { value: "custom_field", label: "Custom field"},
                            { value: "custom_value", label: "Custom value"},
                        ]'
                                    :show-labels="true"
                                    :close-on-select="true"
                                    :canClear="false"
                                    :searchable="true"
                                    @update:modelValue="form.errors = validate(form)"
                                    :error="form.errors['field_type'] ?? null"
                                />
                                <CustomInput
                                    v-if="element.field_type === 'title' || element.field_type === 'description' || element.field_type === 'gallery'"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.feeds[0].can_edit"
                                    v-model="element.chars_limit"
                                    name="chars_limit"
                                    label="Characters limit"
                                    placeholder="Enter chars limit"
                                    type="number"
                                />
                                <CustomInput
                                    v-if="element.field_type === 'availability'"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.feeds[0].can_edit"
                                    v-model="element.out_of_stock_value"
                                    name="out_of_stock_value"
                                    label='Out of stock Value - Default is "outofstock"'
                                    placeholder="Enter out-of-stock value"
                                    type="text"
                                />
                            </div>
                        </div>
                    </div>
                </template>
            </draggable>

            <CustomButton
                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.feeds[0].can_edit"
                title="Add new section"
                @click="addNewSection"
                type="button"
                class="flex items-center gap-2 rounded bg-meta-3 py-2 px-3.5 font-medium text-white hover:bg-opacity-80 mr-auto ml-auto mb-5"
            >
                <font-awesome-icon :icon="'plus'" class="size-5"/>
                Add feed
            </CustomButton>
        </div>

        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template
                        v-if="emitAction === 'update'">
                        <router-link
                            :to="`/tools/feeds/clone/${form.id}`"
                            class="flex items-center gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                        >
                            <font-awesome-icon :icon="['fasr', 'clone']"/>
                            Clone
                        </router-link>
                    </template>
                    <template v-if="auth.user_group.permissions_by_name.feeds[0].can_delete">
                        <CustomButton
                            v-if="emitAction === 'update'"
                            @click="store.commit('feed/SET_DELETE_MODAL_VALUE', {
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

                    <template v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.feeds[0].can_edit">
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
