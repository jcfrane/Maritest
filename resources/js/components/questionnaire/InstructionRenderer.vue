<script setup lang="ts">
import type { QuestionnaireItem } from '@/types/questionnaire';

defineProps<{
    item: QuestionnaireItem;
}>();
</script>

<template>
    <div
        class="prose prose-sm max-w-none text-muted-foreground"
        v-html="item.content"
    />
</template>
