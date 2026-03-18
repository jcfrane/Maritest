<script setup lang="ts">
import { ImageIcon, Loader2, Upload, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { useTenantImageUpload } from '@/composables/useTenantImageUpload';

defineProps<{
    modelValue: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const isDragging = ref(false);
const isUploading = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const { uploadImage } = useTenantImageUpload();

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

async function processFile(file: File): Promise<void> {
    if (isUploading.value) return;

    isUploading.value = true;

    try {
        emit('update:modelValue', await uploadImage(file));
    } catch (error) {
        console.error('Error uploading image:', error);
    } finally {
        isUploading.value = false;
        if (fileInput.value) {
            fileInput.value.value = '';
        }
    }
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
            <div v-if="isUploading" class="flex flex-col items-center justify-center">
                <Loader2 class="mb-3 size-10 animate-spin text-primary" />
                <p class="text-sm font-medium text-foreground/70">
                    Uploading image...
                </p>
            </div>
            <template v-else>
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
            </template>
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
