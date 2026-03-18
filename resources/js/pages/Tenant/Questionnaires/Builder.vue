<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { Cloud, CloudOff, Loader2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { useBuilderAutoSave } from '@/composables/useBuilderAutoSave';
import { useBuilderForm } from '@/composables/useBuilderForm';
import { useBuilderItems } from '@/composables/useBuilderItems';
import { useBuilderPages } from '@/composables/useBuilderPages';
import { useBuilderTiptap } from '@/composables/useBuilderTiptap';
import { useTenantImageUpload } from '@/composables/useTenantImageUpload';
import TenantLayout from '@/layouts/TenantLayout.vue';
import { toNullableNumber } from '@/lib/questionnaire';
import { dashboard } from '@/routes/tenant';
import { index as questionnairesIndex } from '@/routes/tenant/questionnaires';
import type { BreadcrumbItem } from '@/types';
import type { Questionnaire } from '@/types/questionnaire';
import ItemCard from './partials/ItemCard.vue';
import ItemProperties from './partials/ItemProperties.vue';
import ItemToolbox from './partials/ItemToolbox.vue';
import PageTabBar from './partials/PageTabBar.vue';

type Props = {
    questionnaire?: Questionnaire;
};

const props = defineProps<Props>();

const page = usePage();
const { pendingUploads } = useTenantImageUpload();
const slug = computed(
    () => (page.props.currentTenant as { slug: string })?.slug ?? '',
);

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Dashboard', href: dashboard.url(slug.value) },
    { title: 'Questionnaires', href: questionnairesIndex.url(slug.value) },
    { title: isEditing.value ? 'Edit' : 'Create', href: '#' },
]);

const { form, isEditing, createPage, createItem, getPayload } = useBuilderForm(props.questionnaire);

const { activePageIndex, activePage, addPage, removePage, selectPage, renamePage } = useBuilderPages(
    form.pages,
    createPage,
    () => { selectedItemIndex.value = null; },
);

const {
    selectedItemIndex, selectedItem, getItemUiKey,
    initializeItemKeys,
    addItem, removeItem, moveItem, reorderItem,
    isItemCollapsed, toggleItemCollapsed,
    addChoice, removeChoice, handleChoiceCorrect,
} = useBuilderItems(activePage, createItem);

const {
    tiptapEnabledChoices,
    isItemTiptapEnabled, toggleItemTiptap,
    choiceKeyPrefix, toggleChoiceTiptap,
} = useBuilderTiptap(props.questionnaire);

const { saving, lastSaved, formattedLastSaved, save, publish } = useBuilderAutoSave({
    form,
    getPayload,
    isEditing,
    pendingUploads,
    slug,
    questionnaireId: computed(() => props.questionnaire?.id),
});

const activeTab = ref<'build' | 'settings'>('build');

initializeItemKeys(form.pages);

function handlePublish(): void {
    publish(form);
}
</script>

