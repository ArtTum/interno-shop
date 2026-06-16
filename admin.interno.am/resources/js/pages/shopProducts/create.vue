<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import CustomForm from "@components/shopProduct/CustomForm.vue";
import {computed, reactive} from "vue";
import {useRouter} from "vue-router";
import {useStore} from "vuex";

const store = useStore();
const router = useRouter();
const params = computed(() => store.getters['shopProduct/getParams']);

const form = reactive({
    language_id: null,
    shop_category_id: null,
    global_slug: '',
    price: 0,
    attribute_prices: {
        height: [],
        unit: [],
        size: [],
        power: [],
    },
    kind: '',
    option_code: '',
    option_size: '',
    option_quantity: '',
    option_type_id: null,
    option_unit: '',
    option_piece: '',
    option_height: '',
    option_material: '',
    option_color_id: null,
    media_id: null,
    media: [],
    image: '',
    gallery_media_ids: [],
    gallery: [],
    gallery_text: '',
    is_new: false,
    is_temporarily_unavailable: false,
    status: true,
    sort_order: 0,
    title: '',
    slug: '',
    short_description: '',
    description: '',
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
    errors: {},
});

const fetchParams = async () => {
    const data = await store.dispatch('shopProduct/fetchParams', {language_id: form.language_id || 0});
    form.language_id = form.language_id || data.base_language_id || data.languages?.[0]?.value || null;
};

const submit = async () => {
    try {
        form.errors = {};
        const response = await store.dispatch('shopProduct/create', form);
        await router.push(`/shop-products/update/${response.data.data.id}/${form.language_id}`);
        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully created',
        });
    } catch (error) {
        form.errors = error;
    }
};

fetchParams();
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Create shop product" :breadcrumb="[
            {path: '/', title: 'Dashboard'},
            {path: '/shop-products', title: 'Shop Products'},
        ]"/>

        <div class="rounded-sm border border-stroke bg-white shadow-default">
            <div class="border-b border-stroke px-6 py-4">
                <h3 class="font-medium text-black">Fields</h3>
            </div>
            <CustomForm
                v-model="form"
                :params="params"
                emit-action="create"
                @language-change="fetchParams"
                @submit="submit"
            />
        </div>
    </DefaultLayoutComponent>
</template>
