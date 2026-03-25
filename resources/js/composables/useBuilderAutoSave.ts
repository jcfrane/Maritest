import type { RequestPayload } from '@inertiajs/core';
import { router } from '@inertiajs/vue3';
import type { ComputedRef, Ref } from 'vue';
import { computed, nextTick, ref, watch } from 'vue';
import {
    store as questionnairesStore,
    update as questionnairesUpdate,
} from '@/routes/tenant/questionnaires';

export function useBuilderAutoSave(options: {
    form: object;
    getPayload: () => object;
    isEditing: ComputedRef<boolean>;
    pendingUploads: Readonly<Ref<number>>;
    slug: ComputedRef<string>;
    questionnaireId: ComputedRef<number | undefined>;
}) {
    const {
        form,
        getPayload,
        isEditing,
        pendingUploads,
        slug,
        questionnaireId,
    } = options;

    const saving = ref(false);
    const lastSaved = ref<Date | null>(null);

    const formattedLastSaved = computed(() => {
        if (!lastSaved.value) {
            return null;
        }

        return lastSaved.value.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
        });
    });

    function save(): void {
        if (pendingUploads.value > 0) {
            return;
        }

        saving.value = true;
        const payload = getPayload();

        if (isEditing.value) {
            router.put(
                questionnairesUpdate.url({
                    tenant: slug.value,
                    questionnaire: questionnaireId.value!,
                }),
                payload as unknown as RequestPayload,
                {
                    preserveState: true,
                    preserveScroll: true,
                    onFinish: () => {
                        saving.value = false;
                        lastSaved.value = new Date();
                    },
                },
            );
        } else {
            router.post(
                questionnairesStore.url(slug.value),
                payload as unknown as RequestPayload,
                {
                    onFinish: () => {
                        saving.value = false;
                        lastSaved.value = new Date();
                    },
                },
            );
        }
    }

    function publish(formStatus: { status: string }): void {
        formStatus.status = 'published';
        nextTick(() => save());
    }

    // Auto-save watcher with 3s debounce
    let autoSaveTimer: ReturnType<typeof setTimeout> | null = null;

    watch(
        () => JSON.stringify(form),
        () => {
            if (!isEditing.value || pendingUploads.value > 0) {
                return;
            }

            if (autoSaveTimer) {
                clearTimeout(autoSaveTimer);
            }

            autoSaveTimer = setTimeout(() => save(), 3000);
        },
    );

    return { saving, lastSaved, formattedLastSaved, save, publish };
}
