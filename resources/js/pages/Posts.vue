<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import DataTableDialog from '@/components/DataTableDialog.vue';
import axios from 'axios';
import { useToast } from '@/components/ui/toast/use-toast';
import { useForm } from 'vee-validate';
import { toTypedSchema } from '@vee-validate/zod';
import * as z from 'zod';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Plus } from 'lucide-vue-next';

const { toast } = useToast();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Posts', href: '/posts' }];
const props = defineProps<{ posts: any }>();

const operation = ref<'Create' | 'Edit'>('Create');
const isDialogOpen = ref(false);
const posts = ref(props.posts);
const post = ref<Partial<any>>({});

const formSchema = toTypedSchema(z.object({
    title: z.string().min(1, 'Title is required').max(255, 'Title must be 255 characters or less'),
    content: z.string().min(1, 'Content is required'),
    feature_image: z
        .instanceof(File)
        .refine((file) => file.size > 0, { message: 'Image is required' })
        .refine((file) => file.size <= 5 * 1024 * 1024, { message: 'Image must be less than 5MB' })
        .refine((file) => ['image/jpeg', 'image/png'].includes(file.type), {
            message: 'Only JPEG and PNG images are allowed'
        })
        .or(z.literal('').optional()),
}));

const defaultPostValues = computed(() => ({
    title: '',
    content: '',
}));

const form = useForm({
    validationSchema: formSchema,
    initialValues: post.value,
});

watch(
    () => post.value,
    (newPost) => {
        if (newPost) {
            form.setValues(newPost);
        }
    },
    { deep: false }
);

const handleCreateClick = () => {
    operation.value = 'Create';
    isDialogOpen.value = true;
    post.value = { ...defaultPostValues.value };
};

const handleEdit = (data: any) => {
    operation.value = 'Edit';
    isDialogOpen.value = true;
    post.value = { ...data };
};

const handleApiResponse = async (method: string, url: string, values: any) => {
    try {
        const headers = { 'Content-Type': 'multipart/form-data' };

        if (method === 'put') {
            values.append('_method', 'PUT');
        }

        const response = await axios.post(url, values, { headers });

        if (response.status === 201) {
            posts.value.data.unshift(response.data.data);
            toast({ description: 'Post created successfully' });
        } else {
            const index = posts.value.data.findIndex((p: any) => p.id === response.data.data.id);
            if (index !== -1) {
                posts.value.data[index] = response.data.data;
                toast({ description: 'Post updated successfully' });
            }
        }

        return true;
    } catch (error: any) {
        const errors = error.response?.data.errors;
        if (errors && 'validation' in errors) {
            form.setErrors(errors.validation);
        } else {
            toast({ description: 'An unexpected error occurred.', variant: 'destructive' });
        }
        return false;
    }
};

const onSubmit = form.handleSubmit(async (values) => {
    const method = operation.value === 'Create' ? 'post' : 'put';
    const url = `/posts${operation.value === 'Edit' ? '/' + post.value.id : ''}`;

    const formData = new FormData();
    formData.append('title', values.title);
    formData.append('content', values.content);
    if (values.feature_image instanceof File) {
        formData.append('feature_image', values.feature_image);
    }

    const success = await handleApiResponse(method, url, formData);

    if (success) {
        isDialogOpen.value = false;
        form.resetForm();
    }
});

const onDialogClose = () => {
    isDialogOpen.value = false;
    form.resetForm();
};

const handleFileChange = (file: File | null | Event) => {
    let selectedFile: File | null = null;

    if (file instanceof Event) {
        const input = file.target as HTMLInputElement;
        selectedFile = input.files?.[0] || null;
    } else {
        selectedFile = file as File | null;
    }

    if (selectedFile) {
        form.setFieldValue('feature_image', selectedFile);
    } else {
        form.setFieldValue('feature_image', null);
    }
};

const currentFileValue = computed(() => form.values.feature_image || null);

const hasNoPosts = computed(() => !posts.value.data.length);
</script>

<template>

    <Head title="Posts" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto">
            <div class="flex items-center justify-end py-4 space-x-2">
                <Button size="sm" @click="handleCreateClick">
                    <Plus class="w-5 h-5" />
                    Create Post
                </Button>
            </div>
            <template v-if="!hasNoPosts">
                <div class="grid gap-4">
                    <Card v-for="post in posts.data" :key="post.id">
                        <CardHeader>{{ post.title }}</CardHeader>
                        <CardContent>
                            <div v-if="post.feature_image" class="w-full h-48 overflow-hidden rounded-lg border">
                                <img :src="post.feature_image" alt="Feature Image" class="w-full h-full object-cover" />
                            </div>
                            {{ post.content }}
                        </CardContent>
                        <CardFooter>
                            <Button size="sm" @click="handleEdit(post)">Edit</Button>
                        </CardFooter>
                    </Card>
                </div>
            </template>
            <div v-else class="text-center py-8 text-gray-500">
                No posts available. Create your first post!
            </div>
        </div>

        <DataTableDialog :isDialogOpen="isDialogOpen" :operation="operation" @onDialogClose="onDialogClose">
            <template #dialog-content>
                <form @submit.prevent="onSubmit" class="space-y-4 p-4">
                    <FormField :validateOnBlur="false" v-slot="{ componentField }" name="title">
                        <FormItem>
                            <FormLabel>Title</FormLabel>
                            <FormControl>
                                <Input v-bind="componentField" placeholder="Enter post title" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField :validateOnBlur="false" v-slot="{ componentField }" name="content">
                        <FormItem>
                            <FormLabel>Content</FormLabel>
                            <FormControl>
                                <Textarea v-bind="componentField" placeholder="Enter post content" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField :validateOnBlur="false" v-slot="{ componentField }" name="feature_image">
                        <FormItem>
                            <FormLabel>Image</FormLabel>
                            <FormControl>
                                <Input type="file" @change="handleFileChange" accept="image/*" class="mt-1 block w-full"
                                    ref="fileInput" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <div class="flex justify-end space-x-2">
                        <Button variant="outline" @click="onDialogClose">Cancel</Button>
                        <Button type="submit">Save</Button>
                    </div>
                </form>
            </template>
        </DataTableDialog>
    </AppLayout>
</template>