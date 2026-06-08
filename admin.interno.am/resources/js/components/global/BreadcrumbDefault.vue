<script setup>
defineProps({
    pageTitle: {
        type: String
    },
    breadcrumb: {
        type: Array,
    },
});

</script>

<template>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-semibold text-black">
            {{ pageTitle }}
        </h2>

        <nav>
            <ol class="flex items-center gap-2 mr-4">
                <template v-for="(item, index) in breadcrumb" :key="index">
                    <router-link class="font-medium" :to="item.path">{{ item.title }}<template v-if="index !== breadcrumb.length"> /</template> </router-link>
                </template>
                <li class="font-medium text-primary">
                   {{ pageTitle }}
                </li>
            </ol>
        </nav>
    </div>
</template>
