<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import DeleteModal from "@components/global/DeleteModal.vue";
import {computed, ref} from "vue";
import {useRoute, useRouter} from "vue-router";
import {useStore} from "vuex";

const store = useStore();
const route = useRoute();
const router = useRouter();

const params = ref({
    page: Number(route.query.page) || 1,
    per_page: Number(route.query.per_page) || 25,
    search: route.query.search || '',
    language_id: Number(route.query.language_id) || 0,
    parent_id: route.query.parent_id ?? '',
    status: route.query.status ?? '',
});

const auth = computed(() => store.getters['auth/getUser']);
const permission = computed(() => auth.value?.user_group?.permissions_by_name?.shop_categories?.[0] || {});
const canAdd = computed(() => auth.value?.superadmin || permission.value.can_add);
const canDelete = computed(() => auth.value?.superadmin || permission.value.can_delete);
const pageData = computed(() => store.getters['shopCategory/getPageData']);

const updateQueryParams = async () => {
    await router.push({query: {...params.value}});
};

const fetchPageData = async () => {
    const data = await store.dispatch('shopCategory/fetchPageData', params.value);
    if (!params.value.language_id && data.base_language_id) {
        params.value.language_id = data.base_language_id;
    }
};

const doFetch = async () => {
    params.value.page = 1;
    await updateQueryParams();
    await fetchPageData();
};

fetchPageData();
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Shop Categories" :breadcrumb="[{path: '/', title: 'Dashboard'}]" />

        <div class="mb-4 rounded-sm border border-stroke bg-white p-4 shadow-default">
            <div class="grid grid-cols-5 gap-3 max-xl:grid-cols-2 max-sm:grid-cols-1">
                <input v-model="params.search" class="rounded border border-stroke px-3 py-2 text-black" placeholder="Search name, slug, meta title" @keyup.enter="doFetch" />
                <select v-model.number="params.language_id" class="rounded border border-stroke px-3 py-2 text-black" @change="doFetch">
                    <option v-for="language in pageData.languages" :key="language.value" :value="language.value">{{ language.label }}</option>
                </select>
                <select v-model="params.parent_id" class="rounded border border-stroke px-3 py-2 text-black" @change="doFetch">
                    <option value="">All parents</option>
                    <option value="root">Root only</option>
                    <option v-for="parent in pageData.parents" :key="parent.value ?? 'none'" :value="parent.value" v-show="parent.value">{{ parent.label }}</option>
                </select>
                <select v-model="params.status" class="rounded border border-stroke px-3 py-2 text-black" @change="doFetch">
                    <option value="">All statuses</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <div class="flex gap-2">
                    <button type="button" class="rounded bg-black px-4 py-2 font-medium text-white" @click="doFetch">Filter</button>
                    <RouterLink v-if="canAdd" to="/shop-categories/create" class="rounded bg-primary px-4 py-2 font-medium text-white">Create</RouterLink>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto rounded-sm border border-stroke bg-white shadow-default">
            <table class="w-full min-w-220 text-left">
                <thead class="bg-gray-50 text-black">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Slug</th>
                        <th class="px-4 py-3">Parent</th>
                        <th class="px-4 py-3">Meta title</th>
                        <th class="px-4 py-3">Children</th>
                        <th class="px-4 py-3">Products</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in pageData.data" :key="item.id" class="border-t border-stroke">
                        <td class="px-4 py-3 font-medium text-black">#{{ item.id }}</td>
                        <td class="px-4 py-3 text-black">{{ item.name || '-' }}</td>
                        <td class="px-4 py-3 text-black">{{ item.slug || item.global_slug }}</td>
                        <td class="px-4 py-3 text-black">{{ item.parent_name || '-' }}</td>
                        <td class="px-4 py-3 text-black">{{ item.meta_title || '-' }}</td>
                        <td class="px-4 py-3 text-black">{{ item.children_count }}</td>
                        <td class="px-4 py-3 text-black">{{ item.products_count }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded px-2 py-1 text-xs font-medium" :class="item.status ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger'">
                                {{ item.status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-3">
                                <RouterLink :to="`/shop-categories/update/${item.id}/${params.language_id}`" class="text-primary">Edit</RouterLink>
                                <button v-if="canDelete" type="button" class="text-meta-1" @click="store.commit('shopCategory/SET_DELETE_MODAL_VALUE', {value: true, id: item.id})">Delete</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <DeleteModal
            @fetch="fetchPageData()"
            action-variable="shopCategory/delete"
            getter-variable="shopCategory/getDeleteModelValue"
            mutation-variable="shopCategory/SET_DELETE_MODAL_VALUE"
        />
    </DefaultLayoutComponent>
</template>
