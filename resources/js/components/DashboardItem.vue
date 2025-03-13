<script setup lang="ts">
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { type CardItem } from '@/types';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    cardItems: CardItem[];
}>()

</script>

<template>
    <Link v-for="(item, index) in cardItems" :key="index" :href="item.route" class="block">
    <Card
        class="cursor-pointer select-none shadow-sm shadow-black/10 transition-shadow hover:shadow-md hover:shadow-black/20">
        <CardHeader v-if="item.title || item.description">
            <CardTitle v-if="item.title">{{ item.title }}</CardTitle>
            <CardDescription v-if="item.description">{{ item.description }}</CardDescription>
        </CardHeader>
        <CardContent>
            <component v-if="item.icon" :is="item.icon" class="h-16 w-16 text-primary" />
        </CardContent>
        <CardFooter v-if="item.title && item.count" class="flex justify-between px-6 pb-6">
            Total {{ item.title }}: {{ item.count }}
        </CardFooter>
    </Card>
    </Link>
</template>