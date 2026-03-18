<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { QuestionnaireItem } from '@/types/questionnaire';

defineProps<{
    item: QuestionnaireItem;
    modelValue?: number | string;
}>();

defineEmits<{
    'update:modelValue': [value: number | string];
}>();
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
            class="max-w-xs"
            placeholder="Enter a number..."
            @update:model-value="$emit('update:modelValue', $event)"
        />
    </div>
</template>
