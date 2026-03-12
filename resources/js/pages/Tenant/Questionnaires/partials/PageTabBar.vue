<script setup lang="ts">
import { Plus, X } from 'lucide-vue-next';
import { ref } from 'vue';
import type { QuestionnairePage } from '@/types/questionnaire';

const props = defineProps<{
    pages: QuestionnairePage[];
    activePageIndex: number;
}>();

const emit = defineEmits<{
    selectPage: [index: number];
    addPage: [];
    removePage: [index: number];
    renamePage: [index: number, title: string];
}>();

const editingIndex = ref<number | null>(null);
const editingTitle = ref('');

function startEditing(index: number): void {
    editingIndex.value = index;
    editingTitle.value = props.pages[index].title || `Page ${index + 1}`;
}

function finishEditing(): void {
    if (editingIndex.value !== null) {
        emit('renamePage', editingIndex.value, editingTitle.value);
        editingIndex.value = null;
    }
}
</script>

<template>
    <div class="flex items-center gap-0 border-t border-border/60 bg-muted/15 px-2">
        <div class="flex items-center overflow-x-auto">
            <div
                v-for="(pg, pIndex) in pages"
                :key="pIndex"
                class="group relative flex shrink-0 cursor-pointer items-center border-r border-border/30 transition-colors"
                :class="
                    activePageIndex === pIndex
                        ? 'bg-background text-foreground shadow-sm'
                        : 'text-muted-foreground hover:bg-muted/30 hover:text-foreground'
                "
                @click="$emit('selectPage', pIndex)"
            >
                <input
                    v-if="editingIndex === pIndex"
                    v-model="editingTitle"
                    type="text"
                    class="w-24 border-none bg-transparent px-3 py-2 text-xs font-medium outline-none focus:outline-none"
                    @blur="finishEditing"
                    @keydown.enter="finishEditing"
                    @click.stop
                />
                <span
                    v-else
                    class="px-3 py-2 text-xs font-medium"
                    @dblclick.stop="startEditing(pIndex)"
                >
                    {{ pg.title || `Page ${pIndex + 1}` }}
                </span>

                <button
                    v-if="pages.length > 1"
                    type="button"
                    class="mr-1 rounded p-0.5 text-muted-foreground/40 opacity-0 transition-opacity hover:text-destructive group-hover:opacity-100"
                    @click.stop="$emit('removePage', pIndex)"
                >
                    <X class="size-3" />
                </button>

                <div
                    v-if="activePageIndex === pIndex"
                    class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary"
                />
            </div>
        </div>

        <button
            type="button"
            class="ml-1 flex shrink-0 items-center gap-1 rounded px-2 py-2 text-xs text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
            @click="$emit('addPage')"
        >
            <Plus class="size-3.5" />
        </button>
    </div>
</template>
