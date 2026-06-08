<script setup>
import {computed, reactive, watch} from "vue";
import {useStore} from "vuex";
import CustomSelect from "@components/global/CustomSelect.vue";

const store = useStore();

const props = defineProps({
    params: {
        type: Object,
        required: false,
        default: {}
    },
})

const data = computed(() => store.getters['lead/getUpdateModalData']);
const form = reactive({});

watch(data, (newData) => {
    console.log('newData: ', newData.item);
    form.id = newData.item?.id;
    form.status_1_old = newData.item?.process?.status_1 || null;
    form.status_1 = newData.item?.process?.status_1 || null;
    form.status_1_desc = newData.item?.process?.status_1_desc || null;
    form.status_2 = newData.item?.process?.status_2 || null;
    form.status_2_desc = newData.item?.process?.status_2_desc || null;

});

const handeCancelClick = () => {
    store.commit('lead/SET_UPDATE_MODAL_DATA', {
        modalOpen: false,
        item: {}
    });
}

const updateAction = async () => {
    try {
        store.commit('lead/SET_UPDATE_MODAL_DATA', {
            modalOpen: false,
            item: {}
        });

        console.log('form: ', form)
        await store.dispatch('lead/update', form);

        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully updated'
        });

        await store.dispatch('lead/fetchPageData', props.params);
    } catch (error) {
        form.errors = error;
    }
};

const statusOptions = [
    {value: 'Not reachable', label: 'Not reachable'},
    {value: 'Uncertain', label: 'Uncertain'},
    {value: 'Cart received', label: 'Cart received'},
    {value: 'Craftsman contact received', label: 'Craftsman contact received'},
    {value: 'Cart purchased', label: 'Cart purchased'},
    {value: 'Craftsman too expensive', label: 'Craftsman too expensive'},
    {value: 'Material too expensive', label: 'Material too expensive'},
    {value: 'Craftsman + material too expensive', label: 'Craftsman + material too expensive'},
    {value: 'Competitor better', label: 'Competitor better'},
    {value: 'Alternative product better', label: 'Alternative product better'},
    {value: 'Not interested', label: 'Not interested'},
];
</script>

<template>
    <div
        v-show="data.modalOpen"
        class="fixed top-0 left-0 z-999999 flex h-full min-h-screen w-full items-center justify-center bg-black/90 px-4 py-5"
    >
        <div class="w-full max-w-203 rounded-lg bg-white  py-12 px-8 text-center md:py-15 md:px-17.5">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <CustomSelect
                        :disabled="!!form.status_2"
                        v-model="form.status_1"
                        mode="single"
                        label="Status 1"
                        placeholder="Select status 1"
                        :options="statusOptions"
                        :searchable="false"
                        :canClear="false"
                    />
                </div>
            </div>
            <div class="grid grid-cols-1 gap-6">
                <div class="flex flex-col">
                    <label class="mb-2.5 block font-medium text-black">Status 1 descriptions</label>
                    <textarea
                        :disabled="!!form.status_2"
                        v-model="form.status_1_desc"
                        rows="6"
                        placeholder="Enter meta description"
                        class="w-full rounded border-[1.5px] text-black border-stroke bg-transparent py-3 px-5 font-normal outline-none focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter"
                    ></textarea>
                </div>
            </div>
            <template v-if="form.status_1_old">
                <div class="grid grid-cols-1 gap-6 mt-5">
                    <div>
                        <CustomSelect
                            v-model="form.status_2"
                            mode="single"
                            label="Status 2"
                            placeholder="Select status 2"
                            :options="statusOptions"
                            :searchable="false"
                            :canClear="false"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-6">
                    <div class="flex flex-col">
                        <label class="mb-2.5 block font-medium text-black">Status 2 descriptions</label>
                        <textarea
                            v-model="form.status_2_desc"
                            rows="6"
                            placeholder="Enter meta description"
                            class="w-full rounded border-[1.5px] text-black border-stroke bg-transparent py-3 px-5 font-normal outline-none focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter"
                        ></textarea>
                    </div>
                </div>
            </template>
            <div class="grid-cols-1 flex gap-5 sm:grid-cols-1 mt-5">
                <button
                    @click="handeCancelClick"
                    class="block ml-auto  w-fit rounded border border-stroke bg-gray p-3 text-center font-medium text-black"
                >
                    Cancel
                </button>
                <button
                    @click="updateAction()"
                    class="block w-fit rounded border border-meta-3 bg-meta-3 p-3 text-center font-medium text-white hover:bg-opacity-90"
                >
                    Update
                </button>
            </div>
        </div>
    </div>
</template>
