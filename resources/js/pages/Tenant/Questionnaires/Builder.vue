<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { Cloud, CloudOff, Loader2 } from 'lucide-vue-next';
import { computed, nextTick, reactive, ref, watch } from 'vue';
import ItemCard from './partials/ItemCard.vue';
import ItemProperties from './partials/ItemProperties.vue';
import ItemToolbox from './partials/ItemToolbox.vue';
import PageTabBar from './partials/PageTabBar.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import TenantLayout from '@/layouts/TenantLayout.vue';
import { dashboard } from '@/routes/tenant';
import {
    index as questionnairesIndex,
    store as questionnairesStore,
    update as questionnairesUpdate,
} from '@/routes/tenant/questionnaires';
import type { BreadcrumbItem } from '@/types';
import type {
    Questionnaire,
    QuestionnaireChoice,
    QuestionnaireItem,
    QuestionnairePage,
    QuestionnaireSettings,
    ItemType,
} from '@/types/questionnaire';

type Props = {
    questionnaire?: Questionnaire;
};

const props = defineProps<Props>();

const page = usePage();
const slug = computed(
    () => (page.props.currentTenant as { slug: string })?.slug ?? '',
);

const isEditing = computed(() => !!props.questionnaire?.id);

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Dashboard', href: dashboard.url(slug.value) },
    { title: 'Questionnaires', href: questionnairesIndex.url(slug.value) },
    { title: isEditing.value ? 'Edit' : 'Create', href: '#' },
]);

// ── State ────────────────────────────────────────────────────────────

const defaultSettings: QuestionnaireSettings = {
    time_limit: null,
    items_per_page: 10,
    allow_back_navigation: true,
    shuffle_pages: false,
    shuffle_items: false,
};

const form = reactive<{
    title: string;
    description: string;
    status: 'draft' | 'published';
    settings: QuestionnaireSettings;
    pages: QuestionnairePage[];
}>({
    title: props.questionnaire?.title ?? 'Untitled Questionnaire',
    description: props.questionnaire?.description ?? '',
    status: props.questionnaire?.status ?? 'draft',
    settings: { ...defaultSettings, ...(props.questionnaire?.settings ?? {}) },
    pages: props.questionnaire?.pages?.length
        ? props.questionnaire.pages.map((p) => ({
              ...p,
              items: p.items.map((i) => ({
                  ...i,
                  choices: i.choices?.map((c) => ({ ...c })) ?? [],
              })),
          }))
        : [createPage(0)],
});

const activePageIndex = ref(0);
const selectedItemIndex = ref<number | null>(null);
const activeTab = ref<'build' | 'settings'>('build');
const saving = ref(false);
const lastSaved = ref<Date | null>(null);

const activePage = computed(() => form.pages[activePageIndex.value] ?? null);

const selectedItem = computed(() => {
    if (selectedItemIndex.value === null || !activePage.value) {
        return null;
    }
    return activePage.value.items[selectedItemIndex.value] ?? null;
});

// ── Tiptap toggle state ──────────────────────────────────────────────

const tiptapEnabledItems = ref<Set<string>>(new Set());
const tiptapEnabledChoices = ref<Set<string>>(new Set());

function itemKey(pIndex: number, iIndex: number): string {
    return `${pIndex}-${iIndex}`;
}

function isItemTiptapEnabled(pIndex: number, iIndex: number, type: string): boolean {
    if (type === 'instruction') {
        return true;
    }
    return tiptapEnabledItems.value.has(itemKey(pIndex, iIndex));
}

function toggleItemTiptap(pIndex: number, iIndex: number): void {
    const key = itemKey(pIndex, iIndex);
    if (tiptapEnabledItems.value.has(key)) {
        tiptapEnabledItems.value.delete(key);
    } else {
        tiptapEnabledItems.value.add(key);
    }
}

function choiceKeyPrefix(pIndex: number, iIndex: number): string {
    return `${pIndex}-${iIndex}`;
}

