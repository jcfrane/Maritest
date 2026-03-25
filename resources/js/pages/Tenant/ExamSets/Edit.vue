<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, Layers3, Plus, X } from 'lucide-vue-next';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import PagePanel from '@/components/page/PagePanel.vue';
import PageShell from '@/components/page/PageShell.vue';
import { Badge } from '@/components/ui/badge';
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
import {
    edit as examSetsEdit,
    index as examSetsIndex,
    update as examSetsUpdate,
} from '@/routes/tenant/exam-sets';
import type { BreadcrumbItem } from '@/types';
import type {
    ExamSet,
    ExamSetQuestionnaire,
} from '@/types/exam-set';

type Props = {
    examSet: {
        data: ExamSet;
    };
    availableQuestionnaires: ExamSetQuestionnaire[];
};

const props = defineProps<Props>();

const page = usePage();
const tenant = page.props.currentTenant;
const slug = tenant?.slug ?? '';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard.url(slug) },
    { title: 'Exam Sets', href: examSetsIndex.url(slug) },
    {
        title: 'Edit Exam Set',
        href: examSetsEdit.url({
            tenant: slug,
            exam_set: props.examSet.data.id!,
        }),
    },
];

const form = useForm<{
    title: string;
    description: string;
    status: 'draft' | 'published';
    questionnaire_ids: number[];
}>({
    title: props.examSet.data.title,
    description: props.examSet.data.description ?? '',
    status: props.examSet.data.status,
    questionnaire_ids: props.examSet.data.questionnaires.map(
        (questionnaire) => questionnaire.id,
    ),
});

const selectedQuestionnaires = computed(() =>
    form.questionnaire_ids
        .map((id) =>
            props.availableQuestionnaires.find(
                (questionnaire) => questionnaire.id === id,
            ),
        )
        .filter(
            (questionnaire): questionnaire is ExamSetQuestionnaire =>
                questionnaire !== undefined,
        ),
);

const availableOptions = computed(() =>
    props.availableQuestionnaires.filter(
        (questionnaire) => !form.questionnaire_ids.includes(questionnaire.id),
    ),
);

function addQuestionnaire(id: number): void {
    form.questionnaire_ids = [...form.questionnaire_ids, id];
}

function removeQuestionnaire(id: number): void {
    form.questionnaire_ids = form.questionnaire_ids.filter(
        (questionnaireId) => questionnaireId !== id,
    );
}

function moveQuestionnaire(id: number, direction: 'up' | 'down'): void {
    const currentIndex = form.questionnaire_ids.indexOf(id);

    if (currentIndex === -1) {
        return;
    }

    const nextIndex = direction === 'up' ? currentIndex - 1 : currentIndex + 1;

    if (nextIndex < 0 || nextIndex >= form.questionnaire_ids.length) {
        return;
    }

    const reorderedIds = [...form.questionnaire_ids];
    const [movedId] = reorderedIds.splice(currentIndex, 1);

    reorderedIds.splice(nextIndex, 0, movedId);

    form.questionnaire_ids = reorderedIds;
}

function submit(): void {
    form.put(
        examSetsUpdate.url({
            tenant: slug,
            exam_set: props.examSet.data.id!,
        }),
    );
}
</script>

