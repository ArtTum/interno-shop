<template>
    <div
        class="rounded-sm vh-100 border border-stroke bg-white px-5 py-20 shadow-default"
    >
        <div class="mx-auto max-w-[410px]">
            <img src="@/assets/images/illustration/illustration-01.svg" alt="illustration"/>

            <div class="mt-7.5 text-center">
                <h2 class="mb-3 text-2xl font-bold text-black">
                    Sorry, the page can’t be found
                </h2>
                <p class="font-medium">
                    The page you were looking for appears to have been moved, deleted or does not exist.
                </p>
                <router-link
                    to="/"
                    class="mt-7.5 inline-flex items-center gap-2 rounded-md bg-primary py-3 px-6 font-medium text-white hover:bg-opacity-90"
                >
                    <font-awesome-icon :icon="['far', 'arrow-left']"
                                       class="fill-current"/>
                    <RouterLink to="/">
                        <span>Back to Home</span>
                    </RouterLink>
                </router-link>
            </div>
        </div>
    </div>
</template>
<script setup>
</script>
<style>
.vh-100 {
    height: 100vh;
}
</style>
