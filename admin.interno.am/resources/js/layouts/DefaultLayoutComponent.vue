<script setup>
import HeaderComponent from "@layouts/HeaderComponent.vue";
import LeftMenuComponent from "@layouts/LeftMenuComponent.vue";
import Notification from "@components/global/Notifications/Notification.vue";
import NotificationThree from "@components/global/Notifications/NotificationThree.vue";

import {useStore} from "vuex";
import {computed} from "vue";

const store = useStore()
const pushNotifications = computed(() => store.getters['notification/getPushNotifications']);

</script>

<template>
    <div class="flex h-screen overflow-hidden">
        <LeftMenuComponent />
        <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
            <HeaderComponent />
            <main class="mb-15">
                <div class="mx-1 md:mx-4 py-6 2xl:py-10 px-0">
                    <Notification/>
                    <div class="fixed right-4 top-50">
                        <template
                            v-for="(notification, key) in pushNotifications"
                            :key="key"
                        >
                            <template v-if="notification.show">
                                <NotificationThree
                                    :params="notification"
                                    :index="key"
                                />
                            </template>
                        </template>
                    </div>
                    <slot></slot>
                </div>
            </main>
        </div>
    </div>
</template>

<style lang="scss">
    @import '@assets/scss/global';
</style>
