<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import Switch from "@components/global/Switch.vue";
import TooltipOne from "@components/global/Tooltips/TooltipOne.vue";
import BadgeThree from "@components/global/Badges/BadgeThree.vue";
import CustomTextarea from "@components/global/CustomTextarea.vue";
import CustomMediaList from "@components/media/CustomMediaList.vue";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";
import PopupWithSlot from "@components/global/PopupWithSlot.vue";
import CustomTableSecond from "@components/global/CustomTableSecond.vue";
import TieredPricesForVariants from "@components/product/TieredPricesForVariants.vue";

const AccordionTwo = defineAsyncComponent(() => import("@components/accordion/AccordionTwo.vue"));

import {computed, ref, toRefs, watch, defineAsyncComponent} from "vue";
import {validate} from "@validation/customValidation.js";
import {useStore} from "vuex";

const store = useStore()

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    emitAction: {
        type: String
    },
    productId: {
        type: String,
        required: false
    },
    errors: {
        type: Array,
        required: false
    },
    multiselectProductErrors: {
        type: Array,
        required: false
    },
});

const {modelValue} = toRefs(props);
const form = ref(modelValue.value);
const newVariant = ref(null);
const media = ref(form.value.media);
const media_translation = ref(form.value.media_translation);
const watermark_media = ref(form.value.watermark_settings?.watermark_media ? [{
    "type": "images",
    "path": form.value.watermark_settings?.watermark_media
}] : '');

const params = computed(() => store.getters['product/getParams']);
const showingAttributeIds = ref({})

watch(() => showingAttributeIds.value, (newVal) => {
    Object.keys(form.value.attribute_type_variables).forEach(key => {
        if (
            newVal && !newVal.variable.includes(key) &&
            !newVal.simple.includes(key)
        ) {
            form.value.attribute_type_variables[key] = [];
        }

    });
}, {deep: true});

watch(() => params.value, (newVal) => {
    showingAttributeIds.value = newVal.selectedShowingAttributes;
}, {immediate: true});

const generatingVariationsAttributeValues = ref({});
const generatingVariationsAttributeValuesCount = ref({});
const vendorKey = localStorage.getItem('vendor_key');
const tabsRoutes = [
    {key: 'general', title: 'General *', icon: ['far', 'gear']},
    {key: 'gift_card_prices', title: 'Gift card prices *', icon: ['far', 'gift']},
    {key: 'inventory', title: 'Inventory *', icon: ['fass', 'file-pen']},
    {key: 'multiselect', title: 'Multiselect', icon: ['far', 'calculator-simple']},
    {key: 'attributes', title: 'Attributes', icon: ['far', 'screwdriver-wrench']},
    {key: 'watermark_image', title: 'Watermark image', icon: ['far', 'image']},
    {key: 'gallery', title: 'Gallery', icon: ['far', 'image']},
    {key: 'gallery_translation', title: 'Gallery', icon: ['far', 'image']},
    {key: 'custom_fields', title: 'Custom fields', icon: ['far', 'input-text']},
    {key: 'linked_products', title: 'Linked products', icon: ['far', 'link']},
    {key: 'bundling', title: 'Bundling', icon: ['far', 'layer-group']},
    {key: 'contents', title: 'Contents', icon: ['far', 'arrows-to-circle']},
    {key: 'tools', title: 'Tools', icon: ['far', 'screwdriver-wrench']},
    {key: 'accesses', title: 'Accesses', icon: ['fasr', 'eye']},
    {key: 'variations', title: 'Variations', icon: ['fasds', 'layer-group']},
    {key: 'seo', title: 'SEO', icon: ['fasds', 'robot']},
    {key: 'b2b', title: 'B2B', icon: ['far', 'business-time']},
];

const taxableOptions = [
    {value: true, label: 'Taxable'},
    {value: false, label: 'None'}
];

const stockStatuses = [
    {value: true, label: 'In stock'},
    {value: false, label: 'Out of stock'}
];

const tabsWithErrors = ref([]);
const justSubmitted = ref(false);
const activeTab = ref('general');


const dynamicValidation = (index, key, form, validationArrayKey) => {
    emits('dynamic-validation', index, key, form, validationArrayKey);
};

const addNewCustomField = () => {
    form.value.custom_fields.unshift({
        key: '',
        value: '',
        deleted: false,
        changed: false
    });
}

const generateVariations = async () => {
    await store.dispatch('product/generateVariations', {
        id: props.productId,
        sku: form.value.sku,
        generating_variations_attribute_values: generatingVariationsAttributeValues.value
    });
    emits('fetch-variations');
    generatingVariationsAttributeValues.value = {};
}

const deleteVariations = async (deleteInvalids) => {
    await store.dispatch('product/deleteVariations', {product_id: props.productId, delete_invalids: deleteInvalids});
    emits('fetch-variations');
}

const saveNewVariant = async () => {
    try {
        const errors = validate(newVariant.value);
        if (Object.keys(errors).length > 0) {
            newVariant.value.errors = errors;
            return false;
        }

        await store.dispatch('product/storeVariant', newVariant.value);

        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully created'
        });

        newVariant.value = null;
        await emits('fetch-variations');
    } catch (error) {
        newVariant.value.errors = error;
    }
}

const addNewVariantInitial = () => {
    newVariant.value = {
        product_id: props.productId,
        sku: '',
        skuRules: ['required', 'maxLength:50'],
        parents: '',
        parentsRules: ['skuFormat', 'required'],
        regular_price: '',
        regular_priceRules: ['required', 'validDecimal'],
        sales_price: '',
        sales_priceRules: ['validDecimal'],
        tax_status: true,
        stock_status: true,
        status: true,
        media: [],
        gallery: [],
        media_id: '',
        open: true,
        errors: {}
    }

    if (params.value.preparedAttributes) {
        for (let i = 0; i < params.value.preparedAttributes.length; i++) {
            let element = params.value.preparedAttributes[i];
            if (element.logic == 1) continue;

            newVariant.value[element.variable_name] = '';
            newVariant.value[`${element.variable_name}Rules`] = ['required'];
        }
    }
};

watch(modelValue, (newVal) => {
    form.value = newVal;
}, {immediate: true});

watch(form.value, (newVal) => {
    emits('update:modelValue', newVal);
});

const getCount = () => {
    generatingVariationsAttributeValuesCount.value = 0;
    Object.keys(generatingVariationsAttributeValues.value).forEach(key => {
        if (generatingVariationsAttributeValues.value[key] && generatingVariationsAttributeValues.value[key].length) {
            generatingVariationsAttributeValuesCount.value++;
        }
    });
}

watch(() => generatingVariationsAttributeValues.value, (newVal) => {
    getCount();
}, {immediate: true, deep: true});

watch(
    () => form.value.errors,
    (newVal) => {
        tabsWithErrors.value = [];
        if (form.value.language_id == -1) {
            if (
                Object.hasOwn(newVal, 'tax_status') || Object.hasOwn(newVal, 'regular_price') || Object.hasOwn(newVal, 'stock_status')
                || Object.hasOwn(newVal, 'product_type') || Object.hasOwn(newVal, 'category_ids')
            ) {
                if (justSubmitted.value) {
                    activeTab.value = 'general';
                    justSubmitted.value = false;
                }
                tabsWithErrors.value.push('general');
            }
            if (
                Object.hasOwn(newVal, 'sku') || Object.hasOwn(newVal, 'stock_status') || Object.hasOwn(newVal, 'parents')
            ) {
                if (justSubmitted.value) {
                    activeTab.value = 'inventory';
                    justSubmitted.value = false;
                }
                tabsWithErrors.value.push('inventory');
            }
        } else {
            if (
                Object.hasOwn(newVal, 'name')
            ) {
                if (justSubmitted.value) {
                    activeTab.value = 'general';
                    justSubmitted.value = false;
                }
                tabsWithErrors.value.push('general');
            }
        }
    },
    {deep: true}
);


const removeGallery = (id, index, removeGallery = 'removeGallery') => {
    if (index >= 0) {
        if (!form.value.product_variations[index][removeGallery]) {
            form.value.product_variations[index][removeGallery] = [];
        }
        form.value.product_variations[index][removeGallery].push(id);
    } else {
        if (id) {
            form.value[removeGallery].push(id);
        }
    }
}
const removeSingleGallery = (id, index, fieldName = 'media_id', fieldNameMediaArray = 'media') => {
    if (index >= 0) {
        form.value.product_variations[index][fieldName] = null;
        form.value.product_variations[index][fieldNameMediaArray] = '';
    } else {
        form.value[fieldName] = null;
    }
}

const mediaData = (media) => {
    return {
        id: '',
        media_id: media.id,
        product_id: form.value.id,
        path: media.original_path,
        type: media.type,
        file_type: media.file_type,
        video_type: '',
        video_url: '',
    };
}
const insert = (data, type, forSingleFieldMediaId = 'media_id', forSingleFieldMedia = 'media', forGallery = 'gallery') => {
    const indexValue = computed(() => store.getters['media/getMediaIndex']);

    if (type === 'new') {
        if (data.mode.value === 'single') {
            data.media.forEach(mediaItem => {
                if (mediaItem.id) {
                    newVariant.value[forSingleFieldMediaId] = mediaItem.id
                    let singleMedia = mediaData(mediaItem);
                    newVariant.value[forSingleFieldMedia] = [singleMedia];
                }
            });
        } else {
            data.media.forEach(mediaItem => {
                if (mediaItem.id) {
                    newVariant.value[forGallery].push(mediaData(mediaItem));
                }
            });
        }
    } else if (indexValue.value >= 0 && indexValue.value !== null) {
        if (data.mode.value === 'single') {
            data.media.forEach(mediaItem => {
                if (mediaItem.id) {
                    form.value.product_variations[indexValue.value][forSingleFieldMediaId] = mediaItem.id
                    form.value.product_variations[indexValue.value][forSingleFieldMedia] = mediaData(mediaItem);
                }
            });
        } else {
            data.media.forEach(mediaItem => {
                if (mediaItem.id) {
                    if (!form.value.product_variations[indexValue.value][forGallery]) {
                        form.value.product_variations[indexValue.value][forGallery] = [];
                    }
                    form.value.product_variations[indexValue.value][forGallery].push(mediaData(mediaItem));
                }
            });
        }
    } else {
        if (data.mode.value === 'single') {
            data.media.forEach(mediaItem => {
                if (mediaItem.id) {
                    form.value[forSingleFieldMediaId] = mediaItem.id

                    if (forSingleFieldMediaId === 'media_id') {
                        media.value = [mediaData(mediaItem)];
                    } else {
                        media_translation.value = [mediaData(mediaItem)];
                    }
                }
            });
        } else {
            data.media.forEach(mediaItem => {
                if (mediaItem.id) {
                    form.value[forGallery].push(mediaData(mediaItem));
                }
            });
        }
    }
}

const insertWatermark = (data) => {
    data.media.forEach(media => {
        if (media.id) {
            watermark_media.value = [mediaData(media)];
            form.value.watermark_settings.watermark_media = media.original_path;
        }
    });
}

const removeSingleWatermark = () => {
    form.value.watermark_settings.watermark_media = '';
}

const getAdminBaseUrl = computed(() => store.getters['general/getAdminBaseUrl']);

const insertMediaToDescriptions = (data, variantIndex, descriptionTypeKey) => {
    data.media.forEach(file => {
        store.dispatch('media/fetchByField', {
            media_id: file.id,
            language_id: data.language_id,
        }).then((mediaData) => {
            let alt = '';
            if (mediaData && mediaData.alt) {
                alt = mediaData.alt;
            } else {
                alt = file.file_name;
            }

            if (file.path) {
                form.value.product_variations[variantIndex][descriptionTypeKey] += `<img src="${getAdminBaseUrl.value}/uploads/${vendorKey}/images/large${file.path}" loading="lazy" alt="${alt}" title="${alt}" width="${file.width}" height="${file.height}" sizes="(max-width: 640px) 300px, (max-width: 1024) 640px, 1024px" srcset="${getAdminBaseUrl.value}/uploads/${vendorKey}/images/thumbnail${file.path} 300w, ${getAdminBaseUrl.value}/uploads/${vendorKey}/images/medium${file.path} 640w, ${getAdminBaseUrl.value}/uploads/${vendorKey}/images/large${file.path} 1024w"/><br>`;
            } else {
                form.value.product_variations[variantIndex][descriptionTypeKey] += `<img src="${getAdminBaseUrl.value}${file.original_path}" loading="lazy" alt="${alt}" title="${alt}" width="${file.width}" height="${file.height}"/><br>`;
            }
        });
    });
}

const insertMediaToAttributesDescriptions = (data, attributeId) => {
    data.media.forEach(file => {
        store.dispatch('media/fetchByField', {
            media_id: file.id,
            language_id: data.language_id,
        }).then((mediaData) => {
            let alt = '';
            if (mediaData && mediaData.alt) {
                alt = mediaData.alt;
            } else {
                alt = file.file_name;
            }

            if (file.path) {
                form.value.attributes_description_popup[attributeId] += `<img src="${getAdminBaseUrl.value}/uploads/${vendorKey}/images/large${file.path}" loading="lazy" alt="${alt}" title="${alt}" width="${file.width}" height="${file.height}" sizes="(max-width: 640px) 300px, (max-width: 1024) 640px, 1024px" srcset="${getAdminBaseUrl.value}/uploads/${vendorKey}/images/thumbnail${file.path} 300w, ${getAdminBaseUrl.value}/uploads/${vendorKey}/images/medium${file.path} 640w, ${getAdminBaseUrl.value}/uploads/${vendorKey}/images/large${file.path} 1024w"/><br>`;
            } else {
                form.value.attributes_description_popup[attributeId] += `<img src="${getAdminBaseUrl.value}${file.original_path}" loading="lazy" alt="${alt}" title="${alt}" width="${file.width}" height="${file.height}"/><br>`;
            }
        });
    });
}

