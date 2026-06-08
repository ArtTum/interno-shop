<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import PageNestedDragAndDrop from "@components/page/PageNestedDragAndDrop.vue";
import PopupWithSlot from "@components/global/PopupWithSlot.vue";
import SliderComponentConfig from "@components/page/SliderComponentConfig.vue";
import FAQComponentConfig from "@components/page/FAQComponentConfig.vue";
import CustomLinkBoxComponentConfig from "@components/page/CustomLinkBoxComponentConfig.vue";
import CustomLinkBox2ComponentConfig from "@components/page/CustomLinkBox2ComponentConfig.vue";
import ImageComponentConfig from "@components/page/BuilderElements/Image/ImageComponentConfig.vue";
import VideoComponentConfig from "@components/page/BuilderElements/Video/VideoComponentConfig.vue";
import RelationBox1ComponentConfig from "@components/page/BuilderElements/RelationBox/RelationBox1ComponentConfig.vue";
import GalleryComponentConfig from "@components/page/BuilderElements/Image/GalleryComponentConfig.vue";
import CategoryComponentConfig from "@components/page/BuilderElements/Category/CategoryComponentConfig.vue";
import RelationBox2ComponentConfig from "@components/page/BuilderElements/RelationBox/RelationBox2ComponentConfig.vue";
import RelationBox3ComponentConfig from "@components/page/BuilderElements/RelationBox/RelationBox3ComponentConfig.vue";
import EditorComponentConfig from "@components/page/BuilderElements/Editor/EditorComponentConfig.vue";
import OfferComponentConfig from "@components/page/BuilderElements/Offer/OfferComponentConfig.vue";
import EventComponentConfig from "@components/page/BuilderElements/Event/EventComponentConfig.vue";
import EmployeeComponentConfig from "@components/page/BuilderElements/Employee/EmployeeComponentConfig.vue";
import Switch from "@components/global/Switch.vue";
import FormComponentConfig from "@components/page/BuilderElements/Form/FormComponentConfig.vue";
import CustomMediaList from "@components/media/CustomMediaList.vue";
import CustomDatePicker from "@components/global/CustomDatePicker.vue";
import CategoryBlogComponentConfig from "@components/page/BuilderElements/CategoryBlog/CategoryBlogComponentConfig.vue";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";
import APlusContentComponentConfig
    from "@components/page/BuilderElements/APlusContents/APlusContentComponentConfig.vue";
import CalculatorComponentConfig from "@components/page/BuilderElements/Calculator/CalculatorComponentConfig.vue";
import TestimonialsComponentConfig from "@components/page/BuilderElements/Testimonials/TestimonialsComponentConfig.vue";
import {computed, ref, toRefs, watch} from "vue";
import {validate} from "@validation/customValidation.js";
import {useStore} from "vuex";
import ResultNumbersComponentConfig
    from "@components/page/BuilderElements/ResultNumbers/ResultNumbersComponentConfig.vue";
import USPListComponentConfig from "@components/page/BuilderElements/USPList/USPListComponentConfig.vue";
import TrustpilotComponentConfig from "@components/page/BuilderElements/Trustpilot/TrustpilotComponentConfig.vue";
import BuilderButtonComponent from "@components/page/BuilderElements/Global/BuilderButtonComponent.vue";
import LeadFormComponentConfig from "@components/page/BuilderElements/LeadForm/LeadFormComponentConfig.vue";
import BulletPointsComponentConfig from "@components/page/BuilderElements/BulletPoints/BulletPointsComponentConfig.vue";
import AllProductsComponentConfig
    from "@components/page/BuilderElements/AllProductsTable/AllProductsComponentConfig.vue";
import B2BQuickCart from "@components/page/BuilderElements/B2BQuickCart/B2BQuickCart.vue";
import Newsletter from "@components/page/BuilderElements/Newsletter/Newsletter.vue";
import InvoiceRequestFormComponentConfig
    from "@components/page/BuilderElements/Form/InvoiceRequestFormComponentConfig.vue";
import VideoSliderComponentConfig from "@components/page/BuilderElements/VideoSlider/VideoSliderComponentConfig.vue";
import TrackingFormComponentConfig from "@components/page/BuilderElements/Tracking/TrackingFormComponentConfig.vue";

const store = useStore()

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    isUpdate: {
        type: Boolean,
        default: false
    },
    pageType: {
        type: Number,
    },
    pageTypeName: {
        type: String,
    },
    pageTypeKey: {
        type: String,
    },
});

const {modelValue} = toRefs(props);
const form = ref(modelValue.value);

const emits = defineEmits([
    'update:modelValue',
    'submit'
])

watch(modelValue, (newVal) => {
    form.value = newVal;
});

watch(form.value, (newVal) => {
    emits('update:modelValue', newVal);
});

const generatePath = async () => {
    if (!form.value.parent_id) {
        form.value.path = null;
        form.value.breadcrumb = null;
        return;
    }
    let res = await store.dispatch(`${props.pageTypeKey}/generatePath`, {id: form.value.parent_id});
    form.value.path = res.path;
    form.value.breadcrumb = res.breadcrumb;
}

const spaceOptions = [
    {value: '', label: 'None'},
    {value: 'sp-xs', label: 'XS'},
    {value: 'sp-s', label: 'S'},
    {value: 'sp-m', label: 'M'},
    {value: 'sp-l', label: 'L'},
    {value: 'sp-xl', label: 'XL'},
];

const verticalAlignOptions = [
    {value: '', label: 'None'},
    {value: 'fas', label: 'Top'},
    {value: 'fac', label: 'Center'},
    {value: 'fae', label: 'Bottom'},
];

const colorOptions = [
    {value: '', label: 'None'},
    {value: 'light-bg', label: 'Light gray'},
    {value: 'bg-white', label: 'White'},
];

