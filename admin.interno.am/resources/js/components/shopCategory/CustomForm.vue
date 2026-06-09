<script setup>
import {computed, ref, watch} from "vue";
import {useStore} from "vuex";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomButton from "@components/global/CustomButton.vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import Switch from "@components/global/Switch.vue";

const store = useStore();

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },
    params: {
        type: Object,
        required: true,
    },
    emitAction: {
        type: String,
        required: true,
    },
});

const emits = defineEmits(['update:modelValue', 'submit', 'language-change']);

const form = computed({
    get: () => props.modelValue,
    set: (value) => emits('update:modelValue', value),
});

const activeTab = ref('general');
const tabsRoutes = [
    {key: 'general', title: 'General *', icon: ['far', 'gear']},
    {key: 'content', title: 'Content *', icon: ['far', 'pen-to-square']},
    {key: 'seo', title: 'SEO', icon: ['fasds', 'robot']},
];

const auth = computed(() => store.getters['auth/getUser']);
const permission = computed(() => auth.value?.user_group?.permissions_by_name?.shop_categories?.[0] || {});
const canEdit = computed(() => auth.value?.superadmin || props.emitAction !== 'update' || permission.value.can_edit);
const canDelete = computed(() => auth.value?.superadmin || permission.value.can_delete);

watch(() => form.value.language_id, (languageId, oldLanguageId) => {
    if (languageId && languageId !== oldLanguageId) {
        emits('language-change', languageId);
    }
});
</script>

<template>
    <form @submit.prevent="emits('submit')">
        <div
            v-if="form.errors && Object.keys(form.errors).length > 0"
            class="grid grid-cols-1 p-6 max-md:p-4 max-sm:p-2"
        >
            <AlertError :errors="form.errors"/>
        </div>

        <div class="border-b border-stroke p-6 max-md:p-4">
            <div class="flex flex-col max-w-[300px]">
                <CustomSelect
                    label="Languages"
                    v-model="form.language_id"
                    mode="single"
                    placeholder="Select language"
                    :disabled="!canEdit"
                    :options="params.languages"
                    :searchable="true"
                    :canClear="false"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    :error="form.errors?.language_id"
                />
            </div>
        </div>

        <div class="w-full p-6 max-md:p-4">
            <div class="overflow-x-auto mb-6">
                <div class="flex gap-9 border-b border-stroke">
                    <button
                        v-for="tabRoute in tabsRoutes"
                        :key="tabRoute.key"
                        type="button"
                        @click="activeTab = tabRoute.key"
                        :class="{
                            'text-primary border-primary': activeTab === tabRoute.key,
                            'border-transparent': activeTab !== tabRoute.key
                        }"
                        class="shrink-0 border-b-2 py-4 px-2 text-sm font-medium hover:text-primary md:text-base"
                    >
                        <font-awesome-icon :icon="tabRoute.icon"/>
                        {{ tabRoute.title }}
                    </button>
                </div>
            </div>

            <div v-if="activeTab === 'general'">
                <div class="grid grid-cols-4 gap-6 max-xl:grid-cols-2 max-sm:grid-cols-1">
                    <div>
                        <CustomSelect
                            label="Parent"
                            v-model="form.parent_id"
                            mode="single"
                            placeholder="Select"
                            :disabled="!canEdit"
                            :options="params.parents"
                            :searchable="true"
                            :canClear="false"
                            class="py-2 rounded-lg border-stroke bg-transparent"
                            :error="form.errors?.parent_id"
                        />
                    </div>
                    <div>
                        <CustomInput
                            :disabled="!canEdit"
                            v-model="form.global_slug"
                            name="global_slug"
                            label="Global slug"
                            type="text"
                            placeholder="Enter or will generate automatically"
                            :error="form.errors?.global_slug"
                        />
                    </div>
                    <div>
                        <CustomInput
                            :disabled="!canEdit"
                            v-model="form.sort_order"
                            name="sort_order"
                            label="Sort order"
                            type="number"
                            placeholder="Enter sort order"
                            :error="form.errors?.sort_order"
                        />
                    </div>
                    <div class="flex items-end pb-2">
                        <Switch
                            :disabled="!canEdit"
                            @change="(value) => form.status = value"
                            :value="form.status"
                            id="shop_category_status"
                            label="Active"
                        />
                    </div>
                </div>
            </div>

            <div v-if="activeTab === 'content'">
                <div class="grid grid-cols-6 gap-6 max-md:grid-cols-1">
                    <div class="md:col-span-3">
                        <CustomInput
                            :disabled="!canEdit"
                            v-model="form.name"
                            name="name"
                            label="Name *"
                            type="text"
                            placeholder="Enter name"
                            :error="form.errors?.name"
                        />
                    </div>
                    <div class="md:col-span-3">
                        <CustomInput
                            :disabled="!canEdit"
                            v-model="form.slug"
                            name="slug"
                            label="Slug"
                            type="text"
                            placeholder="Enter or will generate automatically"
                            :error="form.errors?.slug"
                        />
                    </div>
                </div>
            </div>

            <div v-if="activeTab === 'seo'">
                <div class="grid grid-cols-5 gap-6 max-md:grid-cols-1">
                    <div class="md:col-span-3">
                        <CustomInput
                            :disabled="!canEdit"
                            v-model="form.meta_title"
                            name="meta_title"
                            label="Meta title"
                            type="text"
                            placeholder="Enter meta title"
                            :error="form.errors?.meta_title"
                        />
                    </div>
                    <div class="md:col-span-2">
                        <CustomInput
                            :disabled="!canEdit"
                            v-model="form.meta_keywords"
                            name="meta_keywords"
                            label="Meta keywords"
                            type="text"
                            placeholder="Enter meta keywords"
                            :error="form.errors?.meta_keywords"
                        />
                    </div>
                    <div class="md:col-span-5">
                        <label class="mb-2.5 block font-medium text-black">Meta description</label>
                        <textarea
                            :disabled="!canEdit"
                            v-model="form.meta_description"
                            name="meta_description"
                            placeholder="Enter meta description"
                            rows="5"
                            class="w-full rounded-lg border border-stroke bg-transparent py-4 px-6 pr-6 text-black outline-none focus:border-primary focus-visible:shadow-none"
                            :class="{
                                'is-invalid': form.errors?.meta_description,
                                'disabled': !canEdit
                            }"
                        ></textarea>
                        <div v-if="form.errors?.meta_description" class="invalid-feedback">
                            <span v-if="Array.isArray(form.errors.meta_description)">{{ form.errors.meta_description[0] }}</span>
                            <span v-else>{{ form.errors.meta_description }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="text-gray mt-6.5">
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="emitAction === 'update' && canDelete">
                        <CustomButton
                            @click="store.commit('shopCategory/SET_DELETE_MODAL_VALUE', {
                                value: true,
                                id: form.id
                            });"
                            class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'trash']"/>
                            Delete
                        </CustomButton>
                    </template>

                    <template v-if="canEdit">
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