const insertShortDescription = (data) => {
    data.media.forEach(file => {
        store.dispatch('media/fetchByField', {
            media_id: file.id,
            language_id: data.language_id,
        }).then((mediaData) => {
            let alt = '';
            if (mediaData && mediaData.alt) {
                alt = mediaData.alt;
            } else {
                alt = file.file_name;
            }

            if (file.path) {
                form.value.short_description += `<img src="${getAdminBaseUrl.value}/uploads/${vendorKey}/images/large${file.path}" loading="lazy" alt="${alt}" title="${alt}" width="${file.width}" height="${file.height}" sizes="(max-width: 640px) 300px, (max-width: 1024) 640px, 1024px" srcset="${getAdminBaseUrl.value}/uploads/${vendorKey}/images/thumbnail${file.path} 300w, ${getAdminBaseUrl.value}/uploads/${vendorKey}/images/medium${file.path} 640w, ${getAdminBaseUrl.value}/uploads/${vendorKey}/images/large${file.path} 1024w"/><br>`;
            } else {
                form.value.short_description += `<img src="${getAdminBaseUrl.value}${file.original_path}" loading="lazy" alt="${alt}" title="${alt}" width="${file.width}" height="${file.height}"/><br>`;
            }
        });
    });
}
const insertDescription = (data) => {
    data.media.forEach(file => {
        store.dispatch('media/fetchByField', {
            media_id: file.id,
            language_id: data.language_id,
        }).then((mediaData) => {
            let alt = '';
            if (mediaData && mediaData.alt) {
                alt = mediaData.alt;
            } else {
                alt = file.file_name;
            }

            if (file.path) {
                form.value.description += `<img src="${getAdminBaseUrl.value}/uploads/${vendorKey}/images/large${file.path}" loading="lazy" alt="${alt}" title="${alt}" width="${file.width}" height="${file.height}" sizes="(max-width: 640px) 300px, (max-width: 1024) 640px, 1024px" srcset="${getAdminBaseUrl.value}/uploads/${vendorKey}/images/thumbnail${file.path} 300w, ${getAdminBaseUrl.value}/uploads/${vendorKey}/images/medium${file.path} 640w, ${getAdminBaseUrl.value}/uploads/${vendorKey}/images/large${file.path} 1024w"/><br>`;
            } else {
                form.value.description += `<img src="${getAdminBaseUrl.value}${file.original_path}" loading="lazy" alt="${alt}" title="${alt}" width="${file.width}" height="${file.height}"/><br>`;
            }
        });
    });
}
const changeProductType = (type) => {
    if (type === 3) {
        form.value.regular_price = 0;
        form.value.gift_card_prices = {};
    } else {
        form.value.gift_card_prices = 0;
        form.value.regular_price = (form.value.regular_price > 0) ? form.value.regular_price : {};
    }
};

const emits = defineEmits([
    'update:modelValue',
    'submit',
    'fetch-variations',
    'dynamic-validation',
    'update-variant',
    'fetch-params',
    'fetch-by-field'
]);

emits('fetch-params')

const variableAttributesLength = computed(() => {
    return params.value.preparedAttributes.filter(attribute => attribute.logic === 0).length;
});

const auth = computed(() => store.getters['auth/getUser']);

const filteredCategories = computed(() => {
    if (!params.value.structuredCategories) return [];
    return params.value.structuredCategories.filter(item => form.value.category_ids.includes(item.value));
});

const cloneProduct = ref({
    popupOpen: false,
    sku: '',
    skuRules: ['required', 'maxLength:50'],
    gtin: '',
    gtinRules: ['maxLength:50'],
    cloned_product_id: null,
    errors: {}
})

const submitCloneProduct = async () => {
    try {
        const errors = validate(cloneProduct.value);
        if (Object.keys(errors).length > 0) {
            cloneProduct.value.errors = errors;
            return false;
        }
        const response = await store.dispatch('product/clone', {
            product_id: form.value.id,
            sku: cloneProduct.value.sku,
            gtin: cloneProduct.value.gtin,
        });

        cloneProduct.value.cloned_product_id = response.data.product_id;
        cloneProduct.value.sku = '';
        cloneProduct.value.gtin = '';

        emits('fetch-by-field')

        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully cloned'
        });
    } catch (error) {
        cloneProduct.value.errors = error;
    }
}

const submitCreateDraftProduct = async () => {
    try {
        await store.dispatch('product/clone', {
            product_id: form.value.id,
            is_draft: true,
            sku: `${form.value.sku}-drafting`,
        });

        emits('fetch-by-field')

        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully created'
        });
    } catch (error) {

    }
}

const selectAllAttributeValues = (options, variable) => {
    for (let i = 0; i < options.length; i++) {
        if (!form.value.attribute_type_variables[variable].includes(options[i].value)) {
            form.value.attribute_type_variables[variable].push(options[i].value)
        }
    }
}

const importType = ref({
    value: null,
    options: [
        {value: 2, label: 'Only update'},
    ],
    fileUploaded: false,
})

const importActions = ref(false);
const exportActions = ref(false);
const uploadFileInput = ref(null);

const upload = async () => {
    const formData = new FormData();
    formData.append('file', importType.value.fileUploaded);
    formData.append('import_type', importType.value.value);

    await store.dispatch(`product/uploadVariations`, formData);

    importType.value.value = null;
    importType.value.fileUploaded = false;
    uploadFileInput.value = '';
    importActions.value = false;

    store.commit('notification/SET_NOTIFICATION', {
        visible: true,
        title: 'Success',
        message: 'Successfully uploaded'
    });
};

const handleUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;
    importType.value.fileUploaded = file;
};

const exportFile = async () => {
    let ids = [form.value.id];
    let filename = `${form.value.base_slug ?? 'product-variations'}.xlsx`;

    const response = await store.dispatch('product/exportFileVariations', {
        withMeta: false,
        isAll: false,
        justTemplate: false,
        language_id: form.value.language_id ? form.value.language_id : 2,
        ids
    })

    const blob = new Blob([response.data], {type: response.headers['content-type']});
    const link = document.createElement('a');
    link.setAttribute("target", "_blank");
    link.href = window.URL.createObjectURL(blob);
    link.download = filename;
    link.click();
};

const insertMultiselectDescription = (data) => {
    data.media.forEach(file => {
        store.dispatch('media/fetchByField', {
            media_id: file.id,
            language_id: data.language_id,
        }).then((mediaData) => {
            let alt = '';
            if (mediaData && mediaData.alt) {
                alt = mediaData.alt;
            } else {
                alt = file.file_name;
            }

            form.value.multiselect.description += `<img src="${getAdminBaseUrl.value}/uploads/${vendorKey}/images/large${file.path}" loading="lazy" alt="${alt}" title="${alt}" width="${file.width}" height="${file.height}" sizes="(max-width: 640px) 300px, (max-width: 1024) 640px, 1024px" srcset="${getAdminBaseUrl.value}/uploads/${vendorKey}/images/thumbnail${file.path} 300w, ${getAdminBaseUrl.value}/uploads/${vendorKey}/images/medium${file.path} 640w, ${getAdminBaseUrl.value}/uploads/${vendorKey}/images/large${file.path} 1024w"/><br>`;
        });
    });
}

watch(() => form.value.product_type, (newType) => {
    if (newType === 1 || newType === 4 || newType === 3) {
        form.value.parentsRules = form.value.parentsRules.filter(rule => rule !== 'required');
        delete form.value.errors.parents;
    } else {
        form.value.parentsRules.push('required');
    }
}, {immediate: true});

watch(() => form.value.extra_products_product_ids, (values) => {
    let oldRequiredExtras = {...form.value.required_extras}
    let oldShowPricesExtras = {...form.value.show_prices_extra}
    form.value.required_extras = {};
    form.value.show_prices_extra = {};
    if (values.length) {
        for (let i = 0; i < values.length; i++) {
            if (oldRequiredExtras[values[i]] !== undefined) {
                form.value.required_extras[values[i]] = oldRequiredExtras[values[i]];
            } else {
                form.value.required_extras[values[i]] = false;
            }
            if (oldShowPricesExtras[values[i]] !== undefined) {
                form.value.show_prices_extra[values[i]] = oldShowPricesExtras[values[i]];
            } else {
                form.value.show_prices_extra[values[i]] = false;
            }
        }
    } else {
        form.value.required_extras = {};
        form.value.show_prices_extra = {};
    }
});

watch(() => form.value.watermark_settings, (newMedia) => {
    watermark_media.value = newMedia.watermark_media ? [{"type": "images", "path": newMedia.watermark_media}] : '';
}, {immediate: true});

const generalParams = computed(() => store.getters['general/getParams']);

const newTieredPrice = ref(null);

const finishNewTieredPrice = () => {
    form.value.tiered_prices.unshift(newTieredPrice.value);
    newTieredPrice.value = null;

    store.commit('notification/SET_NOTIFICATION', {
        visible: true,
        title: 'Success',
        message: 'Successfully added and ready for save'
    });
}

const tieredPricesModalByVariant = ref({
    variant_id: null,
    openStatus: false
});

const b2bOptions = [
    {value: 0, label: 'B2C'},
    {value: 1, label: 'B2B'},
    {value: 2, label: 'B2C & B2B'},
];

const isAiTranslateExpanded = ref(false);
const toggleAiTranslate = () => {
    isAiTranslateExpanded.value = !isAiTranslateExpanded.value;
};

const selectedLanguagesForTranslation = ref([]);
const generateAITranslations = async (isAll = false) => {
    await store.dispatch('product/translateAI', {
        translation_id: form.value.translation_id,
        language_ids: isAll ? [] : selectedLanguagesForTranslation.value,
    })

    if (!isAll) selectedLanguagesForTranslation.value = [];

    store.commit('notification/SET_NOTIFICATION', {
        visible: true,
        title: 'Success',
        message: `Successfully submitted (Will work in background)`
    });
}

const approveTranslation = async () => {
    await store.dispatch('product/approveTranslation', {
        translation_id: form.value.translation_id,
    })
    form.value.approved = true;

    store.commit('notification/SET_NOTIFICATION', {
        visible: true,
        title: 'Success',
        message: `Successfully approved`
    });
}

const acceptDraftPopup = ref(false);
const forceDeleteDraftPopup = ref(false);

const acceptDraftAsActual = async () => {
    try {
        await store.dispatch('product/publishDraftAsActual', {
            product_id: form.value.id,
            drafted_product_id: form.value.drafted_product.id,
        });

        emits('fetch-by-field')

        acceptDraftPopup.value = false;

        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully published'
        });
    } catch (error) {

    }
}

const forceDeleteDraft = async () => {
    try {
        await store.dispatch('product/forceDeleteDraft', {
            drafted_product_id: form.value.drafted_product.id,
        });

        emits('fetch-by-field')

        forceDeleteDraftPopup.value = false;

        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully deleted'
        });
    } catch (error) {

    }
}

const insertReel = (data, formKey = 'reel') => {
    data.media.forEach(mediaItem => {
        if (mediaItem.id) {
            form.value[formKey].push(mediaData(mediaItem));
        }
    });
}

const removeReel = (id, removeKey = 'removeReel') => {
    if (id) {
        form.value[removeKey].push(id);
    }
}

</script>

