<script setup lang="ts">
import { Star } from 'lucide-vue-next';
import { Label } from '@/components/ui/label';
import type { QuestionnaireItem } from '@/types/questionnaire';

const props = defineProps<{
    item: QuestionnaireItem;
    modelValue?: number;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: number];
}>();

const maxStars =
    ((props.item.properties as Record<string, unknown>)?.max_stars as number) ??
    5;
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
        <div class="flex items-center gap-1.5">
            <button
                v-for="i in maxStars"
                :key="i"
                type="button"
                class="rounded-full p-1 text-muted-foreground transition-colors hover:text-amber-400 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                :class="modelValue && i <= modelValue ? 'text-amber-400' : ''"
                @click="emit('update:modelValue', i)"
            >
                <Star
                    class="size-8"
                    :class="modelValue && i <= modelValue ? 'fill-current' : ''"
                />
            </button>
        </div>
    </div>
</template>
