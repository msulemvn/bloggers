<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import { toast } from '@/components/ui/toast/use-toast';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select';
import { Input } from '@/components/ui/input';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Permissions', href: '/permissions' },
];

const props = defineProps<{
    auth: any;
    roles: { id: number; name: string; permissions: string[] }[];
    perms: { id: number; name: string }[];
    users: { id: number; name: string; roles: { id: number; name: string }[] }[];
}>();

const adminPermissions = ref<Record<string, string[]>>(
    props.roles.reduce((acc, role) => {
        acc[role.name] = role.permissions;
        return acc;
    }, {} as Record<string, string[]>)
);

const userRoles = ref<Record<number, string>>(
    props.users.reduce((acc, user) => {
        acc[user.id] = user.roles.length > 0 ? user.roles[0].name : '';
        return acc;
    }, {} as Record<number, string>)
);

const allPermissionsByEntity = computed(() => {
    const result: Record<string, string[]> = {};
    props.perms.forEach(permission => {
        const [action, entity] = permission.name.split(':');
        if (!result[entity]) result[entity] = [];
        result[entity].push(permission.name);
    });
    return result;
});

const togglePermission = (role: string, permission: string) => {
    const permissions = adminPermissions.value[role];
    if (permissions.includes(permission)) {
        adminPermissions.value[role] = permissions.filter(p => p !== permission);
    } else {
        adminPermissions.value[role].push(permission);
    }
};

const savePermissions = () => {
    console.log('Saved permissions:', adminPermissions.value);
    toast({
        title: "Success",
        description: "Permissions have been updated successfully",
    });
};

const saveUserRoles = () => {
    console.log('Saved user roles:', userRoles.value);
    toast({
        title: "Success",
        description: "User roles have been updated successfully",
    });
};

const userSearch = ref('');
const permissionSearch = ref('');

const currentUser = computed(() => props.users.find(user => user.id === props.auth.user.id));
const userRole = computed(() => currentUser.value?.roles[0]?.name || 'No Role');
const userPermissions = computed(() => {
    const role = props.roles.find(r => r.name === userRole.value);
    return role ? role.permissions : [];
});

const userPermissionsByModel = computed(() => {
    const result: Record<string, string[]> = {};
    userPermissions.value.forEach(permission => {
        const [action, model] = permission.split(':');
        if (!result[model]) result[model] = [];
        result[model].push(action);
    });
    return result;
});
</script>

<template>

    <Head title="Permissions" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="!is('admin')" class="flex w-full flex-col gap-4 rounded-xl mx-auto p-6">
            <Card>
                <CardHeader>
                    <CardTitle class="text-xl font-semibold">Your Role & Permissions</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-lg font-medium">Role: <span class="font-semibold">{{ userRole }}</span></p>
                    <Separator class="my-4" />
                    <h3 class="text-lg font-semibold">Permissions:</h3>
                    <ScrollArea class="h-auto pr-4 mt-2">
                        <div v-for="(actions, model) in userPermissionsByModel" :key="model" class="mb-4">
                            <h4 class="text-md font-semibold capitalize">{{ model }}</h4>
                            <ul class="list-disc list-inside space-y-2 pl-4">
                                <li v-for="action in actions" :key="action" class="text-sm">
                                    {{ action }}
                                </li>
                            </ul>
                        </div>
                    </ScrollArea>
                </CardContent>
            </Card>
        </div>
        <div v-else class="container mx-auto py-6">
            <Card class="mb-10">
                <CardHeader class="pb-4">
                    <CardTitle class="text-xl font-semibold">Assign Roles to Users</CardTitle>
                </CardHeader>
                <CardContent>
                    <Input v-model="userSearch" placeholder="Search users..." class="mb-4" />
                    <ScrollArea class="h-auto pr-4">
                        <div class="space-y-4">
                            <div v-for="user in props.users.filter(u => u.name.toLowerCase().includes(userSearch.toLowerCase()))"
                                :key="user.id" class="flex items-center justify-between">
                                <span class="text-lg font-medium">{{ user.name }}</span>
                                <Select v-model="userRoles[user.id]">
                                    <SelectTrigger class="w-[200px]">
                                        <SelectValue :placeholder="userRoles[user.id] || 'Select Role'" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="role in props.roles" :key="role.id" :value="role.name">
                                            {{ role.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </ScrollArea>
                </CardContent>
                <div class="flex justify-end p-4">
                    <Button @click="saveUserRoles" size="lg" class="px-8 py-3 text-base">
                        Save User Roles
                    </Button>
                </div>
            </Card>

            <h1 class="text-2xl font-bold mb-6">Change Permissions for Roles</h1>
            <Input v-model="permissionSearch" placeholder="Search roles..." class="mb-4" />
            <ScrollArea class="h-96 pr-4">
                <div>
                    <Card v-for="(permissions, role) in adminPermissions" :key="role" class="mb-10">
                        <CardHeader class="pb-4">
                            <CardTitle class="text-xl font-semibold capitalize">{{ role }}</CardTitle>
                        </CardHeader>
                        <CardContent class="pt-4 px-6 pb-6">
                            <div class="space-y-8">
                                <div v-for="(permissionList, entity, index) in allPermissionsByEntity" :key="entity">
                                    <h3 class="text-lg font-semibold capitalize mb-3">{{ entity }}</h3>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-y-5 gap-x-6 pl-4">
                                        <div v-for="permission in permissionList.filter(p => p.toLowerCase().includes(permissionSearch.toLowerCase()))"
                                            :key="permission" class="flex items-center space-x-3">
                                            <Checkbox :id="`${role}-${permission}`"
                                                :checked="adminPermissions[role].includes(permission)"
                                                @update:checked="() => togglePermission(role, permission)" />
                                            <label :for="`${role}-${permission}`" class="text-sm leading-none">
                                                {{ permission.split(':')[0] }}
                                            </label>
                                        </div>
                                    </div>
                                    <Separator v-if="index < Object.entries(allPermissionsByEntity).length - 1"
                                        class="mt-6" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </ScrollArea>
            <div class="flex justify-end">
                <Button @click="savePermissions" size="lg" class="px-8 py-3 text-base">
                    Save Permissions
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
