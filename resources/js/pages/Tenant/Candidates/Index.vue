<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Search, SlidersHorizontal, UserPlus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import PagePanel from '@/components/page/PagePanel.vue';
import PageShell from '@/components/page/PageShell.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
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
    create as candidatesCreate,
    destroy as candidatesDestroy,
    edit as candidatesEdit,
    index as candidatesIndex,
} from '@/routes/tenant/candidates';
import type { BreadcrumbItem, User } from '@/types';

type PaginatedCandidates = {
    data: User[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
};

type Props = {
    candidates: PaginatedCandidates;
};

defineProps<Props>();

const page = usePage();
const tenant = computed(() => page.props.currentTenant);
const slug = computed(() => tenant.value?.slug ?? '');

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard.url(slug.value) },
    { title: 'Candidates', href: candidatesIndex.url(slug.value) },
];

const search = ref(
    new URL(page.url, 'https://maritest.test').searchParams.get(
        'filter[search]',
    ) ?? '',
);

function applyFilters(): void {
    router.get(
        candidatesIndex.url(slug.value),
        { 'filter[search]': search.value || undefined },
        { preserveState: true, replace: true },
    );
}

function deleteCandidate(candidateId: number): void {
    if (confirm('Are you sure you want to remove this candidate?')) {
        router.delete(
            candidatesDestroy.url({ tenant: slug.value, candidate: candidateId }),
        );
    }
}

function formatDate(date: string): string {
    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(new Date(date));
}
</script>

<template>
    <Head :title="`${tenant?.name} - Candidates`" />

    <TenantLayout :breadcrumbs="breadcrumbs">
        <PageShell
            title="Candidates"
            description="Manage candidate accounts separately from internal tenant users."
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
                                    placeholder="Search candidates or emails..."
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
                            <Link :href="candidatesCreate.url(slug)">
                                <Button class="h-10 shadow-none">
                                    <UserPlus class="size-4" />
                                    Add candidate
                                </Button>
                            </Link>
                        </div>
                    </div>
                </template>

                <Table>
                    <TableHeader class="bg-muted/20">
                        <TableRow class="hover:bg-transparent">
                            <TableHead class="w-[34%]">Candidate</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Added</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow
                            v-for="candidate in candidates.data"
                            :key="candidate.id"
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
                                            {{ getInitials(candidate.name) }}
                                        </AvatarFallback>
                                    </Avatar>

                                    <div class="space-y-1">
                                        <div
                                            class="font-medium text-foreground"
                                        >
                                            {{ candidate.name }}
                                        </div>
                                        <div
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{
                                                candidate.email_verified_at
                                                    ? 'Verified account'
                                                    : 'Email verification pending'
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </TableCell>

                            <TableCell class="text-muted-foreground">
                                <span class="break-all">{{ candidate.email }}</span>
                            </TableCell>

                            <TableCell class="text-muted-foreground">
                                {{ formatDate(candidate.created_at) }}
                            </TableCell>

                            <TableCell class="text-right">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Link
                                        :href="
                                            candidatesEdit.url({
                                                tenant: slug,
                                                candidate: candidate.id,
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
                                        @click="deleteCandidate(candidate.id)"
                                    >
                                        Remove
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableRow
                            v-if="candidates.data.length === 0"
                            class="hover:bg-transparent"
                        >
                            <TableCell
                                colspan="4"
                                class="px-4 py-12 text-center"
                            >
                                <div class="space-y-2">
                                    <p class="font-medium text-foreground">
                                        No candidates found
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        Add a candidate to start managing their account separately.
                                    </p>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <template #footer v-if="candidates.last_page > 1">
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <p class="text-sm text-muted-foreground">
                            Showing page {{ candidates.current_page }} of
                            {{ candidates.last_page }} ({{
                                candidates.total
                            }}
                            total)
                        </p>

                        <div class="flex flex-wrap gap-2">
                            <template
                                v-for="link in candidates.links"
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
