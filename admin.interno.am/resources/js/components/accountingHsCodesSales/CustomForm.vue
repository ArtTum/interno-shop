<script setup>

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
    'submit'
]);

const yearOptions = computed(() => {
    if (!form.value?.yearsMonths) return [];
    return Object.keys(form.value?.yearsMonths).map(year => ({
        label: year,
        value: parseInt(year)
    }));
});

const monthOptions = computed(() => {
    if (!form.value?.yearsMonths || !form.value.year) return [];
    const monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];
    const selectedYearData = form.value.yearsMonths[form.value.year] || [];
    return selectedYearData.map(month => ({
        label: monthNames[month - 1],
        value: month
    }));
});


const auth = computed(() => store.getters['auth/getUser']);

const exportFile = async (id) => {
    const timestamp = Date.now();
    let filename = `hs_sales_${form.value.year}-${form.value.month}_${timestamp}.${form.value.fileFormat.toLowerCase()}`;

    const response = await store.dispatch('accountingHsCodeSales/download', {
        filename: filename,
        year: form.value.year,
        month: form.value.month,
        format: form.value.fileFormat
    })

    const blob = new Blob([response.data], {type: response.headers['content-type']});
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
        <div class="max-w-xl">
            <div class="gap-4 pb-4">
                <CustomSelect
                    v-model="form.year"
                    mode="single"
                    label="Year *"
                    :options="yearOptions"
                    :searchable="false"
                    :canClear="false"
                />
                <CustomSelect
                    v-model="form.month"
                    mode="single"
                    label="Month *"
                    :options="monthOptions"
                    :searchable="false"
                    :canClear="false"
                />
                <CustomSelect
                    v-model="form.fileFormat"
                    mode="single"
                    label="File Format *"
                    :options="['CSV', 'XLSX']"
                    :searchable="false"
                    :canClear="false"
                />
                <div v-if="auth.user_group.permissions_by_name.accounting_files[0].can_edit">
                    <CustomButton
                        :disabled="!form.year || !form.month || !form.fileFormat"
                        @click="exportFile()"
                        class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-90 transition duration-200 ease-in-out"
                        type="button">
                        <font-awesome-icon icon="Download"/>
                        Download CSV
                    </CustomButton>
                </div>
            </div>
        </div>
    </form>
</template>
