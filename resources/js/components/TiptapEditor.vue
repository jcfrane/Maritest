<script setup lang="ts">
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Placeholder from '@tiptap/extension-placeholder';
import Underline from '@tiptap/extension-underline';
import { ref, watch } from 'vue';
import {
    Bold,
    Italic,
    Underline as UnderlineIcon,
    Strikethrough,
    List,
    ListOrdered,
    ImagePlus,
    Undo2,
    Redo2,
    Heading1,
    Heading2,
    Quote,
    Minus,
} from 'lucide-vue-next';

type Props = {
    modelValue: string;
    placeholder?: string;
    editable?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Start typing...',
    editable: true,
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const fileInput = ref<HTMLInputElement | null>(null);

const editor = useEditor({
    content: props.modelValue,
    editable: props.editable,
    extensions: [
        StarterKit,
        Image.configure({ inline: true, allowBase64: true }),
        Placeholder.configure({ placeholder: props.placeholder }),
        Underline,
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-sm max-w-none focus:outline-none min-h-[80px] px-3 py-2 dark:prose-invert',
        },
    },
    onUpdate: ({ editor: e }) => {
        emit('update:modelValue', e.getHTML());
    },
});

watch(
    () => props.modelValue,
    (val) => {
        if (editor.value && editor.value.getHTML() !== val) {
            editor.value.commands.setContent(val, false);
        }
    },
);

function triggerImageUpload(): void {
    fileInput.value?.click();
}

function onFileSelected(event: Event): void {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file || !editor.value) {
        return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        const result = e.target?.result as string;
        editor.value?.chain().focus().setImage({ src: result }).run();
    };
    reader.readAsDataURL(file);
    target.value = '';
}
</script>

<template>
    <div class="overflow-hidden rounded-lg border border-border/60 bg-background">
        <div
            v-if="editable"
            class="flex flex-wrap items-center gap-0.5 border-b border-border/40 bg-muted/20 px-1.5 py-1"
        >
            <button
                type="button"
                class="inline-flex size-7 items-center justify-center rounded text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                :class="{ '!bg-muted !text-foreground': editor?.isActive('bold') }"
                title="Bold"
                @click="editor?.chain().focus().toggleBold().run()"
            >
                <Bold class="size-3.5" />
            </button>

            <button
                type="button"
                class="inline-flex size-7 items-center justify-center rounded text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                :class="{ '!bg-muted !text-foreground': editor?.isActive('italic') }"
                title="Italic"
                @click="editor?.chain().focus().toggleItalic().run()"
            >
                <Italic class="size-3.5" />
            </button>

            <button
                type="button"
                class="inline-flex size-7 items-center justify-center rounded text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                :class="{ '!bg-muted !text-foreground': editor?.isActive('underline') }"
                title="Underline"
                @click="editor?.chain().focus().toggleUnderline().run()"
            >
                <UnderlineIcon class="size-3.5" />
            </button>

            <button
                type="button"
                class="inline-flex size-7 items-center justify-center rounded text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                :class="{ '!bg-muted !text-foreground': editor?.isActive('strike') }"
                title="Strikethrough"
                @click="editor?.chain().focus().toggleStrike().run()"
            >
                <Strikethrough class="size-3.5" />
            </button>

            <div class="mx-1 h-4 w-px bg-border/60" aria-hidden="true" />

            <button
                type="button"
                class="inline-flex size-7 items-center justify-center rounded text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                :class="{ '!bg-muted !text-foreground': editor?.isActive('heading', { level: 1 }) }"
                title="Heading 1"
                @click="editor?.chain().focus().toggleHeading({ level: 1 }).run()"
            >
                <Heading1 class="size-3.5" />
            </button>

            <button
                type="button"
                class="inline-flex size-7 items-center justify-center rounded text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                :class="{ '!bg-muted !text-foreground': editor?.isActive('heading', { level: 2 }) }"
                title="Heading 2"
                @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()"
            >
                <Heading2 class="size-3.5" />
            </button>

            <div class="mx-1 h-4 w-px bg-border/60" aria-hidden="true" />

            <button
                type="button"
                class="inline-flex size-7 items-center justify-center rounded text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                :class="{ '!bg-muted !text-foreground': editor?.isActive('bulletList') }"
                title="Bullet List"
                @click="editor?.chain().focus().toggleBulletList().run()"
            >
                <List class="size-3.5" />
            </button>

            <button
                type="button"
                class="inline-flex size-7 items-center justify-center rounded text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                :class="{ '!bg-muted !text-foreground': editor?.isActive('orderedList') }"
                title="Ordered List"
                @click="editor?.chain().focus().toggleOrderedList().run()"
            >
                <ListOrdered class="size-3.5" />
            </button>

            <button
                type="button"
                class="inline-flex size-7 items-center justify-center rounded text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                :class="{ '!bg-muted !text-foreground': editor?.isActive('blockquote') }"
                title="Blockquote"
                @click="editor?.chain().focus().toggleBlockquote().run()"
            >
                <Quote class="size-3.5" />
            </button>

            <button
                type="button"
                class="inline-flex size-7 items-center justify-center rounded text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                title="Horizontal Rule"
                @click="editor?.chain().focus().setHorizontalRule().run()"
            >
                <Minus class="size-3.5" />
            </button>

            <div class="mx-1 h-4 w-px bg-border/60" aria-hidden="true" />

            <button
                type="button"
                class="inline-flex size-7 items-center justify-center rounded text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                title="Upload Image"
                @click="triggerImageUpload"
            >
                <ImagePlus class="size-3.5" />
            </button>

            <input
                ref="fileInput"
                type="file"
                accept="image/*"
                class="hidden"
                @change="onFileSelected"
            />

            <div class="mx-1 h-4 w-px bg-border/60" aria-hidden="true" />

            <button
                type="button"
                class="inline-flex size-7 items-center justify-center rounded text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                title="Undo"
                @click="editor?.chain().focus().undo().run()"
            >
                <Undo2 class="size-3.5" />
            </button>

            <button
                type="button"
                class="inline-flex size-7 items-center justify-center rounded text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                title="Redo"
                @click="editor?.chain().focus().redo().run()"
            >
                <Redo2 class="size-3.5" />
            </button>
        </div>

        <EditorContent :editor="editor" />
    </div>
</template>
