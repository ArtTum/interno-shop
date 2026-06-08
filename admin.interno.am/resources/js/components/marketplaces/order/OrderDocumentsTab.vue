<script setup>

import {useStore} from "vuex";
import {computed, reactive, ref} from "vue";
import CustomTable from "@components/global/CustomTable.vue";
import DeleteModal from "@components/global/DeleteModal.vue";
import CustomButton from "@components/global/CustomButton.vue";
import CustomInput from "@components/global/CustomInput.vue";
import EADModal from "@components/order/EADModal.vue";

const params = ref({per_page: null})
const store = useStore()
const props = defineProps({
    order_documents: {
        type: Array
    },
    order_id: {
        type: Number
    },
    eadUrl: {
        type: String
    },
    status: {
        type: Number
    },
    full_reshipment: {}
});

const emits = defineEmits([
    'fetch',
]);
const fetchPageData = () => {
    emits('fetch');
};
const pageData = computed(() => store.getters['documentSetting/getPageData']);
const auth = computed(() => store.getters['auth/getUser']);

const orderDocuments = (id) => {
    const document = props.order_documents.filter(doc => doc.document_setting_id === id);
    return document.length ? document[0] : null;
};

const generateDocument = async (id, document_setting_id) => {
    await store.dispatch('order/generate', {
        id: id,
        order_id: props.order_id,
        document_setting_id: document_setting_id,
    });
    emits('fetch');
    store.commit('notification/SET_NOTIFICATION', {
        visible: true,
        title: 'Success',
        message: 'Successfully generate'
    })
};

const isModalOpen = ref(false);
const packaging = reactive({
    order_id: props.order_id,
    count: '',
    type: '',
    length: '',
    width: '',
    height: '',
    orderLanguage: false,
});

