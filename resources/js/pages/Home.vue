<script setup lang="ts">

import Header from '@/components/Header.vue';

const props = defineProps<{
    auth: any;
}>();

</script>

<template>

    <Head title="Home" />
    <div class="flex flex-col items-center bg-[#FDFDFC] p-4 text-[#1b1b18] dark:bg-[#0a0a0a] lg:justify-start">
        <Header :showNav="true" :authenticated="props.auth.user" />
    </div>
    <div class="flex justify-center">
        <h1 class="text-2xl font-bold my-6">Welcome to my homepage</h1>
    </div>
</template>
