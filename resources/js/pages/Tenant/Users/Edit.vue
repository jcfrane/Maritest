<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PagePanel from '@/components/page/PagePanel.vue';
import PageShell from '@/components/page/PageShell.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import TenantLayout from '@/layouts/TenantLayout.vue';
import { dashboard } from '@/routes/tenant';
import { index as usersIndex, edit as usersEdit, update as usersUpdate } from '@/routes/tenant/users';
import type { BreadcrumbItem, User } from '@/types';

type Props = {
    user: {
        data: User & { roles: string[] };
    };
    roles: string[];
};

const props = defineProps<Props>();

const page = usePage();
const tenant = page.props.currentTenant;
const slug = tenant?.slug ?? '';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard.url(slug) },
    { title: 'Users', href: usersIndex.url(slug) },
    { title: 'Edit User', href: usersEdit.url({ tenant: slug, user: props.user.data.id }) },
];

const form = useForm({
    name: props.user.data.name,
    email: props.user.data.email,
    password: '',
    password_confirmation: '',
    role: props.user.data.roles?.[0] ?? '',
});

function submit() {
    form.put(usersUpdate.url({ tenant: slug, user: props.user.data.id }));
}
</script>

<template>
    <Head :title="`${tenant?.name} - Edit User`" />

    <TenantLayout :breadcrumbs="breadcrumbs">
        <PageShell
            title="Edit User"
            description="Update user details and role."
            content-class="gap-6"
        >
            <PagePanel body-class="p-6">
                <form class="max-w-lg space-y-6" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            autofocus
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="Leave blank to keep current"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">
                            Confirm Password
                        </Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="role">Role</Label>
                        <Select v-model="form.role">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Select a role" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="role in roles"
                                    :key="role"
                                    :value="role"
                                >
                                    {{ role }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.role" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button type="submit" :disabled="form.processing">
                            Update User
                        </Button>
                        <Link :href="usersIndex.url(slug)">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                    </div>
                </form>
            </PagePanel>
        </PageShell>
    </TenantLayout>
</template>
