<script setup>
import {useStore} from "vuex";

const store = useStore()

const props = defineProps({
    params: {
        type: Object,
    },
    index: {
        type: Number
    }
});
</script>

<template>
    <transition name="slide-right-left">
        <div class="py-2 bg">
            <div
                class="min-w-[422px] max-w-[422px] rounded-lg py-4 pl-4 pr-4.5 shadow-2 bg-white transition-opacity"
            >
                <div class="flex justify-between">
                    <div class="flex flex-grow gap-6">
                        <div>
                            <font-awesome-icon class="size-10 text-primary" :icon="['far', 'circle-info']" />
                        </div>
                        <div>
                            <h4 class="text-title-xsm font-medium text-black">
                                {{ params.text }}
                            </h4>
                            <h4 class="text-title-xsm font-bold">
                                <span class="text-sm font-medium"> {{ params.datetime }} </span>
                            </h4>
                            <a
                                target="_blank"
                                :href="params.link"
                            >
                                <button class="mt-2 font-medium text-meta-3">Open</button>
                            </a>
                            <button
                                @click="store.commit('notification/SET_PUSH_NOTIFICATION_SHOW_VALUE', {
                                index,
                                value: false,
                            })"
                                class="mt-2 font-medium  text-primary ml-5"
                            >
                                Later
                            </button>
                        </div>
                    </div>

                    <div>
                        <button
                            @click="store.commit('notification/SET_PUSH_NOTIFICATION_SHOW_VALUE', {
                                index,
                                value: false,
                            })"
                            class="hover:text-black"
                        >
                            <font-awesome-icon class="size-5" :icon="['fasr', 'xmark']" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
