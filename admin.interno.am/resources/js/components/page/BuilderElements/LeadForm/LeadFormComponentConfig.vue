<script setup>
import {computed, ref, toRefs, watch} from "vue";
import {useStore} from "vuex";
import CustomInput from "@components/global/CustomInput.vue";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

const store = useStore();
const auth = computed(() => store.getters['auth/getUser']);

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
    languageId: {
        type: [Number, String],
    },
});

const {modelValue} = toRefs(props);
const component = ref(modelValue.value);
const projects = ref(null);

const params = ref({
    page: 1,
    per_page: 100,
    search: '',
    status: -1,
    language_id: props.languageId,
    translation: -1,
    ordering_field: 'id',
    ordering_direction: 'asc'
});

watch(modelValue, (newVal) => {
    component.value = newVal;
    if (Object.keys(component.value.config).length === 0) {
        component.value.config = {
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

const fetchData = async () => {
    await store.dispatch('shippingCountry/fetchPageData', params.value);
    const res = await store.dispatch('leadProject/fetchPageData', params.value);
    projects.value = res.data.map(project => ({
        label: project.project_translations[0]?.name || 'No translation',
        value: project.id
    }));
};

fetchData();

const pageData = computed(() => store.getters['shippingCountry/getPageData']);
const countriesOptions = computed(() => {
    return pageData.value?.data?.map(c => {
        return {
            code: c.code,
            icon: true,
            label: c.name,
            value: c.code
        }
    });
});

const disabled = computed(() => props.isUpdate && !auth.value.user_group.permissions_by_name[props.pageTypeName][0].can_edit);

const colorOptions = [
    {value: 'bg-white', label: 'White'},
    {value: 'light-bg', label: 'Light gray'},
];

</script>

<template>
    <div class="grid grid-cols-1">
        <div class="w-full">
            <div class="grid grid-cols-7 gap-9 text-left">
                <div class="col-span-2">
                    <CustomInput
                        :disabled="disabled"
                        v-model="component.config.receivers"
                        name="receivers"
                        label="Receiver email addresses *"
                        type="email"
                        placeholder="Example: test1@epodex.com, test2@epodex.com"
                    />
                </div>
                <div class="col-span-2">
                    <CustomInput
                        :disabled="disabled"
                        v-model="component.config.subject"
                        name="subject"
                        label="Subject *"
                        type="text"
                        placeholder="Subject"
                    />
                </div>
                <div class="col-span-2">
                    <CustomSelect
                        :disabled="disabled"
                        label="Background color"
                        v-model="component.config.bg_color"
                        mode="single"
                        placeholder="Select"
                        :options="colorOptions"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                    />
                </div>
                <div class="flex items-center">
                    <CustomInput
                        :disabled="disabled"
                        v-model="component.config.send_to_visitor"
                        name="send_to_visitor"
                        label="Also send email to visitor"
                        type="checkbox"
                    />
                </div>
            </div>
            <div class="grid grid-cols-3 gap-9 text-left">
                <div>
                    <CustomInput
                        :disabled="disabled"
                        v-model="component.config.button_text"
                        name="button_text"
                        label="Submit button text *"
                        type="text"
                    />
                </div>
                <div class="col-span-2">
                    <CustomInput
                        :disabled="disabled"
                        v-model="component.config.success_url"
                        name="success_url"
                        label="Success URL"
                        type="text"
                    />
                </div>
            </div>
            <div class="text-left">
                <CustomInput
                    :disabled="disabled"
                    v-model="component.config.success_message"
                    name="success_message"
                    label="Success Message *"
                    type="text"
                />
            </div>
            <div class="text-left">
                <CustomInput
                    :disabled="disabled"
                    v-model="component.config.error_message"
                    name="error_message"
                    label="Error Message *"
                    type="text"
                />
            </div>
            <div class="grid grid-cols-2 gap-9 text-left mt-5 mb-8">
                <div class="flex flex-col">
                    <label class="block font-medium text-black mb-2.5">Email top text</label>
                    <CKEditorComponent
                        :disabled="disabled"
                        :model="component.config.email_top_text"
                        @updateValue="(value) => component.config.email_top_text = value"
                    />
                </div>
                <div class="flex flex-col">
                    <label class="block font-medium text-black mb-2.5">Email bottom text</label>
                    <CKEditorComponent
                        :disabled="disabled"
                        :model="component.config.email_bottom_text"
                        @updateValue="(value) => component.config.email_bottom_text = value"
                    />
                </div>
            </div>
            <div class="text-left">
                <CustomSelect
                    :disabled="disabled"
                    label="Projects *"
                    v-model="component.config.projects"
                    mode="tags"
                    placeholder="Select *"
                    :options="projects"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    @change="(selectedValues) => {
                        const selectedOptions = projects.filter(option => selectedValues.includes(option.value));
                        component.config.projectsData = selectedOptions.map(option => ({
                            label: option.label,
                            value: option.value
                        }));
                    }"
                />
            </div>
            <div class="text-left">
                <CustomSelect
                    :disabled="disabled"
                    label="Available countries *"
                    v-model="component.config.countries"
                    mode="tags"
                    placeholder="Select *"
                    :options="countriesOptions"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                />
            </div>
            <div class="grid grid-cols-3 gap-9 text-left">
                <CustomInput
                    :disabled="disabled"
                    v-model="component.config.project_label"
                    name="project_label"
                    label="Project label *"
                    type="text"
                />
                <CustomInput
                    :disabled="disabled"
                    v-model="component.config.area_size_label"
                    name="area_size_label"
                    label="Area size label *"
                    type="text"
                />
                <CustomInput
                    :disabled="disabled"
                    v-model="component.config.start_date_label"
                    name="start_date_label"
                    label="Start date label *"
                    type="text"
                />
            </div>
            <div class="grid grid-cols-3 gap-9 text-left">
                <CustomInput
                    :disabled="disabled"
                    v-model="component.config.description_label"
                    name="description_label"
                    label="Description label *"
                    type="text"
                />
                <CustomInput
                    :disabled="disabled"
                    v-model="component.config.full_name_label"
                    name="full_name_label"
                    label="Full name label *"
                    type="text"
                />
                <CustomInput
                    :disabled="disabled"
                    v-model="component.config.country_label"
                    name="country_label"
                    label="Country label *"
                    type="text"
                />
            </div>
            <div class="grid grid-cols-3 gap-9 text-left">
                <CustomInput
                    :disabled="disabled"
                    v-model="component.config.email_label"
                    name="email_label"
                    label="Email label *"
                    type="text"
                />
                <CustomInput
                    :disabled="disabled"
                    v-model="component.config.phone_label"
                    name="phone_label"
                    label="Phone label *"
                    type="text"
                />
                <CustomInput
                    :disabled="disabled"
                    v-model="component.config.zip_code_label"
                    name="zip_code_label"
                    label="Post/zip code label *"
                    type="text"
                />
            </div>
        </div>
    </div>
</template>