function toggleChoiceTiptap(pIndex: number, iIndex: number, cIndex: number): void {
    const key = `${pIndex}-${iIndex}-${cIndex}`;
    if (tiptapEnabledChoices.value.has(key)) {
        tiptapEnabledChoices.value.delete(key);
    } else {
        tiptapEnabledChoices.value.add(key);
    }
}

// ── Helpers ──────────────────────────────────────────────────────────

function createPage(order: number): QuestionnairePage {
    return { id: null, title: '', description: '', order, settings: null, items: [] };
}

function createItem(type: ItemType, order: number): QuestionnaireItem {
    const item: QuestionnaireItem = {
        id: null,
        type,
        content: '',
        required: false,
        order,
        properties: type === 'single_choice' || type === 'multiple_choice'
            ? { label_type: 'alphabetical' }
            : null,
        choices: [],
    };

    if (type === 'single_choice' || type === 'multiple_choice') {
        item.choices = [
            { id: null, content: 'Option 1', is_correct: false, order: 0, properties: null },
            { id: null, content: 'Option 2', is_correct: false, order: 1, properties: null },
        ];
    }

    return item;
}

function reorderList<T extends { order: number }>(list: T[]): void {
    list.forEach((item, i) => { item.order = i; });
}

// ── Page actions ─────────────────────────────────────────────────────

function addPage(): void {
    form.pages.push(createPage(form.pages.length));
    activePageIndex.value = form.pages.length - 1;
    selectedItemIndex.value = null;
}

function removePage(index: number): void {
    if (form.pages.length <= 1) {
        return;
    }
    form.pages.splice(index, 1);
    reorderList(form.pages);
    if (activePageIndex.value >= form.pages.length) {
        activePageIndex.value = form.pages.length - 1;
    }
    selectedItemIndex.value = null;
}

function selectPage(index: number): void {
    activePageIndex.value = index;
    selectedItemIndex.value = null;
}

function renamePage(index: number, title: string): void {
    form.pages[index].title = title;
}

// ── Item actions ─────────────────────────────────────────────────────

function addItem(type: ItemType): void {
    if (!activePage.value) {
        return;
    }
    activePage.value.items.push(createItem(type, activePage.value.items.length));
    selectedItemIndex.value = activePage.value.items.length - 1;
}

function removeItem(itemIndex: number): void {
    if (!activePage.value) {
        return;
    }
    activePage.value.items.splice(itemIndex, 1);
    reorderList(activePage.value.items);
    selectedItemIndex.value = null;
}

function moveItem(fromIndex: number, direction: 'up' | 'down'): void {
    if (!activePage.value) {
        return;
    }
    const items = activePage.value.items;
    const toIndex = direction === 'up' ? fromIndex - 1 : fromIndex + 1;
    if (toIndex < 0 || toIndex >= items.length) {
        return;
    }
    const temp = items[fromIndex];
    items[fromIndex] = items[toIndex];
    items[toIndex] = temp;
    reorderList(items);
    if (selectedItemIndex.value === fromIndex) {
        selectedItemIndex.value = toIndex;
    }
}

function reorderItem(fromIndex: number, toIndex: number): void {
    if (!activePage.value) {
        return;
    }
    const items = activePage.value.items;
    const [moved] = items.splice(fromIndex, 1);
    items.splice(toIndex, 0, moved);
    reorderList(items);
    if (selectedItemIndex.value === fromIndex) {
        selectedItemIndex.value = toIndex;
    }
}

// ── Choice actions ───────────────────────────────────────────────────

function addChoice(item: QuestionnaireItem): void {
    item.choices.push({
        id: null, content: '', is_correct: false, order: item.choices.length, properties: null,
    });
}

function removeChoice(item: QuestionnaireItem, choiceIndex: number): void {
    item.choices.splice(choiceIndex, 1);
    reorderList(item.choices);
}

// ── Saving ───────────────────────────────────────────────────────────

