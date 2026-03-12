<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PagePanel from '@/components/page/PagePanel.vue';
import PageShell from '@/components/page/PageShell.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import TenantLayout from '@/layouts/TenantLayout.vue';
import { dashboard } from '@/routes/tenant';
import {
    create as candidatesCreate,
    index as candidatesIndex,
    store as candidatesStore,
} from '@/routes/tenant/candidates';
import type { BreadcrumbItem } from '@/types';

const page = usePage();
const tenant = page.props.currentTenant;
const slug = tenant?.slug ?? '';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard.url(slug) },
    { title: 'Candidates', href: candidatesIndex.url(slug) },
    { title: 'Create Candidate', href: candidatesCreate.url(slug) },
];

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function submit(): void {
    form.post(candidatesStore.url(slug));
}
</script>

<template>
    <Head :title="`${tenant?.name} - Create Candidate`" />

    <TenantLayout :breadcrumbs="breadcrumbs">
        <PageShell
            title="Create Candidate"
            description="Create a candidate profile and start tracking participant activity."
            content-class="gap-6"
        >
            <PagePanel panel-class="mx-auto w-full max-w-3xl" body-class="p-6 md:p-8">
                <template #header>
                    <div class="space-y-1">
                        <h2
                            class="text-lg font-semibold tracking-tight text-foreground"
                        >
                            Account details
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Enter the basic information needed to create the
                            account and start managing candidate activity.
                        </p>
                    </div>
                </template>

                <form
                    class="mx-auto w-full max-w-xl space-y-6"
                    @submit.prevent="submit"
                >
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            autofocus
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">
                            Confirm Password
                        </Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            required
                        />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button type="submit" :disabled="form.processing">
                            Create Candidate
                        </Button>
                        <Link :href="candidatesIndex.url(slug)">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                    </div>
                </form>
            </PagePanel>
        </PageShell>
    </TenantLayout>
</template>
