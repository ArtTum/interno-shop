<script setup>

import {computed, ref, toRefs, watch} from "vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomMediaList from "@components/media/CustomMediaList.vue";

const emits = defineEmits([
    'update:modelValue',
    'save'
]);
import {useStore} from "vuex";

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
    pageTypeName: {
        type: String,
    },
});

const {modelValue} = toRefs(props);

const items = ref(modelValue.value);

watch(modelValue, (newVal) => {
    items.value = newVal;

    if (!items.value.length) {
        items.value[0] = {
            description: null,
            profession: null,
            name: null,
            media_id: null,
            email: null,
            phone: null,
            images: []
        }
    }
}, {immediate: true});

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

const removeSingleGallery = () => {
    items.value[0].media_id = null;
    items.value[0].images = [];
}

</script>

<template>
    <div class="grid grid-cols-4 gap-9 text-left mt-5">
        <div class="flex flex-col col-span-2 gap-9">
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
        <div class="flex flex-col pb-0 col-span-1">
            <CustomInput
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                v-model="items[0].phone"
                name="phone"
                label="Phone *"
                type="text"
                placeholder="Phone"
            />
        </div>
        <div class="flex flex-col pb-0 col-span-1">
            <CustomInput
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                v-model="items[0].email"
                name="email"
                label="Email *"
                type="email"
                placeholder="Email"
            />
        </div>
    </div>
    <div class="grid grid-cols-2 gap-9 text-left mt-5">
        <div class="flex flex-col pb-0">
            <CustomInput
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                v-model="items[0].name"
                name="name"
                label="Name *"
                type="text"
                placeholder="Name"
            />
        </div>
        <div class="flex flex-col pb-0">
            <CustomInput
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                v-model="items[0].profession"
                name="profession"
                label="Profession *"
                type="text"
                placeholder="Profession"
            />
        </div>
    </div>
    <div class="grid grid-cols-1 gap-9">
        <div class="flex flex-col pb-0">
            <label class="mb-2.5 block font-medium text-black text-left">Description</label>
            <textarea
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                v-model="items[0].description"
                rows="6"
                placeholder="Enter description"
                class="w-full rounded border-[1.5px] text-black border-stroke bg-transparent py-3 px-5 font-normal outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter"
            ></textarea>
        </div>
    </div>
</template>

<style scoped>

</style>
