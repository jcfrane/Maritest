import type { LabelType } from '@/types/questionnaire';

export function toRoman(num: number): string {
    const romanNumerals: [number, string][] = [
        [1000, 'M'], [900, 'CM'], [500, 'D'], [400, 'CD'],
        [100, 'C'], [90, 'XC'], [50, 'L'], [40, 'XL'],
        [10, 'X'], [9, 'IX'], [5, 'V'], [4, 'IV'], [1, 'I'],
    ];
    let result = '';

    for (const [value, symbol] of romanNumerals) {
        while (num >= value) {
            result += symbol;
            num -= value;
        }
    }

    return result;
}

export function getChoiceLabel(index: number, labelType: LabelType): string {
    switch (labelType) {
        case 'alphabetical':
            return String.fromCharCode(65 + index);
        case 'numerical':
            return String(index + 1);
        case 'roman':
            return toRoman(index + 1);
        case 'none':
            return '';
    }
}

export function reorderList<T extends { order: number }>(list: T[]): void {
    list.forEach((item, i) => {
        item.order = i;
    });
}

export function toNullableNumber(value: string | number): number | null {
    const normalized = typeof value === 'number' ? value : Number(value);

    return Number.isNaN(normalized) ? null : normalized;
}

export function isRichText(content?: string | null): boolean {
    if (!content) {
        return false;
    }

    return /<[a-z][\s\S]*>/i.test(content);
}
