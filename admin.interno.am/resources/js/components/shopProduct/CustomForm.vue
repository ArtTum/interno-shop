<script setup>
import {computed, nextTick, ref, watch} from "vue";
import {useStore} from "vuex";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomButton from "@components/global/CustomButton.vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import Switch from "@components/global/Switch.vue";
import CustomMediaList from "@components/media/CustomMediaList.vue";

const store = useStore();

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },
    params: {
        type: Object,
        required: true,
    },
    emitAction: {
        type: String,
        required: true,
    },
});

const emits = defineEmits(['update:modelValue', 'submit', 'language-change']);

const form = computed({
    get: () => props.modelValue,
    set: (value) => emits('update:modelValue', value),
});

const activeTab = ref('general');
const tabsRoutes = [
    {key: 'general', title: 'General *', icon: ['far', 'gear']},
    {key: 'content', title: 'Content *', icon: ['far', 'pen-to-square']},
    {key: 'seo', title: 'SEO', icon: ['fasds', 'robot']},
    {key: 'media', title: 'Media', icon: ['far', 'image']},
];

const attributePriceGroups = [
    {key: 'height', title: 'Length'},
    {key: 'unit', title: 'Measurement unit'},
    {key: 'size', title: 'Size'},
    {key: 'power', title: 'Power'},
];

const auth = computed(() => store.getters['auth/getUser']);
const permission = computed(() => auth.value?.user_group?.permissions_by_name?.shop_products?.[0] || {});
const canEdit = computed(() => auth.value?.superadmin || props.emitAction !== 'update' || permission.value.can_edit);
const canDelete = computed(() => auth.value?.superadmin || permission.value.can_delete);

watch(() => form.value.language_id, (languageId, oldLanguageId) => {
    if (languageId && languageId !== oldLanguageId) {
        emits('language-change', languageId);
    }
});

const mediaData = (media) => ({
    id: media.id,
    media_id: media.id,
    path: media.original_path || media.path,
    type: media.type,
    file_type: media.file_type,
    video_type: '',
    video_url: '',
});

const syncGalleryIds = () => {
    form.value.gallery_media_ids = (form.value.gallery || [])
        .map((media) => media.media_id || media.id)
        .filter(Boolean);
};

const insertMainMedia = (data) => {
    const media = data.media.find((mediaItem) => mediaItem.id);

    if (!media) return;

    form.value.media_id = media.id;
    form.value.image = media.original_path || media.path || '';
    form.value.media = [mediaData(media)];
};

const removeMainMedia = () => {
    form.value.media_id = null;
    form.value.image = '';
    form.value.media = [];
};

const insertGalleryMedia = (data) => {
    if (!form.value.gallery) {
        form.value.gallery = [];
    }

    data.media.forEach((mediaItem) => {
        if (!mediaItem.id) return;

        const exists = form.value.gallery.some((item) => (item.media_id || item.id) === mediaItem.id);
        if (!exists) {
            form.value.gallery.push(mediaData(mediaItem));
        }
    });

    syncGalleryIds();
    form.value.gallery_text = (form.value.gallery || []).map((media) => media.path).join('\n');
};

const removeGalleryMedia = async () => {
    await nextTick();
    syncGalleryIds();
    form.value.gallery_text = (form.value.gallery || []).map((media) => media.path).join('\n');
};

const ensureAttributePrices = () => {
    if (!form.value.attribute_prices) {
        form.value.attribute_prices = {};
    }

    attributePriceGroups.forEach((group) => {
        if (!Array.isArray(form.value.attribute_prices[group.key])) {
            form.value.attribute_prices[group.key] = [];
        }
    });
};

const attributeRows = (key) => {
    ensureAttributePrices();

    return form.value.attribute_prices[key];
};

const attributeOptions = (key, currentValue = null) => {
    const selectedIds = attributeRows(key)
        .map((row) => Number(row.attribute_value_id))
        .filter((value) => value && value !== Number(currentValue));

    return (props.params.attributeValues?.[key] || [])
        .filter((option) => !selectedIds.includes(Number(option.value)));
};

const addAttributePrice = (key) => {
    const options = attributeOptions(key);

    if (!options.length) {
        return;
    }

    attributeRows(key).push({
        attribute_value_id: options[0].value,
        price: 0,
    });
};

const removeAttributePrice = (key, index) => {
    attributeRows(key).splice(index, 1);
};