function getPayload() {
    return {
        title: form.title,
        description: form.description,
        status: form.status,
        settings: form.settings,
        pages: form.pages.map((p) => ({
            id: p.id,
            title: p.title,
            description: p.description,
            order: p.order,
            settings: p.settings,
            items: p.items.map((item) => ({
                id: item.id,
                type: item.type,
                content: item.content,
                required: item.required,
                order: item.order,
                properties: item.properties,
                choices: item.choices.map((c) => ({
                    id: c.id,
                    content: c.content,
                    is_correct: c.is_correct,
                    order: c.order,
                    properties: c.properties,
                })),
            })),
        })),
    };
}

function save(): void {
    saving.value = true;
    const payload = getPayload();

    if (isEditing.value) {
        router.put(
            questionnairesUpdate.url({
                tenant: slug.value,
                questionnaire: props.questionnaire!.id!,
            }),
            payload as Record<string, unknown>,
            {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => {
                    saving.value = false;
                    lastSaved.value = new Date();
                },
            },
        );
    } else {
        router.post(
            questionnairesStore.url(slug.value),
            payload as Record<string, unknown>,
            {
                onFinish: () => {
                    saving.value = false;
                    lastSaved.value = new Date();
                },
            },
        );
    }
}

function publish(): void {
    form.status = 'published';
    nextTick(() => save());
}

// ── Auto-save ────────────────────────────────────────────────────────

let autoSaveTimer: ReturnType<typeof setTimeout> | null = null;

watch(
    () => JSON.stringify(form),
    () => {
        if (!isEditing.value) {
            return;
        }
        if (autoSaveTimer) {
            clearTimeout(autoSaveTimer);
        }
        autoSaveTimer = setTimeout(() => save(), 3000);
    },
);

const formattedLastSaved = computed(() => {
    if (!lastSaved.value) {
        return null;
    }
    return lastSaved.value.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
});
</script>

