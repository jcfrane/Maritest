<script setup lang="ts">
import {
    AlignLeft,
    CircleDot,
    FileText,
    Hash,
    ImageIcon,
    ListChecks,
    Settings2,
    Text,
    Upload,
} from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import type { QuestionnaireItem, QuestionnairePage, ItemType } from '@/types/questionnaire';

type LabelType = 'alphabetical' | 'numerical' | 'roman' | 'none';

const ITEM_TYPES: { type: ItemType; label: string; icon: typeof Text }[] = [
    { type: 'instruction', label: 'Instruction', icon: FileText },
    { type: 'short_text', label: 'Short Text', icon: Text },
    { type: 'long_text', label: 'Long Text', icon: AlignLeft },
    { type: 'number', label: 'Number', icon: Hash },
    { type: 'single_choice', label: 'Single Choice', icon: CircleDot },
    { type: 'multiple_choice', label: 'Multiple Choice', icon: ListChecks },
    { type: 'file_upload', label: 'File Upload', icon: Upload },
    { type: 'image', label: 'Image', icon: ImageIcon },
];

const LABEL_TYPE_OPTIONS: { value: LabelType; label: string }[] = [
    { value: 'alphabetical', label: 'A, B, C...' },
    { value: 'numerical', label: '1, 2, 3...' },
    { value: 'roman', label: 'I, II, III...' },
    { value: 'none', label: 'None' },
];

const props = defineProps<{
    selectedItem: QuestionnaireItem | null;
    activePage: QuestionnairePage | null;
}>();

const emit = defineEmits<{
    'update:choice-correct': [cIdx: number, val: boolean];
}>();

const FILE_TYPE_PRESETS: { value: string; label: string }[] = [
    { value: 'any', label: 'Any file' },
    { value: 'documents', label: 'Documents (PDF, Word, Excel)' },
    { value: 'images', label: 'Images (PNG, JPG, GIF, SVG)' },
    { value: 'pdf', label: 'PDF only' },
    { value: 'custom', label: 'Custom mime types' },
];

const FILE_TYPE_MIME_MAP: Record<string, string> = {
    any: '*/*',
    documents: '.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt',
    images: '.png,.jpg,.jpeg,.gif,.svg,.webp',
    pdf: '.pdf',
};

function ensureProperties(item: QuestionnaireItem): Record<string, unknown> {
    if (!item.properties) {
        item.properties = {};
    }

    return item.properties as Record<string, unknown>;
}

function getProperty(item: QuestionnaireItem, key: string, fallback: unknown = ''): unknown {
    return (item.properties as Record<string, unknown>)?.[key] ?? fallback;
}

function setProperty(item: QuestionnaireItem, key: string, value: unknown): void {
    ensureProperties(item)[key] = value;
}


function getItemLabelType(item: QuestionnaireItem): LabelType {
    return (item.properties as Record<string, unknown>)?.label_type as LabelType ?? 'alphabetical';
}

function setItemLabelType(item: QuestionnaireItem, value: string): void {
    if (!item.properties) {
        item.properties = {};
    }

    (item.properties as Record<string, unknown>).label_type = value;
}

function getChoiceLabel(index: number, labelType: LabelType): string {
    switch (labelType) {
        case 'alphabetical':
            return String.fromCharCode(65 + index);
        case 'numerical':
            return String(index + 1);
        case 'roman': {
            const romanNumerals: [number, string][] = [
                [1000, 'M'], [900, 'CM'], [500, 'D'], [400, 'CD'],
                [100, 'C'], [90, 'XC'], [50, 'L'], [40, 'XL'],
                [10, 'X'], [9, 'IX'], [5, 'V'], [4, 'IV'], [1, 'I'],
            ];
            let result = '';
            let n = index + 1;

            for (const [value, symbol] of romanNumerals) {
                while (n >= value) {
                    result += symbol;
                    n -= value;
                }
            }

            return result;
        }
        case 'none':
            return '';
    }
}

function handleUpdateChoiceCorrect(cIdx: number, val: boolean) {
    if (!props.selectedItem) {
return;
}
    
    // We must mutate the prop directly here to immediately update the local UI
    // while we also emit to Builder to keep the form state in sync.
    // This solves the 'Switch not visually updating' issue natively in Vue.
    if (props.selectedItem.type === 'single_choice') {
        props.selectedItem.choices.forEach((c, idx) => {
             
            c.is_correct = val ? (idx === cIdx) : false;
        });
    } else {
        // eslint-disable-next-line vue/no-mutating-props
        props.selectedItem.choices[cIdx].is_correct = val;
    }

    emit('update:choice-correct', cIdx, val);
}
</script>

