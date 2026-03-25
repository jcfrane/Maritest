<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Layers3, Pencil, Plus, Search, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
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
    create as examSetsCreate,
    destroy as examSetsDestroy,
    edit as examSetsEdit,
    index as examSetsIndex,
} from '@/routes/tenant/exam-sets';
import type { BreadcrumbItem } from '@/types';
import type { ExamSet } from '@/types/exam-set';

type Props = {
    examSets: { data: ExamSet[] };
};

const props = defineProps<Props>();

const page = usePage();
const slug = computed(
    () => (page.props.currentTenant as { slug: string })?.slug ?? '',
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard.url(slug.value) },
    { title: 'Exam Sets', href: examSetsIndex.url(slug.value) },
];

const search = ref('');
const selectedExamSet = ref<ExamSet | null>(null);
const isDeletingExamSet = ref(false);

function promptDeleteExamSet(examSet: ExamSet): void {
    selectedExamSet.value = examSet;
}

function setDeleteExamSetDialogOpen(open: boolean): void {
    if (!open) {
        selectedExamSet.value = null;
    }
}

function deleteExamSet(): void {
    if (!selectedExamSet.value?.id) {
        return;
    }

    isDeletingExamSet.value = true;

    router.delete(
        examSetsDestroy.url({
            tenant: slug.value,
            exam_set: selectedExamSet.value.id,
        }),
        {
            onSuccess: () => {
                selectedExamSet.value = null;
            },
            onFinish: () => {
                isDeletingExamSet.value = false;
            },
        },
    );
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

const filteredExamSets = computed(() => {
    const list = props.examSets.data ?? [];

    if (!search.value) {
        return list;
    }

    const query = search.value.toLowerCase();

    return list.filter(
        (examSet) =>
            examSet.title.toLowerCase().includes(query) ||
            (examSet.description ?? '').toLowerCase().includes(query),
    );
});
</script>

<template>
    <Head title="Exam Sets" />

    <TenantLayout :breadcrumbs="breadcrumbs">
        <PageShell
            title="Exam Sets"
            description="Bundle published questionnaires into reusable logical groupings."
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
                                placeholder="Search exam sets..."
                                class="h-10 border-border/70 bg-background pl-9 shadow-none"
                            />
                        </div>

                        <Link :href="examSetsCreate.url(slug)">
                            <Button class="h-10 shadow-none">
                                <Plus class="size-4" />
                                New Exam Set
                            </Button>
                        </Link>
                    </div>
                </template>

                <Table>
                    <TableHeader class="bg-muted/20">
                        <TableRow class="hover:bg-transparent">
                            <TableHead class="w-[40%]">Title</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Questionnaires</TableHead>
                            <TableHead>Updated</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow
                            v-for="examSet in filteredExamSets"
                            :key="examSet.id"
                            class="bg-background/70"
                        >
                            <TableCell>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10"
                                    >
                                        <Layers3 class="size-4 text-primary" />
                                    </div>
                                    <div class="space-y-0.5">
                                        <div
                                            class="font-medium text-foreground"
                                        >
                                            {{ examSet.title }}
                                        </div>
                                        <div
                                            v-if="examSet.description"
                                            class="line-clamp-1 text-xs text-muted-foreground"
                                        >
                                            {{ examSet.description }}
                                        </div>
                                    </div>
                                </div>
                            </TableCell>

                            <TableCell>
                                <Badge
                                    :variant="
                                        examSet.status === 'published'
                                            ? 'default'
                                            : 'secondary'
                                    "
                                    class="capitalize"
                                >
                                    {{ examSet.status }}
                                </Badge>
                            </TableCell>

                            <TableCell class="text-muted-foreground">
                                {{
                                    examSet.questionnaire_count ??
                                    examSet.questionnaires.length
                                }}
                            </TableCell>

                            <TableCell class="text-muted-foreground">
                                {{ formatDate(examSet.updated_at) }}
                            </TableCell>

                            <TableCell class="text-right">
                                <div
                                    class="flex items-center justify-end gap-1"
                                >
                                    <Link
                                        :href="
                                            examSetsEdit.url({
                                                tenant: slug,
                                                exam_set: examSet.id!,
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
                                        :disabled="isDeletingExamSet"
                                        @click="promptDeleteExamSet(examSet)"
                                    >
                                        <Trash2 class="size-3.5" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableRow
                            v-if="filteredExamSets.length === 0"
                            class="hover:bg-transparent"
                        >
                            <TableCell
                                colspan="5"
                                class="px-4 py-12 text-center"
                            >
                                <div class="space-y-2">
                                    <Layers3
                                        class="mx-auto size-8 text-muted-foreground/50"
                                    />
                                    <p class="font-medium text-foreground">
                                        No exam sets found
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        Create an exam set to start bundling
                                        published questionnaires.
                                    </p>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </PagePanel>

            <ConfirmDialog
                :open="selectedExamSet !== null"
                title="Delete exam set?"
                :description="
                    selectedExamSet
                        ? `${selectedExamSet.title} will be permanently deleted. Linked questionnaires will stay intact.`
                        : ''
                "
                confirm-label="Delete exam set"
                pending-label="Deleting exam set..."
                :pending="isDeletingExamSet"
                @update:open="setDeleteExamSetDialogOpen"
                @confirm="deleteExamSet"
            />
        </PageShell>
    </TenantLayout>
</template>
