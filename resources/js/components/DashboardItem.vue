<script setup lang="ts">
import {
    Card,
    CardContent,
    CardDescription,
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
            <CardTitle v-if="item.title">
                <div class="flex justify-start gap-2">
                    <span>{{ item.title }}</span>
                    <component v-if="item.icon" :is="item.icon" class="text-primary" />
                </div>
            </CardTitle>
            <CardDescription v-if="item.description" class="text-lg">
                {{ item.description }}
            </CardDescription>
        </CardHeader>
        <CardContent class="text-4xl">
            {{ item.count }}
        </CardContent>
    </Card>
    </Link>
</template>