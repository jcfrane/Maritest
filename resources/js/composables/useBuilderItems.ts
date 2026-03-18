import type { ComputedRef } from 'vue';
import { computed, nextTick, reactive, ref } from 'vue';
import { reorderList } from '@/lib/questionnaire';
import type { QuestionnaireItem, QuestionnairePage, ItemType } from '@/types/questionnaire';

export function useBuilderItems(
    activePage: ComputedRef<QuestionnairePage | null>,
    createItem: (type: ItemType, order: number) => QuestionnaireItem,
) {
    const itemUiKeys = new WeakMap<QuestionnaireItem, string>();
    const collapsedItemKeys = reactive(new Set<string>());
    let nextItemUiKey = 0;

    const selectedItemIndex = ref<number | null>(null);

    const selectedItem = computed(() => {
        if (selectedItemIndex.value === null || !activePage.value) {
            return null;
        }

        return activePage.value.items[selectedItemIndex.value] ?? null;
    });

    function getItemUiKey(item: QuestionnaireItem): string {
        let key = itemUiKeys.get(item);

        if (!key) {
            key = `item-${nextItemUiKey++}`;
            itemUiKeys.set(item, key);
        }

        return key;
    }

    function initializeItemKeys(pages: QuestionnairePage[]): void {
        pages.forEach((page) => {
            page.items.forEach((item) => {
                getItemUiKey(item);
            });
        });
    }

    function addItem(type: ItemType): void {
        if (!activePage.value) {
            return;
        }

        const item = createItem(type, activePage.value.items.length);

        getItemUiKey(item);
        activePage.value.items.push(item);
        selectedItemIndex.value = activePage.value.items.length - 1;
    }

    function removeItem(itemIndex: number): void {
        if (!activePage.value) {
            return;
        }

        const [removedItem] = activePage.value.items.splice(itemIndex, 1);

        if (removedItem) {
            collapsedItemKeys.delete(getItemUiKey(removedItem));
        }

        reorderList(activePage.value.items);
        selectedItemIndex.value = null;
    }

    function moveItem(fromIndex: number, direction: 'up' | 'down'): void {
        if (!activePage.value) {
            return;
        }

        const items = activePage.value.items;
        const toIndex = direction === 'up' ? fromIndex - 1 : fromIndex + 1;

        if (toIndex < 0 || toIndex >= items.length) {
            return;
        }

        const temp = items[fromIndex];
        items[fromIndex] = items[toIndex];
        items[toIndex] = temp;
        reorderList(items);

        if (selectedItemIndex.value === fromIndex) {
            selectedItemIndex.value = toIndex;
        }
    }

    function reorderItem(fromIndex: number, toIndex: number): void {
        if (!activePage.value) {
            return;
        }

        const items = activePage.value.items;
        const [moved] = items.splice(fromIndex, 1);
        items.splice(toIndex, 0, moved);
        reorderList(items);

        if (selectedItemIndex.value === fromIndex) {
            selectedItemIndex.value = toIndex;
        }
    }

    function isItemCollapsed(item: QuestionnaireItem): boolean {
        return collapsedItemKeys.has(getItemUiKey(item));
    }

    function toggleItemCollapsed(item: QuestionnaireItem): void {
        const key = getItemUiKey(item);

        if (collapsedItemKeys.has(key)) {
            collapsedItemKeys.delete(key);
        } else {
            collapsedItemKeys.add(key);
        }
    }

    function addChoice(item: QuestionnaireItem): void {
        item.choices.push({
            id: null,
            content: '',
            is_correct: false,
            order: item.choices.length,
            properties: null,
        });
    }

    function removeChoice(item: QuestionnaireItem, choiceIndex: number): void {
        item.choices.splice(choiceIndex, 1);
        reorderList(item.choices);
    }

    function handleChoiceCorrect(cIdx: number, val: boolean): void {
        if (activePage.value && selectedItemIndex.value !== null) {
            const item = activePage.value.items[selectedItemIndex.value];

            if (item.type === 'single_choice' && val) {
                nextTick(() => {
                    item.choices = item.choices.map((c, idx) => ({
                        ...c,
                        is_correct: idx === cIdx ? true : false,
                    }));
                });
            }
        }
    }

    return {
        selectedItemIndex,
        selectedItem,
        getItemUiKey,
        initializeItemKeys,
        addItem,
        removeItem,
        moveItem,
        reorderItem,
        isItemCollapsed,
        toggleItemCollapsed,
        addChoice,
        removeChoice,
        handleChoiceCorrect,
    };
}
