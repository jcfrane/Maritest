<script setup lang="ts">
import {
    AlignLeft,
    ChevronDown,
    ChevronUp,
    CircleDot,
    FileText,
    GripVertical,
    Hash,
    ImageIcon,
    ListChecks,
    Text,
    Trash2,
    Upload,
    Eye,
    EyeOff,
    Star,
    ListStart,
    Calendar,
    ChevronDownSquare,
    WholeWord,
} from 'lucide-vue-next';
import { ref } from 'vue';
import ItemRenderer from '@/components/questionnaire/ItemRenderer.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { QuestionnaireItem, ItemType } from '@/types/questionnaire';
import { ITEM_TYPE_LABELS } from '@/types/questionnaire';
import ChoiceList from './ChoiceList.vue';
import ContentEditor from './ContentEditor.vue';
import ImageUpload from './ImageUpload.vue';

const props = defineProps<{
    item: QuestionnaireItem;
    index: number;
    totalItems: number;
    isSelected: boolean;
    isCollapsed: boolean;
    isTiptapEnabled: boolean;
    tiptapEnabledChoices: Set<string>;
    choiceKeyPrefix: string;
}>();

const emit = defineEmits<{
    select: [];
    remove: [];
    moveUp: [];
    moveDown: [];
    toggleCollapse: [];
    toggleItemTiptap: [];
    addChoice: [];
    removeChoice: [choiceIndex: number];
    toggleChoiceTiptap: [choiceIndex: number];
    'update:content': [value: string];
    reorder: [fromIndex: number, toIndex: number];
}>();

const ITEM_ICONS: Record<string, typeof Text> = {
    instruction: FileText,
    short_text: Text,
    long_text: AlignLeft,
    number: Hash,
    single_choice: CircleDot,
    multiple_choice: ListChecks,
    file_upload: Upload,
    image: ImageIcon,
    rating: Star,
    scale_rating: ListStart,
    date_picker: Calendar,
    spinner: ChevronDownSquare,
    fill_in_the_blank: WholeWord,
};

function getItemIcon(type: string) {
    return ITEM_ICONS[type] ?? Text;
}

const isDragging = ref(false);
const isDragOver = ref(false);
const isPreview = ref(false);

function onDragStart(event: DragEvent): void {
    isDragging.value = true;
    event.dataTransfer!.effectAllowed = 'move';
    event.dataTransfer!.setData('text/plain', String(props.index));
}

function onDragEnd(): void {
    isDragging.value = false;
    isDragOver.value = false;
}

function onDragOver(event: DragEvent): void {
    event.dataTransfer!.dropEffect = 'move';
    isDragOver.value = true;
}

function onDrop(event: DragEvent): void {
    isDragOver.value = false;
    const fromIndex = Number(event.dataTransfer!.getData('text/plain'));

    if (fromIndex !== props.index) {
        emit('reorder', fromIndex, props.index);
    }
}
</script>

