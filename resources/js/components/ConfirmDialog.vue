<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

type Props = {
    open: boolean;
    title: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    pendingLabel?: string;
    pending?: boolean;
};

withDefaults(defineProps<Props>(), {
    description: '',
    confirmLabel: 'Confirm',
    cancelLabel: 'Cancel',
    pendingLabel: 'Working...',
    pending: false,
});

const emit = defineEmits<{
    confirm: [];
    'update:open': [value: boolean];
}>();
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent :show-close-button="!pending" class="sm:max-w-md">
            <DialogHeader class="space-y-3">
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription v-if="description">
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="gap-2">
                <Button
                    type="button"
                    variant="secondary"
                    :disabled="pending"
                    @click="emit('update:open', false)"
                >
                    {{ cancelLabel }}
                </Button>

                <Button
                    type="button"
                    variant="destructive"
                    :disabled="pending"
                    @click="emit('confirm')"
                >
                    {{ pending ? pendingLabel : confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
