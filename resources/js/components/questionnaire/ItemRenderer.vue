<script setup lang="ts">
import type { QuestionnaireItem } from '@/types/questionnaire';
import DatePickerRenderer from './DatePickerRenderer.vue';
import FileUploadRenderer from './FileUploadRenderer.vue';
import FillInTheBlankRenderer from './FillInTheBlankRenderer.vue';
import ImageRenderer from './ImageRenderer.vue';
import InstructionRenderer from './InstructionRenderer.vue';
import LongTextRenderer from './LongTextRenderer.vue';
import MultipleChoiceRenderer from './MultipleChoiceRenderer.vue';
import NumberRenderer from './NumberRenderer.vue';
import RatingRenderer from './RatingRenderer.vue';
import ScaleRatingRenderer from './ScaleRatingRenderer.vue';
import ShortTextRenderer from './ShortTextRenderer.vue';
import SingleChoiceRenderer from './SingleChoiceRenderer.vue';
import SpinnerRenderer from './SpinnerRenderer.vue';

defineProps<{
    item: QuestionnaireItem;
    modelValue?: any;
}>();

defineEmits<{
    'update:modelValue': [value: any];
}>();
</script>

<template>
    <div class="questionnaire-renderer">
        <InstructionRenderer v-if="item.type === 'instruction'" :item="item" />
        <ShortTextRenderer
            v-else-if="item.type === 'short_text'"
            :item="item"
            :model-value="modelValue"
            @update:model-value="$emit('update:modelValue', $event)"
        />
        <LongTextRenderer
            v-else-if="item.type === 'long_text'"
            :item="item"
            :model-value="modelValue"
            @update:model-value="$emit('update:modelValue', $event)"
        />
        <NumberRenderer
            v-else-if="item.type === 'number'"
            :item="item"
            :model-value="modelValue"
            @update:model-value="$emit('update:modelValue', $event)"
        />
        <SingleChoiceRenderer
            v-else-if="item.type === 'single_choice'"
            :item="item"
            :model-value="modelValue"
            @update:model-value="$emit('update:modelValue', $event)"
        />
        <MultipleChoiceRenderer
            v-else-if="item.type === 'multiple_choice'"
            :item="item"
            :model-value="modelValue"
            @update:model-value="$emit('update:modelValue', $event)"
        />
        <FileUploadRenderer
            v-else-if="item.type === 'file_upload'"
            :item="item"
            :model-value="modelValue"
            @update:model-value="$emit('update:modelValue', $event)"
        />
        <ImageRenderer v-else-if="item.type === 'image'" :item="item" />
        <RatingRenderer
            v-else-if="item.type === 'rating'"
            :item="item"
            :model-value="modelValue"
            @update:model-value="$emit('update:modelValue', $event)"
        />
        <ScaleRatingRenderer
            v-else-if="item.type === 'scale_rating'"
            :item="item"
            :model-value="modelValue"
            @update:model-value="$emit('update:modelValue', $event)"
        />
        <DatePickerRenderer
            v-else-if="item.type === 'date_picker'"
            :item="item"
            :model-value="modelValue"
            @update:model-value="$emit('update:modelValue', $event)"
        />
        <SpinnerRenderer
            v-else-if="item.type === 'spinner'"
            :item="item"
            :model-value="modelValue"
            @update:model-value="$emit('update:modelValue', $event)"
        />
        <FillInTheBlankRenderer
            v-else-if="item.type === 'fill_in_the_blank'"
            :item="item"
            :model-value="modelValue"
            @update:model-value="$emit('update:modelValue', $event)"
        />
        <div v-else class="text-sm text-destructive">
            Unsupported item type: {{ item.type }}
        </div>
    </div>
</template>
