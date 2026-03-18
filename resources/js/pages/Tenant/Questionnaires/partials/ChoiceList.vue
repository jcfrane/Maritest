<script setup lang="ts">
import { Pilcrow, Plus, Trash2, Type } from 'lucide-vue-next';
import TiptapEditor from '@/components/TiptapEditor.vue';
import { Button } from '@/components/ui/button';
import type { QuestionnaireItem } from '@/types/questionnaire';

type LabelType = 'alphabetical' | 'numerical' | 'roman' | 'none';

const props = defineProps<{
    item: QuestionnaireItem;
    tiptapEnabledChoices: Set<string>;
    choiceKeyPrefix: string;
}>();

defineEmits<{
    addChoice: [];
    removeChoice: [index: number];
    toggleChoiceTiptap: [index: number];
}>();

function toRoman(num: number): string {
    const romanNumerals: [number, string][] = [
        [1000, 'M'], [900, 'CM'], [500, 'D'], [400, 'CD'],
        [100, 'C'], [90, 'XC'], [50, 'L'], [40, 'XL'],
        [10, 'X'], [9, 'IX'], [5, 'V'], [4, 'IV'], [1, 'I'],
    ];
    let result = '';

    for (const [value, symbol] of romanNumerals) {
        while (num >= value) {
            result += symbol;
            num -= value;
        }
    }

    return result;
}

function getChoiceLabel(index: number, labelType: LabelType): string {
    switch (labelType) {
        case 'alphabetical':
            return String.fromCharCode(65 + index);
        case 'numerical':
            return String(index + 1);
        case 'roman':
            return toRoman(index + 1);
        case 'none':
            return '';
    }
}

function getLabelType(): LabelType {
    return (props.item.properties as Record<string, unknown>)?.label_type as LabelType ?? 'alphabetical';
}

function isChoiceTiptap(cIndex: number): boolean {
    return props.tiptapEnabledChoices.has(`${props.choiceKeyPrefix}-${cIndex}`);
}
</script>

<template>
    <div class="space-y-1.5 pl-1">
        <div
            v-for="(choice, cIndex) in item.choices"
            :key="cIndex"
            class="flex items-start gap-2"
        >
            <div class="mt-2 flex shrink-0 items-center gap-1.5">
                <div
                    class="size-4 rounded border border-border/60"
                    :class="item.type === 'single_choice' ? 'rounded-full' : 'rounded-sm'"
                />
                <span
                    v-if="getLabelType() !== 'none'"
                    class="min-w-[1rem] text-xs font-semibold text-muted-foreground"
                >
                    {{ getChoiceLabel(cIndex, getLabelType()) }}.
                </span>
            </div>
            <div class="relative flex-1">
                <TiptapEditor
                    v-if="isChoiceTiptap(cIndex)"
                    v-model="choice.content"
                    placeholder="Option text..."
                />
                <input
                    v-else
                    v-model="choice.content"
                    type="text"
                    placeholder="Option text..."
                    class="w-full rounded-lg border border-border/60 bg-background px-3 py-1.5 text-sm text-foreground outline-none placeholder:text-muted-foreground/50 focus:ring-1 focus:ring-primary/30"
                />
                <button
                    type="button"
                    class="absolute top-0.5 right-0.5 inline-flex items-center rounded p-0.5 text-muted-foreground/40 transition-colors hover:text-foreground"
                    :title="isChoiceTiptap(cIndex) ? 'Switch to plain text' : 'Switch to rich text'"
                    @click.stop="$emit('toggleChoiceTiptap', cIndex)"
                >
                    <Pilcrow v-if="!isChoiceTiptap(cIndex)" class="size-3" />
                    <Type v-else class="size-3" />
                </button>
            </div>
            <Button
                variant="ghost"
                size="sm"
                class="mt-0.5 size-6 shrink-0 p-0 text-muted-foreground hover:text-destructive"
                @click.stop="$emit('removeChoice', cIndex)"
            >
                <Trash2 class="size-3" />
            </Button>
        </div>
        <button
            type="button"
            class="flex items-center gap-1.5 pl-6 text-xs text-primary hover:underline"
            @click.stop="$emit('addChoice')"
        >
            <Plus class="size-3" />
            Add Option
        </button>
    </div>
</template>
