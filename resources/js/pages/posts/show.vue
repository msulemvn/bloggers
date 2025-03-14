<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';

const props = defineProps<{ post: any }>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Posts', href: '/posts' },
    { title: props.post.slug, href: '' },
];
</script>

<template>

    <Head :title="props.post.slug" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <h1 class="text-3xl font-bold mb-4">{{ props.post.title }}</h1>

            <Card class="w-full overflow-hidden">
                <CardHeader class="my-1">
                    <img v-if="props.post.feature_image" :src="props.post.feature_image" alt="Feature Image"
                        class="w-full h-96 object-cover" />
                </CardHeader>
                <CardContent>
                    <p class="text-muted-foreground leading-relaxed">{{ props.post.content }}</p>
                </CardContent>
                <CardFooter>
                    <div v-if="props.post.tags && props.post.tags.length" class="flex flex-wrap gap-2">
                        <span v-for="tag in props.post.tags" :key="tag.id"
                            class="px-3 py-1 bg-muted text-foreground text-sm rounded">
                            {{ tag.title }}
                        </span>
                    </div>
                </CardFooter>
            </Card>

            <div class="mt-6">
                <h2 class="text-2xl font-semibold mb-4">Comments</h2>
                <div class="space-y-4">
                    <Card v-for="comment in props.post.comments" :key="comment.id">
                        <CardContent class="flex gap-4">
                            <Avatar>
                                <AvatarImage :src="comment.user.avatar || ''" />
                                <AvatarFallback>{{ comment.user.name.charAt(0) }}</AvatarFallback>
                            </Avatar>
                            <div>
                                <p class="font-medium">{{ comment.user.name }}</p>
                                <p class="text-muted-foreground">{{ comment.content }}</p>
                            </div>
                        </CardContent>
                        <CardFooter class="pl-14">
                            <Button variant="ghost" size="sm">Reply</Button>
                        </CardFooter>
                    </Card>
                    <div class="mt-4">
                        <Textarea class="w-full" rows="4" placeholder="Write a comment..." />
                        <Button class="mt-4">Post Comment</Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>