export interface QuestionnaireChoice {
    id?: number | null;
    content: string;
    is_correct: boolean;
    order: number;
    properties: Record<string, unknown> | null;
}

export interface QuestionnaireItem {
    id?: number | null;
    type: string;
    content: string;
    required: boolean;
    order: number;
    properties: Record<string, unknown> | null;
    choices: QuestionnaireChoice[];
}

export interface QuestionnairePage {
    id?: number | null;
    title: string;
    description: string;
    order: number;
    settings: Record<string, unknown> | null;
    items: QuestionnaireItem[];
}

export interface QuestionnaireSettings {
    time_limit: number | null;
    items_per_page: number | null;
    allow_back_navigation: boolean;
    shuffle_pages: boolean;
    shuffle_items: boolean;
}

export interface Questionnaire {
    id?: number;
    title: string;
    description: string;
    status: 'draft' | 'published';
    settings: QuestionnaireSettings;
    pages: QuestionnairePage[];
    created_at?: string;
    updated_at?: string;
}

export type LabelType = 'alphabetical' | 'numerical' | 'roman' | 'none';

export type ItemType =
    | 'instruction'
    | 'short_text'
    | 'long_text'
    | 'number'
    | 'single_choice'
    | 'multiple_choice'
    | 'file_upload'
    | 'image'
    | 'rating'
    | 'scale_rating'
    | 'date_picker'
    | 'spinner'
    | 'fill_in_the_blank';

export const ITEM_TYPE_LABELS: Record<ItemType, string> = {
    instruction: 'Instruction',
    short_text: 'Short Text',
    long_text: 'Long Text',
    number: 'Number',
    single_choice: 'Single Choice',
    multiple_choice: 'Multiple Choice',
    file_upload: 'File Upload',
    image: 'Image',
    rating: 'Rating',
    scale_rating: 'Scale Rating',
    date_picker: 'Date Picker',
    spinner: 'Spinner',
    fill_in_the_blank: 'Fill in the Blank',
};
