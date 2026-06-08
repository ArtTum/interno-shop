<script setup>
import {computed, ref} from 'vue'
import {useStore} from "vuex";
import {useRouter} from "vue-router";

const store = useStore();
const router = useRouter();

const target = ref(null)
const dropdownOpen = ref(false)

const logout = async () => {
    await store.dispatch('auth/logout');
    await router.push('/login');
};

const auth = computed(() => store.getters['auth/getUser']);
const vendorKey = localStorage.getItem('vendor_key')

const switchToVendor = async (vendor) => {
    await store.dispatch('auth/switchUser', {
        vendor
    });
    window.location.reload()
}

defineProps({
    vendorsForSwitch: {
        type: Array,
        required: false
    },
});

</script>

<template>
    <div class="relative" ref="target">
        <a
            class="flex items-center gap-4"
            href="javascript:void(0)"
            @click.prevent="dropdownOpen = !dropdownOpen"
        >
      <span class="hidden text-right lg:block">
        <span class="block text-sm font-medium text-black">{{ auth.name }} {{ auth.last_name }}</span>
        <span class="block text-xs font-medium text-body">{{ auth.user_group.name }}</span>
      </span>
            <font-awesome-icon :icon="['fass', 'angle-down']"
                               :class="dropdownOpen && 'rotate-180'"
                               class="fill-current sm:block"
            />
        </a>

        <!-- Dropdown Start -->
        <div
            v-show="dropdownOpen"
            class="absolute right-0 mt-4 flex w-62.5 flex-col rounded-sm border border-stroke bg-white shadow-default"
        >
            <ul class="flex flex-col gap-5 border-b border-stroke px-6 py-7.5">
                <li>
                    <router-link
                        :to="`/users/list/update/${auth.id}`"
                        class="flex items-center gap-3.5 text-sm font-medium duration-300 ease-in-out hover:text-primary lg:text-base"
                    >
                        <font-awesome-icon :icon="['far', 'gear']"
                                           class="fill-current"
                        />
                        Account Settings
                    </router-link>
                </li>
            </ul>
            <ul class="flex flex-col gap-5 border-b border-stroke px-6 py-4" v-if="vendorsForSwitch?.length">
                <li v-for="(vendor, vendInd) in vendorsForSwitch" :key="vendInd">
                    <router-link
                        @click="switchToVendor(vendor)"
                        :to="''"
                        class="flex items-center gap-2 text-sm font-medium duration-300 ease-in-out hover:text-primary lg:text-base"
                    >
                        <font-awesome-icon :icon="['far', 'person-running-fast']"
                                           class="fill-current"
                        />
                        {{ vendor }}
                    </router-link>
                </li>
            </ul>
            <button
                @click="logout"
                class="flex items-center gap-3.5 py-4 px-6 text-sm font-medium duration-300 ease-in-out hover:text-primary lg:text-base"
            >
                <font-awesome-icon :icon="['fal', 'arrow-left-from-bracket']"
                                   class="fill-current"
                />
                Log Out
            </button>
        </div>
        <!-- Dropdown End -->
    </div>
</template>