const submitForm = () => {
    syncGalleryIds();
    ensureAttributePrices();
    emits('submit');
};
</script>

<template>
    <form @submit.prevent="submitForm">
        <div
            v-if="form.errors && Object.keys(form.errors).length > 0"
            class="grid grid-cols-1 p-6 max-md:p-4 max-sm:p-2"
        >
            <AlertError :errors="form.errors"/>
        </div>

        <div class="border-b border-stroke p-6 max-md:p-4">
            <div class="flex flex-col max-w-[300px]">
                <CustomSelect
                    label="Languages"
                    v-model="form.language_id"
                    mode="single"
                    placeholder="Select language"
                    :disabled="!canEdit"
                    :options="params.languages"
                    :searchable="true"
                    :canClear="false"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    :error="form.errors?.language_id"
                />
            </div>
        </div>

        <div class="w-full p-6 max-md:p-4">
            <div class="overflow-x-auto mb-6">
                <div class="flex gap-9 border-b border-stroke">
                    <button
                        v-for="tabRoute in tabsRoutes"
                        :key="tabRoute.key"
                        type="button"
                        @click="activeTab = tabRoute.key"
                        :class="{
                            'text-primary border-primary': activeTab === tabRoute.key,
                            'border-transparent': activeTab !== tabRoute.key
                        }"
                        class="shrink-0 border-b-2 py-4 px-2 text-sm font-medium hover:text-primary md:text-base"
                    >
                        <font-awesome-icon :icon="tabRoute.icon"/>
                        {{ tabRoute.title }}
                    </button>
                </div>
            </div>

            <div v-if="activeTab === 'general'">
                <div class="grid grid-cols-4 gap-6 max-xl:grid-cols-2 max-sm:grid-cols-1">
                    <div>
                        <CustomSelect
                            label="Category"
                            v-model="form.shop_category_id"
                            mode="single"
                            placeholder="Select"
                            :disabled="!canEdit"
                            :options="params.categories"
                            :searchable="true"
                            :canClear="false"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                            :error="form.errors?.shop_category_id"
                        />
                    </div>
                    <div>
                        <CustomInput
                            :disabled="!canEdit"
                            v-model="form.global_slug"
                            name="global_slug"
                            label="Global slug"
                            type="text"
                            placeholder="Enter or will generate automatically"
                            :error="form.errors?.global_slug"
                        />
                    </div>
                    <div>
                        <CustomInput
                            :disabled="!canEdit"
                            v-model="form.option_code"
                            name="option_code"
                            label="Code"
                            type="text"
                            placeholder="Enter code"
                            :error="form.errors?.option_code"
                        />
                    </div>
                    <div>
                        <CustomInput
                            :disabled="!canEdit"
                            v-model="form.sort_order"
                            name="sort_order"
                            label="Sort order"
                            type="number"
                            placeholder="Enter sort order"
                            :error="form.errors?.sort_order"
                        />
                    </div>
                    <div>
                        <CustomInput
                            :disabled="!canEdit"
                            v-model="form.option_size"
                            name="option_size"
                            label="Size"
                            type="text"
                            placeholder="Enter size"
                            :error="form.errors?.option_size"
                        />
                    </div>
                    <div>
                        <CustomInput
                            :disabled="!canEdit"
                            v-model="form.option_quantity"
                            name="option_quantity"
                            label="Quantity"
                            type="text"
                            placeholder="Enter quantity"
                            :error="form.errors?.option_quantity"
                        />
                    </div>
                    <div>
                        <CustomSelect
                            label="Type"
                            v-model="form.option_type_id"
                            mode="single"
                            placeholder="Select type"
                            :disabled="!canEdit"
                            :options="params.optionTypes"
                            :searchable="true"
                            :canClear="false"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                            :error="form.errors?.option_type_id"
                        />
                    </div>
                    <div>
                        <CustomSelect
                            label="Color"
                            v-model="form.option_color_id"
                            mode="single"
                            placeholder="Select color"
                            :disabled="!canEdit"
                            :options="params.optionColors"
                            :searchable="true"
                            :canClear="false"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                            :error="form.errors?.option_color_id"
                        />
                    </div>
                    <div>
                        <CustomInput
                            :disabled="!canEdit"
                            v-model="form.option_unit"
                            name="option_unit"
                            label="Unit"
                            type="text"
                            placeholder="Enter unit"
                            :error="form.errors?.option_unit"
                        />
                    </div>
                    <div>
                        <CustomInput
                            :disabled="!canEdit"
                            v-model="form.option_piece"
                            name="option_piece"
                            label="Piece"
                            type="text"
                            placeholder="Enter piece"
                            :error="form.errors?.option_piece"
                        />
                    </div>
                    <div>
                        <CustomInput
                            :disabled="!canEdit"
                            v-model="form.option_height"
                            name="option_height"
                            label="Height"
                            type="text"
                            placeholder="Enter height"
                            :error="form.errors?.option_height"
                        />
                    </div>
                    <div>
                        <CustomInput
                            :disabled="!canEdit"
                            v-model="form.option_material"
                            name="option_material"
                            label="Material"
                            type="text"
                            placeholder="Enter material"
                            :error="form.errors?.option_material"
                        />
                    </div>
                    <div class="flex items-end pb-2">
                        <Switch
                            :disabled="!canEdit"
                            @change="(value) => form.is_new = value"
                            :value="form.is_new"
                            id="shop_product_is_new"
                            label="New product"
                        />
                    </div>
                    <div class="flex items-end pb-2">
                        <Switch
                            :disabled="!canEdit"
                            @change="(value) => form.is_temporarily_unavailable = value"
                            :value="form.is_temporarily_unavailable"
                            id="shop_product_temporarily_unavailable"
                            label="Temporarily unavailable"
                        />
                    </div>
                    <div class="flex items-end pb-2">
                        <Switch
                            :disabled="!canEdit"
                            @change="(value) => form.status = value"
                            :value="form.status"
                            id="shop_product_status"
                            label="Active"
                        />
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-2 gap-6 max-xl:grid-cols-1">
                    <div
                        v-for="group in attributePriceGroups"
                        :key="group.key"
                        class="rounded-sm border border-stroke bg-white"
                    >
                        <div class="flex items-center justify-between gap-4 border-b border-stroke px-4 py-3">
                            <h4 class="font-medium text-black">{{ group.title }} prices</h4>
                            <button
                                type="button"
                                class="rounded bg-primary px-3 py-2 text-sm font-medium text-white hover:bg-opacity-80 disabled:opacity-60"
                                :disabled="!canEdit || !attributeOptions(group.key).length"
                                @click="addAttributePrice(group.key)"
                            >
                                Add {{ group.title }} price
                            </button>
                        </div>

                        <div class="space-y-4 p-4">
                            <div
                                v-for="(row, index) in attributeRows(group.key)"
                                :key="`${group.key}-${index}`"
                                class="grid grid-cols-[1fr_160px_36px] items-start gap-4 max-sm:grid-cols-1"
                            >
                                <CustomSelect
                                    v-model="row.attribute_value_id"
                                    mode="single"
                                    :label="group.title"
                                    placeholder="Select"
                                    :disabled="!canEdit"
                                    :options="attributeOptions(group.key, row.attribute_value_id)"
                                    :searchable="true"
                                    :canClear="false"
                                    class="py-2 rounded-lg border-stroke bg-transparent"
                                />
                                <CustomInput
                                    :disabled="!canEdit"
                                    v-model="row.price"
                                    :name="`${group.key}_price_${index}`"
                                    label="Price"
                                    type="number"
                                    placeholder="Enter price"
                                />
                                <button
                                    v-if="canEdit"
                                    type="button"
                                    class="mt-8 flex h-9 w-9 items-center justify-center rounded border border-danger text-danger hover:bg-danger hover:text-white"
                                    title="Remove"
                                    @click="removeAttributePrice(group.key, index)"
                                >
                                    <font-awesome-icon :icon="['fas', 'trash-can']"/>
                                </button>
                            </div>
                            <p v-if="!attributeRows(group.key).length" class="text-sm text-gray-500">
                                No {{ group.title }} price added.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="activeTab === 'content'">
                <div class="grid grid-cols-6 gap-6 max-md:grid-cols-1">
                    <div class="md:col-span-3">
                        <CustomInput
                            :disabled="!canEdit"
                            v-model="form.title"
                            name="title"
                            label="Name *"
                            type="text"
                            placeholder="Enter name"
                            :error="form.errors?.title"
                        />
                    </div>
                    <div class="md:col-span-3">
                        <CustomInput
                            :disabled="!canEdit"
                            v-model="form.slug"
                            name="slug"
                            label="Slug"
                            type="text"
                            placeholder="Enter or will generate automatically"
                            :error="form.errors?.slug"
                        />
                    </div>
                    <div class="md:col-span-6">
                        <label class="mb-2.5 block font-medium text-black">Short description</label>
                        <textarea
                            :disabled="!canEdit"
                            v-model="form.short_description"
                            name="short_description"
                            rows="4"
                            placeholder="Enter short description"
                            class="w-full rounded-lg border border-stroke bg-transparent py-4 px-6 pr-6 text-black outline-none focus:border-primary focus-visible:shadow-none"
                            :class="{'is-invalid': form.errors?.short_description, 'disabled': !canEdit}"
                        ></textarea>
                    </div>
                    <div class="md:col-span-6">
                        <label class="mb-2.5 block font-medium text-black">Description</label>
                        <textarea
                            :disabled="!canEdit"
                            v-model="form.description"
                            name="description"
                            rows="8"
                            placeholder="Enter description"
                            class="w-full rounded-lg border border-stroke bg-transparent py-4 px-6 pr-6 text-black outline-none focus:border-primary focus-visible:shadow-none"
                            :class="{'is-invalid': form.errors?.description, 'disabled': !canEdit}"
                        ></textarea>
                    </div>
                </div>
            </div>

            <div v-if="activeTab === 'seo'">
                <div class="grid grid-cols-5 gap-6 max-md:grid-cols-1">
                    <div class="md:col-span-3">
                        <CustomInput
                            :disabled="!canEdit"
                            v-model="form.meta_title"
                            name="meta_title"
                            label="Meta title"
                            type="text"
                            placeholder="Enter meta title"
                            :error="form.errors?.meta_title"
                        />
                    </div>
                    <div class="md:col-span-2">
                        <CustomInput
                            :disabled="!canEdit"
                            v-model="form.meta_keywords"
                            name="meta_keywords"
                            label="Meta keywords"
                            type="text"
                            placeholder="Enter meta keywords"
                            :error="form.errors?.meta_keywords"
                        />
                    </div>
                    <div class="md:col-span-5">
                        <label class="mb-2.5 block font-medium text-black">Meta description</label>
                        <textarea
                            :disabled="!canEdit"
                            v-model="form.meta_description"
                            name="meta_description"
                            placeholder="Enter meta description"
                            rows="5"
                            class="w-full rounded-lg border border-stroke bg-transparent py-4 px-6 pr-6 text-black outline-none focus:border-primary focus-visible:shadow-none"
                            :class="{'is-invalid': form.errors?.meta_description, 'disabled': !canEdit}"
                        ></textarea>
                    </div>
                </div>
            </div>

            <div v-if="activeTab === 'media'">
                <div class="grid grid-cols-6 gap-6 max-md:grid-cols-1">
                    <div class="md:col-span-3">
                        <CustomMediaList
                            v-if="canEdit"
                            @remove-images="removeMainMedia"
                            @insert="insertMainMedia"
                            :images="form.media || []"
                            :types="['images']"
                            mode="single"
                            label="Main image"
                            :language-id="form.language_id"
                        />
                        <div v-else>
                            <label class="mb-2.5 block font-medium text-black">Main image</label>
                            <img v-if="form.image" class="max-w-[180px] rounded border border-stroke" :src="form.image" :alt="form.title">
                            <span v-else>-</span>
                        </div>
                    </div>
                    <div class="md:col-span-3">
                        <CustomMediaList
                            v-if="canEdit"
                            @remove-images="removeGalleryMedia"
                            @insert="insertGalleryMedia"
                            :images="form.gallery || []"
                            :types="['images']"
                            mode="multiple"
                            label="Gallery"
                            :language-id="form.language_id"
                        />
                        <div v-else>
                            <label class="mb-2.5 block font-medium text-black">Gallery</label>
                            <div class="flex flex-wrap gap-4">
                                <img
                                    v-for="media in (form.gallery || [])"
                                    :key="media.media_id || media.id"
                                    class="max-w-[120px] rounded border border-stroke"
                                    :src="media.path"
                                    :alt="form.title"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="text-gray mt-6.5">
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="emitAction === 'update' && canDelete">
                        <CustomButton
                            @click="store.commit('shopProduct/SET_DELETE_MODAL_VALUE', {
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

                    <template v-if="canEdit">
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
