<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ClipboardList,
    Plus,
    Pencil,
    Trash2,
    Search,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import PagePanel from '@/components/page/PagePanel.vue';
import PageShell from '@/components/page/PageShell.vue';
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
import TenantLayout from '@/layouts/TenantLayout.vue';
import { dashboard } from '@/routes/tenant';
import {
    index as questionnairesIndex,
    create as questionnairesCreate,
    edit as questionnairesEdit,
    destroy as questionnairesDestroy,
} from '@/routes/tenant/questionnaires';
import type { BreadcrumbItem } from '@/types';
import type { Questionnaire } from '@/types/questionnaire';

type Props = {
    questionnaires: { data: Questionnaire[] };
};

defineProps<Props>();

const page = usePage();
const slug = computed(() => (page.props.currentTenant as { slug: string })?.slug ?? '');

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard.url(slug.value) },
    { title: 'Questionnaires', href: questionnairesIndex.url(slug.value) },
];

const search = ref('');

function deleteQuestionnaire(id: number): void {
    if (confirm('Are you sure you want to delete this questionnaire?')) {
        router.delete(
            questionnairesDestroy.url({ tenant: slug.value, questionnaire: id }),
        );
    }
}

function formatDate(date?: string): string {
    if (!date) {
        return '—';
    }
    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(new Date(date));
}

const filteredQuestionnaires = computed(() => {
    const props = usePage().props as unknown as Props;
    const list = props.questionnaires?.data ?? [];
    if (!search.value) {
        return list;
    }
    const q = search.value.toLowerCase();
    return list.filter(
        (item) =>
            item.title.toLowerCase().includes(q) ||
            (item.description && item.description.toLowerCase().includes(q)),
    );
});
</script>

<template>
    <Head :title="`Questionnaires`" />

    <TenantLayout :breadcrumbs="breadcrumbs">
        <PageShell
            title="Questionnaires"
            description="Create and manage exams, surveys, and other questionnaires."
            content-class="gap-5"
        >
            <PagePanel>
                <template #header>
                    <div
                        class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
                    >
                        <div class="relative w-full lg:max-w-sm">
                            <Search
                                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                v-model="search"
                                placeholder="Search questionnaires..."
                                class="h-10 border-border/70 bg-background pl-9 shadow-none"
                            />
                        </div>

                        <Link :href="questionnairesCreate.url(slug)">
                            <Button class="h-10 shadow-none">
                                <Plus class="size-4" />
                                New Questionnaire
                            </Button>
                        </Link>
                    </div>
                </template>

                <Table>
                    <TableHeader class="bg-muted/20">
                        <TableRow class="hover:bg-transparent">
                            <TableHead class="w-[40%]">Title</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Sections</TableHead>
                            <TableHead>Updated</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow
                            v-for="questionnaire in filteredQuestionnaires"
                            :key="questionnaire.id"
                            class="bg-background/70"
                        >
                            <TableCell>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10"
                                    >
                                        <ClipboardList class="size-4 text-primary" />
                                    </div>
                                    <div class="space-y-0.5">
                                        <div class="font-medium text-foreground">
                                            {{ questionnaire.title }}
                                        </div>
                                        <div
                                            v-if="questionnaire.description"
                                            class="line-clamp-1 text-xs text-muted-foreground"
                                        >
                                            {{ questionnaire.description }}
                                        </div>
                                    </div>
                                </div>
                            </TableCell>

                            <TableCell>
                                <Badge
                                    :variant="questionnaire.status === 'published' ? 'default' : 'secondary'"
                                    class="capitalize"
                                >
                                    {{ questionnaire.status }}
                                </Badge>
                            </TableCell>

                            <TableCell class="text-muted-foreground">
                                {{ questionnaire.sections?.length ?? 0 }}
                            </TableCell>

                            <TableCell class="text-muted-foreground">
                                {{ formatDate(questionnaire.updated_at) }}
                            </TableCell>

                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <Link
                                        :href="
                                            questionnairesEdit.url({
                                                tenant: slug,
                                                questionnaire: questionnaire.id!,
                                            })
                                        "
                                    >
                                        <Button variant="ghost" size="sm">
                                            <Pencil class="size-3.5" />
                                        </Button>
                                    </Link>

                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="text-destructive hover:text-destructive"
                                        @click="deleteQuestionnaire(questionnaire.id!)"
                                    >
                                        <Trash2 class="size-3.5" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableRow
                            v-if="filteredQuestionnaires.length === 0"
                            class="hover:bg-transparent"
                        >
                            <TableCell
                                colspan="5"
                                class="px-4 py-12 text-center"
                            >
                                <div class="space-y-2">
                                    <ClipboardList class="mx-auto size-8 text-muted-foreground/50" />
                                    <p class="font-medium text-foreground">
                                        No questionnaires found
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        Create your first questionnaire to get started.
                                    </p>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </PagePanel>
        </PageShell>
    </TenantLayout>
</template>
