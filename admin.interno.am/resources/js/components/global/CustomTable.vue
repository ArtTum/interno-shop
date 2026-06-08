<script setup>
import TooltipOne from "@components/global/Tooltips/TooltipOne.vue";
import CustomButton from "@components/global/CustomButton.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import CustomInput from "@components/global/CustomInput.vue";

import {computed, onUpdated, ref, toRefs, watch} from 'vue'

const inRequest = ref(false);

onUpdated(() => {
    const draggables = document.querySelectorAll('.task')
    const droppables = document.querySelectorAll('.swim-lane')

    draggables.forEach((task) => {
        task.addEventListener('dragstart', () => {
            task.classList.add('is-dragging')
        })
        task.addEventListener('dragend', () => {
            if (!inRequest.value) {
                inRequest.value = true;
                const orderedTasks = Array.from(document.querySelectorAll('.task')).map(task => task.dataset.id);
                emits('update-priority', orderedTasks);
                task.classList.remove('is-dragging');

                setTimeout(() => {
                    inRequest.value = false;
                }, 500);
            }
        })
    })
    droppables.forEach((zone) => {
        zone.addEventListener('dragover', (e) => {
            e.preventDefault()
            const bottomTask = insertAboveTask(zone, e.clientY)
            const curTask = document.querySelector('.is-dragging')
            if (!bottomTask) {
                if (curTask) {
                    zone.appendChild(curTask)
                }
            } else {
                if (curTask) {
                    zone.insertBefore(curTask, bottomTask)
                }
            }
        })
    })

    function insertAboveTask(zone, mouseY) {
        const els = Array.from(zone.querySelectorAll('.task:not(.is-dragging)'))
        let closestTask = null
        let closestOffset = Number.NEGATIVE_INFINITY

        els.forEach((task) => {
            const {top} = task.getBoundingClientRect()

            const offset = mouseY - top

            if (offset < 0 && offset > closestOffset) {
                closestOffset = offset
                closestTask = task
            }
        })
        return closestTask
    }
})

const props = defineProps({
    createRoute: {
        type: String,
    },
    showFilter: {
        type: Boolean,
        default: true
    },
    isDrag: {
        type: Boolean,
        default: false
    },
    forProduct: {
        type: Boolean,
        default: false
    },
    forAttributeType: {
        type: Boolean,
        default: false
    },
    uploadInfo: {
        type: Object,
        default: null,
    },
    bulkActions: {
        type: Array,
        default: [],
    },
    columns: {
        type: Array,
        default: []
    },
    modelValue: {
        type: Object,
        required: true
    },
    pagination: {
        type: [Object, Boolean],
    },
    mainSearch: Object,
    pageData: {
        type: Array,
        default: []
    },
    isAllChecked: {
        type: Boolean,
        default: false
    },
    isCheckbox: {
        type: Boolean,
        default: false
    },
    hasBulkDelete: {
        type: Boolean,
        default: false
    },
    disableBulkDeleteButton: {
        type: Boolean,
        default: false
    },
    permissionName: {
        type: String,
        required: false
    },
    exportByPageFilter: {
        type: Boolean,
        default: false
    },
    storeN: {
        type: String,
        required: false
    },
    translationFunctionality: {
        type: Object,
        default: null
    },
})

const toggleAll = (checked) => {
    emits('update-selected-items', checked);
};

const bulkStatusApply = () => {
    emits('apply-bulk-status', bulk_status.value);
};

const {modelValue} = toRefs(props);
const params = ref(modelValue.value);

watch(modelValue, (newVal) => {
    params.value = newVal;
});

const emits = defineEmits([
    'update:modelValue',
    'do-page-fetching',
    'update-priority',
    'update-selected-items',
    'apply-bulk-status',
    'bulk-delete',
])

watch(params.value, () => {
    emits('update:modelValue', params.value)
});

import {useStore} from "vuex";

const store = useStore();
const importActions = ref(false);
const exportActions = ref(false);
const uploadFileInput = ref(null);
const bulk_status = ref(null);

const importType = ref({
    value: null,
    options: [
        {value: 1, label: 'Only insert news'},
        {value: 2, label: 'Only update'},
        {value: 3, label: 'Update or insert'},
    ],
    fileUploaded: false,
})