<template>
    <Head :title="isEditing ? 'Edit Questionnaire' : 'New Questionnaire'" />

    <TenantLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-[calc(100vh-4rem)] flex-col">
            <!-- Top Bar -->
            <div
                class="flex items-center justify-between border-b border-border/60 bg-background px-4 py-2.5"
            >
                <div class="flex items-center gap-3">
                    <button
                        v-for="tab in ['build', 'settings'] as const"
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
                    <div
                        v-if="pendingUploads > 0"
                        class="flex items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <Loader2 class="size-3 animate-spin" />
                        Uploading image...
                    </div>
                    <div
                        v-else-if="saving"
                        class="flex items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <Loader2 class="size-3 animate-spin" />
                        Saving...
                    </div>
                    <div
                        v-else-if="formattedLastSaved"
                        class="flex items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <Cloud class="size-3" />
                        Saved at {{ formattedLastSaved }}
                    </div>
                    <div
                        v-else-if="isEditing"
                        class="flex items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <CloudOff class="size-3" />
                        Not saved yet
                    </div>

                    <Badge
                        :variant="
                            form.status === 'published'
                                ? 'default'
                                : 'secondary'
                        "
                        class="capitalize"
                    >
                        {{ form.status }}
                    </Badge>

                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="saving || pendingUploads > 0"
                        @click="save"
                    >
                        {{ isEditing ? 'Save' : 'Save Draft' }}
                    </Button>

                    <Button
                        v-if="form.status === 'draft'"
                        size="sm"
                        :disabled="saving || pendingUploads > 0"
                        @click="handlePublish"
                    >
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

                        <div
                            v-if="activePage"
                            class="mx-auto max-w-3xl space-y-3"
                        >
                            <ItemCard
                                v-for="(item, iIndex) in activePage.items"
                                :key="getItemUiKey(item)"
                                :item="item"
                                :index="iIndex"
                                :total-items="activePage.items.length"
                                :is-selected="selectedItemIndex === iIndex"
                                :is-collapsed="isItemCollapsed(item)"
                                :is-tiptap-enabled="
                                    isItemTiptapEnabled(
                                        activePageIndex,
                                        iIndex,
                                        item.type,
                                    )
                                "
                                :tiptap-enabled-choices="tiptapEnabledChoices"
                                :choice-key-prefix="
                                    choiceKeyPrefix(activePageIndex, iIndex)
                                "
                                @select="selectedItemIndex = iIndex"
                                @remove="removeItem(iIndex)"
                                @move-up="moveItem(iIndex, 'up')"
                                @move-down="moveItem(iIndex, 'down')"
                                @toggle-collapse="toggleItemCollapsed(item)"
                                @toggle-item-tiptap="
                                    toggleItemTiptap(activePageIndex, iIndex)
                                "
                                @update:content="item.content = $event"
                                @add-choice="addChoice(item)"
                                @remove-choice="removeChoice(item, $event)"
                                @toggle-choice-tiptap="
                                    toggleChoiceTiptap(
                                        activePageIndex,
                                        iIndex,
                                        $event,
                                    )
                                "
                                @reorder="reorderItem"
                            />

                            <div
                                v-if="activePage.items.length === 0"
                                class="rounded-lg border border-dashed border-border/50 px-4 py-12 text-center"
                            >
                                <p class="text-sm text-muted-foreground/60">
                                    This page is empty. Use the left sidebar to
                                    add elements.
                                </p>
                            </div>
                        </div>
                    </main>

                    <ItemProperties
                        :selected-item="selectedItem"
                        :active-page="activePage"
                        @update:choice-correct="handleChoiceCorrect"
                    />
                </template>

                <!-- Settings Tab -->
                <div
                    v-if="activeTab === 'settings'"
                    class="flex-1 overflow-y-auto p-6"
                >
                    <div class="mx-auto max-w-xl space-y-6">
                        <h2 class="text-lg font-semibold text-foreground">
                            Questionnaire Settings
                        </h2>

                        <Card
                            class="space-y-5 border-border/50 p-5 shadow-none"
                        >
                            <div class="space-y-1.5">
                                <Label>Time Limit (minutes)</Label>
                                <Input
                                    :model-value="
                                        form.settings.time_limit ?? ''
                                    "
                                    type="number"
                                    class="h-9"
                                    placeholder="No time limit"
                                    :min="1"
                                    @update:model-value="
                                        form.settings.time_limit =
                                            toNullableNumber($event)
                                    "
                                />
                                <p class="text-xs text-muted-foreground">
                                    Leave empty for no time limit.
                                </p>
                            </div>

                            <div class="space-y-1.5">
                                <Label>Presentation Mode</Label>
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        class="flex-1 rounded-md border px-3 py-2 text-sm font-medium transition-colors"
                                        :class="
                                            form.settings.presentation_mode === 'per_page'
                                                ? 'border-primary bg-primary/10 text-primary'
                                                : 'border-border text-muted-foreground hover:text-foreground'
                                        "
                                        @click="form.settings.presentation_mode = 'per_page'; form.settings.items_per_step = null"
                                    >
                                        Per Page
                                    </button>
                                    <button
                                        type="button"
                                        class="flex-1 rounded-md border px-3 py-2 text-sm font-medium transition-colors"
                                        :class="
                                            form.settings.presentation_mode === 'per_item'
                                                ? 'border-primary bg-primary/10 text-primary'
                                                : 'border-border text-muted-foreground hover:text-foreground'
                                        "
                                        @click="form.settings.presentation_mode = 'per_item'; form.settings.items_per_step = form.settings.items_per_step ?? 1"
                                    >
                                        Per Item
                                    </button>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    <strong>Per Page</strong> shows all items on each author-defined page.
                                    <strong>Per Item</strong> presents a fixed number of items per step.
                                </p>
                            </div>

                            <div
                                v-if="form.settings.presentation_mode === 'per_item'"
                                class="space-y-1.5"
                            >
                                <Label>Items Per Step</Label>
                                <Input
                                    :model-value="
                                        form.settings.items_per_step ?? ''
                                    "
                                    type="number"
                                    class="h-9"
                                    :min="1"
                                    @update:model-value="
                                        form.settings.items_per_step =
                                            toNullableNumber($event)
                                    "
                                />
                                <p class="text-xs text-muted-foreground">
                                    Number of items shown at a time. Defaults to 1 for exam mode.
                                </p>
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <Label>Allow Back Navigation</Label>
                                    <p class="text-xs text-muted-foreground">
                                        Let respondents go back to previous
                                        pages.
                                    </p>
                                </div>
                                <Switch
                                    v-model:checked="
                                        form.settings.allow_back_navigation
                                    "
                                />
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <Label>Shuffle Pages</Label>
                                    <p class="text-xs text-muted-foreground">
                                        Randomize the order of pages.
                                    </p>
                                </div>
                                <Switch
                                    v-model:checked="
                                        form.settings.shuffle_pages
                                    "
                                />
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <Label>Shuffle Items</Label>
                                    <p class="text-xs text-muted-foreground">
                                        Randomize question order within pages.
                                        Instructions stay in place.
                                    </p>
                                </div>
                                <Switch
                                    v-model:checked="
                                        form.settings.shuffle_items
                                    "
                                />
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
