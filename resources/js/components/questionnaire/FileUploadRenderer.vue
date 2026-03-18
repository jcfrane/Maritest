<script setup lang="ts">
import { Upload } from 'lucide-vue-next';
import { Label } from '@/components/ui/label';
import type { QuestionnaireItem } from '@/types/questionnaire';

const props = defineProps<{
    item: QuestionnaireItem;
    modelValue?: File | null;
}>();

defineEmits<{
    'update:modelValue': [value: File | null];
}>();

const acceptedTypes =
    ((props.item.properties as Record<string, unknown>)
        ?.accepted_types as string) ?? '';
const maxFileSizeMb =
    ((props.item.properties as Record<string, unknown>)
        ?.max_file_size as number) ?? 5;
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
        <div
            class="rounded-lg border-2 border-dashed border-border/60 bg-muted/10 px-6 py-10 transition-colors hover:bg-muted/20"
        >
            <div class="flex flex-col items-center justify-center text-center">
                <div
                    class="mb-4 flex size-12 items-center justify-center rounded-full bg-muted/40"
                >
                    <Upload class="size-6 text-muted-foreground" />
                </div>
                <p class="text-sm font-medium text-foreground">
                    Click to upload or drag and drop
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    <span v-if="acceptedTypes">{{
                        acceptedTypes.split(',').join(', ')
                    }}</span>
                    <span v-else>Any file type</span>
                    (Max {{ maxFileSizeMb }}MB)
                </p>
                <!-- This renderer is UI-only for the builder, actual file upload handling will be needed in respondent view -->
            </div>
        </div>
    </div>
</template>
