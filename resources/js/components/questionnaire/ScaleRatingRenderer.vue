<script setup lang="ts">
import { Label } from '@/components/ui/label';
import type { QuestionnaireItem } from '@/types/questionnaire';

const props = defineProps<{
    item: QuestionnaireItem;
    modelValue?: number;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: number];
}>();

const maxScale =
    ((props.item.properties as Record<string, unknown>)?.max_scale as number) ??
    5;
const minLabel =
    ((props.item.properties as Record<string, unknown>)?.min_label as string) ??
    '';
const maxLabel =
    ((props.item.properties as Record<string, unknown>)?.max_label as string) ??
    '';
</script>

<template>
    <div class="space-y-6">
        <div v-if="item.content" class="flex items-start gap-1">
            <div
                class="prose prose-sm max-w-none text-base font-semibold text-foreground dark:prose-invert"
                v-html="item.content"
            />
            <span v-if="item.required" class="mt-1 text-destructive">*</span>
        </div>

        <div class="flex flex-col gap-3">
            <div
                class="flex items-center justify-between px-2 text-xs font-medium text-muted-foreground"
            >
                <span>{{ minLabel }}</span>
                <span>{{ maxLabel }}</span>
            </div>

            <div class="flex items-center justify-between gap-2">
                <label
                    v-for="i in maxScale"
                    :key="i"
                    class="group relative flex flex-1 cursor-pointer flex-col items-center gap-2"
                >
                    <input
                        type="radio"
                        :name="`scale-${item.id ?? item.order}`"
                        :value="i"
                        :checked="modelValue === i"
                        class="peer sr-only"
                        @change="emit('update:modelValue', i)"
                    />
                    <div
                        class="flex aspect-square w-full max-w-14 items-center justify-center rounded-lg border border-border/60 bg-background text-sm font-medium transition-all group-hover:border-primary/50 group-hover:bg-muted peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-primary peer-focus-visible:ring-2 peer-focus-visible:ring-ring peer-focus-visible:ring-offset-2"
                    >
                        {{ i }}
                    </div>
                </label>
            </div>
        </div>
    </div>
</template>
