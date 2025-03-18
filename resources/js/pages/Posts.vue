    <script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import { type BreadcrumbItem, type Tag } from '@/types';
    import { Head, Link } from '@inertiajs/vue3';
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
    import { Plus, X, ChevronDown } from 'lucide-vue-next';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
    import { route } from 'ziggy-js';
    import { Switch } from '@/components/ui/switch'

    const { toast } = useToast();

    const breadcrumbs = computed<BreadcrumbItem[]>(() => {
        return route().current('posts.index')
            ? [{ title: 'Posts', href: '/posts' }]
            : [{ title: 'Feed', href: '/feed' }];
    });
    const props = defineProps<{ posts: any, tags: any }>();

    const tagOptions = computed(() => {
        if (props.tags && Array.isArray(props.tags.data)) {
            return props.tags.data as Tag[];
        }
        else if (Array.isArray(props.tags)) {
            return props.tags as Tag[];
        }
        return [] as Tag[];
    });

    const operation = ref<'Create' | 'Edit'>('Create');
    const isDialogOpen = ref(false);
    const posts = ref(props.posts);
    const post = ref<Partial<any>>({});
    const selectedTags = ref<Tag[]>([]);
    const tagSearchQuery = ref('');

    const filteredTags = computed(() => {
        if (!tagSearchQuery.value) return tagOptions.value;
        return tagOptions.value.filter(tag =>
            tag.title.toLowerCase().includes(tagSearchQuery.value.toLowerCase())
        );
    });

    const formSchema = toTypedSchema(z.object({
        title: z.string().min(1, 'Title is required').max(255, 'Title must be 255 characters or less'),
        content: z.string().min(1, 'Content is required'),
        feature_image: z.union([
            z.instanceof(File).refine((file) => file.size <= 5 * 1024 * 1024, { message: 'Image must be less than 5MB' })
                .refine((file) => ['image/jpeg', 'image/png'].includes(file.type), { message: 'Only JPEG and PNG images are allowed' }),
            z.string(),
            z.null(),
            z.undefined(),
        ]).optional(),
    }));

    const defaultPostValues = computed(() => ({
        title: '',
        content: '',
        feature_image: undefined,
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
                selectedTags.value = Array.isArray(newPost.tags) ? [...newPost.tags] : [];
            }
        },
        { deep: false }
    );

    const handleCreateClick = () => {
        operation.value = 'Create';
        isDialogOpen.value = true;
        post.value = { ...defaultPostValues.value };
        selectedTags.value = [];
        tagSearchQuery.value = '';
    };

    const handleEdit = (data: any) => {
        operation.value = 'Edit';
        isDialogOpen.value = true;
        post.value = { ...data };
        selectedTags.value = Array.isArray(data.tags) ? [...data.tags] : [];
        tagSearchQuery.value = '';
    };

    const handleDelete = async (data: any) => {
        try {
            const index = posts.value.data.findIndex((p: any) => p.id === data.id);
            if (index !== -1) {
                posts.value.data.splice(index, 1);
            }

            const response = await axios.delete(`/posts/${data.slug}`);

            if (response.status === 200) {
                const index = posts.value.data.findIndex((p: any) => p.id === data.id);
                if (index !== -1) {
                    posts.value.data.splice(index, 1);
                }

                toast({ description: 'Post deleted successfully' });
            }
        } catch (error: any) {
            const errorMessage = error.response?.data?.message || 'An error occurred while deleting the post';
            toast({ description: errorMessage, variant: 'destructive' });
        }
    };

    const processPostRequest = async (method: string, url: string, values: any) => {
        try {
            const headers = { 'Content-Type': 'multipart/form-data' };

            if (method === 'put') {
                values.append('_method', 'PUT');
            }

            const response = await axios.post(url, values, { headers });

            if (response.status === 201) {
                const newPost = {
                    ...response.data.data,
                    tags: selectedTags.value,
                };
                posts.value.data.unshift(newPost);
                toast({ description: 'Post created successfully' });
            } else {
                const index = posts.value.data.findIndex((p: any) => p.id === response.data.data.id);
                if (index !== -1) {
                    posts.value.data[index] = {
                        ...response.data.data,
                        tags: selectedTags.value,
                    };
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
        const url = `/posts${operation.value === 'Edit' ? '/' + post.value.slug : ''}`;

        const formData = new FormData();
        formData.append('title', values.title);
        formData.append('content', values.content);

        if (values.feature_image instanceof File) {
            formData.append('feature_image', values.feature_image);
        }

        const tagData = selectedTags.value.map(tag => tag.id || tag.title);
        formData.append('tags', JSON.stringify(tagData));

        const success = await processPostRequest(method, url, formData);

        if (success) {
            isDialogOpen.value = false;
            form.resetForm();
            selectedTags.value = [];
            tagSearchQuery.value = '';
        }
    });

    const onDialogClose = () => {
        isDialogOpen.value = false;
        form.resetForm();
        selectedTags.value = [];
        tagSearchQuery.value = '';
    };

    const handleFileChange = (event: Event) => {
        const input = event.target as HTMLInputElement;
        const file = input.files?.[0] || null;
        form.setFieldValue('feature_image', file);
    };

    const hasNoPosts = computed(() => !posts.value.data.length);

    const toggleTag = (tag: Tag) => {
        const index = selectedTags.value.findIndex(t => t.id === tag.id);
        if (index === -1) {
            selectedTags.value.push(tag);
        } else {
            selectedTags.value.splice(index, 1);
        }
    };

    const removeTag = (tag: Tag) => {
        const index = selectedTags.value.findIndex(t => t.id === tag.id);
        if (index !== -1) {
            selectedTags.value.splice(index, 1);
        }
    };

    const isTagSelected = (tag: Tag) => {
        return selectedTags.value.some(t => t.id === tag.id);
    };

    const getTagKey = (tag: Tag) => tag.id ?? `tag-${tag.title}`;

    const toggleApproval = async (post, value) => {
        try {
            const status = value ? 'approved' : 'disapproved';

            await axios.put(route('posts.update', post.slug), {
                status: status
            });

            post.status = status;
            toast({ description: `Post ${status === 'approved' ? 'approved' : 'disapproved'} successfully` });

        } catch (error) {
            console.error('Error updating approval status:', error);
            toast({ description: 'Failed to update approval status', variant: 'destructive' });

        }
    };

    const togglePublish = async (post, value) => {
        try {
            await axios.put(route('posts.update', post.slug), {
                is_published: value
            });

            post.is_published = value;
            toast({ description: `Post ${value ? 'published' : 'unpublished'} successfully` });
        } catch (error) {
            console.error('Error updating publish status:', error);
            toast({ description: 'Failed to update publish status', variant: 'destructive' });
        }
    };

</script>

    <template>
        <div v-if="!$page.props.auth.user"
            class="flex flex-col items-center bg-[#FDFDFC] p-4 text-[#1b1b18] dark:bg-[#0a0a0a] lg:justify-start">
            <header class="not-has-[nav]:hidden mb-4 w-full max-w-[335px] text-sm lg:max-w-4xl">
                <nav class="flex items-center justify-end gap-4">
                    <Link :href="route('login')"
                        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]">
                    Log in
                    </Link>
                    <Link :href="route('register')"
                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]">
                    Register
                    </Link>
                </nav>
            </header>
        </div>

        <Head title="Posts" />
        <component :is="$page.props.auth.user ? AppLayout : 'div'"
            :breadcrumbs="$page.props.auth.user ? breadcrumbs : undefined">
            <div class="container mx-auto">
                <div class="flex items-center justify-end py-4 space-x-2">
                    <Button size="sm" @click="handleCreateClick"
                        v-if="route().current('posts.index') && can('create:posts')">
                        <Plus class="w-5 h-5" />
                        Create Post
                    </Button>
                </div>
                <template v-if="!hasNoPosts">
                    <div class="grid gap-4">
                        <Card v-for="post in posts.data" :key="post.id"
                            class="cursor-pointer select-none shadow-sm shadow-black/10 transition-shadow hover:shadow-md hover:shadow-black/20">

                            <Link v-if="!$page.props.auth.user || can('view:posts')"
                                :href="route(route().current() === 'feed.index' ? 'feed.show' : 'posts.show', post.slug)">
                            <CardHeader>{{ post.title }}</CardHeader>
                            <CardContent>
                                <div v-if="post.feature_image" class="w-full h-48 overflow-hidden rounded-lg border">
                                    <img :src="post.feature_image" alt="Feature Image"
                                        class="w-full h-full object-cover" />
                                </div>
                                <div v-html="post.content" class="mt-3"></div>
                                <div v-if="post.tags && post.tags.length" class="mt-3 flex flex-wrap gap-2">
                                    <span v-for="tag in post.tags" :key="getTagKey(tag)"
                                        class="px-2 py-1 bg-gray-200 text-gray-700 text-sm rounded">
                                        {{ tag.title }}
                                    </span>
                                </div>
                            </CardContent>
                            </Link>

                            <div v-else class="block opacity-50 cursor-not-allowed">
                                <CardHeader>{{ post.title }}</CardHeader>
                                <CardContent>
                                    <div v-if="post.feature_image"
                                        class="w-full h-48 overflow-hidden rounded-lg border">
                                        <img :src="post.feature_image" alt="Feature Image"
                                            class="w-full h-full object-cover" />
                                    </div>
                                    <div v-html="post.content" class="mt-3"></div>
                                    <div v-if="post.tags && post.tags.length" class="mt-3 flex flex-wrap gap-2">
                                        <span v-for="tag in post.tags" :key="getTagKey(tag)"
                                            class="px-2 py-1 bg-gray-200 text-gray-700 text-sm rounded">
                                            {{ tag.title }}
                                        </span>
                                    </div>
                                </CardContent>
                            </div>

                            <CardFooter class="flex flex-wrap gap-4 items-center justify-start p-4"
                                v-if="route().current('posts.index') && $page.props.auth.user">
                                <Button size="sm" @click="handleEdit(post)" v-if="is('user')">Edit</Button>
                                <Button size="sm" variant="destructive" @click="handleDelete(post)">Delete</Button>
                                <div class="flex items-center gap-2" v-if="can('approve:posts')">
                                    <Label for="approve-switch" class="font-medium text-sm">Approve</Label>
                                    <Switch id="approve-switch" :model-value="post.status === 'approved'"
                                        @update:model-value="(val) => toggleApproval(post, val)"
                                        aria-label="Approve post">
                                        <template #thumb>
                                            <Icon :icon="post.status === 'approved' ? 'lucide:check' : 'lucide:x'"
                                                class="size-3" aria-hidden="true" />
                                        </template>
                                    </Switch>
                                </div>

                                <div class="flex items-center gap-2" v-if="can('publish:posts')">
                                    <Label for="publish-switch" class="font-medium text-sm">Publish</Label>
                                    <Switch id="publish-switch" :model-value="post.is_published"
                                        @update:model-value="(val) => togglePublish(post, val)"
                                        aria-label="Publish post">
                                        <template #thumb>
                                            <Icon :icon="post.is_published ? 'lucide:eye' : 'lucide:eye-off'"
                                                class="size-3" aria-hidden="true" />
                                        </template>
                                    </Switch>
                                </div>
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

                        <FormField :validateOnBlur="false" name="feature_image">
                            <FormItem>
                                <FormLabel>Image (optional)</FormLabel>
                                <FormControl>
                                    <Input type="file" @change="handleFileChange" accept="image/*"
                                        class="mt-1 block w-full" ref="fileInput" />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>

                        <FormField :validateOnBlur="false" name="tags">
                            <FormItem>
                                <FormLabel>Tags</FormLabel>
                                <FormControl>
                                    <Popover>
                                        <PopoverTrigger as-child>
                                            <Button variant="outline" class="w-full justify-between">
                                                {{ selectedTags.length ?
                                                    `${selectedTags.length} tag(s) selected` : 'Select tags' }}
                                                <ChevronDown class="ml-2 h-4 w-4" />
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent class="w-[var(--radix-popover-trigger-width)] max-h-60 p-0"
                                            align="start">
                                            <div class="p-2 space-y-2">
                                                <Input v-model="tagSearchQuery" placeholder="Search tags..."
                                                    class="w-full" @click.stop />
                                                <div v-if="filteredTags.length" class="max-h-48 overflow-y-auto">
                                                    <div v-for="tag in filteredTags" :key="getTagKey(tag)"
                                                        class="flex items-center space-x-2 py-1">
                                                        <Checkbox :id="'tag-' + getTagKey(tag)"
                                                            :checked="isTagSelected(tag)"
                                                            @update:checked="toggleTag(tag)" />
                                                        <label :for="'tag-' + getTagKey(tag)" class="text-sm">{{
                                                            tag.title
                                                            }}</label>
                                                    </div>
                                                </div>
                                                <div v-else class="text-sm text-gray-500 text-center py-2">
                                                    No tags found
                                                </div>
                                            </div>
                                        </PopoverContent>
                                    </Popover>
                                </FormControl>
                                <div v-if="selectedTags.length" class="mt-2 flex flex-wrap gap-2">
                                    <span v-for="tag in selectedTags" :key="getTagKey(tag)"
                                        class="inline-flex items-center px-2 py-1 bg-gray-200 text-gray-700 text-sm rounded">
                                        {{ tag.title }}
                                        <Button variant="ghost" size="sm" class="ml-1 p-0 h-4 w-4"
                                            @click="removeTag(tag)">
                                            <X class="h-3 w-3" />
                                        </Button>
                                    </span>
                                </div>
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
        </component>
    </template>