const exportFileTypes = [
    {value: 1, label: 'Products'},
    {value: 2, label: 'Product variants'},
];

const upload = async () => {
    const formData = new FormData();
    formData.append('file', importType.value.fileUploaded);
    formData.append('import_type', importType.value.value);
    formData.append('pageType', props.uploadInfo.storeName);
    let uploadAction = 'upload';

    if (props.forProduct && params.value.actionFileType === 2) {
        uploadAction = 'uploadVariations';
    }
    await store.dispatch(`${props.uploadInfo.storeName}/${uploadAction}`, formData);

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

const exportFile = async (isAll, justTemplate, byPageFilter) => {
    let ids = [];
    let filename = `${props.uploadInfo.downloadVariantsFileName}.xlsx`;
    let exportStoreName = 'exportFileVariations';

    if (!props.forProduct || params.value.actionFileType === 1) {
        if (
            props.uploadInfo.downloadFileName === 'products' || props.uploadInfo.downloadFileName === 'items' || props.uploadInfo.downloadFileName === 'coupons' ||
            props.uploadInfo.downloadFileName === 'attribute_values' || props.uploadInfo.downloadFileName === 'attributes' || props.uploadInfo.downloadFileName === 'categories'
            || props.uploadInfo.downloadFileName === 'pages' || props.uploadInfo.downloadFileName === 'reviews' || props.uploadInfo.downloadFileName === 'translations'
            || props.uploadInfo.downloadFileName === 'users' || props.uploadInfo.downloadFileName === 'posts'
        ) {
            filename = `${props.uploadInfo.downloadFileName}.xlsx`;
            exportStoreName = 'exportFile';
        } else {
            filename = `${props.uploadInfo.downloadFileName}.csv`;
            exportStoreName = 'exportFile';
        }
    }

    if (!isAll) {
        for (let i = 0; i < props.pageData.length; i++) {
            ids.push(props.pageData[i].id);
        }
    }
    const response = await store.dispatch(`${props.uploadInfo.storeName}/${exportStoreName}`, {
        ...params.value,
        isAll,
        byPageFilter,
        justTemplate,
        ids
    })

    const blob = new Blob([response.data], {type: response.headers['content-type']});
    const link = document.createElement('a');
    link.setAttribute("target", "_blank");
    link.href = window.URL.createObjectURL(blob);
    link.download = filename;
    link.click();
};

const auth = computed(() => store.getters['auth/getUser']);
const paginateCustom = () => {
    setTimeout(() => {
        emits('do-page-fetching', true);
    }, 50);
}

watch(() => params.value.per_page, () => {
    emits('do-page-fetching');
});


const selectedLanguagesForTranslation = ref([]);

const generateAITranslations = async () => {
    await store.dispatch(`${props.storeN}/translateAI`, {
        language_ids: selectedLanguagesForTranslation.value,
        params: {
            ...params.value
        },
        current_language_id: params.value.language_id
    })

    selectedLanguagesForTranslation.value = [];

    store.commit('notification/SET_NOTIFICATION', {
        visible: true,
        title: 'Success',
        message: `Successfully submitted (Will work in background)`
    });
}
</script>
<template>
    <div class="flex flex-col gap-5 md:gap-7 2xl:gap-10">
        <div class="rounded-sm border border-stroke bg-white shadow-default">
            <div class="data-table-common data-table-one max-w-full overflow-x-auto">
                <div class="datatable-wrapper datatable-loading no-footer sortable searchable fixed-columns">
                    <template v-if="uploadInfo">
                        <div class="flex justify-between gap-5 px-7.5 pt-6 pb-2 max-sm:px-2 max-md:flex-col">
                            <div class="flex flex-wrap gap-3">
                                <a v-if="permissionName && auth.user_group.permissions_by_name[permissionName][0].can_upload"
                                   target="_blank"
                                   :href="'/tools/file-uploads?type=' + uploadInfo.for"
                                 class="max-2xsm:w-[100%]">
                                    <CustomButton
                                        class="max-2xsm:w-[100%] justify-center flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 h-[50px]"
                                    >
                                        <font-awesome-icon :icon="['fass', 'upload']"/>
                                        Uploads
                                    </CustomButton>
                                </a>
                                <template
                                    v-if="permissionName && auth.user_group.permissions_by_name[permissionName][0].can_export && !exportActions">
                                    <CustomButton
                                        @click="exportActions = true, importActions = false, params.actionFileType = null"
                                        class="flex max-2xsm:w-[100%] justify-center items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 h-[50px]"
                                    >
                                        <font-awesome-icon :icon="['far', 'file-export']"/>
                                        Export
                                    </CustomButton>
                                </template>
                                <template
                                    v-if="permissionName && auth.user_group.permissions_by_name[permissionName][0].can_upload && !importActions">
                                    <CustomButton
                                        @click="exportActions = false, importActions = true, params.actionFileType = null"
                                        class="flex items-center max-2xsm:w-[100%] justify-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 h-[50px]"
                                    >
                                        <font-awesome-icon :icon="['far', 'file-arrow-down']"/>
                                        Import
                                    </CustomButton>
                                </template>
                            </div>
                            <div class="">
                                <template v-if="exportActions">
                                    <hr class="text-gray mb-4 md:hidden">
                                    <div class="flex flex-col items-end gap-4 max-md:items-start">
                                        <div class="flex items-center flex-wrap gap-4">
                                            <template v-if="forProduct">
                                                <CustomInput
                                                    v-model="params.withMeta"
                                                    label="With meta fields"
                                                    name="withMeta"
                                                    type="checkbox"
                                                    class=""
                                                />
                                            </template>
                                            <template v-if="forProduct">
                                                <CustomSelect
                                                    placeholder="File type"
                                                    v-model="params.actionFileType"
                                                    mode="single"
                                                    :options="exportFileTypes"
                                                    :searchable="false"
                                                    :canClear="false"
                                                    :invalid-feedback-place="false"
                                                    class="py-1 rounded-lg border-stroke bg-transparent w-[200px]"
                                                />
                                            </template>
                                        </div>
                                       <div class="flex flex-wrap gap-3">
                                           <template v-if="!forProduct || params.actionFileType">
                                               <template v-if="props.pageData.length">
                                                   <template v-if="exportByPageFilter">
                                                       <CustomButton
                                                           @click="exportFile(true, false, true)"
                                                           class="flex max-2xsm:w-[100%] justify-center items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 h-[50px]"
                                                       >
                                                           <font-awesome-icon :icon="['far', 'file-export']"/>
                                                           Full export by filters
                                                       </CustomButton>
                                                   </template>
                                                   <template v-else>
                                                       <CustomButton
                                                           @click="exportFile(false, false, false)"
                                                           class="flex max-2xsm:w-[100%] justify-center items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 h-[50px]"
                                                       >
                                                           <font-awesome-icon :icon="['far', 'file-export']"/>
                                                           Export page data
                                                       </CustomButton>
                                                   </template>
                                               </template>
                                               <CustomButton
                                                   @click="exportFile(true, false, false)"
                                                   class="flex items-center max-2xsm:w-[100%] justify-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 h-[50px]"
                                               >
                                                   <font-awesome-icon :icon="['far', 'file-export']"/>
                                                   Export all
                                               </CustomButton>
                                               <CustomButton
                                                   @click="exportFile(true, true, false)"
                                                   class="flex items-center max-2xsm:w-[100%] justify-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 h-[50px]"
                                               >
                                                   <font-awesome-icon :icon="['far', 'file-export']"/>
                                                   Export template
                                               </CustomButton>
                                           </template>
                                       </div>


                                    </div>
                                </template>
                                <template v-if="importActions">
                                    <hr class="text-gray mb-4 md:hidden">
                                    <div class="grid grid-cols-2 gap-3  md:w-[600px] max-sm:grid-cols-1">
                                        <div class="">
                                            <CustomSelect
                                                placeholder="Import type"
                                                v-model="importType.value"
                                                mode="single"
                                                :options="params.actionFileType !== 2 && uploadInfo.for !== 9  && uploadInfo.for !== 13 && uploadInfo.for !== 14 ? importType.options : [{value: 2, label: 'Only update'}]"
                                                :searchable="false"
                                                :canClear="false"
                                                :invalid-feedback-place="false"
                                                class="py-1 rounded-lg border-stroke bg-transparent w-full"
                                            />
                                        </div>
                                        <div v-if="forProduct" class="">
                                            <CustomSelect
                                                @update:modelValue="(val) => {
                                                   if (val === 2) importType.value = 2;
                                                }"
                                                placeholder="File type"
                                                v-model="params.actionFileType"
                                                mode="single"
                                                :options="exportFileTypes"
                                                :searchable="false"
                                                :canClear="false"
                                                :invalid-feedback-place="false"
                                                class="py-1 rounded-lg border-stroke bg-transparent w-[100%]"
                                            />
                                        </div>
                                        <div class="col-span-2 max-sm:col-span-1">
                                            <div class="">
                                                <input
                                                    @change="(event) => {
                                                        handleUpload(event);
                                                    }"
                                                    accept=".xlsx,.xls,.csv"
                                                    ref="uploadFileInput"
                                                    type="file"
                                                    class="cursor-pointer w-[100%] rounded border-[1.5px] border-stroke bg-transparent font-medium outline-none transition file:mr-5 file:border-collapse file:cursor-pointer file:border-0 file:border-r file:border-solid file:border-stroke file:bg-whiter file:py-3 file:px-5 file:hover:bg-primary file:hover:bg-opacity-10 focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter"
                                                />
                                            </div>
                                        </div>
                                        <div class="flex col-span-2 max-sm:col-span-1">
                                            <template
                                                v-if="importType.fileUploaded && importType.value && (!forProduct || params.actionFileType)">
                                                <CustomButton
                                                    @click="upload()"
                                                    class="items-center w-[200px] ml-auto gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 h-[46px]"
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
                        <hr class="text-gray my-4">
                    </template>

                    <template
                        v-if="permissionName && auth.user_group.permissions_by_name[permissionName][0].can_edit && translationFunctionality">
                        <div class="flex px-7.5 py-2 gap-3 flex-wrap max-sm:px-2">
                            <div class="min-w-[300px]">
                                <CustomSelect
                                    v-model="selectedLanguagesForTranslation"
                                    mode="tags"
                                    placeholder="Select languages"
                                    :options="translationFunctionality.languages"
                                    :invalid-feedback-place="false"
                                    :close-on-select="false"
                                    :searchable="true"
                                    :with-general="false"
                                    class="rounded-lg border-stroke bg-transparent w-full"
                                />
                            </div>
                            <div>
                                <CustomButton
                                    :title="!selectedLanguagesForTranslation.length ? 'First select languages' : ''"
                                    :disabled="!selectedLanguagesForTranslation.length"
                                    type="button"
                                    @click="generateAITranslations()"
                                    class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 h-[100%]"
                                >
                                    <font-awesome-icon :icon="['fas', 'robot']"/>
                                    Generate AI translations for selected languages
                                </CustomButton>
                            </div>

                        </div>
                        <hr class="text-gray my-4">
                    </template>

                    <div class="datatable-top w-full flex-wrap">
                        <div v-if="mainSearch.visibility" class="datatable-search flex max-w-100 w-[100%]">
                            <input
                                v-model="params.search"
                                class="datatable-input rounded-tl rounded-bl"
                                :placeholder="mainSearch.placeholder"
                                type="search"
                                title="Search within table"
                                @keyup.enter="emits('do-page-fetching')"
                            >
                            <TooltipOne
                                :button-params="mainSearch.tooltip.button"
                                :tooltip-text="mainSearch.tooltip.text"
                            />
                        </div>
                        <div
                            v-if="isCheckbox"
                            class="datatable-search flex"
                        >
                            <CustomSelect
                                v-model="bulk_status"
                                placeholder="Bulk actions"
                                mode="single"
                                :parentDivClasses='"w-[100%]"'
                                :options="bulkActions"
                                :searchable="false"
                                :canClear="true"
                                class="rounded-tl rounded-bl py-0.5 border-stroke bg-transparent"
                            />
                            <div class="group relative inline-block">
                                <button :disabled="!bulk_status" @click="bulkStatusApply"
                                        :class="['inline-flex font-medium text-white rounded-tr rounded-br bg-primary py-3.5 px-3.5', !bulk_status ? 'disabled' : '']">
                                    Apply
                                </button>
                            </div>
                        </div>
                        <div
                            v-if="(hasBulkDelete && permissionName && auth.user_group.permissions_by_name[permissionName][0].can_delete)"
                        >
                            <CustomButton
                                @click="emits('bulk-delete')"
                                class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-3 px-4.5 font-medium text-white hover:bg-opacity-80"
                                type="button"
                                :disabled="disableBulkDeleteButton"
                            >
                                <font-awesome-icon :icon="['far', 'trash']"/>
                                Ջնջել ընտրվածը
                            </CustomButton>
                        </div>

                        <div class="datatable-dropdown ml-auto">
                            <label v-if="pagination">
                                <select
                                    class="datatable-selector "
                                    v-model="params.per_page"
                                >
                                    <option :value="200">200</option>
                                    <option :value="300">300</option>
                                    <option :value="400">400</option>
                                    <option :value="500">500</option>
                                </select> մեկ էջում
                            </label>
                        </div>

                    </div>
                    <div
                        class="bg-amber-200 px-7.5 py-2.5"
                        v-if="(params.ordering_field !== 'priority' || params.ordering_direction !== 'asc') && isDrag"
                    >
                        If you want to use drag for priority, you need to change your sort type to priority by,
                        <strong
                            class="cursor-pointer"
                            @click="params.ordering_field = 'priority', params.ordering_direction = 'asc', emits('do-page-fetching')"
                        >
                            clicking here
                        </strong>
                    </div>
                    <div
                        class="bg-amber-200 px-7.5 py-2.5"
                        v-if="params.attribute_type_id <= 0 && isDrag && forAttributeType"
                    >
                        For easier drag-and-drop prioritization, filter by the specific attribute that matter
                        most.
                    </div>
                    <div class="table-holder index table-responsive">
                        <table class="table w-full table-auto datatable-table">
                            <tbody class="swim-lane">
                            <tr class="bg-gray-2 text-left thead">
                                <th v-if="isCheckbox || (hasBulkDelete && permissionName && auth.user_group.permissions_by_name[permissionName][0].can_delete)">
                                    <input
                                        :checked="isAllChecked"
                                        type="checkbox"
                                        @change="toggleAll($event.target.checked)"
                                        class="form-checkbox h-5 w-5 text-blue-600 cursor-pointer"
                                    />
                                </th>
                                <template
                                    v-for="(column, index) in columns"
                                    :key="index"
                                >
                                    <th class="py-4 px-4 font-medium text-black xl:pl-11">
                                        <div
                                            class="flex items-center gap-1.5"
                                        >
                                            <p v-html="column.title"></p>

                                            <template v-if="column.tooltip">
                                                <TooltipOne
                                                    :button-params="column.tooltip.button"
                                                    :tooltip-text="column.tooltip.text"
                                                    tooltipClass="rounded-md bg-primary py-2 px-2"
                                                />
                                            </template>

                                            <template v-if="column.key">
                                                <a href="#" class="datatable-sorter">
                                                    <div
                                                        @click="
                                                    params.ordering_field = column.key,
                                                    params.ordering_direction = params.ordering_direction === 'desc' ? 'asc' : 'desc',
                                                    emits('do-page-fetching')"
                                                        class="inline-flex flex-col space-y-[2px]">
                                                        <font-awesome-icon
                                                            :icon="['fass', 'sort']"
                                                            class="size-3.5 opacity-70"
                                                        />
                                                    </div>
                                                </a>

                                            </template>
                                        </div>
                                    </th>
                                </template>
                            </tr>
                            <slot></slot>
                            </tbody>
                        </table>

                    </div>
                    <div
                        v-if="pagination"
                        class="datatable-bottom"
                    >
                        <div class="datatable-info">
                            Ցուցադրվում է {{ pagination.showing.from }} -ից {{ pagination.showing.to }} գրառումները
                            {{ pagination.total_items }} -ից
                        </div>

                        <vue-awesome-paginate
                            v-if="params.per_page < pagination.total_items"
                            :total-items="pagination.total_items"
                            :items-per-page="params.per_page"
                            :max-pages-shown="3"
                            v-model="params.page"
                            @click="paginateCustom"
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
            </div>
        </div>
    </div>

</template>
<style lang="scss" scoped>
.index tr th:last-child {
    min-width: 70px;
}
@media (max-width: 576px) {
     .datatable-top {
            padding: 1rem;
    }
}
</style>
