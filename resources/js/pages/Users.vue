<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type UsersResponse, type User } from '@/types';
import { getColumns } from '@/components/columns';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from '@/components/DataTable.vue';
import DataTablePagination from '@/components/DataTablePagination.vue';
import { Button } from '@/components/ui/button';
import { Plus } from 'lucide-vue-next';
import DataTableDialog from "@/components/DataTableDialog.vue";
import axios from "axios";
import { useToast } from '@/components/ui/toast/use-toast';
import { type ErrorMessages } from '@/types';
const { toast } = useToast();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
];

let operation = ref("Create");
let isDialogOpen = ref(false);

const props = defineProps<{
    users: UsersResponse;
}>();

const user = ref<Partial<User>>({});

function onDialogClose() {
    isDialogOpen.value = false;
}

function onCreate(data: User) {
    users.value.data.unshift(data);
}

function onEdit(data: User) {
    const userToUpdate = users.value.data.find((user) => user.id === data.id);
    if (userToUpdate) {
        Object.assign(userToUpdate, data);
    }
}

const users = ref<UsersResponse>(props.users);
const tableInstance = ref<any>(null);

const setTableInstance = (instance: any) => {
    tableInstance.value = instance;
};

const handleCreateClick = () => {
    operation.value = 'Create';
    isDialogOpen.value = true;
    user.value = {};
};

const handleEdit = (data: User) => {
    operation.value = "Edit";
    isDialogOpen.value = true;
    user.value = data;
};

const handleDelete = (data: User) => {
    axios({
        method: "delete",
        url: `/users/${data.id}`,
        responseType: "json",
    }).then(function (response) {
        if (response.status === 204) {
            Object.assign(users.value.data, users.value.data.filter((user) => user.id !== data.id));
            toast({
                description: "User deleted successfully",
            });
        }
    }).catch((error) => {
        const errors = error.response?.data.errors;
        if (errors && 'validation' in errors) {
            const validationErrors = errors['validation'];
            const firstErrorKey = Object.keys(errors)[0];
            const firstErrorMessages: ErrorMessages = errors[firstErrorKey];
            if (validationErrors) {
                Object.entries(firstErrorMessages).forEach(([field, messages]: [string, string[]]) => {
                    toast({
                        title: field,
                        description: messages[0],
                        variant: "destructive"
                    });
                });
            }
        }
    })
};

const myDataTable = ref();

</script>

<template>

    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container py-10 mx-auto">
            <div class="flex items-center justify-end py-4 space-x-2">
                <Button variant="outline" size="sm" @click="handleCreateClick">
                    <Plus class="w-5 h-5" />
                    New User
                </Button>
            </div>
            <DataTable ref="myDataTable" :columns="getColumns(handleEdit, handleDelete)" :data="users.data"
                :meta="users.meta" @table-instance="setTableInstance" />
            <DataTablePagination :table="tableInstance" :meta="users.meta" />
        </div>
        <DataTableDialog v-if="isDialogOpen" :operation="operation" :isDialogOpen="isDialogOpen"
            @onDialogClose="onDialogClose" :user="user" @onCreate="onCreate" @onEdit="onEdit">
        </DataTableDialog>
    </AppLayout>
</template>