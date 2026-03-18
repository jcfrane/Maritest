import { computed, ref } from 'vue';
import { reorderList } from '@/lib/questionnaire';
import type { QuestionnairePage } from '@/types/questionnaire';

export function useBuilderPages(
    pages: QuestionnairePage[],
    createPage: (order: number) => QuestionnairePage,
    onPageChange?: () => void,
) {
    const activePageIndex = ref(0);
    const activePage = computed(() => pages[activePageIndex.value] ?? null);

    function addPage(): void {
        pages.push(createPage(pages.length));
        activePageIndex.value = pages.length - 1;
        onPageChange?.();
    }

    function removePage(index: number): void {
        if (pages.length <= 1) {
            return;
        }

        pages.splice(index, 1);
        reorderList(pages);

        if (activePageIndex.value >= pages.length) {
            activePageIndex.value = pages.length - 1;
        }

        onPageChange?.();
    }

    function selectPage(index: number): void {
        activePageIndex.value = index;
        onPageChange?.();
    }

    function renamePage(index: number, title: string): void {
        pages[index].title = title;
    }

    return { activePageIndex, activePage, addPage, removePage, selectPage, renamePage };
}
