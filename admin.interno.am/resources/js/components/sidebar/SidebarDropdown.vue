<script setup>
import {computed, ref} from 'vue'
import {useStore} from "vuex";

const store = useStore();
const selected = computed(() => store.getters['sideBar/selected']);

const auth = computed(() => store.getters['auth/getUser']);

const props = defineProps(['items', 'page'])
const items = ref(props.items)

</script>

<template>
    <ul class="mt-4 mb-5.5 flex flex-col gap-2.5 pl-6">
        <template v-for="(childItem, index) in items" :key="index">
            <template v-if="(auth && auth.superadmin) || (auth.user_group.permissions_by_name[childItem.name] !== undefined && auth.user_group.permissions_by_name[childItem.name][0].can_view)">
                <li>
                    <router-link
                        :to="childItem.route"
                        class="group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white"
                        :class="{
                            '!text-white': childItem.label === selected
                        }"
                    >
                        {{ childItem.label }}
                    </router-link>
                </li>
            </template>
        </template>
    </ul>
</template>
<style scoped>
.router-link-exact-active.active {
    color: white;
}
</style>
