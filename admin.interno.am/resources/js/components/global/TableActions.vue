<script setup>

import CustomButton from "@components/global/CustomButton.vue";
import {computed, onMounted, onUnmounted, ref} from 'vue'
import {useStore} from "vuex";

const filterMenu = ref(true);
const store = useStore();

const props = defineProps({
    createRoute: {
        type: String,
        default: null,
        required: false
    },
    showFilter: {
        type: Boolean,
        default: true
    },
    buttonName: {
        type: String,
    },
    taxActions: {
        default: false
    },
    saveExists: {
        type: Boolean,
        required: false
    },
    filterMenuInitialValue: {
        type: Boolean,
        default: false
    }
})

filterMenu.value = props.filterMenuInitialValue;

const emits = defineEmits([
    'add-new-row',
    'save-changes',
    'emit-changes',
    'apply-filters',
]);

const auth = computed(() => store.getters['auth/getUser']);

const handleEnterKey = (event) => {
    if (event.key === "Enter") emits('apply-filters', false);
};

onMounted(() => {
    window.addEventListener("keydown", handleEnterKey);
});

onUnmounted(() => {
    window.removeEventListener("keydown", handleEnterKey);
});

</script>

<template>
    <div>
        <div
            class="flex flex-col gap-y-4 mb-4 rounded-sm border border-stroke bg-white p-3 shadow-default sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex -space-x-2" v-if="props.showFilter">
                    <button type="button" @click="filterMenu = !filterMenu"
                            class="flex h-9 w-9 items-center justify-center rounded-full border border-stroke bg-white text-primary">
                        <font-awesome-icon :icon="['far', 'bars-filter']"/>
                    </button>
                </div>
            </div>
            <div class="flex flex-col gap-4 2xsm:flex-row 2xsm:items-center">

                <div class="flex flex-wrap gap-4">
                    <CustomButton
                        @click="emits('apply-filters')"
                        class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                        type="button"
                    >
                        <font-awesome-icon :icon="['far', 'magnifying-glass']"/>
                        Կիրառել որոնումը և ֆիլտրերը
                    </CustomButton>
                    <template v-if="saveExists">
                        <CustomButton
                            @click="emits('save-changes')"
                            class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                            Save
                        </CustomButton>
                    </template>
                    <slot name="actions"/>
                    <template v-if="props.createRoute">
                        <RouterLink :to="props.createRoute">
                            <CustomButton
                                class="flex items-center gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80">
                                <font-awesome-icon :icon="'plus'"/>
                                Ավելացնել
                            </CustomButton>
                        </RouterLink>
                    </template>
                    <template v-else-if="buttonName">
                        <CustomButton
                            type="button"
                            @click="emits('emit-changes')"
                            class="flex items-center gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80">
                            <font-awesome-icon :icon="'plus'"/>
                            {{ buttonName }}
                        </CustomButton>
                    </template>
                    <template v-else-if="taxActions">
                        <template
                            v-if="auth.user_group.permissions_by_name.tax[0].can_add || auth.user_group.permissions_by_name.tax[0].can_edit">
                            <CustomButton
                                type="button"
                                @click="emits('save-changes')"
                                class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 "
                            >
                                <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                                Հաստատել
                            </CustomButton>
                        </template>
                        <template v-if="auth.user_group.permissions_by_name.tax[0].can_add">
                            <CustomButton
                                type="button"
                                @click="emits('add-new-row')"
                                class="flex items-center gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 "
                            >
                                <font-awesome-icon :icon="'plus'"/>
                                Add row
                            </CustomButton>
                        </template>
                    </template>
                </div>
            </div>
        </div>
        <div v-show="filterMenu"
             class="flex flex-col gap-y-4 mb-4 rounded-sm border border-stroke bg-white p-3 shadow-default">
            <div>
                <slot></slot>
            </div>
        </div>
    </div>
</template>