const addNewSection = (fromStart) => {
    const pushingData = {
        responsive_settings: {
            'mobile': '1', 'tablet': '1', 'desktop': '1',
            config: {
                section_showing_type_desktop: 0,
                section_showing_type_mobile: 0,
                section_header: '',
                vertical_alignment: '',
                space_sections_top: 'sp-l',
                space_sections_bottom: 'sp-l',
                space_columns: 'sp-s',
                space_components_top: 'sp-s',
                space_components_bottom: 'sp-s',
                bg_color: '',
                media_id: null,
                image_path: null,
                images: []
            },
        },
        columns: [{
            responsive_settings: {'mobile': '1', 'tablet': '1', 'desktop': '1'},
            status: false,
            components: []
        }],
        type: 1,
        status: false,
        deleted: false,
    };

    if (fromStart) {
        form.value.sections.unshift(pushingData);
    } else {
        form.value.sections.push(pushingData);
    }
}

const addComponents = () => {
    let column = form.value.sections[componentPopupParams.value.sectionIndex].columns[componentPopupParams.value.columnIndex];
    form.value.sections[componentPopupParams.value.sectionIndex].columns[componentPopupParams.value.columnIndex].components = [...column.components, ...componentPopupParams.value.components];
    componentsPopupOpen.value = false;
    componentPopupParams.value = null;
}

const addItemToComponent = (newItem) => {
    form.value.sections[componentEditPopupParams.value.sectionIndex].columns[componentEditPopupParams.value.columnIndex].components[componentEditPopupParams.value.componentIndex].items.push(newItem);
}

const editSectionColumns = (index, type) => {
    let columnsClone = [...form.value.sections[index].columns];
    form.value.sections[index].columns = [];
    form.value.sections[index].type = type;
    if (type === 1) {
        form.value.sections[index].responsive_settings = returnResponsiveStylesForRows('1', '1', '1', form.value.sections[index].responsive_settings.config);
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
    } else if (type === 2) {
        form.value.sections[index].responsive_settings = returnResponsiveStylesForRows('1', '2', '2', form.value.sections[index].responsive_settings.config);
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
    } else if (type === 3) {
        form.value.sections[index].responsive_settings = returnResponsiveStylesForRows('1', '2', '3', form.value.sections[index].responsive_settings.config);
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '2'))
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
    } else if (type === 4) {
        form.value.sections[index].responsive_settings = returnResponsiveStylesForRows('1', '1', '3', form.value.sections[index].responsive_settings.config);
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
    } else if (type === 5) {
        form.value.sections[index].responsive_settings = returnResponsiveStylesForRows('1', '2', '4', form.value.sections[index].responsive_settings.config);
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
    } else if (type === 6) {
        form.value.sections[index].responsive_settings = returnResponsiveStylesForRows('1', '2', '3', form.value.sections[index].responsive_settings.config);
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '2'))
    } else if (type === 7) {
        form.value.sections[index].responsive_settings = returnResponsiveStylesForRows('1', '1', '5', form.value.sections[index].responsive_settings.config);
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
    } else if (type === 8) {
        form.value.sections[index].responsive_settings = returnResponsiveStylesForRows('1', '2', '6', form.value.sections[index].responsive_settings.config);
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
    } else if (type === 9) {
        form.value.sections[index].responsive_settings = returnResponsiveStylesForRows('1', '2', '4', form.value.sections[index].responsive_settings.config);
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '3'))
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
    } else if (type === 10) {
        form.value.sections[index].responsive_settings = returnResponsiveStylesForRows('1', '2', '4', form.value.sections[index].responsive_settings.config);
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '3'))
    } else if (type === 11) {
        form.value.sections[index].responsive_settings = returnResponsiveStylesForRows('1', '2', '4', form.value.sections[index].responsive_settings.config);
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '2'))
        form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
    }

    let newColumnsLength = form.value.sections[index].columns.length;

    let orienterIndex = 0;

    for (let i = 0; i < columnsClone.length; i++) {
        if (!columnsClone[i].components.length) continue;
        if (orienterIndex >= newColumnsLength) {
            form.value.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
        }
        form.value.sections[index].columns[orienterIndex] = JSON.parse(JSON.stringify(columnsClone[i]))
        orienterIndex++;
    }

    if (type === 10) {
        form.value.sections[index].columns[0].responsive_settings.desktop = 1;
        form.value.sections[index].columns[1].responsive_settings.desktop = 3;
    } else if (type === 9) {
        form.value.sections[index].columns[0].responsive_settings.desktop = 3;
        form.value.sections[index].columns[1].responsive_settings.desktop = 1;
    } else if (type === 6) {
        form.value.sections[index].columns[0].responsive_settings.desktop = 1;
        form.value.sections[index].columns[1].responsive_settings.desktop = 2;
    } else if (type === 3) {
        form.value.sections[index].columns[0].responsive_settings.desktop = 2;
        form.value.sections[index].columns[1].responsive_settings.desktop = 1;
    } else if (type === 11) {
        form.value.sections[index].columns[0].responsive_settings.desktop = 1;
        form.value.sections[index].columns[1].responsive_settings.desktop = 2;
        form.value.sections[index].columns[2].responsive_settings.desktop = 1;
    } else {
        for (let k = 0; k < form.value.sections[index].columns.length; k++) {
            form.value.sections[index].columns[k].responsive_settings.desktop = 1;
        }
    }
}

const returnResponsiveStylesForRows = (mobile, tablet, desktop, config) => {
    return {
        'mobile': mobile,
        'tablet': tablet,
        'desktop': desktop,
        config: config
    }
}

const returnResponsiveStylesForColumns = (mobile, tablet, desktop) => {
    return {
        responsive_settings: {
            'mobile': mobile,
            'tablet': tablet,
            'desktop': desktop,
        },
        components: [],
    }
}

const params = computed(() => store.getters[`${props.pageTypeKey}/getParams`]);
const auth = computed(() => store.getters['auth/getUser']);

const componentsPopupOpen = ref(false);
const componentPopupParams = ref(null);

const componentEditPopupOpen = ref(false);
const componentEditPopupParams = ref(null);

const sectionEditPopupOpen = ref(false);
const sectionEditPopupParams = ref(null);

const activeTab = ref('general');
const tabsWithErrors = ref([]);
const justSubmitted = ref(false);

