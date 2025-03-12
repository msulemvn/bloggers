<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Payment } from '@/types';
import { columns } from '@/components/columns';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import DataTable from '@/components/DataTable.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
];

const data = ref<Payment[]>([])

async function getData(): Promise<Payment[]> {
    // Fetch data from your API here.
    return [
        {
            id: '728ed52f',
            amount: 100,
            status: 'pending',
            email: 'm@example.com',
        },
        // ...
    ]
}

onMounted(async () => {
    data.value = await getData()
})
</script>

<template>

    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container py-10 mx-auto">
            <DataTable :columns="columns" :data="data" />
        </div>
    </AppLayout>
</template>