<template>
    <aside class="w-72 shrink-0 overflow-y-auto border-l border-border/60 bg-background p-4">
        <!-- Item selected -->
        <template v-if="selectedItem">
            <div class="mb-3 flex items-center gap-2">
                <Settings2 class="size-4 text-muted-foreground" />
                <h3 class="text-sm font-semibold text-foreground">
                    {{ ITEM_TYPES.find((t) => t.type === selectedItem!.type)?.label ?? 'Item' }} Properties
                </h3>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <Label class="text-xs">Required</Label>
                    <!-- eslint-disable-next-line vue/no-mutating-props -->
                    <Switch v-model:checked="selectedItem.required" />
                </div>

                <!-- File Upload properties -->
                <template v-if="selectedItem.type === 'file_upload'">
                    <div class="my-1 h-px bg-border/30" />

                    <div class="space-y-1.5">
                        <Label class="text-xs">Allowed File Types</Label>
                        <Select
                            :model-value="(getProperty(selectedItem, 'file_type_preset', 'any') as string)"
                            @update:model-value="(value) => {
                                const preset = String(value ?? 'any');

                                setProperty(selectedItem!, 'file_type_preset', preset);

                                if (preset !== 'custom') {
                                    setProperty(selectedItem!, 'accepted_types', FILE_TYPE_MIME_MAP[preset]);
                                }
                            }"
                        >
                            <SelectTrigger class="h-9">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="opt in FILE_TYPE_PRESETS"
                                    :key="opt.value"
                                    :value="opt.value"
                                >
                                    {{ opt.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div
                        v-if="getProperty(selectedItem, 'file_type_preset', 'any') === 'custom'"
                        class="space-y-1.5"
                    >
                        <Label class="text-xs">Custom Mime Types</Label>
                        <Input
                            :model-value="(getProperty(selectedItem, 'accepted_types', '') as string)"
                            class="h-9"
                            placeholder=".pdf,.docx,.png"
                            @input="setProperty(selectedItem!, 'accepted_types', ($event.target as HTMLInputElement).value)"
                        />
                        <p class="text-[10px] text-muted-foreground">
                            Comma-separated file extensions.
                        </p>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs">Max File Size (MB)</Label>
                        <Input
                            :model-value="(getProperty(selectedItem, 'max_file_size', 5) as number)"
                            type="number"
                            class="h-9"
                            :min="1"
                            :max="100"
                            @input="setProperty(selectedItem!, 'max_file_size', Number(($event.target as HTMLInputElement).value))"
                        />
                    </div>
                </template>

                <!-- Rating properties -->
                <template v-if="selectedItem.type === 'rating'">
                    <div class="my-1 h-px bg-border/30" />
                    
                    <div class="space-y-1.5">
                        <Label class="text-xs">Max Stars</Label>
                        <Select
                            :model-value="String(getProperty(selectedItem, 'max_stars', 5))"
                            @update:model-value="(v) => setProperty(selectedItem!, 'max_stars', Number(v))"
                        >
                            <SelectTrigger class="h-9">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="3">3 Stars</SelectItem>
                                <SelectItem value="4">4 Stars</SelectItem>
                                <SelectItem value="5">5 Stars</SelectItem>
                                <SelectItem value="10">10 Stars</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </template>

                <!-- Scale Rating properties -->
                <template v-if="selectedItem.type === 'scale_rating'">
                    <div class="my-1 h-px bg-border/30" />
                    
                    <div class="space-y-1.5">
                        <Label class="text-xs">Scale Top (Max)</Label>
                        <Select
                            :model-value="String(getProperty(selectedItem, 'max_scale', 5))"
                            @update:model-value="(v) => setProperty(selectedItem!, 'max_scale', Number(v))"
                        >
                            <SelectTrigger class="h-9">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="3">1 to 3</SelectItem>
                                <SelectItem value="4">1 to 4</SelectItem>
                                <SelectItem value="5">1 to 5</SelectItem>
                                <SelectItem value="7">1 to 7</SelectItem>
                                <SelectItem value="10">1 to 10</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-1.5 pt-2">
                        <Label class="text-xs">Min Label (optional)</Label>
                        <Input
                            :model-value="(getProperty(selectedItem, 'min_label', '') as string)"
                            class="h-9"
                            placeholder="e.g. Strongly Disagree"
                            @input="setProperty(selectedItem!, 'min_label', ($event.target as HTMLInputElement).value)"
                        />
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs">Max Label (optional)</Label>
                        <Input
                            :model-value="(getProperty(selectedItem, 'max_label', '') as string)"
                            class="h-9"
                            placeholder="e.g. Strongly Agree"
                            @input="setProperty(selectedItem!, 'max_label', ($event.target as HTMLInputElement).value)"
                        />
                    </div>
                </template>

                <!-- Spinner properties -->
                <template v-if="selectedItem.type === 'spinner'">
                    <div class="my-1 h-px bg-border/30" />
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label class="text-xs">Min Value</Label>
                            <Input
                                :model-value="(getProperty(selectedItem, 'min', 0) as number)"
                                type="number"
                                class="h-9"
                                @input="setProperty(selectedItem!, 'min', Number(($event.target as HTMLInputElement).value))"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs">Max Value</Label>
                            <Input
                                :model-value="(getProperty(selectedItem, 'max', 100) as number)"
                                type="number"
                                class="h-9"
                                @input="setProperty(selectedItem!, 'max', Number(($event.target as HTMLInputElement).value))"
                            />
                        </div>
                    </div>
                    
                    <div class="space-y-1.5">
                        <Label class="text-xs">Increment Step</Label>
                        <Input
                            :model-value="(getProperty(selectedItem, 'step', 1) as number)"
                            type="number"
                            class="h-9"
                            :min="1"
                            @input="setProperty(selectedItem!, 'step', Number(($event.target as HTMLInputElement).value))"
                        />
                    </div>
                </template>

                <!-- Choice properties -->
                <template
                    v-if="
                        selectedItem.type === 'single_choice' ||
                        selectedItem.type === 'multiple_choice'
                    "
                >
                    <div class="my-1 h-px bg-border/30" />

                    <div class="space-y-1.5">
                        <Label class="text-xs">Choice Labels</Label>
                        <Select
                            :model-value="getItemLabelType(selectedItem)"
                            @update:model-value="(value) => setItemLabelType(selectedItem!, String(value ?? 'alphabetical'))"
                        >
                            <SelectTrigger class="h-9">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="opt in LABEL_TYPE_OPTIONS"
                                    :key="opt.value"
                                    :value="opt.value"
                                >
                                    {{ opt.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="my-1 h-px bg-border/30" />

                    <p class="text-xs text-muted-foreground">
                        Manage choices for this question in the canvas area.
                    </p>
                    <div
                        v-for="(choice, cIdx) in selectedItem.choices"
                        :key="cIdx"
                        class="space-y-1.5"
                    >
                        <div class="flex items-center justify-between">
                            <Label class="text-xs">
                                <span
                                    v-if="getItemLabelType(selectedItem) !== 'none'"
                                    class="font-semibold"
                                >
                                    {{ getChoiceLabel(cIdx, getItemLabelType(selectedItem)) }}.
                                </span>
                                Option {{ cIdx + 1 }}
                            </Label>
                            <div class="flex items-center gap-1.5">
                                <Label class="text-[10px] text-muted-foreground">Correct</Label>
                                <Switch
                                    :key="`switch-${cIdx}-${choice.is_correct}`"
                                    :checked="choice.is_correct"
                                    class="scale-75"
                                    @update:checked="handleUpdateChoiceCorrect(cIdx, $event)"
                                />
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <!-- Page properties -->
        <template v-else-if="activePage">
            <div class="mb-3 flex items-center gap-2">
                <Settings2 class="size-4 text-muted-foreground" />
                <h3 class="text-sm font-semibold text-foreground">Page Properties</h3>
            </div>

            <div class="space-y-4">
                <div class="space-y-1.5">
                    <Label class="text-xs">Page Title</Label>
                    <!-- eslint-disable-next-line vue/no-mutating-props -->
                    <Input v-model="activePage.title" class="h-9" placeholder="Untitled Page" />
                </div>
                <div class="space-y-1.5">
                    <Label class="text-xs">Description</Label>
                    <!-- eslint-disable vue/no-mutating-props -->
                    <Input
                        v-model="activePage.description"
                        class="h-9"
                        placeholder="Optional description"
                    />
                    <!-- eslint-enable vue/no-mutating-props -->
                </div>
            </div>
        </template>

        <!-- No selection -->
        <template v-else>
            <div class="flex h-32 items-center justify-center text-center text-xs text-muted-foreground">
                Select a page or item to edit its properties.
            </div>
        </template>
    </aside>
</template>
