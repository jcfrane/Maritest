<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import type { QuestionnaireItem } from '@/types/questionnaire';

const props = defineProps<{
    item: QuestionnaireItem;
    modelValue?: Record<string, string>;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: Record<string, string>];
}>();

// Replace ___ (three or more underscores) with unique tokens for replacement
const parts = computed(() => {
    // Basic stripping of block HTML like <p> but keeping inline text
    const text = props.item.content.replace(/<\/?p[^>]*>/g, ' ').trim();
    // Split by 3 or more underscores
    const segments = text.split(/_{3,}/);

    const result: Array<{
        type: 'text' | 'input';
        content?: string;
        id?: string;
    }> = [];

    segments.forEach((segment, index) => {
        if (segment) {
            result.push({ type: 'text', content: segment });
        }

        // If not the last segment, it means there was an underscore block here
        if (index < segments.length - 1) {
            result.push({ type: 'input', id: `blank_${index}` });
        }
    });

    return result;
});

function updateBlank(id: string, value: string) {
    const current = { ...(props.modelValue || {}) };
    current[id] = value;
    emit('update:modelValue', current);
}

function getValue(id: string): string {
    return props.modelValue?.[id] || '';
}
</script>

<template>
    <div class="space-y-4">
        <!-- Optional instruction/context if needed, though usually the text is the item content itself -->
        <span v-if="item.required" class="text-xs font-medium text-destructive">
            * Required
        </span>
        <div class="leading-loose text-foreground">
            <template v-for="(part, idx) in parts" :key="idx">
                <span v-if="part.type === 'text'" v-html="part.content" />
                <Input
                    v-else-if="part.type === 'input'"
                    :model-value="getValue(part.id!)"
                    class="mx-1 inline-flex h-8 w-32 px-2 py-1 align-baseline"
                    @update:model-value="
                        (v) => updateBlank(part.id!, v as string)
                    "
                />
            </template>
        </div>
    </div>
</template>
