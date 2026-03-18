import { computed, reactive } from 'vue';
import type {
    Questionnaire,
    QuestionnaireItem,
    QuestionnairePage,
    QuestionnaireSettings,
    ItemType,
} from '@/types/questionnaire';

const defaultSettings: QuestionnaireSettings = {
    time_limit: null,
    presentation_mode: 'per_page',
    items_per_step: null,
    allow_back_navigation: true,
    shuffle_pages: false,
    shuffle_items: false,
};

export function useBuilderForm(questionnaire?: Questionnaire) {
    const isEditing = computed(() => !!questionnaire?.id);

    const form = reactive<{
        title: string;
        description: string;
        status: 'draft' | 'published';
        settings: QuestionnaireSettings;
        pages: QuestionnairePage[];
    }>({
        title: questionnaire?.title ?? 'Untitled Questionnaire',
        description: questionnaire?.description ?? '',
        status: questionnaire?.status ?? 'draft',
        settings: { ...defaultSettings, ...(questionnaire?.settings ?? {}) },
        pages: questionnaire?.pages?.length
            ? questionnaire.pages.map((p) => ({
                  ...p,
                  items: p.items.map((i) => ({
                      ...i,
                      choices: i.choices?.map((c) => ({ ...c })) ?? [],
                  })),
              }))
            : [createPage(0)],
    });

    function createPage(order: number): QuestionnairePage {
        return {
            id: null,
            title: '',
            description: '',
            order,
            settings: null,
            items: [],
        };
    }

    function createItem(type: ItemType, order: number): QuestionnaireItem {
        const item: QuestionnaireItem = {
            id: null,
            type,
            content: '',
            required: false,
            order,
            properties:
                type === 'single_choice' || type === 'multiple_choice'
                    ? { label_type: 'alphabetical' }
                    : type === 'rating'
                      ? { max_stars: 5 }
                      : type === 'scale_rating'
                        ? {
                              max_scale: 5,
                              min_label: 'Lowest',
                              max_label: 'Highest',
                          }
                        : type === 'spinner'
                          ? { min: 0, max: 100, step: 1 }
                          : null,
            choices: [],
        };

        if (type === 'single_choice' || type === 'multiple_choice') {
            item.choices = [
                {
                    id: null,
                    content: 'Option 1',
                    is_correct: false,
                    order: 0,
                    properties: null,
                },
                {
                    id: null,
                    content: 'Option 2',
                    is_correct: false,
                    order: 1,
                    properties: null,
                },
            ];
        }

        return item;
    }

    function getPayload() {
        return {
            title: form.title,
            description: form.description,
            status: form.status,
            settings: form.settings,
            pages: form.pages.map((p) => ({
                id: p.id,
                title: p.title,
                description: p.description,
                order: p.order,
                settings: p.settings,
                items: p.items.map((item) => ({
                    id: item.id,
                    type: item.type,
                    content: item.content,
                    required: item.required,
                    order: item.order,
                    properties: item.properties,
                    choices: item.choices.map((c) => ({
                        id: c.id,
                        content: c.content,
                        is_correct: c.is_correct,
                        order: c.order,
                        properties: c.properties,
                    })),
                })),
            })),
        };
    }

    return { form, isEditing, createPage, createItem, getPayload };
}
