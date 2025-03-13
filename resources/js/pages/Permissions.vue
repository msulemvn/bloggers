<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Permissions',
        href: '/permissions',
    },
];

const props = defineProps<{
    permissions: string[];
}>();

const groupedPermissions = props.permissions.reduce((acc: Record<string, string[]>, permission) => {
    const [action, entity] = permission.split(':');
    if (!acc[entity]) acc[entity] = [];
    acc[entity].push(action);
    return acc;
}, {});
</script>

<template>

    <Head title="Permissions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div v-for="(actions, entity) in groupedPermissions" :key="entity" class="rounded-lg border p-3 shadow-md">
                <h2 class="text-lg font-semibold capitalize mb-2">{{ entity }}</h2>
                <ul class="list-disc list-inside">
                    <li v-for="action in actions" :key="action" class="text-sm text-gray-700">
                        {{ action }}
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>
