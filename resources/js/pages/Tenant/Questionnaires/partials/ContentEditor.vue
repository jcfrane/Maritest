<script setup lang="ts">
import { Pilcrow, Type } from 'lucide-vue-next';
import TiptapEditor from '@/components/TiptapEditor.vue';

defineProps<{
    modelValue: string;
    placeholder?: string;
    isTiptapEnabled: boolean;
    isInstruction: boolean;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
    toggleEditor: [];
}>();

function onInput(value: string): void {
    emit('update:modelValue', value);
}
</script>

<template>
    <div class="relative">
        <TiptapEditor
            v-if="isTiptapEnabled"
            :model-value="modelValue"
            :placeholder="placeholder"
            @update:model-value="onInput"
        />
        <textarea
            v-else
            :value="modelValue"
            :placeholder="placeholder"
            rows="3"
            class="w-full resize-y rounded-lg border border-border/60 bg-background px-3 py-2 text-sm text-foreground outline-none placeholder:text-muted-foreground/50 focus:ring-1 focus:ring-primary/30"
            @input="onInput(($event.target as HTMLTextAreaElement).value)"
        />
        <button
            v-if="!isInstruction"
            type="button"
            class="absolute top-1 right-1 inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-[10px] text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
            :title="isTiptapEnabled ? 'Switch to plain text' : 'Switch to rich text editor'"
            @click.stop="$emit('toggleEditor')"
        >
            <Pilcrow v-if="!isTiptapEnabled" class="size-3" />
            <Type v-else class="size-3" />
            {{ isTiptapEnabled ? 'Plain' : 'Rich' }}
        </button>
    </div>
</template>
