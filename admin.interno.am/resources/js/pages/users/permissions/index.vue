<script setup>

import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import DeleteModal from "@components/global/DeleteModal.vue";
import CustomTableSecond from "@components/global/CustomTableSecond.vue";

import {computed, nextTick, ref} from 'vue'

import {useStore} from "vuex";
import {useRoute, useRouter} from "vue-router";
import CustomSelect from "@components/global/CustomSelect.vue";
import CustomInput from "@components/global/CustomInput.vue";

const store = useStore();
const route = useRoute();
const router = useRouter();

const params = ref({
    user_group_id: route.query.user_group_id === undefined ? -1 : Number(route.query.user_group_id),
});
const permissionsData = ref([]);
const userGroups = ref([]);

const updateQueryParams = async () => {
    await router.push({
        query: {
            ...route.query,
            ...params.value
        },
    });
};

const fetchPageData = async () => {
    let res = await store.dispatch('permission/fetchPageData', params.value);
    permissionsData.value = res.data.data;
    userGroups.value = res.data.userGroups;
};

const doPageFetching = async (isPagination = false) => {
    if (!isPagination) {
        params.value.page = 1;
    }
    await updateQueryParams();
    await fetchPageData();
};

fetchPageData();

const saveChanges = async () => {
    await store.dispatch('permission/update', {params: permissionsData.value});

    await doPageFetching();

    store.commit('notification/SET_NOTIFICATION', {
        visible: true,
        title: 'Success',
        message: 'Successfully updated'
    })
};
const auth = computed(() => store.getters['auth/getUser']);
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Permissions" :breadcrumb="[
            {path: '/dashboard', title: 'Dashboard'},
        ]"/>
        <CustomTableSecond
            title=""
            class="relative"
            @save="saveChanges"
            :button-info="params.user_group_id > 0 && auth.user_group.permissions_by_name.permissions[0].can_edit ? {
            title: 'Save',
            emitName: 'save',
            icon: 'floppy-disk',
            classes: 'bg-primary',
            disabled: (params.user_group_id < 0)
            } : null"
        >
            <template #header>
                <CustomSelect
                    @update:modelValue="() => {
                            nextTick(() => {
                                doPageFetching();
                            });
                        }"
                    v-model="params.user_group_id"
                    mode="single"
                    label="Select user group"
                    :options="userGroups"
                    :searchable="false"
                    :canClear="false"
                    placeholder="Select user group"
                />
            </template>

            <template #content>
                <div
                    class="grid grid-cols-9 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5 sticky-header text-black"
                >
                    <div class="col-span-2 flex items-center">
                        <p class="font-bold">Name</p>
                    </div>
                    <div class="col-span-1 items-center sm:flex">
                        <p class="font-bold">View</p>
                    </div>
                    <div class="col-span-1 flex items-center">
                        <p class="font-bold">Add</p>
                    </div>
                    <div class="col-span-1 flex items-center">
                        <p class="font-bold">Edit</p>
                    </div>
                    <div class="col-span-1 flex items-center">
                        <p class="font-bold">Delete</p>
                    </div>
                    <div class="col-span-1 flex items-center">
                        <p class="font-bold">Upload</p>
                    </div>
                    <div class="col-span-1 flex items-center">
                        <p class="font-bold">Export</p>
                    </div>
                    <div class="col-span-1 flex items-center">
                        <p class="font-bold">Updated date</p>
                    </div>
                </div>

                <template
                    v-for="(permission, key) in permissionsData"
                    :key="key"
                >
                    <div
                        class="grid grid-cols-9 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5">
                        <div class="col-span-2 flex items-center text-black" :class="{'font-bold': permission.is_parent}">
                            {{ permission.title }}:
                        </div>
                        <div class="col-span-1 flex items-center">
                            <CustomInput
                                :disabled="!auth.user_group.permissions_by_name.permissions[0].can_edit"
                                v-model="permission.can_view"
                                @input="permission.changed = true"
                                label=""
                                name="can_view"
                                type="checkbox"
                                class="mt-auto mb-auto"
                            />
                        </div>
                        <div class="col-span-1 flex items-center">
                            <CustomInput
                                :disabled="!auth.user_group.permissions_by_name.permissions[0].can_edit"
                                v-model="permission.can_add"
                                @input="permission.changed = true"
                                label=""
                                name="can_add"
                                type="checkbox"
                                class="mt-auto mb-auto"
                            />
                        </div>
                        <div class="col-span-1 flex items-center">
                            <CustomInput
                                :disabled="!auth.user_group.permissions_by_name.permissions[0].can_edit"
                                v-model="permission.can_edit"
                                @input="permission.changed = true"
                                label=""
                                name="can_edit"
                                type="checkbox"
                                class="mt-auto mb-auto"
                            />
                        </div>
                        <div class="col-span-1 flex items-center">
                            <CustomInput
                                :disabled="!auth.user_group.permissions_by_name.permissions[0].can_edit"
                                v-model="permission.can_delete"
                                @input="permission.changed = true"
                                label=""
                                name="can_delete"
                                type="checkbox"
                                class="mt-auto mb-auto"
                            />
                        </div>
                        <div class="col-span-1 flex items-center">
                            <CustomInput
                                :disabled="!auth.user_group.permissions_by_name.permissions[0].can_edit"
                                v-model="permission.can_upload"
                                @input="permission.changed = true"
                                label=""
                                name="can_upload"
                                type="checkbox"
                                class="mt-auto mb-auto"
                            />
                        </div>
                        <div class="col-span-1 flex items-center">
                            <CustomInput
                                :disabled="!auth.user_group.permissions_by_name.permissions[0].can_edit"
                                v-model="permission.can_export"
                                @input="permission.changed = true"
                                label=""
                                name="can_export"
                                type="checkbox"
                                class="mt-auto mb-auto"
                            />
                        </div>
                        <div class="col-span-1 flex items-center">
                            {{ permission.updated_at }}
                        </div>
                    </div>

                </template>
            </template>
        </CustomTableSecond>

        <DeleteModal
            @fetch="fetchPageData()"
            action-variable="permission/delete"
            getter-variable="permission/getDeleteModelValue"
            mutation-variable="permission/SET_DELETE_MODAL_VALUE"
        />

    </DefaultLayoutComponent>
</template>

<style lang="scss">
@import '@assets/scss/tables';

.sticky-header {
    position: sticky;
    top: 90px;
    left: 0;
    background: white;
    z-index: 1;
}
</style>