<template>
    <template v-if="tieredPricesModalByVariant.openStatus">
        <PopupWithSlot
            classes="max-w-[80%] w-[80%]"
            @close="tieredPricesModalByVariant = {
                variant_id: null,
                openStatus: false
            }"
        >
            <TieredPricesForVariants :variant-id="tieredPricesModalByVariant.variant_id"/>
        </PopupWithSlot>
    </template>

    <template v-if="cloneProduct.popupOpen">
        <PopupWithSlot
            classes="max-w-[80%] w-[80%]"
            @close="cloneProduct.popupOpen = false, cloneProduct.cloned_product_id = null"
        >
            <div
                v-if="Object.keys(cloneProduct.errors).length > 0 && cloneProduct.errors.general"
                class="grid grid-cols-1 gap-9 p-6.5"
            >
                <AlertError :errors="cloneProduct.errors.general"/>
            </div>
            <template v-if="cloneProduct.cloned_product_id">
                <div class="flex justify-center">
                    <div>
                        <a
                            class="ml-2 inline-block"
                            :href="'/catalog/products/update/'+ cloneProduct.cloned_product_id +'/-1'"
                            target="_blank"
                        >
                    <span
                        class="flex items-center gap-2 rounded bg-meta-3 py-3 px-4.5 font-medium text-white hover:bg-opacity-80"
                    >
                        <font-awesome-icon :icon="['fasr', 'eye']"/>
                        Show cloned product
                    </span>
                        </a>
                    </div>
                    <div class="ml-2">
                        <CustomButton
                            @click="cloneProduct.cloned_product_id = null"
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
                <div class="grid grid-cols-2 gap-9">
                    <div class="flex flex-col p-6.5">
                        <CustomInput
                            v-model="cloneProduct.sku"
                            name="sku"
                            label="SKU *"
                            type="text"
                            placeholder="Enter SKU"
                            @keyup="cloneProduct.errors = validate(cloneProduct)"
                            :error="cloneProduct.errors['sku']"
                        />
                    </div>
                    <div class="flex flex-col p-6.5">
                        <CustomInput
                            v-model="cloneProduct.gtin"
                            name="gtin"
                            label="GTIN"
                            type="text"
                            placeholder="Enter GTIN"
                            @keyup="cloneProduct.errors = validate(cloneProduct)"
                            :error="cloneProduct.errors['gtin']"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-9">
                    <div class="flex flex-col">

                    </div>
                    <div class="flex flex-col items-end">
                        <CustomButton
                            @click="submitCloneProduct"
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

    <template v-if="acceptDraftPopup">
        <PopupWithSlot
            classes="max-w-[50%] w-[50%]"
            @close="acceptDraftPopup = false"
        >
            <span class="mx-auto inline-block p-4 rounded-full bg-wrong-color">
                <font-awesome-icon :icon="['far', 'triangle-exclamation']" class="text-danger" size="2x"/>
            </span>
            <h3 class="mt-5.5 pb-2 text-xl font-bold text-black sm:text-2xl">
                Are you sure?
            </h3>
            <p class="mb-10 font-medium">
                Are you sure you want to publish this draft product? Once published, it cannot be reverted.
            </p>
            <div class="-mx-3 flex flex-wrap gap-y-4">
                <div class="w-full px-3 2xsm:w-1/2">
                    <button
                        type="button"
                        @click="acceptDraftPopup = false"
                        class="block w-full rounded border border-stroke bg-gray p-3 text-center font-medium text-black hover:bg-opacity-60"
                    >
                        Cancel
                    </button>
                </div>
                <div class="w-full px-3 2xsm:w-1/2">
                    <button
                        type="button"
                        @click="acceptDraftAsActual()"
                        class="block w-full rounded border bg-meta-3 p-3 text-center font-medium text-white hover:bg-opacity-90"
                    >
                        Yes, publish!
                    </button>
                </div>
            </div>
        </PopupWithSlot>
    </template>

    <template v-if="forceDeleteDraftPopup">
        <PopupWithSlot
            classes="max-w-[50%] w-[50%]"
            @close="forceDeleteDraftPopup = false"
        >
            <span class="mx-auto inline-block p-4 rounded-full bg-wrong-color">
                <font-awesome-icon :icon="['far', 'triangle-exclamation']" class="text-danger" size="2x"/>
            </span>
            <h3 class="mt-5.5 pb-2 text-xl font-bold text-black sm:text-2xl">
                Are you sure?
            </h3>
            <p class="mb-10 font-medium">
                Are you sure you want to permanently delete this draft product? This action cannot be undone.
            </p>
            <div class="-mx-3 flex flex-wrap gap-y-4">
                <div class="w-full px-3 2xsm:w-1/2">
                    <button
                        type="button"
                        @click="forceDeleteDraftPopup = false"
                        class="block w-full rounded border border-stroke bg-gray p-3 text-center font-medium text-black hover:bg-opacity-60"
                    >
                        Cancel
                    </button>
                </div>
                <div class="w-full px-3 2xsm:w-1/2">
                    <button
                        type="button"
                        @click="forceDeleteDraft()"
                        class="block w-full rounded border border-meta-1 bg-meta-1 p-3 text-center font-medium text-white hover:bg-opacity-90"
                    >
                        Yes, delete!
                    </button>
                </div>
            </div>
        </PopupWithSlot>
    </template>
    <form @submit.prevent="emits('submit'), justSubmitted = true">
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 gap-9 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>
        <div class="p-6 max-md:p-4">
            <div class="flex flex-col max-w-[300px]">
                <CustomSelect
                    label="Languages"
                    v-model="form.language_id"
                    mode="single"
                    placeholder="Select languages"
                    :disabled="emitAction === 'create'"
                    :options="params.languages"
                    :searchable="true"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    @update:modelValue="form.errors = validate(form), activeTab = 'general';"
                    :error="form.errors['language_id']"
                />
            </div>
            <div
                v-if="emitAction === 'update'"
                class="grid grid-cols-1 gap-9 mt-3"
            >
                <div class="flex flex-col">
                    <template v-if="auth.user_group.permissions_by_name.reviews[0].can_view">
                        <a
                            target="_blank"
                            class="hover-trigger hover:text-primary"
                            :href="getAdminBaseUrl + '/crm/reviews?product_id=' + form.id"
                        >
                            <span class="font-semibold text-black">{{ form.reviews_count }} </span>
                            <span class="text-md font-semibold text-black"> Reviews</span>
                            <font-awesome-icon
                                class="text-black-2 ml-2"
                                :icon="['fass', 'up-right-from-square']"
                            />
                        </a>
                    </template>
                    <template v-else>
                        <span class="font-semibold text-black">{{ form.reviews_count }} </span>
                        <span class="text-md"> Reviews</span>
                    </template>

                </div>
            </div>
            <template v-if="form.slug">
                <div class="mt-1">
                    <a
                        target="_blank"
                        class="hover-trigger hover:text-primary"
                        :href="form.front_url_view"
                    >
                        <span class="font-semibold text-black">{{ form.front_url_view }}</span>
                    </a>
                </div>
            </template>


        </div>
        <div class="flex flex-col px-6 max-md:px-4" v-if="form.translation_id">
            <div class="pb-6 max-md:pb-4">
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
            <div class="pb-3" :class="{ 'hidden': !isAiTranslateExpanded }">
                <template v-if="form.approved">
                    <h2 class="text-black mt-2 font-bold">You can generate AI translations from this language to the
                        missing ones.</h2>
                    <div class="flex mt-2 gap-4 flex-wrap">
                        <div>
                            <CustomButton
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                type="button"
                                @click="generateAITranslations(true)"
                                class="flex items-center gap-2 rounded bg-primary py-4 px-4.5 font-medium text-white hover:bg-opacity-80"
                            >
                                <font-awesome-icon :icon="['fas', 'robot']"/>
                                Generate AI translations (all missing)
                            </CustomButton>
                        </div>
                        <div class="flex gap-2 flex-wrap">
                            <div class="w-[300px] max-xsm:w-[100%]">
                                <CustomSelect
                                    v-model="selectedLanguagesForTranslation"
                                    mode="tags"
                                    placeholder="Select languages"
                                    :disabled="emitAction === 'create'"
                                    :options="params.languages"
                                    :invalid-feedback-place="false"
                                    :close-on-select="false"
                                    :searchable="true"
                                    :with-general="false"
                                    class="py-2 rounded-lg border-stroke bg-transparent w-full"
                                />
                            </div>
                            <div class="">
                                <CustomButton
                                    :title="!selectedLanguagesForTranslation.length ? 'First select languages' : ''"
                                    :disabled="(emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit) || !selectedLanguagesForTranslation.length"
                                    type="button"
                                    @click="generateAITranslations()"
                                    class="flex items-center gap-2 rounded bg-primary py-4 px-4.5 font-medium text-white hover:bg-opacity-80"
                                >
                                    <font-awesome-icon :icon="['fas', 'robot']"/>
                                    Generate AI translations for selected languages
                                </CustomButton>
                            </div>
                        </div>

                    </div>
                </template>
                <template v-else>
                    <h2 class="text-black mt-2 font-bold">This translation was generated by AI. Please approve it if
                        everything looks correct.</h2>
                    <div class="flex mt-2">
                        <div>
                            <CustomButton
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
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

        <hr class="text-gray">
        <div class="grid grid-cols-1 gap-9">
            <div class="w-full p-7.5 max-md:p-4">
                <div class="overflow-x-auto mb-6">
                    <div class="flex gap-9 border-b border-stroke">
                        <template
                            :key="key"
                            v-for="(tabRoute, key) in tabsRoutes"
                        >
                            <router-link
                                v-if="tabRoute.key === 'general' ||
                                      tabRoute.key === 'bundling' ||
                                      tabRoute.key === 'accesses' ||
                                      tabRoute.key === 'attributes' ||
                                      (form.language_id == -1 && (
                                      tabRoute.key === 'inventory' ||
                                      (tabRoute.key === 'gift_card_prices' && form.product_type === 3) ||
                                      tabRoute.key === 'gallery' ||
                                      tabRoute.key === 'linked_products' ||
                                      tabRoute.key === 'tools')) ||
                                      (tabRoute.key === 'b2b' && generalParams?.vendor.b2b && form.language_id == -1 && emitAction === 'update') ||
                                      (tabRoute.key === 'custom_fields' && form.language_id > -1) ||
                                      (tabRoute.key === 'seo' && form.language_id > -1) ||
                                      (tabRoute.key === 'gallery_translation' && form.language_id > -1) ||
                                      (tabRoute.key === 'contents' && form.language_id > -1) ||
                                      (tabRoute.key === 'variations' && emitAction === 'update' && form.product_type == params.productTypeWithKeyOfName?.variable) ||
                                      (tabRoute.key === 'watermark_image' && emitAction === 'update' && form.product_type == params.productTypeWithKeyOfName?.variable) ||
                                      (tabRoute.key === 'multiselect' && form.product_type == params.productTypeWithKeyOfName?.with_multiselect)
