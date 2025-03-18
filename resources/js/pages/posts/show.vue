<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Post } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardFooter } from '@/components/ui/card';
import { ref, onMounted, computed } from 'vue';
import Quill from 'quill';
import { QuillyEditor } from 'vue-quilly';
import 'quill/dist/quill.core.css';
import 'quill/dist/quill.snow.css';
import 'quill/dist/quill.bubble.css';


const props = defineProps<{
    post: Post;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Posts', href: '/posts' },
    { title: props.post.data.slug, href: '' },
];

const options = {
    theme: 'snow',
    modules: {
        toolbar: true,
    },
    placeholder: 'Compose an epic...',
    readOnly: false,
};

const comments = ref([
    {
        id: 1,
        author: 'John Doe',
        content: 'This is a great post! Thanks for sharing this valuable information.',
        date: 'March 15, 2025',
        replies: [
            {
                id: 4,
                author: 'Alice Johnson',
                content: 'I completely agree with you, John. The insights here are quite valuable.',
                date: 'March 16, 2025',
            },
        ],
    },
]);

const totalComments = computed(() => {
    return comments.value.reduce((total, comment) => total + 1 + comment.replies.length, 0);
});

const editor = ref<InstanceType<typeof QuillyEditor>>();
const model = ref<string>(props.post.data.content);

let quill: Quill | null = null;
onMounted(() => {
    quill = editor.value?.initialize(Quill)!
})
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

    <Head :title="props.post.data.title" />

    <component :is="$page.props.auth.user ? AppLayout : 'div'"
        :breadcrumbs="$page.props.auth.user ? breadcrumbs : undefined">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <h1 class="text-3xl font-bold mb-4">{{ props.post.data.title }}</h1>

            <Card class="w-full overflow-hidden">
                <CardHeader v-if="props.post.data.feature_image" class="my-1 p-0">
                    <img :src="props.post.data.feature_image" alt="Feature Image" class="w-full h-96 object-cover" />
                </CardHeader>

                <CardContent>
                    <QuillyEditor class="min-h-[10vh] border"
                        v-if="route().current('posts.show') && $page.props.auth.user && $page.props.auth.user.id == props.post.data.user_id"
                        ref="editor" v-model="model" :options="options"
                        @update:modelValue="(value) => console.log('HTML model updated:', value)"
                        @text-change="({ delta, oldContent, source }) => console.log('text-change', delta, oldContent, source)"
                        @selection-change="({ range, oldRange, source }) => console.log('selection-change', range, oldRange, source)"
                        @editor-change="(eventName) => console.log('editor-change', `eventName: ${eventName}`)"
                        @focus="(quill) => console.log('focus', quill)" @blur="(quill) => console.log('blur', quill)"
                        @ready="(quill) => console.log('ready', quill)" />
                    <div v-else v-html="props.post.data.content" class="prose max-w-none"></div>
                </CardContent>

                <CardFooter>
                    <div v-if="props.post.data.tags && props.post.data.tags.length" class="flex flex-wrap gap-2">
                        <span v-for="tag in props.post.data.tags" :key="tag.id"
                            class="px-3 py-1 bg-muted text-foreground text-sm rounded">
                            {{ tag.title }}
                        </span>
                    </div>
                </CardFooter>
            </Card>
        </div>

        <Card v-if="!route().current('posts.show') && $page.props.auth.user" class="w-full mt-6">
            <CardHeader>
                <h2 class="text-xl font-semibold">Comments ({{ totalComments }})</h2>
            </CardHeader>
            <CardContent>
                <div v-for="comment in comments" :key="comment.id" class="border-b pb-4 last:border-b-0 last:pb-0">
                    <div>
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium">{{ comment.author }}</h3>
                            <span class="text-sm text-muted-foreground">{{ comment.date }}</span>
                        </div>
                        <p class="mt-1">{{ comment.content }}</p>
                    </div>
                </div>
                <div class="mt-6">
                    <textarea class="w-full p-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                        rows="3" placeholder="Write a comment..."></textarea>
                    <button class="mt-2 px-4 py-2 bg-primary text-primary-foreground rounded-md">Post Comment</button>
                </div>
            </CardContent>
        </Card>

        <div v-else-if="!$page.props.auth.user" class="mt-6 p-4 bg-muted rounded-md text-center">
            <p class="text-muted-foreground">Please <a href="/login" class="text-primary hover:underline">log in</a> to
                leave a comment</p>
        </div>
    </component>
</template>