<template>
    <Head :title="`${tenant?.name} - Edit Exam Set`" />

    <TenantLayout :breadcrumbs="breadcrumbs">
        <PageShell
            title="Edit Exam Set"
            description="Adjust the bundle details and questionnaire order without changing the overall visual flow."
            content-class="gap-6"
        >
            <form
                class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]"
                @submit.prevent="submit"
            >
                <div class="space-y-6">
                    <PagePanel body-class="space-y-6 p-6 md:p-8">
                        <template #header>
                            <div class="space-y-1">
                                <h2
                                    class="text-lg font-semibold tracking-tight text-foreground"
                                >
                                    Exam set details
                                </h2>
                                <p class="text-sm text-muted-foreground">
                                    Update the title, notes, and publication
                                    state.
                                </p>
                            </div>
                        </template>

                        <div class="grid gap-2">
                            <Label for="title">Title</Label>
                            <Input
                                id="title"
                                v-model="form.title"
                                type="text"
                                required
                                autofocus
                            />
                            <InputError :message="form.errors.title" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="description">Description</Label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                class="w-full resize-y rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                placeholder="Describe what this exam set is used for."
                            />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="status">Status</Label>
                            <Select v-model="form.status">
                                <SelectTrigger id="status" class="w-full">
                                    <SelectValue
                                        placeholder="Select a status"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="draft">
                                        Draft
                                    </SelectItem>
                                    <SelectItem value="published">
                                        Published
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p class="text-xs text-muted-foreground">
                                Published exam sets must keep at least one
                                published questionnaire attached.
                            </p>
                            <InputError :message="form.errors.status" />
                        </div>
                    </PagePanel>

                    <PagePanel body-class="space-y-4 p-6">
                        <template #header>
                            <div class="space-y-1">
                                <h2
                                    class="text-lg font-semibold tracking-tight text-foreground"
                                >
                                    Published questionnaires
                                </h2>
                                <p class="text-sm text-muted-foreground">
                                    Add more published questionnaires to expand
                                    this exam set.
                                </p>
                            </div>
                        </template>

                        <div
                            v-if="availableOptions.length === 0"
                            class="rounded-xl border border-dashed border-border/70 bg-muted/10 px-4 py-10 text-center"
                        >
                            <p class="font-medium text-foreground">
                                No additional questionnaires available
                            </p>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Everything published in this tenant is already
                                selected here.
                            </p>
                        </div>

                        <div
                            v-for="questionnaire in availableOptions"
                            :key="questionnaire.id"
                            class="flex flex-col gap-3 rounded-xl border border-border/70 bg-background/70 px-4 py-4 md:flex-row md:items-start md:justify-between"
                        >
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <p class="font-medium text-foreground">
                                        {{ questionnaire.title }}
                                    </p>
                                    <Badge
                                        variant="secondary"
                                        class="capitalize"
                                    >
                                        {{ questionnaire.status }}
                                    </Badge>
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    {{
                                        questionnaire.description ||
                                        'No description provided.'
                                    }}
                                </p>
                            </div>

                            <Button
                                type="button"
                                variant="outline"
                                class="shrink-0"
                                @click="addQuestionnaire(questionnaire.id)"
                            >
                                <Plus class="size-4" />
                                Add
                            </Button>
                        </div>
                    </PagePanel>
                </div>

                <PagePanel
                    body-class="space-y-4 p-6"
                    footer-class="flex items-center gap-4"
                >
                    <template #header>
                        <div class="space-y-1">
                            <h2
                                class="text-lg font-semibold tracking-tight text-foreground"
                            >
                                Selected questionnaires
                            </h2>
                            <p class="text-sm text-muted-foreground">
                                Reorder or remove questionnaires to reshape the
                                sequence.
                            </p>
                        </div>
                    </template>

                    <div
                        v-if="selectedQuestionnaires.length === 0"
                        class="rounded-xl border border-dashed border-border/70 bg-muted/10 px-4 py-10 text-center"
                    >
                        <Layers3
                            class="mx-auto size-8 text-muted-foreground/50"
                        />
                        <p class="mt-3 font-medium text-foreground">
                            No questionnaires selected
                        </p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Add at least one published questionnaire before
                            publishing this exam set.
                        </p>
                    </div>

                    <div
                        v-for="(questionnaire, index) in selectedQuestionnaires"
                        :key="questionnaire.id"
                        class="rounded-xl border border-border/70 bg-background/70 px-4 py-4"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm font-semibold text-primary"
                                    >
                                        {{ index + 1 }}.
                                    </span>
                                    <p class="font-medium text-foreground">
                                        {{ questionnaire.title }}
                                    </p>
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    {{
                                        questionnaire.description ||
                                        'No description provided.'
                                    }}
                                </p>
                            </div>

                            <div class="flex items-center gap-1">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    :disabled="index === 0"
                                    @click="
                                        moveQuestionnaire(
                                            questionnaire.id,
                                            'up',
                                        )
                                    "
                                >
                                    <ArrowUp class="size-4" />
                                </Button>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    :disabled="
                                        index ===
                                        selectedQuestionnaires.length - 1
                                    "
                                    @click="
                                        moveQuestionnaire(
                                            questionnaire.id,
                                            'down',
                                        )
                                    "
                                >
                                    <ArrowDown class="size-4" />
                                </Button>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="text-destructive hover:text-destructive"
                                    @click="
                                        removeQuestionnaire(questionnaire.id)
                                    "
                                >
                                    <X class="size-4" />
                                </Button>
                            </div>
                        </div>
                    </div>

                    <InputError :message="form.errors.questionnaire_ids" />

                    <template #footer>
                        <Button type="submit" :disabled="form.processing">
                            Update Exam Set
                        </Button>
                        <Link :href="examSetsIndex.url(slug)">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                    </template>
                </PagePanel>
            </form>
        </PageShell>
    </TenantLayout>
</template>
