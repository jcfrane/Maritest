<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Search, SlidersHorizontal, UserPlus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import PagePanel from '@/components/page/PagePanel.vue';
import PageShell from '@/components/page/PageShell.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { getInitials } from '@/composables/useInitials';
import TenantLayout from '@/layouts/TenantLayout.vue';
import { dashboard } from '@/routes/tenant';
import {
    create as usersCreate,
    destroy as usersDestroy,
    edit as usersEdit,
    index as usersIndex,
} from '@/routes/tenant/users';
import type { BreadcrumbItem, User } from '@/types';

type PaginatedUsers = {
    data: (User & { roles?: string[] })[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
};

type Props = {
    users: PaginatedUsers;
    roles: string[];
};

const props = defineProps<Props>();

const page = usePage();
const tenant = computed(() => page.props.currentTenant);
const slug = computed(() => tenant.value?.slug ?? '');

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard.url(slug.value) },
    { title: 'Users', href: usersIndex.url(slug.value) },
];

const search = ref(
    new URL(page.url, 'https://maritest.test').searchParams.get(
        'filter[search]',
    ) ?? '',
);

const tenantUsers = computed(() =>
    props.users.data.map((user) => ({
        ...user,
        roles: user.roles ?? [],
    })),
);

function applyFilters(): void {
    router.get(
        usersIndex.url(slug.value),
        { 'filter[search]': search.value || undefined },
        { preserveState: true, replace: true },
    );
}

function deleteUser(userId: number): void {
    if (confirm('Are you sure you want to remove this user?')) {
        router.delete(usersDestroy.url({ tenant: slug.value, user: userId }));
    }
}
</script>

<template>
    <Head :title="`${tenant?.name} - Users`" />

    <TenantLayout :breadcrumbs="breadcrumbs">
        <PageShell
            title="Users"
            description="Manage tenant team members and their access."
            content-class="gap-5"
        >
            <PagePanel>
                <template #header>
                    <div
                        class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
                    >
                        <form
                            class="flex w-full items-center gap-3 lg:max-w-xl"
                            @submit.prevent="applyFilters"
                        >
                            <div class="relative flex-1">
                                <Search
                                    class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                                />
                                <Input
                                    v-model="search"
                                    placeholder="Search users or emails..."
                                    class="h-10 border-border/70 bg-background pl-9 shadow-none"
                                />
                            </div>

                            <div
                                class="h-6 w-px shrink-0 bg-border/80"
                                aria-hidden="true"
                            />

                            <Button
                                type="submit"
                                variant="outline"
                                class="h-10 min-w-20 border-border/70 bg-background"
                            >
                                <SlidersHorizontal class="size-4" />
                                Apply
                            </Button>
                        </form>

                        <div
                            class="flex items-center gap-3 self-end lg:self-auto"
                        >
                            <Link :href="usersCreate.url(slug)">
                                <Button class="h-10 shadow-none">
                                    <UserPlus class="size-4" />
                                    Add user
                                </Button>
                            </Link>
                        </div>
                    </div>
                </template>

                <Table>
                    <TableHeader class="bg-muted/20">
                        <TableRow class="hover:bg-transparent">
                            <TableHead class="w-[34%]">User</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Roles</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow
                            v-for="user in tenantUsers"
                            :key="user.id"
                            class="bg-background/70"
                        >
                            <TableCell>
                                <div class="flex items-center gap-3">
                                    <Avatar
                                        class="size-10 border border-primary/10"
                                    >
                                        <AvatarFallback
                                            class="bg-primary/10 font-semibold text-primary"
                                        >
                                            {{ getInitials(user.name) }}
                                        </AvatarFallback>
                                    </Avatar>

                                    <div class="space-y-1">
                                        <div
                                            class="font-medium text-foreground"
                                        >
                                            {{ user.name }}
                                        </div>
                                        <div
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{
                                                user.email_verified_at
                                                    ? 'Verified account'
                                                    : 'Email verification pending'
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </TableCell>

                            <TableCell class="text-muted-foreground">
                                <span class="break-all">{{ user.email }}</span>
                            </TableCell>

                            <TableCell>
                                <div class="flex flex-wrap gap-2">
                                    <Badge
                                        v-for="role in user.roles"
                                        :key="role"
                                        variant="secondary"
                                        class="rounded-full px-3 py-1"
                                    >
                                        {{ role }}
                                    </Badge>

                                    <Badge
                                        v-if="user.roles.length === 0"
                                        variant="outline"
                                        class="rounded-full px-3 py-1"
                                    >
                                        No role
                                    </Badge>
                                </div>
                            </TableCell>

                            <TableCell class="text-right">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Link
                                        :href="
                                            usersEdit.url({
                                                tenant: slug,
                                                user: user.id,
                                            })
                                        "
                                    >
                                        <Button variant="outline" size="sm">
                                            Edit
                                        </Button>
                                    </Link>

                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="text-destructive hover:text-destructive"
                                        @click="deleteUser(user.id)"
                                    >
                                        Remove
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableRow
                            v-if="users.data.length === 0"
                            class="hover:bg-transparent"
                        >
                            <TableCell
                                colspan="4"
                                class="px-4 py-12 text-center"
                            >
                                <div class="space-y-2">
                                    <p class="font-medium text-foreground">
                                        No users found
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        Try widening your search or add a new
                                        team member to get started.
                                    </p>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <template #footer v-if="users.last_page > 1">
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <p class="text-sm text-muted-foreground">
                            Showing page {{ users.current_page }} of
                            {{ users.last_page }} ({{ users.total }} total)
                        </p>

                        <div class="flex flex-wrap gap-2">
                            <template
                                v-for="link in users.links"
                                :key="link.label"
                            >
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    preserve-state
                                >
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        :class="
                                            link.active
                                                ? 'border-primary/30 bg-primary text-primary-foreground hover:bg-primary/95 hover:text-primary-foreground'
                                                : ''
                                        "
                                    >
                                        <span v-html="link.label" />
                                    </Button>
                                </Link>

                                <Button
                                    v-else
                                    variant="outline"
                                    size="sm"
                                    disabled
                                >
                                    <span v-html="link.label" />
                                </Button>
                            </template>
                        </div>
                    </div>
                </template>
            </PagePanel>
        </PageShell>
    </TenantLayout>
</template>
