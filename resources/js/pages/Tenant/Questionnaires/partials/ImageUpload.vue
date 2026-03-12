<script setup lang="ts">
import { ImageIcon, Upload, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    modelValue: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const isDragging = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);

function handleDragOver(event: DragEvent): void {
    event.preventDefault();
    isDragging.value = true;
}

function handleDragLeave(): void {
    isDragging.value = false;
}

function handleDrop(event: DragEvent): void {
    event.preventDefault();
    isDragging.value = false;

    const file = event.dataTransfer?.files?.[0];
    if (file && file.type.startsWith('image/')) {
        processFile(file);
    }
}

function handleFileSelect(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (file) {
        processFile(file);
    }
    input.value = '';
}

function processFile(file: File): void {
    const reader = new FileReader();
    reader.onload = (e) => {
        const result = e.target?.result as string;
        emit('update:modelValue', result);
    };
    reader.readAsDataURL(file);
}

function triggerFileInput(): void {
    fileInput.value?.click();
}

function removeImage(): void {
    emit('update:modelValue', '');
}
</script>

<template>
    <div>
        <!-- Preview -->
        <div v-if="modelValue" class="relative">
            <img
                :src="modelValue"
                alt="Uploaded image"
                class="max-h-64 w-full rounded-lg border border-border/60 object-contain"
            />
            <Button
                variant="destructive"
                size="sm"
                class="absolute top-2 right-2 size-7 p-0"
                @click.stop="removeImage"
            >
                <X class="size-3.5" />
            </Button>
        </div>

        <!-- Upload zone -->
        <div
            v-else
            class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed px-4 py-8 transition-colors"
            :class="
                isDragging
                    ? 'border-primary bg-primary/5'
                    : 'border-border/60 hover:border-border hover:bg-muted/20'
            "
            @dragover="handleDragOver"
            @dragleave="handleDragLeave"
            @drop="handleDrop"
            @click.stop="triggerFileInput"
        >
            <div
                class="mb-3 flex size-10 items-center justify-center rounded-full bg-muted/40"
            >
                <ImageIcon class="size-5 text-muted-foreground" />
            </div>
            <p class="text-sm font-medium text-foreground/70">
                Drop image here or click to upload
            </p>
            <p class="mt-1 text-xs text-muted-foreground">
                PNG, JPG, GIF, SVG up to 5MB
            </p>
            <div class="mt-3 flex items-center gap-1.5 text-xs text-primary">
                <Upload class="size-3" />
                Browse files
            </div>
        </div>

        <input
            ref="fileInput"
            type="file"
            accept="image/*"
            class="hidden"
            @change="handleFileSelect"
        />
    </div>
</template>
