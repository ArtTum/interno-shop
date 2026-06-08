<script setup>

import CustomTextarea from "@components/global/CustomTextarea.vue";
import CustomDatePicker from "@components/global/CustomDatePicker.vue";
import CustomButton from "@components/global/CustomButton.vue";

import {computed, ref, toRefs} from "vue";
import {useStore} from "vuex";
import CustomSelect from "@components/global/CustomSelect.vue";
const store = useStore()
const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
});

const {modelValue} = toRefs(props);
const form = ref(modelValue.value);

const emits = defineEmits([
    'update:modelValue',
    'submit'
]);

const auth = computed(() => store.getters['auth/getUser']);

const exportFile = async (id) => {
    const now = new Date();
    const formattedDate = now.toISOString().split('T')[0];
    const formattedTime = now.toTimeString().split(' ')[0].replace(/:/g, '')
    let filename;
    if (form.value.date_range) {
        filename = `tax-orders_${form.value.date_range.replace(/ /g, '_')}.csv`;
    } else {
        filename = `tax-orders_${formattedDate}-${formattedTime}.csv`;
    }

    const response = await store.dispatch('accountingFile/download', {
        id:id,
        filename:filename,
        date_range:form.value.date_range,
        order_ids:form.value.order_ids,
    })

    const blob = new Blob([response.data], { type: response.headers['content-type'] });
    const link = document.createElement('a');
    link.setAttribute("target", "_blank");
    link.href = window.URL.createObjectURL(blob);
    link.download = filename;
    link.click();
};

const downloadFile = (fileUrl, fileName) => {
    const link = document.createElement('a');
    link.setAttribute("target", "_blank");
    link.href = fileUrl;
    link.download = fileName;
    link.click();
};

</script>

<template>
    <form @submit.prevent="emits('submit')">
        <div class="grid grid-cols-2 gap-9">
            <div class="flex flex-col p-6.5">
                <CustomDatePicker
                    :disabled="!auth.user_group.permissions_by_name.accounting_files[0].can_edit"
                    placeholder="yyyy/mm/dd"
                    label="Select date range"
                    format="Y-m-d"
                    v-model="form.date_range"
                    :mode="'range'"
                />
                <CustomTextarea
                    :disabled="!auth.user_group.permissions_by_name.accounting_files[0].can_edit"
                    :label="`Orders IDs to export, separated by comma (,) <br>
                            <span class='text-red'>if orders IDs provided date filter will be ignored</span>`"
                    name="description"
                    :rows="4"
                    v-model="form.order_ids"
                />
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.accounting_files[0].can_edit">
                        <CustomButton
                            :disabled="!form.date_range && !form.order_ids"
                            @click="exportFile('all')"
                            class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon icon="download"/>
                            Download
                        </CustomButton>
                    </template>
                </div>
            </div>

            <div class="flex flex-col p-6.5">
                <h3 class="text-title-sm font-semibold text-black my-3">Files History</h3>
                <div class="datatable-container data-table-common">
                    <table class="table w-full table-auto datatable-table">
                        <thead>
                        <tr class="bg-gray-2">
                            <td><span class="pl-5">#</span></td>
                            <td>Date</td>
                            <td>File name</td>
                            <td>Action</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(row, key) in form.row" :key="key" class="odd:bg-white even:bg-gray-100">
                            <td>{{ key + 1}}</td>
                            <td>{{ row.created_at}}</td>
                            <td>
                                {{ row.name }}
                            </td>
                            <td>
                                <template v-if="auth.user_group.permissions_by_name.accounting_files[0].can_edit">
                                    <CustomButton
                                        @click="downloadFile(row.path, row.name)"
                                        class="flex items-center gap-2 rounded py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                                        type="button"
                                    >
                                        <font-awesome-icon class="text-primary" icon="download"/>
                                    </CustomButton>
                                </template>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </form>
</template>

<style scoped>
.data-table-common .datatable-table > tbody > tr > td {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
}
</style>
