<script setup>
import {computed, watch} from "vue";
import {useStore} from "vuex";

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

const auth = computed(() => store.getters['auth/getUser']);
const permission = computed(() => auth.value?.user_group?.permissions_by_name?.shop_categories?.[0] || {});
const canEdit = computed(() => auth.value?.superadmin || props.emitAction !== 'update' || permission.value.can_edit);
const canDelete = computed(() => auth.value?.superadmin || permission.value.can_delete);

watch(() => form.value.language_id, (languageId) => {
    emits('language-change', languageId);
});
</script>

<template>
    <form @submit.prevent="emits('submit')" class="p-6">
        <div v-if="form.errors?.general" class="mb-4 rounded border border-meta-1 bg-red-50 p-3 text-meta-1">
            <div v-for="(messages, key) in form.errors.general" :key="key">
                <p v-for="message in messages" :key="message">{{ message }}</p>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 max-xl:grid-cols-2 max-sm:grid-cols-1">
            <label class="block">
                <span class="mb-2 block font-medium text-black">Language</span>
                <select v-model="form.language_id" class="w-full rounded border border-stroke px-3 py-2 text-black" :disabled="!canEdit">
                    <option v-for="language in params.languages" :key="language.value" :value="language.value">
                        {{ language.label }}
                    </option>
                </select>
            </label>

            <label class="block">
                <span class="mb-2 block font-medium text-black">Parent</span>
                <select v-model="form.parent_id" class="w-full rounded border border-stroke px-3 py-2 text-black" :disabled="!canEdit">
                    <option v-for="parent in params.parents" :key="parent.value ?? 'none'" :value="parent.value">
                        {{ parent.label }}
                    </option>
                </select>
            </label>

            <label class="block">
                <span class="mb-2 block font-medium text-black">Sort order</span>
                <input v-model.number="form.sort_order" type="number" min="0" class="w-full rounded border border-stroke px-3 py-2 text-black" :disabled="!canEdit" />
            </label>

            <label class="block">
                <span class="mb-2 block font-medium text-black">Name</span>
                <input v-model="form.name" class="w-full rounded border border-stroke px-3 py-2 text-black" :disabled="!canEdit" />
            </label>

            <label class="block">
                <span class="mb-2 block font-medium text-black">Slug</span>
                <input v-model="form.slug" class="w-full rounded border border-stroke px-3 py-2 text-black" :disabled="!canEdit" />
            </label>

            <label class="block">
                <span class="mb-2 block font-medium text-black">Global slug</span>
                <input v-model="form.global_slug" class="w-full rounded border border-stroke px-3 py-2 text-black" :disabled="!canEdit" />
            </label>

            <label class="block">
                <span class="mb-2 block font-medium text-black">Meta title</span>
                <input v-model="form.meta_title" class="w-full rounded border border-stroke px-3 py-2 text-black" :disabled="!canEdit" />
            </label>

            <label class="col-span-2 block max-xl:col-span-1">
                <span class="mb-2 block font-medium text-black">Meta keywords</span>
                <input v-model="form.meta_keywords" class="w-full rounded border border-stroke px-3 py-2 text-black" :disabled="!canEdit" />
            </label>

            <label class="col-span-3 block max-xl:col-span-2 max-sm:col-span-1">
                <span class="mb-2 block font-medium text-black">Meta description</span>
                <textarea v-model="form.meta_description" rows="3" class="w-full rounded border border-stroke px-3 py-2 text-black" :disabled="!canEdit"></textarea>
            </label>

            <label class="flex items-center gap-2 rounded border border-stroke px-3 py-2 text-black">
                <input v-model="form.status" type="checkbox" :disabled="!canEdit" />
                Active
            </label>
        </div>

        <div class="save-button-fixed mt-6 flex justify-end gap-3">
            <button
                v-if="emitAction === 'update' && canDelete"
                type="button"
                class="rounded bg-meta-1 px-5 py-2 font-medium text-white"
                @click="store.commit('shopCategory/SET_DELETE_MODAL_VALUE', {value: true, id: form.id})"
            >
                Delete
            </button>
            <button v-if="canEdit" type="submit" class="rounded bg-primary px-5 py-2 font-medium text-white">
                Save
            </button>
        </div>
    </form>
</template>
