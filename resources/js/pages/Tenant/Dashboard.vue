<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { CalendarDays, ClipboardList, Users } from 'lucide-vue-next';
import DashboardMetricCard from '@/components/DashboardMetricCard.vue';
import PageShell from '@/components/page/PageShell.vue';
import TenantLayout from '@/layouts/TenantLayout.vue';
import { dashboard } from '@/routes/tenant';
import type { BreadcrumbItem } from '@/types';

const page = usePage();
const tenant = page.props.currentTenant;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard.url(tenant?.slug ?? ''),
    },
];

const metricCards = [
    {
        title: 'Candidates',
        value: 0,
        description:
            'Candidate activity will appear here as your workspace data is connected.',
        icon: Users,
        accentClass: 'border-primary/15 bg-primary/10 text-primary',
    },
    {
        title: 'Upcoming Schedules',
        value: 0,
        description:
            'Upcoming schedules will appear here as your calendar data is connected.',
        icon: CalendarDays,
        accentClass:
            'border-secondary/70 bg-secondary text-secondary-foreground',
    },
    {
        title: 'Questionnaires',
        value: 0,
        description:
            'Questionnaire totals will appear here as your workspace data is connected.',
        icon: ClipboardList,
        accentClass: 'border-accent/70 bg-accent text-accent-foreground',
    },
];
</script>

<template>
    <Head :title="`${tenant?.name} - Dashboard`" />

    <TenantLayout :breadcrumbs="breadcrumbs">
        <PageShell
            title="Dashboard"
            description="Review your workspace performance, current activity, and the tools available to you."
            content-class="gap-6"
        >
            <section class="space-y-4">
                <div class="grid gap-4 md:grid-cols-3">
                    <DashboardMetricCard
                        v-for="card in metricCards"
                        :key="card.title"
                        :title="card.title"
                        :value="card.value"
                        :description="card.description"
                        :icon="card.icon"
                        :accent-class="card.accentClass"
                    />
                </div>
            </section>
        </PageShell>
    </TenantLayout>
</template>
