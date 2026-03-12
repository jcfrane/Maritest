<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    BadgeCheck,
    Building2,
    ShieldCheck,
    Sparkles,
    Users,
} from 'lucide-vue-next';
import DashboardMetricCard from '@/components/DashboardMetricCard.vue';
import PagePanel from '@/components/page/PagePanel.vue';
import PageShell from '@/components/page/PageShell.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { dashboard } from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: dashboard.url(),
    },
];

const metricCards = [
    {
        title: 'Tenant oversight',
        value: 'Ready',
        description:
            'Monitor workspace health and prepare for tenant expansion.',
        icon: Building2,
        accentClass: 'border-primary/15 bg-primary/10 text-primary',
    },
    {
        title: 'User administration',
        value: 'Enabled',
        description: 'Access controls and role-based permissions are wired in.',
        icon: Users,
        accentClass:
            'border-secondary/70 bg-secondary text-secondary-foreground',
    },
    {
        title: 'Platform setup',
        value: 'In progress',
        description:
            'Questionnaires, schedules, and reporting can layer in next.',
        icon: Sparkles,
        accentClass: 'border-accent/70 bg-accent text-accent-foreground',
    },
];

const focusAreas = [
    'Create and brand tenant workspaces before inviting production users.',
    'Use role assignments to keep landlord and tenant access boundaries clear.',
    'Stage exam modules in admin-first flows before exposing them to tenants.',
];

const coverageItems = [
    {
        label: 'Landlord controls',
        value: 'Active',
        class: 'bg-primary/10 text-primary',
    },
    {
        label: 'Permission model',
        value: 'Configured',
        class: 'bg-secondary text-secondary-foreground',
    },
    {
        label: 'Tenant onboarding',
        value: 'Next step',
        class: 'bg-accent text-accent-foreground',
    },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <PageShell
            title="Admin Dashboard"
            description="Review landlord-level workspace status, governance coverage, and the next operational priorities."
            content-class="gap-6"
        >
            <Card
                class="relative overflow-hidden border-primary/15 bg-gradient-to-br from-primary/10 via-card to-secondary/45 py-0"
            >
                <CardContent class="relative space-y-6 p-6 md:p-8">
                    <div class="flex flex-wrap items-center gap-3">
                        <Badge
                            variant="secondary"
                            class="rounded-full border border-primary/15 bg-background/80 px-3 py-1 text-[0.7rem] tracking-[0.18em] text-primary uppercase"
                        >
                            Landlord control
                        </Badge>
                        <span class="text-sm text-muted-foreground">
                            Central view for multi-tenant operations
                        </span>
                    </div>

                    <div class="max-w-3xl space-y-3">
                        <h2
                            class="text-3xl font-semibold tracking-tight sm:text-4xl"
                        >
                            Admin dashboard with clearer hierarchy and cleaner
                            surfaces.
                        </h2>
                        <p class="text-sm leading-7 text-muted-foreground">
                            Use this space to manage tenant onboarding, user
                            governance, and platform-wide setup without the
                            starter-kit placeholder blocks.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Badge
                            variant="secondary"
                            class="rounded-full bg-background/75 px-3 py-1"
                        >
                            Tenant management
                        </Badge>
                        <Badge
                            variant="secondary"
                            class="rounded-full bg-background/75 px-3 py-1"
                        >
                            User governance
                        </Badge>
                        <Badge
                            variant="secondary"
                            class="rounded-full bg-background/75 px-3 py-1"
                        >
                            Platform configuration
                        </Badge>
                    </div>
                </CardContent>

                <div
                    class="pointer-events-none absolute right-[-3.5rem] bottom-[-4.5rem] size-56 rounded-full bg-primary/12 blur-3xl"
                />
            </Card>

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

            <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
                <PagePanel body-class="space-y-3 p-6">
                    <template #header>
                        <div class="space-y-1">
                            <h2 class="text-xl font-semibold tracking-tight">
                                Operational focus
                            </h2>
                            <p class="text-sm text-muted-foreground">
                                The highest-leverage admin work to shape next.
                            </p>
                        </div>
                    </template>

                    <div
                        v-for="item in focusAreas"
                        :key="item"
                        class="flex items-start gap-3 rounded-2xl border border-border/60 bg-background/75 px-4 py-3"
                    >
                        <div
                            class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary"
                        >
                            <ShieldCheck class="size-4" />
                        </div>
                        <p class="text-sm leading-6 text-muted-foreground">
                            {{ item }}
                        </p>
                    </div>
                </PagePanel>

                <PagePanel body-class="space-y-3 p-6">
                    <template #header>
                        <div class="space-y-1">
                            <h2 class="text-xl font-semibold tracking-tight">
                                Coverage snapshot
                            </h2>
                            <p class="text-sm text-muted-foreground">
                                A lightweight status view for the landlord
                                layer.
                            </p>
                        </div>
                    </template>

                    <div
                        v-for="item in coverageItems"
                        :key="item.label"
                        class="flex items-center justify-between gap-4 rounded-2xl border border-border/60 bg-background/75 px-4 py-3"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-8 items-center justify-center rounded-full bg-secondary text-secondary-foreground"
                            >
                                <BadgeCheck class="size-4" />
                            </div>
                            <span class="text-sm font-medium text-foreground">
                                {{ item.label }}
                            </span>
                        </div>

                        <span
                            class="rounded-full px-3 py-1 text-xs font-semibold"
                            :class="item.class"
                        >
                            {{ item.value }}
                        </span>
                    </div>
                </PagePanel>
            </div>
        </PageShell>
    </AdminLayout>
</template>
