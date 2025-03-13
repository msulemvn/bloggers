<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type CardItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import DashboardItem from '@/components/DashboardItem.vue';
import { Users, FileText, Tag } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const cardItems: CardItem[] = [
    {
        title: "Users",
        description: "Active users in the system",
        icon: Users,
        count: 15,
        route: "/users"
    },
    {
        title: "Posts",
        description: "Published content items",
        icon: FileText,
        count: 7,
        route: "/posts"
    },
    {
        title: "Tags",
        description: "Content categorization tags",
        icon: Tag,
        count: 25,
        route: "/tags"
    }
];

</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <DashboardItem :card-items="cardItems" />
            </div>
        </div>
    </AppLayout>
</template>
