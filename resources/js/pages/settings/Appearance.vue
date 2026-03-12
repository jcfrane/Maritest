<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import PagePanel from '@/components/page/PagePanel.vue';
import PageShell from '@/components/page/PageShell.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/appearance';
import type { BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: edit(),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance settings" />

        <PageShell
            title="Appearance settings"
            description="Choose how the interface should look during daily work."
            content-class="gap-6"
        >
            <SettingsLayout>
                <PagePanel body-class="p-6">
                    <template #header>
                        <div class="space-y-1">
                            <h2
                                class="text-lg font-semibold tracking-tight text-foreground"
                            >
                                Theme preference
                            </h2>
                            <p class="text-sm text-muted-foreground">
                                Update your account's appearance settings.
                            </p>
                        </div>
                    </template>

                    <AppearanceTabs />
                </PagePanel>
            </SettingsLayout>
        </PageShell>
    </AppLayout>
</template>
