<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Plus } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Posts', href: '/posts' }];
const props = defineProps<{ posts: any }>();
const deleteDialogOpen = ref(false);
const selectedPost = ref(null);

const truncateContent = (content: string, maxLength: number = 120) =>
    content?.length > maxLength ? content.substring(0, maxLength) + '...' : content;

const getStatusBadge = (isPublished: number, status: number) => {
    if (isPublished && status) return { label: 'Published', variant: 'success' };
    if (isPublished) return { label: 'Pending', variant: 'warning' };
    return { label: 'Draft', variant: 'secondary' };
};

const confirmDelete = (post) => {
    selectedPost.value = post;
    deleteDialogOpen.value = true;
};

const deletePost = () => {
    if (selectedPost.value) {
        router.delete(`/posts/${selectedPost.value.id}`, {
            onSuccess: () => {
                deleteDialogOpen.value = false;
                selectedPost.value = null;
            }
        });
    }
};

const handleCreateClick = () => {
    router.visit('/posts/create');
};
</script>

<template>

    <Head title="Posts" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Posts</h1>
                <div class="flex items-center justify-end py-4 space-x-2">
                    <Button size="sm" @click="handleCreateClick">
                        <Plus class="w-5 h-5" />
                        Create Post
                    </Button>
                </div>
            </div>

            <div v-if="posts.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <Card v-for="post in posts.data" :key="post.id" class="overflow-hidden">
                    <div class="h-48 overflow-hidden">
                        <img :src="post.feature_image" :alt="post.title"
                            class="w-full h-full object-cover hover:scale-105 transition duration-300">
                    </div>

                    <CardHeader class="pb-2">
                        <div class="flex justify-between items-center mb-1">
                            <Badge :variant="getStatusBadge(post.is_published, post.status).variant">
                                {{ getStatusBadge(post.is_published, post.status).label }}
                            </Badge>
                            <span class="text-xs text-muted-foreground">ID: {{ post.id }}</span>
                        </div>
                        <h3 class="text-lg font-semibold line-clamp-2">{{ post.title }}</h3>
                    </CardHeader>

                    <CardContent>
                        <p class="text-sm text-muted-foreground line-clamp-3">{{ truncateContent(post.content) }}</p>
                    </CardContent>

                    <CardFooter class="pt-2 border-t flex justify-between">
                        <Button variant="ghost" size="sm" @click="router.visit(`/posts/${post.id}`)">View</Button>
                        <div class="flex gap-2">
                            <Button variant="outline" size="sm"
                                @click="router.visit(`/posts/${post.id}/edit`)">Edit</Button>
                            <Button variant="destructive" size="sm" @click="confirmDelete(post)">Delete</Button>
                        </div>
                    </CardFooter>
                </Card>
            </div>

            <div v-else
                class="flex flex-col items-center justify-center py-16 bg-muted/20 rounded-lg border border-dashed">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-4 text-muted" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>
                <p class="text-lg font-medium mb-1">No posts available</p>
                <Button @click="handleCreateClick" class="mt-4">Create Post</Button>
            </div>
        </div>
    </AppLayout>

    <Dialog :open="deleteDialogOpen" @update:open="deleteDialogOpen = $event">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete Post</DialogTitle>
                <DialogDescription>Are you sure you want to delete "{{ selectedPost?.title }}"?</DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="deleteDialogOpen = false">Cancel</Button>
                <Button variant="destructive" @click="deletePost">Delete</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>