<script setup lang="ts">
import {
    AlignLeft,
    CircleDot,
    FileText,
    Hash,
    ImageIcon,
    ListChecks,
    Plus,
    Text,
    Upload,
} from 'lucide-vue-next';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import type { ItemType } from '@/types/questionnaire';

defineEmits<{
    addItem: [type: ItemType];
    addPage: [];
}>();

const ITEM_TYPES: { type: ItemType; label: string; icon: typeof Text; tooltip: string }[] = [
    {
        type: 'instruction',
        label: 'Instruction',
        icon: FileText,
        tooltip: 'Display read-only text, directions, or context. The respondent cannot interact with this — it\'s purely informational.',
    },
    {
        type: 'short_text',
        label: 'Short Text',
        icon: Text,
        tooltip: 'The respondent types a brief answer in a single-line text field. Ideal for names, emails, or short responses.',
    },
    {
        type: 'long_text',
        label: 'Long Text',
        icon: AlignLeft,
        tooltip: 'The respondent types a longer answer in a multi-line textarea. Best for essays, explanations, or detailed feedback.',
    },
    {
        type: 'number',
        label: 'Number',
        icon: Hash,
        tooltip: 'The respondent enters a numeric value. Useful for ages, quantities, scores, or any number-based answer.',
    },
    {
        type: 'single_choice',
        label: 'Single Choice',
        icon: CircleDot,
        tooltip: 'The respondent picks exactly one option from a list (radio buttons). Use for questions with a single correct or preferred answer.',
    },
    {
        type: 'multiple_choice',
        label: 'Multiple Choice',
        icon: ListChecks,
        tooltip: 'The respondent can select one or more options from a list (checkboxes). Use when multiple answers are allowed.',
    },
    {
        type: 'file_upload',
        label: 'File Upload',
        icon: Upload,
        tooltip: 'The respondent uploads a file (PDF, image, document, etc.). Useful for collecting resumes, assignments, or supporting documents.',
    },
    {
        type: 'image',
        label: 'Image',
        icon: ImageIcon,
        tooltip: 'Display an image as part of the questionnaire. The respondent sees the image but does not interact with it directly.',
    },
];
</script>

<template>
    <aside class="w-56 shrink-0 overflow-y-auto border-r border-border/60 bg-muted/10 p-3">
        <p class="mb-2 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase">
            Add Element
        </p>
        <TooltipProvider :delay-duration="300">
            <div class="space-y-1">
                <Tooltip v-for="item in ITEM_TYPES" :key="item.type">
                    <TooltipTrigger as-child>
                        <button
                            type="button"
                            class="flex w-full items-center gap-2 rounded-md px-2.5 py-2 text-left text-sm text-foreground/80 transition-colors hover:bg-muted hover:text-foreground"
                            @click="$emit('addItem', item.type)"
                        >
                            <component
                                :is="item.icon"
                                class="size-4 shrink-0 text-muted-foreground"
                            />
                            {{ item.label }}
                        </button>
                    </TooltipTrigger>
                    <TooltipContent side="right" :side-offset="8" class="max-w-64 text-xs">
                        {{ item.tooltip }}
                    </TooltipContent>
                </Tooltip>
            </div>
        </TooltipProvider>

        <div class="my-3 h-px bg-border/40" />

        <button
            type="button"
            class="flex w-full items-center gap-2 rounded-md px-2.5 py-2 text-left text-sm text-foreground/80 transition-colors hover:bg-muted hover:text-foreground"
            @click="$emit('addPage')"
        >
            <Plus class="size-4 shrink-0 text-muted-foreground" />
            Add Page
        </button>
    </aside>
</template>