const openModal = () => {
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const handleConfirm = async () => {
    try {
        const response = await store.dispatch('order/createExportAccompanyingDocument', packaging);
        if (response) {
            store.commit('notification/SET_NOTIFICATION', {
                visible: true,
                title: 'Success',
                message: 'Successfully'
            });
            closeModal();
            emits('fetch');
        }
    } catch (error) {
        console.error("Error creating document:", error);
    }
};

</script>

<template>
    <div class="flex flex-wrap justify-end gap-6 mb-6">
        <div class="flex flex-wrap gap-4">
            <custom-button
                @click="openModal"
                type="button"
                class="gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80">
                <font-awesome-icon :icon="['fas', 'plus']"/>
                Generate EAD (Ausfuhrbegleitdokument)
            </custom-button>
            <a
                target="_blank"
                :href="`${eadUrl}?v=${Date.now()}`"
                v-if="eadUrl"
                class="text-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-fit"
                type="button"
            >
                <font-awesome-icon :icon="['far', 'download']"/>
                Download EAD (Ausfuhrbegleitdokument)
            </a>
        </div>
    </div>
    <EADModal
        :isOpen="isModalOpen"
        title="Generate Export Accompanying Document (Ausfuhrbegleitdokument)"
        @close="closeModal"
        @confirm="handleConfirm"
    >
        <div class="p-2 ead-form-fields">
            <CustomInput
                v-model="packaging.count"
                class="col-span-1"
                label="Packaging count"
                type="number"
                placeholder="Enter value"
            />
            <CustomInput
                v-model="packaging.type"
                class="col-span-1"
                label="Packaging type"
                type="text"
                placeholder="Enter value"
            />
            <CustomInput
                v-model="packaging.length"
                class="col-span-1"
                label="Packaging length"
                type="number"
                placeholder="Enter value"
            />
            <CustomInput
                v-model="packaging.width"
                class="col-span-1"
                label="Packaging width"
                type="number"
                placeholder="Enter value"
            />
            <CustomInput
                v-model="packaging.height"
                label="Packaging height"
                type="number"
                placeholder="Enter value"
            />
            <CustomInput
                v-model="packaging.orderLanguage"
                label="Document with order language"
                type="checkbox"
            />
        </div>
    </EADModal>
    <CustomTable
        table-name="Order documents"
        :main-search="{visibility: false}"
        @do-page-fetching=""
        @debounced-do-page-fetching=""
        v-model="params"
        :pagination="false"
        :columns="[
              { title: 'ID'},
              { title: 'Name' },
              { title: 'Document number' },
              { title: 'Document date' },
              { title: 'Action' },
            ]"
    >
        <template
            v-for="(item, index) in pageData.data"
            :key="index"
        >
            <tr>
                <td class="py-5 px-4 pl-9 xl:pl-11" width="150">
                    <h5 class="font-medium text-black">#{{ item.id }}</h5>
                </td>
                <td class="py-5 px-4 pl-9 xl:pl-11">
                    <h5 class="font-medium text-black">{{ item.name }}</h5>
                </td>
                <td class="py-5 px-4 pl-9 xl:pl-11">
                    <h5 class="font-medium text-black">
                        {{ orderDocuments(item.id)?.generate_number }}
                    </h5>
                </td>
                <td class="py-5 px-4 pl-9 xl:pl-11">
                    <h5 class="font-medium text-black">
                        {{ orderDocuments(item.id)?.updated_at }}
                    </h5>
                </td>
                <td class="py-5 px-4" width="150">
                    <div class="flex items-center space-x-3.5">
                        <div v-if="orderDocuments(item.id)">
                            <button
                                @click="generateDocument(orderDocuments(item.id)?.id, item.id)"
                                v-if="!orderDocuments(item.id).order_sub_documents.length && item.id !== 4"
                                type="button"
                                class="hover:text-primary"
                                title="Regenerate"
                            >
                                <font-awesome-icon :icon="['fas', 'rotate-right']"/>
                            </button>
                        </div>
                        <div v-else>
                            <button
                                v-if="(full_reshipment === null) && item.id === 1 && item.statuses && Array.isArray(item.statuses) && item.statuses.includes(status)"
                                @click="generateDocument(null, item.id)"
                                type="button"
                                class="hover:text-primary"
                                title="Generate"
                            >
                                <font-awesome-icon :icon="['fas', 'plus']"/>
                            </button>
                            <button
                                v-else-if="item.id === 2 && item.statuses && Array.isArray(item.statuses) "
                                @click="generateDocument(null, item.id)"
                                type="button"
                                class="hover:text-primary"
                                title="Generate"
                            >
                                <font-awesome-icon :icon="['fas', 'plus']"/>
                            </button>
                            <button
                                v-else-if="item.id === 3 &&  item.generate_on_new_order"
                                @click="generateDocument(null, item.id)"
                                type="button"
                                class="hover:text-primary"
                                title="Generate"
                            >
                                <font-awesome-icon :icon="['fas', 'plus']"/>
                            </button>
                            <!--                            <button-->
                            <!--                                v-else-if="(full_reshipment === null) && item.id === 4 &&  item.create_automatically_after_refunding"-->
                            <!--                                @click="generateDocument(null, item.id)"-->
                            <!--                                type="button"-->
                            <!--                                class="hover:text-primary"-->
                            <!--                                title="Generate"-->
                            <!--                            >-->
                            <!--                                <font-awesome-icon :icon="['fas', 'plus']"/>-->
                            <!--                            </button>-->
                        </div>
                        <a
                            target="_blank"
                            :href="orderDocuments(item.id).path + '?v=' + Date.now()"
                            v-if="orderDocuments(item.id)"
                            class="hover:text-primary"
                            title="View document"
                        >
                            <font-awesome-icon :icon="['fas', 'eye']"/>
                        </a>
                        <span v-if="item.id === 1 || item.id === 2 || item.id === 3">
                            <a
                                target="_blank"
                                :href="orderDocuments(item.id).base_path + '?v=' + Date.now()"
                                v-if="orderDocuments(item.id) && orderDocuments(item.id).base_path"
                                class="hover:text-primary"
                                title="View document base"
                            >
                                <font-awesome-icon :icon="['fas', 'eye']"/>
                            </a>
                        </span>
                        <button
                            v-if="orderDocuments(item.id) && status === 3 && item.id === 1"
                            type="button"
                            @click="store.commit('order/SET_DELETE_DOCUMENT_MODAL_VALUE', {
                                value: true,
                                id: orderDocuments(item.id).id
                            });"
                            class="hover:text-primary"
                            title="Delete"
                        >
                            <font-awesome-icon :icon="['fas', 'trash-can']"/>
                        </button>
                    </div>
                </td>
            </tr>
            <template
                v-if="orderDocuments(item.id) && orderDocuments(item.id).order_sub_documents"
                v-for="(subItem, i) in orderDocuments(item.id).order_sub_documents" :key="i"
            >
                <tr>
                    <td class="py-5 px-4 pl-9 xl:pl-11" width="150">
                        <h5 class="font-medium text-black">#{{ 4 + subItem.document_number }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">{{ item.name + ' - ' + subItem.document_number }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">
                            {{ orderDocuments(item.id)?.generate_number + ' - ' + subItem.document_number }}
                        </h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">
                            {{ subItem?.updated_at }}
                        </h5>
                    </td>
                    <td class="py-5 px-4" width="150">
                        <div class="flex items-center space-x-3.5">
                            <a
                                target="_blank"
                                :href="subItem.path + '?v=' + Date.now()"
                                class="hover:text-primary ml-3.5"
                                title="View document"
                            >
                                <font-awesome-icon :icon="['fas', 'eye']"/>
                            </a>
                        </div>
                    </td>
                </tr>
            </template>
        </template>
    </CustomTable>
    <DeleteModal
        @fetch="fetchPageData()"
        action-variable="order/deleteDocument"
        getter-variable="order/getDeleteDocumentModelValue"
        mutation-variable="order/SET_DELETE_DOCUMENT_MODAL_VALUE"
    />
</template>

<style lang="scss" scoped>
.ead-form-fields {
    &:deep(.invalid-feedback) {
        display: none;
    }
}
</style>
