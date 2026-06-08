<script setup>
import {computed, ref, toRefs, watch} from "vue";
import {useStore} from "vuex";
import CustomButton from "@components/global/CustomButton.vue";
import CustomInput from "@components/global/CustomInput.vue";
import BulletPointsComponentItemDragAndDrop
    from "@components/page/BuilderElements/BulletPoints/BulletPointsComponentItemDragAndDrop.vue";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";

const store = useStore();
const auth = computed(() => store.getters['auth/getUser']);
const newItem = ref(null);

const emits = defineEmits([
    'update:modelValue',
    'save'
]);

const props = defineProps({
    modelValue: {
        type: Object,
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
const component = ref(modelValue.value);

watch(modelValue, (newVal) => {
    component.value = newVal;

    if (Object.keys(component.value.config).length === 0) {
        component.value.config = {
            bullet_color: '',
        }
    }
}, {immediate: true});

watch(
    () => component.value,
    (newVal) => {
        emits('update:modelValue', newVal);
    },
    {deep: true}
);

</script>

<template>
    <div class="grid grid-cols-3 gap-9 text-left mt-5">
        <div class="flex flex-col gap-9">
            <CustomInput
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                v-model="component.config.bullet_color"
                name="bullet-color"
                label="Bullet points color *"
                type="text"
                placeholder="default #454343"
            />
        </div>
    </div>
    <div class="flex justify-center">
        <div>
            <template v-if="!newItem">
                <CustomButton
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    @click="newItem = {row_text: null}"
                    title="Add new item"
                    type="button"
                    class="h-[45px] flex items-center gap-2 rounded bg-meta-3 px-3.5 mt-5 font-medium text-white hover:bg-opacity-80 ml-auto"
                >
                    <font-awesome-icon :icon="'plus'" class="size-5"/>
                    New item
                </CustomButton>
            </template>
            <template v-else>
                <div class="flex">
                    <CustomButton
                        @click="newItem = null"
                        class="block w-full rounded border border-stroke bg-gray px-4.5 py-2.5 text-center font-medium text-black hover:bg-opacity-60"
                        type="button"
                    >
                        <font-awesome-icon :icon="['far', 'arrow-rotate-left']"/>
                        Cancel
                    </CustomButton>

                    <template v-if="!newItem.row_text">
                        <CustomButton
                            disabled
                            class="flex items-center gap-2 ml-3 rounded bg-primary px-4.5 py-2.5  font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                            Save
                        </CustomButton>
                    </template>
                    <template v-else>
                        <CustomButton
                            @click="emits('save', newItem), newItem = null"
                            class="flex items-center gap-2 ml-3 rounded bg-primary px-4.5 py-2.5  font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                            Save
                        </CustomButton>
                    </template>
                </div>
            </template>
        </div>
    </div>
    <template v-if="newItem">
        <div class="text-left my-5 px-6.5">
            <div>
                <label class="block font-medium text-black mb-2">Row text *</label>
            </div>
            <CKEditorComponent
                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                :model="newItem.row_text"
                @updateValue="(value) => {newItem.row_text = value}"
            />
        </div>
    </template>
    <template v-if="component.items">
        <BulletPointsComponentItemDragAndDrop
            class="col-8"
            v-model="component.items"
        />
    </template>
</template>

<style scoped>

</style>
