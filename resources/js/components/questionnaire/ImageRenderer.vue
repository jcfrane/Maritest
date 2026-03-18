<script setup lang="ts">
import { Image as ImageIcon } from 'lucide-vue-next';
import type { QuestionnaireItem } from '@/types/questionnaire';

defineProps<{
    item: QuestionnaireItem;
}>();
</script>

<template>
    <div>
        <div v-if="item.content">
            <img
                :src="item.content"
                alt="Questionnaire image"
                class="max-h-[600px] w-full rounded-lg border border-border/60 object-contain"
            />
        </div>
        <div
            v-else
            class="flex items-center justify-center rounded-lg border border-dashed border-border/60 bg-muted/10 p-12 text-muted-foreground"
        >
            <div class="flex flex-col items-center gap-2">
                <ImageIcon class="size-8 opacity-50" />
                <span class="text-sm">No image uploaded</span>
            </div>
        </div>
    </div>
</template>