<template>
    <div
        class="group relative flex cursor-pointer items-start gap-3 rounded-lg border bg-background px-4 py-3 transition-all hover:shadow-sm"
        :class="[
            isSelected
                ? 'border-primary/50 shadow-sm ring-2 ring-primary/20'
                : 'border-border hover:border-border/80',
            isDragOver ? 'border-t-2 border-t-primary' : '',
            isDragging ? 'opacity-40' : '',
        ]"
        :draggable="true"
        @click.stop="$emit('select')"
        @dragstart="onDragStart"
        @dragend="onDragEnd"
        @dragover.prevent="onDragOver"
        @dragleave="isDragOver = false"
        @drop.prevent="onDrop"
    >
        <div class="mt-0.5 flex shrink-0 flex-col items-center gap-0.5">
            <GripVertical
                class="size-4 cursor-grab text-muted-foreground/40 active:cursor-grabbing"
            />
            <button
                v-if="index > 0"
                type="button"
                class="rounded p-0.5 text-muted-foreground/40 hover:text-foreground"
                @click.stop="$emit('moveUp')"
            >
                <ChevronUp class="size-3" />
            </button>
            <button
                v-if="index < totalItems - 1"
                type="button"
                class="rounded p-0.5 text-muted-foreground/40 hover:text-foreground"
                @click.stop="$emit('moveDown')"
            >
                <ChevronDown class="size-3" />
            </button>
        </div>

        <div class="flex-1 space-y-2">
            <div class="flex items-center gap-2">
                <component
                    :is="getItemIcon(item.type)"
                    class="size-4 shrink-0 text-muted-foreground"
                />
                <span
                    class="text-xs font-medium text-muted-foreground uppercase"
                >
                    {{ ITEM_TYPE_LABELS[item.type as ItemType] ?? item.type }}
                </span>
                <Badge
                    v-if="item.required"
                    variant="outline"
                    class="text-[10px]"
                >
                    Required
                </Badge>
                <Badge
                    v-if="isCollapsed"
                    variant="secondary"
                    class="text-[10px]"
                >
                    Collapsed
                </Badge>
            </div>

            <div v-show="!isCollapsed" class="space-y-2">
                <template v-if="!isPreview">
                    <ImageUpload
                        v-if="item.type === 'image'"
                        :model-value="item.content"
                        @update:model-value="$emit('update:content', $event)"
                    />

                    <ContentEditor
                        v-else
                        :model-value="item.content"
                        :placeholder="
                            item.type === 'instruction'
                                ? 'Enter instruction...'
                                : item.type === 'fill_in_the_blank'
                                  ? 'Type ___ (three underscores) for blanks...'
                                  : 'Enter question text...'
                        "
                        :is-tiptap-enabled="isTiptapEnabled"
                        :is-instruction="item.type === 'instruction'"
                        @update:model-value="$emit('update:content', $event)"
                        @toggle-editor="$emit('toggleItemTiptap')"
                    />

                    <ChoiceList
                        v-if="
                            item.type === 'single_choice' ||
                            item.type === 'multiple_choice'
                        "
                        :item="item"
                        :tiptap-enabled-choices="tiptapEnabledChoices"
                        :choice-key-prefix="choiceKeyPrefix"
                        @add-choice="$emit('addChoice')"
                        @remove-choice="$emit('removeChoice', $event)"
                        @toggle-choice-tiptap="
                            $emit('toggleChoiceTiptap', $event)
                        "
                    />
                </template>

                <template v-else>
                    <div
                        class="mt-4 rounded-md border border-border/40 bg-muted/5 p-4"
                    >
                        <ItemRenderer :item="item" />
                    </div>
                </template>
            </div>
        </div>

        <div
            class="flex flex-col items-center gap-1 transition-opacity"
            :class="
                isSelected || isCollapsed
                    ? 'opacity-100'
                    : 'opacity-0 group-hover:opacity-100'
            "
        >
            <Button
                variant="ghost"
                size="sm"
                class="size-7 shrink-0 p-0 text-muted-foreground hover:text-foreground"
                :title="isCollapsed ? 'Expand item' : 'Collapse item'"
                :aria-label="isCollapsed ? 'Expand item' : 'Collapse item'"
                :aria-expanded="!isCollapsed"
                @click.stop="$emit('toggleCollapse')"
            >
                <ChevronDown v-if="isCollapsed" class="size-3.5" />
                <ChevronUp v-else class="size-3.5" />
            </Button>
            <Button
                v-if="!isCollapsed"
                variant="ghost"
                size="sm"
                class="size-7 shrink-0 p-0 text-muted-foreground hover:text-foreground"
                :title="isPreview ? 'Exit preview' : 'Preview as respondent'"
                @click.stop="isPreview = !isPreview"
            >
                <EyeOff v-if="isPreview" class="size-3.5" />
                <Eye v-else class="size-3.5" />
            </Button>
            <Button
                v-if="!isCollapsed"
                variant="ghost"
                size="sm"
                class="size-7 shrink-0 p-0 text-muted-foreground hover:text-destructive"
                title="Remove item"
                @click.stop="$emit('remove')"
            >
                <Trash2 class="size-3.5" />
            </Button>
        </div>
    </div>
</template>