const tabsRoutes = [
    {key: 'general', title: 'General *', icon: ['far', 'gear']},
    {key: 'builder_elements', title: 'Builder elements', icon: ['far', 'wrench']},
    {key: 'seo', title: 'SEO', icon: ['fasds', 'robot']},
];

const tabletOrMobilePerRaw = [1, 2, 3, 4, 6];

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

const removeSingleGallery = () => {
    form.value.media_id = null;
    form.value.media = [];
}

const insert = (data) => {
    data.media.forEach(media => {
        if (media.id) {
            form.value.media_id = media.id
            form.value.media = [mediaData(media)];
        }
    });
}

const removeSingleSectionGallery = (sectionIndex) => {
    form.value.sections[sectionIndex].responsive_settings.config.media_id = null;
    form.value.sections[sectionIndex].responsive_settings.config.images = [];
    form.value.sections[sectionIndex].responsive_settings.config.image_path = null;
}

const insertSectionImage = (data, sectionIndex) => {
    data.media.forEach(media => {
        if (media.id) {
            form.value.sections[sectionIndex].responsive_settings.config.media_id = media.id
            form.value.sections[sectionIndex].responsive_settings.config.image_path = media.path
            form.value.sections[sectionIndex].responsive_settings.config.images = [mediaData(media)];
        }
    });
}

const mediaData = (media) => {
    return {
        id: '',
        media_id: media.id,
        path: media.original_path,
        type: media.type,
        file_type: media.file_type,
        video_type: '',
        video_url: '',
    };
}

watch(() => form.value.is_home, (newVal) => {
    if (newVal) {
        form.value.priority = 10;
    } else {
        form.value.priority = 8;
    }
});

const priorityOptions = [10, 9, 8, 7, 6, 5, 4, 3, 2, 1];

const cloningComponentParams = ref(null)

const clonePage = ref({
    popupOpen: false,
    language_id: '',
    language_idRules: ['required'],
    cloned_page_translation_id: null,
    errors: {}
})

const submitClonePage = async () => {
    try {
        const errors = validate(clonePage.value);
        if (Object.keys(errors).length > 0) {
            clonePage.value.errors = errors;
            return false;
        }
        const response = await store.dispatch(`${props.pageTypeKey}/clone`, {
            page_translation_id: form.value.translation_id,
            language_id: clonePage.value.language_id
        });

        clonePage.value.cloned_page_translation_id = response.data.page_translation_id;

        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully cloned'
        });
    } catch (error) {
        clonePage.value.errors = error;
    }
}
const generalParams = computed(() => store.getters['general/getParams']);

const isAiTranslateExpanded = ref(false);
const toggleAiTranslate = () => {
    isAiTranslateExpanded.value = !isAiTranslateExpanded.value;
};

const selectedLanguagesForTranslation = ref([]);
const aItranslationsAdditionalData = ref(null);
const generateAITranslations = async (isAll = false) => {
    try {
        aItranslationsAdditionalData.value = null;
        let res = await store.dispatch(`${props.pageTypeKey}/translateAI`, {
            translation_id: form.value.translation_id,
            language_ids: isAll ? [] : selectedLanguagesForTranslation.value,
        });

        if (res?.data?.noParentTranslations.length > 0) {
            aItranslationsAdditionalData.value = 'The following languages have no parent translations: '+res.data.noParentTranslations.join(', ');
        }

        if (!isAll) selectedLanguagesForTranslation.value = [];

        // Show success notification
        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: res.data.message,
        });
    } catch (error) {
        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Error',
            type: 'error',
            message: 'An unexpected error occurred',
        });
    }
};

const approveTranslation = async () => {
    await store.dispatch(`${props.pageTypeKey}/approveTranslation`, {
        translation_id: form.value.translation_id,
    })
    form.value.approved = true;

    store.commit('notification/SET_NOTIFICATION', {
        visible: true,
        title: 'Success',
        message: `Successfully approved`
    });
}

</script>

