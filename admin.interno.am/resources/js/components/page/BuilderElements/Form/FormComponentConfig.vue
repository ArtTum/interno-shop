<script setup>

import {computed, ref, toRefs, watch} from "vue";
import CustomButton from "@components/global/CustomButton.vue";
import CustomInput from "@components/global/CustomInput.vue";

import {useStore} from "vuex";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import FormComponentFieldsDragAndDrop from "@components/page/BuilderElements/Form/FormComponentFieldsDragAndDrop.vue";

const store = useStore();

const auth = computed(() => store.getters['auth/getUser']);

const newItem = ref(null);
const emits = defineEmits([
    'update:modelValue',
    'save'
]);

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    isUpdate: {
        type: Boolean,
        default: false
    },
    params: {
        type: Object,
        default: {}
    },
    pageTypeName: {
        type: String,
    },
});

const {modelValue} = toRefs(props);
const component = ref(modelValue.value);

watch(modelValue, (newVal) => {
    component.value = newVal;

    if (Object.keys(component.value.config).length === 0) {
        component.value.config = {
            generate_contract: false,
            send_to_visitor: true,
            bg_color: 'bg-white',
        }
    }
}, {immediate: true});
watch(
    () => component.value,
    (newVal) => {
        emits('update:modelValue', newVal);
    },
    {deep: true}
);

const activeTab = ref('general');

const tabsRoutes = [
    {key: 'general', title: 'General *', icon: ['far', 'gear']},
    {key: 'fields', title: 'Fields', icon: ['far', 'align-justify']},
    {key: 'contract_settings', title: 'Contact settings *', icon: ['far', 'file-contract']},
];

const colorOptions = [
    {value: 'bg-white', label: 'White'},
    {value: 'light-bg', label: 'Light gray'},
];

</script>