<template>
    <Head :title="isEditing ? 'Edit Questionnaire' : 'New Questionnaire'" />

    <TenantLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-[calc(100vh-4rem)] flex-col">
            <!-- Top Bar -->
            <div class="flex items-center justify-between border-b border-border/60 bg-background px-4 py-2.5">
                <div class="flex items-center gap-3">
                    <button
                        v-for="tab in (['build', 'settings'] as const)"
                        :key="tab"
                        type="button"
                        class="rounded-md px-3 py-1.5 text-sm font-medium capitalize transition-colors"
                        :class="
                            activeTab === tab
                                ? 'bg-primary text-primary-foreground'
                                : 'text-muted-foreground hover:text-foreground'
                        "
                        @click="activeTab = tab"
                    >
                        {{ tab }}
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    <div v-if="saving" class="flex items-center gap-1.5 text-xs text-muted-foreground">
                        <Loader2 class="size-3 animate-spin" />
                        Saving...
                    </div>
                    <div v-else-if="formattedLastSaved" class="flex items-center gap-1.5 text-xs text-muted-foreground">
                        <Cloud class="size-3" />
                        Saved at {{ formattedLastSaved }}
                    </div>
                    <div v-else-if="isEditing" class="flex items-center gap-1.5 text-xs text-muted-foreground">
                        <CloudOff class="size-3" />
                        Not saved yet
                    </div>

                    <Badge :variant="form.status === 'published' ? 'default' : 'secondary'" class="capitalize">
                        {{ form.status }}
                    </Badge>

                    <Button variant="outline" size="sm" :disabled="saving" @click="save">
                        {{ isEditing ? 'Save' : 'Save Draft' }}
                    </Button>

                    <Button v-if="form.status === 'draft'" size="sm" :disabled="saving" @click="publish">
                        Publish
                    </Button>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex flex-1 overflow-hidden">
                <!-- Build Tab -->
                <template v-if="activeTab === 'build'">
                    <ItemToolbox @add-item="addItem" @add-page="addPage" />

                    <!-- Center Canvas -->
                    <main class="flex-1 overflow-y-auto bg-muted/5 p-6">
                        <div class="mx-auto mb-6 max-w-3xl">
                            <input
                                v-model="form.title"
                                type="text"
                                placeholder="Questionnaire Title"
                                class="w-full border-none bg-transparent text-2xl font-semibold text-foreground outline-none placeholder:text-muted-foreground/50 focus:outline-none"
                            />
                            <input
                                v-model="form.description"
                                type="text"
                                placeholder="Add a description (optional)"
                                class="mt-1 w-full border-none bg-transparent text-sm text-muted-foreground outline-none placeholder:text-muted-foreground/40 focus:outline-none"
                            />
                        </div>

                        <div v-if="activePage" class="mx-auto max-w-3xl space-y-3">
                            <ItemCard
                                v-for="(item, iIndex) in activePage.items"
                                :key="iIndex"
                                :item="item"
                                :index="iIndex"
                                :total-items="activePage.items.length"
                                :is-selected="selectedItemIndex === iIndex"
                                :is-tiptap-enabled="isItemTiptapEnabled(activePageIndex, iIndex, item.type)"
                                :tiptap-enabled-choices="tiptapEnabledChoices"
                                :choice-key-prefix="choiceKeyPrefix(activePageIndex, iIndex)"
                                @select="selectedItemIndex = iIndex"
                                @remove="removeItem(iIndex)"
                                @move-up="moveItem(iIndex, 'up')"
                                @move-down="moveItem(iIndex, 'down')"
                                @toggle-item-tiptap="toggleItemTiptap(activePageIndex, iIndex)"
                                @update:content="item.content = $event"
                                @add-choice="addChoice(item)"
                                @remove-choice="removeChoice(item, $event)"
                                @toggle-choice-tiptap="toggleChoiceTiptap(activePageIndex, iIndex, $event)"
                                @reorder="reorderItem"
                            />

                            <div
                                v-if="activePage.items.length === 0"
                                class="rounded-lg border border-dashed border-border/50 px-4 py-12 text-center"
                            >
                                <p class="text-sm text-muted-foreground/60">
                                    This page is empty. Use the left sidebar to add elements.
                                </p>
                            </div>
                        </div>
                    </main>

                    <ItemProperties
                        :selected-item="selectedItem"
                        :active-page="activePage"
                    />
                </template>

                <!-- Settings Tab -->
                <div v-if="activeTab === 'settings'" class="flex-1 overflow-y-auto p-6">
                    <div class="mx-auto max-w-xl space-y-6">
                        <h2 class="text-lg font-semibold text-foreground">
                            Questionnaire Settings
                        </h2>

                        <Card class="space-y-5 border-border/50 p-5 shadow-none">
                            <div class="space-y-1.5">
                                <Label>Time Limit (minutes)</Label>
                                <Input
                                    v-model.number="form.settings.time_limit"
                                    type="number"
                                    class="h-9"
                                    placeholder="No time limit"
                                    :min="1"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Leave empty for no time limit.
                                </p>
                            </div>

                            <div class="space-y-1.5">
                                <Label>Items Per Page</Label>
                                <Input
                                    v-model.number="form.settings.items_per_page"
                                    type="number"
                                    class="h-9"
                                    :min="1"
                                />
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <Label>Allow Back Navigation</Label>
                                    <p class="text-xs text-muted-foreground">
                                        Let respondents go back to previous pages.
                                    </p>
                                </div>
                                <Switch v-model:checked="form.settings.allow_back_navigation" />
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <Label>Shuffle Pages</Label>
                                    <p class="text-xs text-muted-foreground">
                                        Randomize the order of pages.
                                    </p>
                                </div>
                                <Switch v-model:checked="form.settings.shuffle_pages" />
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <Label>Shuffle Items</Label>
                                    <p class="text-xs text-muted-foreground">
                                        Randomize question order within pages. Instructions stay in place.
                                    </p>
                                </div>
                                <Switch v-model:checked="form.settings.shuffle_items" />
                            </div>
                        </Card>
                    </div>
                </div>
            </div>

            <!-- Bottom Page Tabs -->
            <PageTabBar
                v-if="activeTab === 'build'"
                :pages="form.pages"
                :active-page-index="activePageIndex"
                @select-page="selectPage"
                @add-page="addPage"
                @remove-page="removePage"
                @rename-page="renamePage"
            />
        </div>
    </TenantLayout>
</template>
