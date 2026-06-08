<script setup>
import {useStore} from "vuex";
import DropdownUser from '@components/header/DropdownUser.vue'
import {computed, ref, watch} from 'vue';
import CustomSelect from "@components/global/CustomSelect.vue";
import CustomButton from "@components/global/CustomButton.vue";

const store = useStore();
const isSidebarOpen = computed(() => store.getters['sideBar/isSidebarOpen']);
const generalParams = computed(() => store.getters['general/getParams']);

const vendorKey = localStorage.getItem('vendor_key');
const workingLanguageId = ref(localStorage.getItem(`${vendorKey}:workingLanguageId`) || null);

watch(generalParams, (newVal) => {
    if (!workingLanguageId.value) {
        workingLanguageId.value = newVal.base_language_id;
        localStorage.setItem(`${vendorKey}:workingLanguageId`, workingLanguageId.value);
    }
}, {immediate: true});

const setLanguageIdInLocaleStorage = () => {
    localStorage.setItem(`${vendorKey}:workingLanguageId`, workingLanguageId.value);
    window.location.reload()
}
const toggleSidebar = () => {
    store.dispatch("sideBar/toggleSidebarMenu");
};

const getVendorsForSwitch = computed(() => store.getters['general/getVendorsForSwitch']);
const subscribesCount = computed(() => store.getters['subscribe/getCount']);

const fetchVendors = async () => {
    if (Array.isArray(getVendorsForSwitch.value)) return false;

    await store.dispatch('general/fetchVendorsForSwitch', {
        dontNeedLoading: true
    });
}

fetchVendors();
store.dispatch('subscribe/fetchCount');

</script>

<template>
    <header
        class="sticky top-0 z-999 flex w-full bg-white drop-shadow-1"
    >
        <div class="flex flex-grow items-center justify-between py-4 px-4 shadow-2 md:px-6 2xl:px-11 max-sm:py-2">
            <div class="flex items-center gap-4 lg:hidden">
                <!-- Hamburger Toggle BTN -->
                <button
                    class="z-99999 block rounded-sm border border-stroke bg-white p-1.5 shadow-sm lg:hidden"
                    @click="store.dispatch('sideBar/toggleSidebar');"
                >
                  <span class="relative block h-5.5 w-5.5 cursor-pointer">
                    <span class="du-block absolute right-0 h-full w-full">
                      <span
                          class="relative top-0 left-0 my-1 block h-0.5 w-0 rounded-sm bg-black delay-[0] duration-200 ease-in-out"
                          :class="{ '!w-full delay-300': !isSidebarOpen }"
                      ></span>
                      <span
                          class="relative top-0 left-0 my-1 block h-0.5 w-0 rounded-sm bg-black delay-150 duration-200 ease-in-out"
                          :class="{ '!w-full delay-400': !isSidebarOpen }"
                      ></span>
                      <span
                          class="relative top-0 left-0 my-1 block h-0.5 w-0 rounded-sm bg-black delay-200 duration-200 ease-in-out"
                          :class="{ '!w-full delay-500': !isSidebarOpen }"
                      ></span>
                    </span>
                    <span class="du-block absolute right-0 h-full w-full rotate-45">
                      <span
                          class="absolute left-2.5 top-0 block h-full w-0.5 rounded-sm bg-black delay-300 duration-200 ease-in-out"
                          :class="{ '!h-0 delay-[0]': !isSidebarOpen }"
                      ></span>
                      <span
                          class="delay-400 absolute left-0 top-2.5 block h-0.5 w-full rounded-sm bg-black duration-200 ease-in-out"
                          :class="{ '!h-0 dealy-200': !isSidebarOpen }"
                      ></span>
                    </span>
                  </span>
                </button>
            </div>
            <div class="flex items-center gap-4 hidden lg:block " >
                <!-- Hamburger Toggle BTN -->
                <button
                    class="z-99999 rounded-sm border border-stroke bg-white p-1.5 hidden lg:block"
                    @click="toggleSidebar"
                >
                  <span class="relative block h-5.5 w-5.5 cursor-pointer">
                    <span class="du-block absolute right-0 h-full w-full">
                      <span
                          class="relative top-0 left-0 my-1 block h-0.5 w-0 rounded-sm bg-black delay-[0] duration-200 ease-in-out"
                          :class="{ '!w-full delay-300': true }"
                      ></span>
                      <span
                          class="relative top-0 left-0 my-1 block h-0.5 w-0 rounded-sm bg-black delay-150 duration-200 ease-in-out"
                          :class="{ '!w-full delay-400': true }"
                      ></span>
                      <span
                          class="relative top-0 left-0 my-1 block h-0.5 w-0 rounded-sm bg-black delay-200 duration-200 ease-in-out"
                          :class="{ '!w-full delay-500': true }"
                      ></span>
                    </span>
                    <span class="du-block absolute right-0 h-full w-full rotate-45">
                      <span
                          class="absolute left-2.5 top-0 block h-full w-0.5 rounded-sm bg-black delay-300 duration-200 ease-in-out"
                          :class="{ '!h-0 delay-[0]': true }"
                      ></span>
                      <span
                          class="delay-400 absolute left-0 top-2.5 block h-0.5 w-full rounded-sm bg-black duration-200 ease-in-out"
                          :class="{ '!h-0 dealy-200': true }"
                      ></span>
                    </span>
                  </span>
                </button>
            </div>

            <div class="flex items-center gap-3 2xsm:gap-7">
                <ul class="flex items-center gap-2 2xsm:gap-4">
                        <router-link
                            to="/subscribes"
                            class="flex items-center gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80">
                            <font-awesome-icon :icon="['fab', 'telegram']"/>
                            Հերթագրվածներ ({{ subscribesCount }})
                        </router-link>

                </ul>
                <DropdownUser
                    :vendors-for-switch="getVendorsForSwitch"
                />
            </div>
        </div>
    </header>
</template>
