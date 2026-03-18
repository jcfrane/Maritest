<script setup lang="ts">
import { Label } from '@/components/ui/label';
import type { QuestionnaireItem } from '@/types/questionnaire';

const props = defineProps<{
    item: QuestionnaireItem;
    modelValue?: number | null; // choice id or index depending on setup, using index for now
}>();

const emit = defineEmits<{
    'update:modelValue': [value: number];
}>();

function getChoiceLabel(index: number): string {
    const labelType =
        ((props.item.properties as Record<string, unknown>)
            ?.label_type as string) ?? 'alphabetical';

    switch (labelType) {
        case 'alphabetical':
            return String.fromCharCode(65 + index);
        case 'numerical':
            return String(index + 1);
        case 'roman': {
            const romanNumerals: [number, string][] = [
                [100, 'C'],
                [90, 'XC'],
                [50, 'L'],
                [40, 'XL'],
                [10, 'X'],
                [9, 'IX'],
                [5, 'V'],
                [4, 'IV'],
                [1, 'I'],
            ];
            let result = '';
            let n = index + 1;

            for (const [value, symbol] of romanNumerals) {
                while (n >= value) {
                    result += symbol;
                    n -= value;
                }
            }

            return result;
        }
        case 'none':
        default:
            return '';
    }
}
</script>

<template>
    <div class="space-y-4">
        <div v-if="item.content" class="flex items-start gap-1">
            <div
                class="prose prose-sm max-w-none text-base font-semibold text-foreground dark:prose-invert"
                v-html="item.content"
            />
            <span v-if="item.required" class="mt-1 text-destructive">*</span>
        </div>
        <div class="space-y-3">
            <label
                v-for="(choice, cIdx) in item.choices"
                :key="cIdx"
                class="flex cursor-pointer items-start gap-3 rounded-lg border border-border/60 p-3 transition-colors hover:bg-muted/30"
                :class="
                    modelValue === cIdx
                        ? 'border-primary/50 bg-primary/5 ring-1 ring-primary/20'
                        : ''
                "
            >
                <div class="mt-0.5 flex h-4 items-center gap-2">
                    <input
                        type="radio"
                        :name="`item-${item.id ?? item.order}`"
                        :value="cIdx"
                        :checked="modelValue === cIdx"
                        class="size-4 border-muted-foreground/50 text-primary focus:ring-primary"
                        @change="emit('update:modelValue', cIdx)"
                    />
                    <span
                        v-if="getChoiceLabel(cIdx)"
                        class="min-w-[1rem] text-sm font-semibold text-muted-foreground"
                    >
                        {{ getChoiceLabel(cIdx) }}.
                    </span>
                </div>
                <div
                    class="prose prose-sm max-w-none flex-1 text-foreground"
                    v-html="choice.content"
                />
            </label>
        </div>
    </div>
</template>
