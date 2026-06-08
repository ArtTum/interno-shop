<template>
    <transition name="slide-right-left">
        <div
            v-if="notification.visible"
            class="fixed top-30 right-4 min-w-[422px] max-w-[422px] rounded-lg py-4 pl-4 pr-4.5 shadow-2 bg-white transition-opacity"
        >
            <div class="flex items-center justify-between">
                <div class="flex flex-grow items-center gap-5">
                    <template v-if="notification.type === 'error'">
                        <div class="flex h-10 w-full max-w-10 items-center justify-center rounded-full bg-danger text-white">
                            <font-awesome-icon :icon="['fas', 'triangle-exclamation']" />
                        </div>
                    </template>
                    <template v-else>
                        <div class="flex h-10 w-full max-w-10 items-center justify-center rounded-full bg-meta-3">
                            <font-awesome-icon :icon="['fas', 'check']" class="text-white size-5"/>
                        </div>
                    </template>
                    <div>
                        <h4 class="mb-0.5 text-title-xsm font-medium text-black">
                            {{ notification.title }}
                        </h4>
                        <p class="text-sm font-medium">{{ notification.message }}</p>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import {computed} from 'vue';
import {useStore} from "vuex";

const store = useStore();
const notification = computed(() => store.getters['notification/getNotification']);

</script>

<style scoped>
.slide-right-left-enter-active,
.slide-right-left-leave-active {
    transition: all 0.5s ease;
}

.slide-right-left-enter-from,
.slide-right-left-leave-to {
    opacity: 0;
    transform: translateX(100%);
}

.transition-opacity {
    transition-property: opacity;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
    z-index: 9999999;
}
</style>
