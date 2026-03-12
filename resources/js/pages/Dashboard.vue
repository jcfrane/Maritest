<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageShell from '@/components/page/PageShell.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PageShell
            title="Dashboard"
            description="Your workspace overview will appear here."
            content-class="gap-6"
        />
    </AppLayout>
</template>
