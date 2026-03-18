<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { QuestionnaireItem } from '@/types/questionnaire';

const props = defineProps<{
    item: QuestionnaireItem;
    modelValue?: number;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: number];
}>();

const properties = props.item.properties as Record<string, unknown> | null;
const min = properties?.min as number | undefined;
const max = properties?.max as number | undefined;
const step = (properties?.step as number | undefined) ?? 1;

function updateValue(value: string) {
    emit('update:modelValue', value === '' ? 0 : Number(value));
}
</script>

<template>
    <div class="space-y-3">
        <div v-if="item.content" class="flex items-start gap-1">
            <div
                class="prose prose-sm max-w-none text-base font-semibold text-foreground dark:prose-invert"
                v-html="item.content"
            />
            <span v-if="item.required" class="mt-1 text-destructive">*</span>
        </div>
        <Input
            :model-value="modelValue"
            type="number"
            class="max-w-[150px]"
            :min="min"
            :max="max"
            :step="step"
            @update:model-value="(v) => updateValue(v as string)"
        />
    </div>
</template>
