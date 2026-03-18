<script setup lang="ts">
import { Label } from '@/components/ui/label';
import type { QuestionnaireItem } from '@/types/questionnaire';

defineProps<{
    item: QuestionnaireItem;
    modelValue?: string;
}>();

defineEmits<{
    'update:modelValue': [value: string];
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
        <textarea
            :value="modelValue"
            class="w-full resize-y rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
            rows="4"
            placeholder="Type your detailed answer here..."
            @input="
                $emit(
                    'update:modelValue',
                    ($event.target as HTMLTextAreaElement).value,
                )
            "
        />
    </div>
</template>
