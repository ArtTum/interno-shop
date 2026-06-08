<script setup>
import Switch from "@components/global/Switch.vue";

const emits = defineEmits(['edit-section-columns', 'edit-section', 'delete-section', 'clone-section', 'update-status']);
import {useStore} from "vuex";
import {computed} from "vue";
const store = useStore();

const auth = computed(() => store.getters['auth/getUser']);

const props = defineProps({
    type: {
        type: Number
    },
    status: {
        type: Boolean
    },
    disabled: {
        type: Boolean,
        default: false
    },
    statusId: {
        type: String
    }
});
</script>

<template>
    <div class="flex justify-between">
        <div
            class="flex items-center"
        >
            <router-link
                :class="{'bg-primary text-white': type === 1, 'hover:bg-primary hover:text-white hover-border-white-trigger': !disabled, 'opacity-60': disabled}"
                @click="() => {
                    if (!disabled) emits('edit-section-columns', 1);
                }"
                title="full"
                :to="''"
                class="w-[60px] px-1 bg-gray border border-stroke border-b-0 border-l-0 text-white inline-flex items-center py-1 font-medium sm:py-3"
            >
                <div
                    class="w-full border-[1px] border-graydark"
                    :class="{'border-[1px] border-white': type === 1}"
                ></div>
            </router-link>
            <router-link
                :class="{'bg-primary text-white': type === 2, 'hover:bg-primary hover:text-white hover-border-white-trigger': !disabled, 'opacity-60': disabled}"
                @click="() => {
                    if (!disabled) emits('edit-section-columns', 2);
                }"
                title="1/2 - 1/2"
                :to="''"
                class="w-[60px] flex justify-between ml-1 px-1 bg-gray border border-stroke border-b-0 text-white items-center py-1 font-medium sm:py-3"
            >
                <div
                    class="w-[48%] border-[1px] border-graydark"
                    :class="{'border-[1px] border-white': type === 2}"
                >
                </div>
                <div
                    class="w-[48%] border-[1px] border-graydark"
                    :class="{'border-[1px] border-white': type === 2}"
                >
                </div>
            </router-link>
            <router-link
                :class="{'bg-primary text-white': type === 3, 'hover:bg-primary hover:text-white hover-border-white-trigger': !disabled, 'opacity-60': disabled}"
                @click="() => {
                    if (!disabled) emits('edit-section-columns', 3);
                }"
                title="2/3 - 1/3"
                :to="''"
                class="w-[60px] flex justify-between ml-1 px-1 bg-gray border border-stroke border-b-0 text-white items-center py-1 font-medium sm:py-3"
            >
                <div
                    class="w-[65%] border-[1px] border-graydark"
                    :class="{'border-[1px] border-white': type === 3}">
                </div>
                <div
                    class="w-[33%] border-[1px] border-graydark"
                    :class="{'border-[1px] border-white': type === 3}">
                </div>
            </router-link>
            <router-link
                :class="{'bg-primary text-white': type === 4, 'hover:bg-primary hover:text-white hover-border-white-trigger': !disabled, 'opacity-60': disabled}"
                @click="() => {
                    if (!disabled) emits('edit-section-columns', 4);
                }"
                title="1/3 - 1/3 - 1/3"
                :to="''"
                class="w-[60px] flex justify-between ml-1 px-1 bg-gray border border-stroke border-b-0 text-white items-center py-1 font-medium sm:py-3"
            >
                <div class="w-[32%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 4}"
                >
                </div>
                <div class="w-[32%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 4}"
                >
                </div>
                <div class="w-[33%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 4}"
                >
                </div>
            </router-link>
            <router-link
                :class="{'bg-primary text-white': type === 5, 'hover:bg-primary hover:text-white hover-border-white-trigger': !disabled, 'opacity-60': disabled}"
                @click="() => {
                    if (!disabled) emits('edit-section-columns', 5);
                }"
                title="1/4 - 1/4 - 1/4 - 1/4"
                :to="''"
                class="w-[60px] flex justify-between ml-1 px-1 bg-gray border border-stroke border-b-0 text-white items-center py-1 font-medium  sm:py-3"
            >
                <div class="w-[22.5%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 5}"></div>
                <div class="w-[22.5%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 5}"></div>
                <div class="w-[22.5%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 5}"></div>
                <div class="w-[22.5%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 5}"></div>
            </router-link>
            <router-link
                :class="{'bg-primary text-white': type === 6, 'hover:bg-primary hover:text-white hover-border-white-trigger': !disabled, 'opacity-60': disabled}"
                @click="() => {
                    if (!disabled) emits('edit-section-columns', 6);
                }"
                title="1/3 - 2/3"
                :to="''"
                class="w-[60px] flex justify-between ml-1 px-1 bg-gray border border-stroke border-b-0 text-white items-center py-1 font-medium sm:py-3"
            >
                <div class="w-[33%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 6}"></div>
                <div class="w-[65%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 6}"></div>
            </router-link>
            <router-link
                :class="{'bg-primary text-white': type === 7, 'hover:bg-primary hover:text-white hover-border-white-trigger': !disabled, 'opacity-60': disabled}"
                @click="() => {
                    if (!disabled) emits('edit-section-columns', 7);
                }"
                title="1/5 - 1/5 - 1/5 - 1/5 - 1/5"
                :to="''"
                class="w-[60px] flex justify-between ml-1 px-1 bg-gray border border-stroke border-b-0 text-white items-center py-1 font-medium sm:py-3"
            >
                <div class="w-[18.5%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 7}"></div>
                <div class="w-[18.5%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 7}"></div>
                <div class="w-[18.5%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 7}"></div>
                <div class="w-[18.5%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 7}"></div>
                <div class="w-[18.5%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 7}"></div>
            </router-link>
            <router-link
                :class="{'bg-primary text-white': type === 8, 'hover:bg-primary hover:text-white hover-border-white-trigger': !disabled, 'opacity-60': disabled}"
                @click="() => {
                    if (!disabled) emits('edit-section-columns', 8);
                }"
                title="1/6 - 1/6 - 1/6 - 1/6 - 1/6 - 1/6"
                :to="''"
                class="cursor-pointer w-[60px] flex justify-between ml-1 px-1 bg-gray border border-stroke border-b-0 text-white items-center py-1 font-medium sm:py-3"
            >
                <div class="w-[14%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 8}"></div>
                <div class="w-[14%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 8}"></div>
                <div class="w-[14%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 8}"></div>
                <div class="w-[14%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 8}"></div>
                <div class="w-[14%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 8}"></div>
                <div class="w-[14%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 8}"></div>
            </router-link>
            <router-link
                :class="{'bg-primary text-white': type === 9, 'hover:bg-primary hover:text-white hover-border-white-trigger': !disabled, 'opacity-60': disabled}"
                @click="() => {
                    if (!disabled) emits('edit-section-columns', 9);
                }"
                title="3/4 - 1/4"
                :to="''"
                class="w-[60px] flex justify-between ml-1 px-1 bg-gray border border-stroke border-b-0 text-white items-center py-1 font-medium sm:py-3"
            >
                <div
                    class="w-[75%] border-[1px] border-graydark"
                    :class="{'border-[1px] border-white': type === 9}">
                </div>
                <div
                    class="w-[22%] border-[1px] border-graydark"
                    :class="{'border-[1px] border-white': type === 9}">
                </div>
            </router-link>
            <router-link
                :class="{'bg-primary text-white': type === 10, 'hover:bg-primary hover:text-white hover-border-white-trigger': !disabled, 'opacity-60': disabled}"
                @click="() => {
                    if (!disabled) emits('edit-section-columns', 10);
                }"
                title="1/4 - 3/4"
                :to="''"
                class="w-[60px] flex justify-between ml-1 px-1 bg-gray border border-stroke border-b-0 text-white items-center py-1 font-medium sm:py-3"
            >
                <div class="w-[22%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 10}"></div>
                <div class="w-[75%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 10}"></div>
            </router-link>
            <router-link
                :class="{'bg-primary text-white': type === 11, 'hover:bg-primary hover:text-white hover-border-white-trigger': !disabled, 'opacity-60': disabled}"
                @click="() => {
                    if (!disabled) emits('edit-section-columns', 11);
                }"
                title="1/4 - 2/4 - 1/4"
                :to="''"
                class="w-[60px] flex justify-between ml-1 px-1 bg-gray border border-stroke border-b-0 text-white items-center py-1 font-medium sm:py-3"
            >
                <div class="w-[22%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 11}"></div>
                <div class="w-[49%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 11}"></div>
                <div class="w-[22%] border-[1px] border-graydark"
                     :class="{'border-[1px] border-white': type === 11}"></div>
            </router-link>
        </div>
        <div
            class="flex items-center"
        >
            <Switch
                @change="(value) => {
                   emits('update-status', value)
                }"
                :disabled="disabled"
                class="-mt-3 mr-1"
                :value="status"
                :id="statusId"
                :color-danger="!status"
                :color-success="status"
                label=""
            />
            <router-link
                title="Drag"
                :to="''"
                class="mr-1 flex items-center cursor-pointer w-[27px] h-[27px] bg-gray border border-stroke border-b-0 text-white text-center font-medium"
            >
                <font-awesome-icon :icon="['far', 'arrows-from-dotted-line']" class="w-full text-black mr-auto"/>
            </router-link>
            <router-link
                @click="() => {
                   emits('edit-section');
                }"
                title="Edit"
                :to="''"
                class="mr-1 flex items-center cursor-pointer w-[27px] h-[27px] hover-border-white-trigger bg-gray border border-white border-b-0 text-white text-center font-medium hover:bg-primary hover:border-primary"
            >
                <font-awesome-icon :icon="['far', 'pen-to-square']" class="w-full text-black mr-auto"/>

            </router-link>
            <router-link
                @click="() => {
                   emits('clone-section');
                }"
                title="Clone"
                :to="''"
                class="mr-1 flex items-center cursor-pointer w-[27px] h-[27px] hover-border-white-trigger bg-gray border border-white border-b-0 text-white text-center font-medium hover:bg-primary hover:border-primary"
            >
                <font-awesome-icon :icon="['fasr', 'clone']" class="w-full text-black mr-auto"/>

            </router-link>
            <router-link
                @click="() => {
                   emits('additional-column');
                }"
                title="Additional column"
                :to="''"
                class="mr-1 flex items-center cursor-pointer w-[27px] h-[27px] hover-border-white-trigger bg-gray border border-white border-b-0 text-white text-center font-medium hover:bg-primary hover:border-primary"
            >
                <font-awesome-icon :icon="['far', 'plus']" class="w-full text-black mr-auto"/>

            </router-link>
            <router-link
                @click="() => {
                   emits('delete-section');
                }"
                title="Delete"
                :to="''"
                class="flex items-center cursor-pointer w-[27px] h-[27px] hover-border-white-trigger bg-gray border border-stroke border-r-0 border-b-0 text-white text-center font-medium hover:bg-danger hover:border-danger hover:text-white"
            >
                <font-awesome-icon :icon="['fas', 'xmark']" class="w-full text-black mr-auto"/>
            </router-link>
        </div>
    </div>
</template>

<style>
.hover-border-white-trigger:hover div {
    border: 1px solid white;
}
.hover-border-white-trigger:hover svg {
    color: white;
}
</style>
