<script setup>
import { ref } from 'vue'

const currentPage = ref(1)
const totalPages = ref(100)

const pages = ref([])

// Initialize pages array
for (let i = 1; i <= totalPages.value; i++) {
    pages.value.push(i)
}

const navigatePage = (direction) => {
    currentPage.value += direction
    updatePages()
}

const goToPage = (page) => {
    currentPage.value = page
    updatePages()
}

const updatePages = () => {
    if (currentPage.value < 1) {
        currentPage.value = 1
    } else if (currentPage.value > totalPages.value) {
        currentPage.value = totalPages.value
    }
}
</script>

<template>
    <nav>
        <ul class="flex flex-wrap items-center">
            <li>
                <router-link
                    class="flex h-9 w-9 items-center justify-center rounded-l-md border border-stroke hover:border-primary hover:bg-gray hover:text-primary"
                    to="#"
                    @click="navigatePage(-1)"
                >
                    <font-awesome-icon :icon="['fal', 'angle-left']" />
                </router-link>
            </li>
            <li v-for="page in pages" :key="page">
                <router-link
                    class="flex items-center justify-center border border-stroke border-l-transparent py-[5px] px-4 font-medium hover:border-primary hover:bg-gray hover:text-primary"
                    to="#"
                    @click="goToPage(page)"
                >
                    {{ page }}
                </router-link>
            </li>
            <li>
                <router-link
                    class="flex h-9 w-9 items-center justify-center rounded-r-md border border-stroke border-l-transparent hover:border-primary hover:bg-gray hover:text-primary"
                    to="#"
                    @click="navigatePage(1)"
                >
                    <font-awesome-icon :icon="['fal', 'angle-right']" />
                </router-link>
            </li>
        </ul>
    </nav>
</template>
