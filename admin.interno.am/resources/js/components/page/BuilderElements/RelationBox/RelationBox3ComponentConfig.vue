<script setup>

import {computed, ref, toRefs, watch} from "vue";
import CustomMediaList from "@components/media/CustomMediaList.vue";
import {debounce} from "lodash";
import CustomSelect from "@components/global/CustomSelect.vue";
import {useStore} from "vuex";
import CustomInput from "@components/global/CustomInput.vue";

const emits = defineEmits([
    'update:modelValue'
]);

const store = useStore();

const auth = computed(() => store.getters['auth/getUser']);

const props = defineProps({
    modelValue: {
        type: Array,
        default: []
    },
    isUpdate: {
        type: Boolean,
        default: false
    },
    categories: {
        type: Array,
        default: []
    },
    pages: {
        type: Array,
        default: []
    },
    posts: {
        type: Array,
        default: []
    },
    pageTypeName: {
        type: String,
    },
    languageId: {
        type: Number,
    },
    selfId: {
        type: [Number, String],
        default: null
    },
});

const {modelValue} = toRefs(props);

const items = ref(modelValue.value);

watch(modelValue, (newVal) => {
    items.value = newVal;
    if (!items.value.length) {
        items.value[0] = {
            category_translation_id: null,
            product_translation_id: null,
            page_translation_id: null,
            media_id: null,
            priority: null,
            config: null,
            images: []
        }
    }
}, {immediate: true});

const removeSingleGallery = () => {
    items.value[0].media_id = null;
    items.value[0].images = [];
}

watch(
    () => items.value,
    (newVal) => {
        emits('update:modelValue', newVal);
    },
    {deep: true}
);

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
const insert = (data) => {
    data.media.forEach(media => {
        if (media.id) {
            items.value[0].media_id = media.id
            items.value[0].images = [mediaData(media)];
        }
    });
}

const productsOptions = ref([]);

const productsAutocomplete = debounce(async (term) => {
    productsOptions.value = await store.dispatch('product/autocomplete', {
        field: 'name',
        term,
        forOrder: false,
        language_id: props.languageId
    });
}, 300);

const autocompleteRequest = async (alreadySelectedId, term = '') => {
    productsOptions.value = await store.dispatch('product/autocomplete', {
        field: 'name',
        term,
        alreadySelectIds: [alreadySelectedId],
        language_id: props.languageId
    });
}

if (props.isUpdate) {
    autocompleteRequest(items.value[0].product_translation_id);
}

</script>

<template>
    <div class="grid grid-cols-2 gap-9 text-left mt-5">
        <div class="flex flex-col gap-9">
            <CustomMediaList
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                @remove-images="removeSingleGallery"
                label="Image"
                @insert="insert"
                :images="items[0].images ? items[0].images : []"
                :types="['images']"
                mode="single"
            />
        </div>
        <div class="flex flex-col gap-9">
            <CustomSelect
                :disabled="(!!items[0].category_translation_id || !!items[0].page_translation_id) || (isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit)"
                @search-change="productsAutocomplete"
                label="Related product"
                v-model="items[0].product_translation_id"
                mode="single"
                :searchable="true"
                :canClear="true"
                :need-autocomplete="true"
                placeholder="Select"
                :options="productsOptions"
                class="py-2 rounded-lg border-stroke bg-transparent"
            />
        </div>
    </div>
    <div class="grid grid-cols-3 gap-9 text-left mt-5">
        <div class="flex flex-col gap-9">
            <CustomSelect
                :disabled="(!!items[0].product_translation_id || !!items[0].page_translation_id) || (isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit)"
                label="Related category"
                v-model="items[0].category_translation_id"
                mode="single"
                :canClear="true"
                placeholder="Select"
                :options="categories"
                :searchable="true"
                class="py-2 rounded-lg border-stroke bg-transparent"
            />
        </div>
        <div class="flex flex-col gap-9">
            <CustomSelect
                :disabled="((!!items[0].product_translation_id || !!items[0].category_translation_id)) || (isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit)"
                label="Related page"
                v-model="items[0].page_translation_id"
                mode="single"
                :excluded-value="selfId"
                :canClear="true"
                :searchable="true"
                placeholder="Select"
                :options="pages"
                class="py-2 rounded-lg border-stroke bg-transparent"
            />
        </div>
        <div class="flex flex-col gap-9">
            <CustomSelect
                :disabled="((!!items[0].product_translation_id || !!items[0].category_translation_id)) || (isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit)"
                label="Related post"
                v-model="items[0].page_translation_id"
                :excluded-value="selfId"
                mode="single"
                :canClear="true"
                :searchable="true"
                placeholder="Select"
                :options="posts"
                class="py-2 rounded-lg border-stroke bg-transparent"
            />
        </div>
    </div>
    <div class="grid grid-cols-2 gap-9 text-left mt-5">
        <div class="flex flex-col gap-9">
            <CustomInput
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                v-model="items[0].title"
                name="title"
                label="Title"
                type="text"
                placeholder="Title"
            />
        </div>
        <div class="flex flex-col gap-9">
            <CustomInput
                :disabled="(!!items[0].product_translation_id || !!items[0].category_translation_id || !!items[0].page_translation_id) || (isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit)"
                v-model="items[0].url"
                name="url"
                label="URL"
                type="text"
                placeholder="URL"
            />
        </div>
    </div>
</template>

<style scoped>

</style>
