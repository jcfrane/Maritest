import { ref } from 'vue';
import { isRichText } from '@/lib/questionnaire';
import type { Questionnaire } from '@/types/questionnaire';

export function useBuilderTiptap(questionnaire?: Questionnaire) {
    const initialTiptapItems = new Set<string>();
    const initialTiptapChoices = new Set<string>();

    if (questionnaire?.pages?.length) {
        questionnaire.pages.forEach((p, pIndex) => {
            p.items?.forEach((i, iIndex) => {
                if (isRichText(i.content)) {
                    initialTiptapItems.add(`${pIndex}-${iIndex}`);
                }

                i.choices?.forEach((c, cIndex) => {
                    if (isRichText(c.content)) {
                        initialTiptapChoices.add(`${pIndex}-${iIndex}-${cIndex}`);
                    }
                });
            });
        });
    }

    const tiptapEnabledItems = ref<Set<string>>(initialTiptapItems);
    const tiptapEnabledChoices = ref<Set<string>>(initialTiptapChoices);

    function itemKey(pIndex: number, iIndex: number): string {
        return `${pIndex}-${iIndex}`;
    }

    function isItemTiptapEnabled(
        pIndex: number,
        iIndex: number,
        type: string,
    ): boolean {
        if (type === 'instruction') {
            return true;
        }

        return tiptapEnabledItems.value.has(itemKey(pIndex, iIndex));
    }

    function toggleItemTiptap(pIndex: number, iIndex: number): void {
        const key = itemKey(pIndex, iIndex);

        if (tiptapEnabledItems.value.has(key)) {
            tiptapEnabledItems.value.delete(key);
        } else {
            tiptapEnabledItems.value.add(key);
        }
    }

    function choiceKeyPrefix(pIndex: number, iIndex: number): string {
        return `${pIndex}-${iIndex}`;
    }

    function toggleChoiceTiptap(
        pIndex: number,
        iIndex: number,
        cIndex: number,
    ): void {
        const key = `${pIndex}-${iIndex}-${cIndex}`;

        if (tiptapEnabledChoices.value.has(key)) {
            tiptapEnabledChoices.value.delete(key);
        } else {
            tiptapEnabledChoices.value.add(key);
        }
    }

    return {
        tiptapEnabledItems,
        tiptapEnabledChoices,
        isItemTiptapEnabled,
        toggleItemTiptap,
        choiceKeyPrefix,
        toggleChoiceTiptap,
    };
}