"
                                to=""
                                @click="activeTab = tabRoute.key"
                                :class="{
                                    'text-danger border-danger': tabsWithErrors.includes(tabRoute.key),
                                    'text-primary border-primary': activeTab === tabRoute.key && !tabsWithErrors.includes(tabRoute.key),
                                    'border-transparent': activeTab !== tabRoute.key
                                }"
                                class="border-b-2 shrink-0 py-4 text-sm font-medium hover:text-primary md:text-base px-2"
                            >
                                <font-awesome-icon :icon="tabRoute.icon"/>
                                {{ tabRoute.title }}
                            </router-link>
                        </template>
                    </div>
                </div>
                <div v-if="activeTab === 'general'">
                    <template v-if="form.language_id == -1">
                        <div
                            class="grid grid-cols-6 p-6 gap-6 max-xl:grid-cols-4 max-xl:py-4 max-xl:px-0 max-md:grid-cols-2 max-md:gap-4 max-xsm:grid-cols-1">
                            <div class="flex flex-col col-span-2 max-xsm:col-span-1">
                                <CustomSelect
                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                    v-model="form.category_ids"
                                    @update:modelValue="form.errors = validate(form)"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    :searchable="true"
                                    mode="tags"
                                    label="Categories *"
                                    placeholder="Select categories"
                                    :options="params.structuredCategories"
                                    :show-labels="true"
                                    :close-on-select="false"
                                    :canClear="false"
                                    :error="form.errors['category_ids']"
                                />
                            </div>
                            <div class="flex flex-col col-span-2 max-xsm:col-span-1">
                                <CustomSelect
                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                    v-model="form.primary_category_id"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    mode="single"
                                    label="Primary category *"
                                    placeholder="Select primary"
                                    :options="filteredCategories"
                                    :show-labels="true"
                                    :canClear="false"
                                />
                            </div>
                            <div class="flex flex-col ">
                                <CustomSelect
                                    label="Product type *"
                                    v-model="form.product_type"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    mode="single"
                                    :canClear="false"
                                    placeholder="Select"
                                    :options="params.productTypes"
                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                    @change="(event) => changeProductType(event)"
                                    @update:modelValue="form.errors = validate(form)"
                                    :error="form.errors['product_type']"
                                />
                            </div>
                            <div class="flex flex-col ">
                                <CustomSelect
                                    label="Tax status *"
                                    v-model="form.tax_status"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    mode="single"
                                    :canClear="false"
                                    placeholder="Select"
                                    :options="taxableOptions"
                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                    @update:modelValue="form.errors = validate(form)"
                                    :error="form.errors['tax_status']"
                                />
                            </div>
                            <template v-if="form.product_type != 3">
                                <div class="flex flex-col">
                                    <CustomInput
                                        v-model="form.regular_price"
                                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                        name="regular_price"
                                        label="Regular price *"
                                        type="text"
                                        placeholder="Enter regular price"
                                        @keyup="form.errors = validate(form)"
                                        :error="form.errors['regular_price']"
                                    />
                                </div>
                                <div class="flex flex-col">
                                    <CustomInput
                                        v-model="form.sales_price"
                                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                        name="sales_price"
                                        label="Sales price"
                                        type="text"
                                        placeholder="Enter sales price"
                                    />
                                </div>
                                <div class="flex flex-col"
                                     v-if="form.product_type == params.productTypeWithKeyOfName?.with_multiselect">
                                    <Switch
                                        @change="(value) => {
                                        form.overwrite_price = value;
                                    }"
                                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                        :value="form.overwrite_price"
                                        id="overwrite_price"
                                        label="Overwrite price"
                                    />
                                </div>
                                <div class="flex flex-col">
                                    <CustomInput
                                        v-model="form.hs_code"
                                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                        name="hs_code"
                                        label="HS Code"
                                        type="text"
                                        placeholder="HS Code"
                                    />
                                </div>
                            </template>
                            <div class="flex flex-col  ">
                                <Switch
                                    @change="(value) => {
                                        form.extra_product = value;
                                    }"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    :value="form.extra_product"
                                    id="extra_product"
                                    label="Is extra product"
                                />
                            </div>
                            <div class="flex flex-col ">
                                <Switch
                                    @change="(value) => {
                                        form.variants_export_into_feed = value;
                                    }"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    :value="form.variants_export_into_feed"
                                    id="Variants_export_into_feed"
                                    label="Variants export into feed"
                                />
                            </div>
                        </div>


                    </template>
                    <template v-else>
                        <div class="grid grid-cols-2 gap-9">
                            <div class="flex flex-col p-6.5 pb-0">
                                <CustomInput
                                    v-model="form.name"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    name="name"
                                    label="Name *"
                                    type="text"
                                    placeholder="Enter name"
                                    @keyup="form.errors = validate(form)"
                                    :error="form.errors['name']"
                                />
                            </div>
                            <div class="flex flex-col p-6.5 pb-0">
                                <div>
                                    <CustomInput
                                        v-model="form.slug"
                                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                        name="slug"
                                        label="Slug"
                                        type="text"
                                        placeholder="Enter or will generate automatically"
                                        :error="form.errors['slug']"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-9">
                            <div class="flex flex-col p-6.5 pb-0">
                                <div>
                                    <CustomInput
                                        v-model="form.sub_name"
                                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                        name="sub_name"
                                        label="Sub name"
                                        type="text"
                                        placeholder="Enter sub name"
                                        @keyup="form.errors = validate(form)"
                                        :error="form.errors['sub_name']"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-9">
                            <div class="flex flex-col p-6.5 pb-0">
                                <label class="mb-2.5 block font-medium text-black">Short
                                    description</label>
                                <template
                                    v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.products[0].can_edit">
                                    <CustomMediaList
                                        label="Insert media"
                                        @insert="insertShortDescription"
                                        :images="[]"
                                        :types="['images']"
                                        :button="true"
                                        :languageId="form.language_id"
                                    />
                                </template>
                                <CKEditorComponent
                                    :model="form.short_description"
                                    @updateValue="(value) => {
                                        form.short_description = value
                                    }"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                />
                            </div>
                        </div>
                        <div class="flex flex-col p-6.5 pb-0">
                            <label class="mb-2.5 block font-medium text-black">Description</label>
                            <template
                                v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.products[0].can_edit">
                                <CustomMediaList
                                    label="Insert media"
                                    @insert="insertDescription"
                                    :images="[]"
                                    :types="['images']"
                                    :button="true"
                                    :languageId="form.language_id"
                                />
                            </template>
                            <CKEditorComponent
                                :model="form.description"
                                @updateValue="(value) => {
                                        form.description = value
                                    }"
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                            />
                        </div>
                    </template>
                </div>
                <div v-else-if="activeTab === 'gift_card_prices'">
                    <div class="grid grid-cols-2">
                        <div class="flex flex-col p-6.5 pb-0">
                            <CustomSelect
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                                v-model="form.currency_id"
                                label="Currencies"
                                placeholder="Select currencies"
                                :searchable="true"
                                :options="params.currencies"
                                :close-on-select="false"
                                :canClear="false"
                            />
                        </div>
                        <div class="flex flex-col p-6.5 pb-0" v-if="form.currency_id">
                            <CustomInput
                                v-model="form.gift_card_prices[form.currency_id]"
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                name="gift_card_prices"
                                label="Gift card prices *"
                                type="text"
                                placeholder="Enter gift card prices (price1|price2|price3)"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['gift_card_prices']"
                            />
                        </div>
                    </div>
                </div>
                <div v-else-if="activeTab === 'inventory'">
                    <div
                        class="grid grid-cols-4 p-6 gap-6 max-xl:py-4 max-xl:px-0 max-md:grid-cols-2 max-md:gap-4 max-sm:grid-cols-1">
                        <div class="flex flex-col">
                            <CustomInput
                                v-model="form.sku"
                                name="sku"
                                label="SKU *"
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                type="text"
                                placeholder="Enter SKU"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['sku']"
                            />
                        </div>
                        <div class="flex flex-col">
                            <CustomInput
                                v-model="form.gtin"
                                name="gtin"
                                label="Gtin"
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                type="text"
                                placeholder="Enter gtin"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['gtin']"
                            />
                        </div>
                        <div class="flex flex-col">
                            <CustomSelect
                                label="Stock status *"
                                v-model="form.stock_status"
                                mode="single"
                                :canClear="false"
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                placeholder="Select"
                                :options="stockStatuses"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                                @update:modelValue="form.errors = validate(form)"
                                :error="form.errors['stock_status']"
                            />
                        </div>
                        <div class="flex flex-col">
                            <Switch
                                @change="(value) => {
                                        form.independent_stock_status = value;
                                    }"
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                :value="form.independent_stock_status"
                                id="independent_stock_status"
                                label="Independent stock status"
                            />
                        </div>
                        <div class="flex flex-col col-span-4 max-md:col-span-2 max-sm:col-span-1 ">
                            <CustomTextarea
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                :label="`Item's SKUs ${form.product_type !== 4 && form.product_type !== 1  && form.product_type !== 3 ? '*' : ''}`"
                                name="parents"
                                :rows="1"
                                v-model="form.parents"
                                placeholder="Example: 9x4064824550895 OR 9x4064824550895,9x4064824550888,17x4064824559478"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['parents']"
                            />
                        </div>
                    </div>
                </div>
                <div v-else-if="activeTab === 'multiselect'">
                    <div
                        class="bg-amber-200 px-6.5 py-2.5"
                    >
                        <font-awesome-icon :icon="['far', 'triangle-exclamation']"/>
                        <template v-if="form.language_id == -1">
                            For block title, description and options title you need to change Language and translate for
                            each language
                        </template>
                        <template v-else>
                            For creating options with general info or removing them, you need to go General info
                        </template>
                    </div>
                    <div class="grid grid-cols-4 gap-9" v-if="form.language_id == -1">
                        <div class="flex flex-col p-6.5 pb-0 col-span-1">
                            <CustomInput
                                v-model="form.multiselect.options_limit"
                                name="options_limit"
                                label="Options limit"
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                type="number"
                                placeholder="Enter limit"
                            />
                        </div>
                        <div class="flex flex-col p-6.5 pb-0 col-span-3">
                            <CustomButton
                                type="button"
                                @click="() => {
                                    form.multiselect.options.push({
                                       parents: null,
                                       additional_price: 0,
                                       media_id: null
                                    });
                                }"
                                class="flex items-center gap-2 rounded bg-meta-3 py-2 px-4.5 mt-5 font-medium text-white hover:bg-opacity-80 ml-auto"
                            >
                                <font-awesome-icon :icon="'plus'"/>
                                Add new option
                            </CustomButton>
                        </div>
                    </div>
                    <div class="grid grid-cols-4 gap-9" v-if="form.language_id > -1">
                        <div class="flex flex-col p-6.5 pb-0 col-span-1">
                            <CustomInput
                                v-model="form.multiselect.title"
                                name="multiselect_title"
                                label="Title *"
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                type="text"
                                placeholder="Enter title"
                            />
                        </div>
                        <div class="flex flex-col p-6.5 pb-0 col-span-3">
                            <div class="flex flex-col pb-0">
                                <label class="mb-2.5 block font-medium text-black">Description</label>
                                <CustomMediaList
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    label="Insert media"
                                    @insert="insertMultiselectDescription"
                                    :images="[]"
                                    :types="['images']"
                                    :button="true"
                                />
                                <CKEditorComponent
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    :model="form.multiselect.description"
                                    @updateValue="(value) => {
                                        form.multiselect.description = value
                                    }"
                                />
                            </div>
                        </div>
                    </div>
                    <template v-for="(multiOption, multiOptionIndex) in form.multiselect.options"
                              :key="multiOptionIndex">
                        <div class="mt-5" v-if="!multiOption.deleted">
                            <div class="flex justify-between" v-if="form.language_id == -1">
                                <div></div>
                                <div class="flex items-center">
                                    <router-link
                                        @click="multiOption.deleted = true"
                                        title="Delete"
                                        :to="''"
                                        class="flex items-center cursor-pointer w-[27px] h-[27px] hover-border-white-trigger bg-gray border border-stroke border-r-0 border-b-0 text-white text-center font-medium hover:bg-danger hover:border-danger hover:text-white"
                                    >
                                        <font-awesome-icon :icon="['fas', 'xmark']" class="w-full text-black mr-auto"/>
                                    </router-link>
                                </div>
                            </div>
                            <div class="grid grid-cols-6 gap-9 border-gray border rounded"
                                 v-if="form.language_id == -1">
                                <div class="flex flex-col p-6.5 col-span-2">
                                    <CustomMediaList
                                        @remove-images="() => {
                                                multiOption.media_id = null
                                                multiOption.media = [];
                                        }"
                                        label="Image *"
                                        @insert="(data) => {
                                            data.media.forEach(mediaItem => {
                                                if (mediaItem.id) {
                                                    multiOption.media_id = mediaItem.id
                                                    multiOption.media = [mediaData(mediaItem)];
                                                }
                                            });
                                        }"
                                        :images="multiOption.media ? multiOption.media : []"
                                        :types="['images']"
                                        mode="single"
                                    />
                                </div>
                                <div class="flex flex-col p-6.5 pb-0 col-span-1">
                                    <CustomInput
                                        v-model="multiOption.additional_price"
                                        name="additional_price"
                                        label="Additional price"
                                        type="text"
                                        placeholder="Enter price"
                                    />
                                </div>
                                <div class="flex flex-col p-6.5 pb-0 col-span-3">
                                    <div>
                                        <TooltipOne
                                            :button-params="{showingType: 'info'}"
                                            :tooltip-text="'Need after every SKU write =x{qty}, and if it is not last, add %<br>Examples:<br>Has 1 parent:<br>9x4064824550895<br>Has multiple parents<br>9x4064824550895,9x4064824550888,17x4064824559478'"
                                            tooltipClass="rounded-md bg-primary py-2 px-2 mx-3 mb-2"
                                        />
                                        <CustomTextarea
                                            :disabled="!auth.user_group.permissions_by_name.products[0].can_edit"
                                            label="Item's SKUs *"
                                            name="parents"
                                            :rows="2"
                                            v-model="multiOption.parents"
                                            placeholder="Example: 9x4064824550895 OR 9x4064824550895,9x4064824550888,17x4064824559478"
                                            @keyup="dynamicValidation(multiOptionIndex, 'parents', {parents: multiOption.parents, parentsRules: ['required', 'skuFormat']}, 'multiselectProductErrors')"
                                            :error="multiselectProductErrors[multiOptionIndex]?.['parents'] ?? null"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-4 gap-9 border-gray border rounded" v-if="form.language_id > -1">
                                <div class="flex flex-col p-6.5 pb-0 col-span-1">
                                    <img :src="multiOption.media[0].path" alt="" class="max-w-[200px]">
                                </div>
                                <div class="flex flex-col p-6.5 pb-0 col-span-3">
                                    <CustomInput
                                        v-model="multiOption.title"
                                        name="option_title"
                                        label="Title *"
                                        type="text"
                                        placeholder="Enter title"
                                    />
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div v-else-if="activeTab === 'attributes'">
                    <template v-if="form.language_id == -1">
                        <div class="grid grid-cols-2 p-6 gap-6 max-xl:px-0 max-md:gap-4 max-md:grid-cols-1">
                            <div class="flex flex-col" v-if="form.product_type == 1">
                                <CustomSelect
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                    v-model="showingAttributeIds.variable"
                                    mode="tags"
                                    :searchable="true"
                                    label="Variable attributes"
                                    :placeholder="`Select variable attributes`"
                                    :options="params.attributeTypesAsParamVariables"
                                    :show-labels="true"
                                    :close-on-select="false"
                                    :canClear="false"
                                />
                            </div>
                            <div class="flex flex-col">
                                <CustomSelect
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                    :searchable="true"
                                    v-model="showingAttributeIds.simple"
                                    mode="tags"
                                    label="Simple attributes"
                                    :placeholder="`Select simple attributes`"
                                    :options="params.attributeTypesAsParamSimples"
                                    :show-labels="true"
                                    :close-on-select="false"
                                    :canClear="false"
                                />
                            </div>
                        </div>
                        <hr class="text-gray m-4 max-md:m-2">
                        <template v-if="form.product_type == 1 && showingAttributeIds.variable.length">
                            <div class="p-6 max-xl:px-0">
                                <h3 class="text-black text-title-lg font-bold">Variable attribute values</h3>
                            </div>
                            <div class="grid gap-6 px-6 max-xl:px-0">
                                <template
                                    :key="key"
                                    v-for="(attribute, key) in params.preparedAttributes"
                                >
                                    <template
                                        v-if="attribute.logic === 0 && showingAttributeIds.variable.includes(attribute.variable_name)">
                                        <div class="flex flex-col pb-0">
                                            <div class="flex">
                                                <div class="w-full relative absolute-holder-for-label">
                                                    <CustomSelect
                                                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                                        class="py-2 rounded-lg border-stroke bg-transparent"
                                                        v-model="form.attribute_type_variables[attribute.variable_name]"
                                                        mode="tags"
                                                        :searchable="true"
                                                        :label="attribute.label + 's'"
                                                        :placeholder="`Select ${attribute.label}`"
                                                        :options="attribute.options"
                                                        :show-labels="true"
                                                        :close-on-select="false"
                                                        :canClear="false"
                                                    />
                                                    <div class="absolute top-0 right-0">
                                                        <CustomButton
                                                            @click="selectAllAttributeValues(attribute.options, attribute.variable_name)"
                                                            type="button"
                                                            class="flex h-fit items-center rounded bg-primary mt-auto py-0.5 mb-auto px-4.5 font-medium text-white hover:bg-opacity-80 max-sm:px-2"
                                                        >
                                                            <div class="flex items-center">
                                                                All
                                                                <font-awesome-icon
                                                                    class="ml-2 max-sm:w-[9px] max-sm:ml-1"
                                                                    :icon="['fas', 'check']"/>
                                                            </div>
                                                        </CustomButton>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </template>
                            </div>
                        </template>
                        <template v-if="showingAttributeIds.simple.length">
                            <div class="grid grid-cols-1 gap-9">
                                <h3 class="p-6.5 pb-0 text-black text-title-lg font-bold">Simple attribute values</h3>
                            </div>
                            <hr class="text-gray mt-6.5">
                            <div class="grid grid-cols-3 gap-9">
                                <template
                                    :key="key"
                                    v-for="(attribute, key) in params.preparedAttributes"
                                >
                                    <template
                                        v-if="attribute.logic === 1 && showingAttributeIds.simple.includes(attribute.variable_name)">
                                        <div class="flex flex-col p-6.5 pb-0 col-span-1">
                                            <CustomSelect
                                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                                class="py-2 rounded-lg border-stroke bg-transparent"
                                                v-model="form.attribute_type_variables[attribute.variable_name]"
                                                mode="tags"
                                                :searchable="true"
                                                :label="attribute.label + 's'"
                                                :placeholder="`Select ${attribute.label}`"
                                                :options="attribute.options"
                                                :show-labels="true"
                                                :close-on-select="false"
                                                :canClear="false"
                                            />
                                        </div>
                                    </template>
                                </template>
                            </div>
                        </template>
                    </template>
                    <template v-else>
                        <template v-if="form.product_type == 1 && showingAttributeIds.variable.length">
                            <div class="grid grid-cols-1">
                                <div class="flex flex-col p-6.5 pb-0 text-title-xl text-black font-bold">
                                    Attribute's descriptions
                                </div>
                            </div>
                            <div class="grid grid-cols-1">
                                <div class="flex flex-col p-6.5 pb-0">
                                    <div
                                        class="border border-stroke grid grid-cols-4"
                                    >
                                        <div
                                            class="flex flex-col px-6.5 py-4 border-r-2 border-stroke text-title-md text-black font-bold col-span-1">
                                            Attribute
                                        </div>
                                        <div
                                            class="flex flex-col px-6.5 py-4 text-title-md text-black font-bold col-span-3">
                                            Description
                                        </div>
                                    </div>
                                    <template
                                        v-for="(attribute, attrKey, index) in params.preparedAttributes" :key="attrKey"
                                        :class="{'border-t-0': index}">
                                        <div
                                            class="border border-stroke grid grid-cols-4"
                                            v-if="attribute.logic === 0 && showingAttributeIds.variable.includes(attribute.variable_name)"
                                        >
                                            <div class="flex flex-col px-6.5 py-4 border-r-2 border-stroke">
                                                {{ attribute.label }}
                                            </div>
                                            <div class="flex flex-col px-6.5 py-4 col-span-3">
                                                <CustomMediaList
                                                    label="Insert media"
                                                    @insert="(data) => {
                                                        insertMediaToAttributesDescriptions(data, attribute.id)
                                                    }"
                                                    :images="[]"
                                                    :types="['videos', 'images']"
                                                    :button="true"
                                                    :languageId="form.language_id"
                                                />
                                                <CKEditorComponent
                                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                                    :model="form.attributes_description_popup[attribute.id]"
                                                    @updateValue="(value) => {
                                                        form.attributes_description_popup[attribute.id] = value
                                                    }"
                                                />
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </template>
                </div>
                <div v-else-if="activeTab === 'gallery'">
                    <template
                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.products[0].can_edit">
                        <div class="p-6 max-xl:py-4 max-xl:px-0">
                            <div class="grid grid-cols-5 gap-6 max-md:gap-4 max-xl:grid-cols-6 max-md:grid-cols-1">
                                <div class="max-xl:col-span-2 max-md:col-span-1 max-md:max-w-[250px]">
                                    <CustomMediaList
                                        @remove-images="removeSingleGallery"
                                        label="Base image"
                                        @insert="insert"
                                        :images="media ? media : []"
                                        :types="['videos', 'images']"
                                        mode="single"
                                    />
                                </div>
                                <div class="col-span-4 max-md:col-span-1">
                                    <CustomMediaList
                                        label="Gallery"
                                        @remove-images="removeGallery"
                                        @insert="insert"
                                        :images="form.gallery ? form.gallery : []"
                                        :types="['videos', 'images']"
                                        :videoUrl="true"
                                    />
                                </div>
                                <div class="col-span-5 max-xl:col-span-6 max-md:col-span-1">
                                    <CustomTextarea
                                        :disabled="!auth.user_group.permissions_by_name.products[0].can_edit"
                                        label="Shorts"
                                        name="shorts"
                                        :rows="2"
                                        v-model="form.shorts"
                                        placeholder="Shorts links separated by comma (,)"
                                    />
                                </div>
                            </div>
                        </div>


                    </template>
                </div>
                <div v-else-if="activeTab === 'watermark_image'">
                    <div class="p-6 max-xl:px-0 max-xl:py-4">
                        <div class="grid grid-cols-4 gap-6 max-md:gap-4 mb-4 max-lg:grid-cols-5 max-md:grid-cols-1">
                            <template
                                v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.products[0].can_edit">
                                <div class="max-lg:col-span-2 max-md:col-span-1 max-md:max-w-[350px]">
                                    <CustomMediaList
                                        @remove-images="removeSingleWatermark"
                                        label="Watermark image"
                                        @insert="insertWatermark"
                                        :images="watermark_media ? watermark_media  : []"
                                        :types="['images']"
                                        mode="single"
                                    />
                                </div>
                            </template>
                            <div
                                class="grid grid-cols-4 gap-6 max-md:gap-4 col-span-3 max-lg:grid-cols-2 max-md:col-span-1 max-xsm:grid-cols-1">
                                <CustomInput
                                    v-model="form.watermark_settings.watermark_height"
                                    name="watermark_height"
                                    label="Height"
                                    type="text"
                                    placeholder="Enter height"
                                    @update:modelValue="form.errors = validate(form.watermark_settings)"
                                    :error="form.errors['watermark_height']"
                                />
                                <CustomSelect
                                    label="Position"
                                    v-model="form.watermark_settings['watermark_position']"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    mode="single"
                                    :canClear="false"
                                    placeholder="Select"
                                    :options="['top-left', 'top-right', 'bottom-left', 'bottom-right', 'center']"
                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                />
                                <CustomInput
                                    v-model="form.watermark_settings['watermark_x']"
                                    name="watermark_x"
                                    label="Watermark X"
                                    type="text"
                                    placeholder="Enter watermark x"
                                />
                                <CustomInput
                                    class=""
                                    v-model="form.watermark_settings['watermark_y']"
                                    name="watermark_y"
                                    label="Watermark Y"
                                    type="text"
                                    placeholder="Enter watermark y"
                                    @update:modelValue="form.errors = validate(form)"
                                    :error="form.errors['watermark_y']"
                                />
                            </div>
                        </div>
                        <a :href="watermark_media ? ('/overlay-watermark-image?params='+JSON.stringify(form.watermark_settings)) : null"
                           target="_blank"
                           :class="['ml-auto disabled:cursor-not-allowed  w-fit flex items-center gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80', watermark_media ? '' : 'disabled']">Generate
                            watermark image</a>
                    </div>
                </div>
                <div v-else-if="activeTab === 'gallery_translation'">
                    <template
                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.products[0].can_edit">
                        <div class="p-6.5">
                            <CustomMediaList
                                @remove-images="(id) => {removeSingleGallery(id, -1, 'media_id_translation', 'media_translation')}"
                                label="Base image translation"
                                @insert="(data) => {
                                    insert(data, 0, 'media_id_translation', 'media_translation', 'gallery_translation')
                                }"
                                :images="media_translation ? media_translation : []"
                                :types="['videos', 'images']"
                                mode="single"
                            />
                        </div>
                        <div class="p-6.5">
                            <CustomMediaList
                                label="Gallery translation"
                                @remove-images="(id) => {removeGallery(id, -1, 'removeGalleryTranslation')}"
                                @insert="(data) => {
                                    insert(data, 0, 'media_id_translation', 'media_translation', 'gallery_translation')
                                }"
                                :images="form.gallery_translation ? form.gallery_translation : []"
                                :types="['videos', 'images']"
                                :videoUrl="true"
                            />
                        </div>
                        <div class="mt-4">
                            <CustomTextarea
                                :disabled="!auth.user_group.permissions_by_name.products[0].can_edit"
                                label="Shorts translation"
                                name="shorts_translation"
                                :rows="2"
                                v-model="form.shorts_translation"
                                placeholder="Shorts links separated by comma (,)"
                            />
                        </div>
                    </template>
                </div>
                <div v-else-if="activeTab === 'b2b'">
                    <CustomButton
                        type="button"
                        :disabled="!!newTieredPrice"
                        @click="() => {
                            newTieredPrice = {
                                customer_group_id: null,
                                min: 0,
                                price: 0,
                            }
                        }"
                        class="flex items-center gap-2 rounded bg-meta-3 py-2 px-4.5 mt-5 font-medium text-white hover:bg-opacity-80 ml-auto"
                    >
                        <font-awesome-icon :icon="'plus'"/>
                        Add tiered price
                    </CustomButton>

                    <CustomTableSecond
                        title=""
                        class="relative mt-4"
                        :button-info="null"
                    >
                        <template #header>
                        </template>

                        <template #content>
                            <div
                                class="grid grid-cols-7 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5 sticky-header text-black"
                            >
                                <div class="col-span-2 flex items-center">
                                    <p class="font-bold">Customer group</p>
                                </div>
                                <div class="col-span-2 items-center sm:flex">
                                    <p class="font-bold">Min quantity</p>
                                </div>
                                <div class="col-span-2 flex items-center">
                                    <p class="font-bold">Price</p>
                                </div>
                                <div class="col-span-1 flex items-center">
                                    <p class="font-bold">Actions</p>
                                </div>
                            </div>
                            <div
                                v-if="newTieredPrice"
                                class="grid grid-cols-7 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5 sticky-header text-black"
                            >
                                <div class="col-span-2 items-center pr-3">
                                    <CustomSelect
                                        label=""
                                        v-model="newTieredPrice.customer_group_id"
                                        mode="single"
                                        placeholder="Select B2B customer group"
                                        :options="params.customerGroups"
                                        :searchable="true"
                                        class="py-2  rounded-lg border-stroke bg-transparent"
                                    />
                                </div>
                                <div class="col-span-2 items-center pr-3">
                                    <CustomInput
                                        v-model="newTieredPrice.min"
                                        name="min"
                                        label=""
                                        type="number"
                                        placeholder="Enter min quantity"
                                    />
                                </div>
                                <div class="col-span-2 items-center pr-3">
                                    <CustomInput
                                        v-model="newTieredPrice.price"
                                        name="price"
                                        label=""
                                        type="text"
                                        placeholder="Enter price"
                                    />
                                </div>
                                <div class="col-span-1 items-center">
                                    <div class="flex">
                                        <div>
                                            <CustomButton
                                                class="flex ml-auto mr-3 items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 h-[50px]"
                                                type="button"
                                                :disabled="(!newTieredPrice || !newTieredPrice.customer_group_id || !newTieredPrice.price)"
                                                @click="finishNewTieredPrice()"
                                            >
                                                Add
                                            </CustomButton>
                                        </div>
                                        <div>
                                            <CustomButton
                                                class="flex ml-auto mr-3 items-center gap-2 rounded bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 h-[50px]"
                                                type="button"
                                                @click="newTieredPrice = null"
                                            >
                                                Delete
                                            </CustomButton>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <template
                                v-for="(tieredPrice, key) in form.tiered_prices"
                                :key="key"
                            >
                                <div
                                    v-if="!tieredPrice.deleted"
                                    class="grid grid-cols-7 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5 sticky-header text-black"
                                >
                                    <div class="col-span-2 items-center pr-3">
                                        <CustomSelect
                                            label=""
                                            v-model="tieredPrice.customer_group_id"
                                            mode="single"
                                            placeholder="Select B2B customer group"
                                            :options="params.customerGroups"
                                            :searchable="true"
                                            class="py-2  rounded-lg border-stroke bg-transparent"
                                        />
                                    </div>
                                    <div class="col-span-2 items-center pr-3">
                                        <CustomInput
                                            v-model="tieredPrice.min"
                                            name="min"
                                            label=""
                                            type="number"
                                            placeholder="Enter min quantity"
                                        />
                                    </div>
                                    <div class="col-span-2 items-center pr-3">
                                        <CustomInput
                                            v-model="tieredPrice.price"
                                            name="price"
                                            label=""
                                            type="text"
                                            placeholder="Enter price"
                                        />
                                    </div>
                                    <div class="col-span-1 items-center">
                                        <div class="flex">
                                            <div>
                                                <CustomButton
                                                    class="flex ml-auto mr-3 items-center gap-2 rounded bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 h-[50px]"
                                                    type="button"
                                                    @click="tieredPrice.deleted = true"
                                                >
                                                    Delete
                                                </CustomButton>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </template>
                        </template>
                    </CustomTableSecond>
                </div>
                <div v-else-if="activeTab === 'custom_fields'">
                    <div class="px-6.5">
                        <template v-if="auth.user_group.permissions_by_name.products[0].can_add">
                            <CustomButton
                                type="button"
                                @click="addNewCustomField"
                                class="flex items-center gap-2 rounded bg-meta-3 py-2 px-4.5 mt-5 font-medium text-white hover:bg-opacity-80 ml-auto"
                            >
                                <font-awesome-icon :icon="'plus'"/>
                                Add new
                            </CustomButton>
                        </template>
                    </div>
                    <template
                        v-for="(customField, index) in form.custom_fields"
                        :key="index"
                    >
                        <template v-if="!customField.deleted">
                            <div class="grid grid-cols-3 gap-9">
                                <div class="flex flex-col p-6.5 pb-0 col-span-1">
                                    <CustomInput
                                        :disabled="customField.id && !auth.user_group.permissions_by_name.products[0].can_edit"
                                        @keyup="customField.changed = true"
                                        v-model="customField.key"
                                        name="key"
                                        label="Key *"
                                        type="text"
                                        placeholder="Enter key"
                                    />
                                </div>
                                <div class="flex flex-row p-6.5 pb-0 col-span-2">
                                    <div class="w-full">
                                        <CustomInput
                                            :disabled="customField.id && !auth.user_group.permissions_by_name.products[0].can_edit"
                                            @keyup="customField.changed = true"
                                            v-model="customField.value"
                                            name="value"
                                            label="Value *"
                                            type="text"
                                            placeholder="Enter value"
                                        />
                                    </div>
                                    <div>
                                        <template v-if="auth.user_group.permissions_by_name.products[0].can_delete">
                                            <button
                                                @click="customField.deleted = true"
                                                class="hover:text-danger"
                                                title="Delete"
                                            >
                                                <font-awesome-icon :icon="['fas', 'trash-can']"/>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </template>

                </div>
                <div v-else-if="activeTab === 'linked_products'">
                    <div class="p-6 max-xl:py-4 max-xl:px-0">

                        <div
                            class="grid grid-cols-2 gap-6  max-md:gap-4  max-md:grid-cols-1 ">
                            <div class="flex flex-col ">
                                <CustomSelect
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                    v-model="form.upsells_product_ids"
                                    mode="tags"
                                    :searchable="true"
                                    label="Upsells"
                                    :excluded-value="form.id"
                                    placeholder="Select products"
                                    :options="params.relatedProducts"
                                    :show-labels="true"
                                    :close-on-select="false"
                                    :canClear="false"
                                />
                            </div>
                            <div class="flex flex-col ">
                                <CustomSelect
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                    v-model="form.cross_sells_product_ids"
                                    mode="tags"
                                    :searchable="true"
                                    label="Cross-sells"
                                    :excluded-value="form.id"
                                    placeholder="Select products"
                                    :options="params.relatedProducts"
                                    :show-labels="true"
                                    :close-on-select="false"
                                    :canClear="false"
                                />
                            </div>
                            <div class="flex flex-col">
                                <CustomSelect
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                    v-model="form.related_product_ids"
                                    mode="tags"
                                    :searchable="true"
                                    label="Related products"
                                    :excluded-value="form.id"
                                    placeholder="Select products"
                                    :options="params.relatedProducts"
                                    :show-labels="true"
                                    :close-on-select="false"
                                    :canClear="false"
                                />
                            </div>
                            <div class="flex flex-col">
                                <CustomSelect
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                    v-model="form.related_reviewer_id"
                                    :searchable="true"
                                    label="Related reviewer product"
                                    :excluded-value="form.id"
                                    placeholder="Select product"
                                    :options="params.relatedProducts"
                                    :show-labels="true"
                                    :close-on-select="false"
                                    :canClear="false"
                                />
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="flex flex-col text-title-xl text-black font-bold mb-6 max-md:mb-4">
                                Extra products
                            </div>

                            <div class="grid grid-cols-5 gap-6 max-md:gap-4 max-md:grid-cols-1">
                                <div class="flex flex-col col-span-2 max-md:col-span-1">
                                    <CustomSelect
                                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                        class="py-2 rounded-lg border-stroke bg-transparent"
                                        v-model="form.extra_products_product_ids"
                                        mode="tags"
                                        :searchable="true"
                                        label=""
                                        :excluded-value="form.id"
                                        placeholder="Select products"
                                        :options="params.extraProducts"
                                        :show-labels="true"
                                        :close-on-select="false"
                                        :canClear="false"
                                    />
                                </div>
                                <template v-if="Object.keys(form.required_extras).length">
                                    <div class="flex flex-col col-span-3 max-md:col-span-1">
                                        <div
                                            class="border border-stroke grid grid-cols-7"
                                        >
                                            <div
                                                class="flex flex-col px-3 py-4 border-r-2 border-stroke text-title-md text-black font-bold col-span-3">
                                                Product
                                            </div>
                                            <div
                                                class="flex flex-col px-3 py-4 text-title-md text-black font-bold border-r-2 border-stroke col-span-2">
                                                Is required
                                            </div>
                                            <div
                                                class="flex flex-col px-3 py-4 text-title-md text-black font-bold col-span-2">
                                                Show prices
                                            </div>
                                        </div>
                                        <div
                                            class="border border-stroke grid grid-cols-7"
                                            v-for="(extraProdReq, extraPrId, index) in form.required_extras"
                                            :key="extraPrId"
                                            :class="{'border-t-0': index}"
                                        >
                                            <div class="flex flex-col px-3 py-4 border-r-2 border-stroke col-span-3">
                                                {{ params.extraProducts.find(pr => pr.value == extraPrId)?.label }}
                                            </div>
                                            <div class="flex flex-col px-3 py-4 border-r-2 border-stroke col-span-2">
                                                <Switch
                                                    @change="(value) => {
                                                form.required_extras[extraPrId] = value;
                                            }"
                                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                                    :value="extraProdReq"
                                                    :id="`is_req_${extraPrId}`"
                                                    label=""
                                                />
                                            </div>
                                            <div class="flex flex-col px-3 py-4 col-span-2">
                                                <Switch
                                                    @change="(value) => {
                                                form.show_prices_extra[extraPrId] = value;
                                            }"
                                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                                    :value="form.show_prices_extra[extraPrId]"
                                                    :id="`show_prices_${extraPrId}`"
                                                    label=""
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else-if="activeTab === 'bundling'">
                    <template v-if="form.language_id == -1">
                        <div class="grid grid-cols-5 gap-6 p-6 max-xl:py-4 max-xl:px-0 max-md:grid-cols-1 max-md:gap-4">
                            <div class="flex flex-col col-span-3 max-md:col-span-1">
                                <CustomSelect
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                    v-model="form.bundling_product_ids"
                                    mode="tags"
                                    :searchable="true"
                                    label="Link products"
                                    :excluded-value="form.id"
                                    placeholder="Select products"
                                    :options="params.relatedProducts"
                                    :show-labels="true"
                                    :close-on-select="false"
                                    :canClear="false"
                                />
                            </div>
                            <div class="flex flex-col col-span-2 max-md:col-span-1">
                                <CustomSelect
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                    v-model="form.bundle_showing_type"
                                    label="Showing type"
                                    :excluded-value="form.id"
                                    placeholder="Select type"
                                    :options="params.bundlingOptions"
                                    :close-on-select="true"
                                />
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="p-6 max-xl:py-4 max-xl:px-0">
                            <div class="flex flex-col">
                                <CustomInput
                                    :disabled="!auth.user_group.permissions_by_name.products[0].can_edit"
                                    v-model="form.bundle_label"
                                    name="bundle_label"
                                    label="Label"
                                    type="text"
                                    placeholder="Enter label"
                                />
                            </div>
                        </div>
                    </template>
                </div>
                <div v-else-if="activeTab === 'accesses'">
                    <div class=" p-6 max-xl:py-4 max-xl:px-0">
                        <div class="flex flex-wrap gap-6 max-md:gap-4">
                            <template v-if="form.language_id == -1">
                                <Switch
                                    @change="(value) => {
                                    form.new = value;
                                }"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    :value="form.new"
                                    id="main_new"
                                    label="New"
                                />
                                <Switch
                                    @change="(value) => {
                                    form.bestseller = value;
                                }"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    :value="form.bestseller"
                                    id="main_bestseller"
                                    label="Bestseller"
                                />
                                <Switch
                                    @change="(value) => {
                                     form.status = value;
                                }"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    :value="form.status"
                                    id="main_status"
                                    label="Status"
                                />
                                <Switch
                                    @change="(value) => {
                                    form.enable_reviews = value;
                                }"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    :value="form.enable_reviews"
                                    id="main_enable_reviews"
                                    label="Enable reviews"
                                />
                                <div class="min-w-[250px]" v-if="generalParams?.vendor.b2b">
                                    <CustomSelect
                                        class="w-full"
                                        v-model="form.b2b"
                                        mode="single"
                                        label="For"
                                        :options="b2bOptions"
                                        :searchable="false"
                                        :canClear="false"
                                    />
                                </div>
                            </template>
                            <template v-else>
                                <Switch
                                    @change="(value) => {
                                     form.translation_status = value;
                                   }"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    :value="form.translation_status"
                                    id="translation_status"
                                    label="Status"
                                />
                                <Switch
                                    @change="(value) => {
                                     form.category_inheritance_calculator = value;
                                   }"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    :value="form.category_inheritance_calculator"
                                    id="category_inheritance_calculator"
                                    label="Implement category's calculator"
                                />
                                <Switch
                                    @change="(value) => {
                                     form.category_inheritance_a_plus = value;
                                   }"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    :value="form.category_inheritance_a_plus"
                                    id="category_inheritance_a_plus"
                                    label="Implement category's A + content"
                                />
                                <Switch
                                    @change="(value) => {
                                     form.category_inheritance_snippet = value;
                                   }"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                    :value="form.category_inheritance_snippet"
                                    id="category_inheritance_snippet"
                                    label="Implement category's snippet"
                                />
                            </template>
                        </div>
                    </div>
                </div>
                <div v-else-if="activeTab === 'variations'">
                    <div class=" p-6 max-xl:py-4 max-xl:px-0">
                        <div class="flex justify-between flex-wrap gap-6 max-md:gap-4">
                            <div class="flex flex-wrap gap-3 max-xsm:w-[100%]">
                                <a v-if="auth.user_group.permissions_by_name.products[0].can_upload"
                                   target="_blank"
                                   href="/tools/file-uploads?type=7"
                                   class="max-2xsm:w-[100%]">
                                    <CustomButton
                                        class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 h-[50px] max-2xsm:w-[100%]"
                                        type="button"
                                    >
                                        <font-awesome-icon :icon="['fass', 'upload']"/>
                                        Uploads
                                    </CustomButton>
                                </a>
                                <template
                                    v-if="auth.user_group.permissions_by_name.products[0].can_export && !exportActions">
                                    <CustomButton
                                        @click="exportActions = true, importActions = false"
                                        class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 h-[50px] max-2xsm:w-[100%]"
                                        type="button"
                                    >
                                        <font-awesome-icon :icon="['far', 'file-export']"/>
                                        Export
                                    </CustomButton>
                                </template>
                                <template
                                    v-if="auth.user_group.permissions_by_name.products[0].can_upload && !importActions">
                                    <CustomButton
                                        @click="exportActions = false, importActions = true"
                                        class="flex items-center max-2xsm:w-[100%] gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 h-[50px]"
                                        type="button"
                                    >
                                        <font-awesome-icon :icon="['far', 'file-arrow-down']"/>
                                        Import
                                    </CustomButton>
                                </template>
                            </div>
                            <div class="max-2xsm:w-[100%]">
                                <template v-if="exportActions">
                                    <div class="flex">
                                        <CustomButton
                                            @click="exportFile()"
                                            class="flex items-center max-2xsm:w-[100%] gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 h-[50px]"
                                            type="button"
                                        >
                                            <font-awesome-icon :icon="['far', 'file-export']"/>
                                            Export variations
                                        </CustomButton>
                                    </div>
                                </template>
                                <template v-if="importActions">
                                    <div class=" w-full">
                                        <div class="relative ">
                                            <div class="max-sm:w-[100%]">
                                                <input
                                                    @change="(event) => {
                                                        handleUpload(event);
                                                    }"
                                                    ref="uploadFileInput"
                                                    type="file"
                                                    class="cursor-pointer rounded border-[1.5px] border-stroke bg-transparent font-medium outline-none transition file:mr-5 file:border-collapse file:cursor-pointer file:border-0 file:border-r file:border-solid file:border-stroke file:bg-whiter file:py-3 file:px-5 file:hover:bg-primary file:hover:bg-opacity-10 focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter w-[100%]"
                                                />
                                            </div>
                                        </div>
                                        <div>
                                            <template
                                                v-if="importType.fileUploaded">
                                                <CustomButton
                                                    @click="upload()"
                                                    class="float-right mt-3 items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 h-[50px] max-2xsm:w-[100%]"
                                                    type="button"
                                                >
                                                    <font-awesome-icon :icon="['fass', 'upload']"/>
                                                    Upload
                                                </CustomButton>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="mt-6" v-if="form.language_id == -1">
                            <hr class="text-gray my-6">
                            <div class="grid grid-cols-2 gap-6 max-md:gap-4 max-md:grid-cols-1 holder-with-buttons">
                                <template
                                    :key="key"
                                    v-for="(attribute, key) in params.preparedAttributes"
                                >
                                    <template
                                        v-if="attribute.logic === 0 && form.attribute_type_variables_old[attribute.variable_name].length">
                                        <div class="flex flex-col filter-select-holder">
                                            <CustomSelect
                                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                                class="py-2 rounded-lg border-stroke bg-transparent"
                                                mode="tags"
                                                :searchable="true"
                                                :check-exists-in-array="form.attribute_type_variables_old[attribute.variable_name]"
                                                v-model="generatingVariationsAttributeValues[attribute.variable_name]"
                                                :label="attribute.label + 's'"
                                                :placeholder="`Select ${attribute.label}`"
                                                :options="attribute.options"
                                                :show-labels="true"
                                                :close-on-select="false"
                                                :canClear="false"
                                            />
                                        </div>
                                    </template>
                                </template>
                            </div>
                            <div
                                class="flex justify-end gap-4 flex-wrap max-xsm:justify-start max-xsm:gap-2 max-xsm:grid max-xsm:grid-cols-1 mt-6">
                                <div class="">
                                    <template v-if="auth.user_group.permissions_by_name.products[0].can_delete">
                                        <CustomButton
                                            type="button"
                                            @click="deleteVariations(true)"
                                            class="flex items-center justify-center max-xsm:w-[100%] gap-2 rounded bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                                        >
                                            <font-awesome-icon :icon="['far', 'trash']"/>
                                            Delete invalids
                                        </CustomButton>
                                    </template>
                                </div>
                                <div class="">
                                    <template v-if="auth.user_group.permissions_by_name.products[0].can_delete">
                                        <CustomButton
                                            type="button"
                                            @click="deleteVariations(false)"
                                            class="flex items-center gap-2 justify-center max-xsm:w-[100%] rounded bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                                        >
                                            <font-awesome-icon :icon="['far', 'trash']"/>
                                            Delete all
                                        </CustomButton>
                                    </template>
                                </div>
                                <div class="">
                                    <template v-if="auth.user_group.permissions_by_name.products[0].can_add">
                                        <CustomButton
                                            :disabled="(!!generatingVariationsAttributeValuesCount && params.usedVariableAttributesCount !== generatingVariationsAttributeValuesCount)"
                                            type="button"
                                            @click="generateVariations"
                                            class="flex items-center gap-2 rounded justify-center max-xsm:w-[100%] bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                                        >
                                            <font-awesome-icon :icon="['far', 'wind-turbine']"/>
                                            Generate variations
                                        </CustomButton>
                                    </template>
                                </div>
                                <div>
                                    <template v-if="auth.user_group.permissions_by_name.products[0].can_add">
                                        <CustomButton
                                            :disabled="!!newVariant"
                                            type="button"
                                            @click="addNewVariantInitial"
                                            class="flex items-center gap-2 rounded justify-center max-xsm:w-[100%] bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 disabled:cursor-default disabled:bg-whiter"
                                        >
                                            <font-awesome-icon :icon="'plus'"/>
                                            Add manually
                                        </CustomButton>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <hr class="text-gray my-6 max-sm:my-4">
                        <div>
                            <div class="text-2xl text-black font-bold mb-6">
                                Filters and search
                            </div>
                            <div class="flex flex-col items-end gap-6 max-md:gap-4">
                                <div
                                    class="grid gap-6 grid-cols-2 max-md:gap-4 w-[100%] max-md:grid-cols-1 holder-with-button">
                                    <template
                                        :key="key"
                                        v-for="(attribute, key) in params.preparedAttributes"
                                    >
                                        <template
                                            v-if="attribute.logic === 0 && form.attribute_type_variables_old[attribute.variable_name].length">
                                            <div class="flex flex-col filter-select-holder">
                                                <CustomSelect
                                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                                    :searchable="true"
                                                    :check-exists-in-array="form.attribute_type_variables_old[attribute.variable_name]"
                                                    v-model="form.variations_query.attribute_ids[attribute.variable_name]"
                                                    :label="attribute.label"
                                                    :placeholder="`Select ${attribute.label}`"
                                                    :options="attribute.options"
                                                    :show-labels="true"
                                                    :canClear="true"
                                                />
                                            </div>
                                        </template>
                                    </template>
                                    <div class="md:col-span-2">
                                        <CustomButton
                                            @click="emits('fetch-variations')"
                                            class="flex items-center gap-2 ml-auto rounded bg-primary py-4 px-4.5 font-medium text-white hover:bg-opacity-80"
                                            type="button"
                                        >
                                            <font-awesome-icon :icon="['far', 'magnifying-glass']"/>
                                            Apply search and filters
                                        </CustomButton>
                                    </div>

                                </div>

                            </div>
                        </div>
                        <hr class="text-gray my-6 max-sm:my-4">
                        <div class="">

                            <div class="flex gap-4 flex-wrap justify-between">
                                <p class="text-title-lg text-black font-bold ">Variations</p>
                                <div class="datatable-info flex flex-wrap gap-2 items-end justify-end">
                            <span class="">
                                Showing {{ form.variations_query.pagination.showing.from }} to
                            {{ form.variations_query.pagination.showing.to }} of
                            {{ form.variations_query.pagination.total_items }} entries
                            </span>
                                    <vue-awesome-paginate
                                        v-if="20 < form.variations_query.pagination.total_items"
                                        :total-items="form.variations_query.pagination.total_items"
                                        :items-per-page="20"
                                        :max-pages-shown="3"
                                        v-model="form.variations_query.page"
                                        @click="emits('fetch-variations')"
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

                            <div>
                                <template v-if="newVariant">
                                    <AccordionTwo
                                        :parent="newVariant"
                                        :invalid="!newVariant.regular_price"
                                    >
                                        <template #header>
                                            <div class="flex flex-col w-full">
                                                <div>
                                                    <BadgeThree :badge-item="{name: 'New variant', color: '#13C296'}"/>
                                                </div>
                                                <div
                                                    class="grid w-full"
                                                    :class="`grid-cols-${variableAttributesLength}`"
                                                >
                                                    <template
                                                        :key="key"
                                                        v-for="(attribute, key) in params.preparedAttributes"
                                                    >
                                                        <template v-if="attribute.logic == 0">
                                                            <div class="flex flex-col p-6.5 pb-0">
                                                                <CustomSelect
                                                                    @click.stop
                                                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                                                    v-model="newVariant[attribute.variable_name]"
                                                                    @update:modelValue="newVariant.errors = validate(newVariant)"
                                                                    mode="single"
                                                                    :searchable="true"
                                                                    :label="attribute.label"
                                                                    :placeholder="`Select ${attribute.label}`"
                                                                    :options="attribute.options"
                                                                    :canClear="false"
                                                                    :error="newVariant.errors[attribute.variable_name]"
                                                                />
                                                            </div>
                                                        </template>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                        <template #content>
                                            <div
                                                v-if="Object.keys(newVariant.errors).length > 0 && newVariant.errors.general"
                                                class="grid grid-cols-1 gap-9 p-6.5"
                                            >
                                                <AlertError :errors="newVariant.errors.general"/>
                                            </div>
                                            <div class="grid grid-cols-4 w-full">
                                                <div class="flex flex-col p-6.5 pb-0 col-span-1">
                                                    <CustomMediaList
                                                        @remove-images="(id) => {removeSingleGallery(id, 'new')}"
                                                        label="Base image"
                                                        @insert="(data) => {insert(data, 'new')}"
                                                        :images="newVariant.media"
                                                        :types="['videos', 'images']"
                                                        mode="single"
                                                    />
                                                </div>
                                                <div class="flex flex-col p-6.5 pb-0 col-span-3">
                                                    <div>
                                                        <CustomInput
                                                            v-model="newVariant.sku"
                                                            name="sku"
                                                            label="SKU *"
                                                            type="text"
                                                            placeholder="Enter SKU"
                                                            @keyup="newVariant.errors = validate(newVariant)"
                                                            :error="newVariant.errors['sku']"
                                                        />
                                                    </div>
                                                    <div>
                                                        <TooltipOne
                                                            :button-params="{showingType: 'info'}"
                                                            :tooltip-text="'Need after every SKU write =x{qty}, and if it is not last, add %<br>Examples:<br>Has 1 parent:<br>9x4064824550895<br>Has multiple parents<br>9x4064824550895,9x4064824550888,17x4064824559478'"
                                                            tooltipClass="rounded-md bg-primary py-2 px-2 mx-3 mb-2"
                                                        />
                                                        <CustomTextarea
                                                            label="Item's SKUs *"
                                                            name="parents"
                                                            :rows="1"
                                                            v-model="newVariant.parents"
                                                            placeholder="Example: 9x4064824550895 OR 9x4064824550895,9x4064824550888,17x4064824559478"
                                                            @keyup="newVariant.errors = validate(newVariant)"
                                                            :error="newVariant.errors['parents']"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                            <hr class="text-gray mt-6.5">
                                            <div class="grid grid-cols-3 w-full">
                                                <div class="flex flex-col p-6.5 pb-0">
                                                    <CustomSelect
                                                        label="Tax status *"
                                                        v-model="newVariant.tax_status"
                                                        mode="single"
                                                        :canClear="false"
                                                        placeholder="Select"
                                                        :options="taxableOptions"
                                                        class="py-2 rounded-lg border-stroke bg-transparent"
                                                    />
                                                </div>
                                                <div class="flex flex-col p-6.5 pb-0">
                                                    <CustomInput
                                                        v-model="newVariant.regular_price"
                                                        name="regular_price"
                                                        label="Regular price *"
                                                        type="text"
                                                        placeholder="Enter regular price"
                                                        @keyup="newVariant.errors = validate(newVariant)"
                                                        :error="newVariant.errors['regular_price']"
                                                    />
                                                </div>
                                                <div class="flex flex-col p-6.5 pb-0">
                                                    <CustomInput
                                                        v-model="newVariant.sales_price"
                                                        name="sales_price"
                                                        label="Sales price"
                                                        type="text"
                                                        placeholder="Enter sales price"
                                                        @keyup="form.errors = validate(form)"
                                                        :error="form.errors['sales_price']"
                                                    />
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-4 w-full">
                                                <div class="flex flex-col p-6.5 pb-0">
                                                    <CustomSelect
                                                        label="Stock status *"
                                                        v-model="newVariant.stock_status"
                                                        mode="single"
                                                        :canClear="false"
                                                        placeholder="Select"
                                                        :options="stockStatuses"
                                                        class="py-2 rounded-lg border-stroke bg-transparent"
                                                        @keyup="newVariant.errors = validate(newVariant)"
                                                        :error="newVariant.errors['stock_status']"
                                                    />
                                                </div>
                                                <div class="flex flex-col p-6.5 pb-0">
                                                    <Switch
                                                        @change="(value) => {
                                                newVariant.independent_stock_status = value;
                                            }"
                                                        :value="newVariant.independent_stock_status"
                                                        id="v_independent_stock_status"
                                                        label="Independent stock status"
                                                    />
                                                </div>
                                                <div class="flex flex-col p-6.5 pb-0">
                                                    <Switch
                                                        @change="(value) => {
                                               newVariant.status = value;
                                            }"
                                                        :value="newVariant.status"
                                                        id="new_status"
                                                        label="Status"
                                                    />
                                                </div>
                                                <div class="flex flex-col p-6.5 pb-0">

                                                </div>
                                            </div>
                                            <CustomMediaList
                                                label="Gallery"
                                                @remove-images="(id) => {removeGallery(id, 'new')}"
                                                @insert="(data) => {insert(data, 'new')}"
                                                :images="newVariant['gallery']"
                                                :types="['videos', 'images']"
                                                :videoUrl="true"
                                            />
                                            <div class="grid grid-cols-1">
                                                <div class="flex flex-col p-6.5">
                                                    <div class="flex ml-auto gap-5">
                                                        <CustomButton
                                                            @click="newVariant = null"
                                                            class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                                                            type="button"
                                                        >
                                                            <font-awesome-icon :icon="['far', 'trash']"/>
                                                            Delete
                                                        </CustomButton>

                                                        <template
                                                            v-if="auth.user_group.permissions_by_name.products[0].can_add">
                                                            <CustomButton
                                                                @click="saveNewVariant()"
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
                                <template
                                    v-for="(variant, index) in form.product_variations"
                                    :key="index"
                                >
                                    <AccordionTwo
                                        :parent="variant"
                                        :invalid="!variant.regular_price"
                                    >
                                        <template #header>
                                            <div class="flex w-full">
                                                <div class="w-full">
                                                    <div class="text-left mb-4">
                                                        <span>#{{ variant.id }}</span>
                                                    </div>

                                                    <div
                                                        class="grid gap-4  max-md:grid-cols-1"
                                                        :class="[`${variant.product_variant_attributes.length}` > 2 ? 'grid-cols-3':`grid-cols-${variant.product_variant_attributes.length}`,
                                                        `${variant.product_variant_attributes.length}` > 1 ? 'max-xl:grid-cols-2':'']"
                                                    >
                                                        <template
                                                            :key="key"
                                                            v-for="(productVariantAttribute, key) in variant.product_variant_attributes"
                                                        >
                                                            <div class="flex flex-col">
                                                                <CustomSelect
                                                                    v-model="productVariantAttribute.attribute.id"
                                                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                                                    :label="productVariantAttribute.attribute.attribute_type.name"
                                                                    :options="[{value: productVariantAttribute.attribute.id, label: productVariantAttribute.attribute.value}]"
                                                                    :disabled="true"
                                                                    :show-labels="true"
                                                                    :close-on-select="false"
                                                                    :canClear="false"
                                                                />
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>

                                            </div>
                                        </template>
                                        <template #content>
                                            <template v-if="form.language_id == -1">
                                                <div class="grid grid-cols-4 w-full gap-6 max-md:gap-4 max-md:grid-cols-1">
                                                    <div class="">
                                                        <CustomInput
                                                            :disabled="!auth.user_group.permissions_by_name.products[0].can_edit"
                                                            v-model="variant.sku"
                                                            name="sku"
                                                            label="SKU *"
                                                            type="text"
                                                            placeholder="Enter SKU"
                                                            @keyup="dynamicValidation(index, 'sku', {sku: variant.sku, skuRules: ['required', 'maxLength:50']}, 'errors')"
                                                            :error="errors[index]['sku'] ?? null"
                                                        />
                                                    </div>
                                                    <div class="col-span-3 relative absolute-holder-for-label max-md:col-span-1">
                                                        <CustomTextarea
                                                            :disabled="!auth.user_group.permissions_by_name.products[0].can_edit"
                                                            label="Item's SKUs *"
                                                            name="parents"
                                                            :rows="1"
                                                            v-model="variant.parents"
                                                            placeholder="Example: 9x4064824550895 OR 9x4064824550895,9x4064824550888,17x4064824559478"
                                                            @keyup="dynamicValidation(index, 'parents', {parents: variant.parents, parentsRules: ['required', 'skuFormat']}, 'errors')"
                                                            :error="errors[index]['parents'] ?? null"
                                                        />
                                                        <div class="absolute left-[95px] top-[-3px]">
                                                            <TooltipOne
                                                                :button-params="{showingType: 'info'}"
                                                                :tooltip-text="'Need after every SKU write =x{qty}, and if it is not last, add %<br>Examples:<br>Has 1 parent:<br>9x4064824550895<br>Has multiple parents<br>9x4064824550895,9x4064824550888,17x4064824559478'"
                                                                tooltipClass="rounded-md bg-primary py-2 px-2 mx-3"
                                                            />
                                                        </div>

                                                    </div>
                                                </div>
                                                <hr class="text-gray my-6 max-md:my-4">
                                                <div class="grid grid-cols-3 gap-6 max-md:gap-4 max-md:grid-cols-2 max-sm:grid-cols-1">
                                                    <div class="flex flex-col ">
                                                        <CustomSelect
                                                            :disabled="!auth.user_group.permissions_by_name.products[0].can_edit"
                                                            label="Tax status *"
                                                            v-model="variant.tax_status"
                                                            mode="single"
                                                            :canClear="false"
                                                            placeholder="Select"
                                                            :options="taxableOptions"
                                                            class="py-2 rounded-lg border-stroke bg-transparent"
                                                        />
                                                    </div>
                                                    <div class="flex flex-col ">
                                                        <CustomInput
                                                            :disabled="!auth.user_group.permissions_by_name.products[0].can_edit"
                                                            v-model="variant.regular_price"
                                                            name="regular_price"
                                                            label="Regular price *"
                                                            type="text"
                                                            placeholder="Enter regular price"
                                                            @keyup="dynamicValidation(index, 'regular_price', {regular_price: variant.regular_price, regular_priceRules: ['required', 'validDecimal']}, 'errors')"
                                                            :error="errors[index]['regular_price'] ?? null"
                                                        />
                                                    </div>
                                                    <div class="flex flex-col ">
                                                        <CustomInput
                                                            :disabled="!auth.user_group.permissions_by_name.products[0].can_edit"
                                                            v-model="variant.sales_price"
                                                            name="sales_price"
                                                            label="Sales price"
                                                            type="text"
                                                            placeholder="Enter sales price"
                                                            @keyup="dynamicValidation(index, 'sales_price', {sales_price: variant.sales_price, sales_priceRules: ['validDecimal']}, 'errors')"
                                                            :error="errors[index]['sales_price'] ?? null"
                                                        />
                                                    </div>
                                                    <div class="flex flex-col">
                                                        <CustomSelect
                                                            :disabled="!auth.user_group.permissions_by_name.products[0].can_edit"
                                                            label="Stock status *"
                                                            v-model="variant.stock_status"
                                                            mode="single"
                                                            :canClear="false"
                                                            placeholder="Select"
                                                            :options="stockStatuses"
                                                            class="py-2 rounded-lg border-stroke bg-transparent"
                                                            @keyup="dynamicValidation(index, 'stock_status', {stock_status: variant.stock_status, stock_statusRules: ['required']}, 'errors')"
                                                            :error="errors[index]['stock_status'] ?? null"
                                                        />
                                                    </div>
                                                    <div class="flex flex-col">
                                                        <Switch
                                                            @change="(value) => {
                                                variant.independent_stock_status = value;
                                            }"
                                                            :disabled="!auth.user_group.permissions_by_name.products[0].can_edit"
                                                            :value="variant.independent_stock_status"
                                                            id="v_independent_stock_status"
                                                            label="Independent stock status"
                                                        />
                                                    </div>
                                                    <div class="flex flex-col">
                                                        <Switch
                                                            @change="(value) => {
                                                    variant.status = value;
                                                }"
                                                            :disabled="!auth.user_group.permissions_by_name.products[0].can_edit"
                                                            :value="variant.status"
                                                            :id="'status' + index"
                                                            label="Status"
                                                        />
                                                    </div>
                                                </div>
                                                <hr class="text-gray my-6 max-md:my-4">
                                                <div class="grid grid-cols-5 gap-6 max-md:gap-4 max-xl:grid-cols-6 max-md:grid-cols-1">
                                                    <template
                                                        v-if="auth.user_group.permissions_by_name.products[0].can_edit">
                                                        <div class="max-xl:col-span-2 max-md:col-span-1 max-md:max-w-[250px]">
                                                            <CustomMediaList
                                                                @remove-images="(id) => {removeSingleGallery(id, index)}"
                                                                label="Base image"
                                                                @insert="(data) => {insert(data, index)}"
                                                                :images="form.product_variations[index]['media'] ? [form.product_variations[index]['media']] : []"
                                                                :types="['videos', 'images']"
                                                                :index="index"
                                                                mode="single"
                                                            />
                                                        </div>

                                                    </template>
                                                    <template
                                                        v-if="auth.user_group.permissions_by_name.products[0].can_edit">
                                                        <div class="col-span-4 max-md:col-span-1">
                                                            <CustomMediaList
                                                                label="Gallery"
                                                                @remove-images="(id) => {removeGallery(id, index)}"
                                                                @insert="(data) => {insert(data, index)}"
                                                                :images="form.product_variations[index]['gallery'] ? form.product_variations[index]['gallery'] : []"
                                                                :types="['videos', 'images']"
                                                                :videoUrl="true"
                                                                :index="index"
                                                            />
                                                        </div>

                                                    </template>
                                                </div>

                                            </template>
                                            <template v-else>
                                                <div class="grid grid-cols-1 gap-9">
                                                    <template
                                                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.products[0].can_edit">
                                                        <div class="p-6.5">
                                                            <CustomMediaList
                                                                @remove-images="(id) => {removeSingleGallery(id, index, 'media_id_translation', 'media_translation')}"
                                                                label="Base image translation"
                                                                @insert="(data) => {insert(data, index, 'media_id_translation', 'media_translation', 'gallery_translation')}"
                                                                :images="form.product_variations[index]['media_translation'] ? [form.product_variations[index]['media_translation']] : []"
                                                                :types="['videos', 'images']"
                                                                mode="single"
                                                                :index="index"
                                                            />
                                                        </div>
                                                        <div class="p-6.5">
                                                            <CustomMediaList
                                                                label="Gallery translation"
                                                                @remove-images="(id) => {removeGallery(id, index, 'removeGalleryTranslation')}"
                                                                @insert="(data) => {insert(data, index, 'media_id_translation', 'media_translation', 'gallery_translation')}"
                                                                :images="form.product_variations[index]['gallery_translation'] ? form.product_variations[index]['gallery_translation'] : []"
                                                                :types="['videos', 'images']"
                                                                :videoUrl="true"
                                                                :index="index"
                                                            />
                                                        </div>
                                                    </template>
                                                </div>
                                                <div class="grid grid-cols-1 gap-9">
                                                    <div class="flex flex-col p-6.5 pb-0">
                                                        <CustomInput
                                                            :disabled="!auth.user_group.permissions_by_name.products[0].can_edit"
                                                            v-model="variant.name"
                                                            :name="`variant_name_${index}`"
                                                            label="Name"
                                                            type="text"
                                                            placeholder="Enter name"
                                                        />
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-1 gap-9">
                                                    <div class="flex flex-col p-6.5 pb-0">
                                                        <label class="mb-2.5 block font-medium text-black">Short
                                                            description</label>
                                                        <template
                                                            v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.products[0].can_edit">
                                                            <CustomMediaList
                                                                label="Insert media"
                                                                @insert="(data) => {
                                                        insertMediaToDescriptions(data, index, 'short_description')
                                                    }"
                                                                :images="[]"
                                                                :types="['images']"
                                                                :button="true"
                                                                :languageId="form.language_id"
                                                            />
                                                        </template>
                                                        <CKEditorComponent
                                                            :model="variant.short_description"
                                                            @updateValue="(value) => {
                                                    variant.short_description = value
                                                }"
                                                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                                        />
                                                    </div>
                                                </div>
                                                <div class="flex flex-col p-6.5 pb-0">
                                                    <label
                                                        class="mb-2.5 block font-medium text-black">Description</label>
                                                    <template
                                                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.products[0].can_edit">
                                                        <CustomMediaList
                                                            label="Insert media"
                                                            @insert="(data) => {
                                                   insertMediaToDescriptions(data, index, 'description')
                                                }"
                                                            :images="[]"
                                                            :types="['images']"
                                                            :button="true"
                                                            :languageId="form.language_id"
                                                        />
                                                    </template>
                                                    <CKEditorComponent
                                                        :model="variant.description"
                                                        @updateValue="(value) => {
                                                variant.description = value
                                            }"
                                                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                                    />
                                                </div>
                                                <!--                                    <template-->
                                                <!--                                        v-if="!form.product_variations.length && !variant.product_variant_custom_field_translation.length">-->
                                                <!--                                        <div-->
                                                <!--                                            class="bg-amber-200 px-6.5 py-2.5"-->
                                                <!--                                        >-->
                                                <!--                                            To add custom fields for your variation, you must enter general information-->
                                                <!--                                            and save it!-->
                                                <!--                                        </div>-->
                                                <!--                                    </template>-->
                                                <!--                                    <div class="grid grid-cols-1 mt-2.5 gap-9">-->
                                                <!--                                        <div class="px-6.5">-->
                                                <!--                                            <template v-if="auth.user_group.permissions_by_name.products[0].can_add">-->
                                                <!--                                                <CustomButton-->
                                                <!--                                                    type="button"-->
                                                <!--                                                    @click="variant.product_variant_custom_field_translation.unshift({-->
                                                <!--                                                        key: '',-->
                                                <!--                                                        value: '',-->
                                                <!--                                                        deleted: false,-->
                                                <!--                                                        changed: false-->
                                                <!--                                                    });"-->
                                                <!--                                                    class="flex items-center gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 ml-auto"-->
                                                <!--                                                >-->
                                                <!--                                                    <font-awesome-icon :icon="'plus'"/>-->
                                                <!--                                                    Add new-->
                                                <!--                                                </CustomButton>-->
                                                <!--                                            </template>-->
                                                <!--                                        </div>-->
                                                <!--                                    </div>-->
                                                <!--                                    <template-->
                                                <!--                                        v-for="(customField, customFieldIndex) in variant.product_variant_custom_field_translation"-->
                                                <!--                                        :key="index"-->
                                                <!--                                    >-->
                                                <!--                                        <template v-if="!customField.deleted">-->
                                                <!--                                            <div class="grid grid-cols-3 gap-9">-->
                                                <!--                                                <div class="flex flex-col p-6.5 pb-0 col-span-1">-->
                                                <!--                                                    <CustomInput-->
                                                <!--                                                        :disabled="customField.id && !auth.user_group.permissions_by_name.products[0].can_edit"-->
                                                <!--                                                        @keyup="customField.changed = true"-->
                                                <!--                                                        v-model="customField.key"-->
                                                <!--                                                        name="key"-->
                                                <!--                                                        label="Key *"-->
                                                <!--                                                        type="text"-->
                                                <!--                                                        placeholder="Enter key"-->
                                                <!--                                                        :error="errors[index]?.['product_variant_custom_field_translation']?.[customFieldIndex]?.['key'] ?? null"-->
                                                <!--                                                    />-->
                                                <!--                                                </div>-->
                                                <!--                                                <div class="flex flex-row p-6.5 pb-0 col-span-2">-->
                                                <!--                                                    <div class="w-full">-->
                                                <!--                                                        <CustomInput-->
                                                <!--                                                            :disabled="customField.id && !auth.user_group.permissions_by_name.products[0].can_edit"-->
                                                <!--                                                            @keyup="customField.changed = true"-->
                                                <!--                                                            v-model="customField.value"-->
                                                <!--                                                            name="value"-->
                                                <!--                                                            label="Value *"-->
                                                <!--                                                            type="text"-->
                                                <!--                                                            placeholder="Enter value"-->
                                                <!--                                                            :error="errors[index]?.['product_variant_custom_field_translation']?.[customFieldIndex]?.['value'] ?? null"-->
                                                <!--                                                        />-->
                                                <!--                                                    </div>-->
                                                <!--                                                    <div>-->
                                                <!--                                                        <template-->
                                                <!--                                                            v-if="auth.user_group.permissions_by_name.products[0].can_delete">-->
                                                <!--                                                            <button-->
                                                <!--                                                                @click="customField.deleted = true"-->
                                                <!--                                                                class="hover:text-danger"-->
                                                <!--                                                                title="Delete"-->
                                                <!--                                                            >-->
                                                <!--                                                                <font-awesome-icon :icon="['fas', 'trash-can']"/>-->
                                                <!--                                                            </button>-->
                                                <!--                                                        </template>-->
                                                <!--                                                    </div>-->
                                                <!--                                                </div>-->
                                                <!--                                            </div>-->
                                                <!--                                        </template>-->
                                                <!--                                    </template>-->
                                            </template>
                                            <div class="grid grid-cols-1">
                                                <div class="flex flex-col py-6 max-md:py-4">
                                                    <div class="flex ml-auto gap-4">
                                                        <template v-if="generalParams?.vendor.b2b">
                                                            <CustomButton
                                                                type="button"
                                                                @click="() => {
                                                        tieredPricesModalByVariant = {
                                                            openStatus: true,
                                                            variant_id: variant.id,
                                                        }
                                                    }"
                                                                class="flex items-center gap-2 rounded border-meta-3 bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                                                            >
                                                                <font-awesome-icon :icon="['far', 'scanner-keyboard']"/>
                                                                Tiered prices
                                                            </CustomButton>
                                                        </template>
                                                        <template
                                                            v-if="auth.user_group.permissions_by_name.products[0].can_delete && form.language_id == -1">
                                                            <CustomButton
                                                                @click="store.commit('product/SET_DELETE_MODAL_VALUE', {
                                                    value: true,
                                                    id: variant.id,
                                                    deletingActionApi: 'delete-variant',
                                                    deletingText: 'Deleting this variant will be permanent and cannot be undone. Once deleted, you will not be able to restore it.'
                                                })"
                                                                class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                                                                type="button"
                                                            >
                                                                <font-awesome-icon :icon="['far', 'trash']"/>
                                                                Delete
                                                            </CustomButton>
                                                        </template>

                                                        <template
                                                            v-if="auth.user_group.permissions_by_name.products[0].can_edit">
                                                            <CustomButton
                                                                :disabled="(!variant.short_description && !variant.description && form.language_id > 0)"
                                                                @click="emits('update-variant', variant, index)"
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

                </div>
                <div v-else-if="activeTab === 'seo'">
                    <div class="grid grid-cols-2 gap-9">
                        <div class="flex flex-col p-6.5 pb-0">
                            <div>
                                <CustomInput
                                    :disabled="!auth.user_group.permissions_by_name.products[0].can_edit"
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
                        <div class="flex flex-col p-6.5 pb-0">
                            <div>
                                <CustomInput
                                    :disabled="!auth.user_group.permissions_by_name.products[0].can_edit"
                                    v-model="form.meta_keywords"
                                    name="meta_keywords"
                                    label="Meta keywords"
                                    type="text"
                                    placeholder="Enter meta keywords"
                                    :error="form.errors['meta_keywords']"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-9">
                        <div class="flex flex-col p-6.5 pb-0">
                            <label class="mb-2.5 block font-medium text-black">Meta description</label>
                            <textarea
                                :disabled="!auth.user_group.permissions_by_name.products[0].can_edit"
                                v-model="form.meta_description"
                                rows="6"
                                placeholder="Enter meta description"
                                class="w-full rounded border-[1.5px] text-black border-stroke bg-transparent py-3 px-5 font-normal outline-none focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter"
                            ></textarea>
                        </div>
                    </div>
                </div>
                <div v-else-if="activeTab === 'contents'">
                    <div class="grid grid-cols-3 gap-9">
                        <div class="flex flex-col p-6.5 pb-0">
                            <CustomSelect
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                label="A + content"
                                v-model="form.a_plus_content_id"
                                mode="single"
                                :searchable="true"
                                placeholder="Select"
                                :canClear="true"
                                :options="params.aPlusContents"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                            />
                        </div>
                        <div class="flex flex-col p-6.5 pb-0">
                            <CustomSelect
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                label="Second A + content"
                                v-model="form.sec_a_plus_content_id"
                                mode="single"
                                :searchable="true"
                                placeholder="Select"
                                :canClear="true"
                                :options="params.aPlusContents"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                            />
                        </div>
                        <div class="flex flex-col p-6.5 pb-0">
                            <CustomSelect
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.products[0].can_edit"
                                label="Snippet"
                                v-model="form.snippet_id"
                                mode="single"
                                placeholder="Select"
                                :canClear="true"
                                :searchable="true"
                                :options="params.snippets"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                            />
                        </div>
                    </div>
                </div>
                <div v-else-if="activeTab === 'tools'">
                    <div class="p-6 max-xl:py-4 max-xl:px-0">
                        <div class="flex flex-col md:w-[50%]">
                            <CustomSelect
                                label="Related calculator"
                                v-model="form.calculator_id"
                                mode="single"
                                :canClear="true"
                                :searchable="true"
                                placeholder="Select"
                                :options="params.calculators"
                                class="py-2 rounded-lg border-stroke bg-transparent"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr class="text-gray mt-6.5">
        <div class="grid grid-cols-1 gap-9" v-if="activeTab !== 'variations'">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template
                        v-if="(emitAction !== 'update' || auth.user_group.permissions_by_name.products[0].can_edit) && form.real_product_id">
                        <a
                            target="_blank"
                            :href="`/catalog/products/update/${form.real_product_id}/-1`"
                        >
                            <CustomButton
                                class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                                type="button"
                            >
                                <font-awesome-icon :icon="['far', 'house']"/>
                                Real product
                            </CustomButton>
                        </a>
                    </template>

                    <template
                        v-if="(emitAction !== 'update' || auth.user_group.permissions_by_name.products[0].can_edit) && form.drafted_product">
                        <CustomButton
                            @click="acceptDraftPopup = true"
                            class="flex items-center gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'file-check']"/>
                            Publish draft as actual
                        </CustomButton>
                    </template>

                    <template
                        v-if="(emitAction !== 'update' || auth.user_group.permissions_by_name.products[0].can_edit) && form.drafted_product">
                        <a
                            target="_blank"
                            :href="`/catalog/products/update/${form.drafted_product.id}/-1`"
                        >
                            <CustomButton
                                class="flex items-center gap-2 rounded bg-warning py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                                type="button"
                            >
                                <font-awesome-icon :icon="['fasr', 'file-pen']"/>
                                Drafted product
                            </CustomButton>
                        </a>
                    </template>

                    <template
                        v-if="(emitAction !== 'update' || auth.user_group.permissions_by_name.products[0].can_edit) && form.drafted_product">
                        <CustomButton
                            @click="forceDeleteDraftPopup = true"
                            class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['fasr', 'trash']"/>
                            Force delete draft
                        </CustomButton>
                    </template>

                    <template
                        v-if="(emitAction === 'update' || auth.user_group.permissions_by_name.products[0].can_add) && !form.drafted_product && !form.real_product_id">
                        <CustomButton
                            @click="submitCreateDraftProduct"
                            class="flex items-center gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['fasr', 'file-pen']"/>
                            Create draft
                        </CustomButton>
                    </template>

                    <template
                        v-if="emitAction === 'update' || auth.user_group.permissions_by_name.products[0].can_add">
                        <CustomButton
                            @click="cloneProduct.popupOpen = true"
                            class="flex items-center gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['fasr', 'clone']"/>
                            Clone
                        </CustomButton>
                    </template>

                    <template v-if="auth.user_group.permissions_by_name.products[0].can_delete">
                        <CustomButton
                            v-if="emitAction === 'update' && (form.language_id == -1 || form.translation_id)"
                            @click="store.commit('product/SET_DELETE_MODAL_VALUE', {
                                  value: true,
                                    id: form.language_id == -1 ? form.id : form.translation_id,
                                    deletingActionApi: form.language_id == -1 ? 'delete' : 'delete-translation',
                                    deletingText: form.language_id == -1 ? null : 'Deleting this translation will be permanent and cannot be undone. Once deleted, you will not be able to restore it.',
                                });"
                            class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'trash']"/>
                            Delete
                        </CustomButton>
                    </template>

                    <template
                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.products[0].can_edit">
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
<style lang="scss">
.absolute-holder-for-label {
    label {
        padding-right: 85px;
        @media (max-width: 576px) {
            padding-right: 53px;
        }
    }
}

.holder-with-button:has(> .filter-select-holder):not(:has(> .filter-select-holder:nth-of-type(2))) {
    align-items: flex-end;

    div:not(.filter-select-holder) {
        grid-column: span 1 / span 1;

        button {
            @media (min-width: 960px) {
                margin-left: 0;
            }

        }
    }
}

.holder-with-buttons:has(.filter-select-holder:only-child) {
    .filter-select-holder {
        grid-column: span 2 / span 2;
    }
}
</style>
