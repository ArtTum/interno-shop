<script setup>
import {computed, ref, toRefs, watch,} from "vue";
import {useStore} from "vuex";
import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import {validate} from "@validation/customValidation.js";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";
import CustomMediaList from "@components/media/CustomMediaList.vue";
import Switch from "@components/global/Switch.vue";

const store = useStore();

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    emitAction: {
        type: String
    }
});

const {modelValue} = toRefs(props);
const form = ref(modelValue.value);

const emits = defineEmits([
    'update:modelValue',
    'submit'
]);

watch(modelValue, (newVal) => {
    form.value = newVal;
});

watch(form.value, (newVal) => {
    emits('update:modelValue', newVal);
});

const fetchParams = () => {
    store.dispatch('loyaltyProgram/fetchParams', {
        id: form.value.id
    });
}

fetchParams();

const params = computed(() => store.getters['loyaltyProgram/getParams']);
const auth = computed(() => store.getters['auth/getUser']);

const removeSingleGallery = () => {
    form.value.media_id = null;
    form.value.media = [];
}

const mediaData = (media) => {
    return {
        id: '',
        media_id: media.id,
        product_id: form.value.id,
        path: media.original_path,
        type: media.type,
        file_type: media.file_type,
        video_type: '',
        video_url: '',
    };
}

const media = ref(form.value.media);
const insert = (data) => {
    data.media.forEach(mediaItem => {
        if (mediaItem.id) {
            form.value.media_id = mediaItem.id;
            const updatedMedia = mediaData(mediaItem);
            media.value = [updatedMedia];
        }
    });
};

</script>

<template>
    <form @submit.prevent="emits('submit')">
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 gap-9 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>
        <div class="grid grid-cols-5 gap-9">
            <div class="flex flex-col p-6.5">
                <CustomSelect
                    label="Languages"
                    v-model="form.language_id"
                    mode="single"
                    placeholder="Select languages"
                    :disabled="emitAction === 'create'"
                    :options="params.languages"
                    :searchable="true"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['language_id']"
                />
            </div>
        </div>
        <hr class="text-gray">
        <template v-if="form.language_id == -1">
            <div class="grid grid-cols-3 gap-9">
                <div class="flex flex-col p-6.5 pb-0">
                    <template v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.loyalty_programs[0].can_edit">
                        <CustomMediaList
                            @remove-images="removeSingleGallery"
                            label="Image"
                            @insert="insert"
                            :images="media"
                            :types="['images']"
                            mode="single"
                        />
                    </template>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-9 p-6.5 pb-0">
                <div class="flex flex-col pb-0 col-span-1">
                    <CustomInput
                        v-model="form.spent"
                        name="spent"
                        label="Spent *"
                        type="number"
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.loyalty_programs[0].can_edit"
                        placeholder="Enter spent"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['spent']"
                    />
                </div>
                <div class="flex flex-col pb-0 col-span-1">
                    <CustomInput
                        v-model="form.cashback"
                        name="cashback"
                        label="Cashback *"
                        type="number"
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.loyalty_programs[0].can_edit"
                        placeholder="Enter cashback"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['cashback']"
                    />
                </div>
                <div class="flex flex-col pb-0 col-span-1">
                    <CustomInput
                        v-model="form.discount"
                        name="discount"
                        label="Discount"
                        type="number"
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.loyalty_programs[0].can_edit"
                        placeholder="Enter discount"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['discount']"
                    />
                </div>
                <div class="flex flex-col pb-0 col-span-3 mb-10">
                    <h3 class="text-title-md mb-3">Advantages</h3>
                    <template v-for="(value, key) in params.options" :key="key">
                        <div class="grid grid-cols-3  pb-0">
                            <div class="flex-col pb-0 border-b border-stroke col-span-1 p-4"> {{ value }}:</div>
                            <div class="flex-col pb-0 w-fit border-b border-stroke col-span-1">
                                <Switch
                                    class="p-4"
                                    @change="(val) => {
                                    form.options[key] = val;
                                }"
                                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.loyalty_programs[0].can_edit"
                                    :value="form.options[key]"
                                    :id="key"
                                    label=""
                                />
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </template>
        <template v-else>
            <div class="grid grid-cols-1 gap-9">
                <div class="flex flex-col p-6.5 pb-0 mb-12">
                    <div class="grid grid-cols-2">
                        <CustomInput
                            v-model="form.name"
                            name="name"
                            label="Name *"
                            type="text"
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.loyalty_programs[0].can_edit"
                            placeholder="Enter name"
                            @keyup="form.errors = validate(form)"
                            :error="form.errors['name']"
                        />
                    </div>
                    <div>
                        <label class="mb-2.5 block font-medium text-black">Description</label>
                        <CKEditorComponent
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.loyalty_programs[0].can_edit"
                            :model="form.description"
                            @updateValue="(value) => {
                                form.description = value
                            }"
                        />
                    </div>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.loyalty_programs[0].can_delete">
                        <CustomButton
                            v-if="emitAction === 'update' && (form.language_id == -1 || form.translation_id)"
                            @click="store.commit('loyaltyProgram/SET_DELETE_MODAL_VALUE', {
                                    value: true,
                                    id: form.language_id == -1 ? form.id : form.translation_id,
                                    deletingActionApi: form.language_id == -1 ? 'delete' : 'delete-translation',
                                    deletingText: form.language_id == -1 ? null : 'Deleting this translation will be permanent and cannot be undone. Once deleted, you will not be able to restore it.',
                                });"
                            class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'trash']"/>
                            Delete
                        </CustomButton>
                    </template>
                    <template v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.loyalty_programs[0].can_edit">
                        <CustomButton
                            class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="submit"
                        >
                            <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                            Save
                        </CustomButton>
                    </template>
                </div>
            </div>
        </div>
    </form>
</template>
