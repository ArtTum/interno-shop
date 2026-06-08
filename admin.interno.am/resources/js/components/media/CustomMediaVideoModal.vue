<script setup>
import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

import {reactive, watch} from "vue";
import {validate} from "@validation/customValidation.js";

const props = defineProps({
    media: {
        type: Object,
        default: () => ({})
    },
    modelValue: {
        type: Boolean,
        default: false
    }
})

const form = reactive({
    id: '',
    video_type: '',
    video_url: '',
    video_typeRules: ['required'],
    video_urlRules: ['required'],
    errors: {},
    valid: true
});

// Watch the media prop directly instead of store
watch(() => props.media, (newMedia) => {
    if (newMedia) {
        form.id = newMedia.id || '';
        form.video_type = newMedia.video_type || '';
        form.video_url = newMedia.video_url || '';
    }
}, {immediate: true});

const confirm = () => {
    const errors = validate(form);
    if (Object.keys(errors).length > 0) {
        form.errors = errors;
        return false;
    }
    emits('video-data', form);
};

const emits = defineEmits([
    'video-data',
    'close'
])

const closeModal = () => {
    emits('close');
};
</script>

<template>
    <div
        v-show="modelValue"
        class="fixed  top-0 left-0 z-999999 flex h-full min-h-screen w-full items-center justify-center bg-black/90 px-4 py-5"
    >
        <div
            ref="target"
            class="relative w-full max-w-142.5 rounded-lg bg-white py-12 px-8 md:py-15 md:px-17.5"
        >
            <button
                type="button"
                @click="closeModal"
                class="absolute top-6 right-6 flex h-7 w-7 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white hover:text-primary"
            >
                <font-awesome-icon :icon="['fas', 'xmark']" class="size-6 text-black"/>
            </button>

            <div class="grid grid-cols-1 gap-9">
                <div class="col-span-1 text-sm">
                    <CustomSelect
                        label="Video Type"
                        v-model="form.video_type"
                        mode="single"
                        placeholder="Select type"
                        :options="[{value: 1, label: 'HTML 5'}, {value: 2, label: 'Youtube'}]"
                        class="py-2 rounded-lg border-stroke bg-transparent"
                        @update:modelValue="form.errors = validate(form)"
                        :error="form.errors['video_type']"
                    />
                    <CustomInput
                        v-model="form.video_url"
                        name="video_url"
                        label="Video url"
                        placeholder="Enter url"
                        @update:modelValue="form.errors = validate(form)"
                        :error="form.errors['video_url']"
                    />
                </div>
            </div>

            <div class="grid-cols-1 flex gap-5">
                <CustomButton
                    type="button"
                    @click="confirm"
                    class="ml-auto  gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                >
                    <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                    Confirm
                </CustomButton>
            </div>

        </div>
    </div>
</template>