<template>
    <div class="grid grid-cols-1">
        <div class="w-full">
            <div class="mb-6 flex flex-wrap gap-5 border-b border-stroke">
                <template
                    :key="key"
                    v-for="(tabRoute, key) in tabsRoutes"
                >
                    <router-link
                        v-if="tabRoute.key !== 'contract_settings' || component.config.generate_contract"
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
            <div v-if="activeTab === 'general'">
                <div class="grid grid-cols-3 gap-9 text-left mt-5">
                    <div class="flex flex-col gap-9">
                        <CustomInput
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            v-model="component.config.title"
                            name="title"
                            label="Title *"
                            type="text"
                            placeholder="Title"
                        />
                    </div>
                    <div class="flex flex-col gap-9">
                        <CustomInput
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            v-model="component.config.subject"
                            name="subject"
                            label="Subject *"
                            type="text"
                            placeholder="Subject"
                        />
                    </div>
                    <div class="flex flex-col gap-9">
                        <CustomSelect
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            label="Background color"
                            v-model="component.config.bg_color"
                            mode="single"
                            placeholder="Select"
                            :options="colorOptions"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-6 gap-9 text-left mt-5">
                    <div class="flex flex-col gap-9 col-span-2">
                        <CustomInput
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            v-model="component.config.receivers"
                            name="receivers"
                            label="Receiver email addresses *"
                            type="email"
                            placeholder="Example: test1@epodex.com, test2@epodex.com"
                        />
                    </div>
                    <div class="flex flex-col gap-9 col-span-2">
                        <CustomInput
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            v-model="component.config.button_text"
                            name="button_text"
                            label="Button text *"
                            type="text"
                            placeholder="Submit button text"
                        />
                    </div>
                    <div class="flex flex-col gap-9 col-span-1 justify-center">
                        <CustomInput
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            v-model="component.config.generate_contract"
                            name="generate_contract"
                            label="Generate contract"
                            type="checkbox"
                        />
                    </div>
                    <div class="flex flex-col gap-9 col-span-1 justify-center">
                        <CustomInput
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            v-model="component.config.send_to_visitor"
                            name="send_to_visitor"
                            label="Also send email to visitor"
                            type="checkbox"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-9 text-left mt-5">
                    <div class="flex flex-col">
                        <label class="block font-medium text-black mb-2.5">Form top text</label>
                        <CKEditorComponent
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            :model="component.config.form_top_text"
                            @updateValue="(value) => {
                                component.config.form_top_text = value
                            }"
                        />
                    </div>
                    <div class="flex flex-col">
                        <label class="block font-medium text-black mb-2.5">Form bottom text</label>
                        <CKEditorComponent
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            :model="component.config.form_bottom_text"
                            @updateValue="(value) => {
                                component.config.form_bottom_text = value
                            }"
                        />
                    </div>
                </div>
            </div>
            <div v-if="activeTab === 'fields'">
                <div class="flex justify-center">
                    <div>
                        <template v-if="!newItem">
                            <CustomButton
                                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                @click="newItem = {placeholder: null, label: null, field_type: 'text', required: false, use_as_name: true, make_as_full_width: false}"
                                title="Add new field"
                                type="button"
                                class="h-[45px] flex items-center gap-2 rounded bg-meta-3 px-3.5 mt-5 font-medium text-white hover:bg-opacity-80 ml-auto"
                            >
                                <font-awesome-icon :icon="'plus'" class="size-5"/>
                                New field
                            </CustomButton>
                        </template>
                        <template v-else>
                            <div class="flex">
                                <CustomButton
                                    @click="newItem = null"
                                    class="block w-full rounded border border-stroke bg-gray px-4.5 py-2.5 text-center font-medium text-black hover:bg-opacity-60"
                                    type="button"
                                >
                                    <font-awesome-icon :icon="['far', 'arrow-rotate-left']"/>
                                    Cancel
                                </CustomButton>

                                <template v-if="!newItem.label || !newItem.placeholder">
                                    <CustomButton
                                        disabled
                                        class="flex items-center gap-2 ml-3 rounded bg-primary px-4.5 py-2.5  font-medium text-white hover:bg-opacity-80"
                                        type="button"
                                    >
                                        <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                                        Save
                                    </CustomButton>
                                </template>
                                <template v-else>
                                    <CustomButton
                                        @click="emits('save', newItem), newItem = null"
                                        class="flex items-center gap-2 ml-3 rounded bg-primary px-4.5 py-2.5  font-medium text-white hover:bg-opacity-80"
                                        type="button"
                                    >
                                        <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                                        Save
                                    </CustomButton>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
                <template v-if="newItem">
                    <div class="grid grid-cols-3 gap-9 text-left mt-5 px-6.5">
                        <div class="flex flex-col gap-9">
                            <CustomSelect
                                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                label="Field type *"
                                v-model="newItem.field_type"
                                mode="single"
                                placeholder="Select *"
                                :options="params.fieldTypes"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                            />
                        </div>
                        <div class="flex flex-col gap-9">
                            <CustomInput
                                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                v-model="newItem.label"
                                name="label"
                                label="Label *"
                                type="text"
                                placeholder="Label *"
                            />
                        </div>
                        <div class="flex flex-col gap-9">
                            <CustomInput
                                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                v-model="newItem.placeholder"
                                name="placeholder"
                                label="Placeholder *"
                                type="text"
                                placeholder="Placeholder *"
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-9 text-left mt-5 px-6.5">
                        <div class="flex flex-col gap-9">
                            <CustomInput
                                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                v-model="newItem.required"
                                name="required"
                                label="Field is required"
                                type="checkbox"
                            />
                        </div>
                        <div class="flex flex-col gap-9">
                            <CustomInput
                                :disabled="isUpdate && !auth.user_group.permissions_by_name.pages[0].can_edit"
                                v-model="newItem.use_as_name"
                                name="use_as_name"
                                label="Us as name"
                                type="checkbox"
                            />
                        </div>
                        <div class="flex flex-col gap-9">
                            <CustomInput
                                :disabled="isUpdate && !auth.user_group.permissions_by_name.pages[0].can_edit"
                                v-model="newItem.make_as_full_width"
                                name="make_as_full_width"
                                label="Make the field full width."
                                type="checkbox"
                            />
                        </div>
                    </div>
                </template>
                <template v-if="component.items">
                    <FormComponentFieldsDragAndDrop
                        :params="params"
                        class="col-8"
                        v-model="component.items"
                    />
                </template>
            </div>
            <div v-if="activeTab === 'contract_settings'">
                <div class="grid grid-cols-2 gap-9 text-left mt-5">
                    <div class="flex flex-col gap-9">
                        <CustomInput
                            :disabled="isUpdate && !auth.user_group.permissions_by_name.pages[0].can_edit"
                            v-model="component.config.pdf_header"
                            name="pdf_header"
                            label="PDF header"
                            type="text"
                            placeholder="PDF header"
                        />
                    </div>
                    <div class="flex flex-col">
                        <label class="block font-medium text-black mb-2.5">PDF footer text</label>
                        <CKEditorComponent
                            :disabled="isUpdate && !auth.user_group.permissions_by_name.pages[0].can_edit"
                            :model="component.config.pdf_footer_text"
                            @updateValue="(value) => {
                                component.config.pdf_footer_text = value
                            }"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-9 text-left mt-5">
                    <div class="flex flex-col">
                        <label class="block font-medium text-black mb-2.5">PDF top text</label>
                        <CKEditorComponent
                            :disabled="isUpdate && !auth.user_group.permissions_by_name.pages[0].can_edit"
                            :model="component.config.pdf_top_text"
                            @updateValue="(value) => {
                                component.config.pdf_top_text = value
                            }"
                        />
                    </div>
                    <div class="flex flex-col">
                        <label class="block font-medium text-black mb-2.5">PDF bottom text</label>
                        <CKEditorComponent
                            :disabled="isUpdate && !auth.user_group.permissions_by_name.pages[0].can_edit"
                            :model="component.config.pdf_bottom_text"
                            @updateValue="(value) => {
                                component.config.pdf_bottom_text = value
                            }"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