<template>
    <template v-if="clonePage.popupOpen">
        <PopupWithSlot
            classes="max-w-[80%] w-[80%]"
            @close="clonePage.popupOpen = false, clonePage.cloned_page_translation_id = null, clonePage.language_id = ''"
        >
            <div
                v-if="Object.keys(clonePage.errors).length > 0 && clonePage.errors.general"
                class="grid grid-cols-1 gap-9 p-6.5"
            >
                <AlertError :errors="clonePage.errors.general"/>
            </div>
            <template v-if="clonePage.cloned_page_translation_id">
                <div class="flex justify-center">
                    <div>
                        <a
                            class="ml-2 inline-block"
                            :href="`/contents/${pageTypeName.replace(/_/g, '-')}/update/${form.id}/${clonePage.language_id}`"
                            target="_blank"
                        >
                    <span
                        class="flex items-center gap-2 rounded bg-meta-3 py-3 px-4.5 font-medium text-white hover:bg-opacity-80"
                    >
                        <font-awesome-icon :icon="['fasr', 'eye']"/>
                        Show cloned page
                    </span>
                        </a>
                    </div>
                    <div class="ml-2">
                        <CustomButton
                            @click="clonePage.cloned_page_translation_id = null"
                            class="flex items-center gap-2 rounded bg-primary py-3 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['fasr', 'clone']"/>
                            Clone one more
                        </CustomButton>
                    </div>
                </div>
            </template>
            <template v-else>
                <div class="grid grid-cols-3 gap-9">
                    <div class="flex flex-col p-6.5">
                    </div>
                    <div class="flex flex-col p-6.5">
                        <CustomSelect
                            label="Language *"
                            v-model="clonePage.language_id"
                            mode="single"
                            placeholder="Select language *"
                            :options="params.languages"
                            :excluded-value="form.language_id"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                            @update:modelValue="clonePage.errors = validate(form)"
                            :error="clonePage.errors['language_id']"
                        />
                    </div>
                    <div class="flex flex-col p-6.5">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-9">
                    <div class="flex flex-col">

                    </div>
                    <div class="flex flex-col items-end">
                        <CustomButton
                            @click="submitClonePage"
                            class="flex items-center gap-2 rounded bg-meta-3 py-3 px-4.5 font-medium text-white hover:bg-opacity-80 max-w-[140px]"
                            type="button"
                        >
                            <font-awesome-icon :icon="['fasr', 'clone']"/>
                            Create
                        </CustomButton>
                    </div>
                </div>
            </template>
        </PopupWithSlot>
    </template>
    <form @submit.prevent="emits('submit'), justSubmitted = true">
        <template v-if="componentsPopupOpen">
            <PopupWithSlot
                classes="max-w-[80%] w-[80%]"
                @close="componentsPopupOpen = false, componentPopupParams = null"
            >
                <div class="grid grid-cols-1">
                    <div class="flex flex-col p-6.5 pb-0 text-left font-bold text-black text-2xl">
                        Components
                    </div>
                </div>
                <div class="grid grid-cols-5 gap-9">

                    <template
                        v-for="(component, index) in params.components"
                        :key="index"
                    >
                        <div class="flex flex-col p-6.5 text-left">
                            <div class="flex">
                                <div>
                                    <p>{{ component.name }}:</p>
                                </div>
                                <CustomInput
                                    label=""
                                    type="checkbox"
                                    class="mt-auto mb-auto ml-1"
                                    @change="(value) => {
                                        if (value) {
                                            if (!componentPopupParams.components) componentPopupParams.components = [];
                                            componentPopupParams.components.push({...component, items: [], config: {}, status: false},);
                                        } else {
                                            componentPopupParams.components = componentPopupParams.components.filter(item => item.id !== component.id);
                                        }
                                    }"
                                />
                            </div>
                            <img
                                class="zoom-img mt-5"
                                :src="component.layout_image"
                                :alt="component.name"
                            >
                        </div>
                    </template>
                </div>
                <template v-if="componentPopupParams.components && componentPopupParams.components.length">
                    <div class="grid grid-cols-1 gap-9">
                        <div class="flex flex-col">
                            <div class="flex ml-auto gap-5">
                                <CustomButton
                                    type="button"
                                    @click="addComponents()"
                                    class="flex items-center gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 "
                                >
                                    <font-awesome-icon :icon="'plus'"/>
                                    Add components
                                </CustomButton>
                            </div>
                        </div>
                    </div>
                </template>
            </PopupWithSlot>
        </template>
        <template v-if="sectionEditPopupOpen">
            <PopupWithSlot
                classes="max-w-[80%] w-[80%]"
                @close="sectionEditPopupOpen = false"
            >
                <div class="grid grid-cols-11 gap-9 text-left mt-5">
                    <div class="flex flex-col gap-9 col-span-2">
                        <Switch
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            @change="(value) => {
                            form.sections[sectionEditPopupParams.sectionIndex].responsive_settings.config.is_full_width = value;
                        }"
                            :value="form.sections[sectionEditPopupParams.sectionIndex].responsive_settings.config.is_full_width"
                            :id="`is_full_width${sectionEditPopupParams.sectionIndex}`"
                            label="Full width"
                        />
                    </div>
                    <div class="flex flex-col gap-9 col-span-2">
                        <Switch
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            @change="(value) => {
                            form.sections[sectionEditPopupParams.sectionIndex].responsive_settings.config.mobile_reverse = value;
                        }"
                            :value="form.sections[sectionEditPopupParams.sectionIndex].responsive_settings.config.mobile_reverse"
                            :id="`mobile_reverse_${sectionEditPopupParams.sectionIndex}`"
                            label="Mobile reverse"
                        />
                    </div>
                    <div class="flex flex-col gap-9 col-span-2">
                        <CustomSelect
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            label="Vertical Alignment *"
                            v-model="form.sections[sectionEditPopupParams.sectionIndex].responsive_settings.config.vertical_alignment"
                            mode="single"
                            placeholder="Select *"
                            :options="verticalAlignOptions"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                        />
                    </div>
                    <div class="flex flex-col gap-9 col-span-3">
                        <CustomSelect
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            label="Background color"
                            v-model="form.sections[sectionEditPopupParams.sectionIndex].responsive_settings.config.bg_color"
                            mode="single"
                            placeholder="Select *"
                            :options="colorOptions"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-10 gap-9 text-left mt-5">
                    <div class="flex flex-col gap-9 col-span-2">
                        <CustomSelect
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            label="Section's space top"
                            v-model="form.sections[sectionEditPopupParams.sectionIndex].responsive_settings.config.space_sections_top"
                            mode="single"
                            placeholder="Select *"
                            :options="spaceOptions"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                        />
                    </div>
                    <div class="flex flex-col gap-9 col-span-2">
                        <CustomSelect
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            label="Section's space bottom"
                            v-model="form.sections[sectionEditPopupParams.sectionIndex].responsive_settings.config.space_sections_bottom"
                            mode="single"
                            placeholder="Select *"
                            :options="spaceOptions"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                        />
                    </div>
                    <div class="flex flex-col gap-9 col-span-2">
                        <CustomSelect
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            label="Column's space"
                            v-model="form.sections[sectionEditPopupParams.sectionIndex].responsive_settings.config.space_columns"
                            mode="single"
                            placeholder="Select *"
                            :options="spaceOptions"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                        />
                    </div>
                    <div class="flex flex-col gap-9 col-span-2">
                        <CustomSelect
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            label="Component's space top"
                            v-model="form.sections[sectionEditPopupParams.sectionIndex].responsive_settings.config.space_components_top"
                            mode="single"
                            placeholder="Select *"
                            :options="spaceOptions"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                        />
                    </div>
                    <div class="flex flex-col gap-9 col-span-2">
                        <CustomSelect
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            label="Component's space bottom"
                            v-model="form.sections[sectionEditPopupParams.sectionIndex].responsive_settings.config.space_components_bottom"
                            mode="single"
                            placeholder="Select *"
                            :options="spaceOptions"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-9 text-left mt-5">
                    <div class="flex flex-col col-span-1">
                        <label class="block font-medium text-black mb-5">Section header</label>
                        <CKEditorComponent
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            :model="form.sections[sectionEditPopupParams.sectionIndex].responsive_settings.config.section_header"
                            @updateValue="(value) => {
                                form.sections[sectionEditPopupParams.sectionIndex].responsive_settings.config.section_header = value
                            }"
                        />
                    </div>
                    <div class="flex flex-col col-span-1">
                        <CustomMediaList
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            @remove-images="removeSingleSectionGallery(sectionEditPopupParams.sectionIndex)"
                            label="Background image"
                            @insert="(data) => {
                                insertSectionImage(data, sectionEditPopupParams.sectionIndex)
                            }"
                            :images="form.sections[sectionEditPopupParams.sectionIndex].responsive_settings.config.images ? form.sections[sectionEditPopupParams.sectionIndex].responsive_settings.config.images : []"
                            :types="['images']"
                            mode="single"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-4 gap-9 text-left mt-5">
                    <div class="flex flex-col col-span-2 gap-9">
                        <CustomSelect
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            label="Section showing type: Mobile"
                            v-model="form.sections[sectionEditPopupParams.sectionIndex].responsive_settings.config.section_showing_type_mobile"
                            mode="single"
                            placeholder="Select"
                            :options="params.sectionShowingTypes"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                        />
                    </div>
                    <div
                        class="flex flex-col col-span-1 gap-9"
                    >
                        <CustomSelect
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            label="Per raw: Tablet"
                            v-model="form.sections[sectionEditPopupParams.sectionIndex].responsive_settings.tablet"
                            mode="single"
                            placeholder="Select"
                            :options="tabletOrMobilePerRaw"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                        />
                    </div>
                    <div
                        class="flex flex-col col-span-1 gap-9"
                    >
                        <CustomSelect
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            label="Per raw: Mobile"
                            v-model="form.sections[sectionEditPopupParams.sectionIndex].responsive_settings.mobile"
                            mode="single"
                            placeholder="Select"
                            :options="tabletOrMobilePerRaw"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                        />
                    </div>
                </div>
            </PopupWithSlot>
        </template>
        <template v-if="componentEditPopupOpen">
            <PopupWithSlot
                classes="max-w-[80%] w-[80%]"
                @close="componentEditPopupOpen = false, componentEditPopupParams = null"
            >
                <template v-if="componentEditPopupParams.componentKey === 'faq_component'">
                    <FAQComponentConfig
                        :page-type-name="pageTypeName"
                        @save="(newItem) => {
                            addItemToComponent(newItem);
                        }"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex].items"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'result_numbers_component'">
                    <ResultNumbersComponentConfig
                        :page-type-name="pageTypeName"
                        @save="(newItem) => {
                            addItemToComponent(newItem);
                        }"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex]"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'usp_list_component'">
                    <USPListComponentConfig
                        :page-type-name="pageTypeName"
                        @save="(newItem) => {
                            addItemToComponent(newItem);
                        }"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex]"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'builder_button_component'">
                    <BuilderButtonComponent
                        :page-type-name="pageTypeName"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex]"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'slider_first'">
                    <SliderComponentConfig
                        :page-type-name="pageTypeName"
                        @save="(newItem) => {
                            addItemToComponent(newItem);
                        }"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex].items"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'custom_links_component_first'">
                    <CustomLinkBoxComponentConfig
                        :page-type-name="pageTypeName"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex].items"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'custom_links_component_second'">
                    <CustomLinkBox2ComponentConfig
                        :page-type-name="pageTypeName"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex].items"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'image_component'">
                    <ImageComponentConfig
                        :page-type-name="pageTypeName"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex].items"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'b2b_quick_cart'">
                    <B2BQuickCart
                        :page-type-name="pageTypeName"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex]"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'newsletter_component'">
                    <Newsletter
                        :page-type-name="pageTypeName"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex]"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'invoice_request_form_component'">
                    <InvoiceRequestFormComponentConfig
                        :page-type-name="pageTypeName"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex]"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'video_component'">
                    <VideoComponentConfig
                        :page-type-name="pageTypeName"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex].items"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'video_slider_component'">
                    <VideoSliderComponentConfig
                        :page-type-name="pageTypeName"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex].items"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'relation_component_first'">
                    <RelationBox1ComponentConfig
                        :page-type-name="pageTypeName"
                        :categories="params.structuredCategories"
                        :pages="params.pages"
                        :posts="params.posts"
                        :language-id="form.language_id"
                        :self-id="form.id"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex].items"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'relation_component_second'">
                    <RelationBox2ComponentConfig
                        :page-type-name="pageTypeName"
                        :categories="params.structuredCategories"
                        :pages="params.pages"
                        :posts="params.posts"
                        :language-id="form.language_id"
                        :is-update="isUpdate"
                        :self-id="form.id"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex].items"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'relation_component_third'">
                    <RelationBox3ComponentConfig
                        :page-type-name="pageTypeName"
                        :categories="params.structuredCategories"
                        :pages="params.pages"
                        :is-update="isUpdate"
                        :language-id="form.language_id"
                        :posts="params.posts"
                        :self-id="form.id"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex].items"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'products_by_categories_component'">
                    <CategoryComponentConfig
                        :page-type-name="pageTypeName"
                        @save="(newItem) => {
                            addItemToComponent(newItem);
                        }"
                        :is-update="isUpdate"
                        :section-showing-types="params.sectionShowingTypes"
                        :categories="params.structuredCategories"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex]"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'blogs_component'">
                    <CategoryBlogComponentConfig
                        :page-type-name="pageTypeName"
                        @save="(newItem) => {
                            addItemToComponent(newItem);
                        }"
                        :is-update="isUpdate"
                        :section-showing-types="params.sectionShowingTypes"
                        :categories="params.postCategories"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex]"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'a_plus_content_component'">
                    <APlusContentComponentConfig
                        :page-type-name="pageTypeName"
                        @save="(newItem) => {
                            addItemToComponent(newItem);
                        }"
                        :is-update="isUpdate"
                        :contents="params.aPlusContents"
                        :self-id="form.id"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex]"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'calculator_component'">
                    <CalculatorComponentConfig
                        :is-update="isUpdate"
                        :calculators="params.calculators"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex].items"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'gallery_component'">
                    <GalleryComponentConfig
                        :page-type-name="pageTypeName"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex]"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'editor_component'">
                    <EditorComponentConfig
                        :page-type-name="pageTypeName"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex]"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'offer_component'">
                    <OfferComponentConfig
                        :page-type-name="pageTypeName"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex].items"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'event_component'">
                    <EventComponentConfig
                        :page-type-name="pageTypeName"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex].items"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'employee_component'">
                    <EmployeeComponentConfig
                        :page-type-name="pageTypeName"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex].items"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'form_component'">
                    <FormComponentConfig
                        :page-type-name="pageTypeName"
                        @save="(newItem) => {
                            addItemToComponent(newItem);
                        }"
                        :params="params"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex]"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'lead_form_component'">
                    <LeadFormComponentConfig
                        :page-type-name="pageTypeName"
                        @save="(newItem) => {
                            addItemToComponent(newItem);
                        }"
                        :language-id="form.language_id"
                        :params="params"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex]"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'trustpilot_component'">
                    <TrustpilotComponentConfig
                        :page-type-name="pageTypeName"
                        @save="(newItem) => {
                            addItemToComponent(newItem);
                        }"
                        :params="params"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex]"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'testimonials_component'">
                    <TestimonialsComponentConfig
                        :page-type-name="pageTypeName"
                        @save="(newItem) => {
                            addItemToComponent(newItem);
                        }"
                        :params="params"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex]"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'bullet_points_component'">
                    <BulletPointsComponentConfig
                        :page-type-name="pageTypeName"
                        @save="(newItem) => {
                            addItemToComponent(newItem);
                        }"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex]"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'all_products_component'">
                    <AllProductsComponentConfig
                        :page-type-name="pageTypeName"
                        @save="(newItem) => {
                            addItemToComponent(newItem);
                        }"
                        :is-update="isUpdate"
                        :section-showing-types="params.sectionShowingTypes"
                        :categories="params.structuredCategories"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex]"
                    />
                </template>
                <template v-else-if="componentEditPopupParams.componentKey === 'tracking_form_component'">
                    <TrackingFormComponentConfig
                        :page-type-name="pageTypeName"
                        :is-update="isUpdate"
                        v-model="form.sections[componentEditPopupParams.sectionIndex].columns[componentEditPopupParams.columnIndex].components[componentEditPopupParams.componentIndex].items"
                    />
                </template>
            </PopupWithSlot>
        </template>
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 gap-9 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>
        <div class="grid grid-cols-5 gap-7 px-6.5 py-2">
            <div class="flex flex-col">
                <CustomSelect
                    label="Languages"
                    v-model="form.language_id"
                    mode="single"
                    placeholder="Select languages"
                    :disabled="!isUpdate"
                    :options="params.languages"
                    :searchable="true"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['language_id']"
                />
            </div>
        </div>
        <div class="flex flex-col px-6.5 col-span-4" v-if="form.translation_id">
            <div class="pb-5">
                <CustomButton
                    :disabled="!auth.user_group.permissions_by_name.attributes[0].can_edit"
                    @click="toggleAiTranslate"
                    type="button"
                    class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                >
                    <font-awesome-icon :icon="['fasr', 'robot']"/>
                    Translate with AI
                </CustomButton>
            </div>
            <div v-if="aItranslationsAdditionalData">
                <font-awesome-icon class="text-danger" :icon="['fasr', 'circle-exclamation']"/>
                <span class="text-danger font-bold ml-1">{{ aItranslationsAdditionalData }}</span>
            </div>
            <div class="pb-3" :class="{ 'hidden': !isAiTranslateExpanded }">
                <template v-if="form.approved">
                    <h2 class="text-black mt-2 font-bold">You can generate AI translations from this language to the
                        missing ones.</h2>
                    <div class="flex mt-2">
                        <div>
                            <CustomButton
                                :disabled="isUpdate && !auth.user_group.permissions_by_name.products[0].can_edit"
                                type="button"
                                @click="generateAITranslations(true)"
                                class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            >
                                <font-awesome-icon :icon="['fas', 'robot']"/>
                                Generate AI translations (all missing)
                            </CustomButton>
                        </div>
                        <div class="ml-2">
                            <CustomButton
                                :title="!selectedLanguagesForTranslation.length ? 'First select languages' : ''"
                                :disabled="(isUpdate && !auth.user_group.permissions_by_name.products[0].can_edit) || !selectedLanguagesForTranslation.length"
                                type="button"
                                @click="generateAITranslations()"
                                class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            >
                                <font-awesome-icon :icon="['fas', 'robot']"/>
                                Generate AI translations for selected languages
                            </CustomButton>
                        </div>
                        <div class="ml-2 min-w-[300px]">
                            <CustomSelect
                                v-model="selectedLanguagesForTranslation"
                                mode="tags"
                                placeholder="Select languages"
                                :disabled="!isUpdate"
                                :options="params.languages"
                                :invalid-feedback-place="false"
                                :close-on-select="false"
                                :searchable="true"
                                :with-general="false"
                                class="py-2 rounded-lg border-stroke bg-transparent w-full"
                            />
                        </div>
                    </div>
                </template>
                <template v-else>
                    <h2 class="text-black mt-2 font-bold">This translation was generated by AI. Please approve it if
                        everything looks correct.</h2>
                    <div class="flex mt-2">
                        <div>
                            <CustomButton
                                :disabled="isUpdate && !auth.user_group.permissions_by_name.products[0].can_edit"
                                type="button"
                                @click="approveTranslation()"
                                class="flex items-center gap-2 rounded bg-warning py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            >
                                <font-awesome-icon :icon="['fas', 'robot']"/>
                                Approve translation
                            </CustomButton>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <div
            v-if="isUpdate && form.front_url_view && (pageType === 1 || pageType === 0)"
            class="grid grid-cols-1 gap-9"
        >
            <div class="flex flex-col px-6.5 pb-2">
                <a
                    target="_blank"
                    class="hover-trigger hover:text-primary"
                    :href="form.front_url_view"
                >
                    <span class="font-semibold text-black">{{ form.front_url_view }}</span>
                </a>
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
                    <template v-if="pageType === 1">
                        <div class="grid grid-cols-3 gap-9">
                            <div class="flex flex-col p-6.5 pb-0">
                                <CustomMediaList
                                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                    @remove-images="removeSingleGallery"
                                    label="Image"
                                    @insert="insert"
                                    :images="form.media"
                                    :types="['images']"
                                    mode="single"
                                />
                            </div>
                        </div>
                    </template>
                    <div class="grid grid-cols-7 gap-9">
                        <template v-if="pageType === 0">
                            <div class="flex flex-col p-6.5 pb-0 col-span-2">
                                <CustomSelect
                                    :disabled="(isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit) || form.is_home"
                                    v-model="form.parent_id"
                                    @update:modelValue="() => {
                                   generatePath();
                                }"
                                    label="Parent"
                                    :searchable="true"
                                    mode="single"
                                    :can-clear="true"
                                    placeholder="Select parent"
                                    :options="params.pages"
                                    :excluded-value="form.translation_id"
                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                />
                            </div>
                        </template>
                        <template v-if="pageType === 1">
                            <div class="flex flex-col p-6.5 pb-0 col-span-2">
                                <CustomSelect
                                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                    label="Category *"
                                    v-model="form.post_category_translation_id"
                                    mode="single"
                                    placeholder="Select"
                                    :searchable="true"
                                    :options="params.postCategories"
                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                    @update:modelValue="form.errors = validate(form)"
                                    :error="form.errors['post_category_translation_id']"
                                />
                            </div>
                        </template>
                        <template v-if="pageType === 2">
                            <div class="flex flex-col p-6.5 pb-0 col-span-2">
                                <CustomSelect
                                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                    label="Type *"
                                    v-model="form.a_plus_content_type"
                                    mode="single"
                                    placeholder="Select"
                                    :searchable="true"
                                    :options="params.aPlusContentTypes"
                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                    @keyup="form.errors = validate(form)"
                                    :error="form.errors['a_plus_content_type']"
                                />
                            </div>
                        </template>
                        <div class="flex flex-col p-6.5 pb-0 col-span-2">
                            <CustomInput
                                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                v-model="form.name"
                                name="name"
                                label="Name *"
                                type="text"
                                placeholder="Enter name"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['name']"
                            />
                        </div>
                        <template v-if="pageType === 1 || pageType === 0">
                            <div class="flex flex-col p-6.5 pb-0 col-span-2">
                                <CustomInput
                                    :disabled="(isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit) || form.is_home"
                                    v-model="form.slug"
                                    name="slug"
                                    label="Slug"
                                    type="text"
                                    placeholder="Enter or will generate automatically"
                                    :error="form.errors['slug']"
                                />
                            </div>
                        </template>
                        <template v-if="pageType === 2">
                            <div class="flex flex-col p-6.5 pb-0 col-span-2">
                                <CustomInput
                                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                    v-model="form.button_text"
                                    name="button_text"
                                    label="Button text"
                                    type="text"
                                    placeholder="Enter button text"
                                    @keyup="form.errors = validate(form)"
                                    :error="form.errors['button_text']"
                                />
                            </div>
                        </template>
                        <div class="flex flex-col p-6.5 pb-0 col-span-1">
                            <Switch
                                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                @change="(value) => {
                                     form.status = value;
                                }"
                                :value="form.status"
                                id="status"
                                label="Status"
                            />
                            <Switch
                                v-if="pageType === 0"
                                class="mt-3"
                                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                @change="(value) => {
                                     form.is_home = value;
                                }"
                                :value="form.is_home"
                                id="is_home"
                                label="Is home"
                            />
                            <Switch
                                v-if="pageType === 0 || pageType === 1"
                                class="mt-3"
                                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                @change="(value) => {
                                     form.no_index = value;
                                }"
                                :value="form.no_index"
                                id="no_index"
                                label="No index"
                            />
                        </div>
                    </div>
                    <template v-if="pageType === 1">
                        <div class="grid grid-cols-3 gap-9">
                            <div class="flex flex-col p-6.5 pb-0">
                                <CustomInput
                                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                    v-model="form.subname"
                                    name="subname"
                                    label="Subname"
                                    type="text"
                                    placeholder="Enter subname"
                                    @keyup="form.errors = validate(form)"
                                    :error="form.errors['subname']"
                                />
                            </div>
                            <div class="flex flex-col p-6.5 pb-0">
                                <CustomDatePicker
                                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                    placeholder="yyyy/mm/dd"
                                    label="Published date"
                                    format="Y-m-d"
                                    v-model="form.published_at"
                                />
                            </div>
                            <div class="flex flex-col p-6.5 pb-0">
                                <CustomInput
                                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                    v-model="form.button_text"
                                    name="button_text"
                                    label="Button text"
                                    type="text"
                                    placeholder="Enter button text"
                                    @keyup="form.errors = validate(form)"
                                    :error="form.errors['button_text']"
                                />
                            </div>
                        </div>
                    </template>
                    <div class="grid grid-cols-3 gap-9">
                        <div class="flex flex-col px-6.5"
                             v-if="(pageType === 1 || pageType === 0) && generalParams?.vendor?.b2b">
                            <CustomSelect
                                class="py-2 rounded-lg  border-stroke bg-transparent"
                                v-model="form.customer_group_ids"
                                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                mode="tags"
                                label="B2B Customer groups"
                                placeholder="Select groups"
                                :options="params.customerGroups"
                                :show-labels="true"
                                :close-on-select="false"
                            />
                        </div>
                        <div class="flex flex-col px-6.5">
                            <CustomSelect
                                class="py-2 rounded-lg  border-stroke bg-transparent"
                                v-model="form.user_level_ids"
                                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                mode="tags"
                                label="User levels"
                                placeholder="Select user levels"
                                :options="params.userLevels"
                                :show-labels="true"
                                :close-on-select="false"
                            />
                        </div>
                    </div>
                </div>
                <div v-else-if="activeTab === 'builder_elements'">
                    <div class="flex justify-center mt-8" v-if="form.sections.length > 2">
                        <div>
                            <CustomButton
                                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                title="Add new section"
                                @click="addNewSection(true)"
                                type="button"
                                class="w-[45px] h-[45px] flex items-center gap-2 rounded bg-meta-3 py-2 px-3.5 mt-5 font-medium text-white hover:bg-opacity-80 ml-auto"
                            >
                                <font-awesome-icon :icon="'plus'" class="size-5"/>
                            </CustomButton>
                        </div>
                    </div>
                    <PageNestedDragAndDrop
                        @editSectionColumns="(index, type) => {
                             editSectionColumns(index, type)
                        }"
                        @editSection="(index) => {
                            sectionEditPopupParams = {
                                sectionIndex: index
                            }
                            sectionEditPopupOpen = true;
                        }"
                        @additionalColumn="(index) => {
                            form.sections[index].columns.push(returnResponsiveStylesForColumns('1', '1', '1'))
                        }"
                        @cloneSection="(index) => {
                            form.sections.push(JSON.parse(JSON.stringify(form.sections[index])));
                        }"
                        @openComponentsPopupForColumn="(index, columnIndex) => {
                            componentPopupParams = {
                                sectionIndex: index,
                                columnIndex: columnIndex
                            }
                            componentsPopupOpen = true
                        }"
                        @pastComponent="(index, columnIndex) => {
                            if (cloningComponentParams) {
                                form.sections[index].columns[columnIndex].components.push(cloningComponentParams)
                               store.commit('notification/SET_NOTIFICATION', {
                                    visible: true,
                                    title: 'Success',
                                    message: 'Successfully cloned'
                               });

                            cloningComponentParams = null;
                            }
                        }"
                        @openEditComponentPopup="(index, columnIndex, componentIndex, componentKey) => {
                            componentEditPopupParams = {
                                sectionIndex: index,
                                columnIndex: columnIndex,
                                componentIndex: componentIndex,
                                componentKey: componentKey,
                            }
                            componentEditPopupOpen = true
                        }"
                        @cloneComponent="(index, columnIndex, componentIndex, componentKey) => {
                            cloningComponentParams = form.sections[index].columns[columnIndex].components[componentIndex];
                               store.commit('notification/SET_NOTIFICATION', {
                                    visible: true,
                                    title: 'Success',
                                    message: 'Successfully copied'
                                });
                        }"
                        :parent="true"
                        class="col-8"
                        :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                        v-model="form.sections"
                    />

                    <div class="flex justify-center mt-8">
                        <div>
                            <CustomButton
                                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                title="Add new section"
                                @click="addNewSection(false)"
                                type="button"
                                class="w-[45px] h-[45px] flex items-center gap-2 rounded bg-meta-3 py-2 px-3.5 mt-5 font-medium text-white hover:bg-opacity-80 ml-auto"
                            >
                                <font-awesome-icon :icon="'plus'" class="size-5"/>
                            </CustomButton>
                        </div>
                    </div>
                </div>
                <div v-else-if="activeTab === 'seo'">
                    <div class="grid grid-cols-5 gap-9">
                        <div class="flex flex-col p-6.5 pb-0 col-span-2">
                            <div>
                                <CustomInput
                                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                    v-model="form.meta_title"
                                    name="meta_title"
                                    label="Meta title"
                                    type="text"
                                    placeholder="Enter meta title"
                                    @keyup="form.errors = validate(form)"
                                    :error="form.errors['meta_title']"
                                />
                            </div>
                        </div>
                        <div class="flex flex-col p-6.5 pb-0 col-span-2">
                            <div>
                                <CustomInput
                                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                    v-model="form.meta_keywords"
                                    name="meta_keywords"
                                    label="Meta keywords"
                                    type="text"
                                    placeholder="Enter meta keywords"
                                    :error="form.errors['meta_keywords']"
                                />
                            </div>
                        </div>
                        <template v-if="pageType === 1 || pageType === 0">
                            <div class="flex flex-col p-6.5 pb-0 col-span-1">
                                <CustomSelect
                                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                    label="Sitemap priority *"
                                    v-model="form.priority"
                                    mode="single"
                                    placeholder="Select"
                                    :options="priorityOptions"
                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                    @keyup="form.errors = validate(form)"
                                    :error="form.errors['priority']"
                                />
                            </div>
                        </template>
                    </div>
                    <div class="grid grid-cols-1 gap-9">
                        <div class="flex flex-col p-6.5 pb-0">
                            <label class="mb-2.5 block font-medium text-black">Meta description</label>
                            <textarea
                                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                v-model="form.meta_description"
                                rows="6"
                                placeholder="Enter meta description"
                                class="w-full rounded border-[1.5px] text-black border-stroke bg-transparent py-3 px-5 font-normal outline-none focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-10 gap-9 p-6.5 pt-0">
            <div class="col-span-7 p-6.5">
                <div v-if="pageType === 0 && !form.is_home">
                    Permalink: {{ form.path }}
                    <template v-if="form.slug">/{{ form.slug }}</template>
                </div>
            </div>
            <div class="flex flex-col p-6.5 col-span-3 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template
                        v-if="isUpdate || auth.user_group.permissions_by_name[pageTypeName][0].can_add">
                        <CustomButton
                            @click="clonePage.popupOpen = true"
                            class="flex items-center gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['fasr', 'clone']"/>
                            Clone
                        </CustomButton>
                    </template>
                    <template v-if="auth.user_group.permissions_by_name[pageTypeName][0].can_delete">
                        <CustomButton
                            v-if="isUpdate && form.translation_id"
                            @click="store.commit(`${pageTypeKey}/SET_DELETE_MODAL_VALUE`, {
                                  value: true,
                                    id: form.translation_id,
                                    deletingActionApi: 'delete-translation',
                                    deletingText: 'Deleting this translation will be permanent and cannot be undone. Once deleted, you will not be able to restore it.',
                                });"
                            class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'trash']"/>
                            Delete
                        </CustomButton>
                    </template>

                    <template v-if="!isUpdate || auth.user_group.permissions_by_name[pageTypeName][0].can_edit">
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

<style>

.zoom-img {
    transition: transform 0.2s ease;
    cursor: zoom-in;
}

.zoom-img:hover {
    transform: scale(1.5);
    z-index: 10;
}
</style>
